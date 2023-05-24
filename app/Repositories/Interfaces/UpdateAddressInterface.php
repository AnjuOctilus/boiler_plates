<?php


namespace App\Repositories\Interfaces;


interface UpdateAddressInterface
{
  public function updateDetails($limit,$start,$end);
  public function updateAddress($userId);
  public function updatePreviousAddress($userId);
}
