<?php

namespace App\Http\Controllers;

use App\CustomModels\CycleMethods;
use Illuminate\Http\Request;
use App\Models\Association;
use App\Models\Cycle;
use Validator;

class cycleController extends Controller
{
    /** 
     * Create cycle api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function createCycle(Request $request)
    {
        // Verifier si les differents champs ne sont pas vide
        $validator = Validator::make($request->all(), [
            "type_assemblee" => 'required',
            "date_premiere_assemblee" => 'required',
            "heure_assemblee" => 'required',
            "sanction_retard" => 'required',
            "sanction_abscence" => 'required',
            "frequence_seance" => 'required'
        ]);
        //Renvoie un message d'erreur si un champ est vide
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return CycleMethods::createCycle($request);
    }
    /**
     * Get association's cycles
     * @param Association $id
     * @return Cycles
     */
    public function getCycleByAssociationId(Request $request)
    {

        return CycleMethods::getCycleByAssociationId($request);
    }

    /**
     * Get association's cycle details
     * @param Association $id
     * @param Cycle $id
     * @return Cycles
     */
    public function getCycleByAssociation(Request $request)
    {

        return CycleMethods::getCycleByAssociation($request);
    }


    /**
     * Supprimer un cycle d'une association
     *  @param Association $id
     * @param Cycle $id
     * @return data
     */
    public function deleteCycle(Request $request)
    {

        return CycleMethods::deleteCycle($request);
    }

    public function updateCycle($assocId, $id, Request $request)
    {

        return CycleMethods::updateCycle($request->all(), $assocId, $id);
    }

    /**
     * Supprimer plusieurs cycles d'une association
     *  @param Association $id
     * @param Cycle $id
     * @return data
     */
    public function deleteCycles(Request $request)
    {

        return CycleMethods::deleteCycles($request);
    }


    public function activate($cycle_id)
    {

        $cycle  = CycleMethods::activateCycle($cycle_id);
        if ($cycle['status'] == "OK") {
            return response()->json($cycle, 201);
        } else if ($cycle['data']['errNo'] == "15") {
            return response()->json($cycle, 404);
        } else {
            return response()->json($cycle, 500);
        }
    }

    public function desactivate($cycle_id)
    {
        $cycle  = CycleMethods::desactivateCycle($cycle_id);
        if ($cycle['status'] == "OK") {
            return response()->json($cycle, 201);
        } else if ($cycle['data']['errNo'] == "15") {
            return response()->json($cycle, 404);
        } else {
            return response()->json($cycle, 500);
        }
    }
}
