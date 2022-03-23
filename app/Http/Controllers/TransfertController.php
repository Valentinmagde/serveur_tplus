<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\CustomModels\TransfertMethods;

class TransfertController extends Controller
{
    //
    public function transfert(Request $request)
    {

        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'montant' => 'required',
            'expediteur' => 'required',
            'recepteur' => 'required',
            'created_by' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $transfert = TransfertMethods::transfert($request->input('expediteur'), $request->input('recepteur'), $request->input('montant'), $request->input('libelle'), $request->input('created_by'));
        if ($transfert['status'] == 'OK') {
            return response()->json($transfert, 201);
        } else if ($transfert['data']['errNo'] == 15) {
            return response()->json($transfert, 404);
        } else {
            return response()->json($transfert, 500);
        }
    }
}
