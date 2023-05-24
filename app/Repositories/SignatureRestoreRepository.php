<?php


namespace App\Repositories;

use App\Jobs\GenerateLOAPDF;
use App\Models\FollowupVisit;
use App\Models\User;
use App\Models\UserMilestoneStats;
use App\Repositories\Interfaces\FollowupDataIngestionInterface;
use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use App\Repositories\LogRepository;
use App\Repositories\PixelFireRepository;
use App\Repositories\VisitorRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\SignatureDataIngestionInterface;
use App\Repositories\Interfaces\SignatureRestoreInterface;
use App\Repositories\Interfaces\HistoryInterface;
use Illuminate\Support\Facades\Log;
use App\Models\Signature;
use App\Models\UserExtraDetail;
use App\Repositories\HistoryRepository;
use App\Models\FollowupHistories;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Repositories\UserRepository;

/**
 * Class FollowupDataIngestionRepository
 *
 * @package App\Repositories\DataIngestion
 */
class SignatureRestoreRepository implements SignatureRestoreInterface
{
    /**
     * FollowupDataIngestionRepository constructor.
     *
     * @param LiveSessionInterface $liveSessionInterface
     * @param PixelFireInterface $pixelFireInterface
     * @param HistoryInterface $historyInterface
     * @param S3SignatureDataIngestionInterface $s3SignatureDataIngestionInterface
     */
    public function __construct(
    LiveSessionInterface $liveSessionInterface,PixelFireInterface $pixelFireInterface,
    HistoryInterface $historyInterface,
    S3SignatureDataIngestionInterface $s3SignatureDataIngestionInterface)
    {
        $this->liveSessionInterface = $liveSessionInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->historyInterface = $historyInterface;
        $this->S3SignatureDataIngestionInterface = $s3SignatureDataIngestionInterface;
}
public function signatureStore($data)
    {$data = (object)$data;
        $signatureData = $data->signature_data;
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
     
            $userId = $data->user_id;
            $user = User::find($userId);
            $visitorId = $user->visitor_id;
        $s3_signatureData = $this->S3SignatureDataIngestionInterface->userS3SignatureStore($signatureData,$userId,'user');
        $type                       = 'digital';
        $signatureResult            = Signature::where('user_id', '=', $userId)
                                        ->first();

        if (!empty($signatureResult)) {
            $signatureResult->s3_file_path   = $s3_signatureData;
            $signatureResult->status            = 1;
            $signatureResult->type              = $type;
            $signatureResult->update();
            $signature_id                          = $signatureResult->id;
        } else {
            $objSignature                       = new Signature;
            $objSignature->user_id              = $userId;
            $objSignature->bank_id              = 0;
            $objSignature->s3_file_path     = $s3_signatureData;
            $objSignature->status               = 1;
            $objSignature->type                 = $type;
            $objSignature->save();
            $signature_id                          = $objSignature->id;
        }

       // $this->updateTYPixel($followupdata,$currentTime);
        $source =  'FLP';

        if ($signature_id) {
            $this->historyInterface->insertFollowupLiveHistory(array(
                        "user_id" =>$userId,
                        "type" =>'signature',
                        "type_id" =>0,
                        "source" =>'FLP',
                        "value" =>'1',
                        "post_crm" =>0,
                    )
                );
           
            $this->pixelFireInterface->SetPixelFireStatus("FLSN", $visitorId, $userId);
            self::updateFollowUpCompleteStatus($userId,$source);

           

        }
        return;
    }
    public function updateFollowUpCompleteStatus($userId,$source)
    {
        $signData = self::getSignData($userId);
        $questionData = self::getQuestionData($userId);
        $time_now = Carbon::now();
        // $update = UserMilestoneStats::where(['user_id' => $userId] )
        //                             ->where(['source'=> $source]);
                           
        if (!empty($signData->user_sign) && !empty($questionData)) {

            UserMilestoneStats::where(['user_id' => $userId] )
                                ->where(['source'=> $source])
                                ->update(
                                [
                                    'user_completed' => 1,
                                    'user_completed_date' => $time_now,
                                    'completed' => 1,
                                    'completed_date' => $time_now
                                ]
                );

            UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
        }
    }
    public function getQuestionData($userId)
    {
       
        $answerCount = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA', 'Q.id', '=', 'questionnaire_id')
            ->where('UQA.user_id', '=', $userId)
            ->select('UQA.questionnaire_id')
            ->whereIn('UQA.questionnaire_id', ['5','6','7','8','9','10','11','12','13','14'])
            ->groupBy('UQA.questionnaire_id')
            ->get()
            ->toArray();
        
        if (sizeof($answerCount) > 9) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getSignData($userId)
    {
        $result = User::where(['users.id' => $userId])
            ->leftJoin('signatures', 'users.id', 'signatures.user_id')
            ->select('signatures.s3_file_path as user_sign')
            ->first();
        return $result;
    }
}