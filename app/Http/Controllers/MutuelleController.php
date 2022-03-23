<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\MutuelleMethods;
use Illuminate\Support\Facades\DB;
use Validator;

class MutuelleController extends Controller
{
    //

    /**
     * get all mutuelle for one association
     */
    public function getAllMutuellesAssociation($assoc_id)
    {
        $mutuelles = MutuelleMethods::getAllMutuellesAssociation($assoc_id);
        if ($mutuelles['status'] == 'OK') {
            return response()->json($mutuelles, 200);
        } else if ($mutuelles['data']['errNo'] == 15) {
            return response()->json($mutuelles, 404);
        } else {
            return response()->json($mutuelles, 500);
        }
    }

    public function getAllCreditsAssociation($assoc_id)
    {
        $mutuelles = MutuelleMethods::getAllCreditAssociation($assoc_id);
        if ($mutuelles['status'] == 'OK') {
            return response()->json($mutuelles, 200);
        } else if ($mutuelles['data']['errNo'] == 15) {
            return response()->json($mutuelles, 404);
        } else {
            return response()->json($mutuelles, 500);
        }
    }

    /**
     * get all mutuelle for one activity
     */
    public function getAllMutuellesActivite($activites_id)
    {
        $mutuelles = MutuelleMethods::getAllMutuellesActivite($activites_id);
        return response()->json($mutuelles, 200);
    }


    /**
     * get all credit for one mutuelle
     */
    public function getAllCreditMutuelle($mutuelle_id)
    {
        $credits = MutuelleMethods::getAllCreditMutuelle($mutuelle_id);
        return response()->json($credits, 200);
    }

    /**
     * get all credit for one mutuelle
     */
    public function getAllMiseMutuelle($mutuelle_id)
    {
        $credits = MutuelleMethods::getAllMiseMutuelle($mutuelle_id);
        return response()->json($credits, 200);
    }

    /**
     * add one credit
     */
    public function addCredit($activites_id, $mutuelle_id, Request $request)
    {
        $credit = MutuelleMethods::storeCredit($activites_id, $mutuelle_id, $request->all());

        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    /**
     * update le credit
     */
    public function updateCredit($activites_id, $mutuelle_id, $credit_id, Request $request)
    {
        $credit = MutuelleMethods::updateCredit($activites_id, $mutuelle_id, $credit_id, $request->all());

        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    /**
     * add one mise de fond
     */
    public function addMise($membres_id, $mutuelle_id, Request $request)
    {
        $credit = MutuelleMethods::storeMise($mutuelle_id, $membres_id, $request->all());

        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    /**
     * add one credit pending
     */
    public function addCreditPending($activites_id, $mutuelle_id, Request $request)
    {
        $credit = MutuelleMethods::storeCreditEnCours($activites_id, $mutuelle_id, $request->input('credit'), $request->input('echeances'));

        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }


    /**
     * add multiple credit 
     */
    public function addMultipleCredit($activites_id, $mutuelle_id, Request $request)
    {
        $credits = array();
        DB::beginTransaction();
        foreach (\json_decode($request->input('credit')) as $key => $value) {
            $credit = MutuelleMethods::storeCredit($activites_id, $mutuelle_id, (array) $value);
            if ($credit['status'] == "NOK") {
                if ($credit['data']['errNo'] == 15) {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 404);
                } else {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 500);
                }
            }
            $credits[] = $credit;
        }
        DB::commit();
        return response()->json($credits, 201);
    }

    /**
     * add multiple mises
     */
    public function addMultipleMise($mutuelle_id, Request $request)
    {
        $credits = array();
        DB::beginTransaction();
        foreach (\json_decode($request->input('mises')) as $key => $value) {
            $credit = MutuelleMethods::storeMise($mutuelle_id, $value->membres_id, (array) $value);
            if ($credit['status'] == "NOK") {
                if ($credit['data']['errNo'] == 15) {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 404);
                } else {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 500);
                }
            }

            $credits[] = $credit;
        }
        DB::commit();
        return response()->json($credits, 201);
    }

    public function addMiseCsvFile($mutuelle_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required | file',
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $mises = MutuelleMethods::storeMiseCsvFile($mutuelle_id, $request->file('file'));
        if ($mises['status'] == 'OK') {
            return response()->json($mises, 201);
        } else if ($mises['data']['errNo'] == 15) {
            return response()->json($mises, 404);
        } else {
            return response()->json($mises, 500);
        }
    }



    /**
     * add multiple credits pending 
     */
    public function addMultipleCreditPending($activites_id, $mutuelle_id, Request $request)
    {
        $credits = array();
        DB::beginTransaction();
        foreach (\json_decode($request->input('credit')) as $key => $value) {
            $credit = MutuelleMethods::storeCreditEnCours($activites_id, $mutuelle_id, (array) $value);
            if ($credit['status'] == "NOK") {
                if ($credit['data']['errNo'] == 15) {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 404);
                } else {
                    DB::rollback();
                    $credits = [];
                    return response()->json($credit, 500);
                }
            }

            $credits[] = $credit;
        }
        DB::commit();
        return response()->json($credits, 201);
    }


    public function deleteMutuelle($mutuelle_id)
    {
        $mutuelle = MutuelleMethods::deleteMutuelle($mutuelle_id);

        if ($mutuelle['status'] == 'OK') {
            return response()->json($mutuelle, 203);
        } else if ($mutuelle['data']['errNo'] == 15) {
            return response()->json($mutuelle, 404);
        } else {
            return response()->json($mutuelle, 500);
        }
    }

    /**
     * approuve credit 
     */
    public function approuveCredit($credit_id)
    {
        $credit = MutuelleMethods::approuveCredit($credit_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    /**
     * reject credit 
     */
    public function rejectCredit($activites_id, $credit_id)
    {
        $credit = MutuelleMethods::rejectCredit($activites_id, $credit_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    public function annulerCredit($activites_id, $credit_id)
    {
        $credit = MutuelleMethods::annulerCredit($activites_id, $credit_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 201);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    public function deleteMise($mise_id)
    {
        $mise = MutuelleMethods::deleteMise($mise_id);
        if ($mise['status'] == 'OK') {
            return response()->json($mise, 203);
        } else if ($mise['data']['errNo'] == 15) {
            return response()->json($mise, 404);
        } else {
            return response()->json($mise, 500);
        }
    }

    public function deleteCredit($credit_id)
    {
        $credit = MutuelleMethods::deleteCredit($credit_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 203);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    public function deleteCreditPending($activites_id, $credit_id)
    {
        $credit = MutuelleMethods::deleteCreditEnCours($activites_id, $credit_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 203);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }

    public function previewEcheanceCredit($mutuelle_id, Request $request)
    {
        $credit = MutuelleMethods::previewEcheancesCredit($request->all(), $mutuelle_id);
        if ($credit['status'] == 'OK') {
            return response()->json($credit, 200);
        } else if ($credit['data']['errNo'] == 15) {
            return response()->json($credit, 404);
        } else {
            return response()->json($credit, 500);
        }
    }
}
