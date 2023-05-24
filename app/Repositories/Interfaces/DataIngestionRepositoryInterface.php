<?php

namespace App\Repositories\Interfaces;
use Illuminate\Http\Request;


interface DataIngestionRepositoryInterface
{
    public function setAgentRequest($page,Request $request,$arrParam,$uuid);

}