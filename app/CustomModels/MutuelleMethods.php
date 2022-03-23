<?php

namespace App\CustomModels;

use App\Models\Mutuelle;
use App\Models\Credit;
use App\Models\Echeancier;
use App\Models\MiseDeFond;
use Illuminate\Support\Facades\DB;

class MutuelleMethods
{


    public static function getById($id)
    {
        $mutuelle = Mutuelle::find($id);
        if ($mutuelle) {
            return $mutuelle;
        }

        return "not found";
    }

    public static function getByIdActivite($id)
    {
        $mutuelle = Mutuelle::where('activites_id', $id)->first();
        if ($mutuelle) {
            return $mutuelle;
        }

        return "not found";
    }

    public static function getCreditById($id)
    {
        $credit = Credit::find($id);
        if ($credit) {
            return $credit;
        }

        return "not found";
    }

    public static function getMiseById($id)
    {
        $mise = MiseDeFond::find($id);
        if ($mise) {
            return $mise;
        }

        return "not found";
    }


    public static function getAllMutuellesActivite($activites_id)
    {
        $mutuelles = Mutuelle::where('activites_id', $activites_id)->get();
        $success['status'] = "OK";
        $success['data'] = $mutuelles;
        return $success;
    }

    /**
     * récupération de toutes les mises de fonds de mutuelles
     */
    public static function getAllMiseMutuelle($mutuelle_id)
    {
        $Mises = MiseDeFond::where('mutuelles_id', $mutuelle_id)->get();

        foreach ($Mises as $key => $value) {
            $membre = MembreMethods::getById($value->membres_id);
            if ($membre != "not found") {
                $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
            }
        }
        $success['status'] = "OK";
        $success['data'] = $Mises;
        return $success;
    }

    public static function getAllMiseMutuelleMembre($mutuelle_id, $membres_id)
    {
        $Mises = MiseDeFond::where('mutuelles_id', $mutuelle_id)->where("membres_id", $membres_id)->get();

        foreach ($Mises as $key => $value) {
            $membre = MembreMethods::getById($value->membres_id);
            if ($membre != "not found") {
                $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
            }
        }
        $success['status'] = "OK";
        $success['data'] = $Mises;
        return $success;
    }


    public static function getAllMiseMutuelleMembreSomme($mutuelle_id, $membres_id)
    {
        $Mises = MiseDeFond::where('mutuelles_id', $mutuelle_id)->where("membres_id", $membres_id)->get();
        $somme = 0;
        foreach ($Mises as $key => $value) {
            $somme += $value->montant;
        }
        $success['status'] = "OK";
        $success['data'] = $somme;
        return $success;
    }
    public static function getAllMiseMutuelleMembreTotal($mutuelle_id)
    {
        $Mises = MiseDeFond::where('mutuelles_id', $mutuelle_id)->get();
        $somme = 0;
        foreach ($Mises as $key => $value) {
            $somme += $value->montant;
        }
        $success['status'] = "OK";
        $success['data'] = $somme;
        return $success;
    }
    /**
     * récupération de tous les crédits de mutuelles
     */
    public static function getAllCreditMutuelle($mutuelle_id)
    {
        $credits = Credit::where('mutuelles_id', $mutuelle_id)->get();

        foreach ($credits as $key => $value) {
            $membre = MembreMethods::getById($value->membres_id);
            if ($membre != "not found") {
                $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
            }
        }
        $success['status'] = "OK";
        $success['data'] = $credits;
        return $success;
    }

    public static function getAllCreditMutuelleMembreSomme($mutuelle_id, $membres_id)
    {
        $agCourant = AgMethods::getCurrentAg($membres_id);
        $credits = Credit::where('mutuelles_id', $mutuelle_id)->where('membres_id', $membres_id)->get();
        $somme = 0;
        foreach ($credits as $key => $value) {
            if($agCourant != "not found"){
                $echeances = Echeancier::where('serie', "credit-{$value->id}")
                                        ->where('date_limite', $agCourant->date_ag)
                                        ->where('etat','!=', 'cloture')
                                        ->get();
                foreach($echeances as $ech){
                    $somme += ($ech->montant - $ech->montant_realise);
                }
            }
        }
        return $somme;
    }

    public static function getAllCreditAssociation($assoc_id)
    {
        $association = AssociationMethods::getById($assoc_id);
        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "association {$assoc_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $credits = array();
        $activities = ActiviteMethods::getActivitiesByType($assoc_id, "Mutuelle");
        foreach ($activities['data'] as $key => $activity) {
            $credit = Credit::where('mutuelles_activites_id', $activity['id'])
                ->get();
            foreach ($credit as $key => $value) {
                $mutuelle = MutuelleMethods::getById($value->mutuelles_id);
                if ($mutuelle != "not found") {
                    $value['mutuelle'] = $mutuelle;
                }
                $membre = MembreMethods::getById($value->membres_id);
                if ($membre != "not found") {
                    $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
                }
                $value['activity_name'] = $activity['nom'];
                $credits[] = $value;
            }
        }
        return array(
            "status" => "OK",
            "data" => $credits
        );
    }


