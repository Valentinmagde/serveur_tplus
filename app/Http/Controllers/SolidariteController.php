<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\SolidariteMethods;
use Validator;

class SolidariteController extends Controller
{
    //

    public function indexTypeAssistance($assocId)
    {
        $types = SolidariteMethods::indexTypeAssistances($assocId);
        if ($types['status'] == "OK") {
            return response()->json($types, 200);
        } else if ($types['data']['errNo'] == 15) {
            return response()->json($types, 404);
        } else {
            return response()->json($types, 500);
        }
    }

    public function indexAssistance($activite_id)
    {

        $assistances = SolidariteMethods::indexAssistance($activite_id);
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 200);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }

    public function storeTypeAssistance($assocId, Request $request)
    {
        $assistances = SolidariteMethods::storeTypeAssistance($assocId, $request->all());
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 201);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }

    public function storeAssistance($activite_id, $membre_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'solidarites_id' => 'required',
            'montant_alloue' => 'required',
            'date_evenement' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        $assistances = SolidariteMethods::storeAssistance($activite_id, $membre_id, $request->all());
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 201);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }

    public function storeAssistancePast($activite_id, $membre_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assistances' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        $assistances = SolidariteMethods::storeAssistancePast($activite_id, $membre_id, $request->input('assistances'));
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 201);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }

    public function deleteTypeAssistance($type_assistance)
    {
        $assistances = SolidariteMethods::deleteType($type_assistance);
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 203);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }

    public function deleteAssistance($assistance_id)
    {
        $assistances = SolidariteMethods::deleteAssistance($assistance_id);
        if ($assistances['status'] == "OK") {
            return response()->json($assistances, 203);
        } else if ($assistances['data']['errNo'] == 15) {
            return response()->json($assistances, 404);
        } else {
            return response()->json($assistances, 500);
        }
    }
}
