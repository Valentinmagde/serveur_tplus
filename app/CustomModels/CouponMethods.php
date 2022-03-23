<?php
namespace App\CustomModels;

use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CouponMethods{

    /**
     * get all coupon qui sont encore valid
     */
    public static function getAllValidCoupon(){
        $coupons = Coupon::where('date_limite','>',DateMethods::getCurrentDateInt())->get();
        return array(
            "status" => "OK",
            "data" => $coupons
        );
    }

    /**
     * récupérer tout les coupons en bd
     */
    public static function getAllCoupons(){
        $coupons = Coupon::all();
        return array(
            "status" => "OK",
            "data" => $coupons
        );
    }


    /**
     * get un coupon par son code
     */
    public static function getCouponByCode($coupon){
        $coupon = Coupon::where('code', $coupon)->first();
        if($coupon){
            if($coupon->date_limite < DateMethods::getCurrentDateInt()){
                CouponMethods::deleteCoupon($coupon);
                return null;
            }
            return $coupon;
        }

        return null;
    }

    /**
     * delete coupon a partir de l'objet coupon
     */
    public static function deleteCoupon($coupon){
        return $coupon->delete();
    }

    /**
     * delete d'un coupon par id  
     */ 
    public static function deleteCouponById($id){
        $coupon = Coupon::find($id);
        return $coupon->delete();
    }

    /**
     * générer un coupon avec le pourcentage qu'on veut lui attribuer et de sa date limite de validité
     */
    public static function generateCoupon($type, $valeur, $date_limite){
        do {
            $code = WalletsMethods::generateRandomString(5);
            $exist = CouponMethods::getCouponByCode($code);
        } while ($exist);

        try {
            if($type === "pourcentage"){
                $coupon = Coupon::create(array(
                    "code" => $code,
                    "type" => $type,
                    "pourcentage" => $valeur,
                    "date_limite" => $date_limite,
                    'created_by' => Auth::id(),
                    'created_at' => DateMethods::getCurrentDateInt(),
                ));
            }else{
                $coupon = Coupon::create(array(
                    "code" => $code,
                    "type" => $type,
                    "montant" => $valeur,
                    "date_limite" => $date_limite,
                    'created_by' => Auth::id(),
                    'created_at' => DateMethods::getCurrentDateInt(),
                ));
            }
            

            return array(
                "status" => "OK",
                "data" => $coupon
            );
            
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }

    /**
     * mise a niveau es coupons et suppression de celles qui ont déjà expiré
     */
    public static function miseANiveauCoupons(){
        try {
            Coupon::where('date_limite','<',DateMethods::getCurrentDateInt())->delete();
            return array(
                "status" => "OK",
                "data" => "successfull"
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }

}