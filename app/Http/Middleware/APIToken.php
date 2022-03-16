<?php

namespace App\Http\Middleware;

use App;
use Closure;
use DB;
use Session;

class APIToken
{
    public function handle($request, Closure $next)
    {
        if ($request->header('AuthorizationToken') && $request->header('userId') ) {
            $userId = $request->header('userId');
            $accesstoken = $request->header('AuthorizationToken');
            $user_data = DB::table('users')->where(['id' => $userId])->first();
			$response = [];
			if (!empty($user_data)) {
			    if ($user_data->access_token == $accesstoken) {
                    return $next($request);
                } else {
                    $response['code'] = 200;
                    $response['success'] = false;
                    $response['auth_token_status'] = false;
                    $response['message'] = 'Invalid token.';
                    //$response['data'] = null;
                    return response()->json($response, 200);
                }
            } else {
                $response['code'] = 200;
                $response['success'] = false;
                $response['auth_token_status'] = false;
                $response['message'] = 'Unauthorised';
               // $response['data'] = null;
                return response()->json($response, 200);
            }
		}  else {
		    $response['code'] = 200;
            $response['success'] = false;
            $response['auth_token_status'] = false;
            $response['message'] = 'Not a valid API request.';
           // $response['data'] = null;
            return response()->json($response, 200);
        }
    }
}

