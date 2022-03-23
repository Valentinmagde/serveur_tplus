<?php


namespace App\CustomModels;


use App\Models\Cycle;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CycleMethods{


    /**
     * fonction permettant de créer un cycle
     * 
     * @param $request qui est l'ensemble des elements du cycle
     */
    public static function createCycle($request){
        DB::beginTransaction();
        try {
            //vérification de la durée
            CycleMethods::cycleTime($request->duree_cycle);
          
            //récupération de l'association
            $association = AssociationMethods::getById($request->assocId);
            if($association == "not found"){
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            $dernierCycle = CycleMethods::getLatestCycle($request->assocId);

            if($dernierCycle){
                    //date actuelle
                    $datactu = Carbon::now();
                    //date de départ
                    $dateDepart = $dernierCycle->date_premiere_assemblee;

                    //durée à rajouter;
                    $duree = $dernierCycle->duree_cycle;

                    //la première étape est de transformer cette date en timestamp
                    $dateDepartTimestamp = $dateDepart;

                    //on calcule la date de fin
                    $dateFin =  strtotime('+'.$duree.' month', $dateDepartTimestamp);
                    
                    //Comparaison de la date de fin du cycle à la date actuelle
                    if($dateFin >= $request->date_premiere_assemblee){
                        $err['errNo'] = 14;
                        $err['errMsg'] = 'The old cycle not yet come to an end';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 400);
                    } 

            }

            //Attribuer tous les paramettres à une variable
            $input = $request->all(); 

            //Attribuer la valeur de l'identifiant de l'association au champ {associations_id} du cycle
            $input['associations_id'] = $request->assocId;
            $input['etat'] = "create";
            //Création du cycle
            $cycle = Cycle::create( $input);
            
            $type = array(
                "nom" => "Retard",
                "montant" => $cycle->sanction_retard,
                "description" => "montant à payer lors d'un retard"
            );
            $typeSanc = SanctionMethods::storeTypeSanction($request->assocId, $type);
            if($typeSanc['status'] == "NOK"){
                DB::rollback();
                $err['errNo'] = $typeSanc['data']['errNo'];
                $err['errMsg'] = $typeSanc['data']['errMsg'];
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $type = array(
                "nom" => "Absence",
                "montant" => $cycle->sanction_abscence,
                "description" => "montant à payer lors d'une abscence"
            );
            $typeSanc = SanctionMethods::storeTypeSanction($request->assocId, $type);
            if($typeSanc['status'] == "NOK"){
                DB::rollback();
                $err['errNo'] = $typeSanc['data']['errNo'];
                $err['errMsg'] = $typeSanc['data']['errMsg'];
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

           

            DB::commit();
            $success['status'] = 'OK'; 
            $success['data'] =  $cycle;

            return response()->json($success,201);


        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
        
    }

    /**
     * update d'un cycle
     */
    public static function updateCycle($cycle_data, $assoc_id, $cycle_id){

        try {
            $association = AssociationMethods::getById($assoc_id);

            if($association == "not found"){
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            $cycle = CycleMethods::getById($cycle_id);
            $cycle_data['update_at'] = DateMethods::getCurrentDateInt();
            $cycle->fill($cycle_data);
            $cycle->save();

            $success['status'] = "OK";
            $success['data'] = $cycle;

            return response()->json($success, 202);

        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * function qui calcul la durée d'un cycle
     * 
     * @param $duree qui est la durée du cycle en question
     */
    public static function cycleTime($duree){
        if($duree > 18){
            $err['errNo'] = 15;
            $err['errMsg'] = 'the duration of the cycle is greater than 18 months';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400); 
        }
    }

    /**
     * function de récupération du cycle précédent de l'association
     */
    public static function getLatestCycle($assocId){
        return Cycle::latest('id')->where("associations_id", $assocId)->first();
    }
    

    /**
     * retrouver un cycle par son ID
     */

     public static function getById($id){
        $cycle = Cycle::find($id);
        if($cycle){
            return $cycle;
        }

        return "not found";
     }

      /**
     * retrouver un cycle par  l'ID de l'association
     */

    public static function getByIdAssociation($id){
        $cycle = Cycle::where('associations_id',$id)->get();
        if($cycle){
            return $cycle;
        }

        return "not found";
     }


    /**
     * fonction qui retourne un cycle par son association
     * 
     * @param $request les données requises
     */
    public static function getCycleByAssociation($request){

        $association = AssociationMethods::getById($request->assocId);
        
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404); 
        } else{
            //Selectionner le cycle de l'association à travers son id
            $cycle = CycleMethods::getById($request->cycleId);

            if($cycle == "not found")
            {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);    
            }

            $success['status'] = 'OK';
            $success['data'] = $cycle;
            return response()->json($success, 200);    
        }
    }

     /**
     * fonction qui retourne un cycle par son association
     * 
     * @param $request les données requises
     */
    public static function getCycleByAssociationId($request){

        $association = AssociationMethods::getById($request->assocId);
        
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404); 
        } else{

            $cycle = CycleMethods::getByIdAssociation($request->assocId);

            if($cycle == "not found")
            {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);    
            }
           
            $data = array();
            $i = 0;
            foreach($cycle as $key => $value) { 
                $ags=AgMethods::getByIdCycle($value->id);
                $data[$i]['cycle'] = $value;
                $data[$i]['ag'] = $ags;

                $i++;
            
            }

            $success['status'] = 'OK';
            $success['data'] = $data;
            return response()->json($success, 200);  
        }
    }


    /**
     * fonction de suppression d'un cycle
     */
    public static function deleteCycle($request){

        $association = AssociationMethods::getById($request->assocId);
        
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404); 
        } else{
            //Selectionner le cycle de l'association à travers son id
            $cycle = CycleMethods::getById($request->id);

            if($cycle == "not found")
            {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);    
            }
            

            if($cycle->etat == "actif"){
                $err['errNo'] = 14;
                $err['errMsg'] = 'impossible de supprimer le cycle car il est actif';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);    
            }

            AgMethods::deleteAgWithCycleId($cycle->id);
            ActiviteMethods::deleteACtiviteWithCycleId($cycle->id);
            $cycle->delete(); 

            $success['status'] = 'OK'; 
            $success['data'] = 'The cycle was deleted successfully.';

            return response()->json($success,203);   
        }
    }

     /**
     * fonction de suppression de plusieurs cycles
     */
    public static function deleteCycles($request){

        $association = AssociationMethods::getById($request->assocId);
        
        if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404); 
        } else{

            $id = $request->id;
            for ($i = 0; $i < count($id); $i ++)
            {

                $cycle = CycleMethods::getById($id[$i]);

                if($cycle == "not found")
                {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Cycle doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);    
                }else{
                    AgMethods::deleteAgWithCycleId($cycle->id);
                    $cycle->delete();
                }
            }

            $success['status'] = 'OK'; 
            $success['data'] = 'The cycle was deleted successfully.';

            return response()->json($success,203);
             
        }
    }


    public static function deleteSingleAssociationCycle($idAss){

        return Cycle::where('associations_id', $idAss)->delete();

    }


    public static function checkActifCycle($id_ass){
        $cycles = Cycle::where('associations_id',$id_ass)
                        ->get();
        foreach ($cycles as $key => $cycle) {
            if( $cycle->etat == "init" || $cycle->etat == "actif" || $cycle->etat == "create")   return $cycle;
        }

        return "not found";
    }



    /**
     * recupérer la date de fin du cycle
     */
    public static function getDateFin($dernierCycle){
          //date de départ
          $dateDepart = $dernierCycle->date_premiere_assemblee;
  
          //durée à rajouter;
          $duree = $dernierCycle->duree_cycle;

          //la première étape est de transformer cette date en timestamp
          $dateDepartTimestamp = $dateDepart;

          //on calcule la date de fin
          $dateFin =  strtotime('+'.$duree.' month', $dateDepartTimestamp);

          return $dateFin;
    }

    public static function activateCycle($cycle){
        $cycle = CycleMethods::getById($cycle);
        if($cycle != "not found"){
            try {
                $cycle->fill(["etat"=> "actif"]);
                $cycle->save();

                return array(
                    "status"=> "OK",
                    "data" => "successful"
                );
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
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

    public static function desactivateCycle($cycle){
        $cycle = CycleMethods::getById($cycle);
        if($cycle != "not found"){
            try {
                $cycle->fill(["etat"=> "inactif"]);
                $cycle->save();

                return array(
                    "status"=> "OK",
                    "data" => "successful"
                );
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
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

    public static function cloturerCycle($cycle){
        $cycle = CycleMethods::getById($cycle);
        if($cycle != "not found"){
            try {
                $cycle->fill(["etat"=> "cloture"]);
                $cycle->save();

                return array(
                    "status"=> "OK",
                    "data" => "successful"
                );
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
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

}