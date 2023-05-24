<?php 

namespace App\Repositories\Interfaces;

interface ApiClassInterface
{
    public static function validateToken($request);
    public static function validateRequest($request);


}