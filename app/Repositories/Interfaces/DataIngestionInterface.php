<?php

namespace App\Repositories\Interfaces;
use Illuminate\Http\Request;


interface DataIngestionInterface
{
    public static function setAgentRequest($request,$arrParam,$uuid);

}
