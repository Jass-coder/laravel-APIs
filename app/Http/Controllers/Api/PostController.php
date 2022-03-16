<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\Post;
use App\Models\User;

class PostController extends Controller
{
    public function createPost(Request $request)
    {
        $action = "create_post";
        $input = $request->all();
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
                    'title' => 'required',
                    'description' => 'required',
                    'post_time' => 'required',
                    'post_date' => 'required',
                ]);

                if ($validator->fails()) {
                    $errorString = implode(",", $validator->messages()->all());
                    return response()->json([
                        'status' => false,
                        'action' => $action,
                        'message' => $errorString,
                    ], 200);
                }

                $post = new Post();
                $post->title = $request->title;
                $post->description = $request->description;
                $post->post_time = $request->post_time;
                $post->post_date = $request->post_date;
                $post->added_by = $user_token->id;
                $post->created_at = date('Y-m-d H:i:s');
                if($post->save()){
                    return response()->json([
                        'status' => true,
                        'action' => $action,
                        "message" => "New post created !!!",
                        "data"=>$post,
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
