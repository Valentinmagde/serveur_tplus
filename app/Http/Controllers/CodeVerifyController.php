<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Notifications\SignupActivate;
use Carbon\Carbon;

class CodeVerifyController extends Controller
{
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID' => 'required',
            'code_pin' => 'required'
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        if ($user = Utilisateur::where('code', $request->code_pin)->first()) {
            if ($user->id == $request->userID) {
                $user->active = 1;
                $user->code = null;
                $user->save();

                $success['status'] = 'OK';
                $success['data'] =  $user;
                return response()->json($success, 200);
            }
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = 'This code is incorrect, please try again';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return response()->json($error, 400);
        }
    }
}
