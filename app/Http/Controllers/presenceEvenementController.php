<?php

namespace App\Http\Controllers;

use App\CustomModels\PresenceEvenementMethods;
use Illuminate\Http\Request;
use Validator;

class PresenceEvenementController extends Controller
{
    //

    public function store($assocId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membre_id' => 'required',
            'evt_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $presence = PresenceEvenementMethods::addPresence($request->input('membre_id'), $request->input('evt_id'), $request->input('status'), $request->input('raison') ?? "");
        if ($presence['status'] == "OK") {
            return response()->json($presence, 201);
        } else if ($presence['data']['errNo'] == 15) {
            return response()->json($presence, 404);
        } else {
            return response()->json($presence, 500);
        }
    }

    public function index($assocId, $evt_id)
    {
        $presence = PresenceEvenementMethods::checkAssociationPresenceAll($assocId, $evt_id);
        if ($presence['status'] == "OK") {
            return response()->json($presence, 201);
        } else if ($presence['data']['errNo'] == 15) {
            return response()->json($presence, 404);
        } else {
            return response()->json($presence, 500);
        }
    }

    public function show($assocId, $membre_id, $evt_id)
    {
        $presence = PresenceEvenementMethods::checkPresence($assocId, $membre_id, $evt_id);
        if ($presence['status'] == "OK") {
            return response()->json($presence, 201);
        } else if ($presence['data']['errNo'] == 15) {
            return response()->json($presence, 404);
        } else {
            return response()->json($presence, 500);
        }
    }

    public function showAssoc($assocId, $membre_id)
    {
        $presence = PresenceEvenementMethods::checkPresenceMemberEvt($assocId, $membre_id);
        if ($presence['status'] == "OK") {
            return response()->json($presence, 201);
        } else if ($presence['data']['errNo'] == 15) {
            return response()->json($presence, 404);
        } else {
            return response()->json($presence, 500);
        }
    }

    public function updatePresence($assocId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membre_id' => 'required',
            'evt_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $presence = PresenceEvenementMethods::updatePresence($request->input('membre_id'), $request->input('evt_id'), $request->input('status'));
        if ($presence['status'] == "OK") {
            return response()->json($presence, 201);
        } else if ($presence['data']['errNo'] == 15) {
            return response()->json($presence, 404);
        } else {
            return response()->json($presence, 500);
        }
    }
}
