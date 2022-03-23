<?php

namespace App\CustomModels;

class SendPassword
{
    public static function sendPwd($phone){
        $pwd = str_random(8);
        $nexmo = app('Nexmo\Client');
        $nexmo->message()->send([
            'to' => $phone,
            'from' => "14188637770",
            'text' => "Your new password on Tontine.Plus is : ".$pwd
        ]);
        return $pwd;
    }
}