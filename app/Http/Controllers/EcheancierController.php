<?php

namespace App\Http\Controllers;

use App\CustomModels\EcheancesMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcheancierController extends Controller
{
    //

    public function setToCloseCreditOut()
    {
        $echeanciers = EcheancesMethods::setToCloseCreditOut();
        if ($echeanciers['status'] == "OK") {
            return response()->json($echeanciers, 201);
        } else {
            return response()->json($echeanciers, 500);
        }
    }

    public function deleteAcquitementZero()
    {
        $echeanciers = EcheancesMethods::deleteAcquitementZero();
        if ($echeanciers['status'] == "OK") {
            return response()->json($echeanciers, 201);
        } else {
            return response()->json($echeanciers, 500);
        }
    }

    public function updateToUTCTimeAllDateEcheancier()
    {
        $echeanciers = EcheancesMethods::updateToUTCTimeAllDateEcheancier();
        if ($echeanciers['status'] == "OK") {
            return response()->json($echeanciers, 201);
        } else {
            return response()->json($echeanciers, 500);
        }
    }

    public function updateEtatEcheancierPast()
    {
        $echeanciers = EcheancesMethods::updateEtatEcheancierPast();
        if ($echeanciers['status'] == "OK") {
            return response()->json($echeanciers, 201);
        } else {
            return response()->json($echeanciers, 500);
        }
    }

    public function store($activite, Request $request)
    {
        $echeances = EcheancesMethods::createForAllMembers($activite, $request->all());

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function destroy($activite, $echeancier)
    {
        $echeances = EcheancesMethods::deleteEcheancierEndPoint($echeancier);
        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function storeEcheancesForSomeCompte($activite, Request $request)
    {
        DB::beginTransaction();
        $echeances = array();
        foreach ($request->all() as $key => $echeancier) {
            $echeance = EcheancesMethods::createForAllMembers($activite, $echeancier);

            if ($echeance['status'] == "OK") {
                $echeances[] = $echeance['data'];
            } else {
                DB::rollback();
                if ($echeance['data']['errNo'] == 15) {
                    return response()->json($echeance, 404);
                } else {
                    return response()->json($echeance, 500);
                }
            }
        }

        DB::commit();
        $success['status'] = "OK";
        $success['data'] = $echeances;


        return response()->json($success, 201);
    }

    public function storeSome($activite, Request $request)
    {

        $echeances = EcheancesMethods::createForSomeMembers($activite, $request->input('echeancier'), $request->input('membres'));

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function storeSomeEcheancesForSomeCompte($activite, Request $request)
    {
        DB::beginTransaction();
        $echeances = array();
        foreach ($request->input('echeanciers') as $key => $echeancier) {
            $echeance = EcheancesMethods::createForSomeMembers($activite, $echeancier, $request->input('membres'));

            if ($echeance['status'] == "OK") {
                $echeances[] = $echeance['data'];
            } else {
                DB::rollback();
                if ($echeance['data']['errNo'] == 15) {
                    return response()->json($echeance, 404);
                } else {
                    return response()->json($echeance, 500);
                }
            }
        }

        DB::commit();
        $success['status'] = "OK";
        $success['data'] = $echeances;


        return response()->json($success, 201);
    }

    public function getActive($activite)
    {
        $echeances = EcheancesMethods::getAllEcheancier($activite);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 200);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }


    public function getEcheancesByMember($member_id)
    {
        $echeances = EcheancesMethods::getEcheancesByMember($member_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 200);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function getEcheancesForAllMembers($assocId)
    {
        $echeances = EcheancesMethods::getEcheancesForAllMembers($assocId);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 200);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function getEcheancesForAllMembersAtAgs($assocId, $ags_id)
    {
        $echeances = EcheancesMethods::getEcheancesForAllMembersAtAgs($assocId, $ags_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 200);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function getEcheancesDecaissementForAllMembersAtAgs($assocId, $ags_id)
    {
        $echeances = EcheancesMethods::getEcheancesDecaissementForAllMembersAtAgs($assocId, $ags_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 200);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }
}
