<?php

namespace App\CustomModels;

use App\Models\Transaction;

class TransactionMethods{

    /**
     * retourner toutes les transactions par l'id de l'operation 
     */
    public static function getByOperationId($id){
        $trans = Transaction::where('operations_id', $id)->get();

        if($trans){
            foreach ($trans as $key => $value) {
                $activite = CompteMethods::getActivityByCompteId($value->comptes_id);
                $value['activite'] = $activite;
            }
            return $trans;
        }else{
            return 'not found';
        }
    }

    public static function getByCompteId($compte_id){
        $trans = Transaction::where('comptes_id', $compte_id)->get();

        if($trans){
            return $trans;
        }else{
            return 'not found';
        }
    }

    public static function getByCompteIdAtAg($compte_id, $date){
        $trans = Transaction::where('comptes_id', $compte_id)->get();

        if($trans){
            return $trans;
        }else{
            return 'not found';
        }
    }

    public static function deleteByOperationId($id){
        try {
            $trans = Transaction::where('operations_id', $id)->get();
            foreach ($trans as $key => $tr) {
                $tr->delete();
            }
            $success['success'] = "OK";
            $success['data'] = true;

            return $success;
        } catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

      /**
     * retourner toutes les transactions par l'id 
     */
    public static function getById($id){
        $trans = Transaction::where('id', $id)->first();

        if($trans){
            return $trans;
        }else{
            return 'not found';
        }
    }

    /**
     * création d'une transaction
     */
    public static function store($transaction, $operation){
        $compte = CompteMethods::getById($transaction->comptes_id);

        if($compte != "not found"){
            $statistiques = MembreMethods::getStatistiqueMembreActivity($compte->membres_id, $compte->activites_id);
            $op = OperationMethods::getById($operation);
            if($op != "not found"){
                try {
                    
                    $date = DateMethods::getCurrentDateInt();

                    if($transaction->debit_credit == "credit")
                        $transaction->montant_attendu = $statistiques['data']['a_payer'];
                    else
                        $transaction->montant_attendu = $statistiques['data']['a_retirer'];

                    $transaction->operations_id = $operation;
                    $transaction->date_created = $date;
                    if(!property_exists( $transaction, "etat")  || $transaction->etat == null) $transaction->etat = "EN_ATTENTE";
                    
                    $transaction = (array) $transaction;
                    
                    $trans = Transaction::create($transaction);

                    $success['status'] = "OK";
                    $success['data'] = $trans;

                    return $success;

                }catch(\Exception $e){
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }
            $err['errNo'] = 15;
            $err['errMsg'] = "operation {$operation} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$compte} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
        
    }

    /**
     * update d'une transaction
     */
    public static function update($transaction, $operation){
        $compte = CompteMethods::getById($transaction['comptes_id']);

        if($compte != "not found"){

            $op = OperationMethods::getById($operation);
            if($op != "not found"){
                try {
                    
                   $trans = TransactionMethods::getById($transaction['id']);
                   $trans->fill($transaction);
                   $trans->save();

                   $success['status'] = "OK";
                   $success['data'] = $trans;

                   return $success;

                }catch(\Exception $e){
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }
            $err['errNo'] = 15;
            $err['errMsg'] = "operation {$operation} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$compte} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }
    

    /**
     * show une transaction
     */

     public static function show($operation, $compte, $id){
        $compte = CompteMethods::getById($compte);

        if($compte != "not found"){

            $op = OperationMethods::getById($operation);
            if($op != "not found"){
                try {
                    
                    $trans = Transaction::where('id', $id)
                                        ->where('comptes_id', $compte)
                                        ->where('operations_id', $operation)
                                        ->first();

                    $success['status'] = "OK";
                    $success['data'] = $trans;

                    return $success;

                }catch(\Exception $e){
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }
            $err['errNo'] = 15;
            $err['errMsg'] = "operation {$operation} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $err['errNo'] = 15;
        $err['errMsg'] = "compte {$compte} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
     }
}
