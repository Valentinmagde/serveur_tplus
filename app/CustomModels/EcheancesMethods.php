<?php

namespace App\CustomModels;

use App\Models\Dette;
use App\Models\Echeancier;
use App\Models\Credit;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;

class EcheancesMethods
{

    public static function deleteAcquitementZero(){
        $echs = Echeancier::where('montant', 0)->get();
        try {
            foreach ($echs as $key => $value) {
                $value->delete();
            }

            return array(
                "status" => "OK",
                "data" => "successfull"
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

    public static function updateToUTCTimeAllDateEcheancier()
    {
        DB::beginTransaction();
        $all = Echeancier::all();
        $ags = array();
        try {
            foreach ($all as $key => $echeancier) {
                $membre = MembreMethods::getById($echeancier->membres_id);
                if ($membre != "not found") {
                    $association = AssociationMethods::getById($membre->associations_id);
                    if ($association != "not found") {
                        $offset = (int) $association->fuseau_horaire * 3600;
                        $datetime = $echeancier->date_limite - $offset;
                        $echeancier->fill([
                            "date_limite" => $datetime
                        ]);
                        $echeancier->save();
                        $echeanciers[] = $echeancier;
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = "Association {$membre->associations_id} doesn't exist";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "member {$echeancier->membres_id} doesn't exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $echeanciers
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

    public static function updateEtatEcheancierPast()
    {
        DB::beginTransaction();
        $all = Echeancier::all();
        $echeanciers = array();
        try {

            $associations = AssociationMethods::getAll();

            foreach ($associations as $key => $assoc) {
                $cycles = CycleMethods::checkActifCycle($assoc->id);
                if ($cycles != "not found") {
                    $ag = AgMethods::getCurrentCycle($cycles->id);
                    if ($ag != "not found") {
                        $activites = ActiviteMethods::getActivitiesByAssociations($assoc->id);

                        foreach ($activites['data'] as $key => $activite) {
                            $echeances = EcheancesMethods::getAll($activite->id);
                            if ($echeances['status'] == "OK") {
                                foreach ($echeances['data'] as $key => $ech) {
                                    if ($ech->date_limite < $ag->date_ag) {
                                        $ech->fill([
                                            "etat" => "cloture"
                                        ]);
                                        $ech->save();
                                        $echeanciers[] = $ech;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $echeanciers
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

    public static function getById($id)
    {
        return Echeancier::find($id);
    }

    public static function getAll($activite_id)
    {
        $comptes = CompteMethods::getAll($activite_id);

        $echeances = array();

        foreach ($comptes['data'] as $key => $compte) {
            $echeance =  Echeancier::where('comptes_id', $compte['id'])
                ->get();
            foreach ($echeance as $key => $value) {
                $echeances[] = $value;
            }
        }


        $success['status'] = 'OK';
        $success['data'] = $echeances;

        return $success;
    }


    public static function setEcheancesCreditWithNoDate($assocId, $cycle_id)
    {
        $ags = AgMethods::getByIdCycle($cycle_id);
        $mutuelles = ActiviteMethods::getActivitiesByType($assocId, "Mutuelle");
        $echeances = array();
        DB::beginTransaction();
        try {
            foreach ($mutuelles['data'] as $key => $mutuelle) {
                $comptes = CompteMethods::getByIdAAll($mutuelle->id);
                foreach ($comptes as $key => $compte) {
                    $echs = Echeancier::where('comptes_id', $compte->id)
                        ->where('membres_id', $compte->membres_id)
                        ->where('date_limite', 0)
                        ->where('serie', 'like', 'credit-%')
                        ->get();

                    $ic = 0;
                    $ia = 0;
                    foreach ($echs as $key => $value) {
                        if ($value->debit_credit == "cotisation") {
                            if ($value->next_date_in) {
                                if ($ags[$value->next_date_in - 1]) {
                                    $value->fill(['date_limite' => $ags[$value->next_date_in - 1]->date_ag]);
                                    $value->save();
                                } else {
                                    $value->fill(['next_date_in' => $value->next_date_in - count($ags)]);
                                    $value->save();
                                }
                            } else {
                                $value->fill(['date_limite' => $ags[$ic]->date_ag]);
                                $value->save();
                                $ic++;
                            }
                        } else  if ($value->debit_credit == "acquitement") {
                            if ($value->next_date_in) {
                                if ($ags[$value->next_date_in - 1]) {
                                    $value->fill(['date_limite' => $ags[$value->next_date_in - 1]->date_ag]);
                                    $value->save();
                                } else {
                                    $value->fill(['next_date_in' => $value->next_date_in - count($ags)]);
                                    $value->save();
                                }
                            } else {
                                $value->fill(['date_limite' => $ags[$ia]->date_ag]);
                                $value->save();
                                $ia++;
                            }
                        }
                    }

                    $echs = Echeancier::where('comptes_id', $compte->id)
                        ->where('membres_id', $compte->membres_id)
                        ->where('date_limite', 0)
                        ->where('serie', 'like', 'assistance-%')
                        ->get();

                    foreach ($echs as $key => $value) {
                        if ($value->debit_credit == "cotisation") {
                            if ($value->next_date_in) {
                                if ($ags[$value->next_date_in - 1]) {
                                    $value->fill(['date_limite' => $ags[$value->next_date_in - 1]->date_ag]);
                                    $value->save();
                                } else {
                                    $value->fill(['next_date_in' => $value->next_date_in - count($ags)]);
                                    $value->save();
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => "generate"
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

    public static function getByLimitDate($date)
    {
        $echeances = Echeancier::where('date_limite', $date)
            ->first();

        if ($echeances) {

            return $echeances;
        }
        return "not found";
    }

    public static function cloturerEcheances($association_id, $ags_id)
    {
        DB::beginTransaction();
        try {
            $ag = AgMethods::getById($ags_id);
            if ($ag == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "ag {$ags_id} doesn't exist";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $nextAg = AgMethods::getNextAgByAg($ag, $ag->cycles_id);
            $association = ActiviteMethods::getActivitiesByAssociations($association_id);
            foreach ($association['data'] as $key => $activite) {
                $comptes = CompteMethods::getByIdAAll($activite->id);
                if ($comptes != "not found") {
                    foreach ($comptes as $key => $compte) {



                        $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDate($compte->membres_id, $compte->id, $ag->date_ag);

                        foreach ($echeances as $key => $echeance) {
                            switch ($echeance->debit_credit) {
                                case 'cotisation':

                                    $compte->fill([
                                        'dette_c' => $compte->dette_c + $echeance->montant - $echeance->montant_realise
                                    ]);
                                    $compte->save();
                                    DetteMethods::create($compte, $echeance, "cotisation");
                                    $echeance->fill(['etat' => 'cloture']);
                                    $echeance->save();
                                    break;

                                case 'acquitement':
                                    $compte->fill(['dette_a' => $compte->dette_a + $echeance->montant - $echeance->montant_realise]);
                                    $compte->save();
                                    DetteMethods::create($compte, $echeance, "acquitement");
                                    $echeance->fill(['etat' => 'cloture']);
                                    $echeance->save();
                                    break;

                                case 'decaissement':
                                    list($str, $eid) = explode('-', $echeance->serie ?? "s-s");

                                    if ($str == "lot") {
                                        $lot = TontineMethods::getLotById($eid);
                                        if ($lot != "not found") {
                                            unset($lot['membre']);
                                            unset($lot['membres_id']);
                                            $lot->fill(["montant_recu" => $echeance->montant_realise ?? 0, "etat" => "paye"]);
                                            $lot->save();
                                        }
                                    }
                                    $compte->fill([
                                        'avoir' => $compte->avoir + $echeance->montant - $echeance->montant_realise,
                                    ]);
                                    $compte->save();
                                    $echeance->fill(['etat' => 'cloture']);
                                    $echeance->save();
                                    break;
                            }

                            list($str, $id) = explode('-', $echeance->serie ?? "s-s");
                            if($str === "credit"){
                                EcheancesMethods::clotureEcheanceCredit($echeance->serie, $id);
                            }
                        }

                        if ($compte->dette_c > 0) {
                            $penalite = ($compte->dette_c * $activite->taux_penalite) / 100;
                            if ($penalite > 0) {
                                $echeancier = array(
                                    "date_limite" => $nextAg->date_ag ?? 0,
                                    "montant" => $penalite,
                                    "etat" => "init",
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "libelle" => "Acquitement - " . $activite->nom . " - echec de cotisation ",
                                    "membres_id" => $compte->membres_id,
                                    "comptes_id" => $compte->id,
                                    "debit_credit" => "acquitement",
                                    "serie" => "compte-$compte->id"
                                );
                                $echeancier = Echeancier::create($echeancier);
                            }
                        }
                    }
                }
            }

            DB::commit();

            $success['status'] = "OK";
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
    }

    public static function getAllNonCloturedEcheances($compte_id, $date)
    {
        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('etat', "!=", "cloture")
            ->where('date_limite', $date)
            ->get();

        if ($echeances) {

            return $echeances;
        }
        return "not found";
    }


    public static function setToCloseCreditOut(){
        DB::beginTransaction();
        $credits = Credit::all();
        try {
            foreach ($credits as $key => $credit) {
                EcheancesMethods::clotureEcheanceCredit("credit-".$credit->id, $credit->id);
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successfull"
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

    public static function clotureEcheanceCredit($serie, $id){
        $echeances = Echeancier::where('serie', $serie)
                                ->where('etat', '!=', 'cloture')
                                ->get();
        if(count($echeances) === 0){
            $credit = MutuelleMethods::getCreditById($id);
            if($credit != "not found"){
                $credit->montant_restant = 0;
                $credit->etat = "cloture";
                $credit->save();
            }
        }else{
            $montant = 0;
            foreach ($echeances as $key => $ech) {
                if($ech->debit_credit == "cotisation"){
                    $montant += $ech->montant;
                }
            }
            $credit = MutuelleMethods::getCreditById($id);
            if($credit != "not found"){
                $credit->montant_restant = $montant;
                $credit->save();
            }
        }
    }

    public static function getNextEcheanceByType($membre_id, $compte_id, $date, $type)
    {
        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('membres_id', $membre_id)
            ->where('debit_credit', $type)
            ->where('date_limite', $date)
            ->where('etat', '!=', 'cloture')
            ->get();

        if ($echeances) {

            return $echeances;
        }
        return "not found";
    }

    public static function traitementEcheancesAsc($compte, $transaction){
        $dateLastAg = AgMethods::getNextAgDueDate($compte->membres_id);
        if ($dateLastAg != "not found") {
            $arrEch = [];
            //couverture des echeances pour le cycle actuel
            $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDateAsc($compte->membres_id, $compte->id, $dateLastAg);
            foreach ($echeances as $key => $echeance) {
                $montant = $compte->solde - $compte->solde_anterieur;
                if ($montant > 0) {
                    switch ($echeance->debit_credit) {
                        case 'acquitement':
                            VirementMethods::acquitement($compte->id, $compte->activites_id, min($montant, $echeance->montant), $transaction->created_by);
                            $echeance->montant_realise += min($montant, $echeance->montant);
                            $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                            if ($echeance->montant_realise >= $echeance->montant) {
                                $echeance->etat = "cloture";
                            }

                            $echeance->save();
                            $compte->save();
                        break;

                        case 'cotisation':

                            if($echeance->type == "mise_de_fond"){
                                $arrEch[] = $echeance;
                            }else{
                                $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                                $echeance->montant_realise += min($montant, $echeance->montant);
                                if ($echeance->montant_realise >= $echeance->montant) {
                                    $echeance->etat = "cloture";
                                }
                                $echeance->save();
                                list($str, $id) = explode('-', $echeance->serie ?? "s-s");
                                if($str === "credit"){
                                    EcheancesMethods::clotureEcheanceCredit($echeance->serie, $id);
                                }
                                $compte->save();
                            }
                        break;
                    }
                }else{
                    break;
                }
            }

            //traitement des echeances de mise de fond si on a à faire à une mutuelle
            foreach ($arrEch as $key => $echeance) {
                $montant = $compte->solde - $compte->solde_anterieur;
                if ($montant > 0) {
                    $member = MembreMethods::getById($compte->membres_id);
                    if ($member != "not found") {
                        $activite_type = ActiviteMethods::getActivityById($compte->activites_id);
                        if ($activite_type != "not found") {
                            if ($activite_type->type == "Mutuelle") {
                                $compte->solde = $compte->solde - $montant;
                                $compte->save();
                                $mutuelle = MutuelleMethods::getByIdActivite($activite_type->id);
                                if ($mutuelle != "not found" && min($montant, $echeance->montant) > 0) {
                                    MutuelleMethods::storeMise($mutuelle->id, $member->id, array("montant" => min($montant, $echeance->montant), "date_versement" => DateMethods::getCurrentDateInt()));
                                }
                            } 
                        }
                    }
                    $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                    $echeance->montant_realise += min($montant, $echeance->montant);
                    if ($echeance->montant_realise >= $echeance->montant) {
                        $echeance->etat = "cloture";
                    }
                    $echeance->save();
                    $compte->save();
                }else{
                    break;
                }
            }
            
            // couverture des echeances pour le cycle qui suit en cas de paiement en avance
            $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDateZero($compte->membres_id, $compte->id);
            foreach ($echeances as $key => $echeance) {
                $montant = $compte->solde - $compte->solde_anterieur;
                if ($montant > 0) {
                    switch ($echeance->debit_credit) {
                        case 'acquitement':
                            VirementMethods::acquitement($compte->id, $compte->activites_id, min($montant, $echeance->montant), $transaction->created_by);
                            $echeance->montant_realise += min($montant, $echeance->montant);
                            $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                            if ($echeance->montant_realise >= $echeance->montant) {
                                $echeance->etat = "cloture";
                            }

                            $echeance->save();
                            $compte->save();
                        break;

                        case 'cotisation':
                            $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                            $echeance->montant_realise += min($montant, $echeance->montant);
                            if ($echeance->montant_realise >= $echeance->montant) {
                                $echeance->etat = "cloture";
                            }
                            $echeance->save();
                            list($str, $id) = explode('-', $echeance->serie ?? "s-s");
                            if($str === "credit"){
                                EcheancesMethods::clotureEcheanceCredit($echeance->serie, $id);
                            }
                            $compte->save();
                        break;
                    }
                }else{
                    break;
                }
            }
        
        } else {
            throw new Exception('aucune AG prochaine trouvée');
        }

        $success['status'] = "OK";
        $success['data'] = "successfull";
        return $success;
    }

    /**
     * traitement des echeances d'acquitement
     */
    public static function traitementEcheancesAcquitement($compte, $transaction)
    {
        $dateLastAg = AgMethods::getNextAgDueDate($compte->membres_id);
        if ($dateLastAg != "not found") {
            $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDateAsc($compte->membres_id, $compte->id, $dateLastAg);

            if ($echeances != "not found") {
                foreach ($echeances as $key => $echeance) {

                    if ($echeance->debit_credit == "acquitement") {
                        $montant = $compte->solde - $compte->solde_anterieur;
                        if ($montant > 0) {
                            VirementMethods::acquitement($compte->id, $compte->activites_id, min($montant, $echeance->montant), $transaction->created_by);
                            $echeance->montant_realise += min($montant, $echeance->montant);
                            $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                            if ($echeance->montant_realise >= $echeance->montant) {
                                $echeance->etat = "cloture";
                            }

                            $echeance->save();
                            $compte->save();
                            // else{
                            //     $compte->dette_a += $echeance->montant - $echeance->montant_realise;
                            //     $compte->save();
                            //     DetteMethods::create($compte, $echeance, "acquitement");
                            //     // $echeance->etat = "cloture";
                            // }

                            // $echeance->etat = "cloture";
                            // $echeance->save();

                        }
                    }
                }
            } else {
                throw new Exception('aucune Echeance trouvée');
            }
        } else {
            throw new Exception('aucune AG prochaine trouvée');
        }

        $success['status'] = "OK";
        $success['data'] = "successfull";
        return $success;
    }

    /**
     * traitement des echeances de cotisation
     */
    public static function traitementEcheancesCotisation($compte)
    {
        $dateLastAg = AgMethods::getNextAgDueDate($compte->membres_id);
        if ($dateLastAg != "not found") {
            $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDateAsc($compte->membres_id, $compte->id, $dateLastAg);

            if ($echeances != "not found") {
                foreach ($echeances as $key => $echeance) {
                    if ($echeance->debit_credit == "cotisation") {
                        $montant = $compte->solde - $compte->solde_anterieur;
                        if ($montant > 0) {
                            $compte->solde_anterieur = $compte->solde_anterieur + min($montant, $echeance->montant);
                            $echeance->montant_realise += min($montant, $echeance->montant);
                            if ($echeance->montant_realise >= $echeance->montant) {
                                $echeance->etat = "cloture";
                            }

                            $echeance->save();
                            list($str, $id) = explode('-', $echeance->serie ?? "s-s");
                            if($str === "credit"){
                                EcheancesMethods::clotureEcheanceCredit($echeance->serie, $id);
                            }
                            // else{
                            //     $compte->dette_c += $echeance->montant - $echeance->montant_realise;
                            //     $compte->save();
                            //     DetteMethods::create($compte, $echeance, "cotisation");

                            //     $echeance->etat = "cloture";
                            // }

                            // $echeance->etat = "cloture";
                            $compte->save();
                        }
                    }
                }
            } else {
                throw new Exception('aucune Echeance trouvée');
            }
        } else {
            throw new Exception('aucune AG prochaine trouvée');
        }

        $success['status'] = "OK";
        $success['data'] = "successfull";
        return $success;
    }

    public static function traitementEcheancesDecaissement($compte, $montant)
    {
        try {


            $dateLastAg = AgMethods::getNextAgDueDate($compte->membres_id);
            if ($dateLastAg != "not found") {

                $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDate($compte->membres_id, $compte->id, $dateLastAg);

                if ($echeances != "not found") {
                    foreach ($echeances as $key => $echeance) {
                        if ($echeance->debit_credit == "decaissement") {
                            if ($montant > 0) {

                                // throw new \Exception($echeance);
                                list($str, $eid) = explode('-', $echeance->serie ?? "s-s");

                                if ($str == "lot") {
                                    $lot = TontineMethods::getLotById($eid);
                                    if ($lot != "not found") {
                                        unset($lot['membre']);
                                        unset($lot['membres_id']);
                                        $lot->fill(["montant_recu" => min($montant, $echeance->montant), "etat" => "paye"]);
                                        $lot->save();
                                    }
                                }

                                $echeance->montant_realise = min($montant, $echeance->montant);
                                $montant = abs($montant - $echeance->montant);
                                $echeance->etat = "cloture";

                                $echeance->save();
                            }
                        }
                    }
                } else {
                    throw new Exception('aucune Echeance trouvée');
                }
            } else {
                throw new Exception('aucune AG prochaine trouvée');
            }

            $success['status'] = "OK";
            $success['data'] = "successfull";
            return $success;
        } catch (\Throwable $th) {
            throw new Exception("problème avec la sauvegarde d'une donnée");
        }
    }

    /**
     * 
     */
    public static function getNextEcheancesByMemberCompteDate($membre_id, $compte_id, $date)
    {
        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('membres_id', $membre_id)
            ->where('etat', '!=', 'cloture')
            ->get();

        $echs = [];
        $da = gmdate('Y-m-d', $date);
        foreach ($echeances as $key => $value) {
            $dec = gmdate('Y-m-d', $value->date_limite);
            if ($da == $dec) {
                $echs[] = $value;
            }
        }

        return $echs;
    }

    public static function getNextEcheancesByMemberCompteDateAsc($membre_id, $compte_id, $date)
    {
        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('membres_id', $membre_id)
            ->where('etat', '!=', 'cloture')
            ->where('date_limite', '>=', $date)
            ->orderBy('date_limite', 'asc')
            ->get();

        return $echeances;
    }

    public static function getNextEcheancesByMemberCompteDateZero($membre_id, $compte_id)
    {
        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('membres_id', $membre_id)
            ->where('etat', '!=', 'cloture')
            ->where('date_limite', 0)
            ->get();

        return $echeances;
    }

    public static function getNextEcheances($membre_id, $compte_id, $date)
    {

        $echeances = Echeancier::where('comptes_id', $compte_id)
            ->where('membres_id', $membre_id)
            ->where('date_limite', $date)
            ->orderBy('priorite', "desc")
            ->get()
            ->groupBy('priorite');

        if ($echeances) {

            return $echeances;
        }
        return "not found";
    }

    /**
     * récupérer tout les echeances impayées
     */
    public static function getAllEcheancier($activite_id)
    {
        $activite = ActiviteMethods::getActivityById($activite_id);

        if ($activite != "not found") {

            $comptes = CompteMethods::getAll($activite_id);

            $echeances = array();

            if ($activite->etat != "cloture") {

                foreach ($comptes['data'] as $key => $compte) {
                    $membre = MembreMethods::getById($compte['membres_id']);
                    $echeance =  Echeancier::where('comptes_id', $compte['id'])
                        ->where('etat', '!=', "cloture")
                        ->get();
                    foreach ($echeance as $key => $value) {
                        if ($membre != "not found")
                            $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
                    }

                    $echeances[] = $echeance;
                }
            }
            $success['status'] = 'OK';
            $success['data'] = $echeances;

            return $success;
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activite} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function getAllEcheancierAtAgs($activite_id, $ags_id)
    {
        $ags = AgMethods::getById($ags_id);
        if ($ags == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $activite = ActiviteMethods::getActivityById($activite_id);

        if ($activite != "not found") {

            $comptes = CompteMethods::getAll($activite_id);

            $echeances = array();

            foreach ($comptes['data'] as $key => $compte) {
                $membre = MembreMethods::getById($compte['membres_id']);
                $echeance =  Echeancier::where('comptes_id', $compte['id'])
                    ->get();
                foreach ($echeance as $key => $value) {

                    $da = gmdate("Y-m-d", $ags->date_ag);
                    $dea = gmdate("Y-m-d", $value->date_limite);
                    if ($da == $dea) {
                        if ($membre != "not found")
                            $value['membre'] = $membre->firstName . ' ' . $membre->lastName;
                        $echeances[] = $value;
                    }
                }
            }


            $success['status'] = 'OK';
            $success['data'] = $echeances;

            return $success;
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activite} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function getAllEcheancierTypeAndDelete($activite_id, $type)
    {

        $comptes = CompteMethods::getAll($activite_id);
        foreach ($comptes['data'] as $key => $compte) {
            $echeance =  Echeancier::where('comptes_id', $compte['id'])
                ->where('debit_credit', $type)
                ->get();
            foreach ($echeance as $key => $value) {
                $value->delete();
            }
        }
    }

    public static function getAllEcheancierTypeForCompteAndDelete($type, $comptes_id)
    {

        $echeance =  Echeancier::where('comptes_id', $comptes_id)
            ->where('debit_credit', $type)
            ->get();
        foreach ($echeance as $key => $value) {
            $value->delete();
        }
    }

    /**
     * creer les echeances pour tous les membres d'une activité
     */
    public static  function createForAllMembers($activite_id, $echeancier)
    {

        $activite = ActiviteMethods::getActivityById($activite_id);
        DB::beginTransaction();
        if ($activite != "not found") {
            try {
                $comptes = CompteMethods::getAll($activite_id);
                $echeances = array();

                // $echeancier['date_limite'] = DateMethods::getDateInt($echeancier['date_limite']);
                $echeancier['date_created'] = DateMethods::getCurrentDateInt();
                $echeancier['etat'] = 'init';

                $montant = $echeancier['montant'];
                foreach ($comptes['data'] as $key => $compte) {

                    $echeancier['comptes_id'] = $compte['id'];
                    $echeancier['membres_id'] = $compte['membres_id'];
                    if ($echeancier['debit_credit'] == "decaissement") $echeancier['montant'] = $montant;
                    else  $echeancier['montant'] = $montant * $compte->nombre_noms;
                    $echeancier['etat'] = 'init';
                    $echeancier['date_created'] = DateMethods::getCurrentDateInt();

                    if($echeancier['montant'] != 0){
                        $echeance = Echeancier::create($echeancier);

                        $echeances[] = $echeance;
                    }
                }

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = $echeances;

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
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activite} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * echeances pour certains membres de l'activité
     */
    public static  function createForSomeMembers($activite, $echeancier, $membres)
    {

        $activite = ActiviteMethods::getActivityById($activite);
        DB::beginTransaction();
        if ($activite != "not found") {
            try {
                $comptes = CompteMethods::getAll($activite->id);

                $echeances = array();

                $echeancier['date_created'] = DateMethods::getCurrentDateInt();
                $montant = $echeancier['montant'];
                foreach ($comptes['data'] as $key => $compte) {

                    if (in_array($compte['membres_id'], $membres)) {

                        $echeancier['comptes_id'] = $compte['id'];
                        $echeancier['membres_id'] = $compte['membres_id'];
                        if ($echeancier['debit_credit'] == "decaissement") $echeancier['montant'] = $montant;
                        else  $echeancier['montant'] = $montant * $compte->nombre_noms;
                        $echeancier['etat'] = 'init';
                        $echeancier['date_created'] = DateMethods::getCurrentDateInt();

                        if($echeancier['montant'] != 0){
                            $echeance = Echeancier::create($echeancier);
    
                            $echeances[] = $echeance;
                        }
                    }
                }

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = $echeances;

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
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = "activity {$activite} not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function getEcheancesByMember($membres_id)
    {

        $member = MembreMethods::getById($membres_id);

        if ($member != "not found") {

            $comptes = Echeancier::where('membres_id', $membres_id)
                ->get();

            foreach ($comptes as $key => $compte) {
                $cpt = CompteMethods::getById($compte->comptes_id);
                if ($cpt != "not found") {
                    $activite = ActiviteMethods::getActivityById($cpt->activites_id);
                    if ($activite != "not found") {
                        $compte['type_activite'] = $activite->type;
                        $compte['nom_activite'] = $activite->nom;
                    }
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

    public static function getEcheancesForAllMembers($assocId)
    {

        $membres = MembreMethods::getByAssociationId($assocId);
        $echeances = array();
        foreach ($membres as $key => $member) {
            $comptes = Echeancier::where('membres_id', $member->id)
                ->where('etat', '!=', 'cloture')
                ->get();

            foreach ($comptes as $key => $compte) {
                $cpt = CompteMethods::getById($compte->comptes_id);
                if ($cpt != "not found") {
                    $activite = ActiviteMethods::getActivityById($cpt->activites_id);
                    if ($activite != "not found") {
                        $compte['type_activite'] = $activite->type;
                        $compte['nom_activite'] = $activite->nom;
                    }
                }
                $compte['membre'] = $member->firstName . ' ' . $member->lastName;
            }
            $echeances[] = $comptes;
        }
        $success['status'] = "OK";
        $success['data'] = $echeances;

        return $success;
    }

    public static function getEcheancesForAllMembersAtAgs($assocId, $ags_id)
    {
        $ags = AgMethods::getById($ags_id);
        if ($ags == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $previousAg = AgMethods::getPreviousAgInCycle($ags, $ags->cycles_id);

        $membres = MembreMethods::getByAssociationId($assocId);
        $echeances = array();

        $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt() + 86400);
        if ($previousAg != "not found") $dpa = $previousAg->date_cloture ?? 0;
        else $dpa = 0;
        foreach ($membres as $key => $member) {
            $comptes = CompteMethods::getComptesByMember($member->id);
            $montant_attendu = 0;
            $montant_attendu_init = 0;
            $montant_realise = 0;
            $transacs = array();
            $echss = array();
            foreach ($comptes as $key => $compte) {

                $transactions  = Transaction::where('comptes_id', $compte->id)
                    ->where('debit_credit', "credit")
                    ->where("etat", "VALIDE")
                    ->where('date_created', '>', $dpa)
                    ->where('date_created', '<=', strtotime($da))
                    ->get();

                $activite = ActiviteMethods::getActivityById($compte->activites_id);
                $one = 0;
                foreach ($transactions as $key => $trans) {
                    if ($activite != "not found") {
                        $trans['activite'] = $activite;
                    }
                    if ($one == 0) {
                        $montant_attendu_init += $trans->montant_attendu;
                        $one++;
                    }
                    $montant_realise += $trans->montant;
                    $transacs[] = $trans;
                }

                $stat = MembreMethods::getStatistiqueMembreActivity($member->id, $compte->activites_id);
                $montant_attendu += $stat['data']['a_payer'];
                $echs = $stat['data']['details'];

                if (count($transactions) == 0) {
                    foreach ($echs as $key => $ech) {
                        if ($ech['echeances_encaissement']) {
                            foreach ($ech['echeances_encaissement'] as $key => $value) {
                                if ($value['montant_realise'] == null) $montant_attendu_init += $value['montant'];
                            }
                        }
                    }
                    $montant_attendu_init += ($compte->dette_c + $compte->dette_a);
                }

                $echss[] = $echs;
            }
            $cte['membre'] = $member->firstName . ' ' . $member->lastName;
            $cte['membres_id'] = $member->id;
            $cte['montant_attendu'] = $montant_attendu;
            $cte['montant_attendu_init'] = $montant_attendu_init;
            $cte['montant_realise'] = $montant_realise;
            $cte['transactions'] = $transacs;
            $cte['echeances'] = $echss;
            $echeances[] = $cte;
        }
        $success['status'] = "OK";
        $success['data'] = $echeances;

        return $success;
    }

    public static function getEcheancesDecaissementForAllMembersAtAgs($assocId, $ags_id)
    {
        $ags = AgMethods::getById($ags_id);
        if ($ags == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $previousAg = AgMethods::getPreviousAgInCycle($ags, $ags->cycles_id);

        $membres = MembreMethods::getByAssociationId($assocId);
        $echeances = array();

        $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt() + 86400);
        if ($previousAg != "not found") $dpa = $previousAg->date_cloture ?? 0;
        else $dpa = 0;
        foreach ($membres as $key => $member) {
            $comptes = CompteMethods::getComptesByMember($member->id);
            $montant_attendu = 0;
            $montant_attendu_init = 0;
            $montant_realise = 0;
            $transacs = array();
            $echss = array();
            foreach ($comptes as $key => $compte) {

                $transactions  = Transaction::where('comptes_id', $compte->id)
                    ->where('debit_credit', "debit")
                    ->where("etat", "VALIDE")
                    ->where('date_created', '>', $dpa)
                    ->where('date_created', '<=', strtotime($da))
                    ->get();


                $activite = ActiviteMethods::getActivityById($compte->activites_id);
                $one = 0;
                foreach ($transactions as $key => $trans) {
                    if ($activite != "not found") {
                        $trans['activite'] = $activite;
                    }
                    $transacs[] = $trans;
                    if ($one == 0) {
                        $montant_attendu_init += $trans->montant_attendu;
                        $one++;
                    }
                    // ($trans->montant_attendu - $trans->montant) < 0 ? $montant_attendu += 0 : $montant_attendu += $trans->montant_attendu - $trans->montant;
                    $montant_realise += $trans->montant;
                }

                $stat = MembreMethods::getStatistiqueMembreActivity($member->id, $compte->activites_id);
                $echs = $stat['data']['details'];
                $montant_attendu += $stat['data']['a_retirer'];


                if (count($transactions) == 0) {
                    foreach ($echs as $key => $ech) {
                        if ($ech['echeances_decaissement']) {
                            foreach ($ech['echeances_decaissement'] as $key => $value) {
                                if ($value['montant_realise'] == null) $montant_attendu_init += $value['montant'];
                            }
                        }
                    }
                }


                $echss[] = $echs;
            }

            $cte['membre_id'] = $member->id;
            $cte['membre'] = $member->firstName . ' ' . $member->lastName;
            $cte['montant_attendu'] = $montant_attendu;
            $cte['montant_attendu_init'] = $montant_attendu_init;
            $cte['montant_realise'] = $montant_realise;
            $cte['transactions'] = $transacs;
            $cte['echeances'] = $echss;
            $echeances[] = $cte;
        }
        $success['status'] = "OK";
        $success['data'] = $echeances;

        return $success;
    }

    public static function createEcheance($comptes_id, $echeance)
    {
        DB::beginTransaction();
        $compte = CompteMethods::getById($comptes_id);
        if ($compte != "not found") {
            $echeance['membres_id'] = $compte->membres_id;
            (array_key_exists('etat', $echeance)) ? 1 : $echeance['etat'] = "init";
            try {
                $echeance = Echeancier::create($echeance);

                $success['status'] = "OK";
                $success['data'] = $echeance;
                DB::commit();
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
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = "le compte not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteEcheancier($id)
    {
        $echeance = Echeancier::find($id);
        if ($echeance) $echeance->delete();
    }

    public static function deleteEcheancierEndPoint($id)
    {
        $echeance = Echeancier::find($id);
        try {
            if ($echeance) $echeance->delete();
            $success['status'] = "OK";
            $success['data'] = "successful";

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
}
