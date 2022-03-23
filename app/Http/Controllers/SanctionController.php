<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\SanctionMethods;
use Validator;

class SanctionController extends Controller
{
    //

    public function indexTypeSanction($assocId)
    {
        $sanction = SanctionMethods::indexTypeSanction($assocId);
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 200);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function indexSanction($ags_id)
    {
        $sanction = SanctionMethods::indexSanction($ags_id);
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 200);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function deleteSanction($assocId, $sanctionId)
    {
        $sanction = SanctionMethods::deleteSanction($sanctionId);
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 203);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function deleteTypeSanction($assocId, $typesanctionId)
    {
        $sanction = SanctionMethods::deleteTypeSanction($assocId, $typesanctionId);
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 203);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function storeSanction($assocId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membres_id' => 'required',
            'ags_id' => 'required',
            'montant' => 'required',
            'commentaire' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $sanction = SanctionMethods::storeSanction($assocId, $request->input('membres_id'), $request->input('ags_id'), $request->input('montant'), $request->input('commentaire'), $request->input('type'));
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 201);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function storeTypeSanction($assocId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'montant' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $sanction = SanctionMethods::storeTypeSanction($assocId, $request->all());
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 201);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }

    public function updateTypeSanction($assocId, $type_id, Request $request)
    {

        $sanction = SanctionMethods::updateTypeSanction($type_id, $request->all());
        if ($sanction['status'] == "OK") {
            return response()->json($sanction, 201);
        } else if ($sanction['data']['errNo'] == 15) {
            return response()->json($sanction, 404);
        } else {
            return response()->json($sanction, 500);
        }
    }
}
