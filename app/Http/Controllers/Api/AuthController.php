<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */

    public function __construct()
    {
        // Unique Token
        $this->apiToken = uniqid(base64_encode(Str::random(20)));
    }

    public function signup(Request $request)
    {
        $action = "signup";
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'contact_number' => 'required|min:10',
            'email' => 'unique:users|email',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return response()->json([
                'status' => false,
                'action' => $action,
                'message' => $errorString,
            ], 200);
        }

        $user = new User();
        $user->name = $request->name;
        $user->contact_number = $request->contact_number;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->created_at = date('Y-m-d H:i:s');
        if($user->save()){
            return response()->json([
                'status' => true,
                'action' => $action,
                "message" => "You are successfully registered.",
                "data"=>$user,
            ],200);
        } else {
            return response()->json([
                'status' => false,
                'action' => $action,
                'message' => 'Try Again',
            ], 422);
        }
    }


    public function login(Request $request)
    {
        $action = "login";
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return response()->json([
                'status' => false,
                'action' => $action,
                'message' => $errorString,
            ], 200);
        }

        $user = User::where('email', $request->username)->first();

        if (empty($user)) {
            return response()->json([
                'message' => 'Please login with registered email/username !!!',
                'status' => false,
                'action' => $action,
                'input_data' => $input
            ], 200);
        }
       
        if (Hash::check($request->password, $user->password)) {
            $user->remember_token = $this->apiToken;
            $user->save();

            return response()->json([
                'status' => true,
                'action' => $action,
                'message' => 'You are loggedin sucessfully !!!',
                'data'=> $user
            ], 200);
        }
        else {
            return response()->json([
                'status' => true,
                'action' => $action,
                'message' => 'Password mismatch,please try again !!!',
                'input_data' => $input
            ], 422);
        }
    }


    public function logout(Request $request)
    {
        $action = "logout";

        $user_access_token = $request->header('AuthorizationToken');
        $user = User::where('remember_token', $user_access_token)->first();

        if (empty($user)) {
            return response()->json([
                'message' => 'you are not logged in',
                'status' => false,
                'action' => $action
            ], 200);
        }

        $user->remember_token = null;

        if ($user->save()) {
            return response()->json([
                'status' => true,
                'action' => $action,
                'message' => 'You are successfully logged out'
            ],200);
        }
    }

    public function editProfile(Request $request)
    {
        $action = "edit-user-profile";
        $input = $request->all();
        // dd($input);
        $header_token = $request->header('AuthorizationToken');
        $user_token = User::where('remember_token', $header_token)->first();
        if(empty($header_token)){
            return response()->json([
                'message' => 'Please pass header parameters !!!',
                'status' => false,
                'action' => $action,
            ], 422);
        }
        elseif (!empty($header_token)) {
            if ($user_token->remember_token != $header_token) {
                $response['code'] = 200;
                $response['success'] = false;
                $response['auth_token_status'] = false;
                $response['message'] = 'Invalid token';
                return response()->json($response);
            }
            else{
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'contact_number' => 'required|min:10',
                    'email' => 'email',
                ]);

                if ($validator->fails()) {
                    $errorString = implode(",", $validator->messages()->all());
                    return response()->json([
                        'status' => false,
                        'action' => $action,
                        'message' => $errorString,
                    ], 200);
                }

                $user = User::where('id',$user_token->id)->first();
                $user->name = $request->name;
                $user->contact_number = $request->contact_number;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->location = $request->location;
                $user->created_at = date('Y-m-d H:i:s');
                if($request->hasfile('image')){
                    $file = $request->file('image');
                    $name=time().uniqid(rand()).'.'.$file->extension();
                    $status = $file->move(public_path().'/upload_images/', $name);
                    $user->image= $name;
                    // $user->update();
                }
                if($user->save()){
                    return response()->json([
                        'status' => true,
                        'action' => $action,
                        "message" => "Record updated !!!",
                        "data"=>$user,
                    ],200);
                } else {
                    return response()->json([
                        'status' => false,
                        'action' => $action,
                        'message' => 'Try Again',
                    ], 422);
                }
            }
        }
    }

}
