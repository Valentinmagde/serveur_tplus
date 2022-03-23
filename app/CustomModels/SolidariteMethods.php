<?php

namespace App\CustomModels;

use App\Models\TypeAssistance;
use App\Models\Assistance;
use App\Models\Solidarite;
use App\Models\Echeancier;
use Illuminate\Support\Facades\DB;

class SolidariteMethods{

    /**
     * get all type assistances of association
     */
    public static function indexTypeAssistances($assocId){
        $association = AssociationMethods::getById($assocId);
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association $assocId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $tp = TypeAssistance::where('associations_id', $assocId)->get();
        return array(
            "status" => "OK",
            "data" => $tp
        );
    }

    /**
     * store type assistance
     */
    public static function storeTypeAssistance($assocId, $type_assistance){
        $association = AssociationMethods::getById($assocId);
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association $assocId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $type_assistance['associations_id'] = $assocId;
        if(!array_key_exists("max", $type_assistance) || $type_assistance['max'] === ""){
            $err['errNo'] = 15;
            $err['errMsg'] = "max column is require";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        if( !array_key_exists('max_cycle', $type_assistance) || $type_assistance['max_cycle'] === ""){
            $err['errNo'] = 15;
            $err['errMsg'] = "max_cycle column is require";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $type = TypeAssistance::create($type_assistance);

            return array(
                "status" => "OK",
                "data" => $type
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
     * update type d'assistance
     */
    public static function updateTypeAssistance($typeId, $type_assistance){

        try {
            $type = TypeAssistance::find($typeId);
            if($type){
                $type->fill($type_assistance);
                $type->save();
                return array(
                    "status" => "OK",
                    "data" => $type
                );
            }

            $err['errNo'] = 15;
            $err['errMsg'] = "type assistance $typeId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;

        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }

    }

    /**
     * delete type assistance
     */
    public static function deleteType($typeId){
        try {
            $type = TypeAssistance::find($typeId);
            if($type){
                $type->delete();
                return array(
                    "status" => "OK",
                    "data" => "successfully deleted"
                );
            }

            $err['errNo'] = 15;
            $err['errMsg'] = "type assistance $typeId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;

        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }


    /**
     * store assistance
     */
    public static function storeAssistance($activite_id, $membres_id, $assistance){

        
        $assistance['solidarites_activites_id'] = $activite_id;
        $assistance['membres_id'] = $membres_id;
        $assistance['date_created'] = DateMethods::getCurrentDateInt();
        $assistance['etat'] = "init";

        $membre = MembreMethods::getById($membres_id);
        if($membre == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "membre $membres_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $cycle = CycleMethods::checkActifCycle($membre->associations_id);
        if($cycle == "not found")
        {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = "actif cycle not found in association $membre->associations_id";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $activite = ActiviteMethods::getActivityById($assistance['solidarites_activites_id']);
        if($activite == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$assistance['solidarites_activites_id']} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $solidarite = Solidarite::find($assistance['solidarites_id']);
        if(!$solidarite){
            $err['errNo'] = 15;
            $err['errMsg'] = "activity solidarity {$assistance['solidarites_id']} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $typeAss = TypeAssistance::find($assistance['type']);
        if($typeAss){
            $count = Assistance::where('type', $assistance['type'])->where("membres_id", $membres_id)->count();
            if($typeAss->max > 0 && $count > $typeAss->max){
                $err['errNo'] = 14;
                $err['errMsg'] = "maximum d'assistance atteint pour ce type d'assistance $typeAss->nom";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $firstAg = AgMethods::getFirstAg($cycle->id);
            $lastAg = AgMethods::getLatestAg($cycle->id);
            if($firstAg != "not found" && $lastAg != "not found"){
                $allAssist = Assistance::where("membres_id", $membres_id)
                                        ->where("type", $assistance['type'])
                                        ->get();
                $isIn = 0;
                foreach ($allAssist as $key => $value) {
                    if($value->date_evenement >=$firstAg->date_ag || $value->date_evenement <= $lastAg->date_ag) $isIn++;
                }
                if($isIn > $typeAss->max_cycle){
                    $err['errNo'] = 14;
                    $err['errMsg'] = "maximum d'assistance atteint pour ce type d'assistance $typeAss->nom dans ce cycle";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }
        }


        DB::beginTransaction();
        try {
            $assist = Assistance::create($assistance);

            $compte = CompteMethods::getByIdMA($assist->solidarites_activites_id, $membres_id);
            if($compte == "not found"){
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = "compte not found in activity $assist->solidarites_activites_id from member $membres_id";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $ag = AgMethods::getNextAg($cycle->id);
            if($ag == "not found"){
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = "no next general assembly found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $echeancier = array(
                "date_limite" => $ag->date_ag,
                "montant" => $assist->montant_alloue,
                "etat" => "init",
                "date_created" => DateMethods::getCurrentDateInt(),
                "libelle" => "Decaissement - ".$activite->nom." - $typeAss->nom - $membre->firstName $membre->lastName",
                "membres_id" => $compte->membres_id,
                "comptes_id" => $compte->id,
                "debit_credit" => "decaissement",
                "serie" => "assistance-$assist->id"
            );

            $echeance = Echeancier::create($echeancier);
        
            $assist->fill([
                'echeances_id' => $echeance->id
            ]);
            $assist->save();

            $assist['encaissement'] = $echeance;
            $ags = AgMethods::getAllNextAgsAfterCurrent($cycle->id, $ag);
            $comptes = CompteMethods::getAll($activite->id);
            $echeances = array();
            foreach ($comptes['data'] as $key => $compte) {
                if($compte->membres_id != $membres_id){
                    $echeancier = array(
                        "date_limite" => $ags[$solidarite->delai_mise_a_niveau - 1]->date_ag ?? 0,
                        "montant" => $assist->montant_alloue/(count($comptes['data'])-1),
                        "etat" => "init",
                        "date_created" => DateMethods::getCurrentDateInt(),
                        "libelle" => "Cotisation - ".$activite->nom." - $typeAss->nom - $membre->firstName $membre->lastName",
                        "membres_id" => $compte->membres_id,
                        "comptes_id" => $compte->id,
                        "debit_credit" => "cotisation",
                        "serie" => "assistance-$assist->id",
                        "next_date_in" => $solidarite->delai_mise_a_niveau - count($ags)
                    );
                    $echeance = Echeancier::create($echeancier);
    
                    $echeances[] = $echeance;
                }
            }

            $assist['versements'] = $echeances;

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $assist
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

    public static function storeAssistancePast($activite_id, $membres_id, $assistances){

        
       
        $membre = MembreMethods::getById($membres_id);
        if($membre == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "membre $membres_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
        DB::beginTransaction();
        try {
            foreach ($assistances as $key => $assistance) {
                $assistance['solidarites_activites_id'] = $activite_id;
                $assistance['membres_id'] = $membres_id;
                $assistance['date_created'] = DateMethods::getCurrentDateInt();
                $assistance['etat'] = "past";
                $activite = ActiviteMethods::getActivityById($assistance['solidarites_activites_id']);
                if($activite == "not found"){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "activity {$assistance['solidarites_activites_id']} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
        
                $solidarite = Solidarite::find($assistance['solidarites_id']);
                if(!$solidarite){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "activity solidarity {$assistance['solidarites_id']} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $assist = Assistance::create($assistance);
            }
           

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $assist
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
     * index Assitances d'une activite
     */
    public static function indexAssistance($activite_id){
        $assists = Assistance::where('solidarites_activites_id', $activite_id)->get();
        foreach ($assists as $key => $assist) {
            $membre = MembreMethods::getById($assist->membres_id);
            if($membre != "not found"){
                $assist['membre'] = "$membre->firstName $membre->lastName";
            }
        }
        return array(
            "status" => "OK",
            "data" => $assists
        );
    }


    public static function deleteAssistance($assistance_id){
        $assist = Assistance::find($assistance_id);
        if(!$assist){
            $err['errNo'] = 15;
            $err['errMsg'] = "aassistance {$assistance_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        
        DB::beginTransaction();
        try {
            $echeancier = Echeancier::where('serie', "assistance-$assist->id")->delete();

            $assist->delete();

            DB::commit();
            $success['status'] = "OK";
            $success['data'] = "deleted successfully";
            return $success;

        } catch(\Exception $e){
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }





}
