<?php

namespace App\Http\Controllers;

use App\CustomModels\OperationMethods;
use App\CustomModels\MembreMethods;
use App\CustomModels\TransactionMethods;
use App\CustomModels\AssociationMethods;
use App\CustomModels\CompteMethods;
use App\CustomModels\UserMethods;
use App\CustomModels\NotificationMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use App\Events\Finance;

class OperationController extends Controller
{
    //
    public function index($member_id)
    {
        $operation = OperationMethods::index($member_id);

        if ($operation['status'] == 'OK') {
            return response()->json($operation, 200);
        } else if ($operation['data']['errNo'] == 15) {
            return response()->json($operation, 404);
        } else {
            return response()->json($operation, 500);
        }
    }

    /**
     * création d'une operation
     */
    public function store($role,$member_id, Request $request)
    {

        DB::beginTransaction();
        try {

            $opera = \json_decode($request->input("operation"));
            $montant_op = $opera->montant;
            $montant_tr = 0;
            $montant_at = 0;
            if ($request->input('transactions')) {
                if (\json_decode($request->input('transactions')) === 0) {
                    DB::rollback();
                    $err['errNo'] = 9;
                    $err['errMsg'] = "veuillez ajouter des transactions";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
                foreach (\json_decode($request->input('transactions')) as $key => $transaction) {
                    $compte = CompteMethods::getById($transaction->comptes_id);
                    // if($compte != "not found"){
                    //     $statistiques = MembreMethods::getStatistiqueMembreActivity($member_id, $compte->activites_id);
                    //     $montant_at += $statistiques['data']['a_payer'];
                    // }
                    $montant_tr += $transaction->montant;
                }
            }else{
                DB::rollback();
                $err['errNo'] = 9;
                $err['errMsg'] = "veuillez ajouter des transactions";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }

            if ($montant_op == $montant_tr) {


                
                // if($opera->debit_credit == "credit"){
                //     if( $montant_at === 0){
                //         DB::rollback();
                //         $err['errNo'] = 9;
                //         $err['errMsg'] = "le montant attendu est egal à 0 vous ne pouvez pas faire de transaction";
                //         $error['status'] = 'NOK';
                //         $error['data'] = $err;
                //         return response()->json($error, 500);
                //     }
                    
                //     if( $montant_at < $montant_op){
                //         DB::rollback();
                //         $err['errNo'] = 9;
                //         $err['errMsg'] = "le montant de l'operation ne doit pas être supérieur au montant attendu générale de toutes les activites choisis";
                //         $error['status'] = 'NOK';
                //         $error['data'] = $err;
                //         return response()->json($error, 500);
                //     }
                // }

                if ($request->file('preuve')) {
                    $operation = OperationMethods::store($role, $opera, $member_id, $request->file('preuve'));
                } else {
                    $operation = OperationMethods::store($role, $opera, $member_id, null);
                }

                if ($operation['status'] == 'OK') {
                    if ($request->input('transactions')) {
                        $allTrans = \json_decode($request->input('transactions'));
                        foreach ($allTrans as $key => $transaction) {
                            $trans = TransactionMethods::store($transaction, $operation['data']['id']);
                            if($trans['status'] == 'NOK'){
                                DB::rollback();
                                $err['errNo'] = $trans['data']['errNo'];
                                $err['errMsg'] = $trans['data']['errMsg'];
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return response()->json($error, 500);
                            }
                        }
                    } else {
                        
                    }

                    if ($operation['data']['debit_credit'] == "debit") {
                        $operation = OperationMethods::validate($operation['data']['id'], $request->input('methode_paiement'), \json_decode($request->input("comptes")) ?? null );
                        if ($operation['status'] == 'OK') {
                            event(new Finance($operation['data'], $member_id, "création d'une nouvelle opération", "creation d'une operation"));

                            DB::commit();
                            return response()->json($operation, 202);
                        } else if ($operation['data']['errNo'] == 15) {
                            DB::rollback();
                            return response()->json($operation, 404);
                        } else {
                            DB::rollback();
                            return response()->json($operation, 500);
                        }
                    }

                    $op = OperationMethods::show($operation['data']['id'], $member_id);
                    if ($op['status'] == 'OK') {
                        event(new Finance($operation['data'], $member_id, "création d'une nouvelle opération", "creation d'une operation"));
                        DB::commit();
                        return response()->json($op, 201);
                    } else if ($op['data']['errNo'] == 15) {
                        DB::rollback();
                        return response()->json($op, 404);
                    } else {
                        DB::rollback();
                        return response()->json($op, 500);
                    }
                } else {
                    DB::rollback();
                    if ($operation['data']['errNo'] == 15) {
                        return response()->json($operation, 404);
                    } else {
                        return response()->json($operation, 500);
                    }
                }
            } else {
                DB::rollback();
                $err['errNo'] = 9;
                $err['errMsg'] = "le montant de l'operation doit être equivalent au montant des transactions";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        } catch (\Exception $e) {

            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    public function storeEnSeance($role, $member_id, Request $request)
    {

        DB::beginTransaction();
        try {
            $opera = \json_decode($request->input("operation"));
            $montant_op = $opera->montant;
            $montant_tr = 0;
            $montant_at = 0;
            if ($request->input('transactions')) {
                if (\json_decode($request->input('transactions')) === 0) {
                    DB::rollback();
                    $err['errNo'] = 9;
                    $err['errMsg'] = "veuillez ajouter des transactions";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
                foreach (\json_decode($request->input('transactions')) as $key => $transaction) {
                    // $compte = CompteMethods::getById($transaction->comptes_id);
                    // if($compte != "not found"){
                    //     $statistiques = MembreMethods::getStatistiqueMembreActivity($member_id, $compte->activites_id);
                    //     $montant_at += $statistiques['data']['a_payer'];
                    // }

                    $montant_tr += $transaction->montant;
                }
            }else{
                DB::rollback();
                $err['errNo'] = 9;
                $err['errMsg'] = "veuillez ajouter des transactions";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }

            if ($montant_op == $montant_tr) {

                // if($opera->debit_credit === "credit"){

                //     if( $montant_at === 0){
                //         DB::rollback();
                //         $err['errNo'] = 9;
                //         $err['errMsg'] = "le montant attendu est egal à 0 vous ne pouvez pas faire de transaction";
                //         $error['status'] = 'NOK';
                //         $error['data'] = $err;
                //         return response()->json($error, 500);
                //     }

                //     if( $montant_at < $montant_op){
                //         DB::rollback();
                //         $err['errNo'] = 9;
                //         $err['errMsg'] = "le montant de l'operation ne doit pas être supérieur au montant attendu générale de toutes les activités choisis";
                //         $error['status'] = 'NOK';
                //         $error['data'] = $err;
                //         return response()->json($error, 500);
                //     }
                // }
                
                if ($request->file('preuve')) {
                    $operation = OperationMethods::store($role, $opera, $member_id, $request->file('preuve'));
                } else {
                    $operation = OperationMethods::store($role, $opera, $member_id, null);
                }

                if ($operation['status'] == 'OK') {
                    if ($request->input('transactions')) {
                        $allTrans = \json_decode($request->input('transactions'));
                        
                        foreach ($allTrans as $key => $transaction) {
                            $trans = TransactionMethods::store($transaction, $operation['data']['id']);
                            if($trans['status'] == 'NOK'){
                                DB::rollback();
                                $err['errNo'] = $trans['data']['errNo'];
                                $err['errMsg'] = $trans['data']['errMsg'];
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return response()->json($error, 500);
                            }
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 9;
                        $err['errMsg'] = "veuillez ajouter des transactions";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500);
                    }

                    if ($operation['data']['debit_credit'] == "debit")
                        $operation = OperationMethods::validate($operation['data']['id'], $request->input('methode_paiement'), \json_decode($request->input("comptes")) ?? null );
                    else $operation = OperationMethods::validate($operation['data']['id'], null,null );

                    if ($operation['status'] == 'OK') {
                        event(new Finance($operation['data'], $member_id, "création d'une opération en séance", "creation d'une operation"));
                        DB::commit();
                        return response()->json($operation, 202);
                    } else if ($operation['data']['errNo'] == 15) {
                        DB::rollback();
                        return response()->json($operation, 404);
                    } else {
                        DB::rollback();
                        return response()->json($operation, 500);
                    }
                } else {
                    DB::rollback();
                    if ($operation['data']['errNo'] == 15) {
                        return response()->json($operation, 404);
                    } else {
                        return response()->json($operation, 500);
                    }
                }
            } else {
                DB::rollback();
                $err['errNo'] = 9;
                $err['errMsg'] = "le montant de l'operation doit être equivalent au montant des transactions";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        } catch (\Exception $e) {

            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    // public function store($member_id, Request $request){

    //     DB::beginTransaction();
    //     try {

    //         $montant_op = $request->input("operation.montant");
    //         $montant_tr = 0;
    //         if($request->input('transactions')){
    //             foreach ($request->input('transactions') as $key => $transaction) {
    //                $montant_tr += $transaction['montant'];
    //             }
    //         }

    //         if($montant_op == $montant_tr){


    //             if($request->file('operation.preuve')){
    //                 $operation = OperationMethods::store($request->input('operation'), $member_id, $request->file('operation.preuve'));
    //             }else{
    //                 $operation = OperationMethods::store($request->input('operation'), $member_id, null);
    //             }

    //             if($operation['status'] == 'OK'){ 
    //                 if($request->input('transactions')){
    //                     $allTrans = $request->input('transactions');
    //                     foreach ($allTrans as $key => $transaction) {
    //                         TransactionMethods::store($transaction, $operation['data']['id']);
    //                     }
    //                 }

    //                 if($operation['data']['debit_credit'] == "debit"){
    //                     $operation = OperationMethods::validate($operation['data']['id']);
    //                     DB::commit();
    //                     return response()->json($operation, 202);
    //                 }

    //                 DB::commit();
    //                 $op = OperationMethods::show($operation['data']['id'], $member_id);
    //                 if($op['status'] == 'OK'){
    //                     return response()->json($op, 201);
    //                 }else if($op['data']['errNo'] == 15){
    //                     return response()->json($op, 404);
    //                 }else{
    //                     return response()->json($op, 500);
    //                 }
    //             }else{
    //                 DB::rollback();
    //                 if($operation['data']['errNo'] == 15){
    //                     return response()->json($operation, 404);
    //                 }else{
    //                     return response()->json($operation, 500);
    //                 }

    //             }


    //         }else{
    //             DB::rollback();
    //             $err['errNo'] = 9;
    //             $err['errMsg'] = "le montant de l'operation doit être equivalent au montant des transactions";
    //             $error['status'] = 'NOK';
    //             $error['data'] = $err;
    //             return response()->json($error, 500);
    //         }

    //     } catch (\Exception $e) {

    //         DB::rollback();
    //         $err['errNo'] = 11;
    //         $err['errMsg'] = $e->getMessage();
    //         $error['status'] = 'NOK';
    //         $error['data'] = $err;
    //         return response()->json($error, 500);
    //     }


    // }


    /**
     * update d'une opération
     */
    // public function update($member_id, $operation, Request $request){


    //     DB::beginTransaction();
    //     try {


    //         $montant_op = $request->input("operation.montant");
    //         $montant_tr = 0;
    //         if($request->input('transactions')){
    //             foreach ($request->input('transactions') as $key => $transaction) {
    //                $montant_tr += $transaction['montant'];
    //             }
    //         }

    //         if($montant_op == $montant_tr){

    //             if($request->file('operation.preuve')){
    //                 $operation = OperationMethods::update($request->except('preuve'), $member_id, $operation, $request->file('preuve'));
    //             }else{
    //                 $operation = OperationMethods::update($request->all(), $member_id, $operation, null);
    //             }

    //             if($operation['status'] == 'OK' && $request->input('transactions')){ 

    //                 $delete = TransactionMethods::deleteByOperationId($operation['data']['id']);
    //                 if($delete['status'] == "OK"){
    //                     $allTrans = $request->input('transactions');
    //                     $trans = array();
    //                     foreach ($allTrans as $key => $transaction) {
    //                         if(array_key_exists('id', $transaction)){
    //                             unset($transaction['id']);
    //                             $tr = TransactionMethods::store($transaction, $operation['data']['id']);
    //                             $trans[] = $tr;
    //                         }else{
    //                             $tr = TransactionMethods::store($transaction, $operation['data']['id']);
    //                             $trans[] = $tr;
    //                         }

    //                     }
    //                     // $transactions = TransactionMethods::getByOperationId($operation['data']['id']);
    //                     // foreach ($transactions as $key => $trOld) {
    //                     //     $isIn = false;
    //                     //     foreach ($trans as $key => $trAdd) {
    //                     //         if($trOld->id == $trAdd->id) $isIn = true;
    //                     //     }
    //                     //     if(!$isIn){
    //                     //         try {
    //                     //             $trOld->delete();
    //                     //         } catch (\Exception $e) {
    //                     //             $err['errNo'] = 11;
    //                     //             $err['errMsg'] = $e->getMessage();
    //                     //             $error['status'] = 'NOK';
    //                     //             $error['data'] = $err;
    //                     //             return response()->json($error, 500);
    //                     //         }
    //                     //     }
    //                     // }

    //                     DB::commit();

    //                     $op = OperationMethods::show($operation['data']['id'], $member_id);
    //                     if($op['status'] == 'OK'){
    //                         return response()->json($op, 201);
    //                     }else if($operation['data']['errNo'] == 15){
    //                         return response()->json($op, 404);
    //                     }else{
    //                         return response()->json($op, 500);
    //                     }
    //                 }else{
    //                     DB::rollback();
    //                     return response()->json($delete, 500);
    //                 }

    //             }else{
    //                 DB::rollback();
    //                 if($operation['data']['errNo'] == 15){
    //                     return response()->json($op, 404);
    //                 }else{
    //                     return response()->json($op, 500);
    //                 }
    //             }



    //         }else{
    //             DB::rollback();
    //             $err['errNo'] = 9;
    //             $err['errMsg'] = "le montant de l'operation doit être equivalent au montant des transactions";
    //             $error['status'] = 'NOK';
    //             $error['data'] = $err;
    //             return response()->json($error, 500);
    //         }

    //     } catch (\Exception $e) {

    //         DB::rollback();
    //         $err['errNo'] = 11;
    //         $err['errMsg'] = $e->getMessage();
    //         $error['status'] = 'NOK';
    //         $error['data'] = $err;
    //         return response()->json($error, 500);
    //     }



    // }
    public function update($member_id, $operation, Request $request)
    {


        DB::beginTransaction();
        try {

            $opera = \json_decode($request->input("operation"));
            $montant_op = $opera->montant;
            $montant_tr = 0;
            if ($request->input('transactions')) {
                foreach (\json_decode($request->input('transactions')) as $key => $transaction) {
                    $montant_tr += $transaction->montant;
                }
            }

            if ($montant_op == $montant_tr) {

                if ($request->file('preuve')) {
                    $operation = OperationMethods::update($opera, $member_id, $operation, $request->file('preuve'));
                } else {
                    $operation = OperationMethods::update($opera, $member_id, $operation, null);
                }

                if ($operation['status'] == 'OK' && $request->input('transactions')) {

                    $delete = TransactionMethods::deleteByOperationId($operation['data']['id']);
                    if ($delete['success'] == "OK") {
                        $allTrans = \json_decode($request->input('transactions'));
                        $trans = array();
                        foreach ($allTrans as $key => $transaction) {
                            if (array_key_exists('id', (array) $transaction)) {
                                unset($transaction['id']);
                                $tr = TransactionMethods::store($transaction, $operation['data']['id']);
                                $trans[] = $tr;
                            } else {
                                $tr = TransactionMethods::store($transaction, $operation['data']['id']);
                                $trans[] = $tr;
                            }
                        }

                        $op = OperationMethods::show($operation['data']['id'], $member_id);
                        if ($op['status'] == 'OK') {
                            event(new Finance($operation['data'], $member_id, "une opération a été mise à jour", "mise à jour d'une operation"));
                            DB::commit();
                            return response()->json($op, 201);
                        } else if ($op['data']['errNo'] == 15) {
                            DB::rollback();
                            return response()->json($op, 404);
                        } else {
                            DB::rollback();
                            return response()->json($op, 500);
                        }
                    } else {
                        DB::rollback();
                        return response()->json($delete, 500);
                    }
                } else {
                    DB::rollback();
                    if ($operation['data']['errNo'] == 15) {
                        return response()->json($operation, 404);
                    } else {
                        return response()->json($operation, 500);
                    }
                }
            } else {
                DB::rollback();
                $err['errNo'] = 9;
                $err['errMsg'] = "le montant de l'operation doit être equivalent au montant des transactions";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        } catch (\Exception $e) {

            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }


    /**
     * retourner une operation avec ses transactions
     */
    public function show($member_id, $operation)
    {
        $operation = OperationMethods::show($operation, $member_id);

        if ($operation['status'] == 'OK') {
            return response()->json($operation, 200);
        } else if ($operation['data']['errNo'] == 15) {
            return response()->json($operation, 404);
        } else {
            return response()->json($operation, 500);
        }
    }

    public function showUniqueId($member_id, $operation)
    {
        $operation = OperationMethods::showUniqueId($member_id, $operation);

        if ($operation['status'] == 'OK') {
            return response()->json($operation, 200);
        } else if ($operation['data']['errNo'] == 15) {
            return response()->json($operation, 404);
        } else {
            return response()->json($operation, 500);
        }
    }

    /**
     * valider une operation et ses transactions
     */
    public function validateTransactions($member_id, $operation)
    {
        DB::beginTransaction();
        try {

            $operation = OperationMethods::validate($operation,null,  null);
            if ($operation['status'] == 'OK') {
                event(new Finance($operation['data'], $member_id, "l'opération a été validé", "validation d'une opération"));
                DB::commit();
                return response()->json($operation, 202);
            } else if ($operation['data']['errNo'] == 15) {
                DB::rollback();
                return response()->json($operation, 404);
            } else {
                DB::rollback();
                return response()->json($operation, 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }


    public function rejeterTransactions($member_id, $operation)
    {
        DB::beginTransaction();
        try {

            $operation = OperationMethods::rejeter($operation);

            event(new Finance($operation['data'], $member_id, "l'opération a été rejeté", "rejet d'une opération"));
            DB::commit();
            return response()->json($operation, 202);
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    public function getAssociationOperation($assoc_id)
    {
        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $operations = OperationMethods::getAssociationOperation($assoc_id);
        return response()->json($operations, 200);
    }

    public function destroy($member_id, $operation)
    {
        $op = OperationMethods::delete($operation);
        if ($op['status'] == 'OK') {
            return response()->json($op, 200);
        } else if ($operation['data']['errNo'] == 15) {
            return response()->json($op, 404);
        } else {
            return response()->json($op, 500);
        }
    }
}
