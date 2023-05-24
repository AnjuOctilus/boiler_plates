<?php


namespace App\Repositories\DataIngestion;


use App\Models\User;
use App\Models\SplitUuid;
use App\Models\Signature;
use App\Models\SignatureDetails;
use App\Models\UserAddressDetails;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\SignatureDataIngestionInterface;
use App\Repositories\Interfaces\VisitorInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\Interfaces\LogInterface;
use Carbon\Carbon;
use App\Repositories\HistoryRepository;
use App\Models\UserExtraDetail;

/**
 * Class SignatureDataIngestionRepository
 * 
 * @package App\Repositories\DataIngestion
 */
class SignatureDataIngestionRepository implements SignatureDataIngestionInterface
{
    /**
     * SignatureDataIngestionDataIngestionRepository constructor.
     *
     * @param VisitorInterface $visitorInterface
     */
    public function __construct(VisitorInterface $visitorInterface, LPDataIngestionInterface $LPDataIngestionInterface,
        LiveSessionInterface $liveSessionInterface,PixelFireInterface $pixelFireInterface,UserInterface $user_repo,LogInterface $logRepo)
    {
        $this->visitorInterface = $visitorInterface;
        $this->LPDataIngestionInterface = $LPDataIngestionInterface;
        $this->liveSessionInterface = $liveSessionInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->user_repo = $user_repo;
        $this->logInterface   = $logRepo;
    }

    /**
     * Store
     *
     * @param $data
     * @param $visitorParameters
     */
    public function store($signatureData, $visitorParameters,$previousData,$formData,$visitorData,$queryString)
    {
        
        // TODO: Implement store() method.
        $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . "\n Signature Page - Visitors Parameters: " . json_encode( $visitorParameters ) . '  \n';
        $logWrite   = $this->logInterface->writeLog( '-getvisitorsParameters', $strFileContent);
        $visitor = SplitUuid::where(['uuid' => $visitorParameters['uuid']])->first();
        $visitorId = isset($visitor->visitor_id) ? $visitor->visitor_id:null;
        $user = (isset($visitorId) && !empty($visitorId)) ? User::where(['visitor_id' => $visitorId])->first() :null;
        if(isset($user) && !empty($user)){
            $userId = $user->id;
        } else{
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');
            $data['currentTime'] = $currentTime;

            $arrResponse = $this->LPDataIngestionInterface->store($formData,$queryString, $visitorParameters,$currentTime,$formData['page_name'],$visitorData);
            //$user = User::where(['user_uuid' => $visitorParameters['uuid']])->first();
            $visitor = SplitUuid::where(['uuid' => $visitorParameters['uuid']])->first();
            $user = User::where(['visitor_id' => $visitor->visitor_id])->first();
            $userId = $user->id;
        }

        $status = 'live';
        $type                       = 'digital';
        $signatureResult            = Signature::where('user_id', '=', $userId)
                                        ->first();

        if (!empty($signatureResult)) {
            $signatureResult->s3_file_path   = $signatureData;
            $signatureResult->status            = 1;
            $signatureResult->type              = $type;
            $signatureResult->update();
            $signature_id                          = $signatureResult->id;
            //$signatureResult->previous_name = isset($previousData['previous_name'])?$previousData['previous_name']:'test';
            
        } else {
            $objSignature                       = new Signature;
            $objSignature->user_id              = $userId;
            $objSignature->bank_id              = 0;
            $objSignature->s3_file_path      = $signatureData;
            $objSignature->status               = 1;
            $objSignature->type                 = $type;
            $objSignature->save();
            $signature_id                       = $objSignature->id;
            //$objSignature->previous_name       = isset($previousData['previous_name'])?$previousData['previous_name']:'testme';
        }

        if (isset($signature_id)) {
            $this->HistoryRepo =  new HistoryRepository();
            $this->HistoryRepo->insertFollowupLiveHistory(array(
                        "user_id" =>$userId,
                        "type" =>'signature',
                        "type_id" =>0,
                        "source" =>'live',
                        "value" =>'1',
                        "post_crm" =>0,
                    )
                );
            $this->liveSessionInterface->createUserMilestoneStats(array(
                    "user_id" => $userId,
                    "source" => $status,
                    "user_signature" => 1,
                )
            );
            
            $this->pixelFireInterface->SetPixelFireStatus("SN", $visitorId, $userId);
            $this->liveSessionInterface->completedStatusUpdate($userId, 'live');
            
            if (isset($previousData['previous_address_data']) && $previousData['previous_address_data'] != '') {
                $previousAddress = $previousData['previous_address_data'];
                //die();
                if (is_iterable($previousAddress)) {
                    $addrTypeInc = 0;
                    foreach($previousAddress as $address) {
                        ++$addrTypeInc;
                        $userAddressArr = [
                            'user_id' => $userId,
                            'address_type' => $addrTypeInc,
                            'postcode' => $address['postcode'],
                            'address_line1' => $address['line_1'],
                            'address_line2' => $address['line_2'],
                            'address_line3' => $address['line_3'],
                            'address_line4' => $address['line_4'],
                            'town' => $address['town'],
                            'county' => $address['county'],
                            'country' => $address['country'],
                            'locality' => $address['locality'],
                            'district' => $address['district'],
                            'vendor' => $address['vendor'],
                            
                            'previous_name' => isset($previousData['previous_name'])?$previousData['previous_name']:'',
                            'previous_post_code' => $address['postcode'],
                            'address_id' => $previousData['previous_address'],
                            'previous_address' => $previousData['previous_address'],
                            'is_manual' => 1
                        ];

                        $IntUserAddressDetails = UserAddressDetails::insertGetId($userAddressArr);
                    }
                }

                $signatureData = Signature::updateOrCreate(
                    ['user_id' => $userId],
                    ["previous_name" =>  isset($previousData['previous_name'])?$previousData['previous_name']:'',
                    ]
                );
                //Signature::where('user_id',$userId)->update($previousData);
               // UserExtraDetail::where('user_id', $userId)->update($previousData);
            }

        }
    }
}