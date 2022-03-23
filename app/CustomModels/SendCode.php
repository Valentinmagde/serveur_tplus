<?php

namespace App\CustomModels;

class SendCode
{
    public static function sendCode($phone, $code){
       
        $nexmo = app('Nexmo\Client');
        $nexmo->message()->send([
            'to' => $phone,
            'from' => "14188637770",
            'text' => "Your activation code On Tontine.Plus is: ". $code
        ]);
        return $code;
    }
    public static function sendChatApiCode($phone, $code)
    {
        $data = [
            'phone' => $phone, // Receivers phone
            'body' => "Your activation code On Tontine.Plus is: ". $code// Message
        ];
        $json = json_encode($data); // Encode data to JSON
        // URL for request POST /message
        $url = 'https://eu196.chat-api.com/instance182965/sendMessage?token=lqsp5w7ih1h2fvwg';
        // Make a POST request
        $options = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/json',
        'content' => $json
        ]
        ]);
        // Send a request
        $result = @file_get_contents($url, false, $options);
    }
    
}