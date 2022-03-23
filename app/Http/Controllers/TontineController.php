<?php

namespace App\Http\Controllers;

use App\CustomModels\TontineMethods;
use Illuminate\Http\Request;
use Validator;
class TontineController
{



    public function updateToUTCTimeAllDateLotsTontine()
    {
        $LotsTontines = TontineMethods::updateToUTCTimeAllDateLotsTontine();
        if ($LotsTontines['status'] == "OK") {
            return response()->json($LotsTontines, 201);
        } else {
            return response()->json($LotsTontines, 500);
        }
    }

    /**
     * générer les echeanciers d'une tontine
     */
    public function echeanciers($assocId, $tontine_id)
    {

        $echeances = TontineMethods::EcheancierTontine($assocId, $tontine_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }


    public function echeanciersTontineVariable($assocId, $tontine_id, Request $request)
    {

        $echeances = TontineMethods::EcheancierTontineVariable($assocId, $tontine_id, $request->all());

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }


    /**
     * tirage au sort des gagnant de lots
     */
    public function calendrier($assocId, $tontine_id)
    {

        $echeances = TontineMethods::getNumberOfGain($assocId, $tontine_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    /**
     * tirage au sort des gagnant de lots
     */
    public function tirage($assocId, $tontine_id)
    {

        $echeances = TontineMethods::tirageAuSort($assocId, $tontine_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    /**
     * récupération de la liste de lots d'une tontine
     */
    public function lots($assocId, $tontine_id)
    {

        $lots = TontineMethods::getLots($tontine_id);
        return response()->json($lots, 200);
    }

    /**
     * assigner une date de bouffe à un ou plusieurs comptes
     */
    public function assignation($assocId, $tontine_id, Request $request)
    {

        $echeances = TontineMethods::assignLotsComptes($tontine_id, $request->input('lots'));

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    /**
     * assigner une date de bouffe à un ou plusieurs comptes
     */
    public function assignationLotPrincipal($assocId, $tontine_id, Request $request)
    {

        $lot = $request->input('lot');
        if(is_string($lot)){
            $lot = (array) json_decode($lot);
        }

        $echeances = TontineMethods::assignLotPrincipalComptes($tontine_id, $lot, $lot['id'], $request->input('enchere'));

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function desassignationLotPrincipal($assocId, $tontine_id, Request $request)
    {
        $lot = $request->input('lot');
        if(is_string($lot)){
            $lot = (array) json_decode($lot);
        }

        $echeances = TontineMethods::desassignLotPrincipalComptes($tontine_id, $lot, $lot['id'], $lot['enchere']);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    /**
     * assigner un lot à un unique membre
     */
    public function assignationSingle($assocId, $tontine_id, Request $request)
    {

        $lot = $request->input('lot');
        if(is_string($lot)){
            $lot = (array) json_decode($lot);
        }

        $echeances = TontineMethods::createLotEndpoint($tontine_id, $lot, $request->input('type'), $request->input('enchere') ?? 0, $request->input('interet') ?? 0, $request->input('remboursement') ?? 0);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }


    /**
     * permutation de date de bouffe de deux comptes
     */
    public function permutation($assocId, $tontine_id, Request $request)
    {

        $echeances = TontineMethods::permutationLots($tontine_id, $request->input('compte1'), $request->input('compte2'));

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function changeDateLotTontine($assocId, $tontine_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'lot' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $echeances = TontineMethods::changeDateLotTontine($assocId, $tontine_id, $request->input('lot'), $request->input('date'));

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 201);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }


    public function deleteLotSecondaire($tontine_id, $lot_id)
    {

        $echeances = TontineMethods::deleteLotSecondaire($tontine_id, $lot_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 203);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }

    public function deleteCompte($activites_id, $comptes_id)
    {

        $echeances = TontineMethods::deleteCompte($comptes_id, $activites_id);

        if ($echeances['status'] == "OK") {
            return response()->json($echeances, 203);
        } else if ($echeances['data']['errNo'] == 15) {
            return response()->json($echeances, 404);
        } else {
            return response()->json($echeances, 500);
        }
    }
}
