<?php


namespace App\Repositories\DataIngestion;


use App\Models\User;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\PixelFireInterface;
// use App\Repositories\Interfaces\PreviousDetailsInterface;
use App\Repositories\Interfaces\SignatureDataIngestionInterface;
use App\Repositories\Interfaces\VisitorInterface;
use App\Repositories\Interfaces\HistoryInterface;
use Carbon\Carbon;
use App\Jobs\PostLeadsToCake;
use App\Repositories\Interfaces\LogInterface;
use App\Repositories\Interfaces\UserDocumentsDataIngestionInterface;
use App\Repositories\Interfaces\UserDocumentsInterface;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Jobs\PDFGeneration;


class UserDocumentsDataIngestionRepository implements UserDocumentsDataIngestionInterface
{
    /**
     * SignatureDataIngestionDataIngestionRepository constructor.
     *
     * @param VisitorInterface $visitorInterface
     */
    public function __construct(VisitorInterface $visitorInterface, LPDataIngestionInterface $LPDataIngestionInterface,LiveSessionInterface $liveSessionInterface,PixelFireInterface $pixelFireInterface,CommonFunctionsInterface $commonFunctionsInterface,
                                HistoryInterface $historyInterface,LogInterface $logInterface,
                                UserDocumentsInterface $userDocumentsInterface

    )
    {
        $this->visitorInterface = $visitorInterface;
        $this->LPDataIngestionInterface = $LPDataIngestionInterface;
        // $this->previousDetailsInterface = $previousDetailsInterface;
        $this->liveSessionInterface = $liveSessionInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->historyInterface = $historyInterface;
        $this->logInterface     = $logInterface;
        $this->userDocumentsInterface     = $userDocumentsInterface;
        $this->commonFunctionsInterface = $commonFunctionsInterface;
    }

    /**
     * Store
     *
     * @param $data
     * @param $visitorParameters
     */
    public function store($userdocumentdata, $visitorParameters,$formData,$visitorData,$queryString)
    {
        // TODO: Implement store() method.
        $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . "\n User Documents Page - Visitors Parameters: " . json_encode( $visitorParameters ) . '  \n';
        $logWrite   = $this->logInterface->writeLog( '-getvisitorsParameters', $strFileContent);
        // $visitorId = $this->LPDataIngestionInterface->getVisitorId($visitorParameters);
        $user = User::where(['user_uuid' => $visitorParameters['uuid']])->first();

        if (isset($user)) {
            $userId = $user->id; 
            $visitorId = $user->visitor_id;
        } else {
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');
            $data['currentTime'] = $currentTime; 
            $user = User::where(['user_uuid' => $visitorParameters['uuid']])->first();
            $userId = $user->id; 
            $visitorId = $user->visitor_id;

        }
        $PreviousArray = array(
            'user_id' => $userId,
            'visitor_id' => @$visitorId,
            'userdocument_data' => $userdocumentdata,
            'bank_id' => '0',
        );
        $status = 'live';

        $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . "\n User Documents Page - Visitors Parameters: " . json_encode( $visitorParameters ) . '  \n'." VID: " .$visitorId." \n UID: " .$userId. '  \n';
        $logWrite   = $this->logInterface->writeLog( '-getuserDocu', $strFileContent);

        $intDocId = $this->userDocumentsInterface->sendUserDocuments($PreviousArray);

        if (isset($intDocId)) {

            $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . '  \n'." intDocId: " .$intDocId." \n UID: " .$userId. '  \n';
            $logWrite   = $this->logInterface->writeLog( '-getintDocIds', $strFileContent);

            $this->historyInterface->insertFollowupLiveHistory(array(
                        "user_id" =>$userId,
                        "type" =>'userdocument',
                        "type_id" =>0,
                        "source" =>'live',
                        "value" =>'1',
                        "post_crm" =>0,
                    )
                );
        }
    }
}