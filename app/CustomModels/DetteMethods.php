<?php

namespace App\CustomModels;

use App\Models\Dette;

class DetteMethods{

    /**
     * creation d'une dette
     */
    public static function create($compte, $echeance, $type){
      
        Dette::create([
            "comptes_id" => $compte->id,
            "type" => $type,
            "montant" => $echeance->montant - $echeance->montant_realise,
            "date" => DateMethods::getCurrentDateInt(),
            "libelle" => "{$type} activite {$compte->activites_id} du {$echeance->date_limite}",
        ]);


    }

    public static function createWithMontant($compte, $echeance, $montant, $type){
      
        Dette::create([
            "comptes_id" => $compte->id,
            "type" => $type,
            "montant" => $montant,
            "date" => DateMethods::getCurrentDateInt(),
            "libelle" => "{$type} activite {$compte->activites_id} du {$echeance->date_limite}",
        ]);


    }

    /**
     * mise à jour des dettes de cotisations
     */
    public static function traitementDetteCotisation($compte){
        try {
            $montant = $compte->solde - $compte->solde_anterieur;
            if($montant > 0){
                $compte->fill(["solde_anterieur" => $compte->solde_anterieur + min($compte->dette_c, $montant),
                                "dette_c" => $compte->dette_c - min($compte->dette_c, $montant)]);
                $compte->save();
            }
            return $compte;
        } catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        

    }

    /**
     * mise a jour des dettes d'acquitements
     */
    public static function traitementDetteAcquitement($compte, $transaction){
        try {
            $montant = $compte->solde - $compte->solde_anterieur;
            if($montant > 0){
                VirementMethods::acquitement($compte->id, $compte->activites_id, min($montant, $compte->dette_a), $transaction->created_by);
                $compte->fill(["solde_anterieur" => $compte->solde_anterieur + min($compte->dette_a, $montant),
                "dette_a" => $compte->dette_a - min($compte->dette_a, $montant)]);
                $compte->save();
            }
            return $compte;
        } catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

}