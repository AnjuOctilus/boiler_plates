<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserExtraDetail;
use App\Models\UserMilestoneStats;
use App\Models\BuyerApiResponse;
use App\Models\Signature;
use App\Repositories\Interfaces\ApiClassInterface;
use App\Repositories\Interfaces\QuestionnairesInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

/**
 * Class FollowupController
 *
 * @package App\Http\Controllers\V1
 */
class FollowupController extends Controller
{
    /**
     * Constructor
     *
     * FollowupController constructor.
     * @param ApiClassInterface $apiClassInterface
     */
    public function __construct(ApiClassInterface $apiClassInterface, QuestionnairesInterface $questionnairesInterface, UserInterface $userInterface)
    {
        $this->apiClassInterface = $apiClassInterface;
        $this->questionnairesInterface = $questionnairesInterface;
        $this->userInterface = $userInterface;
    }

       public function getPendingUserDetails(Request $request)
    {   
        
      $valid = $this->apiClassInterface->validateToken($request);
      
        if ($valid == 1) {
            if (isset($request) && !empty($request)) {
                $queryStringData = array();
                parse_str($request, $queryStringData);
                $token = isset($queryStringData['atp_sub2'])?$queryStringData['atp_sub2']:null;
                
                if (isset($token) && !empty($token)) {
                    $user = User::where(['token' => $token])->first();
                   
                    if (isset($user->id) && !empty($user->id)) {

                        $queryStringResponse['pixel'] = $queryStringData['pixel'];
                        $queryStringResponse['atp_source'] = $queryStringData['atp_source'];
                        $queryStringResponse['atp_vendor'] = $queryStringData['atp_vendor'];
                        $queryStringResponse['atp_sub1'] = $queryStringData['atp_sub1'];
                        $queryStringResponse['atp_sub2'] = $queryStringData['atp_sub2'];
                        $queryStringResponse['atp_sub3'] = $queryStringData['atp_sub3'];
                        $queryStringResponse['atp_sub4'] = @$queryStringData['atp_sub4'];
                        $queryStringResponse['url_id'] = @$queryStringData['url_id'];
                        $queryStringResponse['lp_id'] = @$queryStringData['lp_id'];
                        $queryStringResponse['atp_sub6'] = $queryStringData['atp_sub6'];
                        $pendingData = [
                            'is_user_sign' => 1,
                        ];
                        if (!Signature::where(['user_id' => $user->id])->exists()) {
                            $pendingData['is_user_sign'] = 0;
                        }
                        $userRepoObj = new UserRepository();

                        $uerExtra = UserExtraDetail::where(['user_id' => $user->id])->first();
                        $buyer_response = BuyerApiResponse::where(['user_id'=>$user->id,'buyer_id'=>2])
                        ->whereNotNull('lead_id')
                        ->first();
                        $is_buyer_post = isset($buyer_response) ? 1 : 0;
                        $pendingData['is_user_complete'] = (isset($uerExtra->complete_status)) ? $uerExtra->complete_status : 0;

                        $isQuestion = $userRepoObj->isQuestionnaireComplete($user->id);
                        if (!$isQuestion) {
                            $totalQuestionCount = 10;
                            $pendingData['is_questionnaire'] = 0;
                            $pendingData['pending_question'] = $this->questionnairesInterface->getFollowUpPendingQuestions($user->id);
                            $pendingData['filled_question_count'] = $this->questionnairesInterface->getFollowUpPendingQuestionsCount($user->id);
                            $pendingData['pending_question_count'] = $totalQuestionCount-($pendingData['filled_question_count']);
                            $pendingData['filled_question'] = $this->questionnairesInterface->getFollowUpFilledUpQuestions($user->id);
                            $pendingData['filled_question_answers'] = $this->questionnairesInterface->getFollowUpQuestionsAnswers($user->id);
                            //dd($pendingData['pending_question']);
                        }else{
                            //return['status'=>'isQuestion else condition'];
                                $pendingData['is_questionnaire'] = 1;
                                $pendingData['pending_question'] = [];
                                $pendingData['pending_question'] = [];
                        }

                        $userData = $this->userInterface->getUserData($token);
                        $data['user_info'] = array(
                            'user_id'  => $userData[0]['user_id'],
                            'uuid'     => $userData[0]['user_uuid'],
                            'user_name' => ucfirst(strtolower($userData[0]['first_name'])) . ' ' . ucfirst(strtolower($userData[0]['last_name'])),
                            'user_dob'  => (isset($userData[0]['user_dob']) && !empty($userData[0]['user_dob'])) ? date("d/m/Y", strtotime($userData[0]['user_dob'])) : null,
                            'house_number' => $userData[0]['address_line1'],
                            'post_code' => $userData[0]['postcode'],
                            'town' => $userData[0]['town'],
                            'county' => $userData[0]['county'],
                            'country' => $userData[0]['country'],
                        );

                        $data['pending_details'] =  $pendingData;
                        $data['followup_data'] = $queryStringResponse;
                       //return['status'=>'FollowUpdata','data'=>$data['followup_data']['atp_sub2']];
                       // $data['followup_data'] = $queryStringResponse;
                        $response = ['status' => 'Success', 'response' => $data];
                    } else {
                        $response = ['status' => 'Failure', 'response' => 'Invalid user','valid'=>0];
                    }
                } else {
                    $response = ['status' => 'Failure', 'response' => 'Token is missing','valid'=>0];
                }
            } else {
                $response = ['status' => 'Failure', 'response' => 'Invalid request','valid'=>0];
            }
        } else {
            $response = array('response' => 'Authentication Failed', 'status' => 'Failed','valid'=>0);
        }
        return response()->json($response);
    }
    //Get UUID from user Token

   

}
