<?php

namespace App\CustomModels;
use App\Notifications\FinanceNotification;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use App\CustomModels\FCMTokenMethods;
use App\Custom\Firebase;
use App\Custom\Push;

class NotificationMethods{


    public static function createFinanceCreateNotification($operation, $member_id){
        $membre = MembreMethods::getById($member_id);
        if($membre != "not found"){
            $assoc = AssociationMethods::getById($membre->associations_id);
            if($assoc != "not found"){
                $user = UserMethods::getById($assoc->admin_id);
                if($user != "not found"){
                    $notif = array(
                        "membre" => $membre,
                        "message" => "creation d'une operation",
                        "operation" => $operation['data']
                    );
                    $user->notify(new FinanceNotification($notif));
                }
            }
        }

    }

    public static function notifTest($user_id){
        $user = UserMethods::getById($user_id);
        if($user != "not found"){
            $notif = array(
                "membre" => $user_id,
                "message" => "test notif",
                "operation" => "operation"
            );
            $user->notify(new FinanceNotification($notif));
        }
    }


    public static function FCMMessage($user_id, $title, $message, $datas){

        $tokensBD = FCMTokenMethods::getTokens($user_id);
        $tokens = array();
        foreach ($tokensBD as $key => $value) {
            $tokens[] = $value->token;
        }
        if(count($tokens) > 0){

            // $optionBuilder = new OptionsBuilder();
            // $optionBuilder->setTimeToLive(60*20);

            // $notificationBuilder = new PayloadNotificationBuilder($title);
            // $notificationBuilder->setBody($message)
            //                     ->setSound('default');


            // $dataBuilder = new PayloadDataBuilder();
            // $dataBuilder->addData($datas);

            // $option = $optionBuilder->build();
            // $notification = $notificationBuilder->build();
            // $data = $dataBuilder->build();

            // $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            // $tokens = $downstreamResponse->tokensToModify();
            // FCMTokenMethods::updateTokens($tokens);
           
                    
                $firebase = new Firebase();
                $push     = new Push();
    
                $push->setTitle( $title );
                $push->setMessage( $message );
                $push->setPayload( $datas );
    
                $json     = '';
                $response = '';
    
                $json     = $push->getPush();
                
                foreach ($tokens as $key => $token) {
                    $response = $firebase->send($token, $json );
                }
        }


    }

}