    /**
     * récupérer toutes les mutuelles d'une association
     */
    public static function getAllMutuellesAssociation($assoc_id)
    {

        $association = AssociationMethods::getById($assoc_id);
        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "association {$assoc_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $activities = ActiviteMethods::getActivitiesByType($assoc_id, "Mutuelle");

        return $activities;
    }


    /**
     * creer un credit pour un membre
     */
    public static function storeCredit($activites_id, $mutuelle_id, $credit)
    {

        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mutuelle = MutuelleMethods::getById($mutuelle_id);

        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelle_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {

            // $exist = Credit::where('membres_id', $credit['membres_id'])
            //                 ->where('mutuelles_id', $mutuelle_id)
            //                 ->where('mutuelles_activites_id', $activites_id)
            //                 ->first();

            // if($exit){
            //     $err['errNo'] = 15;
            //     $err['errMsg'] = "mutuelle {$mutuelle_id} not found";
            //     $error['status'] = 'NOK';
            //     $error['data'] = $err;
            //     return $error;
            // }

            $credit['mutuelles_id'] = $mutuelle_id;
            $credit['mutuelles_activites_id'] = $activites_id;
            $credit['montant_interet'] = ($credit['montant_credit'] * (int)$mutuelle->taux_interet) / 100;
            $credit['montant_restant'] = $credit['montant_credit'];
            $credit['etat'] = "EN_ATTENTE";

            $mutuelleCreated = Credit::create($credit);

            $success['status'] = "OK";
            $success['data'] = $mutuelleCreated;
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * update du credit
     */
    public static function updateCredit($activites_id, $mutuelle_id, $credit_id, $credit)
    {

        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mutuelle = MutuelleMethods::getById($mutuelle_id);

        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelle_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $creditToUpdate = MutuelleMethods::getCreditById($credit_id);
            if ($credit == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "credit {$credit_id} not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $creditToUpdate->fill($credit);
            $creditToUpdate->save();

            $success['status'] = "OK";
            $success['data'] = $creditToUpdate;
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteCredit($credit_id)
    {
        $credit  = Credit::find($credit_id);
        if ($credit) {
            try {
                $credit->delete();
                $success['status'] = "OK";
                $success['data'] = "deleted successfully";
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
            $err['errMsg'] = "credit {$credit_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }




    /**
     * add credit
     */
    public static function storeMise($mutuelles_id, $membres_id, $mise)
    {
        $mutuelle = MutuelleMethods::getById($mutuelles_id);

        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelles_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $membre = MembreMethods::getById($membres_id);
        if ($membre == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "membre {$membres_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $mise['membres_id'] = $membres_id;
            $mise['mutuelles_id'] = $mutuelles_id;
            $mise['date_created'] = DateMethods::getCurrentDateInt();

            $miseAdded = MiseDeFond::create($mise);
            $compte = CompteMethods::getByIdMA($mutuelle->activites_id, $membres_id);
            if ($compte != "not found") {
                $compte->fill([
                    "solde" => $compte->solde + $mise['montant']
                ]);
                $compte->save();
            }
            $success['status'] = "OK";
            $success['data'] = $miseAdded;
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteMise($mise_id)
    {
        $mise  = MiseDeFond::find($mise_id);
        if ($mise) {
            try {

                $mutuelle = MutuelleMethods::getById($mise->mutuelles_id);

                if ($mutuelle == "not found") {
                    $err['errNo'] = 15;
                    $err['errMsg'] = "mutuelle {$mise->mutuelles_id} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $membre = MembreMethods::getById($mise->membres_id);
                if ($membre == "not found") {
                    $err['errNo'] = 15;
                    $err['errMsg'] = "membre {$mise->membres_id} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $compte = CompteMethods::getByIdMA($mutuelle->activites_id, $mise->membres_id);
                if ($compte != "not found") {
                    $compte->fill([
                        "solde" => $compte->solde - $mise->montant
                    ]);
                    $compte->save();
                }
                $mise->delete();
                $success['status'] = "OK";
                $success['data'] = "deleted successfully";
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
            $err['errMsg'] = "mise de fond {$mise_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * store mise in csv file
     */
    public static function storeMiseCsvFile($mutuelles_id, $file)
    {

        $mutuelle = MutuelleMethods::getById($mutuelles_id);

        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelles_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }


        DB::beginTransaction();
        $data = FileManagementMethods::csvToArray($file);
        if ($data && count($data) != 0) {
            $miseAdded = array();
            foreach ($data as $key => $value) {
                $membre = MembreMethods::getById($value['membres_id']);
                if ($membre == "not found") {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "member {$value['membres_id']} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $value['mutuelles_id'] = $mutuelles_id;
                $value['date_versement'] = DateMethods::getDateInt($value['date_versement']);
                $value['date_created'] = DateMethods::getCurrentDateInt();
                unset($value['nom']);
                try {
                    $miseAdded[] = MiseDeFond::create($value);
                    $compte = CompteMethods::getByIdMA($mutuelle->activites_id, $value['membres_id']);
                    if ($compte != "not found") {
                        $compte->fill([
                            "solde" => $compte->solde + $value['montant']
                        ]);
                        $compte->save();
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
            DB::commit();
            $success['status'] = "OK";
            $success['data'] = $miseAdded;
            return $success;
        } else {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = 'the file is empty';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * creation des credit en cours lors de la création de la mutuelle
     */
    public static function storeCreditEnCours($activites_id, $mutuelle_id, $credit, $echeanciers)
    {

        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mutuelle = MutuelleMethods::getById($mutuelle_id);

        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelle_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            DB::beginTransaction();
            $credit['mutuelles_id'] = $mutuelle_id;
            $credit['mutuelles_activites_id'] = $activites_id;
            // $credit['montant_interet'] = ($credit['montant_credit'] * (int)$mutuelle->taux_interet)/100;
            $credit['etat'] = "EN_COURS";

            $mutuelleCreated = Credit::create($credit);
            $compte = CompteMethods::getByIdMA($activites_id, $credit['membres_id']);
            if ($compte != "not found") {
                $compte->fill([
                    "solde" => $compte->solde - $credit['montant_restant']
                ]);
                $compte->save();
            }

            $echeances = MutuelleMethods::generateEcheancesCredit($mutuelleCreated, $mutuelle, "en cours", $echeanciers);
            if ($echeances['status'] ==  "NOK") {
                DB::rollback();
                $err['errNo'] = $echeances['data']['errNo'];
                $err['errMsg'] = $echeances['data']['errMsg'];
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            DB::commit();
            $mutuelleCreated['echeanciers'] = $echeances['data'];
            $success['status'] = "OK";
            $success['data'] = $mutuelleCreated;
            return $success;
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteCreditEnCours($activites_id, $credit_id)
    {

        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::beginTransaction();
        $credit  = Credit::find($credit_id);
        if ($credit) {
            try {
                $compte = CompteMethods::getByIdMA($activites_id, $credit->membres_id);
                if ($compte != "not found") {
                    $compte->fill([
                        "solde" => $compte->solde + $credit->montant_restant
                    ]);
                    $compte->save();
                }
                $echeancier = Echeancier::where('serie', "credit-$credit->id")->delete();

                $credit->delete();

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = "deleted successfully";
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
            $err['errMsg'] = "credit {$credit_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * delete d'une mutuelle
     */
    public static function deleteMutuelle($mutuelle_id)
    {
        $mutuelle  = MutuelleMethods::getById($mutuelle_id);
        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$mutuelle_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $mutuelle->delete();
            $success['status'] = "OK";
            $success['data'] = "delete successfull";
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * creatiion des echeances d'un crédit
     */
    public static function generateEcheancesCredit($credit, $mutuelle, $status, $echeances = [])
    {

        try {
            $compte = CompteMethods::getByIdMA($credit->mutuelles_activites_id, $credit->membres_id);
            if ($compte == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "echeancier mutuelle: compte not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $activite = ActiviteMethods::getActivityById($credit->mutuelles_activites_id);
            if ($activite == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "echeancier mutuelle:  activity not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $details = array(
                "interet" => 0,
                "montant_credit" => 0,
                "montant_a_payer" => 0,
                "montant_credit" => 0
            );

            if ($status == "en cours") {
                foreach ($echeances as $key => $echeance) {
                    $echeancier = array(
                        "date_limite" => $echeance['date_limite'],
                        "montant" => $echeance['montant'],
                        "etat" => "init",
                        "date_created" => DateMethods::getCurrentDateInt(),
                        "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . count($echeances) . ") ",
                        "membres_id" => $compte->membres_id,
                        "comptes_id" => $compte->id,
                        "debit_credit" => "cotisation",
                        "serie" => "credit-{$credit->id}"
                    );
                    $echeanciers[] = Echeancier::create($echeancier);
                    if($echeance['interet'] != 0){
                        $echeancier = array(
                            "date_limite" => $echeance['date_limite'],
                            "montant" => $echeance['interet'],
                            "etat" => "init",
                            "date_created" => DateMethods::getCurrentDateInt(),
                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . count($echeances) . ") ",
                            "membres_id" => $compte->membres_id,
                            "comptes_id" => $compte->id,
                            "debit_credit" => "acquitement",
                            "serie" => "credit-{$credit->id}"
                        );
                        $echeanciers[] = Echeancier::create($echeancier);
                    }
                }
            } else {
                $echeanciers = array();
                $interet = 0;
                switch ($mutuelle->methode_calcul_interet) {
                    case 'FIXE':
                        $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                        $details['interet'] = $interet;
                        $montant_echeance = 0;
                        switch ($mutuelle->paiement_interet) {
                            case 'ATTRIBUTION':
                                $interet = 0;
                                $montant_echeance = $credit['montant_restant'];
                                break;

                            case 'MEME_TEMPS':
                                $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                                $montant_echeance = $credit['montant_restant'];
                                break;
                        }
                        switch ($mutuelle->remboursement) {
                            case 'EGAUX':

                                $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                foreach ($nextAgs as $key => $ag) {
                                    $echeancier = array(
                                        "date_limite" => $ag->date_ag,
                                        "montant" => ($montant_echeance) / $credit['date_limite'],
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "cotisation",
                                        "serie" => "credit-{$credit->id}"
                                    );
                                    $echeanciers[] = Echeancier::create($echeancier);
                                    if($interet != 0){
                                        $echeancier = array(
                                            "date_limite" => $ag->date_ag,
                                            "montant" => ($interet) / $credit['date_limite'],
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            "serie" => "credit-{$credit->id}"
                                        );
                                        $echeanciers[] = Echeancier::create($echeancier);
                                    }
                                    
                                }

                                if (count($nextAgs) < $credit['date_limite']) {
                                    for ($i = count($nextAgs); $i < $credit['date_limite']; $i++) {
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => ($montant_echeance) / $credit['date_limite'],
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            "serie" => "credit-{$credit->id}"
                                        );
                                        $echeanciers[] = Echeancier::create($echeancier);
                                        if($interet != 0){
                                            $echeancier = array(
                                                "date_limite" => 0,
                                                "montant" => ($interet) / $credit['date_limite'],
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "acquitement",
                                                "serie" => "credit-{$credit->id}"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                        }
                                        
                                    }
                                }
                                break;

                            case 'DISCRETION':
                                $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                $last = $nextAgs[count($nextAgs) - 1];
                                if (count($nextAgs) == $credit['date_limite']) {
                                    $echeancier = array(
                                        "date_limite" => $last->date_ag,
                                        "montant" => $montant_echeance,
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "cotisation",
                                        "serie" => "credit-$credit->id"
                                    );
                                    $echeanciers[] = Echeancier::create($echeancier);
                                    if($interet != 0){
                                        $echeancier = array(
                                            "date_limite" => $last->date_ag,
                                            "montant" => $interet,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = Echeancier::create($echeancier);
                                    }
                                    
                                } else {
                                    $echeancier = array(
                                        "date_limite" => 0,
                                        "montant" => $montant_echeance,
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "cotisation",
                                        "serie" => "credit-$credit->id",
                                        'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                    );
                                    $echeanciers[] = Echeancier::create($echeancier);
                                    if($interet != 0){
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => $interet,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            "serie" => "credit-$credit->id",
                                            'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                        );
                                        $echeanciers[] = Echeancier::create($echeancier);
                                    }
                                    
                                }

                                break;
                        }
                        $details['montant_credit'] = $credit['montant_restant'];
                        $details['montant_a_payer'] = $montant_echeance;
                        break;

                    case 'DUREE':
                        switch ($mutuelle->remboursement) {
                            case 'EGAUX':
                                $interet = 0;
                                $part = $credit['montant_restant'] / $credit['date_limite'];
                                for ($i = 1; $i <= $credit['date_limite']; $i++) {
                                    $interet += ($credit['montant_restant'] - ($part * ($i - 1))) * $mutuelle->taux_interet / 100;
                                }
                                $details['interet'] = $interet;
                                switch ($mutuelle->paiement_interet) {
                                    case 'ATTRIBUTION':
                                        $montant_echeance = $credit['montant_restant'];

                                        $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                        foreach ($nextAgs as $key => $ag) {
                                            $echeancier = array(
                                                "date_limite" => $ag->date_ag,
                                                "montant" => $montant_echeance / $credit['date_limite'],
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            // $echeancier = array(
                                            //     "date_limite" => $ag->date_ag,
                                            //     "montant" => $interet / $credit['date_limite'],
                                            //     "etat" => "init",
                                            //     "date_created" => DateMethods::getCurrentDateInt(),
                                            //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                            //     "membres_id" => $compte->membres_id,
                                            //     "comptes_id" => $compte->id,
                                            //     "debit_credit" => "acquitement",
                                            //     "serie" => "credit-$credit->id"
                                            // );
                                            // $echeanciers[] = Echeancier::create($echeancier);
                                        }

                                        if (count($nextAgs) < $credit['date_limite']) {
                                            for ($i = count($nextAgs); $i < $credit['date_limite']; $i++) {
                                                $echeancier = array(
                                                    "date_limite" => 0,
                                                    "montant" => ($montant_echeance) / $credit['date_limite'],
                                                    "etat" => "init",
                                                    "date_created" => DateMethods::getCurrentDateInt(),
                                                    "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                    "membres_id" => $compte->membres_id,
                                                    "comptes_id" => $compte->id,
                                                    "debit_credit" => "cotisation",
                                                    "serie" => "credit-{$credit->id}"
                                                );
                                                $echeanciers[] = Echeancier::create($echeancier);
                                                // $echeancier = array(
                                                //     "date_limite" => 0,
                                                //     "montant" => ($interet) / $credit['date_limite'],
                                                //     "etat" => "init",
                                                //     "date_created" => DateMethods::getCurrentDateInt(),
                                                //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                //     "membres_id" => $compte->membres_id,
                                                //     "comptes_id" => $compte->id,
                                                //     "debit_credit" => "acquitement",
                                                //     "serie" => "credit-{$credit->id}"
                                                // );
                                                // $echeanciers[] = Echeancier::create($echeancier);
                                            }
                                        }

                                        $details['montant_credit'] = $credit['montant_restant'];
                                        $details['montant_a_payer'] = $montant_echeance;
                                        break;

                                    case 'MEME_TEMPS':
                                        $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                        for ($i = 1; $i <= $credit['date_limite']; $i++) {
                                            $montant_interet = ($credit['montant_restant'] - ($part * ($i - 1))) * $mutuelle->taux_interet / 100;
                                            $montant_echeance = $part;
                                            $details['montant_a_payer'] += $montant_echeance;
                                            $echeancier = array(
                                                "date_limite" => $nextAgs[$i - 1]->date_ag ?? 0,
                                                "montant" => $montant_echeance,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            $echeancier = array(
                                                "date_limite" => $nextAgs[$i - 1]->date_ag ?? 0,
                                                "montant" => $montant_interet,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "acquitement",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                        }

                                        $details['montant_credit'] = $credit['montant_restant'];
                                        break;
                                }


                                break;

                            case 'DISCRETION':

                                $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                                $details['interet'] = $interet;

                                switch ($mutuelle->paiement_interet) {
                                    case 'ATTRIBUTION':
                                        $montant_echeance = $credit['montant_restant'];

                                        $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                        $last = $nextAgs[count($nextAgs) - 1];

                                        if (count($nextAgs) == $credit['date_limite']) {
                                            $echeancier = array(
                                                "date_limite" => $last->date_ag,
                                                "montant" => $montant_echeance,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            // $echeancier = array(
                                            //     "date_limite" => $last->date_ag,
                                            //     "montant" => $interet,
                                            //     "etat" => "init",
                                            //     "date_created" => DateMethods::getCurrentDateInt(),
                                            //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            //     "membres_id" => $compte->membres_id,
                                            //     "comptes_id" => $compte->id,
                                            //     "debit_credit" => "acquitement",
                                            //     "serie" => "credit-$credit->id"
                                            // );
                                            // $echeanciers[] = Echeancier::create($echeancier);
                                        } else {
                                            $echeancier = array(
                                                "date_limite" => 0,
                                                "montant" => $montant_echeance,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id",
                                                'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            // $echeancier = array(
                                            //     "date_limite" => 0,
                                            //     "montant" => $interet,
                                            //     "etat" => "init",
                                            //     "date_created" => DateMethods::getCurrentDateInt(),
                                            //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            //     "membres_id" => $compte->membres_id,
                                            //     "comptes_id" => $compte->id,
                                            //     "debit_credit" => "acquitement",
                                            //     "serie" => "credit-$credit->id",
                                            //     'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                            // );
                                            // $echeanciers[] = Echeancier::create($echeancier);
                                        }

                                        $details['montant_credit'] = $credit['montant_restant'];
                                        $details['montant_a_payer'] = $montant_echeance;

                                        break;

                                    case 'MEME_TEMPS':
                                        $montant_echeance = $credit['montant_restant'];
                                        $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                        $last = $nextAgs[count($nextAgs) - 1];
                                        if (count($nextAgs) == $credit['date_limite']) {
                                            $echeancier = array(
                                                "date_limite" => $last->date_ag,
                                                "montant" => $montant_echeance,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            $echeancier = array(
                                                "date_limite" => $last->date_ag,
                                                "montant" => $interet,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "acquitement",
                                                "serie" => "credit-$credit->id"
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                        } else {
                                            $echeancier = array(
                                                "date_limite" => 0,
                                                "montant" => $montant_echeance,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                "serie" => "credit-$credit->id",
                                                'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                            $echeancier = array(
                                                "date_limite" => 0,
                                                "montant" => $interet,
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "acquitement",
                                                "serie" => "credit-$credit->id",
                                                'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                            );
                                            $echeanciers[] = Echeancier::create($echeancier);
                                        }
                                        $details['montant_credit'] = $credit['montant_restant'];
                                        $details['montant_a_payer'] = $montant_echeance;

                                        break;
                                }

                                break;
                        }
                        break;
                }

                $comptes = CompteMethods::getByIdAAll($credit->mutuelles_activites_id);
                $total = MutuelleMethods::getAllMiseMutuelleMembreTotal($credit->mutuelles_id)['data'];
                foreach ($comptes as $key => $compte) {
                    $mise = MutuelleMethods::getAllMiseMutuelleMembreSomme($credit->mutuelles_id, $compte->membres_id);
                    $cal = 0;
                    if($total > 0) $cal = $mise['data'] / $total;
                    $genere = round($cal * $interet, 2);
                    if($genere != 0){
                        $compte->fill([
                            "interet" => $compte->interet + $genere
                        ]);
                        $compte->save();
                    }
                }
            }

            $success['status'] = "OK";
            $success['data'] = $echeanciers;

            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = "echeancier mutuelle: " . $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * preview des echeances de credit
     */
    public static function previewEcheancesCredit($credit, $mutuelle_id)
    {
        $mutuelle = MutuelleMethods::getById($mutuelle_id);
        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "preview écheances crédit: mutuelle not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            $compte = CompteMethods::getByIdMA($credit['mutuelles_activites_id'], $credit['membres_id']);
            if ($compte == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "preview écheances crédit: compte not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $activite = ActiviteMethods::getActivityById($credit['mutuelles_activites_id']);
            if ($activite == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "preview écheances crédit:  activity not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $details = array(
                "interet" => 0,
                "montant_credit" => 0,
                "montant_a_payer" => 0,
                "montant_credit" => 0
            );

            $echeanciers = array();
            switch ($mutuelle->methode_calcul_interet) {
                case 'FIXE':
                    $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                    $details['interet'] = $interet;
                    $montant_echeance = 0;
                    switch ($mutuelle->paiement_interet) {
                        case 'ATTRIBUTION':
                            $interet = 0;
                            $montant_echeance = $credit['montant_restant'];
                            break;

                        case 'MEME_TEMPS':
                            $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                            $montant_echeance = $credit['montant_restant'];
                            break;
                    }
                    switch ($mutuelle->remboursement) {
                        case 'EGAUX':

                            $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                            foreach ($nextAgs as $key => $ag) {
                                $echeancier = array(
                                    "date_limite" => $ag->date_ag,
                                    "montant" => ($montant_echeance) / $credit['date_limite'],
                                    "etat" => "init",
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                    "membres_id" => $compte->membres_id,
                                    "comptes_id" => $compte->id,
                                    "debit_credit" => "cotisation",
                                    // "serie" => "credit-{$credit->id}"
                                );
                                $echeanciers[] = $echeancier;
                                if($interet != 0){
                                    $echeancier = array(
                                        "date_limite" => $ag->date_ag,
                                        "montant" => ($interet) / $credit['date_limite'],
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "acquitement",
                                        // "serie" => "credit-{$credit->id}"
                                    );
                                    $echeanciers[] = $echeancier;
                                }
                            }

                            if (count($nextAgs) < $credit['date_limite']) {
                                for ($i = count($nextAgs); $i < $credit['date_limite']; $i++) {
                                    $echeancier = array(
                                        "date_limite" => 0,
                                        "montant" => ($montant_echeance) / $credit['date_limite'],
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "cotisation",
                                        // "serie" => "credit-{$credit->id}"
                                    );
                                    $echeanciers[] = $echeancier;
                                    if($interet != 0){
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => ($interet) / $credit['date_limite'],
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            // "serie" => "credit-{$credit->id}"
                                        );
                                        $echeanciers[] = $echeancier;
                                    }
                                    
                                }
                            }
                            break;

                        case 'DISCRETION':
                            $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                            $last = $nextAgs[count($nextAgs) - 1];
                            if (count($nextAgs) == $credit['date_limite']) {
                                $echeancier = array(
                                    "date_limite" => $last->date_ag,
                                    "montant" => $montant_echeance,
                                    "etat" => "init",
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                    "membres_id" => $compte->membres_id,
                                    "comptes_id" => $compte->id,
                                    "debit_credit" => "cotisation",
                                    // "serie" => "credit-$credit->id"
                                );
                                $echeanciers[] = $echeancier;
                                if($interet != 0){
                                    $echeancier = array(
                                        "date_limite" => $last->date_ag,
                                        "montant" => $interet,
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "acquitement",
                                        // "serie" => "credit-$credit->id"
                                    );
                                    $echeanciers[] = $echeancier;
                                }
                               
                            } else {
                                $echeancier = array(
                                    "date_limite" => 0,
                                    "montant" => $montant_echeance,
                                    "etat" => "init",
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                    "membres_id" => $compte->membres_id,
                                    "comptes_id" => $compte->id,
                                    "debit_credit" => "cotisation",
                                    // "serie" => "credit-$credit->id",
                                    'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                );
                                $echeanciers[] = $echeancier;
                                if($interet != 0){
                                    $echeancier = array(
                                        "date_limite" => 0,
                                        "montant" => $interet,
                                        "etat" => "init",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "acquitement",
                                        // "serie" => "credit-$credit->id",
                                        'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                    );
                                    $echeanciers[] = $echeancier;
                                }
                                
                            }

                            break;
                    }
                    $details['montant_credit'] = $credit['montant_restant'];
                    $details['montant_a_payer'] = $montant_echeance;
                    break;

                case 'DUREE':
                    switch ($mutuelle->remboursement) {
                        case 'EGAUX':
                            $interet = 0;
                            $part = $credit['montant_restant'] / $credit['date_limite'];
                            for ($i = 1; $i <= $credit['date_limite']; $i++) {
                                $interet += ($credit['montant_restant'] - ($part * ($i - 1))) * $mutuelle->taux_interet / 100;
                            }
                            $details['interet'] = $interet;
                            switch ($mutuelle->paiement_interet) {
                                case 'ATTRIBUTION':
                                    $montant_echeance = $credit['montant_restant'];

                                    $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                    foreach ($nextAgs as $key => $ag) {
                                        $echeancier = array(
                                            "date_limite" => $ag->date_ag,
                                            "montant" => $montant_echeance / $credit['date_limite'],
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                        // $echeancier = array(
                                        //     "date_limite" => $ag->date_ag,
                                        //     "montant" => $interet / $credit['date_limite'],
                                        //     "etat" => "init",
                                        //     "date_created" => DateMethods::getCurrentDateInt(),
                                        //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($key + 1) . "/" . $credit['date_limite'] . ") ",
                                        //     "membres_id" => $compte->membres_id,
                                        //     "comptes_id" => $compte->id,
                                        //     "debit_credit" => "acquitement",
                                        //     // "serie" => "credit-$credit->id"
                                        // );
                                        // $echeanciers[] = $echeancier;
                                    }

                                    if (count($nextAgs) < $credit['date_limite']) {
                                        for ($i = count($nextAgs); $i < $credit['date_limite']; $i++) {
                                            $echeancier = array(
                                                "date_limite" => 0,
                                                "montant" => ($montant_echeance) / $credit['date_limite'],
                                                "etat" => "init",
                                                "date_created" => DateMethods::getCurrentDateInt(),
                                                "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                                "membres_id" => $compte->membres_id,
                                                "comptes_id" => $compte->id,
                                                "debit_credit" => "cotisation",
                                                // "serie" => "credit-{$credit->id}"
                                            );
                                            $echeanciers[] = $echeancier;
                                            // $echeancier = array(
                                            //     "date_limite" => 0,
                                            //     "montant" => ($interet) / $credit['date_limite'],
                                            //     "etat" => "init",
                                            //     "date_created" => DateMethods::getCurrentDateInt(),
                                            //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                            //     "membres_id" => $compte->membres_id,
                                            //     "comptes_id" => $compte->id,
                                            //     "debit_credit" => "acquitement",
                                            //     // "serie" => "credit-{$credit->id}"
                                            // );
                                            // $echeanciers[] = $echeancier;
                                        }
                                    }

                                    $details['montant_credit'] = $credit['montant_restant'];
                                    $details['montant_a_payer'] = $montant_echeance;
                                    break;

                                case 'MEME_TEMPS':
                                    $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                    for ($i = 1; $i <= $credit['date_limite']; $i++) {
                                        $montant_interet = ($credit['montant_restant'] - ($part * ($i - 1))) * $mutuelle->taux_interet / 100;
                                        $montant_echeance = $part;
                                        $details['montant_a_payer'] += $montant_echeance;
                                        $echeancier = array(
                                            "date_limite" => $nextAgs[$i - 1]->date_ag ?? 0,
                                            "montant" => $montant_echeance,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                        $echeancier = array(
                                            "date_limite" => $nextAgs[$i - 1]->date_ag ?? 0,
                                            "montant" => $montant_interet,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (" . ($i + 1) . "/" . $credit['date_limite'] . ") ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                    }

                                    $details['montant_credit'] = $credit['montant_restant'];
                                    break;
                            }


                            break;

                        case 'DISCRETION':

                            $interet = ($mutuelle->taux_interet * $credit['montant_restant']) / 100;
                            $details['interet'] = $interet;

                            switch ($mutuelle->paiement_interet) {
                                case 'ATTRIBUTION':
                                    $montant_echeance = $credit['montant_restant'];

                                    $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                    $last = $nextAgs[count($nextAgs) - 1];

                                    if (count($nextAgs) == $credit['date_limite']) {
                                        $echeancier = array(
                                            "date_limite" => $last->date_ag,
                                            "montant" => $montant_echeance,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                        // $echeancier = array(
                                        //     "date_limite" => $last->date_ag,
                                        //     "montant" => $interet,
                                        //     "etat" => "init",
                                        //     "date_created" => DateMethods::getCurrentDateInt(),
                                        //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        //     "membres_id" => $compte->membres_id,
                                        //     "comptes_id" => $compte->id,
                                        //     "debit_credit" => "acquitement",
                                        //     // "serie" => "credit-$credit->id"
                                        // );
                                        // $echeanciers[] = $echeancier;
                                    } else {
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => $montant_echeance,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id",
                                            'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                        );
                                        $echeanciers[] = $echeancier;
                                        // $echeancier = array(
                                        //     "date_limite" => 0,
                                        //     "montant" => $interet,
                                        //     "etat" => "init",
                                        //     "date_created" => DateMethods::getCurrentDateInt(),
                                        //     "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                        //     "membres_id" => $compte->membres_id,
                                        //     "comptes_id" => $compte->id,
                                        //     "debit_credit" => "acquitement",
                                        //     // "serie" => "credit-$credit->id",
                                        //     'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                        // );
                                        // $echeanciers[] = $echeancier;
                                    }

                                    $details['montant_credit'] = $credit['montant_restant'];
                                    $details['montant_a_payer'] = $montant_echeance;

                                    break;

                                case 'MEME_TEMPS':
                                    $montant_echeance = $credit['montant_restant'];
                                    $nextAgs  = AgMethods::getNextAgMemberNumber($credit['membres_id'], $credit['date_demande'],  $credit['date_limite']);
                                    $last = $nextAgs[count($nextAgs) - 1];
                                    if (count($nextAgs) == $credit['date_limite']) {
                                        $echeancier = array(
                                            "date_limite" => $last->date_ag,
                                            "montant" => $montant_echeance,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                        $echeancier = array(
                                            "date_limite" => $last->date_ag,
                                            "montant" => $interet,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            // "serie" => "credit-$credit->id"
                                        );
                                        $echeanciers[] = $echeancier;
                                    } else {
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => $montant_echeance,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            // "serie" => "credit-$credit->id",
                                            'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                        );
                                        $echeanciers[] = $echeancier;
                                        $echeancier = array(
                                            "date_limite" => 0,
                                            "montant" => $interet,
                                            "etat" => "init",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Acquitement - " . $activite->nom . " - Credit - Remboursement - (1/1) ",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "acquitement",
                                            // "serie" => "credit-$credit->id",
                                            'next_date_in' => $credit['date_limite'] - count($nextAgs)
                                        );
                                        $echeanciers[] = $echeancier;
                                    }
                                    $details['montant_credit'] = $credit['montant_restant'];
                                    $details['montant_a_payer'] = $montant_echeance;

                                    break;
                            }

                            break;
                    }
                    break;
            }


            $success['status'] = "OK";
            $success['data'] = array(
                "echeances" => $echeanciers,
                "detail" => $details
            );

            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = "echeancier mutuelle: " . $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * approuver un credit par un administrateur
     */
    public static function approuveCredit($credit_id)
    {

        $credit = MutuelleMethods::getCreditById($credit_id);
        if ($credit == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "credit {$credit_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mutuelle = MutuelleMethods::getById($credit->mutuelles_id);
        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mutuelle {$credit->mutuelles_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $compte = CompteMethods::getByIdMA($credit->mutuelles_activites_id, $credit->membres_id);
        if ($compte == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "echeancier mutuelle: compte not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $activite = ActiviteMethods::getActivityById($credit->mutuelles_activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "echeancier mutuelle:  activity not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        DB::beginTransaction();
        try {
            $nexAg = AgMethods::getCurrentAg($credit['membres_id']);
            $echeance = null;
            if ($nexAg != "not found") {
                if($mutuelle->paiement_interet === "ATTRIBUTION"){
                    $montant = $credit->montant_credit - $credit->montant_interet;
                }else{
                    $montant = $credit->montant_credit;
                }
                $echeancier = array(
                    "date_limite" => $nexAg->date_ag,
                    "montant" => $montant,
                    "etat" => "init",
                    "date_created" => DateMethods::getCurrentDateInt(),
                    "libelle" => "Décaissement - " . $activite->nom . " - Credit",
                    "membres_id" => $compte->membres_id,
                    "comptes_id" => $compte->id,
                    "debit_credit" => "decaissement",
                    "serie" => "credit-$credit->id"
                );
                $echeance = Echeancier::create($echeancier);
            }

            $credit->fill([
                "etat" => "VALIDE",
                "echeances" => $echeance->id ?? null
            ]);
            $credit->save();

            $echeances = MutuelleMethods::generateEcheancesCredit($credit, $mutuelle, "credit", []);
            if ($echeances['status'] == "NOK") {
                DB::rollback();
                $err['errNo'] = $echeances['data']['errNo'];
                $err['errMsg'] = $echeances['data']['errMsg'];
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            DB::commit();
            $success['status'] = "OK";
            $success['data'] = $credit;

            return $success;
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
     * rejeter une requete de credit 
     */
    public static function rejectCredit($activites_id, $credit_id)
    {
        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::beginTransaction();
        $credit  = Credit::find($credit_id);
        if ($credit) {
            try {
                $compte = CompteMethods::getByIdMA($activites_id, $credit->membres_id);
                if ($compte != "not found") {
                    $compte->fill([
                        "solde" => $compte->solde + $credit->montant_restant
                    ]);
                    $compte->save();
                }
                $echeancier = Echeancier::where('serie', "credit-$credit->id")->delete();

                $credit->fill([
                    "etat" => "REJETE"
                ]);
                $credit->save();

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $credit;

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
            $err['errMsg'] = "credit {$credit_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function annulerCredit($activites_id, $credit_id)
    {
        $activite = ActiviteMethods::getActivityById($activites_id);

        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::beginTransaction();
        $credit  = Credit::find($credit_id);
        if ($credit) {
            try {
                $compte = CompteMethods::getByIdMA($activites_id, $credit->membres_id);
                if ($compte != "not found") {
                    $compte->fill([
                        "solde" => $compte->solde + $credit->montant_restant
                    ]);
                    $compte->save();
                }
                $echeancier = Echeancier::where('serie', "credit-$credit->id")->delete();

                $credit->fill([
                    "etat" => "ANNULE"
                ]);
                $credit->save();

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $credit;

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
            $err['errMsg'] = "credit {$credit_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    // public static function deleteMise($mise_id){
    //     try {
    //         MiseDeFond::find($mise_id)->delete();
    //         $success['status'] = "OK";
    //         $success['data'] = "deleted successfully";
    //         return $success;
    //     } catch(\Exception $e){
    //         $err['errNo'] = 11;
    //         $err['errMsg'] = $e->getMessage();
    //         $error['status'] = 'NOK';
    //         $error['data'] = $err;
    //         return $error;
    //     }
    // }

    public static function misesDeFondEtDuree($activites_id)
    {

        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mise de fond et duree: activite {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mutuelle = MutuelleMethods::getByIdActivite($activites_id);
        if ($mutuelle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "mise de fond et duree: mutuelle of activity {$activites_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $mises = MutuelleMethods::getAllMiseMutuelle($mutuelle->id);
        $array = array();
        $dureeTotal = 0;
        $miseTotal = 0;
        foreach ($mises['data'] as $key => $mise) {
            $duree = (DateMethods::getCurrentDateInt() - $mise->date_versement) * 3600 * 24;
            $array[] = array(
                "mise" => $mise,
                "duree" => $duree
            );
            $dureeTotal += $duree;
            $miseTotal += $mise->montant;
        }

        foreach ($array as $key => $value) {
            $ponderation = $value['duree'] / $dureeTotal;
            $value['ponderation'] = round($ponderation * 100);

            $interet = $activite->caisse * $ponderation;
            $value['interet'] = $interet;
        }

        return array(
            "status" => "OK",
            "data" => $array
        );
    }
}
