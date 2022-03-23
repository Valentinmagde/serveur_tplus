<?php

namespace App\Http\Controllers;

use App\CustomModels\CompteMethods;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;

class CompteController extends Controller
{
    //
    /**
     * créer un compte
     */
    public function store($activite, $membre, Request $request)
    {

        $compte = CompteMethods::store($request->all(), $membre, $activite);

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function storeOneElse($activite, $membre, Request $request)
    {

        $compte = CompteMethods::storeNewMember($request->all(), $request->input('montant_cotisation'), $membre, $activite);

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function update($activite, $membre, $id, Request $request)
    {

        $compte = CompteMethods::update($id, $request->all(), $membre, $activite);

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function getAll($activite)
    {
        $comptes = CompteMethods::getAll($activite);

        return response()->json($comptes, 200);
    }

    public function multipleDestroy(Request $request)
    {
        $compte = CompteMethods::deleteComptes($request->input("comptes"));

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 202);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        $compte = CompteMethods::storeMultiple($request->all());

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function updateMultiple(Request $request)
    {
        $compte = CompteMethods::updateMultiple($request->all());

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }


    public function getCompteByMember($member_id)
    {
        $compte = CompteMethods::getComptesByMemberEndPoint($member_id);

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function getCompteByAssociation($assocId)
    {
        $compte = CompteMethods::getComptesAssociations($assocId);

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function getCompteByActivityAndMember($activites_id, $member_id)
    {
        $compte = CompteMethods::getByIdMAEndPoint($activites_id, $member_id);
        $success['status'] = "OK";
        $success['data'] = $compte;

        $compte = $success;

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function assignerAvoir($comptes_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required',
            'mode' => ['required', Rule::in(['ESPECES', 'WALLET'])]
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        $compte = CompteMethods::assignerAvoir($comptes_id, $request->input('montant'), $request->input('mode'));

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }

    public function ajouterAvoir($comptes_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        $compte = CompteMethods::ajouterAvoir($comptes_id, $request->input('montant'));

        if ($compte['status'] == 'OK') {
            return response()->json($compte, 200);
        } else if ($compte['data']['errNo'] == 15) {
            return response()->json($compte, 404);
        } else {
            return response()->json($compte, 500);
        }
    }
}
