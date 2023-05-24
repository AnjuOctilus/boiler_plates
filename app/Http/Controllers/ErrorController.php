<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Repositories\CommonFunctionsRepository;

class ErrorController extends Controller
{
  public function index()
  {
     $commonFunRepo = new CommonFunctionsRepository();
     $ip = $commonFunRepo->get_client_ip1();
  }
}
