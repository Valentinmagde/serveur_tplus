<?php 

namespace App\CustomModels;


use App\Models\Echeancier;
use App\Models\Sanction;
use App\Models\TypeSanction;
use Exception;

class SanctionMethods{

    /**
     * store les sanctions
     */
    public static function storeSanction($assocId, $membres_id, $ags_id,  $montant, $commentaire = "", $type = ""){

        $activite = ActiviteMethods::getActivityCaisse($assocId);
        if($activite == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "activity Administration not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ags = AgMethods::getById($ags_id);
        if($ags == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $compte = CompteMethods::getByIdMA($activite->id, $membres_id);

        if($compte == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "compte not found in activity $activite->id from member $membres_id";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $cycle = CycleMethods::checkActifCycle($assocId);
        if($cycle == "not found")
        {
            $err['errNo'] = 15;
            $err['errMsg'] = "actif cycle not found in association $assocId";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ag = AgMethods::getNextAg($cycle->id);
        if($ag == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "no next general assembly found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        

        try {
            $sanction = Sanction::create([
                'membres_id' => $membres_id,
                'ags_id' => $ags_id,
                'montant' => $montant,
                'commentaire' => $commentaire,
                'type' => $type
            ]);

            $echeancier = array(
                "date_limite" => $ags->date_ag,
                "montant" => $montant,
                "etat" => "init",
                "date_created" => DateMethods::getCurrentDateInt(),
                "libelle" => "Cotisation - ".$activite->nom." - sanction",
                "membres_id" => $compte->membres_id,
                "comptes_id" => $compte->id,
                "debit_credit" => "cotisation",
                "serie" => "sanction-$sanction->id"
            );
            $echeance = Echeancier::create($echeancier);
            
            return array(
                "status" => "OK",
                "data" => $sanction
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
     * delete d'une sanction
     */
    public static function deleteSanction($id){
        
        $sanction = Sanction::find($id);
        if(!$sanction){
            $err['errNo'] = 15;
            $err['errMsg'] = "sanction $id not  found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            Echeancier::where('serie', "sanction-$id")->delete();
            $sanction->delete();

            return array(
                "status" => "OK",
                "data" => "successfull deleted"
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
        
    }


    public static function deleteTypeSanction($assocId, $id){
        
        $sanction = TypeSanction::where('id', $id)->where('associations_id', $assocId)->first();
        if(!$sanction){
            $err['errNo'] = 15;
            $err['errMsg'] = "type sanction $id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            $sanction->delete();

            return array(
                "status" => "OK",
                "data" => "successfull deleted"
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
        
    }

    public static function storeTypeSanction($assocId, $type){
        $assoc = AssociationMethods::getById($assocId);
        if($assoc == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association $assocId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $sanc = TypeSanction::where('associations_id', $assocId)->where('nom', $type['nom'])->first();
        if($sanc){
            if($sanc->montant != $type['montant']){
                $sanc->fill(["montant" => $type['montant']]);
                $sanc->save();
            }
            return array(
                "status" => "OK",
                "data" => $sanc
            );
        }
        $type['associations_id'] = $assocId;
        try {
            $ts = TypeSanction::create($type);

            return array(
                "status" => "OK",
                "data" => $ts
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }

    public static function updateTypeSanction($id, $type){
        $sanc = TypeSanction::find($id);
        if(!$sanc){
            $err['errNo'] = 15;
            $err['errMsg'] = "type sanction $id found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $sanc->fill($type);
            $sanc->save();
            return array(
                "status" => "OK",
                "data" => $sanc
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;  
        }
    }

    public static function indexSanction($ags_id){
        $ags = AgMethods::getById($ags_id);
        if($ags == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $sanctions = Sanction::where("ags_id",$ags_id)->get();

        foreach ($sanctions as $key => $sanction) {
            $membre = MembreMethods::getById($sanction->membres_id);
            if($membre != "not found") $sanction['membre'] = "$membre->firstName $membre->lastName";
        }

        return array(
            "status" => "OK",
            "data" => $sanctions
        );
    }

    public static function indexTypeSanction($assocId){
        $assoc = AssociationMethods::getById($assocId);
        if($assoc == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "association $assocId not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $types = TypeSanction::where('associations_id', $assocId)->get();

        return array(
            "status" => "OK",
            "data" => $types
        );
    }


    /**
     * création de sanction
     */
    public static function createSanctionFermetureAg($assocId, $ags_id){
        try {
            $members = MembreMethods::getMemberAssociation($assocId);
            foreach ($members as $key => $member) {
                $presence = PresenceMethods::getByAgAndMember($ags_id, $member->id);
                if($presence != "not found"){
                    if($presence->status == "absent"){
                        $typeSanction = TypeSanction::where('nom', "Absence")->where('associations_id', $assocId)->first();
                        if($typeSanction){
                            $sanction = SanctionMethods::storeSanction($assocId, $member->id, $ags_id, $typeSanction->montant, "absence à la reunion");
                        }
                    }else if($presence->status == "retard"){
                        $typeSanction = TypeSanction::where('nom', "Retard")->where('associations_id', $assocId)->first();
                        if($typeSanction){
                            $sanction = SanctionMethods::storeSanction($assocId, $member->id, $ags_id, $typeSanction->montant, "retard à la reunion");
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            throw new Exception('aucune Echeance trouvée');
        }
        
    }

}