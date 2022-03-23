<?php

namespace App\CustomModels;

use App\Models\Operation;
use App\Models\Transaction;
use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use App\CustomModels\MembreMethods;
use App\Models\MembresHasUser;

class OperationMethods
{

    public static function index($member_id)
    {
        $ops = Operation::where('membre_id', $member_id)
            ->get();
        $membre = MembreMethods::getById($member_id);
        foreach ($ops as $key => $op) {
            $trans = TransactionMethods::getByOperationId($op->id);
            if ($trans != "not found") {
                $op['membre'] = $membre->firstName . ' ' . $membre->lastName;
                $op['transactions'] = $trans;
            }
        }


        $success['status'] = 'OK';
        $success['data'] = $ops;

        return $success;
    }

    public static function getById($id)
    {
        $operation = Operation::where('id', $id)->first();

        if ($operation) {
            return $operation;
        }

        return "not found";
    }

    public static function getByMemberId($id)
    {
        $operation = Operation::where('membre_id', $id)->get();

        if ($operation) {
            foreach ($operation as $key => $op) {
                $transactions = TransactionMethods::getByOperationId($op->id);
                if ($transactions != "not found")
                    $op['transactions'] = $transactions;
            }
            return $operation;
        }

        return "not found";
    }


    /**
     * créer une opération
     */
    public static function store($role, $operation, $member_id, $file)
    {
        $roles = array('membre', 'admin');
        if(!in_array($role, $roles)){
            $err['errNo'] = 15;
            $err['errMsg'] = "role doit être 'membre' ou 'admin'";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            if($operation->mode === "WALLET" && $operation->debit_credit === "credit"){

                if($role === "membre"){
                    $connected_user_id = auth()->user()->id;
                    $member_connected = MembresHasUserMethods::getByUserIdAssociationId($connected_user_id, $member->associations_id);
                    if($member_connected == "not found"){
                        $err['errNo'] = 15;
                        $err['errMsg'] = "l'utilisateur connecté n'a pas de membre dans cet association";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }

                    $member = MembreMethods::getById($member_connected->id);
                    if($member == "not found"){
                        $err['errNo'] = 15;
                        $err['errMsg'] = "member not found";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                }

                $uwallet = WalletsMethods::getWalletByUWallet($member->default_u_wallets_id);
                if($uwallet == "not found"){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "pas de u_wallet relié à ce compte";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($uwallet->solde < $operation->montant){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "solde insuffisant pour le credit: solde actuel {$uwallet->solde}";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $uwallet->solde = $uwallet->solde - $operation->montant;
                $uwallet->transit = $operation->montant;
                $uwallet->save();
                // WalletsMethods::updateWallet($uwallet->id, (array) $uwallet);
                
            }else if($operation->mode === "WALLET" && $operation->debit_credit === "debit"){
                
                $association = AssociationMethods::getById($member->associations_id);
                if($association == "not found"){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "association {$member->associations_id} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $awallet = WalletsMethods::getWalletByAWallet($association->a_wallets_id);
                if($awallet == "not found"){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "wallet association doesn't exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($awallet->solde < $operation->montant){
                    $err['errNo'] = 15;
                    $err['errMsg'] = "solde insuffisant pour le debit: solde actuel {$awallet->solde}";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $awallet->solde = $awallet->solde - $operation->montant;
                $awallet->transit = $operation->montant;
                $awallet->save();

                // WalletsMethods::updateWallet($awallet->id, (array) $awallet);
            }

            $operation->date_realisation = $operation->date_realisation;
            if(!property_exists($operation, "etat") || $operation->etat == null) $operation->etat = "EN_ATTENTE";
            $operation->membre_id = $member_id;

            try {
                /* print_r($operation); */
                $operation = (array)$operation;

                if($operation['mode'] === "WALLET") $operation['membres_id_wallet'] = $member->id;

                $created = Operation::create($operation);

                if ($file != null) {
                    $file = url(FileUpload::operationFileUpload($file, $member_id, $created->id));
                    $created->fill(["preuve" => $file]);
                    $created->save();
                }

                $success['status'] = "OK";
                $success['data'] = $created;

                return $success;
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }

        $err['errNo'] = 15;
        $err['errMsg'] = "member {$member_id} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }

    /**
     * update d'une operation
     */
    public static function update($operation, $member_id, $operation_id, $file)
    {
        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            $op = OperationMethods::getById($operation_id);

            if ($op != "not found") {
                try {

                    if ($file != null) {
                        $file = url(FileUpload::operationFileUpload($file, $member_id, $op->id));
                        $operation->preuve = $file;
                    }

                    $operation = (array) $operation;
                    $op->fill($operation);
                    $op->save();


                    $success['status'] = "OK";
                    $success['data'] = $op;
                    return $success;
                } catch (\Exception $e) {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {

                $err['errNo'] = 15;
                $err['errMsg'] = "operation {$operation_id} not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }

        $err['errNo'] = 15;
        $err['errMsg'] = "member {$member_id} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }


    /**
     * retourner les operations d'un membre
     */
    public static function getMemberOperations($member_id, $operation_id)
    {

        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            $op = Operation::all()
                ->where('id', $operation_id)
                ->where('membre_id', $member_id);

            if ($op) {
                $success['status'] = 'OK';
                $success['data'] = $op;
                return $success;
            }


            $err['errNo'] = 15;
            $err['errMsg'] = 'operations empty';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $err['errNo'] = 15;
        $err['errMsg'] = "member {$member_id} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }

    /**
     * retourner une operation et ses transactions
     */
    public static function show($id, $member_id)
    {
        $op = Operation::where('id', $id)
            ->where('membre_id', $member_id)
            ->first();

        if ($op != "not found") {
            $trans = TransactionMethods::getByOperationId($op->id);

            $success['status'] = 'OK';
            $success['data']['operation'] = $op;
            $success['data']['transactions'] = $trans;

            return $success;
        } else {

            $err['errNo'] = 15;
            $err['errMsg'] = "operation not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function showUniqueId($member_id, $id)
    {
        $op = Operation::where('id', $id)
            ->first();

        if ($op != "not found") {
            $trans = TransactionMethods::getByOperationId($op->id);
            $member = MembreMethods::getById($member_id);
            if ($member != 'not found') {
                $op['membre'] = $member->firstName . " " . $member->lastName;
            }
            $success['status'] = 'OK';
            $success['data']['operation'] = $op;
            $success['data']['transactions'] = $trans;

            return $success;
        } else {

            $err['errNo'] = 15;
            $err['errMsg'] = "operation not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
    /**
     * valider une operation
     */
    public static function validate($id, $type,  $comptes)
    {
        $op = OperationMethods::getById($id);
        DB::beginTransaction();
        try {
            if ($op != "not found") {

                if ($op->etat == "VALIDE") {
                    $err['errNo'] = 13;
                    $err['errMsg'] = "l'opération a déjà été validé";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $transactions = TransactionMethods::getByOperationId($id);
                if ($transactions != "not found") {
                    foreach ($transactions as $key => $transaction) {
                        unset($transaction->activite);

                        switch ($transaction->debit_credit) {
                            case 'credit':
                                $compte = CompteMethods::getById($transaction->comptes_id);
                                if ($compte != "not found") {
                                    unset($compte->membre);

                                    // $member = MembreMethods::getById($compte->membres_id);

                                    // if($transaction->montant  == $transaction->montant_attendu){
                                    //     if ($member != "not found") {
                                    //         $activite_type = ActiviteMethods::getActivityById($compte->activites_id);
                                    //         if ($activite_type != "not found") {
                                    //             if ($activite_type->type == "Mutuelle") {
                                    //                 $mutuelle = MutuelleMethods::getByIdActivite($activite_type->id);
                                    //                 $montant_credit = MutuelleMethods::getAllCreditMutuelleMembreSomme($mutuelle->id, $member->id);
                                    //                 $montant = $transaction->montant - $montant_credit;
                                    //                 if ($mutuelle != "not found" && $montant > 0) {
                                    //                     MutuelleMethods::storeMise($mutuelle->id, $member->id, array("montant" => $montant, "date_versement" => DateMethods::getCurrentDateInt()));
                                    //                 }
                                    //             } 
                                    //         }
                                    //     }
                                    // }

                                    $compte->fill([
                                        'solde_anterieur' => $compte->solde,
                                        'solde' => $compte->solde + $transaction->montant,
                                    ]);
                                    $compte->save();
                                    CompteMethods::MAJComptes($compte, $transaction);

                                    
                                    $montant = $compte->solde-$compte->solde_anterieur;
                                    if($montant > 0){
                                        $member = MembreMethods::getById($compte->membres_id);
                                        if ($member != "not found") {
                                            $activite_type = ActiviteMethods::getActivityById($compte->activites_id);
                                            if ($activite_type != "not found") {
                                                if ($activite_type->type == "Mutuelle") {
                                                    $compte->solde = $compte->solde - $montant;
                                                    $compte->save();
                                                    $mutuelle = MutuelleMethods::getByIdActivite($activite_type->id);
                                                    if ($mutuelle != "not found" && $montant > 0) {
                                                        MutuelleMethods::storeMise($mutuelle->id, $member->id, array("montant" => $montant, "date_versement" => DateMethods::getCurrentDateInt()));
                                                    }
                                                } 
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'debit':
                                $compte = CompteMethods::getById($transaction->comptes_id);
                                if ($compte != "not found") {
                                    unset($compte->membre);
                                    
                                    $solde = CompteMethods::getotalSoldeByActivityId($compte->activites_id);
                                    if ($solde < $transaction->montant) {
                                        // $assocId = ActiviteMethods::getAssociationIdByActiviteId($compte->activites_id);
                                        // if($assocId != "not found"){
                                        //     $tresorerie = ActiviteMethods::getTresorerieByAssociationsData($assocId);
                                        //     if($tresorerie < $transaction->solde){
                                        DB::rollback();
                                        $err['errNo'] = 14;
                                        $err['errMsg'] = "pas assez d'argent pour faire un retrait de $transaction->montant, vous pouvez recevoir maximum $solde";
                                        $error['status'] = 'NOK';
                                        $error['data'] = $err;

                                        return $error;
                                    }
                                    $maj = CompteMethods::MAJComptesDecaissement($compte, $transaction, $type, $comptes);
                                    if ($maj['status'] == "NOK") {
                                        return $maj;
                                    }
                                }
                                break;
                        }
                        if( 
                            $compte->a_supprimer === "oui" &&
                            $compte->solde === 0 && 
                            $compte->avoir === 0 &&
                            $compte->dette_c === 0 &&
                            $compte->dette_a === 0 
                        ){
                            $compte->deleted_at = DateMethods::getCurrentDateInt();
                            $compte->save();
                        }
                        
                        $transaction->fill(['etat' => 'VALIDE']);
                        $transaction->save();
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "transactions not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($op->mode == "WALLET"){
                    $wtransaction = WalletsMethods::woperation($op->id);
                    if($wtransaction['status'] == 'NOK'){
                        DB::rollback();
                        $err['errNo'] = $wtransaction['data']['errNo'];
                        $err['errMsg'] = $wtransaction['data']['errMsg'];
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                }

                $op->fill(['etat' => 'VALIDE']);
                $op->save();
                $success['status'] = 'OK';
                $success['data'] = $op;
                DB::commit();
                return $success;
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = "operation not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * rejeter une operation
     */
    public static function rejeter($id)
    {
        $op = OperationMethods::getById($id);
        DB::beginTransaction();
        try {
            if ($op != "not found") {
                $transactions = TransactionMethods::getByOperationId($id);
                if ($transactions != "not found") {
                    foreach ($transactions as $key => $transaction) {
                        unset($transaction->activite);
                        $transaction->fill(['etat' => 'REJETE']);
                        $transaction->save();
                    }
                } else {
                    $err['errNo'] = 15;
                    $err['errMsg'] = "transactions not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                if($op->mode == "WALLET"){
                    $wtransaction = WalletsMethods::woperationreject($op->id);
                    if($wtransaction['status'] == 'NOK'){
                        DB::rollback();
                        $err['errNo'] = $wtransaction['data']['errNo'];
                        $err['errMsg'] = $wtransaction['data']['errMsg'];
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                }

                $op->fill(['etat' => 'REJETE']);
                $op->save();

                $success['status'] = 'OK';
                $success['data'] = $op;
                DB::commit();
                return $success;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * get associations operations
     */
    public static function getAssociationOperation($assocId)
    {
        $members = MembreMethods::getByAssociationId($assocId);
        $operations = array();
        if ($members != "not found") {
            foreach ($members as $key => $member) {
                $operations[] = array(
                    "membre" => $member->firstName . " " . $member->lastName,
                    "operations" => OperationMethods::getByMemberId($member->id)
                );
            }
        }

        $success['status'] = 'OK';
        $success['data'] = $operations;

        return $success;
    }


    public static function delete($id)
    {
        $op = OperationMethods::getById($id);
        if ($op != "not found") {
            try {
                $op->delete();
                $success['status'] = 'OK';
                $success['data'] = "successfull";

                return $success;
            } catch (\Exception $e) {
                DB::rollback();
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "operation not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
}
