<?php 

namespace App\CustomModels;

use App\Models\Transfert;
use Illuminate\Support\Facades\DB;

class TransfertMethods{

    public static function transfert($expediteur_id, $recepteur_id, $montant, $libelle = " ", $created_by){
        $expediteur = CompteMethods::getById($expediteur_id);

        if($expediteur != "not found"){


            $recepteur = CompteMethods::getById($recepteur_id);

            if($recepteur != "not found"){
                DB::beginTransaction();
                try {
                    
                   if($expediteur->solde >= $montant){
                        unset($expediteur->membre);
                        $expediteur->fill([
                            'solde_anterireur'=> $expediteur->solde,
                            'solde'=> $expediteur->solde - $montant
                            ]);
                        $expediteur->save();

                        unset($recepteur->membre);
                        $recepteur->fill([
                            'solde_anterieur'=> $recepteur->solde,
                            'solde'=> $recepteur->solde + $montant
                            ]);
                        $recepteur->save();


                        $transfert = Transfert::create([
                            'expediteur' => $expediteur_id,
                            'recepteur' => $recepteur_id,
                            'libelle' => $libelle,
                            'montant' => $montant,
                            'date_created' => DateMethods::getCurrentDateInt(),
                            'created_by' => $created_by,
                            'created_at' => DateMethods::getCurrentDateInt()
                        ]);

                        DB::commit();


                        $success['status'] = 'OK';
                        $success['data'] = $transfert;
                        
                        return $success;
                   }else{
                    DB::rollback();
                    $err['errNo'] = 10;
                    $err['errMsg'] = "le solde de l'expéditeur est insuffisant";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
        
                    return $error;
                   }

                } catch (\Exception $e) {
                    DB::rollback();
                    $err['errNo'] = 10;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                
                    return $error;
                }

            }

            $err['errNo'] = 15;
            $err['errMsg'] = "compte {$recepteur_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;

        }

        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$expediteur_id} doesn't exist";
        $error['status'] = 'NOK';
        $error['data'] = $err;

        return $error;

    }

}