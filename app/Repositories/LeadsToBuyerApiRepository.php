<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Repositories\Interfaces\LeadsToBuyerApiInterface;
use App\Repositories\LogRepository;
use Illuminate\Support\Facades\Log;
use App\Repositories\QueueRepository;
use App\Models\User;
use App\Repositories\CommonFunctionsRepository;

/**
 * Class LeadsToBuyerApiRepository
 *
 * @package App\Repositories
 */
class LeadsToBuyerApiRepository implements LeadsToBuyerApiInterface
{
    public function __construct()
    {
        $this->logRepo          = new LogRepository;
        $this->queueRepository  = new QueueRepository;
        $this->commonFunctionsRepo = new CommonFunctionsRepository;
    }

    public function completedLeadsToBuyer($url, $arrData)
    {
        //

    }

    public function completedLeadsToBuyerTransmission($reqArray)
    {
        //
    }

    public function PendingLeadsToBuyer($url, $arrData)
    {
        //
    }

    public function pendingLeadsToBuyerTransmission($reqArray)
    {
       //
    }
}
