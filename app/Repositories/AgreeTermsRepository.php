<?php


namespace App\Repositories;


use App\Models\User;
use App\Models\UserExtraDetail;
use App\Models\UserMilestoneStats;
use App\Repositories\Interfaces\AgreeTermsInterface;
use App\Repositories\Interfaces\QuestionnairesInterface;
use Carbon\Carbon;
use App\Models\UserQuestionnaireAnswers;

class AgreeTermsRepository implements AgreeTermsInterface
{
    /**
     * Constructor
     *
     * AgreeTermsRepository constructor.
     */
    public function __construct(QuestionnairesInterface $questionnairesInterface)
    {
       
       
        $this->questionnairesInterface = $questionnairesInterface;
    }


    public function getJointAccountStatus($user_id)
    {
        $statusQuestions = UserQuestionnaireAnswers::select('questionnaire_id','questionnaire_option_id')
                ->where('questionnaire_id',18)
                ->where('questionnaire_option_id',51)
                ->where('user_id',$user_id)
                ->get();
        if(count($statusQuestions) == 1)
        { 
            return true;
        } 
        else 
        {
            return false;
        }
    }

}
