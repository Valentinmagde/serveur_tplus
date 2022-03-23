<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Models\OauthAccessToken;

class OauthAccessTokenController extends Controller
{
    /** 
     * logout api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID' => 'required'
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        if (!$token = OauthAccessToken::where('user_id', $request->userID)->first()) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'token doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $token = OauthAccessToken::find($token->id)->delete();
        $success['status'] = 'OK';
        $success['data'] =  $token;
        return response()->json($success, 200);
    }
}
