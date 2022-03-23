<?php

namespace App\CustomModels;

use App\Models\Echeancier;
use App\Models\LotsTontine;
use App\Models\Tontine;
use Illuminate\Support\Facades\DB;

class TontineMethods
{

    public static function updateToUTCTimeAllDateLotsTontine()
    {
        DB::beginTransaction();
        $all = LotsTontine::all();
        $ags = array();
        try {
            foreach ($all as $key => $LotsTontine) {

                $compte = CompteMethods::getById($LotsTontine->comptes_id);
                if ($compte != "not found") {
                    $membre = MembreMethods::getById($compte->membres_id);
                    if ($membre != "not found") {
                        $association = AssociationMethods::getById($membre->associations_id);
                        if ($association != "not found") {
                            $offset = (int) $association->fuseau_horaire * 3600;
                            $datetime = $LotsTontine->date_bouffe - $offset;
                            $LotsTontine->fill([
                                "date_bouffe" => $datetime
                            ]);
                            $LotsTontine->save();
                            $LotsTontines[] = $LotsTontine;
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
                        $err['errMsg'] = "member {$compte->membres_id} doesn't exist";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "account {$LotsTontine->comptes_id} doesn't exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $LotsTontines
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }


    public static function getById($id)
    {
        $tontine = Tontine::where('id', $id)->first();
        if ($tontine) {
            return $tontine;
        }

        return "not found";
    }

    public static function getLotById($id)
    {
        $tontine = LotsTontine::where('id', $id)->first();
        if ($tontine) {
            $compte = CompteMethods::getByIdWithIdMember($tontine->comptes_id);
            if ($compte != "not found") {
                $tontine['membre'] = $compte->membre;
                $tontine['membres_id'] = $compte->membres_id;
            }
            return $tontine;
        }

        return "not found";
    }

    public static function getLotByAndTontine($tontineId, $id)
    {
        $tontine = LotsTontine::where('id', $id)->where('tontines_id', $tontineId)->first();
        if ($tontine) {
            return $tontine;
        }

        return "not found";
    }

    public static function getLotByDateLimite($date)
    {
        $tontine = LotsTontine::where('date_bouffe', $date)->first();
        if ($tontine) {

            return $tontine;
        }

        return "not found";
    }



    public static function getByActiviteId($id)
    {
        $tontine = Tontine::where('activites_id', $id)->first();
        if ($tontine) {
            return $tontine;
        }

        return "not found";
    }

    public static function getLotsByIdCompteAndTontine($tontine, $id)
    {
        $tontine = LotsTontine::where('tontines_id', $tontine)->where('comptes_id', $id)->first();
        if ($tontine) {
            return $tontine;
        }

        return "not found";
    }

    public static function getLotsByIdCompte($id)
    {
        $tontine = LotsTontine::where('comptes_id', $id)->get();
        if ($tontine) {
            return $tontine;
        }

        return "not found";
    }

    /**
     * récupération de tout les lots qui sont dans la BD pour une tontine
     */
    public static function getLots($tontineId)
    {
        $lots = LotsTontine::where('tontines_id', $tontineId)->get();
        foreach ($lots as $key => $lot) {
            $compte = CompteMethods::getByIdWithIdMember($lot->comptes_id);
            if ($compte != "not found") {
                $lot['membre'] = $compte->membre;
                $lot['membres_id'] = $compte->membres_id;
            }
        }

        $success['status'] = "OK";
        $success['data'] = $lots;

        return $success;
    }

    public static function getNextLot($tontineId, $ags_id)
    {
        $ags = AgMethods::getById($ags_id);
        if ($ags == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "ag $ags_id not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        $lots = LotsTontine::where('tontines_id', $tontineId)->get();
        $actual = array();
        foreach ($lots as $key => $lot) {

            $da = gmdate("Y-m-d", $ags->date_ag);
            $dla = gmdate("Y-m-d", $lot->date_bouffe);

            if (strtotime($da) == strtotime($dla)) {
                $compte = CompteMethods::getByIdWithIdMember($lot->comptes_id);
                if ($compte != "not found") {
                    $lot['membre'] = $compte->membre;
                    $lot['membres_id'] = $compte->membres_id;
                }
                $actual[] = $lot;
            }
        }
        $success['status'] = "OK";
        $success['data'] = $actual;

        return $success;
    }
    /**
     * calendrier du nombre de bouf par AG
     */
    public static function getNumberOfGain($assocId, $tontineId)
    {
        DB::beginTransaction();
        $calendrier = array();

        $cycle = CycleMethods::checkActifCycle($assocId);
        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {
            $cagnote = $tontine->montant_cagnote;
            $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
            $noms = 0;
            $reste = 0;
            $coti = 0;
            $membres = array();
            if ($comptes != "not found") {
                foreach ($comptes as $key => $compte) {
                    $noms += $compte->nombre_noms;
                }
                if ($noms > 0) {
                    $coti = $noms * $tontine->montant_part;
                    if ($cycle != "not found") {
                        $ags = AgMethods::getByIdCycle($cycle->id);
                        if ($ags != "not found") {
                            $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                            $days = TontineMethods::getDateTontine($ags, $tontine->date_debut, $duree);

                            try {
                                $lots = LotsTontine::where('tontines_id', $tontineId)->get();
                                foreach ($lots as $key => $lot) {
                                    $compte = CompteMethods::getById($lot->comptes_id);
                                    if ($compte != "not found") {
                                        $lot['membre'] = $compte->membre;
                                        $membres[] = $lot;
                                    }
                                }
                                $nbm = count($membres);
                                $nbb = 0;
                                foreach ($days[0] as $key => $ag) {

                                    $somme = $coti + $reste;
                                    $nb_bouf = intdiv($somme, $cagnote);
                                    $reste = $somme - ($cagnote * $nb_bouf);
                                    if ($nb_bouf == 0) {
                                        DB::rollback();
                                        $err['errNo'] = 12;
                                        $err['errMsg'] = "bad configuration of tontine";
                                        $error['status'] = 'NOK';
                                        $error['data'] = $err;
                                        return $error;
                                    }

                                    $boufs = TontineMethods::tirage($nb_bouf, $membres);

                                    $membres = $boufs[1];
                                    $nbb += $nb_bouf;
                                    $calendrier[] = array(
                                        "date_bouffe" => $ag,
                                        "bouffent" => $nb_bouf,
                                        "montant" => $cagnote
                                    );
                                }

                                if ($nbb != $nbm) {

                                    DB::rollback();
                                    $err['errNo'] = 12;
                                    $err['errMsg'] = "mauvaise configuration de la tontine";
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }
                                DB::commit();
                                $success['status'] = 'OK';
                                $success['data'] = $calendrier;

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
                            $err['errMsg'] = 'Ag doesn\'t exist';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Cycle doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'nombres de noms de la tontine à 0';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Compte doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function getNumberOfGainAtAg($assocId, $tontineId, $ag_id)
    {
        DB::beginTransaction();
        $calendrier = array();

        $cycle = CycleMethods::checkActifCycle($assocId);
        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {
            $cagnote = $tontine->montant_cagnote;
            $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
            $noms = 0;
            $reste = 0;
            $coti = 0;
            $membres = array();
            if ($comptes != "not found") {
                foreach ($comptes as $key => $compte) {
                    $noms += $compte->nombre_noms;
                }
                if ($noms > 0) {
                    $coti = $noms * $tontine->montant_part;
                    if ($cycle != "not found") {
                        $ags = AgMethods::getByIdCycle($cycle->id);
                        $checkags = AgMethods::getById($ag_id);

                        if ($ags != "not found" && $checkags != "not found") {
                            $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                            $days = TontineMethods::getDateTontine($ags, $tontine->date_debut, $duree);

                            try {
                                $lots = LotsTontine::where('tontines_id', $tontineId)->get();
                                foreach ($lots as $key => $lot) {
                                    $compte = CompteMethods::getById($lot->comptes_id);
                                    if ($compte != "not found") {
                                        $lot['membre'] = $compte->membre;
                                        $membres[] = $lot;
                                    }
                                }
                                $nbm = count($membres);
                                $nbb = 0;
                                foreach ($days[0] as $key => $ag) {

                                    $somme = $coti + $reste;
                                    $nb_bouf = intdiv($somme, $cagnote);
                                    $reste = $somme - ($cagnote * $nb_bouf);
                                    if ($nb_bouf == 0) {
                                        DB::rollback();
                                        $err['errNo'] = 12;
                                        $err['errMsg'] = "bad configuration of tontine";
                                        $error['status'] = 'NOK';
                                        $error['data'] = $err;
                                        return $error;
                                    }

                                    $boufs = TontineMethods::tirage($nb_bouf, $membres);

                                    $membres = $boufs[1];
                                    $nbb += $nb_bouf;
                                    $calendrier[] = array(
                                        "date_bouffe" => $ag,
                                        "bouffent" => $nb_bouf,
                                        "montant" => $cagnote
                                    );
                                    if ($ag == $checkags->date_ag) {
                                        $success['status'] = 'OK-number';
                                        $success['data'] = $nb_bouf;

                                        return $success;
                                    }
                                }

                                if ($nbb != $nbm) {

                                    DB::rollback();
                                    $err['errNo'] = 12;
                                    $err['errMsg'] = "mauvaise configuration de la tontine";
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }
                                
                                DB::commit();
                                $success['status'] = 'OK';
                                $success['data'] = $calendrier;

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
                            $err['errMsg'] = 'Ag doesn\'t exist';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Cycle doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'nombres de noms de la tontine à 0';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Compte doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * tirage au sort selon le calendrier des boouffes
     */
    public static function tirage($nombre, $comptes)
    {
        $ind = array_rand($comptes, $nombre);
        $bouf = [];
        if (is_array($ind)) {
            foreach ($ind as $key => $i) {
                $bouf[] = $comptes[$i];
                unset($comptes[$i]);
            }
        } else {
            $bouf[] = $comptes[$ind];
            unset($comptes[$ind]);
        }

        return array($bouf, $comptes);
    }


    /**
     * générer les echeanciers d'une tontines
     */
    public static function EcheancierTontine($assocId, $tontine)
    {
        DB::beginTransaction();

        try {
            $cycle = CycleMethods::checkActifCycle($assocId);
            $tontine = TontineMethods::getById($tontine);

            if ($tontine != "not found" && ($tontine->type == "FIXE" || ($tontine->type == "VARIABLE" &&  $tontine->montant_part != null))) {

                $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
                $activite = ActiviteMethods::getActivityById($tontine->activites_id);
                EcheancesMethods::getAllEcheancierTypeAndDelete($tontine->activites_id, "cotisation");
                if ($comptes != "not found") {
                    if ($cycle != "not found") {
                        $allAgs = AgMethods::getByIdCycle($cycle->id);

                        $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                        $ags = AgMethods::getAllNextAgsNumberIncludeDateDebut($cycle->id, $tontine->date_debut, $duree);

                        if ($ags != "not found") {
                            $echeanciers = array();
                            foreach ($ags as $key => $ag) {
                                $dateAg = strtotime(gmdate('Y-m-d', $ag['ag']->date_ag));
                                $dateAc = strtotime(gmdate('Y-m-d', DateMethods::getCurrentDateInt()));

                                foreach ($comptes as $keys => $compte) {
                                    for ($i = 0; $i < $compte->nombre_noms; $i++) {
                                        $echeancier = array(
                                            "date_limite" => $ag['ag']->date_ag,
                                            "montant" => $tontine->montant_part,
                                            "etat" => ($ag['ag']->etat != "past") ? "init" : "cloture",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Part tontine (" . ($i + 1) . "/" . $compte->nombre_noms . ") - Tour (" . ($key + 1) . "/" . $duree . ")",
                                            "membres_id" => $compte->membres_id,
                                            "comptes_id" => $compte->id,
                                            "debit_credit" => "cotisation",
                                            "serie" => "tontine-$tontine->id"
                                        );
                                        $echeanciers[$ag['ag']->date_ag][] = Echeancier::create($echeancier);
                                    }
                                }
                            }
                            DB::commit();
                            $success['status'] = 'OK';
                            $success['data'] = $echeanciers;

                            return $success;
                        } else {
                            DB::rollback();
                            $err['errNo'] = 15;
                            $err['errMsg'] = 'Ag doesn\'t exist';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Cycle doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Compte doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist or it is type VARIABLE without minimum part';
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

    function cmp($a, $b) {
        return  $b->montant > $a->montant;
    }

    public static function EcheancierTontineVariable($assocId, $tontine, $echeances)
    {
        DB::beginTransaction();

        try {
            $cycle = CycleMethods::checkActifCycle($assocId);
            $tontine = TontineMethods::getById($tontine);

            if ($tontine->type == "VARIABLE") {
                $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
                $activite = ActiviteMethods::getActivityById($tontine->activites_id);
                EcheancesMethods::getAllEcheancierTypeAndDelete($tontine->activites_id, "cotisation");
                EcheancesMethods::getAllEcheancierTypeAndDelete($tontine->activites_id, "decaissement");
                if ($comptes != "not found") {
                    if ($cycle != "not found") {
                        $duree = $tontine->duree;
                        $ags = AgMethods::getAllNextAgsNumberIncludeDateDebut($cycle->id, $tontine->date_debut, $duree);
                     
                        if ($ags != "not found") {
                            $echeanciers = array();
                            $echeants = array();
                            foreach ($echeances as $keys => $echeance) {
                                $compte['nombre_noms'] = 1;
                                $compte['activites_id'] = $echeance['activites_id'];
                                $compte['membres_id'] = $echeance['membres_id'];
                                $compte['montant_cotisation'] = $echeance['montant_part'];
                                $comptes = CompteMethods::store($compte, $echeance['membres_id'], $echeance['activites_id']);
                                
                                if($comptes['status'] == "OK"){
                                    $echeance['compte'] = $comptes['data'];
                                    $echeants[] = $echeance;
                                }
                                else if($comptes['data']['errNo'] == 20){
                                    $cpt = CompteMethods::getByIdMA($echeance['activites_id'], $echeance['membres_id']);
                                    if($cpt != "not found"){
                                        $cpt->fill($compte);
                                        $cpt->save();
                                        $echeance['compte'] = $cpt;
                                        $echeants[] = $echeance;
                                    }
                                }else{
                                    DB::rollback();
                                    $err['errNo'] = $comptes['data']['errNo'];
                                    $err['errMsg'] = $comptes['data']['errMsg'];
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }
                                
                            }
                            $echeances = $echeants;
                            foreach ($echeances as $keys => $echeance) {
                                LotsTontine::where('comptes_id', $echeance['compte']['id'])->where('tontines_id', $tontine->id)->delete();
                                $lots = array(
                                    "montant" => $echeance['montant_part'] * $tontine->duree ?? 0,
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "tontines_id" => $tontine->id,
                                    "comptes_id" => $echeance['compte']['id'],
                                    "etat" => "init",
                                    'type' => 'principal',
                                    'created_by' => auth()->user()->id
                                );
                                $lot = LotsTontine::create($lots);
                                $lot['membres_id'] = $echeance['membres_id'];
                                $echeanciers["lots"][] = $lot;
                            }
                            foreach ($ags as $key => $ag) {
                                foreach ($echeances as $keys => $echeance) {
                                        $echeancier = array(
                                            "date_limite" => $ag['ag']->date_ag,
                                            "montant" => $echeance['montant_part'],
                                            "etat" => ($ag['ag']->etat != "past") ? "init" : "cloture",
                                            "date_created" => DateMethods::getCurrentDateInt(),
                                            "libelle" => "Cotisation - " . $activite->nom . " - Part tontine  - Tour (" . ($key + 1) . "/" . $duree . ")",
                                            "membres_id" => $echeance['membres_id'],
                                            "comptes_id" => $echeance['compte']['id'],
                                            "debit_credit" => "cotisation",
                                            "serie" => "tontine-$tontine->id"
                                        );
                                        $echeanciers["cotisations"][$ag['ag']->date_ag][] = Echeancier::create($echeancier);
                                }
                            }

                            DB::commit();
                            $success['status'] = 'OK';
                            $success['data'] = $echeanciers;

                            return $success;
                        } else {
                            DB::rollback();
                            $err['errNo'] = 15;
                            $err['errMsg'] = 'Ag doesn\'t exist';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Cycle doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Compte doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            
                
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist or it is type VARIABLE without minimum part';
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

    public static function EcheancierTontineForOneMember($assocId, $activite, $compte)
    {
        DB::beginTransaction();

        $cycle = CycleMethods::checkActifCycle($assocId);
        $tontine = TontineMethods::getByActiviteId($activite['id']);

        if ($tontine != "not found" && ($tontine->type == "FIXE" || ($tontine->type == "VARIABLE" &&  $tontine->montant_part != null))) {

            EcheancesMethods::getAllEcheancierTypeForCompteAndDelete("cotisation", $compte['id']);
            if ($compte != "not found") {
                if ($cycle != "not found") {
                    $allAgs = AgMethods::getByIdCycle($cycle->id);

                    $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                    $ags = AgMethods::getAllNextAgsNumberIncludeDateDebut($cycle->id, $tontine->date_debut, $duree);

                    if ($ags != "not found") {
                        $echeanciers = array();

                        foreach ($ags as $key => $ag) {
                            try {
                                for ($i = 0; $i < $compte->nombre_noms; $i++) {
                                    $echeancier = array(
                                        "date_limite" => $ag['ag']->date_ag,
                                        "montant" => $tontine->montant_part,
                                        "etat" => ($ag['ag']->etat != "past") ? "init" : "cloture",
                                        "date_created" => DateMethods::getCurrentDateInt(),
                                        "libelle" => "Cotisation - " . $activite->nom . " - Part tontine (" . ($i + 1) . "/" . $compte->nombre_noms . ") - Tour (" . ($key + 1) . "/" . $duree . ")",
                                        "membres_id" => $compte->membres_id,
                                        "comptes_id" => $compte->id,
                                        "debit_credit" => "cotisation",
                                        "serie" => "tontine-$tontine->id"
                                    );
                                    $echeanciers[$ag['ag']->date_ag][] = Echeancier::create($echeancier);
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
                        $success['status'] = 'OK';
                        $success['data'] = $echeanciers;

                        return $success;
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Ag doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Cycle doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Compte doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist or it is type VARIABLE without minimum part';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
    


    /**
     * assigner un lots à plusieurs membres
     */
    public static function assignLotsComptes($tontineId, $lots)
    {

        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            $cycle = null;
            DB::beginTransaction();
            try {
                $saved = array();
                $passedDate = array();
                $init = false;
                $currentAg = DateMethods::getCurrentDateInt();
                foreach ($lots as $key => $lot) {

                    // if(isset($lot['echeanciers_id'])){
                    //     EcheancesMethods::deleteEcheancier($lot['echeanciers_id']);
                    // }
                    if ($activite != "not found") {
                        $ech = Echeancier::where('serie', "lot-{$lot['id']}")->first();
                        $cycle = CycleMethods::checkActifCycle($activite->associations_id);

                        if ($cycle) {
                            $currentAg = AgMethods::getCurrentCycle($cycle->id);
                            if ($currentAg == "not found") {
                                $currentAg == DateMethods::getCurrentDateInt();
                            } else {
                                $currentAg = $currentAg->date_ag;
                            }
                        }

                        $echeancier = array(
                            "comptes_id" => $lot['comptes_id'],
                            "montant" => $lot['montant'],
                            "debit_credit" => "decaissement",
                            "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                            "date_limite" => $lot['date_bouffe'],
                            "serie" => "lot-{$lot['id']}",
                            "etat" => ($lot['date_bouffe'] < $currentAg) ? "cloture" : "init"
                        );

                        if ($ech) {

                            $ech->fill($echeancier);
                            $ech->save();
                        } else {
                            $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);

                            if ($ech["status"] != "OK") {
                                DB::rollback();
                                $err['errNo'] = 15;
                                $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return $error;
                            }

                            $lot['echeanciers_id'] = $ech['data']['id'];
                        }
                    }

                    // $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
                    // if($ech["status"] != "OK"){
                    //     DB::rollback();
                    //     $err['errNo'] = 15;
                    //     $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                    //     $error['status'] = 'NOK';
                    //     $error['data'] = $err;
                    //     return $error;   
                    // }


                    if (($lot['date_bouffe'] < $currentAg) && !$init) {
                        $comptes = CompteMethods::getByIdAAll($activite->id);
                        foreach ($comptes as $key => $compte) {
                            $compte->fill([
                                "solde" => 0
                            ]);
                            $compte->save();
                        }
                        $init = true;
                    }

                    if ($lot['date_bouffe'] && ($lot['date_bouffe'] < $currentAg) && !in_array($lot['date_bouffe'], $passedDate)) {
                        $passedDate[] = $lot['date_bouffe'];
                        if ($cycle) {
                            // $number = AgMethods::countNumberOfAgLessThan($cycle->id, $currentAg);
                            $comptes = CompteMethods::getByIdAAll($activite->id);
                            foreach ($comptes as $key => $compte) {
                                if ($compte->id == $lot['comptes_id']) {
                                    $montant = $compte->solde + $tontine->montant_part * $compte->nombre_noms;
                                    $montant = $montant - $lot['montant'];
                                    $compte->fill([
                                        "solde" => $montant
                                    ]);
                                    $compte->save();
                                } else {
                                    $montant = $compte->solde + $tontine->montant_part * $compte->nombre_noms;
                                    $compte->fill([
                                        "solde" => $montant
                                    ]);
                                    $compte->save();
                                }
                            }
                        }
                        $lot['etat'] = "paye";
                    } else if ($lot['date_bouffe'] && ($lot['date_bouffe'] < $currentAg) && in_array($lot['date_bouffe'], $passedDate)) {
                        if ($cycle) {
                            // $number = AgMethods::countNumberOfAgLessThan($cycle->id, $currentAg);
                            $comptes = CompteMethods::getByIdAAll($activite->id);
                            foreach ($comptes as $key => $compte) {
                                if ($compte->id == $lot['comptes_id']) {
                                    $montant = $compte->solde - $lot['montant'];
                                    $compte->fill([
                                        "solde" => $montant
                                    ]);
                                    $compte->save();
                                }
                            }
                        }
                        $lot['etat'] = "paye";
                    }

                    $lot['date_updated'] =  DateMethods::getCurrentDateInt();
                    $l = LotsTontine::find($lot['id']);
                    if (!$l) {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = "lot {$lot['id']} doesn\'t exist";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                    $l->fill($lot);
                    $l->save();
                    $saved[] = $l;
                }

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = $saved;

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
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function assignLotPrincipalComptes($tontineId, $lot, $lotId, $enchere = 0)
    {

        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            $cycle = null;
            DB::beginTransaction();
            try {
                $saved = array();
                $passedDate = array();
                $init = false;

                if ($activite != "not found") {

                    $cycle = CycleMethods::checkActifCycle($activite->associations_id);
                    if ($cycle == "not found") {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'pas de cycle actif';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                    $currentAg = AgMethods::getCurrentCycle($cycle->id);
                    $lot['date_bouffe'] = $currentAg->date_ag;

                    if ($enchere != 0) {
                        $activite->fill(["caisse" => $activite->caisse + $enchere]);
                        $activite->save();
                        $lot['enchere'] = $enchere;
                    }

                    $ech = Echeancier::where('serie', "lot-{$lot['id']}")->first();

                    $echeancier = array(
                        "comptes_id" => $lot['comptes_id'],
                        "montant" => $lot['montant'],
                        "debit_credit" => "decaissement",
                        "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                        "date_limite" => $lot['date_bouffe'],
                        "serie" => "lot-{$lot['id']}",
                        "etat" => "init"
                    );
                    if ($ech) {
                        $ech->fill($echeancier);
                        $ech->save();
                        $lot['echeanciers_id'] = $ech->id;
                    } else {
                        $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
                        if ($ech["status"] != "OK") {
                            DB::rollback();
                            $err['errNo'] = 15;
                            $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                        $lot['echeanciers_id'] = $ech['data']['id'];
                    }
                }




                if (($lot['date_bouffe'] < $currentAg->date_ag) && !$init) {
                    $comptes = CompteMethods::getByIdAAll($activite->id);
                    foreach ($comptes as $key => $compte) {
                        $compte->fill([
                            "solde" => 0
                        ]);
                        $compte->save();
                    }
                    $init = true;
                }


                $lot['date_updated'] =  DateMethods::getCurrentDateInt();
                $lot['etat'] = "recu";

                $l = LotsTontine::where('id', $lotId)->where('tontines_id', $tontineId)->first();
                if (!$l) {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "lot {$lot['id']} doesn\'t exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $l->fill($lot);
                $l->save();
                $saved[] = $l;

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = $saved;

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
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function desassignLotPrincipalComptes($tontineId, $lot, $lotId, $enchere = 0)
    {

        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            $cycle = null;
            DB::beginTransaction();
            try {
                $saved = array();
                $passedDate = array();
                $init = false;

                if ($activite != "not found") {
                    $lot['date_bouffe'] = null;

                    if ($enchere != 0) {
                        $activite->fill(["caisse" => $activite->caisse - $lot['enchere']]);
                        $activite->save();
                        $lot['enchere'] = 0;
                    }

                    $ech = Echeancier::where('serie', "lot-{$lot['id']}")->first();

                    $echeancier = array(
                        "comptes_id" => $lot['comptes_id'],
                        "montant" => $lot['montant'],
                        "debit_credit" => "decaissement",
                        "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                        "date_limite" => 0,
                        "serie" => "lot-{$lot['id']}",
                        "etat" => "init"
                    );
                    if ($ech) {
                        $ech->fill($echeancier);
                        $ech->save();
                        $lot['echeanciers_id'] = $ech->id;
                    } else {
                        $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
                        if ($ech["status"] != "OK") {
                            DB::rollback();
                            $err['errNo'] = 15;
                            $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                        $lot['echeanciers_id'] = $ech['data']['id'];
                    }
                }

                $lot['date_updated'] =  DateMethods::getCurrentDateInt();
                $lot['etat'] = "init";

                $l = LotsTontine::where('id', $lotId)->where('tontines_id', $tontineId)->first();
                if (!$l) {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "lot {$lot['id']} doesn\'t exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $l->fill($lot);
                $l->save();
                $saved[] = $l;

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = $saved;

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
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function assignLotCompte($tontineId, $lot)
    {
        DB::beginTransaction();
        $tontine = TontineMethods::getById($tontineId);
        if ($tontine == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            if ($activite != "not found") {
                Echeancier::where('serie', "lot-{$lot['id']}")->delete();
                $cycle = CycleMethods::checkActifCycle($activite->associations_id);
                $echeancier = array(
                    "comptes_id" => $lot['comptes_id'],
                    "montant" => $lot['montant'],
                    "debit_credit" => "decaissement",
                    "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                    "date_limite" => $lot['date_bouffe'],
                    "serie" => "lot-{$lot['id']}"
                );

                $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
                if ($ech["status"] != "OK") {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $lot['echeanciers_id'] = $ech['data']['id'];
            }
            $lot['date_updated'] =  DateMethods::getCurrentDateInt();
            $l = LotsTontine::find($lot['id']);
            if (!$l) {
                $err['errNo'] = 15;
                $err['errMsg'] = "lot {$lot['id']} doesn\'t exist";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $l->fill((array)$lot);
            $l->save();

            DB::commit();

            $success['status'] = 'OK';
            $success['data'] = $l;

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
     * permutations de jours de bouffe
     */
    public static function permutationLots($tontineId, $comptes_id1, $comptes_id2)
    {

        $lot1 = TontineMethods::getLotByAndTontine($tontineId, $comptes_id1);
        $lot2 = TontineMethods::getLotByAndTontine($tontineId, $comptes_id2);
        $ech1 = null;
        $ech2 = null;
        try {
            if ($lot1 != "not found" && $lot2 != "not found") {

                $cpt1 = CompteMethods::getById($lot1->comptes_id);
                $cpt2 = CompteMethods::getById($lot2->comptes_id);

                $ech1 = Echeancier::where("debit_credit", "decaissement")->where('serie', "lot-$lot1->id")->first();
                $ech2 = Echeancier::where("debit_credit", "decaissement")->where('serie', "lot-$lot2->id")->first();
                if ($ech1 && $ech2) {
                    $ech1->date_limite = $lot2->date_bouffe;
                    $ech1->save();
                    $ech2->date_limite = $lot1->date_bouffe;
                    $ech2->save();
                }
                $montant = $lot1->montant;
                $date_bouffe = $lot1->date_bouffe;

                $lot1->montant = $lot2->montant;
                $lot1->date_bouffe = $lot2->date_bouffe;
                $lot1->save();

                $lot2->montant = $montant;
                $lot2->date_bouffe = $date_bouffe;
                $lot2->save();

                // if($ech1 && $ech2){

                //     $ech1->comptes_id == $cpt2->id ? $ech1->comptes_id = $cpt1->id : $ech1->comptes_id = $cpt2->id; 
                //     $ech1->comptes_id == $cpt2->id ? $ech1->membres_id = $cpt1->membres_id : $ech1->membres_id = $cpt2->membres_id; 
                //     // $ech1->comptes_id == $cpt2->id ? $ech1->date_limite = $lot1->date_bouffe : $ech1->date_limite = $lot2->date_bouffe; 

                //     $ech2->comptes_id == $cpt2->id ? $ech2->comptes_id = $cpt1->id : $ech2->comptes_id = $cpt2->id; 
                //     $ech2->comptes_id == $cpt2->id ? $ech2->membres_id = $cpt1->membres_id : $ech2->membres_id = $cpt2->membres_id; 
                //     // $ech2->comptes_id == $cpt2->id ? $ech2->date_limite = $lot1->date_bouffe : $ech2->date_limite = $lot2->date_bouffe; 

                //     $ech1->save();
                //     $ech2->save();

                // }

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = "successful";

                return $success;
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'one of these two accounts lots  doesn\'t exist';
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
     * changer la date d'un lot pour une date supérieur
     */
    public static function changeDateLotTontine($assocId, $tontineId, $lotId, $date){

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $lot = TontineMethods::getLotByAndTontine($tontineId, $lotId);
        if($lot == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "lot tontine not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ech = Echeancier::where("debit_credit", "decaissement")->where('serie', "lot-$lot->id")->where('etat','!=','cloture')->first();
        if(!$ech){
            $err['errNo'] = 14;
            $err['errMsg'] = "echeances lot tontine not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $cycle = CycleMethods::checkActifCycle($assocId);
        if ($cycle == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'cycle doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $isDateCycle = AgMethods::checkAgDateExist($cycle->id, $date);
        if($isDateCycle === false){
            $err['errNo'] = 14;
            $err['errMsg'] = "date given is not in this cycle";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if($lot->date_bouffe >= $date){
            $err['errNo'] = 14;
            $err['errMsg'] = "choose date greather than this";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::beginTransaction();
        try {

            $lot->date_bouffe = $date;
            $lot->save();

            $ech->date_limite = $date;
            $ech->save();

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

    /**
     * création du lot avec le comptes et l'activite
     */
    public static function createLot($compte, $activite)
    {
        try {

            DB::beginTransaction();
            $tontine = TontineMethods::getByActiviteId($activite->id);
            if ($tontine != "not found") {

                // if($tontine->type == "VARIABLE"){
                //     $err['errNo'] = 20;
                //     $err['errMsg'] = 'pas possible de creer de lots';
                //     $error['status'] = 'NOK';
                //     $error['data'] = $err;
                //     return $error;   
                // }

                $cycle = CycleMethods::checkActifCycle($activite->associations_id);
                if ($cycle != "not found") {
                    $ags = AgMethods::getByIdCycle($cycle->id);

                    if ($ags != "not found") {
                        if ((isset($tontine->montant_part) && !empty($tontine->montant_part)) && (isset($tontine->montant_cagnote) && !empty($tontine->montant_cagnote))) {
                            $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                            
                            $days = TontineMethods::getDateTontine($ags, $tontine->date_debut, $duree);
                            if ($duree > $days[1]  || $duree < 1) {
                                return "not possible";
                            }
                            $tontine->fill([
                                "date_fin" => $days[count($days) - 1],
                                "duree" => $duree
                            ]);
                            $tontine->save();
                        }
                        $exist = LotsTontine::where('tontines_id', $tontine->id)
                            ->where('comptes_id', $compte->id)
                            ->get();
                        if (count($exist) > 0 && count($exist) != $compte['nombre_noms']) {
                            foreach ($exist as $key => $value) {
                                if(isset($value->echeanciers_id)){
                                    $ech_id = $value->echeanciers_id;
                                    $value->echeanciers_id = null;
                                    $value->save();
                                    EcheancesMethods::deleteEcheancier($ech_id);
                                }else{
                                    Echeancier::where('serie', "lot-{$value->id}")->delete();
                                }
                                $value->delete();
                            }
                            for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                                $lots = array(
                                    "montant" => $tontine->montant_cagnote ?? 0,
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "tontines_id" => $tontine->id,
                                    "comptes_id" => $compte->id,
                                    "etat" => "init",
                                    'type' => 'principal',
                                    'created_by' => auth()->user()->id
                                );
                                $lot = LotsTontine::create($lots);
                            }
                        } else if (count($exist) == 0) {
                            for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                                $lots = array(
                                    "montant" => $tontine->montant_cagnote ?? 0,
                                    "date_created" => DateMethods::getCurrentDateInt(),
                                    "tontines_id" => $tontine->id,
                                    "comptes_id" => $compte->id,
                                    "etat" => "init",
                                    'type' => "principal",
                                    'created_by' => auth()->user()->id
                                );
                                $lot = LotsTontine::create($lots);
                            }
                        }
                    } else {
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'create lot: Ag doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'create lot: Cycle doesn\'t exist or it\'s inactive';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }


                // return $lot;
                // return "not possible";
                DB::commit();
                $success['status'] = "OK";
                $success['data'] = "successful";

                return $success;
            } else {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist';
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

    public static function createLotEndpoint($tontineId, $lot, $type, $enchere, $interet, $remboursement)
    {

        $tontine = TontineMethods::getById($tontineId);
        if ($tontine == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        DB::beginTransaction();
        try {


            $cagnote = TontineMethods::getCagnote($tontine);

            if ($type == "secondaire") {

                if ($lot['montant'] > $cagnote['data']) {
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = "pas assez d'argent pour effectuer la transaction";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            $lot['date_created'] = DateMethods::getCurrentDateInt();
            $lot['etat'] = 'recu';
            $lot['type'] = $type;
            
            $lot['tontines_id'] = $tontineId;
            $lot['created_by'] = auth()->user()->id;
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            if ($activite == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'activity doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            if ($type == "secondaire" && $enchere != 0) {
                $activite->fill(["caisse" => $activite->caisse + $enchere]);
                $activite->save();
                $lot['enchere'] = $enchere;
            }

            $savedLot = LotsTontine::create($lot);

            $ag = AgMethods::getNextAg($activite->cycles_id);
            if ($ag == "not found") {
                DB::rollback();
                $err['errNo'] = 11;
                $err['errMsg'] = "pas de prochaine ag";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $echeancier = array(
                "comptes_id" => $lot['comptes_id'],
                "montant" => $lot['montant'] + $interet,
                "debit_credit" => "cotisation",
                "libelle" => "Cotisation - " . $activite->nom . " - Lot secondaire de la tontine - $activite->nom",
                "date_limite" => $remboursement ?? $ag->date_ag,
                "serie" => "lot-{$savedLot->id}"
            );

            $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
            if ($ech["status"] != "OK") {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }



            $assign = TontineMethods::assignLotCompte($tontineId, $savedLot);
            if ($assign['status'] != "OK") {
                DB::rollback();
                $err['errNo'] = $assign['data']['errNo'];
                $err['errMsg'] = $assign['data']['errMsg'];
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            DB::commit();
            return $assign;
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function deleteLotSecondaire($tontineId, $lot_id)
    {
        DB::beginTransaction();
        $lot = LotsTontine::where('tontines_id', $tontineId)->where('id', $lot_id)->first();

        if (!$lot) {
            $err['errNo'] = 15;
            $err['errMsg'] = ' Lot tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if ($lot->type != "secondaire") {
            $err['errNo'] = 12;
            $err['errMsg'] = 'pas le droit de supprimer ce lot';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {

            $tontine = TontineMethods::getById($tontineId);
            if ($tontine == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            if ($activite == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'activity doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            if ($lot['enchere'] != 0) {
                $activite->fill(["caisse" => $activite->caisse - $lot['enchere']]);
                $activite->save();
            }

            Echeancier::where('serie', "lot-$lot->id")->delete();

            $lot->delete();

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

    public static function createNewMemberLot($compte, $activite, $size)
    {
        try {

            DB::beginTransaction();
            $tontine = TontineMethods::getByActiviteId($activite->id);
            if ($tontine != "not found") {
                $exist = LotsTontine::where('tontines_id', $tontine->id)
                    ->where('comptes_id', $compte->id)
                    ->get();

                $all = array();
                if (count($exist) > 0 && count($exist) != $compte['nombre_noms']) {
                    foreach ($exist as $key => $value) {
                        $value->delete();
                    }
                    for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                        $lots = array(
                            "montant" => $tontine->montant_part * $size,
                            "date_created" => DateMethods::getCurrentDateInt(),
                            "tontines_id" => $tontine->id,
                            "comptes_id" => $compte->id,
                            "etat" => "init",
                            'created_by' => auth()->user()->id
                        );
                        $lot = LotsTontine::create($lots);
                        $all[] = $lot;
                    }
                } else if (count($exist) == 0) {
                    for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                        $lots = array(
                            "montant" => $tontine->montant_part * $size,
                            "date_created" => DateMethods::getCurrentDateInt(),
                            "tontines_id" => $tontine->id,
                            "comptes_id" => $compte->id,
                            "etat" => "init",
                            'created_by' => auth()->user()->id
                        );
                        $lot = LotsTontine::create($lots);
                        $all[] = $lot;
                    }
                }

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $all;

                return $success;
            } else {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist';
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


    public static function createNewMemberLotAdded($compte, $activite, $montant, $date_bouffe)
    {
        try {

            DB::beginTransaction();
            $tontine = TontineMethods::getByActiviteId($activite->id);
            if ($tontine != "not found") {
                $exist = LotsTontine::where('tontines_id', $tontine->id)
                    ->where('comptes_id', $compte->id)
                    ->get();

                $all = array();
                if (count($exist) > 0 && count($exist) != $compte['nombre_noms']) {
                    foreach ($exist as $key => $value) {
                        $value->delete();
                    }
                    for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                        $lots = array(
                            "montant" => $montant,
                            "date_created" => DateMethods::getCurrentDateInt(),
                            "tontines_id" => $tontine->id,
                            "comptes_id" => $compte->id,
                            "etat" => "init",
                            "date_bouffe" => $date_bouffe,
                            'created_by' => auth()->user()->id
                        );
                        $lot = LotsTontine::create($lots);
                        $all[] = $lot;
                    }
                } else if (count($exist) == 0) {
                    for ($i = 0; $i < $compte['nombre_noms']; $i++) {
                        $lots = array(
                            "montant" => $montant,
                            "date_created" => DateMethods::getCurrentDateInt(),
                            "tontines_id" => $tontine->id,
                            "comptes_id" => $compte->id,
                            "etat" => "init",
                            "date_bouffe" => $date_bouffe,
                            'created_by' => auth()->user()->id
                        );
                        $lot = LotsTontine::create($lots);
                        $all[] = $lot;
                    }
                }

                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $all;

                return $success;
            } else {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Tontine doesn\'t exist';
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
     * récupération des jours de tontine à partir de la date de début de la tontine
     */
    public static function getDateTontine($ags, $date, $duree)
    {
        $days = array();
        $nb = 0;
        foreach ($ags as $key => $ag) {
            if ($ag->date_ag >= $date && $duree > 0) {
                $nb++;
                $days[] = $ag->date_ag;
                $duree--;
            }
        }

        return array($days, $nb);
    }



    /**
     * 
     */
    public static function getCagnote($tontine)
    {
        $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
        $cagnote = 0;
        foreach ($comptes as $key => $compte) {
            $cagnote += $compte->solde;
        }

        $activite = ActiviteMethods::getActivityById($tontine->activites_id);
        if ($activite != "not found") {
            $cagnote += $activite->caisse;
        }

        return array(
            "status" => "OK",
            "data" => $cagnote
        );
    }

    /**
     * 
     */
    public static function tirageAuSort($assocId, $tontineId)
    {
        DB::beginTransaction();
        $calendrier = array();

        $cycle = CycleMethods::checkActifCycle($assocId);
        $tontine = TontineMethods::getById($tontineId);
        if ($tontine != "not found") {

            $cagnote = $tontine->montant_cagnote;
            $comptes = CompteMethods::getByIdAAll($tontine->activites_id);
            $activite = ActiviteMethods::getActivityById($tontine->activites_id);
            $noms = 0;
            $reste = 0;
            $coti = 0;
            $membres = array();
            if ($comptes != "not found") {
                foreach ($comptes as $key => $compte) {
                    $noms += $compte->nombre_noms;
                }
                $coti = $noms * $tontine->montant_part;

                if ($cycle != "not found") {
                    $ags = AgMethods::getByIdCycle($cycle->id);

                    if ($ags != "not found") {
                        $duree = intdiv($tontine->montant_cagnote, $tontine->montant_part);
                        $days = TontineMethods::getDateTontine($ags, $tontine->date_debut, $duree);

                        try {

                            $lots = LotsTontine::where('tontines_id', $tontineId)->get();

                            foreach ($lots as $key => $lot) {
                                $compte = CompteMethods::getById($lot->comptes_id);
                                if ($compte != "not found") {
                                    $lot['membre'] = $compte->membre;
                                    $membres[] = $lot;
                                }
                            }

                            $nbm = count($membres);
                            $nbb = 0;

                            foreach ($days[0] as $key => $ag) {

                                $somme = $coti + $reste;
                                $nb_bouf = intdiv($somme, $cagnote);
                                $reste = $somme - ($cagnote * $nb_bouf);
                                if ($nb_bouf == 0) {
                                    DB::rollback();
                                    $err['errNo'] = 11;
                                    $err['errMsg'] = "bad configuration of tontine";
                                    $error['status'] = 'NOK';
                                    $error['data'] = $err;
                                    return $error;
                                }

                                $boufs = TontineMethods::tirage($nb_bouf, $membres);

                                $membres = $boufs[1];
                                $nbb += $nb_bouf;
                                $updated_lots = array();
                                foreach ($boufs[0] as $key => $bouf) {
                                    $lot = TontineMethods::getLotById($bouf->id);
                                    if ($lot != "not found") {
                                        $membre = $lot['membre'];
                                        $membres_id = $lot['membres_id'];
                                        unset($lot['membre']);
                                        unset($lot['membres_id']);
                                        $lot->fill([
                                            "date_bouffe" => $ag,
                                            "date_updated" => DateMethods::getCurrentDateInt()
                                        ]);
                                        $lot->save();
                                        if (isset($lot['echeanciers_id'])) {
                                            $ech_id = $lot->echeanciers_id;
                                            $lot->echeanciers_id = null;
                                            $lot->save();
                                            EcheancesMethods::deleteEcheancier($ech_id);
                                        }else{
                                            $ech = Echeancier::where('serie',"lot-{$lot['id']}")->delete();
                                        }
                                        $echeancier = array(
                                            "comptes_id" => $lot['comptes_id'],
                                            "montant" => $lot['montant'],
                                            "debit_credit" => "decaissement",
                                            "libelle" => "Decaissement - " . $activite->nom . " - Lot principale de la tontine",
                                            "date_limite" => $lot['date_bouffe'],
                                            "serie" => "lot-{$lot['id']}"

                                        );
                                        $ech = EcheancesMethods::createEcheance($lot['comptes_id'], $echeancier);
                                        if ($ech["status"] != "OK") {
                                            DB::rollback();
                                            $err['errNo'] = 15;
                                            $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                                            $error['status'] = 'NOK';
                                            $error['data'] = $err;
                                            return $error;
                                        }
                                        $lot->echeanciers_id = $ech['data']['id'];
                                        $lot->save();

                                        $lot['membre'] = $membre;
                                        $lot['membres_id'] = $membres_id;
                                        $updated_lots[] = $lot;
                                    }
                                }

                                $calendrier[] = array(
                                    "date_bouffe" => $ag,
                                    "bouffent" => $nb_bouf,
                                    "beneficiaires" => $updated_lots,
                                    "montant" => $cagnote
                                );
                            }

                            if ($nbb != $nbm) {

                                DB::rollback();
                                $err['errNo'] = 11;
                                $err['errMsg'] = "mauvaise configuration de la tontine";
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
                                return $error;
                            }
                            DB::commit();
                            $success['status'] = 'OK';
                            $success['data'] = $calendrier;

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
                        $err['errMsg'] = 'Ag doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Cycle doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Compte doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {
            DB::rollback();
            $err['errNo'] = 15;
            $err['errMsg'] = 'Tontine doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function deleteCompte($comptes_id, $activites_id){
        $activite = ActiviteMethods::getActivityById($activites_id);
        if($activite == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "activite not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
        $compte = CompteMethods::getById($comptes_id);
        if($compte == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "compte not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $membre = MembreMethods::getById($compte->membres_id);
        if($membre == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "membre not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $currentAg = AgMethods::getCurrentAg($membre->id);
        if($currentAg == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "currentAg not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if($compte->activites_id != $activites_id){
            $err['errNo'] = 14;
            $err['errMsg'] = "compte don't match with activity";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        unset($compte->membre);
        $solde_total = $compte->solde - $compte->dette_c - $compte->dette_a;
        DB::beginTransaction();
        try {
            if($solde_total < 0){
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('cotisation', $comptes_id);
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('decaissement', $comptes_id);
                LotsTontine::where('comptes_id', $comptes_id)->delete();
                $echeancier = array(
                    "comptes_id" => $comptes_id,
                    "montant" => abs($solde_total),
                    "debit_credit" => "acquitement",
                    "libelle" => "Acquitement - " . $activite->nom . " - dette suppression compte",
                    "date_limite" => $currentAg->date_ag,
                    "serie" => "solde-{$compte->id}"
    
                );
                $ech = EcheancesMethods::createEcheance($comptes_id, $echeancier);
                if ($ech["status"] != "OK") {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $compte->solde = 0;
                $compte->dette_c = 0;
                $compte->dette_a = 0;
                $compte->a_supprimer = "oui";
                $compte->save();
    
                DB::commit();
                return array(
                    "status" => "OK",
                    "data" => "compte supprimé après paiement de l'echeance  l'echeance d'acquitement créé"
                );
            } else if($solde_total > 0){
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('cotisation', $comptes_id);
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('decaissement', $comptes_id);
                LotsTontine::where('comptes_id', $comptes_id)->delete();
                $echeancier = array(
                    "comptes_id" => $comptes_id,
                    "montant" => abs($solde_total),
                    "debit_credit" => "decaissement",
                    "libelle" => "Decaissement - " . $activite->nom . " - solde suppression compte",
                    "date_limite" => $currentAg->date_ag,
                    "serie" => "solde-{$compte->id}"
    
                );
                $ech = EcheancesMethods::createEcheance($comptes_id, $echeancier);
                if ($ech["status"] != "OK") {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'erreur lors de la suppression de l\'echeancier';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $compte->solde = 0;
                $compte->dette_c = 0;
                $compte->dette_a = 0;
                $compte->a_supprimer = "oui";
                $compte->save();
    
                DB::commit();
                return array(
                    "status" => "OK",
                    "data" => "compte supprimé après paiement de l'echeance de décaissement créé"
                );
            }else{
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('cotisation', $comptes_id);
                EcheancesMethods::getAllEcheancierTypeForCompteAndDelete('decaissement', $comptes_id);
                LotsTontine::where('comptes_id', $comptes_id)->delete();
                
                $compte->deleted_at = DateMethods::getCurrentDateInt();
                $compte->save();

                DB::commit();
                return array(
                    "status" => "OK",
                    "data" => "compte supprimé avec succès"
                );
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
}
