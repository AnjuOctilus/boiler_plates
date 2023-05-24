<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Repositories\LogRepository;
use App\Repositories\ApiClassRepository;

class ApiController extends Controller
{
    public function __construct(){
     $logRepo              = new LogRepository;
     $api_repo             = new ApiClassRepository;
    }
    
        
}
