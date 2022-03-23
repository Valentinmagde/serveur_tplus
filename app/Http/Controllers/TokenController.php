<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\CustomModels\FCMTokenMethods; 

class TokenController extends Controller
{
    //

    public function addToken($user_id, $token, Request $request){
        $token = FCMTokenMethods::addToken($user_id, $request->input('token'), $token);

        if($token['status'] == "OK"){
            return response()->json($token, 201);
        }else{
            return response()->json($token, 500);
        }
    }
}
