<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\UserBankDetail;
use App\Repositories\CommonFunctionsRepository;
class PDFDownloadAuthMiddleware
{
    
    public function handle($request, Closure $next)
    {
        $user = User::where('token',$request->token)->first();
        if($user) {
            return $next($request);
        }
        else {
            abort(401, 'Token is invalid');
        }
    }
}
