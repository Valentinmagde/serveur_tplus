<?php

namespace App\CustomModels;

use App\Models\Facture;
use Illuminate\Support\Facades\DB;

class FactureMethods{


    public static function create($cycle_id){
        $cycle = CycleMethods::getById($cycle_id);

        if($cycle != "not found"){
            $assoc = AssociationMethods::getById($cycle->associations_id);

            if($assoc != "not found"){
                $montant = FactureMethods::montantFacture($assoc, $cycle_id);
                try {
                    $date_limite = AgMethods::getDueFirstBillDate($cycle_id);
                    $facture = Facture::create([
                        "cycles_id" => $cycle_id,
                        "statut" => "EN_ATTENTE",
                        "delais_paiement"=> $date_limite ?? 0,
                        "libelle" => "Facture Principal Cycle",
                        "montant" => $montant['cout'],
                        "nb_comptes" => $montant['number'],
                        "periode" => $montant['duree'],
                        "prix_unitaire" => $montant['unit'],
                        "create_at" => DateMethods::getCurrentDateInt()
                    ]);

                    $success['status'] = "OK";
                    $success['data'] = $facture;

                    return $success;
                } catch (\Exception $e) {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;  
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }

    public static function createOther($cycle_id, $ag_id, $number){
        $cycle = CycleMethods::getById($cycle_id);

        if($cycle != "not found"){
            $assoc = AssociationMethods::getById($cycle->associations_id);

            if($assoc != "not found"){
                $montant = FactureMethods::montantFactureOther($assoc, $cycle_id, $number);
                try {
                    $date_limite = AgMethods::getDueSecondBillDate($ag_id);
                    $facture = Facture::create([
                        "cycles_id" => $cycle_id,
                        "statut" => "EN_ATTENTE",
                        "delais_paiement"=> $date_limite ?? 0,
                        "libelle" => "Facture Secondaire Cycle",
                        "montant" => $montant['cout'],
                        "nb_comptes" => $montant['number'],
                        "periode" => $montant['duree'],
                        "prix_unitaire" => $montant['unit'],
                        "create_at" => DateMethods::getCurrentDateInt()
                    ]);

                    $success['status'] = "OK";
                    $success['data'] = $facture;

                    return $success;
                } catch (\Exception $e) {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;  
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }

    public static function getByCycleId($cycle_id){
        $facture = Facture::where('cycles_id', $cycle_id)->get();
        return $facture;
    }

    public static function getByCycleIdNotDue($cycle_id){
        $facture = Facture::where('cycles_id', $cycle_id)->where('statut', 'EN_ATTENTE')->get();
        return $facture;
    }

    public static function getByCycleIdAndId($cycle_id, $id){
        $facture = Facture::where('cycles_id', $cycle_id)->where("id", $id)->first();
        return $facture;
    }


    public static function getById($id){
        $facture = Facture::find($id);

        if($facture) return $facture;

        return "not found";
    }

    public static function getAssociationFactureNotDue($cycle_id){

        $facture = Facture::where('cycles_id', $cycle_id)
                            ->where('status', 'EN_ATTENTE')
                            ->get();

        if($facture)
        {
            $success['status'] = "OK";
            $success['data'] = $facture;

            return $success;
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'facture doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }


    public static function getAllFacture($association_id){
        $cycles = CycleMethods::getByIdAssociation($association_id);
        $factures = array();

        foreach ($cycles as $key => $cycle) {
            $facture = FactureMethods::getByCycleId($cycle->id);
            $factures[] = $facture;
        }

        $success['status'] = "OK";
        $success['data'] = $factures;

        return $success;
    }


    public static function buyFacture($assoc, $cycle_id, $id){

        $cycle = CycleMethods::getById($cycle_id);
        if($cycle == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }

        if($cycle->associations_id !== $assoc->id){
            $err['errNo'] = 15;
            $err['errMsg'] = 'the cycle and the association given doesn\'t match';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error; 
        }


        $facture = FactureMethods::getByCycleIdAndId($cycle_id, $id);
        if($facture && $facture->statut != "PAYE"){
            try {
                
                $paiement = WalletsMethods::payerFacture($assoc, $facture);
                if($paiement['status'] == "NOK"){
                    $err['errNo'] = $paiement['data']['errNo'];
                    $err['errMsg'] = $paiement['data']['errMsg'];
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $facture->fill([
                    "date_paye" => DateMethods::getCurrentDateInt(),
                    "statut" => "PAYE",
                    "update_at" => DateMethods::getCurrentDateInt()
                ]);

                $facture->save();
                $success['status'] = "OK";
                $success['data'] = "paiement successful";
    
                return $success;
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'facture doesn\'t exist or is already buyed';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }


    public static function applyCoupon($assoc, $cycle_id, $facture_id, $coupon){
        DB::beginTransaction();
        $cycle = CycleMethods::getById($cycle_id);
        if($cycle == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }

        if($cycle->associations_id !== $assoc->id){
            $err['errNo'] = 15;
            $err['errMsg'] = 'the cycle and the association given doesn\'t match';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error; 
        }


        $facture = FactureMethods::getByCycleIdAndId($cycle_id, $facture_id);
        if($facture && $facture->statut != "PAYE"){
            try {

                $coup = CouponMethods::getCouponByCode($coupon);
                if($coup === null){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "coupon not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($coup->type == "pourcentage"){
                    $montant = ($facture->montant * $coup->pourcentage)/100;
                    $facture->reduction = $montant;
                    $facture->montant = $facture->montant - $montant;
                    $facture->code_promo = $coupon;
                }else{
                    $facture->montant = $facture->montant - $coup->montant;
                    $facture->reduction = $coup->montant;
                    $facture->code_promo = $coupon;
                }
                    
                $facture->save();
                CouponMethods::deleteCoupon($coup);

                DB::commit();             
                $success['status'] = "OK";
                $success['data'] = "reduction effectué avec success";

                return $success;

            } catch (\Exception $e) {
                DB::rollback();
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'facture doesn\'t exist or is already buyed';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }

    }


    public static function montantFacture($assoc, $cycle_id){

        $membres = MembreMethods::getByAssociationId($assoc->id);
        $length = AgMethods::getCycleEffectiveLength($cycle_id);
        $number = count($membres);
        $cout = 0;
        $unit = 0;
        switch($assoc->devise){
            case "XAF":
                $cout = $number * 200;
                $unit = 200;
            break;
            case "USD":
            case "CAD":
            case "EUR":
                $cout = $number * 1;
                $unit = 1;
            break;
        }

        return array(
            "cout" => $cout * $length,
            "number" => $number,
            "duree" => $length,
            "unit" => $unit
        );

    }

    public static function montantFactureOther($assoc, $cycle_id, $number){

        $length = AgMethods::getCycleEffectiveLength($cycle_id);
        $unit = 0;
        $cout = 0;
        switch($assoc->devise){
            case "XAF":
                $cout = $number * 200;
                $unit = 200;
            break;
            case "USD":
            case "CAD":
            case "EUR":
                $cout = $number * 1;
                $unit = 1;
            break;
        }

        return array(
            "cout" => $cout * $length,
            "number" => $number,
            "duree" => $length,
            "unit" => $unit,
        );

    }

}