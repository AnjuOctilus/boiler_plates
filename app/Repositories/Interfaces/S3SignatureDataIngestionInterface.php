<?php


namespace App\Repositories\Interfaces;


interface S3SignatureDataIngestionInterface
{
  public function userS3SignatureStore($signatureData, $user_id, $sign_holder = 'user');
  //public function partnerS3SignatureStore($signatureData,$user_id);
}
