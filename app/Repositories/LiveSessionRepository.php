<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\UserMilestoneStats;
use App\Models\PartnerMilestoneStats;
use App\Repositories\Interfaces\LiveSessionInterface;
use DB;

// use App\Repositories\UserRepository;

/**
 * Class LiveSessionRepository
 * 
 * @package App\Repositories
 */
class LiveSessionRepository implements LiveSessionInterface
{
    /**
     * Insert Basic live session
     *
     * @param $request
     */
    public function insertBasicLiveSession($request)
    {
        if (!empty($request->spouses_first_name) && !empty($request->spouses_last_name) && !empty($request->spouses_dob)) {
            $this->createUserMilestoneStats(array(
                'user_id' => $request->id,
                'partner_details' => 1,
                'source' => 'live'
            ));
        }
    }

    /**
     * Create user milestone stats
     *
     * @param $request
     */
    public function createUserMilestoneStats($request)
    {
        UserMilestoneStats::updateOrCreate(
            [
                'user_id' => $request['user_id'],
                'source' => $request['source']
            ],
            $request
        );
    }

    /**
     * Create partner milestone stats
     *
     * @param $request
     */
    public function createPartnerMilestoneStats($request)
    {
        PartnerMilestoneStats::updateOrCreate(
            [
                'user_id' => $request['user_id'],
                'is_share' => $request['is_share']
            ],
            $request
        );
    }

    /**
     * Completed status update
     *
     * @param $userId
     * @param string $source
     * @return string[]
     */
    public function completedStatusUpdate($userId, $source = 'live') // $person = user, partner, total
    {
        if ($userId) {
            $user_completed_flag = $partner_completed_flag = $time_now = $ums_db_column = $update_statement = $search_statement = "";
            $userRepoObject = new UserRepository();
            $user_completed_flag = $userRepoObject->isUserComplete($userId);
            $time_now = Carbon::now();
            $people = array('user', 'total');
            if ($source) {
                foreach ($people as $person) {
                    if ($person) {
                        switch ($person) {
                            case 'user':
                                $ums_db_column = "user_completed";
                                break;
                            case 'total':
                                $ums_db_column = "completed";
                                break;
                        }
                        $search_statement = DB::table('user_milestone_stats')
                            ->where('user_id', $userId)
                            ->whereRaw($ums_db_column . " = 1")
                            ->get();
                        $update_statement = DB::table('user_milestone_stats')
                            ->where('user_id', $userId)
                            ->where('source', $source)
                            ->where('user_id', $userId)
                            ->whereRaw("(" . $ums_db_column . " = 0 or " . $ums_db_column . " is null)");
                        if (sizeof($search_statement) == 0) {
                            if ($user_completed_flag == 1 && $person == "total") {
                                // Update completed time and flag to user_milestone_stats table
                                $update_statement->update(['completed' => 1, 'completed_date' => $time_now]);
                            } else if ($user_completed_flag == 1 && $person == "user") {
                                // Update user_completed time and flag to user_milestone_stats table
                                $update_statement->update(['user_completed' => 1, 'user_completed_date' => $time_now]);
                            }
                        }
                    }
                }
            } else {
                return array('status' => 'error', 'response' => 'no source passed');
            }
        } else {
            return array('status' => 'error', 'response' => 'no user passed');
        }
    }
}
