<?php

namespace App\CustomModels;
use App\Models\FcmToken;


class FCMTokenMethods{



    public static function addToken($user_id, $token0, $token1){

        try {
            $token = FcmToken::where('utilisateurs_id', $user_id)->first();
            if($token){
                $token->delete();
            }
            $tok = FcmToken::create(["utilisateurs_id"=> $user_id, "token"=> $token0 ?? $token1]);

            $success['status'] = "OK";
            $success['data'] = $tok;

            return $success;

        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

    }


    public static function getTokens($user_id){

        $tokens = FcmToken::where('utilisateurs_id', $user_id)
                            ->get();
        return $tokens;
    }

    public static function updateTokens($tokens){
        foreach ($tokens as $key => $value) {
            $tok = FcmToken::where('token', $key)->first();
            if($tok){
                $tok->fill(['token'=> $value]);
                $tok->save();
            }
        }
    }

    public static function deleteTokens($tokens){
        foreach ($tokens as $key => $value) {
            FcmToken::where('token', $value)->delete();
        }
    }

}