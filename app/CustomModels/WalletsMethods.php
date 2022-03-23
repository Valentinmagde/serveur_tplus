<?php

namespace App\CustomModels;


use App\Models\Wallet;
use App\Models\AWallet;
use App\Models\TWallet;
use App\Models\UWallet;
use App\Models\CashInsOut;
use App\Models\MembresHasUser;
use App\Models\Utilisateur;
use App\Models\Membre;
use App\Models\WTransaction;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Omnipay\Omnipay;
use FannyPack\Momo\Products\Disbursement;
use FannyPack\Momo\Products\Remittance;
use Bmatovu\MtnMomo\Products\Collection;
use Omnipay\Common\CreditCard;
use \Stripe\StripeClient;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use PaypalPayoutsSDK\Core\PayPalHttpClient;
use PaypalPayoutsSDK\Core\SandboxEnvironment;

class WalletsMethods
{



    /**
     * récupérer tout les uwallets des utilisateurs
     * @param $user_id
     */
    public static function getAllUwallet($user_id)
    {

        $user = UserMethods::getById($user_id);
        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "user not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $wallets = array();
        $u_wallets = UWallet::where('utilisateurs_id', $user_id)->get();
        foreach ($u_wallets as $key => $u_wallet) {
            $wallet = Wallet::find($u_wallet->wallets_id);
            if($wallet){
                $wallet['u_wallet'] = $u_wallet;
            }
            $wallets[] = $wallet;
        }

        return array(
            "status" => "OK",
            "data" => $wallets
        );
    }

