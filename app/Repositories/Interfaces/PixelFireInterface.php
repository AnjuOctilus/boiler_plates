<?php 

namespace App\Repositories\Interfaces;

interface PixelFireInterface
{
    public function getPixelFireStatus($pixelType,$intVisitorId,$intUserId );
    public function getFollowupPixelFireStatus( $pixelType, $flvvisit_id);
    public function setPixelFireStatus( $pixelType, $intVisitorId , $intUserId);
    public function getAdvPixelFireStatus($pixelType, $intVisitorId, $intUserId);
    public function setAdvPixelFireStatus($pixelType, $intVisitorId, $intUserId);
    public function atpPixelFire($chkArry);
    public function atpFollowupPixelFire($chkArry);
}