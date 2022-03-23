<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\CustomModels\CouponMethods;

class CouponController extends Controller
{
    //
    /**
     * récupération de tout les coupons
     */
    public function getAllCoupons(){
        $coupons = CouponMethods::getAllCoupons();
        return response()->json($coupons, 200);
    }

    /**
     * récupération des coupons encores actifs
     */
    public function getAllActiveCoupons(){
        $coupons = CouponMethods::getAllValidCoupon();
        return response()->json($coupons, 200);
    }

    /**
     * mise à niveau des coupons et suppression de celles qui ne sont plus active
     */
    public function miseANiveauCoupons(){
        $coupons = CouponMethods::miseANiveauCoupons();
        if($coupons['status'] == "OK")
            return response()->json($coupons, 200);
        else
            return response()->json($coupons, 500);
    }

    /**
     * creation de coupons
     */
    public function storeCoupon(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'valeur' => 'required',
            'date_limite' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $coupons = CouponMethods::generateCoupon($request->input('type'), $request->input('valeur'),  $request->input('date_limite'));
        if($coupons['status'] == "OK")
            return response()->json($coupons, 200);
        else
            return response()->json($coupons, 500);
    }
}
