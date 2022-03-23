<?php

namespace App\CustomModels;

use App\Models\Virement;
use Illuminate\Support\Facades\DB;

class VirementMethods{

    /**
     * acquitement des frais
     */
    public static function acquitement($compte_id, $activite_id, $montant, $created_by){

        $compte = CompteMethods::getById($compte_id);

        if($compte != "not found"){


            $activite = ActiviteMethods::getActivityById($activite_id);

            if($activite != "not found"){
                DB::beginTransaction();
                try {
                    
                    $compte->fill([
                        'solde_anterieur'=> $compte->solde,
                        'solde'=> $compte->solde - $montant
                        ]);
                    $compte->save();

                    $activite->fill(['caisse'=> $activite->caisse + $montant]);
                    $activite->save();
                    
                    $virement = Virement::create([
                        'comptes_id' => $compte_id,
                        'activites_id' => $activite_id,
                        'montant' => $montant,
                        'type' => 'acquitement',
                        'created_at' => DateMethods::getCurrentDateInt(),
                        'created_by' => $created_by
                    ]);

                    DB::commit();

                    $success['status'] = 'OK';
                    $success['data'] = $virement;
                    
                    return $success;


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
            $err['errMsg'] = "activity {$activite_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;

        }

        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$compte_id} doesn't exist";
        $error['status'] = 'NOK';
        $error['data'] = $err;

        return $error;

    }

    /**
     * attribution
     */
    public static function attribution($compte_id, $activite_id, $montant, $created_by){
        $compte = CompteMethods::getById($compte_id);

        if($compte != "not found"){


            $activite = ActiviteMethods::getActivityById($activite_id);

            if($activite != "not found"){
                DB::beginTransaction();
                try {
                    
                    $compte->fill([
                        'solde_anterieur'=> $compte->solde,
                        'solde'=> $compte->solde + $montant
                        ]);
                    $compte->save();

                    $activite->fill(['caisse'=> $activite->caisse - $montant]);
                    $activite->save();


                    $virement = Virement::create([
                        'comptes_id' => $compte_id,
                        'activites_id' => $activite_id,
                        'montant' => $montant,
                        'type' => 'attribution',
                        'created_at' => DateMethods::getCurrentDateInt(),
                        'created_by' => $created_by
                    ]);

                    DB::commit();

                    $success['status'] = 'OK';
                    $success['data'] = $virement;
                    
                    return $success;


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
            $err['errMsg'] = "activity {$activite_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;

        }

        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$compte_id} doesn't exist";
        $error['status'] = 'NOK';
        $error['data'] = $err;

        return $error;

    }
}