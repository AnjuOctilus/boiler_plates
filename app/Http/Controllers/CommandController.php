<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function index($command, $param)
    {
        $artisan = Artisan::call($command.":".$param);
        $output = Artisan::output();
        return $output;
    }
}
