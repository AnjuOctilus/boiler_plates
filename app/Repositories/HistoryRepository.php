<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\FollowupHistories;
use App\Models\ApiHistory;
use App\Repositories\Interfaces\HistoryInterface;
/**
 * Class HistoryRepository
 *
 * @package App\Repositories
 */
class HistoryRepository implements HistoryInterface
{
    /**
     * Insert followup history
     *
     * @param $request
     */
    public function insertFollowupHistory($request)
    {
        FollowupHistories::create($request);
    }
    /**
     * Insert followup live history
     *
     * @param $request
     */
    public function insertFollowupLiveHistory($request)
    {
        FollowupHistories::updateOrCreate(
            [
                'user_id' => $request['user_id'],
                'type' => $request['type'],
                'type_id' => $request['type_id'],
                'source' => $request['source'],
            ],
            $request
        );
    }
    /**
     * Array walks basic
     *
     * @param $value
     * @param $key
     * @param $extraParam
     */
    public function arrayWalkBasic($value, $key, $extraParam)
    {
        if ($extraParam[0] != $key) {
            $this->insertFollowupLiveHistory(array(
                'user_id' => $extraParam[1],
                'type' => $key,
                'type_id' => 0,
                'source' => 'live',
                'value' => $value
            ));
        }
    }
    /**
     * Insert followup basic history
     *
     * @param $request
     */
    public function insertFollowupBasicHistory($request)
    {
        array_walk($request, array($this, 'arrayWalkBasic'), array('id', $request->id));
    }
    /**
     * Create api  history
     *
     * @param $request
     */
    public function createApiHistory($request)
    {
        ApiHistory::create($request);
    }

    /**
     * User CRMHistory
     *
     * @param $userId
     * @return mixed
     */
    public function userCRMHistory($userId)
    {
        $userCRMs = FollowupHistories::where('user_id', $userId)
            ->where('source', 'crm')
            ->orderBy('id', 'DESC')
            ->get();
        return $userCRMs;
    }
    /**
     * User follow history
     *
     * @param $userId
     * @return mixed
     */
    public function userFollowHistory($userId)
    {
        $userFollows = FollowupHistories::where('user_id', $userId)
            ->where('source', 'FLP')
            ->orderBy('id', 'DESC')
            ->get();

        return $userFollows;
    }
}
