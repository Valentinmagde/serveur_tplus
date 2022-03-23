<?php

namespace App\Http\Controllers;

use App\CustomModels\AgMethods;
use App\CustomModels\CycleMethods;
use Illuminate\Http\Request;
use App\Models\Association;
use App\Models\Cycle;

class agsController extends Controller
{



    public function updateToUTCTimeAllDateAg()
    {
        $ags = AgMethods::updateToUTCTimeAllDateAg();
        if ($ags['status'] == "OK") {
            return response()->json($ags, 201);
        } else {
            return response()->json($ags, 500);
        }
    }

    public function updateDateClotureAg()
    {
        $ags = AgMethods::updateDateClotureAg();
        if ($ags['status'] == "OK") {
            return response()->json($ags, 201);
        } else {
            return response()->json($ags, 500);
        }
    }

    /**
     * Liste des ags de cycle d'une association
     * @param Association $id
     * @param Cycle $id
     * @return Ags
     */
    public function getCycleAssociationAgs(Request $request)
    {

        return AgMethods::getCycleAssociationAgs($request);
    }

    /**
     * Generer la liste des assemblées generales dans un cycle donnée
     * @param Association $id
     * @param Cycle $id
     * @return Ags
     */
    public function createAgs(Request $request)
    {

        return AgMethods::createAg($request);
    }

    /**
     * Detail d'une assamblée generale
     * @param Association $id
     * @param Cycle $id
     * @param Ags $id
     * @return Ags
     */
    public function getAgById(Request $request)
    {

        return AgMethods::getAgById($request);
    }

    /** 
     * Mise à jour de l'assemblee generale (AGs) dans un cycle donnee 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function updateAgs(Request $request)
    {
        return AgMethods::updateAg($request);
    }

    public function changerHote($ag_id, $member_id)
    {
        $ag = AgMethods::changeHote($ag_id, $member_id);

        if ($ag['status'] == 'OK') {
            return response()->json($ag, 200);
        } else if ($ag['data']['errNo'] == 15) {
            return response()->json($ag, 404);
        } else {
            return response()->json($ag, 500);
        }
    }

    public function permuterAg(Request $request)
    {
        $ag = AgMethods::permuterAg($request->input("ag1"), $request->input("ag2"));

        if ($ag['status'] == 'OK') {
            return response()->json($ag, 200);
        } else if ($ag['data']['errNo'] == 15) {
            return response()->json($ag, 404);
        } else {
            return response()->json($ag, 500);
        }
    }

    public function getCyclesAgs($cycle_id)
    {
        $cycle = CycleMethods::getById($cycle_id);

        if ($cycle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $ags = AgMethods::getAgByCycleId($cycle_id);

        return response()->json($ags, 200);
    }

    public function clotureAgs($ag_id)
    {

        $ags = AgMethods::clotureAg($ag_id);

        if ($ags['status'] == "OK") {
            return response()->json($ags, 201);
        }
        if ($ags['data']['errNo'] == 15) {
            return response()->json($ags, 404);
        } else {
            return response()->json($ags, 500);
        }
    }
}
