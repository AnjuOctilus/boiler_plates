<?php


namespace App\Repositories;


use App\Models\LeadDoc;
use App\Models\User;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\Aws\S3\S3Client;
use App\Repositories\CakeRepository;
use App\Repositories\UserRepository;
use Aws;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\LogInterface;
use App\Jobs\PostLeadsToCake;
use App\Models\LeadDocBase;
use Illuminate\Support\Facades\Storage;

/**
 * Class PDFGenerationRepository
 *
 * @package App\Repositories
 */
class PDFGenerationRepository implements PDFGenerationInterface
{
    /**
     * PDFGenerationRepository constructor.
     *
     * @param UserInterface $userInterface
     * @param LogInterface $logRepo
     */
    public function __construct(UserInterface $userInterface, LogInterface $logRepo)
    {
        $this->userInterface = $userInterface;
        $this->logInterface = $logRepo;
        $this->logRepoObject = new LogRepository();
        $this->commonFunObject = new CommonFunctionsRepository();
        $this->cakeRepo = new CakeRepository();

    }

    /**
     * Generate PDF
     */
    public function generatePDF($userId, $milestone_status = "live")
    {
        $isQualified = $this->userInterface->isQualifiedBank($userId);

        if ($isQualified == 1) {
            $is_pdf_complete = $this->userInterface->isPdfDocCompleteBank($userId);
            if ($is_pdf_complete == 0) {
                if (env('APP_ENV') == 'local') {
                    $pdf_storage_path = storage_path() . '/app/uploads/pdf/pdf/';
                } else {
                    $pdf_storage_path = storage_path();
                }

                try{
                    // file name encryption
                    $s3_enc_file_name = substr(md5('loa_' . $userId), 0, 15);
                    $path_pdf_store = $s3_enc_file_name . ".pdf";
                    $path_pdf = $pdf_storage_path . $path_pdf_store;

                    $userRep = new UserRepository();

                    $currentDate = Carbon::now()->toDateString();
                    $currentDate = date("d-m-Y", strtotime($currentDate));
                    $user_data = $user_data_log = $userRep->userPdfDetails($userId);
                    $surname = (isset($user_data['surname']) ? $user_data['surname'] : $user_data['first_name']);

                    $user_data['reference'] = $userId . "-L";

                    $user_address = $userRep->userAddressDetails($userId);

                    if (isset($user_data['signature_image']) && !empty($user_data['signature_image'])) {
                        $user_data['user_signature'] = simplexml_load_file($user_data['signature_image'])->signature->__toString();
                    }
                    
                    foreach($user_address as $key => $address){ 
                        if($address->address_type == 0){
                            //  Address
                            $user_address_combined = [
                                $address->address_line1,
                                $address->address_line2,
                                $address->address_line3,
                                $address->address_line4,
                                $address->town,
                                $address->country,
                                $address->postcode
                            ];
                            $user_address_combined = array_filter($user_address_combined);
                            $user_address_combined = implode(', ', $user_address_combined);
                            $user_data['userAddress'] = $user_address_combined;
                        }
                    }

                    // Generate PDF
                    $pdf = PDF::loadView('PDFLOA.index', ['user_data' => $user_data, 'current_date' => $currentDate]);
                    $pdf->setPaper('A4', 'p');
                    $pdf->save($path_pdf);
                    unset($pdf);

                    // S3 Push
                    $APP_ENV = env('APP_ENV');
                    if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                        $s3_basic_path = "pdf/xml/live/";
                    } else {
                        $s3_basic_path = "pdf/xml/dev/";
                    }

                    $s3_pdf = $this->saveFileIntoS3($s3_enc_file_name . ".pdf", $path_pdf, $s3_basic_path);

                    $leadDocData = LeadDoc::updateOrCreate(
                        [
                            'user_id' => $userId,
                        ],
                        ["bank_loa_pdf_files" => $s3_pdf]
                    );

                } catch (\Exception $exception) {
                    $msg = "pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                }
            } else {
                return "Success";
            }
        } else {
            if ($isQualified != 1) {
                Log::warning("Unqualified Lead : User Id - " . $userId);
                return "Unqualified Lead : User Id - " . $userId;
            }
        }
    }
    /**
     * Generate PDF test
     *
     * @param $userId
     * @param string $milestone_status
     * @return string
     */
    public function generatePDFTest($userId, $milestone_status = "live")
    {
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        $canCreatePdf = $this->canCreatePdf($userId);
        if ($isQualified == 1 && $canCreatePdf == 1) {
            $is_pdf_complete = $this->userInterface->isPdfDocCompleteBank($userId);
            if (true) {
                if (env('APP_ENV') == 'local') {
                    $pdf_storage_path = storage_path() . '/app/uploads/pdf/pdf/';
                } else {
                    $pdf_storage_path = storage_path();
                }
                $userBankData = $this->commonFunObject->fetchUserBanks($userId);
                $fetchUserClaimid = $this->commonFunObject->fetchUserClaimid($userId);
                try {
                    $path_pdf_store = [];
                    $referenceCount = 1;
                    foreach ($userBankData as $bankkey => $bankvalue) {
                        $bank_id = $bankvalue->bank_id;
                        $bank_code = $bankvalue->bank_code;
                        $bank_name = $bankvalue->bank_name;
                        $claim_id = isset($fetchUserClaimid[$bank_code]) ? $fetchUserClaimid[$bank_code] : '';
                        // file name encryption
                        $s3_enc_file_name = substr(md5('plc_loa_' . $userId . '_' . $bank_id), 0, 15);
                        $path_pdf_store[$bank_code] = $s3_enc_file_name . ".pdf";
                        $path_pdf = $pdf_storage_path . $path_pdf_store[$bank_code];

                        $currentDate = Carbon::now()->toDateString();
                        $currentDate = date("d-m-Y", strtotime($currentDate));
                        $user_data = $user_data_log = $this->getUserData($userId);
                        $user_data['lendername'] = (isset($bank_name) ? $bank_name : '');
                        $surname = (isset($user_data['surname']) ? $user_data['surname'] : $user_data['first_name']);
                        $user_prev_address_data = $this->getClaimantPrevAddressData($userId);
                        // reference count

                        $user_data['reference'] = $userId . "-L" . $referenceCount;

                        //  Address
                        $user_address_combined = [
                            $user_data['housename'],
                            $user_data['housenumber'],
                            $user_data['address3'],
                            $user_data['street'],
                            $user_data['town'],
                            $user_data['county'],
                            $user_data['postcode'],
                        ];
                        $user_address_combined = array_filter($user_address_combined);
                        $user_address_combined = implode(', ', $user_address_combined);
                        $user_data['userAddress'] = $user_address_combined;

                        if (empty($user_data['housenumber'])) {
                            $user_data['housenumber'] = $user_data['address3'];
                        } else if (!empty($user_data['address3'])) {
                            $user_data['housenumber'] .= ", " . $user_data['address3'];
                        }

                        //Pre address

                        foreach ($user_prev_address_data as $key => $user_prev_address) {
                            $user_prev_address_combined = [
                                $user_prev_address['previous_address_company'],
                                $user_prev_address['previous_address_line1'],
                                $user_prev_address['previous_address_line2'],
                                $user_prev_address['previous_address_line3'],
                                $user_prev_address['previous_address_city'],
                                $user_prev_address['previous_address_province'],
                                $user_prev_address['previous_postcode'],
                            ];
                        }
                        $user_prev_address_combined = array_filter($user_prev_address_combined);
                        $user_prev_address_combined = implode(', ', $user_prev_address_combined);
                        $user_data['userPreAddress'] = $user_prev_address_combined;
                        // Generate PDF
                        $pdf = PDF::loadView('PDFLOA.index', ['user_data' => $user_data, 'current_date' => $currentDate]);
                        $pdf->setPaper('A4', 'p');
                        $pdf->save($path_pdf);
                        unset($pdf);
                        // S3 Push
                        $APP_ENV = env('APP_ENV');
                        if ($APP_ENV == 'live') {
                            $s3_basic_path = "live/";
                        } elseif ($APP_ENV == 'pre') {
                            $s3_basic_path = "pre/";
                        } else {
                            $s3_basic_path = "dev/";
                        }
                        $s3_rewrite_path = 'http://pdf.onlineplevincheck.co.uk/';
                        $s3_path = $s3_rewrite_path . $s3_basic_path . $s3_enc_file_name . ".pdf";
                        $s3_pdf = $this->saveFileIntoS3($s3_enc_file_name . ".pdf", $path_pdf, $s3_basic_path);
                        $s3_pdf = ($s3_pdf != false) ? $s3_path : "false";

                        // Store the PDF retrieval path
                        $user_token = User::where('id', '=', $userId)->first('token');
                        $pdf_file_name = $s3_enc_file_name . ".pdf";
                        $fbankname = str_replace(' ', '_', $bank_name);
                        $s3_retrieve_path = env('DOC_URL', 'https://doc.onlineplevincheck.co.uk/') . 'loa_pdfs/' . $surname . '_LOA_' . $fbankname . "_" . $pdf_file_name . '?token=' . $user_token->token;
                        $s3_retrieve_path = ($s3_pdf != false) ? $s3_retrieve_path : false;
                        $path_s3_pdf_store[$bank_id] = $s3_retrieve_path;
                        //  save to lead DOC table
                        $file_name = 'pdf_doc_data';
                        $strLogContent = "**************** Date: " . date('Y-m-d H:i:s') . "USER ID: " . $userId . "**********\r\n";
                        $userDetails = [
                            'user_name' => $user_data->user_name,
                            'user_address' => $user_data->housenumber . ',' . $user_data->street . ',' . $user_data->town . ',' . $user_data->county . ',' . $user_data->country . ',' . $user_data->postcode,
                            'user_dob' => date("d-m-Y", strtotime($user_data->user_dob)),
                            'user_sign_date' => date("d-m-Y", strtotime($user_data->user_signature_created)),
                            's3_path' => '',
                            'bank_code' => $bank_code
                        ];
                        $log_data = $strLogContent . "user details:   " . json_encode(@$userDetails) . "***************\r\n";
                        $this->logRepoObject->writeLog($file_name, $log_data);
                        $referenceCount++;
                    }
                    $userLoaPdfData = $this->commonFunObject->fetchUserLoaPdf($userId);
                    if (count($userLoaPdfData) == 0) {

                        $bank_loa_pdf_data = json_encode($path_s3_pdf_store, JSON_UNESCAPED_SLASHES);
                        $pdfdata = array(
                            "bank_loa_pdf_files" => $bank_loa_pdf_data
                        );

                        $leadDocData = LeadDoc::updateOrCreate(
                            [
                                'user_id' => $userId,
                            ],
                            $pdfdata
                        );
                    } else {
                        $bank_loa_pdf_data_update = $userLoaPdfData + $path_s3_pdf_store;
                        $bank_loa_pdf_data_update = json_encode($bank_loa_pdf_data_update);
                        LeadDoc::where('user_id', $userId)
                            ->update(['bank_loa_pdf_files' => $bank_loa_pdf_data_update]);
                    }
                } catch (\Exception $exception) {
                    $msg = "pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                }
                return "Success";
            } else {
                return "failed";
            }
        } else {
            if ($isQualified != 1) {
                Log::warning("Unqualified Lead. User Id:" . $userId);
                return "Unqualified Lead";
            }
        }
    }
    /**
     * Save file  into s3
     *
     * @param $fileName
     * @param $filePath
     * @param $folderPath
     * @return mixed
     */
    public function saveFileIntoS3($fileName, $filePath, $folderPath)
    {
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');

        $s3 = new Aws\S3\S3Client([
            'region' => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);
        $result = $s3->putObject([
            'Bucket' => $s3BucketName,
            'Key' => $folderPath . '' . $fileName,
            //'SourceFile' => $filePath,
            //'content'=>$filePath,
            'Body'=>$filePath,
            'ContentType' => 'application/pdf',
            
            'ACL' => 'private'
        ]);
        $arrResult = (array)$result;
        foreach ($arrResult as $key => $arrValue) {
            if (preg_replace('/[^\da-z]/i', "", $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200") {
                return $arrValue["@metadata"]["effectiveUri"];
            }
        }
    }
    /**
     * Get User Data
     *
     * @param $user_id
     * @return mixed
     */
    public function getUserData($user_id)
    {
        $user_data = User::join('signatures', 'users.id', '=', 'signatures.user_id')
            ->leftjoin('signature_details', 'users.id', '=', 'signature_details.user_id')
            ->leftjoin('lead_docs', 'users.id', '=', 'lead_docs.user_id')
            ->leftjoin('user_extra_details', 'users.id', '=', 'user_extra_details.user_id')
            ->leftjoin('buyer_api_responses', 'users.id', '=', 'buyer_api_responses.user_id')
            ->select('user_extra_details.housenumber',
                'user_extra_details.housename',
                'user_extra_details.street',
                'user_extra_details.town',
                'user_extra_details.country',
                'user_extra_details.postcode',
                'user_extra_details.county',
                'user_extra_details.address3',
                'signatures.s3_file_path AS user_signature',
                DB::raw("DATE_FORMAT(signatures.created_at,'%Y-%m-%d') as user_signature_created"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name) AS user_name"),
                'users.first_name',
                'users.title',
                'users.last_name AS surname',
                'signatures.previous_name',
                'users.dob AS user_dob',
                'buyer_api_responses.lead_id',
                'lead_docs.created_at as lead_created_date',
                'signature_details.previous_address_company as previous_address1',
                'signature_details.previous_address_line1 as previous_address2',
                'signature_details.previous_address_city as previous_town',
                'signature_details.previous_address_province as previous_county',
                'signature_details.previous_postcode'
            )
            ->where('users.id', $user_id)
            ->first();

        return $user_data;
    }
    /**
     * Get claimant data
     *
     * @param $user_id
     * @return mixed
     */
    public function getClaimantData($user_id)
    {
        $user_data = User::join('signatures', 'users.id', '=', 'signatures.user_id')
            ->leftjoin('user_extra_details', 'users.id', '=', 'user_extra_details.user_id')
            // ->leftjoin('buyer_api_responses','users.id','=','buyer_api_responses.user_id')
            ->select('signatures.user_id AS user_reference_number',
                'user_extra_details.housenumber',
                'user_extra_details.street',
                'user_extra_details.town',
                'user_extra_details.country',
                'user_extra_details.postcode',
                'user_extra_details.housename',
                'user_extra_details.county',
                'user_extra_details.address3',
                'signatures.s3_file_path AS user_signature',
                'signatures.previous_name AS user_previous_name',
                DB::raw("DATE_FORMAT(signatures.created_at,'%Y-%m-%d') as user_signature_created"),
                'users.first_name AS user_firstname', 'users.last_name AS user_lastname',
                'users.title AS title', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS user_name"),
                'users.dob AS user_dob')
            ->where('users.id', $user_id)
            ->first();

        return $user_data;
    }
    public function getClaimantPrevAddressData($user_id)
    {
        $user_data = User::join('signatures', 'users.id', '=', 'signatures.user_id')
            ->leftjoin('signature_details', 'users.id', '=', 'signature_details.user_id')
            ->select('signature_details.previous_address_no', 'signature_details.previous_postcode', 'signature_details.previous_address_id', 'signature_details.previous_address', 'signature_details.previous_address_line1', 'signature_details.previous_address_line2', 'signature_details.previous_address_line3', 'signature_details.previous_address_city', 'signature_details.previous_address_province', 'signature_details.previous_address_country', 'signature_details.previous_address_company')
            ->where('users.id', $user_id)
            ->get()
            ->toArray();

        return $user_data;
    }
    public function fnRegeneratePDFTest()
    {

        if (env('APP_ENV') == 'local') {
            $pdf_storage_path = storage_path() . '/app/uploads/pdf/pdf/';
        } else {
            $pdf_storage_path = storage_path();
        }

        ini_set('max_execution_time', 300);
        ini_set("memory_limit", "512M");

        // file name encryption
        $s3_enc_file_name = substr(md5('plc_loa_'), 0, 15);
        $path_pdf_store['1'] = $s3_enc_file_name . ".pdf";
        $path_pdf = $pdf_storage_path . $path_pdf_store['1'];

        //dd($path_pdf);

        $data = 'Data';

        // Generate PDF
        $pdf = PDF::loadView('PDF.sample', ['data' => $data]);
        $pdf->setPaper('A4', 'p');
        $pdf->save($path_pdf);
        unset($pdf);
    }
    //##########################################################
    //  E-signature Pdf Creation
    /**
     * Get e sign user data
     *
     * @param $user_id
     * @return mixed
     */
    public function getE_sign_UserData($user_id)
    {
        $user_data = User::join('signatures', 'users.id', '=', 'signatures.user_id')
            ->leftjoin('lead_docs', 'users.id', '=', 'lead_docs.user_id')
            ->leftjoin('user_extra_details', 'users.id', '=', 'user_extra_details.user_id')
            ->leftjoin('visitors', 'users.visitor_id', '=', 'visitors.id')
            ->leftjoin('buyer_api_responses', 'users.id', '=', 'buyer_api_responses.user_id')
            ->select('user_extra_details.housenumber',
                'user_extra_details.housename',
                'user_extra_details.street',
                'user_extra_details.town',
                'user_extra_details.country',
                'user_extra_details.postcode',
                'user_extra_details.county',
                'user_extra_details.address3',
                'signatures.s3_file_path AS user_signature',
                'signatures.created_at as signed_date',
                DB::raw("DATE_FORMAT(signatures.created_at,'%Y-%m-%d') as user_signature_created"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name) AS user_name"),
                'users.title',
                'users.first_name',
                'users.last_name AS surname',
                'signatures.previous_name',
                'users.dob AS user_dob',
                'buyer_api_responses.lead_id',
                'lead_docs.created_at as lead_created_date',
                'visitors.ip_address'
            )
            ->where('users.id', $user_id)
            ->first();

        return $user_data;
    }
    /**
     * Generate esignature
     *
     * @param $userId
     * @param $lender
     * @return false|string
     */
    public function generateEsignature($userId, $lender)
    {
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        $canCreatePdf = 1;
        if ($isQualified == 1 && $canCreatePdf == 1) {
            $is_pdf_complete = 0;
            if ($is_pdf_complete == 0) {
                if (env('APP_ENV') == 'local') {
                    $pdf_storage_path = storage_path() . '/app/uploads/pdf/pdf/';
                } else {
                    $pdf_storage_path = storage_path();
                }
                $pdf_storage_path = storage_path();
                try {
                    $path_pdf_store = "";
                    $referenceCount = 1;
                    $user_data = $user_data_log = $this->getE_sign_UserData($userId);
                    // file name encryption
                    $s3_enc_file_name = substr(md5('plc_coa_' . $userId . '_' . $lender), 0, 16);
                    $path_pdf_store = $s3_enc_file_name . ".pdf";
                    $path_pdf = $pdf_storage_path . $path_pdf_store;
                    $currentDate = Carbon::now()->toDateString();
                    $currentDate = date("Y-m-d h:i:s", time());
                    $referenceid = substr(md5('plc_coa_' . $userId . '_' . $lender), 0, 22);
                    $surname = (isset($user_data['surname']) ? $user_data['surname'] : $user_data['first_name']);
                    // Generate PDF
                    $pdf = PDF::loadView('ESIGN.index', ['user_data' => $user_data, 'current_date' => $currentDate, 'referId' => $referenceid]);
                    $pdf->setPaper('A4', 'p');
                    $pdf->save($path_pdf);
                    unset($pdf);
                    // ################################################################
                    $APP_ENV = env('APP_ENV');
                    if ($APP_ENV == 'live') {
                        $s3_basic_path = "live/";
                    } elseif ($APP_ENV == 'pre') {
                        $s3_basic_path = "pre/";
                    } else {
                        $s3_basic_path = "dev/";
                    }
                    $s3_rewrite_path = 'hlcc024-pdfs';
                    $s3_path = $s3_rewrite_path . $s3_basic_path . $s3_enc_file_name . ".pdf";
                    $s3_pdf = $this->saveFileIntoS3($s3_enc_file_name . ".pdf", $path_pdf, $s3_basic_path);
                    $s3_pdf = ($s3_pdf != false) ? $s3_path : "false";
                    //Store the PDF retrieval path
                    $path_s3_pdf_store = '';
                    $user_token = User::where('id', '=', $userId)->first('token');
                    $pdf_file_name = $s3_enc_file_name . ".pdf";
                    $bank_name = $lender;
                    $fbankname = str_replace(' ', '_', $bank_name);
                    $s3_retrieve_path = env('DOC_URL', 'https://dev.doc.homecreditclaims.co.uk/') . 'coa_pdfs/' . $surname . '_COA_' . $fbankname . "_" . $pdf_file_name . '?token=' . $user_token->token;
                    $s3_retrieve_path = ($s3_pdf != false) ? $s3_retrieve_path : false;
                    $path_s3_pdf_store = $s3_retrieve_path;
                    //save to lead DOC table
                    $file_name = 'COA_pdf_doc_data';
                    $strLogContent = "**************** Date: " . date('Y-m-d H:i:s') . "USER ID: " . $userId . "**********\r\n";
                    $userDetails = [
                        'userid' => $userId,
                        'user_sign_date' => date("d-m-Y", strtotime($user_data->user_signature_created)),
                        's3_path' => '',
                        'Lender' => $lender
                    ];
                    $log_data = $strLogContent . "user details:   " . json_encode(@$userDetails) . "***************\r\n";
                    $this->logRepoObject->writeLog($file_name, $log_data);
                    $bank_loa_pdf_data = json_encode($path_s3_pdf_store, JSON_UNESCAPED_SLASHES);
                    return $s3_retrieve_path;
                } catch (\Exception $exception) {
                    $msg = "E Sign pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                }
            } else {
                Log::warning("condition change add");
            }
        } else {
            if ($isQualified != 1) {
                Log::warning("Unqualified Lead");
                return "Unqualified Lead";
            }
            if ($canCreatePdf != 1) {
                Log::warning("Incomplete Lead");
                return "Incomplete Lead";
            }
        }
    }
    /**
     * Generate Statement Truth PDF
     * @param $userId integer
     */
    public function generateStatementPDF($userId,$customerId=null,$bankData = [],$flag = null){
        $customerId = isset($customerId)?$customerId:'';
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        // dd($isQualified);
        
        if ($isQualified == 1 ) {
           

                $pdf_folder_name = "statement_pdfs";
                $APP_ENV = env('APP_ENV');
                if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                    $basic_storage_path = config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                } else {
                    $basic_storage_path = config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                }

                $awsBucket = env('AWS_BUCKET');
                $pdf_folder_name = "statement_pdfs";
                $s3_enc_file_name = substr(md5('STAT_' . $userId), 0, 15);
                $path_pdf_store = $s3_enc_file_name . ".pdf";
                $path_pdf_store = $userId."_STAT_".$s3_enc_file_name . ".pdf";
                $userRep = new UserRepository();

                $currentDate = Carbon::now()->toDateString();
                $currentDate = date("d-m-Y", strtotime($currentDate));

                $user_data = $user_data_log = $userRep->userPdfDetails($userId);
                if (isset($user_data['s3_file_path']) && !empty($user_data['s3_file_path'])) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data['s3_file_path']);
                     $user_data['s3_file_path'] = $signatureXml->signature[0][0];
                }
               $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store.'?token='.$user_data['token'];
               echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
               //$pdf = PDF::loadView('PDFLOA.index', ['user_data' => $user_data, 'current_date' => $currentDate]);
               $refNo = isset($customerId) && $customerId != null ? $customerId : $userId;
               $pdf = PDF::loadView('PDFLOA.statement', ['user_data' => $user_data, 'current_date' => $currentDate,
                                                        'user_id'=>$userId,'customer_id'=>$customerId,'flag'=>true, 'refNo' => $refNo]);
                $pdf->setPaper('A4', 'p');
                $pdfOutput = $pdf->output();
                $xmlFileName = $userId."_STAT_".$s3_enc_file_name."_base".'.xml';
                $xmlFileData = $this->covertPDFToXml($pdfOutput);
                //Generate XML
                $hook = "_STAT_";
                $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,null);
                /**
                 * create data for xml
                 */
               /* $xmlFile = explode("/", $xmlFileName );
                  if(isset($xmlFile)){
                      $xmlFileName = $xmlFile[sizeof($xmlFile)-1];
                  }*/
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["statement_of_truth_pdf" => $storage_upload_path]
                );
                $xmlDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["statement_of_truth_pdf_base" =>  $s3_xml_path ]
                );
                //return $pdf->download($path_pdf_store.'.pdf');
                //==========================================S3 PUSH===============================================================
                $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfOutput, $pdf_folder_name,$pdf_folder_name);                        
                unset($pdf);


            

                

        }

        return;
    }
    /**
     * generate Preciew PDF
     * @param $userId integer
     */
    public function generatePreviewPDF($userId,$customerId=null,$bankData = [],$flag = null){
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        
        if ($isQualified == 1 ) {
            echo "INSIDE IS QUALIFIED";
                $APP_ENV = env('APP_ENV');
                if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                    $basic_storage_path = config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                } else {
                    $basic_storage_path = config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                }

                $awsBucket = env('AWS_BUCKET');
                $pdf_folder_name = "preview_pdfs";
                $s3_enc_file_name = substr(md5('preview_pdfs' . $userId), 0, 15);
               
                $path_pdf_store = $s3_enc_file_name . ".pdf";
                $path_pdf_store = $userId."_PREV_".$s3_enc_file_name . ".pdf";
               
                $userRep = new UserRepository();

                $currentDate = Carbon::now()->toDateString();
                $currentDate = date("d-m-Y", strtotime($currentDate));
                $user_data = $user_data_log = $userRep->userPdfDetails($userId);
                // dd($user_data);
                $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store.'?token='.$user_data['token'];
                echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
                if (isset($user_data['s3_file_path']) && !empty($user_data['s3_file_path'])) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data['s3_file_path']);
                    //dd($signatureXml);
                     $user_data['s3_file_path'] = $signatureXml->signature[0][0];
                }

                $refNo = isset($customerId) && $customerId != null ? $customerId : $userId;
                $pdf = PDF::loadView('PDFLOA.review', ['user_data' => $user_data, 'current_date' => $currentDate,'user_id'=>$userId,'customer_id'=>$customerId,'flag'=>true, 'refNo' => $refNo]);
                $pdf->setPaper('A4', 'p');
                $pdfOutput = $pdf->output();

                $xmlFileName         = $userId."_PREV_".$s3_enc_file_name."_base".'.xml';
                $xmlFileData    = $this->covertPDFToXml($pdfOutput);
                //$path_xml = $pdf_storage_path . $path_pdf_store;
                $hook = "_PREV_";
                $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,null);
                /**
                 * create data for xml
                 */
                
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["witness_statement_pdf" => $storage_upload_path]
                );
                $leadDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["witness_statement_pdf_base" => $s3_xml_path]
                );

                //==========================================S3 PUSH===============================================================
                $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfOutput,$pdf_folder_name);
                unset($pdf);

        }
        return;

    }
    /**
     * generate Authenticity PDF
     * @param $userId integer
     */
    public function generateAuthenticityPDF($userId, $customerId=null,$bankData = [],$flag = null,$userBank = null,$key=null){
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        if ($isQualified == 1 ) {
                $userRep = new UserRepository();
                
                $currentDate = Carbon::now()->toDateString();
                $currentDate = date("Y-m-d H:i:s", time());
                $user_data =  $userRep->getCompleteUserDetails($userId);
                //print_r($user_data->signature_image);die();
                if (isset($user_data->signature_image) && !empty($user_data->signature_image)) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data->signature_image);
                    //dd($signatureXml);
                    $user_data->signature_image = $signatureXml->signature[0][0];
                }
                $common_fn = new CommonFunctionsRepository();
                $strIp        =   $common_fn->get_client_ip();
                $user_data->signature_created_at = date("Y-m-d H:i:s", strtotime($user_data->signature_created_at));
                $user_data->ip_address = $strIp;
                $user_address_data = $userRep->userAddressDetails($userId); 
               
                //$user_data = $user_data_log = $userRep->getCompleteUserDetails($userId);
                //echo $user_data->user_signature;
                $userBanks = $userRep->getUserDetailsFromUserId($userId);
                 // S3 Push
                 $APP_ENV = env('APP_ENV');
                 if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                     $s3_basic_path = "pdf/xml/live/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                 } else {
                     $s3_basic_path = "pdf/xml/dev/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                 } 
                $payLoadData = [];
                $finalPayLoadPath = [];
                $payLoadXmlData = [];
                $finalPayLoadXmlData = [];
                $awsBucket = env('AWS_BUCKET');
                $pdf_folder_name = "coa_pdfs";
                foreach($userBanks as $key=>$userBank){
                $s3_enc_file_name = substr(md5('coa_pdfs' . $userId.$key), 0, 15);
                $banknameSpecialCharachters = str_replace(array( '(', ')' ), '', $userBank['bank_name']);
                $bankNameRemoveSpace =  preg_replace('!\s+!', '_', $banknameSpecialCharachters);
                $path_pdf_store = $s3_enc_file_name.$key . ".pdf";
                $path_pdf_store = $userId."_COA_".$bankNameRemoveSpace ."_".$s3_enc_file_name.$key . ".pdf";
                $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store."?token=".$user_data->token;
                echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
                $payLoadData[$userBank['bank_id']] = $storage_upload_path;
                array_push($finalPayLoadPath, $payLoadData);
                $refNo = isset($customerId) && $customerId != null ? $customerId : $s3_enc_file_name;
                $pdf = PDF::loadView('PDFLOA.authenticity', ['user_data' => $user_data, 'current_date' => $currentDate,'s3_enc_file_name' =>$s3_enc_file_name,'user_id'=>$userId, 'refNo' => $refNo]);      
                $pdf->setPaper('A4', 'p');
                $pdfOutput = $pdf->output();
                $xmlFileName         = $userId."_COA_".$bankNameRemoveSpace.'.xml';
                $xmlFileData    = $this->covertPDFToXml($pdfOutput);

                /**
                 * create data for xml
                 */
                $hook = "_COA_";
                   $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,$bankNameRemoveSpace);
                    $payLoadXmlData[$userBank['bank_id']] = $s3_xml_path;
                array_push($finalPayLoadXmlData, $payLoadXmlData);
                //Save to s3

                $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfOutput,$pdf_folder_name);  
                }
                $bankPayload = $this->getLeadDocPayLoad($userId,$payLoadData,2);
                $bankXmlPayLoad = $this->getLeadXmlPayLoad($userId,$payLoadXmlData,1);
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["pdf_file" =>$bankPayload]
                );
                $xmlDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["pdf_file_base" => $bankXmlPayLoad]
                );
               // die();
                //$pdf = PDF::loadView('PDFLOA.index', ['user_data' => $user_data, 'current_date' => $currentDate]);
               
               
                unset($pdf);

        }

       return; 
    }

    /**
     * generate Engagement PDF
     * @param $userId integer
     */
   /* public function generateEngagementPDF($userId,$customerId=null,$bankData = [],$flag = null){       
        
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        
        if ($isQualified == 1 ) {
           // try{
                
                $userRep = new UserRepository();
                
                $currentDate = Carbon::now()->toDateString();
                $todayDate = date("d-m-Y", strtotime($currentDate));

                $user_data =  $userRep->getCompleteUserDetails($userId);

                //print_r($user_data->signature_image);die();
                if (isset($user_data->signature_image) && !empty($user_data->signature_image)) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data->signature_image);
                    //dd($signatureXml);
                    $user_data->signature_image = $signatureXml->signature[0][0];
                }
                $common_fn = new CommonFunctionsRepository();
                $strIp        =   $common_fn->get_client_ip();
                $user_data->signature_created_at = $currentDate = date("d-m-Y", strtotime($user_data->signature_created_at));
                $user_data->ip_address = $strIp;
                $user_address_data = $userRep->userAddressDetails($userId); 
                $user_pdf_data = $user_data_log = $userRep->userPdfDetails($userId);
                $userBanks = $userRep->getUserDetailsFromUserId($userId);
                 // S3 Push
                 $APP_ENV = env('APP_ENV');
                 if ($APP_ENV == 'live' || $APP_ENV == 'prod' || $APP_ENV == 'pre') {
                     $s3_basic_path = "live/live/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                 } else {
                     $s3_basic_path = "pdf/xml/dev/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                 }             
                 $payLoadData = [];
                 $finalPayLoadPath = [];
                 $payLoadXmlData = [];
                 $finalPayLoadXmlData = [];
                 $awsBucket = env('AWS_BUCKET');
                 $pdf_folder_name = "loa_pdfs";
                foreach($userBanks as $key=>$userBank){
                    $s3_enc_file_name = substr(md5('loa_pdfs' . $userId.$key), 0, 15);
                    $claimId = $bankData[$userBank['bank_id']]??$userId;
                    $banknameSpecialCharachters = str_replace(array( '(', ')' ), '', $userBank['bank_name']);
                    $bankNameRemoveSpace =  preg_replace('!\s+!', '_', $banknameSpecialCharachters);
                   // $path_pdf_store = $s3_enc_file_name.$key . ".pdf";
                   $path_pdf_store = $userId."_LOA_".$bankNameRemoveSpace ."_".$s3_enc_file_name.$key . ".pdf";
                   $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store."?token=".$user_data->token;
                   echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
                   $payLoadData = [
                        $userBank['bank_id']=>$storage_upload_path
                    ];
                    array_push($finalPayLoadPath,$payLoadData);
                // $pdf = PDF::loadView('PDFLOA.authenticity', ['user_data' => $user_data, 'current_date' => $currentDate]);               
                $pdf = PDF::loadView('PDFLOA.engagement', ['user_pdf_data' => $user_pdf_data ,'user_address_data' => $user_address_data,'user_data'=>$user_data,
                                                            'todayDate'=>$todayDate,'user_id'=>$claimId,'customer_id'=>$customerId,'flag'=>true,'userBank'=>$userBank]);
                $pdf->setPaper('A4', 'p');
                $pdfoutput = $pdf->output();
                $xmlFileName         = $awsBucket."/base/dev/".$userId."_LOA_".$bankNameRemoveSpace.'.xml';
                $xmlFileData    = $this->covertPDFToXml($pdfoutput);
                //$path_xml = $pdf_storage_path . $path_pdf_store;

                /**
                 * create data for xml
                 */
                /*$xmlFile = explode("/", $xmlFileName );
                  if(isset($xmlFile)){
                      $xmlFileName = $xmlFile[sizeof($xmlFile)-1];
                  }
                  $hook = "_LOA_";
                  $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,$bankNameRemoveSpace);
                  $payLoadXmlData = [
                    $userBank['bank_id']=>$s3_xml_path
                    ];
                array_push($finalPayLoadXmlData, $payLoadXmlData);
                /**
                 * create data for xml
                 */
                //Save to s3
                
               /* $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfoutput,$pdf_folder_name); 
                }
                $bankPayload = $this->getLeadDocPayLoad($userId,$finalPayLoadPath,1);
                $bankXmlPayLoad = $this->getLeadXmlPayLoad($userId,$finalPayLoadXmlData,1);
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["bank_loa_pdf_files" => $this->formatDataJson($bankPayload)]
                );
                $xmlDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["bank_loa_pdf_files_base" => $this->formatDataJson($bankXmlPayLoad)]
                );               
                unset($pdf);


           /* }catch (\Exception $exception) {
                    $msg = "pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                } */

       /* }
        return;

    }*/

    public function generateEngagementPDF($userId,$customerId=null,$bankData = [],$flag = null,$userBank = null,$key=null){       
        
        $isQualified = $this->userInterface->isQualifiedBank($userId);
        
        if ($isQualified == 1 ) {
           // try{
                
                $userRep = new UserRepository();
                
                $currentDate = Carbon::now()->toDateString();
                $todayDate = date("d-m-Y", strtotime($currentDate));

                $user_data =  $userRep->getCompleteUserDetails($userId);

                //print_r($user_data->signature_image);die();
                if (isset($user_data->signature_image) && !empty($user_data->signature_image)) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data->signature_image);
                    //dd($signatureXml);
                    $user_data->signature_image = $signatureXml->signature[0][0];
                }
                
                $common_fn = new CommonFunctionsRepository();
                $strIp = $common_fn->get_client_ip();
                $user_data->signature_created_at = $currentDate = date("d-m-Y", strtotime($user_data->signature_created_at));
                $user_data->ip_address = $strIp;
                $user_address_data = $userRep->userAddressDetails($userId); 
                $user_pdf_data = $user_data_log = $userRep->userPdfDetails($userId);
                $userBanks = $userRep->getUserDetailsFromUserId($userId);
                 // S3 Push
                 $APP_ENV = env('APP_ENV');
                 if ($APP_ENV == 'live' || $APP_ENV == 'prod' || $APP_ENV == 'pre') {
                     $s3_basic_path = "live/live/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                 } else {
                     $s3_basic_path = "pdf/xml/dev/";
                     $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                 }             
                 $payLoadData = [];
                 $finalPayLoadPath = [];
                 $payLoadXmlData = [];
                 $finalPayLoadXmlData = [];
                 $awsBucket = env('AWS_BUCKET');
                 $pdf_folder_name = "loa_pdfs";
               // foreach($userBanks as $key=>$userBank){
                    $s3_enc_file_name = substr(md5('loa_pdfs' . $userId.$key), 0, 15);
                    $claimId = $bankData[$userBank['bank_id']]??$userId;
                    $banknameSpecialCharachters = str_replace(array( '(', ')' ), '', $userBank['bank_name']);
                    $bankNameRemoveSpace =  preg_replace('!\s+!', '_', $banknameSpecialCharachters);
                   // $path_pdf_store = $s3_enc_file_name.$key . ".pdf";
                   $path_pdf_store = $userId."_LOA_".$bankNameRemoveSpace ."_".$s3_enc_file_name.$key . ".pdf";
                   $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store."?token=".$user_data->token;
                   echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
                   
                   $payLoadData[$userBank['bank_id']] = $storage_upload_path;
                    array_push($finalPayLoadPath,$payLoadData);
                // $pdf = PDF::loadView('PDFLOA.authenticity', ['user_data' => $user_data, 'current_date' => $currentDate]);               
                $pdf = PDF::loadView('PDFLOA.engagement', ['user_pdf_data' => $user_pdf_data ,'user_address_data' => $user_address_data,'user_data'=>$user_data,
                                                            'todayDate'=>$todayDate,'user_id'=>$claimId,'customer_id'=>$customerId,'flag'=>true,'userBank'=>$userBank]);
                $pdf->setPaper('A4', 'p');
                $pdfoutput = $pdf->output();
                $xmlFileName         = $awsBucket."/base/dev/".$userId."_LOA_".$bankNameRemoveSpace.'.xml';
                $xmlFileData    = $this->covertPDFToXml($pdfoutput);
                //$path_xml = $pdf_storage_path . $path_pdf_store;

                /**
                 * create data for xml
                 */
                $xmlFile = explode("/", $xmlFileName );
                  if(isset($xmlFile)){
                      $xmlFileName = $xmlFile[sizeof($xmlFile)-1];
                  }
                  $hook = "_LOA_";
                  $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,$bankNameRemoveSpace);
                  $payLoadXmlData[$userBank['bank_id']] = $s3_xml_path;
                array_push($finalPayLoadXmlData, $payLoadXmlData);
                /**
                 * create data for xml
                 */
                //Save to s3
                
                $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfoutput,$pdf_folder_name); 
               // }
                $bankPayload = $this->getLeadDocPayLoad($userId,$payLoadData,1);
                $bankXmlPayLoad = $this->getLeadXmlPayLoad($userId,$payLoadXmlData,1);
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["bank_loa_pdf_files" => $bankPayload]
                );
                $xmlDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["bank_loa_pdf_files_base" => $bankXmlPayLoad]
                );               
                unset($pdf);


           /* }catch (\Exception $exception) {
                    $msg = "pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                } */

        }
        return;

    }


  
    /**
    /**
     * generate Questionnaire PDF
     * @param $userId integer
     */
    public function generateQuestionnairePDF($userId,$customerId=null,$bankData = [],$flag = null){
        $isQualified = $this->userInterface->isQualifiedBank($userId); 
        if ($isQualified == 1 ) {
            //try{
                $APP_ENV = env('APP_ENV');
                if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                    $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH_LIVE');
                } else {
                    $basic_storage_path =config('constants.PLEVIN_PDF_STORAGE_BASE_PATH');
                }    

                $awsBucket = env('AWS_BUCKET');
                $pdf_folder_name = "questionnaire_pdfs";
                $userRep = new UserRepository();
                
                $currentDate = Carbon::now()->toDateString();
                $currentDate = date("d-m-Y", strtotime($currentDate));
                $user_data =  $userRep->getCompleteUserDetails($userId);            
                if (isset($user_data->signature_image) && !empty($user_data->signature_image)) {
                    $signatureXml = $this->getXmlObjectFromS3($user_data->signature_image);
                    //dd($signatureXml);
                    $user_data->signature_image  = $signatureXml->signature[0][0];
                }
                $common_fn = new CommonFunctionsRepository();
                $strIp        =   $common_fn->get_client_ip();
                $user_data->signature_created_at = $currentDate = date("d-m-Y", strtotime($user_data->signature_created_at));
                $user_data->ip_address = $strIp;
                $userQuetionData = $userRep->userQuestionnaireAnswers($userId); 
                $userQuestions = $userRep->getUserQuestionAnswerArray($userId);               
              
                //    dd($userQuetionData); 
                $s3_enc_file_name = substr(md5('ques_pdfs' . $userId), 0, 15);
                $path_pdf_store = $s3_enc_file_name. ".pdf";
                $path_pdf_store = $userId."_QUE_".$s3_enc_file_name . ".pdf";
                $storage_upload_path = $basic_storage_path."/".$pdf_folder_name."/".$path_pdf_store."?token=".$user_data->token;
                $refNo = isset($customerId) && $customerId != null ? $customerId : $userId;
 
                echo  "===============Storage Upload path==============". $storage_upload_path;echo "\n";
                $pdf = PDF::loadView('PDFLOA.questionnaire_pdf', ['user_data' => $user_data,'user_questions'=>$userQuestions,'user_questions_data'=>$userQuetionData, 
                                                                'current_date' => $currentDate,'user_id'=>$userId,'customer_id'=>$customerId,'flag'=>true, 'refNo' => $refNo]);
                $pdf->setPaper('A4', 'p');
               $pdfoutput =  $pdf->output();
               
                $xmlFileName         = $userId.'.xml';
                $xmlFileData    = $this->covertPDFToXml($pdfoutput);
                /**
                 * create data for xml
                 */
                $hook = "_QUE_";
                $s3_xml_path = $this->generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,null);  
                $leadDocData = LeadDoc::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["questionnaire_pdf_files" => $storage_upload_path]
                );
                $xmlDocData = LeadDocBase::updateOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    ["questionnaire_pdf_files_base" =>  $s3_xml_path]
                );
                $s3_pdf = $this->passDatatoS3($path_pdf_store,$pdfoutput,$pdf_folder_name); 
                unset($pdf);
           /* }catch (\Exception $exception) {
                    $msg = "pdf generation failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
                    Log::warning($msg);
                }*/

        }
        return;

    }
    /**
     * Create array for lead_doc table
     * @param integer $userId
     * @param  array $payLoadArray - Existing data Array for appending with new array
     * @param  integer $flag - Which column data need to consider for insertion
     */
    public function getLeadDocPayLoad($userId,$payLoadArray,$flag){
        if (LeadDoc::where('user_id', '=', $userId)->exists()) {
            $leadDocs = LeadDoc::where('user_id', '=', $userId)->first();
                if($flag == 1){
                    $leadDocsColumnValue = $leadDocs->bank_loa_pdf_files;
                }
                else if($flag == 2){
                
                    $leadDocsColumnValue = $leadDocs->pdf_file;
                 
                }
                else if($flag == 3){
                    $leadDocsColumnValue = $leadDocs->coa_pdf_files;
                }
                if(isset($leadDocsColumnValue) && !empty($leadDocsColumnValue))
                $leadDocsArray 	= json_decode($leadDocsColumnValue, TRUE);
                else
                $leadDocsArray = [];

                
                //print_r($leadDocsArray);die();
                // user found
             }
             else{
                $leadDocsArray  = [];
             }

             foreach($payLoadArray as $key => $value) {
                $leadDocsArray[$key] = $value;
             }

             $deJsonBankPayload = json_encode($leadDocsArray, JSON_UNESCAPED_SLASHES);
             return  $deJsonBankPayload ;
    }
    /**
     * Create array for leads_xml table
     * @param integer $userId 
     * @param  array $payLoadArray - Existing data Array for appending with new array
     * @param  integer $flag - Which column data need to consider for insertion
     */
    public function getLeadXmlPayLoad($userId,$payLoadArray,$flag){
        if (LeadDocBase::where('user_id', '=', $userId)->exists()) {
            $xmlDocs = LeadDocBase::where('user_id', '=', $userId)->first();
                if($flag == 1){
                    $xmlDocsColumnValue = $xmlDocs->bank_loa_pdf_files_base;
                }
                else if($flag == 2){
                
                    $xmlDocsColumnValue = $xmlDocs->pdf_file_base;
                 
                }
                else if($flag == 3){
                    $xmlDocsColumnValue = $xmlDocs->coa_pdf_files;
                }
                if(isset($xmlDocsColumnValue) && !empty($xmlDocsColumnValue))
                $xmlDocsArray 	= json_decode($xmlDocsColumnValue, TRUE);
                else
                $xmlDocsArray = [];

                
                //print_r($leadDocsArray);die();
                // user found
             }
             else{
                $xmlDocsArray  = [];
               
             }

             foreach($payLoadArray as $key => $value) {
                $xmlDocsArray[$key] = $value;
             }

             $deJsonBankPayload = json_encode($xmlDocsArray, JSON_UNESCAPED_SLASHES);
             return  $deJsonBankPayload ;
    }

    /**
     * Conert pdf to xml
     */
    public function covertPDFToXml($pathPdf)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ClientDetails></ClientDetails>');
        $covertedFile = base64_encode($pathPdf);

        $xml->addAttribute('version', '1.0');
        $xml->addChild('createpdf', $covertedFile);
        
        // $xmlFile = $filename;
        // $xml->saveXML($xmlFile);
        $xml_content = $xml->asXML();

        return $xml_content;
    }

    /**
     * Pass data to S3
     * @param string $path_pd_store - filename
     * @param string $path_pdf - The path of teh pdf where pdf stored
     */
    public function passDatatoS3($path_pdf_store,$path_pdf, $pdf_folder_name ){
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'live' || $APP_ENV == 'prod') {
            $s3_basic_path = "live/";
        } elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pre/";
        }
        else {
            $s3_basic_path = "dev/";
        }

        $s3_pdf = $this->saveFileIntoS3($path_pdf_store, $path_pdf, $s3_basic_path);
        return $s3_pdf;

    }
    /**
     * Create directory for pdf upload if directory not exist
     */
    public function createDirectoryNotExist($userId,$pdf_folder_name, $basic_storage_path,$awsBucket,$s3_enc_file_name){
        if (env('APP_ENV') == 'local') {
            $pdf_storage_path =  "/app/uploads/".$awsBucket."/dev"."/".$pdf_folder_name."/";
            $xmlStoragePath = storage_path()."/app/uploads/".$awsBucket."/base/dev"."/"; 
           if(!Storage::disk('public')->exists($pdf_storage_path)){
            Storage::makeDirectory("uploads/".$awsBucket."/dev"."/".$pdf_folder_name,0777, true);
            
            }
            if(!Storage::disk('public')->exists($xmlStoragePath)){
                Storage::makeDirectory("uploads/".$awsBucket."/base/dev"."/",0777, true);
                
            }
           //$pdf_storage_path = storage_path() . '/app/uploads/pdf/pdf/';
           $pdf_storage_path =   storage_path()."/app/uploads/".$awsBucket."/dev"."/".$pdf_folder_name."/";
           $xmlStoragePath = storage_path()."/app/uploads/".$awsBucket."/base/dev"."/"; 
           
            
            //storage_path() . '/app/uploads/pdf/pdf/';
        } else {
            $pdf_storage_path =  "/app/uploads/".$awsBucket."/live"."/".$pdf_folder_name."/";
            $xmlStoragePath = storage_path()."/app/uploads/".$awsBucket."base/live"."/"; 
                if(!Storage::disk('public')->exists($pdf_storage_path)){
                Storage::makeDirectory("uploads/".$awsBucket."/live"."/".$pdf_folder_name,0777, true);
                
                }
                if(!Storage::disk('public')->exists($xmlStoragePath)){
                    Storage::makeDirectory("uploads/".$awsBucket."/base/live"."/",0777, true);
                 }
            $pdf_storage_path =   storage_path()."/app/uploads/".$awsBucket."/live"."/".$pdf_folder_name."/";
            $xmlStoragePath = storage_path()."/app/uploads/".$awsBucket."/base/live"."/"; 
        }

    }

    public function saveFileDirectIntoS3($fileName, $content, $folderPath)
    {
        ## S3 BUCKET #############################
        //$s3BucketName = env('AWS_BUCKET');
        //$s3BucketRegion = env('AWS_DEFAULT_REGION');
        //$s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        //$s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');

        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        ## S3 BUCKET #############################

        $s3 = new Aws\S3\S3Client([
            'region'  => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key'    => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);

        $result = $s3->putObject([
            'Bucket'        => $s3BucketName,
            'Key'           => $folderPath.''.$fileName,
            'Body' => $content,
            'ContentDisposition'=>"inline",
            'ContentType'=>"application/vnd.adobe.pdfxml",
            //'ACL'           => 'public-read'
        ]);

        $arrResult = (array)$result;

        foreach ($arrResult as $key => $arrValue) {
            if(preg_replace('/[^\da-z]/i' , "" , $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200"){
                return $arrValue["@metadata"]["effectiveUri"];
            }
        }
    }

    public function generateXmlFile($userId,$awsBucket,$s3_enc_file_name,$xmlFileData,$hook,$bankNameRemoveSpace){
        $bankName = isset($bankNameRemoveSpace)?$bankNameRemoveSpace:'';
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'live' || $APP_ENV == 'prod') {
            $s3_basic_path = "live/base/";
            if(isset($bankName) && !empty($bankName)){
                $saveFilePath = "uploads/".$awsBucket."/base/live"."/".$userId.$hook.$bankName."_base".'.xml';
                $s3FilePath = $userId.$hook.$bankName."_base".'.xml';
            }
            else{
                $saveFilePath = "uploads/".$awsBucket."/base/live"."/".$userId.$hook.$s3_enc_file_name."_base".'.xml';
                $s3FilePath = $userId.$hook.$s3_enc_file_name."_base".'.xml';
            }
           
        } elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pre/base/";
            $s3FilePath = $userId.$hook.$s3_enc_file_name."_base".'.xml';
           
        }
        else {
            $s3_basic_path = "dev/base/";
            if(isset($bankName) && !empty($bankName)){
            $saveFilePath = "uploads/".$awsBucket."/base/dev"."/".$userId.$hook.$bankName."_base".'.xml';
            $s3FilePath = $userId.$hook.$bankName."_base".'.xml';
            }
            else{
            $saveFilePath = "uploads/".$awsBucket."/base/dev"."/".$userId.$hook.$s3_enc_file_name."_base".'.xml';
            $s3FilePath = $userId.$hook.$s3_enc_file_name."_base".'.xml';
            }
            
        }
        //Storage::put($saveFilePath, $xmlFileData);
        $s3_xml_path = $this->saveFileDirectIntoS3($s3FilePath, $xmlFileData, $s3_basic_path);
        return $s3_xml_path;
    }

    /**
     * Convert aws file path
     */
    public function getXmlObjectFromS3($awsurl){
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        $s3 = new \Aws\S3\S3Client([
            'region' => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);

        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'local' || $APP_ENV == 'dev') {
            $s3_basic_path = "pba/signature/dev";
        } elseif($APP_ENV == 'live' || $APP_ENV == 'prod'){
            $s3_basic_path = "pba/signature/live";
        }
        elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pba/signature/pre";
        }
        $result = $s3->getObject(array(
            'Bucket' => $s3BucketName,
            
            'Key'    => $s3_basic_path.'/'.basename($awsurl)
        ));

        $result['Body']->rewind();

        $chunks = [];
        while ($rd = $result['Body']->read(1024)) {
            $chunks[] = $rd;
        }

        return simplexml_load_string((implode('', $chunks)));
        //dd(simplexml_load_string((implode('', $chunks))));
    }

    public function formatDataJson($data) 
    {
        if (!$data) {
            return $data;
        }

        $data = json_decode($data, true);
        $main = [];
        foreach($data as $key => $value) {
            foreach($value as $k => $v) {
                $main[$k] = $v;
            }
        }

        return json_encode($main, JSON_UNESCAPED_SLASHES);
    }

}
