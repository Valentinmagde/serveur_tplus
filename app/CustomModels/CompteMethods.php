<?php

namespace App\CustomModels;

use App\Models\Compte;
use App\Models\Echeancier;
use Illuminate\Support\Facades\DB;

class CompteMethods
{

    public static function getActivityByCompteId($id)
    {

        $compte = Compte::where('id', $id)
            ->first();
        if ($compte) {
            $activite =  ActiviteMethods::getActivityById($compte->activites_id);
            if ($activite != "not found") {
                return $activite;
            }
        }

        return "not found";
    }

    public static function getById($id)
    {
        $compte = Compte::where('id', $id)
            ->first();

        if ($compte) {
            $membre = MembreMethods::getById($compte->membres_id);
            if ($membre != "not found") {
                $compte['membre'] = $membre->firstName . ' ' . $membre->lastName;
                return $compte;
            }
        }

        return "not found";
    }


    public static function getCompteCaisse($membre)
    {
        $activite = ActiviteMethods::getActivityByType('caisse');
        if ($activite != "not found") {
            $compte = CompteMethods::getByIdMA($activite->id, $membre);
            if ($compte != "not found") {
                return $compte;
            } else {
                return "not found";
            }
        } else {
            return "not found";
        }
    }

    public static function getByIdWithIdMember($id)
    {
        $compte = Compte::where('id', $id)
            ->first();

        if ($compte) {
            $membre = MembreMethods::getById($compte->membres_id);
            if ($membre != "not found") {
                $compte['membre'] = $membre->firstName . ' ' . $membre->lastName;
                $compte['membres_id'] = $membre->id;
                return $compte;
            }
        }

        return "not found";
    }

    public static function getComptesAssociations($assocId)
    {
        $membres = MembreMethods::getMemberAssociation($assocId);
        $comptes = array();
        foreach ($membres as $key => $membre) {
            $all = CompteMethods::getComptesByMember($membre->id);
            foreach ($all as $key => $value) {
                $activite = ActiviteMethods::getActivityById($value->activites_id);
                if ($activite != "not found") {
                    $statistique = MembreMethods::getStatistiqueMembreActivity($membre->id, $activite->id);
                    $value['statistique'] = $statistique['data'];
                    $value['type_activite'] = $activite->type;
                    $value['nom_activite'] = $activite->nom;
                }
                $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
                $comptes[] = $value;
            }
        }

        return array(
            "status" => "OK",
            "data" => $comptes
        );
    }

    public static function getotalSoldeByActivityId($activite)
    {
        $comptes = Compte::where("activites_id", $activite)->get();

        $solde = 0;
        foreach ($comptes as $key => $compte) {
            $solde += $compte->solde;
        }

        $activite = ActiviteMethods::getActivityById($activite);
        if ($activite != "not found") {
            $solde += $activite->caisse;
        }

        return $solde;
    }


    public static function getAll($activite)
    {
        $comptes = Compte::where("activites_id", $activite)->get();

        $data = array();
        $statistiques = array();
        foreach ($comptes as $key => $compte) {
            $transactions = TransactionMethods::getByCompteId($compte->id);
            $statistique = MembreMethods::getStatistiqueMembreActivity($compte->membres_id, $activite);
            $compte['statistiques'] = $statistique['data'];
            if ($transactions != "not found") {
                $compte['transactions'] = $transactions;
            }
            $membre = MembreMethods::getById($compte->membres_id);
            if ($membre != "not found") {
                $compte['membre'] = $membre->firstName . ' ' . $membre->lastName;
            }
            $data[] = $compte;
        }

        $success['status'] = 'OK';
        $success['data'] = $data;

        return $success;
    }

    public static function getByIdMA($activite, $membre)
    {
        $compte = Compte::where('activites_id', $activite)
            ->where('membres_id', $membre)
            ->first();

        if ($compte) {
            return $compte;
        }

        return "not found";
    }

    public static function getByIdMAEndPoint($activite, $membre)
    {
        $compte = Compte::where('activites_id', $activite)
            ->where('membres_id', $membre)
            ->first();

        if ($compte) {
            $transactions = TransactionMethods::getByCompteId($compte->id);
            $statistique = MembreMethods::getStatistiqueMembreActivity($compte->membres_id, $activite);
            $compte['statistiques'] = $statistique['data'];
            if ($transactions != "not found") {
                $compte['transactions'] = $transactions;
            }
            $membre = MembreMethods::getById($compte->membres_id);
            if ($membre != "not found") {
                $compte['membre'] = $membre->firstName . ' ' . $membre->lastName;
            }
            return $compte;
        }

        return "not found";
    }

