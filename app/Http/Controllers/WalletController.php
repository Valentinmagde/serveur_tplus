<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\WalletsMethods;
use App\CustomModels\AssociationMethods;
use Validator;

class WalletController extends Controller
{
    //

    /**
     * récupération de tout les wallets d'un utilisateur
     */
    public function getUWallet($user_id){
        $wallet = WalletsMethods::getAllUwallet($user_id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }

     /**
     * récupérationuwallet par user et id
     */
    public function getUwalletByWalletId($user_id, $id){
        $wallet = WalletsMethods::getUwalletByWalletId($user_id, $id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }


    public function getAwalletByIdAndAssocId($assocId, $id){
        $wallet = WalletsMethods::getAwalletByIdAndAssocId($assocId, $id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }

    /**
     * delete wallet
     */

    public function deleteWallet($wallet_id){
        $wallet = WalletsMethods::deletewallet($wallet_id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }


    public function setDefaultUWallet($user_id, $u_wallet_id, $assocId){
        $wallet = WalletsMethods::setDefaultUWallet($u_wallet_id, $user_id, $assocId);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }


    /**
     * creation d'un wallet de tout type
     */
    public function storeWallet($type, Request $request){
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'devise' => 'required|string',
        ]);

        $validator->sometimes('user_id', 'required', function ($type) {
            return $type == "u-wallet";
        });

          //Returns an error if a field is not filled
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        switch ($type) {
            case 'u-wallet':
                
                $all = $request->all();
                $user_id = $all['user_id'];
                unset($all['user_id']);
                $wallet = WalletsMethods::storeUWallet($all, $type, $user_id);
            break;
            
            case "a-wallet":
                $all = $request->all();
                $wallet = WalletsMethods::storeAWallet($all, $type);
            break;
            

            case "t-wallet":
                $all = $request->all();
                $wallet = WalletsMethods::storeTWallet($all, $type);
            break;
        }

        if($wallet){
            if ($wallet['status'] == 'OK') {
                return response()->json($wallet, 201);
            } else if ($wallet['data']['errNo'] == 15) {
                return response()->json($wallet, 404);
            } else {
                return response()->json($wallet, 500);
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "type de wallet '$type' pas pris en charge";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

     /**
     * creation d'un wallet de tout type
     */
    public function updateWallet($wallet_id, Request $request){
        $wallet = WalletsMethods::updateWallet($wallet_id, $request->all());
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
       
    }



    /**
     * faire une opération de cash-in ou de cash-out
     */
    public function cashInOut($type, Request $request){
        $validator = Validator::make($request->all(), [
            'in_out' => 'required|string',
            'montant' => 'required',
            'devise' => 'required|string',
            'wallets_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        // dd($request->all());
        $card = $request->input('card') ?? null;
        if($card != null){
            if(is_string($card)){
                $card = (array) json_decode($card);
            }
        }

        $wallet = WalletsMethods::cashInOut($request->all(), $type, $card);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }

    }

    public function getCashInOut($wallet_id){
        
        $wallet = WalletsMethods::getCashInOut($wallet_id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 200);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }

    }

    public function getWTransactions($wallet_id){
      
        $wallet = WalletsMethods::getWTransactions($wallet_id);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 200);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }

    }

    public function getWTransactionsWalletAssociation($wallet_id, $assocId){
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $wallet = WalletsMethods::getWTransactionsWalletAssociation($wallet_id, $association);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 200);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }

    }

    /** 
     * cash-in / cash-out success
     */
    public function cashInOutSuccess($cash_id, $type, Request $request){
        $wallet = WalletsMethods::cashInOutSuccess($cash_id, $request->all(), $type);
        $link = $request->query->all();
        if ($wallet['status'] == 'OK') {
            return view('transaction')->with('success', true)->with('link', $link['link'] ?? '');
        } else if ($wallet['data']['errNo'] == 15) {
            return view('transaction')->with('success', false)->with('link', $link['link'] ?? '');
        } else {
            return view('transaction')->with('success', false)->with('link', $link['link'] ?? '');
        }
    }

      /** 
     * cash-in / cash-out success
     */
    public function cashInOutError($cash_id, $type, Request $request){
        $wallet = WalletsMethods::cashInOutError($cash_id, $request->all(), $type);
        $link = $request->query->all();
        if ($wallet['status'] == 'OK') {
            return view('transaction')->with('success', true)->with('link', $link['link']);
        } else if ($wallet['data']['errNo'] == 15) {
            return view('transaction')->with('success', false)->with('link', $link['link']);
        } else {
            return view('transaction')->with('success', false)->with('link', $link['link']);
        }
    }

    /**
     * w-transactions
     */
    public function WTransaction($wallets_id_source, $wallets_id_destination, $type, Request $request){
        $wallet = WalletsMethods::w_transaction($wallets_id_source, $wallets_id_destination,$request->all(), $type);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }

    public function PreviewWTransaction($wallets_id_source, $wallets_id_destination, $type, Request $request){
        $wallet = WalletsMethods::preview_w_transaction($wallets_id_source, $wallets_id_destination,$request->all(), $type);
        if ($wallet['status'] == 'OK') {
            return response()->json($wallet, 201);
        } else if ($wallet['data']['errNo'] == 15) {
            return response()->json($wallet, 404);
        } else {
            return response()->json($wallet, 500);
        }
    }
}
