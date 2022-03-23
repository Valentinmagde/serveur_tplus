<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\FactureMethods;
use App\CustomModels\AssociationMethods;


class FactureController extends Controller
{
    //

    public function getFactureById($assocId, $id){
        $association = AssociationMethods::getById($assocId);

        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $facture = FactureMethods::getById($id);
        if($facture == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'facture doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }
       
        return response()->json(array('status'=>'OK', 'data'=>$facture), 200);
        

    }

    public function getAllFacture($assocId){
        $association = AssociationMethods::getById($assocId);

        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $facture = FactureMethods::getAllFacture($assocId);

        if($facture['status'] == "OK"){
            return response()->json($facture, 201);
        }if($facture['data']['errNo'] == 15){
            return response()->json($facture, 404);
        }else{
            return response()->json($facture, 500);
        }

    }


    public function getFacture($assocId, $cycle){
        $association = AssociationMethods::getById($assocId);

        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $facture = FactureMethods::getAssociationFactureNotDue($cycle);

        if($facture['status'] == "OK"){
            return response()->json($facture, 201);
        }if($facture['data']['errNo'] == 15){
            return response()->json($facture, 404);
        }else{
            return response()->json($facture, 500);
        }

    }


    public function buyFacture($assocId, $cycle_id, $facture_id, Request $request){
        $association = AssociationMethods::getById($assocId);

        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }


        $facture = FactureMethods::buyFacture($association, $cycle_id,  $facture_id);

        if($facture['status'] == "OK"){
            return response()->json($facture, 201);
        }if($facture['data']['errNo'] == 15){
            return response()->json($facture, 404);
        }else{
            return response()->json($facture, 500);
        }

    }

    public function applyCoupon($assocId, $cycle_id, $facture_id, $coupon){
        $association = AssociationMethods::getById($assocId);

        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }


        $facture = FactureMethods::applyCoupon($association, $cycle_id, $facture_id, $coupon);

        if($facture['status'] == "OK"){
            return response()->json($facture, 201);
        }if($facture['data']['errNo'] == 15){
            return response()->json($facture, 404);
        }else{
            return response()->json($facture, 500);
        }

    }
}
