<?php 

namespace App\Repositories\Interfaces;

interface LeadsToBuyerApiInterface
{
    public function completedLeadsToBuyer($url,$arrData);
    public function completedLeadsToBuyerTransmission($reqArray);
}