<?php

namespace App\CustomModels;

class SendInvitation
{
    public static function sendInvitation($phone, $association, $data){
        $nexmo = app('Nexmo\Client');
        $nexmo->message()->send([
            'to' => $phone,
            'from' => "14188637770",
            'text' => "Your join code for '{$association}' association on Tontine.Plus is: ". $data
        ]);
    }

    public static function sendChatApiInvitation($phone, $association, $message)
    {
        $data = [
        'phone' => $phone, // Receivers phone
        'body' => "Your join code for '{$association}' association on Tontine.Plus is: ". $message// Message
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