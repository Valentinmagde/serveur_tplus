<?php
namespace App\CustomModels;

use GuzzleHttp\Client;

class CollectionMomoMethods{

    public static function getUrl(){
        if(env('MOMO_ENVIRONMENT') === "sandbox"){
            return "https://sandbox.momodeveloper.mtn.com/v1_0";
        }
    }

    public static function getCollectionUrl(){
        if(env('MOMO_ENVIRONMENT') === "sandbox"){
            return "https://sandbox.momodeveloper.mtn.com";
        }
    }

    public static function getKey($type){
        return env("MOMO_{$type}_SUBSCRIPTION_KEY");
    }

    /**
     * creation du user
     */
    public static function createUser($uuid, $type, $callback){
           
            $context_options = array (
                'http' => array (
                    'method' => 'POST',
                    'header'=> "Content-type: application/json\r\n"
                        . "X-Reference-Id: " . $uuid . "\r\n"
                        . "Ocp-Apim-Subscription-Key: " . CollectionMomoMethods::getKey($type) . "\r\n",
                    'content' => json_encode( array(
                                "providerCallbackHost"=>$callback
                            )
                        )
                    )
                );
            $context_options = stream_context_create($context_options);
            $url = CollectionMomoMethods::getUrl();
            $result = file_get_contents("$url/apiuser", false, $context_options);
            
            if($result === false) return false;
            return true;
           
    }

    /**
     * générer la clé de l'api
     */
    public static function getApiKey($uuid, $type){

            $client = new Client();
            $url = CollectionMomoMethods::getUrl();
            $headers = array(
                "Ocp-Apim-Subscription-Key"=> CollectionMomoMethods::getKey($type)
            );

            try {
               $response =  $client->request('POST', "$url/apiuser/$uuid/apikey", [
                    'headers' => $headers,
                    'form_params' => []
                ] );
                return $response->getBody()->getContents();
            } catch (\Exception $ex) {
                return false;
            }
    }

    /**
     * génération du token
     */
    public static function generateToken($uuid, $type, $type_url){

        $apiKey = CollectionMomoMethods::getApiKey($uuid, $type);
        if(!$apiKey){
            return 'error api key';
        }

        $apiKey = json_decode($apiKey)->apiKey;
        $basic = base64_encode("$uuid:$apiKey");
        $client = new Client();
        $url = CollectionMomoMethods::getCollectionUrl();
        $headers = array(
            "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey($type),
            "Authorization" => "Basic $basic",
        );
        try {
            $response =  $client->request('POST', "$url/$type_url/token/", [
                'headers' => $headers,
                'form_params' => []
            ] );
            return $response->getBody()->getContents();
        } catch (\Exception $ex) {
            dd($ex);
            return false;
        }
    }

    /**
     * pay
     */
    public static function requestToPay($transactionId, $partyId, $amount, $currency, $payerMessage = '', $payeeNote = '', $callback){

        $uuid = CollectionMomoMethods::uuidv4();
        
        $usercreated = CollectionMomoMethods::createUser($uuid, "COLLECTION", $callback);
        if(!$usercreated){
            return array(
                "status" => "NOK",
                "error" => array(
                    "errNo" => "14",
                    "errMsg" => "error de creation du user"
                )
            );
        }
        $token = CollectionMomoMethods::generateToken($uuid, "COLLECTION", "collection");
        if($token === "error api key"){
            return array(
                "status" => "NOK",
                "error" => array(
                    "errNo" => "14",
                    "errMsg" => $token
                )
            );
        }
        $token = json_decode($token);

        $client = new Client();
        $url = CollectionMomoMethods::getCollectionUrl();
        $headers = array(
            "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey("COLLECTION"),
            "Authorization" => "Bearer {$token->access_token}",
            "X-Reference-Id" => $uuid,
            "X-Target-Environment" => env('MOMO_ENVIRONMENT')
        );
        $body = array(
            'amount' => "$amount",
            'currency' => $currency,
            'externalId' => $transactionId,
            'payer' =>[
                'partyIdType' => 'MSISDN',
                'partyId' => $partyId,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote' => $payeeNote,
        );
        // dd($body);
        try {
            $response =  $client->request('POST', "$url/collection/v1_0/requesttopay", [
                'headers' => $headers,
                'json' => $body
            ] );

            $headers = array(
                "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey("COLLECTION"),
                "Authorization" => "Bearer {$token->access_token}",
                "X-Target-Environment" => env('MOMO_ENVIRONMENT'),
            );
            $response =  $client->request('GET', "$url/collection/v1_0/requesttopay/$uuid", [
                'headers' => $headers
            ] );

            // $headers = array(
            //     "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey($type),
            //     "Authorization" => "Bearer {$token->access_token}",
            //     "X-Target-Environment" => env('MOMO_ENVIRONMENT'),
            // );
            // $response =  $client->request('GET', "$url/collection/v1_0/account/balance", [
            //     'headers' => $headers
            // ] );
            return $response->getBody()->getContents();
        } catch (\Exception $ex) {
            dd($ex);
            return false;
        }
    }

    /**
     * disbursement
     */
    public static function transfert($transactionId, $partyId, $amount, $currency, $payerMessage = '', $payeeNote = '', $callback){

        $uuid = CollectionMomoMethods::uuidv4();
        
        $usercreated = CollectionMomoMethods::createUser($uuid, "DISBURSEMENT", $callback);
        if(!$usercreated){
            return array(
                "status" => "NOK",
                "error" => array(
                    "errNo" => "14",
                    "errMsg" => "error de creation du user"
                )
            );
        }
        $token = CollectionMomoMethods::generateToken($uuid, "DISBURSEMENT", "disbursement");
        if($token === "error api key"){
            return array(
                "status" => "NOK",
                "error" => array(
                    "errNo" => "14",
                    "errMsg" => $token
                )
            );
        }
        $token = json_decode($token);
        $client = new Client();
        $url = CollectionMomoMethods::getCollectionUrl();
        $headers = array(
            "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey("DISBURSEMENT"),
            "Authorization" => "Bearer {$token->access_token}",
            "X-Reference-Id" => $uuid,
            "X-Target-Environment" => env('MOMO_ENVIRONMENT')
        );
        $body = array(
            'amount' => "$amount",
            'currency' => $currency,
            'externalId' => $transactionId,
            'payee' =>[
                'partyIdType' => 'MSISDN',
                'partyId' => $partyId,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote' => $payeeNote,
        );
        // dd($body);
        try {
            $response =  $client->request('POST', "$url/disbursement/v1_0/transfer", [
                'headers' => $headers,
                'json' => $body
            ] );

            $headers = array(
                "Ocp-Apim-Subscription-Key" => CollectionMomoMethods::getKey("DISBURSEMENT"),
                "Authorization" => "Bearer {$token->access_token}",
                "X-Target-Environment" => env('MOMO_ENVIRONMENT'),
            );
            $response =  $client->request('GET', "$url/disbursement/v1_0/transfer/$uuid", [
                'headers' => $headers
            ] );

            return $response->getBody()->getContents();
        } catch (\Exception $ex) {
            dd($ex);
            return false;
        }
    }


    /**
     * generation du uuid de version 4
     */
    public static function uuidv4(){
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),

		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
    }

}