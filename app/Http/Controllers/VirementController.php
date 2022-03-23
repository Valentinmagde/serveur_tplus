<?php

namespace App\Http\Controllers;

use App\CustomModels\VirementMethods;
use Illuminate\Http\Request;
use Validator;

class VirementController extends Controller
{
    //

    public function acquitement($activite_id, $compte_id, Request $request)
    {

        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'montant' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $virement = VirementMethods::acquitement($compte_id, $activite_id, $request->input('montant'), $request->input('created_by'));

        if ($virement['status'] == 'OK') {
            return response()->json($virement, 201);
        } else if ($virement['data']['errNo'] == 15) {
            return response()->json($virement, 404);
        } else {
            return response()->json($virement, 500);
        }
    }

    public function attribution($activite_id, $compte_id, Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'montant' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $virement = VirementMethods::attribution($compte_id, $activite_id, $request->input('montant'), $request->input('created_by'));

        if ($virement['status'] == 'OK') {
            return response()->json($virement, 201);
        } else if ($virement['data']['errNo'] == 15) {
            return response()->json($virement, 404);
        } else {
            return response()->json($virement, 500);
        }
    }
}