    public static function getByIdA($activite)
    {
        $compte = Compte::where('activites_id', $activite)
            ->first();

        if ($compte) {
            return $compte;
        }

        return "not found";
    }

    public static function getByIdAAll($activite)
    {
        $compte = Compte::where('activites_id', $activite)
            ->get();

        return $compte;
    }

    public static function storeNewMember($compte, $montant_cotisation, $member_id, $activite_id)
    {
        DB::beginTransaction();
        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            $activite = ActiviteMethods::getActivityById($activite_id);

            if ($activite != "not found" && $activite['type'] == "Tontine") {
                try {
                    $tontine = ActiviteMethods::getSomeActivityByTypeActivityId("Tontine", $activite_id);
                    if($tontine == "not found"){
                        $err['errNo'] = 10;
                        $err['errMsg'] = "tontine not found";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }

                    $currentAg = AgMethods::getCurrentAg($member_id);
                    if($currentAg == "not found"){
                        $err['errNo'] = 10;
                        $err['errMsg'] = "pas d'ag actif";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }

                    $echeances = Echeancier::where('serie',"tontine-$tontine->id")->get();
                    $dateFin = $echeances[count($echeances)-1]->date_limite;
                    $nextAgs = AgMethods::getAllNextAgsIncludeDateDebutUntilDateFin($currentAg->cycles_id, $currentAg->date_ag, $dateFin);
                    $duree = count($nextAgs);
                    $montant_bouffe = $duree * $montant_cotisation;
                    
                    $exist = CompteMethods::getByIdMA($activite_id, $member_id);

                    if ($exist == "not found") {

                        $compte['activites_id'] = $activite_id;
                        $compte['membres_id'] = $member_id;

                        if (isset($compte['solde']))
                            $compte['solde_anterieur'] = 0;
                        else $compte['solde'] = 0;
                        if (!isset($compte['nombre_noms']))
                            $compte['nombre_noms'] = 1;


                        $com = Compte::create($compte);
                        $echeanciers = array();
                       

                        foreach ($nextAgs as $key => $ag) {
                            for ($i = 0; $i < $com->nombre_noms; $i++) {
                                $echeancier = array(
                                    "date_limite" => $ag->date_ag,
                                    "montant" => $montant_cotisation,
                                    "etat" => "init" ,
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "libelle" => "Cotisation - " . $activite->nom . " -  Part tontine (" . ($i + 1) . "/" . $com->nombre_noms . ") - Tour (" . ($key + 1) . "/" . $duree . ") - Nouveau",
                                    "membres_id" => $com->membres_id,
                                    "comptes_id" => $com->id,
                                    "debit_credit" => "cotisation",
                                    "serie" => "tontine-$tontine->id"
                                );
                                $echeanciers[] = Echeancier::create($echeancier);
                            }
                        }

                    
                        $lots = TontineMethods::createNewMemberLotAdded($com, $activite, $montant_bouffe, $nextAgs[$duree - 1]->date_ag);
                        if ($lots['status'] == 'OK') {

                            foreach ($lots['data'] as $key => $lot) {
                                $lot->date_bouffe =  $nextAgs[$duree - 1]->date_ag;

                                $echeancier = array(
                                    "comptes_id" => $lot['comptes_id'],
                                    "montant" => $lot['montant'],
                                    "debit_credit" => "decaissement",
                                    "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                                    "date_limite" => $lot['date_bouffe'],
                                    "serie" => "lot-{$lot['id']}",
                                    "etat" => "init"
                                );
        
                                $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);

                                if ($ech["status"] != "OK") {
                                    DB::rollback();
                                    $err['errNo'] = 15;
                                    $err['errMsg'] = 'erreur lors de la création de l\'echeancier';
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }

                                $lot->echeanciers_id = $ech['data']['id'];
                                $lot->save();
                            }

                        } else {
                            DB::rollback();
                            $err['errNo'] = 10;
                            $err['errMsg'] = $lots['data']['errMsg'];
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }


                        DB::commit();
                        $success['status'] = "OK";
                        $success['data'] = $com;

                        return $success;
                    } else {
                        DB::rollback();

                        $err['errNo'] = 15;
                        $err['errMsg'] = "le compte existe déjà";
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

            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = "Activity {$activite_id} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::rollback();
        $err['errNo'] = 15;
        $err['errMsg'] = "member {$member_id} not found";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }

    public static function store($compte, $member_id, $activite_id)
    {

        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            $activite = ActiviteMethods::getActivityById($activite_id);

            if ($activite != "not found") {
                try {

                    $exist = CompteMethods::getByIdMA($activite_id, $member_id);

                    if ($exist == "not found") {
                        $compte['activites_id'] = $activite_id;
                        $compte['membres_id'] = $member_id;
                        if (isset($compte['solde']))
                            $compte['solde_anterieur'] = 0;
                        if (!isset($compte['nombre_noms']))
                            $compte['nombre_noms'] = 1;


                        $com = Compte::create($compte);

                        $success['status'] = "OK";
                        $success['data'] = $com;

                        return $success;
                    } else {

                        $err['errNo'] = 20;
                        $err['errMsg'] = "le compte existe déjà";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } catch (\Exception $e) {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            $err['errNo'] = 15;
            $err['errMsg'] = "Activity {$activite_id} not found";
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
     * store multiple
     */
    public static function storeMultiple($comptes)
    {

        try {
            DB::beginTransaction();
            if (count($comptes) >= 1) {
                $datas = array();
                foreach ($comptes as $key => $value) {
                    $member = MembreMethods::getById($value["membres_id"]);
                    if ($member != 'not found') {

                        $activite = ActiviteMethods::getActivityById($value["activites_id"]);

                        if ($activite != "not found") {
                            $exist = CompteMethods::getByIdMA($value["activites_id"], $value["membres_id"]);

                            if ($exist == "not found") {
                                if (isset($value['solde']))
                                    $value['solde_anterieur'] = 0;

                                if (!isset($value['nombre_noms']))
                                    $value['nombre_noms'] = 1;

                                if ($value['nombre_noms'] != "0") {
                                    $com = Compte::create($value);
                                    $com['membre'] = $member->firstName . ' ' . $member->lastName;
                                    array_push($datas, $com);

                                    if ($activite->type == "Tontine") {
                                        $lot = TontineMethods::createLot($com, $activite);
                                        if ($lot == "not possible") {
                                            DB::rollback();
                                            $datas = [];
                                            $err['errNo'] = 11;
                                            $err['errMsg'] = "mauvaise configuration de la tontine";
                                            $error['status'] = 'NOK';
                                            $error['data'] = $err;
                                            return $error;
                                        } else if ($lot['status'] == "NOK" && $lot['data']['errNo'] != 20) {
                                            DB::rollback();
                                            $datas = [];
                                            $err['errNo'] = $lot['data']['errNo'];
                                            $err['errMsg'] = $lot['data']['errMsg'];
                                            $error['status'] = 'NOK';
                                            $error['data'] = $err;

                                            return $error;
                                        }
                                    }
                                }
                            } else {
                                if ($value['nombre_noms'] == "0") {
                                    $exist->delete();
                                } else {
                                    $exist->fill($value);
                                    $exist->save();
                                    $exist['membre'] = $member->firstName . ' ' . $member->lastName;
                                    array_push($datas, $exist);
                                    if ($activite->type == "Tontine") {
                                        $lot = TontineMethods::createLot($exist, $activite);
                                        if ($lot == "not possible") {
                                            DB::rollback();
                                            $datas = [];
                                            $err['errNo'] = 11;
                                            $err['errMsg'] = "mauvaise configuration de la tontine";
                                            $error['status'] = 'NOK';
                                            $error['data'] = $err;
                                            return $error;
                                        } else if ($lot['status'] == "NOK" && $lot['data']['errNo'] != 20) {
                                            DB::rollback();
                                            $datas = [];
                                            $err['errNo'] = $lot['data']['errNo'];
                                            $err['errMsg'] = $lot['data']['errMsg'];
                                            $error['status'] = 'NOK';
                                            $error['data'] = $err;

                                            return $error;
                                        }
                                    }
                                }
                            }
                        } else {
                            DB::rollback();

                            $datas = [];
                            $err['errNo'] = 15;
                            $err['errMsg'] = "activite {" . $value["activites_id"] . "} not found";
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    } else {
                        DB::rollback();

                        $datas = [];
                        $err['errNo'] = 15;
                        $err['errMsg'] = "member {" . $value["membres_id"] . "} not found";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                }
                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $datas;

                return $success;
            } else {
                $err['errNo'] = 11;
                $err['errMsg'] = "mauvaise configuration de la tontine : il faut plus d'un compte membre";
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
     * update multiple
     */
    public static function updateMultiple($comptes)
    {
        $data = array();

        foreach ($comptes as $key => $value) {
            $member = MembreMethods::getById($value["membres_id"]);
            if ($member != 'not found') {

                $activite = ActiviteMethods::getActivityById($value["activites_id"]);

                if ($activite != "not found") {
                    try {
                        if (isset($value['solde']))
                            $value['solde_anterieur'] = 0;
                        $compte = CompteMethods::getById($value["id"]);
                        unset($compte->membre);
                        $compte->fill($value);
                        $compte->save();
                        $compte["membre"] = $member->firstName . ' ' . $member->lastName;
                        $data[] = $compte;
                    } catch (\Exception $e) {
                        $err['errNo'] = 11;
                        $err['errMsg'] = $e->getMessage();
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {

                    $err['errNo'] = 15;
                    $err['errMsg'] = "activite {" . $value["activites_id"] . "} not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {

                $err['errNo'] = 15;
                $err['errMsg'] = "member {" . $value["membres_id"] . "} not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }

        $success['status'] = "OK";
        $success['data'] = $data;

        return $success;
    }

    public static function update($compte_id, $compte, $member_id,  $activite_id)
    {

        $member = MembreMethods::getById($member_id);
        if ($member != 'not found') {

            $activite = ActiviteMethods::getActivityById($activite_id);

            if ($activite != "not found") {
                try {

                    $com = CompteMethods::getById($compte_id);
                    unset($com->membre);
                    $com->fill($compte);
                    $com->save();

                    $success['status'] = "OK";
                    $success['data'] = $com;

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
            $err['errMsg'] = "activity {$activite_id} not found";
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
     * mise a jour des comptes lors de la validation d'une transaction
     * @param $compte 
     * @param $transaction
     */
    public static function MAJComptes($compte, $transaction)
    {
        //mise a jour des dettes d'acquitements passées
        DetteMethods::traitementDetteAcquitement($compte, $transaction);

        //traitement des echeances de type acquitement courantes
        // EcheancesMethods::traitementEcheancesAcquitement($compte, $transaction);

        //mise a jour des dettes de cotisations passées
        DetteMethods::traitementDetteCotisation($compte);

        //traitement des echeances de type cotisation courantes
        // EcheancesMethods::traitementEcheancesCotisation($compte);
        
        //traitement des echeances de façon ascendante dans le temps
        EcheancesMethods::traitementEcheancesAsc($compte, $transaction);

    }

    public static function MAJComptesDecaissement($compte, $transaction, $type, $comptes)
    {
        $compte = CompteMethods::getById($transaction->comptes_id);
        if ($compte == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "MAJCompte: le compte $transaction->comptes_id n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $activite = ActiviteMethods::getActivityById($compte->activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "MAJCompte: l'activite $compte->activites_id n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            switch ($type) {
                case "caisse":
                    $caisse = CompteMethods::getotalSoldeByActivityId($activite->id);
                    if ($transaction->montant > $caisse) {
                        $err['errNo'] = 13;
                        $err['errMsg'] = "montant demandé pas disponible, $caisse disponible";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                    if($activite->caisse == 0){
                        $err['errNo'] = 13;
                        $err['errMsg'] = "montant demandé pas disponible en caisse, $activite->caisse disponible";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                    $activite->fill([
                        "caisse" => $activite->caisse - min($activite->caisse, $transaction->montant)
                    ]);
                    $activite->save();
                    EcheancesMethods::traitementEcheancesDecaissement($compte, $transaction->montant);
                    if ($activite->caisse < $transaction->montant) {
                        unset($compte->membre);
                        $compte->fill([
                            'avoir' => $transaction->montant - $activite->caisse
                        ]);
                        $compte->save();
                    }
                break;
                case "collectif":
                    $caisse = CompteMethods::getotalSoldeByActivityId($activite->id);
                    if ($transaction->montant > $caisse) {
                        $err['errNo'] = 13;
                        $err['errMsg'] = "montant demandé pas disponible, $caisse disponible";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }

                    $activite->fill([
                        "caisse" => $activite->caisse - min($activite->caisse, $transaction->montant)
                    ]);
                    $activite->save();
                    $montant = abs($activite->caisse - $transaction->montant);

                    $comptes = CompteMethods::getByIdAAll($activite->id);
                    $noms = 0;
                    foreach ($comptes as $key => $value) {
                        $noms += $value->nombre_noms;
                    }
                    $montant = round($montant / $noms, 2);

                    EcheancesMethods::traitementEcheancesDecaissement($compte, $transaction->montant);

                    if ($transaction->montant < $transaction->montant_attendu) {
                        unset($compte->membre);
                        $compte->fill([
                            'avoir' => $transaction->montant_attendu - $transaction->montant
                        ]);
                        $compte->save();
                    }
                    foreach ($comptes as $key => $compte) {
                        $compte->fill([
                            'solde_anterieur' => $compte->solde,
                            'solde' => $compte->solde - ($montant * $compte->nombre_noms)
                        ]);
                        $compte->save();
                    }

                    break;
                case "individuel":
                    unset($compte->membre);
                    EcheancesMethods::traitementEcheancesDecaissement($compte, $transaction->montant);

                    if ($transaction->montant < $transaction->montant_attendu) {
                        $compte->fill([
                            'solde_anterieur' => $compte->solde,
                            'solde' => $compte->solde - $transaction->montant,
                            'avoir' => $transaction->montant_attendu - $transaction->montant
                        ]);
                        $compte->save();
                    } else {
                        $compte->fill([
                            'solde_anterieur' => $compte->solde,
                            'solde' => $compte->solde - $transaction->montant
                        ]);
                        $compte->save();
                    }

                    break;
                case "membres":
                    $cpts = array();
                    
                    $montant = $transaction->montant;
                    $noms = 0;
                    foreach ($comptes as $key => $value) {
                        $cpte = CompteMethods::getByIdMA($activite->id, $value);
                        if($cpte != "not found"){
                            $noms += $cpte->nombre_noms;
                            $cpts[] = $cpte;
                        }
                    }
                    $montant = round($montant / $noms, 2);
                    
                    EcheancesMethods::traitementEcheancesDecaissement($compte, $transaction->montant);

                    if ($transaction->montant < $transaction->montant_attendu) {
                        unset($compte->membre);
                        $compte->fill([
                            'avoir' => $transaction->montant_attendu - $transaction->montant
                        ]);
                        $compte->save();
                    }
                    foreach ($cpts as $key => $compte) {
                        $compte->fill([
                            'solde_anterieur' => $compte->solde,
                            'solde' => $compte->solde - ($montant * $compte->nombre_noms)
                        ]);
                        $compte->save();
                    }

                break;
            }

            return array(
                "status" => "OK",
                "data" => $compte
            );
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = "MAJCompte:  {$e->getMessage()}";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteCompte($comptes_id)
    {
        $compte = CompteMethods::getById($comptes_id);

        if ($compte != "not found") {
            try {
                $compte->delete();
                $success['status'] = "OK";
                $success['data'] = "delete successfully";

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
            $err['errMsg'] = "le compte n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteComptes($comptes)
    {
        DB::beginTransaction();
        foreach ($comptes as $key => $comptes_id) {
            $compte = CompteMethods::getById($comptes_id);

            if ($compte != "not found") {
                try {
                    $compte->delete();
                } catch (\Exception $e) {
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = "le compte n'existe pas";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }

        DB::commit();
        $success['status'] = "OK";
        $success['data'] = "delete successfully";

        return $success;
    }

    public static function getComptesByMember($membres_id)
    {

        $comptes = Compte::where('membres_id', $membres_id)
            ->get();


        return $comptes;
    }

    public static function getComptesByMemberEndPoint($membres_id)
    {

        $member = MembreMethods::getById($membres_id);

        if ($member != "not found") {

            $comptes = Compte::where('membres_id', $membres_id)
                ->get();

            foreach ($comptes as $key => $compte) {
                $activite = ActiviteMethods::getActivityById($compte->activites_id);
                if ($activite != "not found") {
                    $statistique = MembreMethods::getStatistiqueMembreActivity($membres_id, $activite->id);
                    $compte['statistique'] = $statistique;
                    $compte['type_activite'] = $activite->type;
                    $compte['nom_activite'] = $activite->nom;
                }
                $compte['membre'] = $member->firstName . ' ' . $member->lastName;
            }

            $success['status'] = "OK";
            $success['data'] = $comptes;

            return $success;
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "le membre n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
    /**
     * ajouter un avoir
     */
    public static function ajouterAvoir($comptes_id, $montant)
    {
        DB::beginTransaction();
        $compte = CompteMethods::getById($comptes_id);
        if ($compte == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ajouter avoir:  compte $comptes_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            unset($compte->membre);
            $compte->fill([
                "avoir" => $compte->avoir + $montant
            ]);
            $compte->save();

            DB::commit();

            return array(
                "status" => "OK",
                "data" => $compte
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function assignerAvoir($comptes_id, $montant, $mode)
    {
        DB::beginTransaction();
        $compte = CompteMethods::getById($comptes_id);
        if ($compte == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "assigner avoir:  compte $comptes_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $activite = ActiviteMethods::getActivityById($compte->activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "assigner avoir:  activite $compte->activites_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if ($compte->avoir < $montant) {
            $err['errNo'] = 15;
            $err['errMsg'] = "assigner avoir:  montant demandé supérieur à l'avoir";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $tresorerie = CompteMethods::getotalSoldeByActivityId($activite->id);
        if($tresorerie == 0){
            $err['errNo'] = 15;
            $err['errMsg'] = "assigner avoir:  pas d'argent en caisse";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $montant = min($montant, $tresorerie);
        try {
            unset($compte->membre);
            $compte->fill([
                // "solde" => $compte->solde + $montant,
                "avoir" => $compte->avoir - $montant
            ]);
            $compte->save();

            $op = array(
                "date_realisation" => DateMethods::getCurrentDateInt(),
                "montant" => $montant,
                "debit_credit" => "debit",
                "mode" => $mode,
                "etat" => "VALIDE",
                "enregistre_par" => $compte->membres_id
            );

            $operation = OperationMethods::store('admin', (object) $op, $compte->membres_id, null);
            if ($operation['status'] == 'OK') {
                $tr = array(
                    "comptes_id" => $compte->id,
                    "montant" => $compte->solde,
                    "montant_attendu" => $montant,
                    "debit_credit" => "debit",
                    "etat" => "VALIDE"
                );
                $trans = TransactionMethods::store((object) $tr, $operation['data']['id']);
                if($trans['status'] == "NOK"){
                    DB::rollback();
                    return $trans;
                }
            } else {
                DB::rollback();
                return $operation;
            }
            // $nexAg = AgMethods::getCurrentAg($compte->membres_id);
            // if ($nexAg != "not found") {
            //     $echeancier = array(
            //         "date_limite" => $nexAg->date_ag,
            //         "montant" => $montant,
            //         "etat" => "init",
            //         "date_created" => DateMethods::getCurrentDateInt(),
            //         "libelle" => "Décaissement - " . $activite->nom . " - Avoir",
            //         "membres_id" => $compte->membres_id,
            //         "comptes_id" => $compte->id,
            //         "debit_credit" => "decaissement",
            //         "serie" => "avoir-$compte->id"
            //     );
            //     $echeance = Echeancier::create($echeancier);
            // }

            DB::commit();

            return array(
                "status" => "OK",
                "data" => $compte
            );
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
     * mise à jour des comptes lors de la cloture de l'ag
     */
    public static function MAJSoldes($cycles_id)
    {
        DB::beginTransaction();
        $activites = ActiviteMethods::getActivityByCycleId($cycles_id);
        foreach ($activites as $key => $activite) {
            $comptes = CompteMethods::getByIdAAll($activite->id);
            $nbNoms = 0;
            foreach ($comptes as $key => $compte) {
                $nbNoms += $compte->nombre_noms;
            }
            if($nbNoms != 0)
                $montant = $activite->caisse / $nbNoms;
            foreach ($comptes as $key => $compte) {
                try {
                    $compte->fill([
                        "solde_anterieur" => $compte->solde,
                    ]);
                    $compte->save();


                    $compte->fill([
                        "dette_c" => $compte->dette_c - min($compte->dette_c, $compte->avoir),
                        "avoir" => $compte->avoir - min($compte->dette_c, $compte->avoir)
                    ]);
                    $compte->save();

                    $compte->fill([
                        "dette_a" => $compte->dette_a - min($compte->dette_a, $compte->avoir),
                        "avoir" => $compte->avoir - min($compte->dette_a, $compte->avoir)
                    ]);
                    $compte->save();

                    if ($activite->type != "Mutuelle") {
                        $compte->fill([
                            "interet" => $montant * $compte->nombre_noms
                        ]);
                        $compte->save();
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = "mise à jours des soldes de comptes: $e->getMessage()";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }
        }

        DB::commit();
        return array(
            "status" => "OK",
            "data" => "successful"
        );
    }
}
