<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BuyerCakeInterface;
use DB;
use App\Models\User;
use App\Repositories\CommonFunctionsRepository;
use App\Jobs\PostLeadsToCake;

/**
 * Class BuyerCakeRepository
 * 
 * @package App\Repositories
 */
class BuyerCakeRepository implements BuyerCakeInterface
{
    /**
     * Post lead data to cake
     *
     * @param $usrId
     */
    public function postLeadDataToCake($usrId)
    {
        //************already posted to cake************
        $buyer_data = $this->BuyerPostAllow($usrId);
        $email = User::whereId($usrId)->first()->email;
        if ($buyer_data = 'true') {
            $commonFunctionRepo = new CommonFunctionsRepository;
            $recordStatus = $commonFunctionRepo->isTestLiveEmail($email);
            //*********post data to cake *****************
            dispatch(new PostLeadsToCake($usrId, $recordStatus));
        }
    }

    /**
     * Buyer post allow
     *
     * @param $userId
     * @return string
     */
    public function BuyerPostAllow($userId) // check wether to allow cake posting
    {
        $buyerPostStatus = DB::table('buyer_api_responses AS BAP')
            ->select('BAP.result')
            ->where('BAP.user_id', '=', $userId)
            ->where('BAP.result', '=', "Success")
            ->where('buyer_id', 1)
            ->get();
        $return_val = '';
        if (count($buyerPostStatus) == 0) {
            $return_val = 'true';
        } else {
            $return_val = 'false';
        }
        return $return_val;
    }


}