    public static function getUwalletById($user_id, $id)
    {

        $user = UserMethods::getById($user_id);
        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "user not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $wallets = array();
        $u_wallet = UWallet::where('utilisateurs_id', $user_id)->where('id', $id)->first();
        if($u_wallet){
            $wallet = Wallet::find($u_wallet->wallets_id);
            $wallets[] = $wallet;
    
            return array(
                "status" => "OK",
                "data" => $wallets
            );
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }


    public static function getUwalletByWalletId($user_id, $id)
    {

        $user = UserMethods::getById($user_id);
        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "user not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $wallets = array();
        $u_wallet = UWallet::where('utilisateurs_id', $user_id)->where('wallets_id', $id)->first();
        if($u_wallet){
            $wallet = Wallet::find($u_wallet->wallets_id);
            $wallets[] = $wallet;
    
            return array(
                "status" => "OK",
                "data" => $wallets
            );
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }

    public static function getAwalletByIdAndAssocId($assocId, $id)
    {

        $assoc = AssociationMethods::getById($assocId);
        if ($assoc == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "association not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if($assoc->a_wallets_id != $id){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet id given is not for this association or not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $wallet = WalletsMethods::getWalletByAWallet($id);
        if($wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
        return array(
            "status" => "OK",
            "data" => $wallet
        );
       
        
    }
    

    public static function getCashInOut($wallet_id){
        $cashs = CashInsOut::where('wallets_id', $wallet_id)
                            ->orderBy('created_at', 'desc')
                            ->get();
        return array(
            "status" => "OK",
            "data" => $cashs
        );
    }

    public static function getWTransactions($wallet_id){
        $cashs = WTransaction::where('wallets_source_id', $wallet_id)
                            ->orWhere("wallets_destination_id", $wallet_id)
                            ->orderBy('date_transaction', 'desc')
                            ->get();
        foreach ($cashs as $key => $cash) {
            $wallet_source = Wallet::find($cash->wallets_source_id);
            if($wallet_source){
                $cash['wallet_source'] = $wallet_source;
            }
            $wallet_destination = Wallet::find($cash->wallets_destination_id);
            if($wallet_destination){
                $cash['wallet_destination'] = $wallet_destination;
            }
        }
        return array(
            "status" => "OK",
            "data" => $cashs
        );
    }

    public static function getWTransactionsWalletAssociation($wallet_id, $association){
        $wallet = WalletsMethods::getWalletByAWallet($association->a_wallets_id);
        if($wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $type = array('wdecaissement', 'wcotisation');

        $cashs = WTransaction::whereRaw("(wallets_source_id = $wallet_id or wallets_destination_id = $wallet_id) and (wallets_source_id = $wallet->id or wallets_destination_id = $wallet->id)")
                            ->whereIn('type', $type)
                            ->orderBy('date_transaction', 'desc')
                            ->get();

        foreach ($cashs as $key => $cash) {
            $wallet_source = Wallet::find($cash->wallets_source_id);
            if($wallet_source){
                $cash['wallet_source'] = $wallet_source;
            }
            $wallet_destination = Wallet::find($cash->wallets_destination_id);
            if($wallet_destination){
                $cash['wallet_destination'] = $wallet_destination;
            }
        }
        return array(
            "status" => "OK",
            "data" => $cashs
        );
    }

    /**
     * obtenir un a_wallet a partir de son wallet
     * @param $a_wallets_id
     */
    public static function getWalletByAWallet($a_wallets_id)
    {
        $a_wallet = AWallet::find($a_wallets_id);
        if (!$a_wallet) {
            return "not found";
        }
        $wallet = Wallet::find($a_wallet['wallets_id']);
        if ($wallet) return $wallet;
        return "not found";
    }

    /**
     * obtenir le twallet par son id
     */
    public static function getWalletByTWallet($t_wallets_id)
    {
        $t_wallet = TWallet::find($t_wallets_id);
        if (!$t_wallet) {
            return "not found";
        }
        $wallet = Wallet::find($t_wallet['wallets_id']);
        if ($wallet) return $wallet;
        return "not found";
    }


    /**
     * obtenir le twallet par sa devise
     */
    // public static function getWalletByTWalletCurrency($currency)
    // {
    //     $t_wallet = Wallet::where("devise", $currency)->where("type", "t-wallet")->first();
    //     if (!$t_wallet) {
    //         return "not found";
    //     }

    //     return $t_wallet;
    // }

    /**
     * obtenir un u_wallet a partir de son id
     * @param $u_wallet_id
     */
    public static function getWalletByUWallet($u_wallets_id)
    {
        $u_wallet = UWallet::find($u_wallets_id);
        if (!$u_wallet) {
            return "not found";
        }
        $wallet = Wallet::find($u_wallet['wallets_id']);
        if ($wallet) return $wallet;
        return "not found";
    }
    
    public static function getTWalletByCurrency($currency)
    {
        $wallet = Wallet::where("devise", $currency)->where('type', 't-wallet')->first();
        if ($wallet) return $wallet;
        return "not found";
    }
    /**
     * store un A_Wallet 
     * @param $wallet 
     * @param $type
     * 
     */
    public static function storeAWallet($wallet, $type)
    {
        DB::beginTransaction();
        try {

            $wallet['type'] = $type;
            if (!array_key_exists("solde", $wallet)) $wallet['solde'] = 0;
            if (!array_key_exists("etat", $wallet)) $wallet['etat'] = "init";
            $wallet['created_at'] = DateMethods::getCurrentDateInt();
            $wallet_saved = Wallet::create($wallet);
            $a_wallet = array(
                "wallets_id" => $wallet_saved->id,
                "created_at" => DateMethods::getCurrentDateInt()
            );
            $a_wallet_saved = AWallet::create($a_wallet);

            $wallet_saved[$type] = $a_wallet_saved;
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $wallet_saved
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * delete wallet buy id
     */
    public static function deleteWallet($wallet_id){
        $wallet = Wallet::find($wallet_id);
        if (!$wallet) {
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if($wallet->solde != 0){
            $err['errNo'] = 15;
            $err['errMsg'] = "votre solde n'est pas null vous ne pouvez supprimer ce porte monnaie";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try{
            $u_wallet = UWallet::where('wallets_id', $wallet_id)->first();
            if(!$u_wallet){
                $err['errNo'] = 15;
                $err['errMsg'] = "u-wallet reference to  not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $user = Membre::where('default_u_wallets_id', $u_wallet->id)->first();
            if($user){
                $user->fill([
                    "default_u_wallets_id" => null
                ]);
                $user->save();
            }
            $wallet->delete();
            return array(
                "status" => "OK",
                "data" => "suppression réussi"
            );
        }catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function setDefaultUWallet($u_wallet_id, $user_id, $assocId){
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $wallet = WalletsMethods::getWalletByUWallet($u_wallet_id);
        if ($wallet == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "u-wallet reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $user = Utilisateur::where('id', $user_id)->first();
        if(!$user){

            $err['errNo'] = 15;
            $err['errMsg'] = "user reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
            
        }

        $hasUser =  MembresHasUserMethods::getByUserIdAssocId($user_id, $assocId);
        if($hasUser == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "user don't have member on this association";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
        $member = MembreMethods::getById($hasUser->id);
        if($member == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "member reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try{
            if($wallet->devise !== $association->devise){
                $err['errNo'] = 15;
                $err['errMsg'] = "l'association et le wallet choisie doivent avoir la même devise";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $member->default_u_wallets_id = $u_wallet_id;
            $member->save();

            return array(
                "status" => "OK",
                "data" => $member
            );
        }catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * store un U_Wallet 
     * @param $wallet
     * @param $type
     * @param $user_id
     */
    public static function storeUWallet($wallet, $type, $user_id)
    {
        DB::beginTransaction();
        try {

            $user = UserMethods::getById($user_id);
            if ($user == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "user  not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $exist = WalletsMethods::getAllUwallet($user_id);
            foreach($exist['data'] as $value){
                if($value['devise'] == $wallet['devise']){
                    $err['errNo'] = 14;
                    $err['errMsg'] = "vous avez déjà un wallet de cette devise";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            $wallet['type'] = $type;
            if (!array_key_exists("solde", $wallet)) $wallet['solde'] = 0;
            if (!array_key_exists("etat", $wallet)) $wallet['etat'] = "init";
            $wallet['created_at'] = DateMethods::getCurrentDateInt();
            $wallet_saved = Wallet::create($wallet);
            $u_wallet = array(
                "wallets_id" => $wallet_saved->id,
                "utilisateurs_id" => $user_id,
                "created_at" => DateMethods::getCurrentDateInt()
            );
            $u_wallet_saved = UWallet::create($u_wallet);
            $wallet_saved[$type] = $u_wallet_saved;
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $wallet_saved
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * store TWallet
     */
    public static function storeTWallet($wallet, $type)
    {
        DB::beginTransaction();
        try {

            $wallet['type'] = $type;
            if (!array_key_exists("solde", $wallet)) $wallet['solde'] = 0;
            if (!array_key_exists("etat", $wallet)) $wallet['etat'] = "init";
            $wallet['created_at'] = DateMethods::getCurrentDateInt();
            $wallet_saved = Wallet::create($wallet);
            $t_wallet = array(
                "wallets_id" => $wallet_saved->id,
                "created_at" => DateMethods::getCurrentDateInt()
            );
            $t_wallet_saved = TWallet::create($t_wallet);
            $wallet_saved[$type] = $t_wallet_saved;
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $wallet_saved
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * update d'un wallet
     * @param $wallet_id
     * @param $wallet_data
     */
    public static function updateWallet($wallet_id, $wallet_data)
    {
        $wallet = Wallet::find($wallet_id);
        if (!$wallet) {
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }


        try {

            $wallet->fill($wallet_data);
            $wallet->save();

            $success['status'] = "OK";
            $success['data'] = $wallet;
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * faire un cash-in ou un cash-out dans à l'aide d'un service de paiement
     * @param $cash
     * @param $type
     */
    public static function cashInOut($cash,  $type, $card)
    {   
        DB::beginTransaction();
        try {
            $wallet = Wallet::find($cash['wallets_id']);
            if (!$wallet) {
                $err['errNo'] = 15;
                $err['errMsg'] = "wallet reference to  not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            if($cash['in_out'] == "out" && $wallet->solde < $cash['montant']){
                $err['errNo'] = 14;
                $err['errMsg'] = "solde insuffisant";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            
            $cash['created_at'] = DateMethods::getCurrentDateInt();
            $cash['methode_paiement'] = $type;
            $cash['status'] = "pending";

            $link = null;
            $momoNumber = null;
            if(array_key_exists('link', $cash)){
                $link = $cash['link'];
            }
            if(array_key_exists('momoNumber', $cash)){
                $momoNumber = $cash['momoNumber'];
                unset($cash['momoNumber']);
            }
            $cash = CashInsOut::create($cash);
            if($cash->in_out == "in"){

                switch ($type) {
                    case 'paypal':


                        $gateway = Omnipay::create('PayPal_Rest');
                        $gateway->initialize(array(
                            "clientId" => env('PAYPAL_CLIENT_ID'),
                            "secret" => env('PAYPAL_CLIENT_SECRET'),
                            "testMode" => true
                        ));
                                
                        $response = $gateway->purchase(array(
                            'amount' => $cash->montant,
                            'currency' => $cash->devise ?? "USD",
                            'returnUrl' => url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/success?link={$link}"),
                            'cancelUrl' => url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/error?link={$link}"),
                        ))->send();
    
                        if ($response->isRedirect()) {
                            DB::commit();
                            return array(
                                "status" => "OK",
                                "data" => array(
                                    "redirect" =>  $response->getData()['links'][1]['href'],
                                    "success_link" => url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/success?link={$link}"),
                                    "error_link" => url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/error?link={$link}")
                                )
                            ); 
                        } else {
                            DB::rollback();
                            $cash->status = "echec";
                            $cash->save();
    
                            $err['errNo'] = 12;
                            $err['errMsg'] = $response->getMessage();
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
    
                    break;
                    
                    case 'stripe':

                        $gateway = Omnipay::create('Stripe');
                        $gateway->initialize(array(
                            "apiKey" => env('stripe_api_key')
                        ));

                        $stripe = new StripeClient(
                            env('stripe_api_key')
                        );

                        $token = $stripe->tokens->create([
                            'card' => $card,
                        ]);
                          
                        $transaction = $gateway->purchase(array(
                            'amount' => $cash->montant  + round(($cash['montant'] * 5 )/100, 2),
                            'currency' => $cash->devise,
                            'source' => $token->id
                        ));

                        $response = $transaction->send();
                        if($response->isSuccessful()){

                            $arr_body = $response->getData();
                            $isPaymentExist = $cash;

                            if ($isPaymentExist && $isPaymentExist->payment_id == null) {
                               

                                $wallet->fill([
                                    "solde" => $wallet->solde + $isPaymentExist->montant,
                                    "updated_at" => DateMethods::getCurrentDateInt()
                                ]);
                                $wallet->save();
                                $isPaymentExist->payment_id = $arr_body['id'];
                                $isPaymentExist->payer_id = $arr_body['source']['id'];
                                // $isPaymentExist->payer_email = $arr_body['payer']['payer_info']['email'];
                                $isPaymentExist->status = $arr_body['status'];
                                $isPaymentExist->save();
                            } else {
                                DB::rollback();
                                $err['errNo'] = 15;
                                $err['errMsg'] = "transaction cash-in/cash-out reference to  not found";
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return $error;
                            }
                            
                        }else{
                            $cash->status = "echec";
                            $cash->save();
                            DB::rollback();
                            $err['errNo'] = 11;
                            $err['errMsg'] = $response->getMessage();
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    break;

                    case 'momo':
                        $callback = url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/success");
                        $momo = CollectionMomoMethods::requestToPay(WalletsMethods::generateRandomString(5), $momoNumber, $cash->montant, $cash->devise, '', '', $callback);
                        $momo = json_decode($momo);
                        if($momo->status == "SUCCESSFUL"){
                            $wallet->fill([
                                "solde" => $wallet->solde + $momo->amount,
                                "updated_at" => DateMethods::getCurrentDateInt()
                            ]);
                            $wallet->save();

                            $cash->payment_id = $momo->financialTransactionId;
                            $cash->status = $momo->status;
                            $cash->payer_id = $momo->payer->partyId;
                            $cash->save();
                        }
                    break;
                }
            }
            else{
                
                switch ($type) {
                    case 'paypal':
                    break;    
                    case "stripe":
                        $gateway = Omnipay::create('Stripe');
                        $gateway->initialize(array(
                            "apiKey" => env('stripe_api_key')
                        ));

                        $stripe = new StripeClient(
                            env('stripe_api_key')
                          );
                        $token = $stripe->tokens->create([
                            'card' => $card,
                        ]);
                        $cash->receiver = $token->id;
                        $cash->save();
                    break;
                    case 'momo':
                        $callback = url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/success");
                        $momo = CollectionMomoMethods::transfert(WalletsMethods::generateRandomString(5), $momoNumber, $cash->montant, $cash->devise, '', '', $callback);
                        $momo = json_decode($momo);
                        if($momo->status == "SUCCESSFUL"){
                            
                            $cash->payment_id = $momo->financialTransactionId;
                            $cash->status = $momo->status;
                            $cash->payer_id = $momo->payer->partyId;
                            $cash->save();
                        }
                    break;
                }

                $wallet->fill([
                    "solde" => $wallet->solde - $cash->montant,
                    "updated_at" => DateMethods::getCurrentDateInt()
                ]);
                $wallet->save();
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $cash
            );
            
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function validateCashOut($cash_in_out_id){
        DB::beginTransaction();
        $cash = CashInsOut::find($cash_in_out_id);
        if(!$cash){
            $err['errNo'] = 15;
            $err['errMsg'] = "operation reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        if($cash->status != "pending"){
            $err['errNo'] = 15;
            $err['errMsg'] = "request can't be vakidate";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            if($cash->in_out == "out"){
                switch ($cash->methode_paiement) {
                        case 'paypal':
                            $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));
                            $client = new PayPalHttpClient($environment);
                            $request = new PayoutsPostRequest();
                            $random = WalletsMethods::generateRandomString('10');
                            $montant = $cash->montant - round(($cash['montant'] * 3 )/100, 2);
                            $body = array(
                                "sender_batch_header" => array(
                                    'email_subject' => "payout tontine plus $random"
                                ),
                                "items" =>array( array(
                                        "recipient_type" => "EMAIL",
                                        "receiver" => $cash->receiver,
                                        "note" => "Your $cash->montant payout",
                                        "sender_item_id"=> "$random",
                                        "amount" =>  array(
                                            "currency"=> $cash->devise,
                                            "value"=> "$montant"
                                        )
                                    )
                                )
                            );
                            $request->body = $body; 
                            // dd($request->body);
                            $client = PayPalClient::client();
                            $response = $client->execute($request);
                            $cash->payment_id = $response['result']['batch_header']['payout_batch_id'];
                            $cash->save();

                            DB::commit();
                            return array(
                                "status" => "OK",
                                "data" => $response
                            );
                        break;
                        
                        case "stripe":
                            $montant = $cash->montant - round(($cash['montant'] * 3 )/100, 2);
                            $random = WalletsMethods::generateRandomString(5);
                            $stripe = new \Stripe\StripeClient(
                                'sk_test_51HYvE2Ka5FAWQapchipli1U5h6ppFhwdXkXe0RF8Md1m16cCZqcEG2JGlwyuoO7c4jk4PvsCVLNOEusZjdKiwRVL00jPA9idcv'
                              );
                            $payout = $stripe->transfers->create([
                                'amount' => $montant,
                                'currency' => $cash->devise,
                                'destination' => $cash->receiver,
                                'transfer_group' => "ORDER_{$random}",
                            ]);
                            return array(
                                "status" => "OK",
                                "data" => $payout
                            );
                        break;

                        case 'momo':
                            $callback = url("api/wallet/cashinout/{$cash->id}/type_paiement/paypal/success");
                            $momo = CollectionMomoMethods::transfert(WalletsMethods::generateRandomString(5), $cash->receiver, $cash->montant, $cash->devise, '', '', $callback);
                            $momo = json_decode($momo);
                            if($momo->status == "SUCCESSFUL"){
                                
                                $cash->payment_id = $momo->financialTransactionId;
                                $cash->status = $momo->status;
                                $cash->payer_id = $momo->payer->partyId;
                                $cash->save();
                            }
                        break;
                    }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function updateWalletIfTransactionSuccedd($cash_in_out_id){
        DB::beginTransaction();
        $cash = CashInsOut::find($cash_in_out_id);
        if(!$cash){
            $err['errNo'] = 15;
            $err['errMsg'] = "operation reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        if($cash->status != "pending"){
            $err['errNo'] = 15;
            $err['errMsg'] = "request can't be vakidate";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));
            $client = new PayPalHttpClient($environment);
            $request = new PayoutsGetRequest($cash->payment_id);
            $response = $client->execute($request);
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }


    /**
     * callback third-party en cas de success
     * @param $cash_in_out_id
     * @param $request
     * @param $type
     */
    public static function cashInOutSuccess($cash_in_out_id, $request, $type)
    {
        try {
            switch ($type) {
                case 'paypal':
                    $gateway = Omnipay::create('PayPal_Rest');
                    $gateway->initialize(array(
                        "clientId" => env('PAYPAL_CLIENT_ID'),
                        "secret" => env('PAYPAL_CLIENT_SECRET'),
                        "testMode" => true
                    ));

                    if ($request['paymentId'] && $request['PayerID']) {

                        
                        $transaction = $gateway->completePurchase(array(
                            'payer_id'             => $request['PayerID'],
                            'transactionReference' => $request['paymentId'],
                        ));
                        $response = $transaction->send();
                        
                        if ($response->isSuccessful()) {
                            // The customer has successfully paid.
                            $arr_body = $response->getData();
                            // Insert transaction data into the database
                            $isPaymentExist = CashInsOut::where('id', $cash_in_out_id)->first();
                            
                            if ($isPaymentExist && $isPaymentExist->payment_id == null) {
                                
                                $wallet = Wallet::find($isPaymentExist->wallets_id);
                                if (!$wallet) {
                                    $err['errNo'] = 15;
                                    $err['errMsg'] = "wallet reference to  not found";
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }
                                $wallet->fill([
                                    "solde" => $wallet->solde + $isPaymentExist->montant,
                                    "updated_at" => DateMethods::getCurrentDateInt()
                                ]);
                                $wallet->save();

                                $isPaymentExist->payment_id = $arr_body['id'];
                                $isPaymentExist->payer_id = $arr_body['payer']['payer_info']['payer_id'];
                                $isPaymentExist->payer_email = $arr_body['payer']['payer_info']['email'];
                                $isPaymentExist->status = $arr_body['state'];
                                $isPaymentExist->save();
                            } else {
                                $err['errNo'] = 15;
                                $err['errMsg'] = "transaction cash-in/cash-out reference to  not found";
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return $error;
                            }

                            $success['status'] =  "OK";
                            $success['data'] = $isPaymentExist;
                            return $success;
                        } else {
                            $err['errNo'] = 14;
                            $err['errMsg'] = $response->getMessage();
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    }

                break;
               
            }
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * callback third-party en cas d'erreur
     * @param $cash_in_out_id
     * @param $request
     * @param $type
     */
    public static function cashInOutError($cash_in_out_id, $request, $type)
    {
        try {
            switch ($type) {
                case 'paypal':
                    $isPaymentExist = CashInsOut::where('id', $cash_in_out_id)->first();
                    if ($isPaymentExist) {
                        $isPaymentExist->status = "echec";
                        $isPaymentExist->save();
                    } else {
                        $err['errNo'] = 15;
                        $err['errMsg'] = "transaction cash-in/cash-out reference to  not found";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }

                    $err['errNo'] = 30;
                    $err['errMsg'] = "paiement non accepté";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;

                    break;
            }
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function w_transaction($wallet_id_source, $wallet_id_destination, $transaction, $type){
        DB::beginTransaction();
        $wallet_source = Wallet::find($wallet_id_source);
        if(!$wallet_source){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet source reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $wallet_destination = Wallet::find($wallet_id_destination);
        if(!$wallet_destination){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet destination reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $transaction['wallets_source_id'] = $wallet_id_source;
        $transaction['wallets_destination_id'] = $wallet_id_destination;
        $transaction['devise_source'] = $wallet_source->devise;
        $transaction['devise_destination'] = $wallet_destination->devise;
        $transaction['type'] = $type;
        $transaction['date_transaction'] = DateMethods::getCurrentDateInt();
        try {
            $transaction_saved = null;
            if($wallet_source->devise === $wallet_destination->devise){
                
                if($wallet_source->solde < $transaction['montant']){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "pas assez d'argent dans le wallet source";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $wallet_source->solde = $wallet_source->solde - $transaction['montant'];
                $wallet_source->save();

                $wallet_destination->solde = $wallet_destination->solde + $transaction['montant'];
                $wallet_destination->save();
                
                $transaction['frais'] = 0;
                $transaction['taux_change'] = 0;
                $transaction['status'] = "complete";
                
                $transaction_saved = WTransaction::create($transaction);

            }else{

                $frais = WalletsMethods::getFrais($transaction['montant']);

                if($wallet_source->solde < ($transaction['montant'] + $frais)){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "pas assez d'argent dans le wallet source";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $req_url = "https://api.exchangerate.host/convert?from={$wallet_source->devise}&to={$wallet_destination->devise}";
                $response_json = file_get_contents($req_url);
                if(false === $response_json){

                    $err['errNo'] = 15;
                    $err['errMsg'] = "erreur taux de change";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $response = json_decode($response_json);
                if($response->success === false){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "taux de change erreur de conversion";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $taux = $response->result;
                if(!$taux){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "une des devises utilisées est incorrect";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($type === "wdecaissement"){
                    $wallet_source->solde = $wallet_source->solde - $transaction['montant'];
                    $wallet_source->updated_at = DateMethods::getCurrentDateInt();
                    $wallet_source->save();
    
                    $wallet_destination->solde = $wallet_destination->solde + ( ($transaction['montant'] - $frais) * $taux);
                    $wallet_destination->updated_at = DateMethods::getCurrentDateInt();
                    $wallet_destination->save();
                }else{
                    $wallet_source->solde = $wallet_source->solde - ($transaction['montant'] + $frais);
                    $wallet_source->updated_at = DateMethods::getCurrentDateInt();
                    $wallet_source->save();
    
                    $wallet_destination->solde = $wallet_destination->solde + ( $transaction['montant'] * $taux);
                    $wallet_destination->updated_at = DateMethods::getCurrentDateInt();
                    $wallet_destination->save();
                }
                
                
                $transaction['frais'] = $frais;
                $transaction['taux_change'] = $taux;
                $transaction['status'] = "complete";
                
                $transaction_saved = WTransaction::create($transaction);
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $transaction_saved
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }

    public static function woperation($operation_id){
        DB::beginTransaction();
        $operation = OperationMethods::getById($operation_id);
        
        if($operation == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "operation not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $member = MembreMethods::getById($operation->membres_id_wallet);
        if($member == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "member not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $association = AssociationMethods::getById($member->associations_id);
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $u_wallet = WalletsMethods::getWalletByUWallet($member->default_u_wallets_id);
        if($u_wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "u_wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $a_wallet = WalletsMethods::getWalletByAWallet($association->a_wallets_id);
        if($a_wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "a_wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            if($operation->debit_credit == "credit")
            {

                $u_wallet->solde = $u_wallet->solde + $u_wallet->transit;
                $u_wallet->transit = 0;
                $u_wallet->save();

                $transaction =  array(
                    "montant" => $operation->montant
                );

                $transaction['details'] = "Cotisation du Wallet " . $u_wallet->nom . " (" . $operation->montant . " " . $u_wallet->devise . ") " . "Vers Le Wallet " . $a_wallet->nom;
                $wtransaction = WalletsMethods::w_transaction($u_wallet->id, $a_wallet->id, $transaction, "wcotisation");

            }
            else
            {
                $a_wallet->solde = $a_wallet->solde + $a_wallet->transit;
                $a_wallet->transit = 0;
                $a_wallet->save();

                $transaction =  array(
                    "montant" => min($operation->montant, $a_wallet->solde)
                );
                $transaction['details'] = "Decaissement du Wallet " . $a_wallet->nom . " (" . $operation->montant . " " . $a_wallet->devise . ") " . "Vers Le Wallet " . $u_wallet->nom;
                $wtransaction = WalletsMethods::w_transaction($a_wallet->id, $u_wallet->id, $transaction, "wdecaissement");
            }
            DB::commit();
            return $wtransaction;

        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        

        

    }


    public static function woperationreject($operation_id){
        DB::beginTransaction();
        $operation = OperationMethods::getById($operation_id);
        
        if($operation == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "operation not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $member = MembreMethods::getById($operation->membre_id);
        if($member == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "member not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $association = AssociationMethods::getById($member->associations_id);
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $u_wallet = WalletsMethods::getWalletByUWallet($member->default_u_wallets_id);
        if($u_wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "u_wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $a_wallet = WalletsMethods::getWalletByAWallet($association->a_wallets_id);
        if($a_wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "a_wallet not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            if($operation->debit_credit == "credit")
            {
    
                $u_wallet->solde = $u_wallet->solde + $u_wallet->transit;
                $u_wallet->transit = 0;
                $u_wallet->save();
    
            }
            else
            {
                $a_wallet->solde = $a_wallet->solde + $a_wallet->transit;
                $a_wallet->transit = 0;
                $a_wallet->save();
    
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successful"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        

    }

    public static function preview_w_transaction($wallet_id_source, $wallet_id_destination, $transaction, $type){
        DB::beginTransaction();
        $wallet_source = Wallet::find($wallet_id_source);
        if(!$wallet_source){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet source reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $wallet_destination = Wallet::find($wallet_id_destination);
        if(!$wallet_destination){
            $err['errNo'] = 15;
            $err['errMsg'] = "wallet destination reference to  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $transaction['wallets_source_id'] = $wallet_id_source;
        $transaction['wallets_destination_id'] = $wallet_id_destination;
        $transaction['devise_source'] = $wallet_source->devise;
        $transaction['devise_destination'] = $wallet_destination->devise;
        $transaction['type'] = $type;
        $transaction['date_transaction'] = DateMethods::getCurrentDateInt();
        try {
        
            $frais = WalletsMethods::getFrais($transaction['montant']);

            if($wallet_source->solde < ($transaction['montant'] + $frais)){
                $err['errNo'] = 15;
                $err['errMsg'] = "pas assez d'argent dans le wallet source";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $req_url = "https://api.exchangerate.host/convert?from={$wallet_source->devise}&to={$wallet_destination->devise}";
            $response_json = file_get_contents($req_url);
            if(false === $response_json){

                $err['errNo'] = 15;
                $err['errMsg'] = "erreur taux de change";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $response = json_decode($response_json);
            if($response->success === false){
                $err['errNo'] = 15;
                $err['errMsg'] = "taux de change erreur de conversion";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $taux = $response->result;
            if(!$taux){
                $err['errNo'] = 15;
                $err['errMsg'] = "une des devises utilisées est incorrect";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            
            $transaction['frais'] = $frais;
            $transaction['taux_change'] = $taux;
            $transaction['status'] = "complete";
            $montant_recevoir = $transaction['montant'] * $taux;
            $transaction_saved = array(
                "taux_change" => $taux,
                "frais" => $frais,
                "montant_transfere" => $transaction['montant'],
                "montant_a_recevoir" => $transaction['montant'] * $taux,
                "solde_wallet_source" => $wallet_source->solde - ($transaction['montant'] + $frais),
                "solde_wallet_destination" => $wallet_destination->solde + $montant_recevoir,
                
            );
        
            DB::commit();
            return array(
                "status" => "OK",
                "data" => $transaction_saved
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }

    public static function getFrais($montant){
        return $montant * 1 / 100;
    }

    /**
     * payer facture
     */
    public static function payerFacture($association, $facture){
        $wallet = WalletsMethods::getTWalletByCurrency($association->devise);
        if($wallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "Tontine Wallet reference to not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $awallet = WalletsMethods::getWalletByAWallet($association->a_wallets_id);
        if($awallet == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "Association Wallet reference to not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $montant = $facture->montant;
        $transaction =  array(
            "montant" => $montant
        );
        $transaction['details'] = "Paiement Facture du Wallet " . $awallet->nom . " (" . $montant . " " . $awallet->devise . ") " . "Vers Le Wallet " . $wallet->nom;
        $wtransaction = WalletsMethods::w_transaction($awallet->id, $wallet->id, $transaction, "wpaiement");
        
        return $wtransaction;
    }


}
