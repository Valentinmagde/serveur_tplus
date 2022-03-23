<?php

namespace App\CustomModels;

use App\Models\Activite;
use App\Models\ActivitesGenerique;
use App\Models\Association;
use App\Models\Evenement;
use App\Models\MainsLevee;
use App\Models\Mutuelle;
use App\Models\Pam;
use App\Models\Projet;
use App\Models\Solidarite;
use App\Models\Tontine;
use Illuminate\Support\Carbon;
use App\Events\Finance;

use Illuminate\Support\Facades\DB;

class ActiviteMethods
{

    public static function getAssociationIdByActiviteId($activite_id)
    {
        $activite = ActiviteMethods::getActivityById($activite_id);
        if ($activite != "not found") {
            $assocId = CycleMethods::getById($activite->cycles_id);
            if ($assocId != 'not found') {
                return $assocId->associations_id;
            }
        }

        return 'not found';
    }


    public static function getActivityCaisse($assocId)
    {
        $caisse = Activite::where('associations_id', $assocId)
            ->where('type', 'caisse')
            ->first();
        if ($caisse) return $caisse;
        return 'not found';
    }

    /**
     * tresorerie d'une association
     */
    public static function getTresorerieByAssociations($associationId)
    {

        $activities = Activite::where('associations_id', $associationId)->get();

        $activites = array();

        foreach ($activities as $key => $activity) {
            $solde = CompteMethods::getotalSoldeByActivityId($activity->id);
            // $solde += $activity->caisse;

            $activites[] = array(
                "activite" => $activity,
                "tresorerie" => $solde
            );
        }

        $success['status'] = 'OK';
        $success['data'] =  $activites;

        return $success;
    }

    public static function getTresorerieByAssociationsData($associationId)
    {

        $activities = Activite::where('associations_id', $associationId)->get();

        $activites = array();
        $caisse = 0;
        foreach ($activities as $key => $activity) {
            $solde = CompteMethods::getotalSoldeByActivityId($activity->id);
            // $solde += $activity->caisse;
            $caisse += $solde;
            $activites[] = array(
                "activite" => $activity,
                "tresorerie" => $solde
            );
        }

        return $caisse;
    }
    /**
     * création d'une activité
     * 
     * @param $activity qui est une activite
     * @param $associationId qui est l'id de l'association qui doit abriter l'activité
     * 
     * @return $error/$success 
     */
    public static function createActivity($activity, $associationId)
    {


        $association = AssociationMethods::getById($associationId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "association doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }

        //date actuelle
        $date_actu = Carbon::now();

        //transformer cette date en entier
        $date_actu = strtotime($date_actu);
        $cycle = CycleMethods::checkActifCycle($associationId);
        if ($cycle != "not found" && $activity['type'] != "Mutuelle" && $activity['type'] != "Solidarite" && $activity['type'] != "Pam") {
            $activity['cycles_id'] = $cycle->id;
        }
        $activity['date_created'] = $date_actu;
        $activity['associations_id'] = $associationId;
        $activity['etat'] = "init";
        // switch ($activity['type']) {
        //     case 'Tontine':
        //         $activity['methode_decaissement'] = "individuel";
        //         break;
        //     case 'Mutuelle':
        //         $activity['methode_decaissement'] = "individuel";
        //         break;
        //     case 'Evenement':
        //         $activity['methode_decaissement'] = "collectif";
        //         break;
        //     case 'Solidarite':
        //         $activity['methode_decaissement'] = "collectif";
        //         break;
        //     case 'Projet':
        //         $activity['methode_decaissement'] = "collectif";
        //         break;
        //     case 'Main_levee':
        //         $activity['methode_decaissement'] = "collectif";
        //         break;
        //     case 'Pam':
        //         $activity['methode_decaissement'] = "collectif";
        //         break;
        // }

        $activite = Activite::create($activity);

        $generic = ActivitesGenerique::create(["activites_id" => $activite['id']]);
        if ($activite) {
            $success['status'] = 'OK';
            $success['data'] =  $activite;

            return $success;
        }


        $err['errNo'] = 10;
        $err['errMsg'] = "problems with activity creation";
        $error['status'] = 'NOK';
        $error['data'] = $err;

        return $error;
    }

    /**
     * update d'une activité générale
     */
    public static function updateActivity($activity_data, $activity_id)
    {

        $activity = ActiviteMethods::getActivityById($activity_id);

        if ($activity == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }

        try {

            $activity->fill($activity_data);
            $activity->save();


            $success['status'] = 'OK';
            $success['data'] =  $activity;

            return $success;
        } catch (\Exception $e) {

            $err['errNo'] = 10;
            $err['errMsg'] = "problems with activity update";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }

    public static function updateSomeActivityType($data, $type, $type_id)
    {

        $activity = ActiviteMethods::getSomeActivityById($type, $type_id);

        if ($activity != "not found") {
            try {
                if ($type == "Tontine")
                    unset($activity->lots);
                if ($type == "Solidarite")
                    $date['date_updated'] = DateMethods::getCurrentDateInt();
                $activity->fill($data);
                $activity->save();


                $success['status'] = 'OK';
                $success['data'] =  $activity;

                return $success;
            } catch (\Exception $e) {

                $err['errNo'] = 11;
                $err['errMsg'] = "problems with activity '{$type}' update";
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }


    /**
     * récupérer les activités d'une association
     * 
     * @param $associationId qui l'id de l'association
     * 
     * @return $activite[]
     */
    public static function getActivitiesByAssociations($associationId)
    {

        $activities = Activite::where('associations_id', $associationId)->get();

        $activites = array();

        foreach ($activities as $key => $activity) {
            $activites[] = $activity;
        }

        $success['status'] = 'OK';
        $success['data'] =  $activites;

        return $success;
    }


    public static function getActivitiesByAssociationsType($associationId, $type)
    {

        $activity = Activite::where('associations_id', $associationId)
            ->where('type', $type)
            ->first();

        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }


    /**
     * retourner la liste des activités selon un type bien précis
     * 
     * @param $type type des activités à retourner
     */
    public static function getActivitiesByType($assocId, $type)
    {
        $activities = Activite::where('type', $type)
            ->where('associations_id', $assocId)
            ->get();

        $act = array();
        foreach ($activities as $key => $activity) {
            $data = ActiviteMethods::getSomeActivityByTypeActivityId($type, $activity->id);
            if ($data != "not found") $activity[$activity->type] = $data;
            else $activity[$activity->type] = null;

            $act[] = $activity;
        }

        if ($type == "Generique") {
            $admin = ActiviteMethods::getActivityCaisse($assocId);
            if ($admin != "not found") {
                $act[] = $admin;
            }
        }

        $success['status'] = 'OK';
        $success['data'] =  $activities;

        return $success;
    }

    public static function getActivitiesByTypeAtAgs($assocId, $type, $ags_id)
    {
        $activities = Activite::where('type', $type)
            ->where('associations_id', $assocId)
            ->get();

        foreach ($activities as $key => $activity) {
            $data = ActiviteMethods::getSomeActivityByTypeActivityIdAtAgs($type, $activity->id, $ags_id);
            if ($data != "not found") $activity[$activity->type] = $data;
            else $activity[$activity->type] = null;
        }

        $success['status'] = 'OK';
        $success['data'] =  $activities;

        return $success;
    }

    /**
     * récupération de toutes les activités avec leurs sous activités
     */
    public static function getActivitiesByAssocAndDatas($assocId)
    {
        $activities = Activite::where('associations_id', $assocId)
            ->get();

        foreach ($activities as $key => $activity) {
            $data = ActiviteMethods::getSomeActivityByTypeActivityId($activity->type, $activity->id);
            if ($data != "not found") $activity[$activity->type] = $data;
        }

        $success['status'] = 'OK';
        $success['data'] =  $activities;

        return $success;
    }

    public static function getActivitiesByAssocAndDatasAtAgs($assocId, $ags_id)
    {
        $activities = Activite::where('associations_id', $assocId)
            ->get();

        foreach ($activities as $key => $activity) {
            $data = ActiviteMethods::getSomeActivityByTypeActivityIdAtAgs($activity->type, $activity->id, $ags_id);
            if ($data != "not found") $activity[$activity->type] = $data;
            else $activity[$activity->type] = null;
        }

        $success['status'] = 'OK';
        $success['data'] =  $activities;

        return $success;
    }

    public static function getActivityByType($type)
    {
        $activity = Activite::where('type', $type)->first();

        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }


    /**
     * récupération d'une activité par son Id
     * 
     * @param $activity_id l'id de l'activité
     */
    public static function getActivityById($activity_id)
    {
        $activity = Activite::where('id', $activity_id)->first();

        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }

    public static function getActivityByCycleId($cycles_id)
    {
        $activity = Activite::where('cycles_id', $cycles_id)->get();

        return $activity;
    }

    public static function getActivityByIdAndData($activity_id)
    {
        $activity = Activite::where('id', $activity_id)->first();
        if ($activity) {
            $data = ActiviteMethods::getSomeActivityByTypeActivityId($activity->type, $activity->id);
            if ($data != "not found") $activity[$activity->type] = $data;

            return $activity;
        } else {
            return "not found";
        }
    }


    public static function getSomeActivityById($type, $type_id)
    {

        switch ($type) {
            case 'Tontine':
                $activity = Tontine::find($type_id);
                if ($activity) {
                    $lots = TontineMethods::getLots($activity->id);
                    $activity["lots"] = $lots['data'];
                }
                break;
            case 'Mutuelle':
                $activity = Mutuelle::find($type_id);
                break;
            case 'Evenement':
                $activity = Evenement::find($type_id);
                break;
            case 'Solidarite':
                $activity = Solidarite::find($type_id);
                break;
            case 'Projet':
                $activity = Projet::find($type_id);
                break;
            case 'Main_levee':
                $activity = MainsLevee::find($type_id);
                break;
            case 'Pam':
                $activity = Pam::find($type_id);
                break;
            case 'Generique':
                $activity = ActivitesGenerique::find($type_id);
                break;
                // default:
                //     $activity = "";
                //     break;
        }


        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }

    public static function getSomeActivityByTypeActivityId($type, $activite_id)
    {
        $activity = null;
        switch ($type) {
            case 'Tontine':
                $activity = Tontine::where('activites_id', $activite_id)->first();
                if ($activity) {
                    $lots = TontineMethods::getLots($activity->id);
                    $activity["lots"] = $lots['data'];
                    $data["solde"] = CompteMethods::getotalSoldeByActivityId($activite_id);
                }
                break;
            case 'Mutuelle':
                $activity = Mutuelle::where('activites_id', $activite_id)->first();
                break;
            case 'Evenement':
                $activity = Evenement::where('activites_id', $activite_id)->first();
                break;
            case 'Solidarite':
                $activity = Solidarite::where('activites_id', $activite_id)->first();
                break;
            case 'Projet':
                $activity = Projet::where('activites_id', $activite_id)->first();
                break;
            case 'Main_levee':
                $activity = MainsLevee::where('activites_id', $activite_id)->first();
                break;
            case 'Pam':
                $activity = Pam::where('activites_id', $activite_id)->first();
                break;
            case 'Generique':
                $activity = ActivitesGenerique::where('activites_id', $activite_id)->first();
                break;

                // default:
                //     $activity = "";
                //     break;
        }


        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }

    public static function getSomeActivityByTypeActivityIdAtAgs($type, $activite_id, $ags_id)
    {
        $activite = ActiviteMethods::getActivityById($activite_id);
        if ($activite == "not found") {
            return "not found";
        }
        $activity = null;
        switch ($type) {
            case 'Tontine':
                $activity = Tontine::where('activites_id', $activite_id)->first();
                if ($activity) {
                    if ($activity->type == "FIXE") {
                        $calendrier = TontineMethods::getNumberOfGainAtAg($activite->associations_id, $activity->id, $ags_id);
                        if ($calendrier['status'] == "OK-number") {
                            $activity['calendrier'] = $calendrier['data'];
                        }
                    }
                    $actual = TontineMethods::getNextLot($activity->id, $ags_id);
                    $lots = TontineMethods::getLots($activity->id);
                    $activity["lots"] = $lots['data'];
                    $activity["beneficiere"] = $actual['data'];
                    $cagnote = 0;
                    foreach ($actual['data'] as $key => $value) {
                        $cagnote += $value->montant;
                    }
                    $getCagnote = TontineMethods::getCagnote($activity)['data'];
                    ($getCagnote - $cagnote) < 0 ? $activity['cagnote'] = 0 : $activity['cagnote'] = $getCagnote - $cagnote;
                    // $activity['cagnote'] = $cagnote;
                    $data["solde"] = CompteMethods::getotalSoldeByActivityId($activite_id);
                    
                }
                break;
            case 'Mutuelle':
                $activity = Mutuelle::where('activites_id', $activite_id)->first();
                break;
            case 'Evenement':
                $activity = Evenement::where('activites_id', $activite_id)->first();
                break;
            case 'Solidarite':
                $activity = Solidarite::where('activites_id', $activite_id)->first();
                break;
            case 'Projet':
                $activity = Projet::where('activites_id', $activite_id)->first();
                break;
            case 'Main_levee':
                $activity = MainsLevee::where('activites_id', $activite_id)->first();
                break;
            case 'Pam':
                $activity = Pam::where('activites_id', $activite_id)->first();
                break;
            case 'Generique':
                $activity = ActivitesGenerique::where('activites_id', $activite_id)->first();
                break;

                // default:
                //     $activity = "";
                //     break;
        }


        if ($activity) {
            return $activity;
        } else {
            return "not found";
        }
    }

    /**
     * créer un type d'activité 
     * @param $data l'objet de données de l'activité
     * @param $activity_id l'id de l'activité
     * @param $type le type de l'activité à créer
     * 
     */
    public static function createSomeActivity($data, $activity_id, $type)
    {

        $data['activites_id'] = $activity_id;
        $activity = null;

        try {
            switch ($type) {
                case 'Tontine':
                    $activity = Tontine::create($data);
                    break;
                case 'Mutuelle':
                    $activity = Mutuelle::create($data);
                    break;
                case 'Evenement':
                    $activity = Evenement::create($data);
                    break;
                case 'Solidarite':
                    $data['date_created'] = DateMethods::getCurrentDateInt();
                    $activity = Solidarite::create($data);
                    break;
                case 'Projet':
                    $activity = Projet::create($data);
                    break;
                case 'Main_levee':
                    $activity = MainsLevee::create($data);
                    break;
                case 'Pam':
                    $activity = Pam::create($data);
                    break;
                default:
                    $activity = "";
                    break;
            }

            if ($activity == "") {
                $err['errNo'] = 15;
                $err['errMsg'] = "'{$type}' is not supported";
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }

            $success['status'] = 'OK';
            $success['data'] =  $activity;
            return $success;
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = "problems with activity type '{$type}' creation";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }


    /**
     * récupérer les données d'un type d'activité
     */
    public static function getSomeActivityData($activity_id, $type)
    {

        $activity = ActiviteMethods::getActivityById($activity_id);

        if ($activity == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "activity doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
        switch ($type) {
            case 'Tontine':
                $data = Tontine::where('activites_id', $activity_id)->first();
                if ($data) {
                    $lots = TontineMethods::getLots($data->id);
                    $data["lots"] = $lots['data'];
                    $data["solde"] = CompteMethods::getotalSoldeByActivityId($activity_id);
                }
                break;
            case 'Mutuelle':
                $data = Mutuelle::where('activites_id', $activity_id)->first();
                break;
            case 'Evenement':
                $data = Evenement::where('activites_id', $activity_id)->first();
                break;
            case 'Solidarite':
                $data = Solidarite::where('activites_id', $activity_id)->first();
                break;
            case 'Projet':
                $data = Projet::where('activites_id', $activity_id)->first();
                break;
            case 'Main_levee':
                $data = MainsLevee::where('activites_id', $activity_id)->first();
                break;
            case 'Pam':
                $data = Pam::where('activites_id', $activity_id)->first();
                break;
            case 'Generique':
                $activity = ActivitesGenerique::where('activites_id', $activity_id)->first();
                break;

            default:
                $data = "";
                break;
        }
        $comptes = CompteMethods::getByIdA($activity->id);


        $success['status'] = 'OK';
        $success['data']['activite'] =  $activity;
        $success['data'][$type] =  $data;
        $success['data']["comptes"] =  $comptes;
        return $success;
    }

    /**
     * je ne participe pas à un évènement
     */
    public static function noGoToEvent($membres_id, $activites_id)
    {
        $comptes = CompteMethods::getByIdMA($activites_id, $membres_id);

        if ($comptes != "not found") {
            $delete = CompteMethods::deleteCompte($comptes->id);
            return $delete;
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "le compte n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function changeState($activite, $state)
    {
        $activity = $activite;
        if ($activity) {
            try {
                $activity->fill([
                    "etat" => $state
                ]);
                $activity->save();
            } catch (\Exception $e) {
                $err['errNo'] = 10;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }
            $success['status'] = "OK";
            $success['data'] = $activite;

            return $success;
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * récupérer les activités par membres avec sa prochaine echeance et son compte
     */
    public static function getActivitiesByMember($assocId, $member)
    {
        $activites = ActiviteMethods::getActivitiesByAssociations($assocId);
        $activites = $activites['data'];
        $data = array();
        $ag = AgMethods::getNextAgDueDate($member);
        if ($ag != "not found") {
            foreach ($activites as $key => $activite) {
                if ($activite->etat != "cloture") {
                    $compte = CompteMethods::getByIdMA($activite->id, $member);
                    if ($compte != "not found") {
                        $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDate($member, $compte->id, $ag);
                        $decaissement = 0;
                        $cotisation = 0;
                        foreach ($echeances as $key => $echeance) {
                            if ($echeance->debit_credit != "decaissement") $cotisation += ($echeance->montant - $echeance->montant_realise ?? 0);
                            else $decaissement += ($echeance->montant - $echeance->montant_realise ?? 0);
                        }
                        $dette = ($compte->dette_c ?? 0 + $compte->dette_a ?? 0);
                        // $cotisation += $dette;

                        $data[] = array(
                            "activite" => $activite,
                            "compte" => $compte,
                            "prochaines_echeances" => $echeances,
                            "prochaine_ag" => $ag,
                            "a_payer" => $cotisation + $dette,
                            "a_retirer" => $decaissement
                        );
                    }
                }
            }
        }

        $success['status'] = "OK";
        $success['data'] = $data;

        return $success;
    }

    /**
     * suppression d'une activité par son id
     */
    public static function deleteACtiviteWithId($id)
    {
        Activite::find($id)->delete();
    }

    /**
     * suppression d'une activité par l'id du cycle
     */
    public static function deleteACtiviteWithCycleId($cycles_id)
    {
        Activite::where("cycles_id", $cycles_id)->delete();
    }


    public static function clotureGeneralActivite($activites_id, $params = null)
    {
        DB::beginTransaction();
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        if ($params) {
            try {

                if (array_key_exists('solde', $params)) {
                    $solde = ActiviteMethods::ActivitesSoldesMembres($activites_id, $params['solde']);
                    if ($solde['status'] == "NOK") {
                        DB::rollback();
                        return $solde;
                    }
                }

                if (array_key_exists('dette', $params)) {
                    $dette = ActiviteMethods::ActivitesDettesMembres($activites_id, $params['dette']);
                    if ($dette['status'] == "NOK") {
                        DB::rollback();
                        return $dette;
                    }
                }



                if (array_key_exists('avoir', $params)) {
                    $avoir = ActiviteMethods::ActivitesAvoirsMembres($activites_id, $params['avoir']);
                    if ($avoir['status'] == "NOK") {
                        DB::rollback();
                        return $avoir;
                    }
                }


                switch ($params['caisse']) {
                    case 'redistribuer':
                        $caisse = ActiviteMethods::redistributionCaisse($activites_id, "redistribution");
                        if ($caisse['status'] == "NOK") {
                            DB::rollback();
                            return $caisse;
                        }
                        break;
                    case 'virer':
                        $caisse = ActiviteMethods::virerCaisseDansAdministration($activites_id);
                        if ($caisse['status'] == "NOK") {
                            DB::rollback();
                            return $caisse;
                        }
                        break;

                    case 'mises_de_fond':
                        $caisse = ActiviteMethods::redistributionCaisse($activites_id, "mises_de_fond");
                        if ($caisse['status'] == "NOK") {
                            DB::rollback();
                            return $caisse;
                        }
                        break;

                    default:
                        # code...
                        break;
                }


                if ($params['cloturer']) {
                    $cloture = ActiviteMethods::clotureActivite($activites_id);
                    if ($cloture['status'] == "NOK") {
                        DB::rollback();
                        return $cloture;
                    }
                }
                DB::commit();
                return array(
                    'status' => "OK",
                    'data' => "successfull"
                );
            } catch (\Exception $e) {
                DB::rollback();
                $err['errNo'] = 10;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }
        } else {
            DB::rollback();
            $err['errNo'] = 14;
            $err['errMsg'] = "les paramètres de clotures sont requis";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function clotureActivite($activites_id)
    {
        DB::beginTransaction();
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        try {
            $soldes = CompteMethods::getotalSoldeByActivityId($activites_id);
            if ($soldes == 0) {

                $echeances = EcheancesMethods::getAllEcheancier($activites_id);
                foreach ($echeances['data'] as $key => $echeance1) {
                    foreach ($echeance1 as $key => $echeance) {
                        $ech = EcheancesMethods::getById($echeance->id);
                        unset($echeance['membre']);
                        $echeance->etat = "cloture";
                        if ($ech) {
                            $ech->fill((array)$echeance);
                            $ech->save();
                        }
                    }
                }

                $activite->fill(['etat' => "cloture"]);
                $activite->save();

                DB::commit();
                return array(
                    "status" => "OK",
                    "data" => "successfull"
                );
            } else {
                DB::rollback();
                return array(
                    'status' => "NOK",
                    "data" => array(
                        "errNo" => 5,
                        "errMsg" => array(
                            "soldes" => $soldes,
                            "activite" => $activite
                        )
                    )
                );
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }

    public static function operationReversement($compte)
    {
        try {
            DB::beginTransaction();
            $op = array(
                "date_realisation" => DateMethods::getCurrentDateInt(),
                "montant" => $compte->solde,
                "debit_credit" => "debit",
                "mode" => "ESPECES",
                "enregistre_par" => $compte->membres_id
            );

            $operation = OperationMethods::store('admin', (object) $op, $compte->membres_id, null);
            if ($operation['status'] == 'OK') {
                $tr = array(
                    "comptes_id" => $compte->id,
                    "montant" => $compte->solde,
                    "montant_attendu" => $compte->solde,
                    "debit_credit" => "debit"
                );
                TransactionMethods::store((object) $tr, $operation['data']['id']);
            } else {
                DB::rollback();
                return $operation;
            }
            $operation = OperationMethods::validate($operation['data']['id'], "individuel", null);

            if ($operation['status'] == 'OK') {
                DB::commit();
                event(new Finance($operation['data'], $compte->membres_id, "création d'une opération en séance", "creation d'une operation"));
                return $operation;
            } else {
                DB::rollback();
                return $operation;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }


    public static function redistributionCaisse($activites_id, $action = null)
    {
        DB::beginTransaction();
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            if ($activite->caisse > 0) {

                switch ($activite->type) {
                    case 'Tontine':
                    case 'Generique':
                        $comptes = CompteMethods::getByIdAAll($activite->id);
                        $nbNoms = 0;
                        foreach ($comptes as $key => $compte) {
                            $nbNoms += $compte->nombre_noms;
                        }
                        $montant = $activite->caisse / $nbNoms;
                        $activite->fill([
                            "caisse" => 0
                        ]);
                        $activite->save();

                        foreach ($comptes as $key => $compte) {
                            $compte->fill([
                                "solde" => $compte->solde + ($montant * $compte->nombre_noms)
                            ]);
                            $compte->save();
                        }

                        $comptes = CompteMethods::getByIdAAll($activite->id);
                        foreach ($comptes as $key => $compte) {
                            if ($compte->solde > 0) {
                                $operation = ActiviteMethods::operationReversement($compte);

                                if ($operation['status'] == "NOK") {
                                    DB::rollback();
                                    return $operation;
                                }
                            }
                        }

                        break;
                    case 'Mutuelle':
                        if ($action) {
                            switch ($action) {
                                case 'redistribution':
                                    $comptes = CompteMethods::getByIdAAll($activite->id);
                                    $interet_general = 0;
                                    foreach ($comptes as $key => $compte) {
                                        $interet_general += $compte->interet;
                                    }

                                    $pourcentage = round(($activite->caisse * 100) / $interet_general, 2);

                                    foreach ($comptes as $key => $compte) {

                                        $percu = round(($compte->interet * $pourcentage) / 100, 2);
                                        $compte->fill([
                                            "solde" => $compte->solde + $percu,
                                            "interet" => 0
                                        ]);
                                        $compte->save();
                                        if ($compte->solde > 0) {
                                            $operation = ActiviteMethods::operationReversement($compte);
                                            if ($operation['status'] == "NOK") {
                                                DB::rollback();
                                                return $operation;
                                            }
                                        }
                                    }
                                    $activite->fill([
                                        'caisse' => 0
                                    ]);
                                    $activite->save();
                                    break;

                                case 'mises_de_fond':
                                    $comptes = CompteMethods::getByIdAAll($activite->id);
                                    $interet_general = 0;

                                    foreach ($comptes as $key => $compte) {
                                        $interet_general += $compte->interet;
                                    }

                                    $pourcentage = round(($activite->caisse * 100) / $interet_general, 2);
                                    $mutuelle = MutuelleMethods::getByIdActivite($activite->id);
                                    if ($mutuelle != "not found") {
                                        foreach ($comptes as $key => $compte) {

                                            $percu = round(($compte->interet * $pourcentage) / 100, 2);
                                            if ($percu > 0) {
                                                $mise = array(
                                                    "montant" => $percu,
                                                    "date_versement" => DateMethods::getCurrentDateInt()
                                                );
                                                $mise = MutuelleMethods::storeMise($mutuelle->id, $compte->membres_id, $mise);
                                                if ($mise['status'] == "NOK") {
                                                    DB::rollback();
                                                    return $mise;
                                                }

                                                $compte->fill([
                                                    "interet" => 0
                                                ]);
                                                $compte->save();
                                            }
                                        }
                                        $activite->fill([
                                            'caisse' => 0
                                        ]);
                                        $activite->save();
                                    }
                                    break;

                                default:
                                    # code...
                                    break;
                            }
                        }
                        break;

                    default:
                        # code...
                        break;
                }
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successfull"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }

    public static function virerCaisseDansAdministration($activites_id)
    {
        DB::beginTransaction();
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        try {
            if ($activite->caisse > 0) {
                $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                if ($admin != "not found") {
                    $admin->fill(['caisse' => $admin->caisse + $activite->caisse]);
                    $admin->save();

                    $activite->fill(['caisse' => 0]);
                    $activite->save();

                    $comptes = CompteMethods::getByIdAAll($activites_id);
                    foreach ($comptes as $key => $compte) {
                        $compte->fill([
                            "interet" => 0
                        ]);
                        $compte->save();
                    }
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successfull"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }


    public static function ActivitesSoldesMembres($activites_id, $action)
    {
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        DB::beginTransaction();
        try {
            $comptes = CompteMethods::getByIdAAll($activites_id);
            switch ($action) {
                case 'reverser':
                    foreach ($comptes as $key => $compte) {
                        if ($compte->solde > 0) {
                            $operation = ActiviteMethods::operationReversement($compte);
                            if ($operation['status'] == "NOK") {
                                DB::rollback();
                                return $operation;
                            }
                        }
                    }
                    break;

                case 'virer_caisse':
                    $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                    $montant = 0;
                    if ($admin != "not found")
                        foreach ($comptes as $key => $compte) {
                            $montant += $compte->solde;
                            $compte->fill(["solde" => 0, "solde_anterieur" => 0]);
                            $compte->save();
                        }

                    $admin->fill([
                        "caisse" => $admin->caisse + $montant
                    ]);
                    $admin->save();

                    break;

                case 'virer_solde_administration':
                    $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                    $montant = 0;
                    if ($admin != "not found") {
                        $compteAds = CompteMethods::getByIdAAll($admin->id);
                        foreach ($comptes as $key => $compte) {
                            foreach ($compteAds as $keyAds => $value) {

                                $search = array_search($compte->membres_id, array_column((array) $value, 'membres_id'));
                                if ($search) break;
                            }
                            if ($search) {

                                $compteAds[$keyAds]->fill([
                                    "solde" => $compteAds[$keyAds]->solde + $compte->solde
                                ]);
                                $compteAds[$keyAds]->save();

                                $compte->fill(["solde" => 0, "solde_anterieur" => 0]);
                                $compte->save();
                            }
                        }
                    }

                    break;
                default:
                    # code...
                    break;
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successful"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }


    public static function ActivitesDettesMembres($activites_id, $action)
    {
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        DB::beginTransaction();
        try {
            $comptes = CompteMethods::getByIdAAll($activites_id);
            switch ($action) {
                case 'perte_profit':
                    $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                    if ($admin != "not found") {
                        // $compteAds = CompteMethods::getByIdAAll($admin->id);
                        foreach ($comptes as $key => $compte) {

                            $compte->fill([
                                "dette_c" => $compte->dette_c - min($compte->dette_c, $admin->caisse)
                            ]);
                            $compte->save();


                            $admin->fill([
                                "caisse" => $admin->caisse - min($compte->dette_c, $admin->caisse)
                            ]);
                            $admin->save();

                            $compte->fill([
                                "dette_a" => $compte->dette_a - min($compte->dette_a, $admin->caisse)
                            ]);
                            $compte->save();


                            $admin->fill([
                                "caisse" => $admin->caisse - min($compte->dette_a, $admin->caisse)
                            ]);
                            $admin->save();


                            // $search = array_search($compte->membres_id, array_column((array) $compteAds, 'membres_id'));
                            // if ($search) {

                            //     $compteAds[$keyAds]->fill([
                            //         "dette_c" => $compteAds[$keyAds]->dette_c + $compte->dette_c,
                            //         "dette_a" => $compteAds[$keyAds]->dette_a + $compte->dette_a,
                            //     ]);
                            //     $compteAds[$keyAds]->save();

                            //     $compte->fill(["dette_c" => 0, "dette_a" => 0]);
                            //     $compte->save();
                            // }
                        }
                    }

                    break;

                case 'virer_dette_administration':
                    $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                    if ($admin != "not found") {
                        $compteAds = CompteMethods::getByIdAAll($admin->id);
                        foreach ($comptes as $key => $compte) {
                            foreach ($compteAds as $keyAds => $value) {

                                $search = array_search($compte->membres_id, array_column((array) $value, 'membres_id'));
                                if ($search) break;
                            }

                            if ($search) {

                                $compteAds[$keyAds]->fill([
                                    "dette_a" => $compteAds[$keyAds]->dette_a + $compte->dette_a,
                                    "dette_c" => $compteAds[$keyAds]->dette_c + $compte->dette_c,
                                ]);
                                $compteAds[$keyAds]->save();

                                $compte->fill(["dette_a" => 0, "dette_c" => 0]);
                                $compte->save();
                            }
                        }
                    }

                    break;
                default:
                    # code...
                    break;
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successfull"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }

    public static function ActivitesAvoirsMembres($activites_id, $action)
    {
        $activite = ActiviteMethods::getActivityById($activites_id);
        if ($activite == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = "l'activite n'existe pas";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        DB::beginTransaction();
        try {
            $comptes = CompteMethods::getByIdAAll($activites_id);
            switch ($action) {
                case 'payer':
                    foreach ($comptes as $key => $compte) {
                        if ($compte->avoir > 0) {
                            $compte->fill(["solde" => $compte->solde + $compte->avoir]);
                            $compte->save();
                        }
                    }
                    $comptes = CompteMethods::getByIdAAll($activites_id);
                    foreach ($comptes as $key => $compte) {
                        $operation = ActiviteMethods::operationReversement($compte);
                        if ($operation['status'] == "NOK") {
                            DB::rollback();
                            return $operation;
                        }

                        $compte->fill(["avoir" => 0]);
                        $compte->save();
                    }

                    break;

                case 'virer_avoir_administration':
                    $admin = ActiviteMethods::getActivityCaisse($activite->associations_id);
                    $montant = 0;
                    if ($admin != "not found") {
                        $compteAds = CompteMethods::getByIdAAll($admin->id);
                        foreach ($comptes as $key => $compte) {
                            foreach ($compteAds as $keyAds => $value) {

                                $search = array_search($compte->membres_id, array_column((array) $value, 'membres_id'));
                                if ($search) break;
                            }

                            if ($search) {

                                $compteAds[$keyAds]->fill([
                                    "avoir" => $compteAds[$keyAds]->avoir + $compte->avoir
                                ]);
                                $compteAds[$keyAds]->save();

                                $compte->fill(["avoir" => 0, "solde_anterieur" => 0]);
                                $compte->save();
                            }
                        }
                    }

                    break;
                default:
                    # code...
                    break;
            }
            DB::commit();
            return array(
                "status" => "OK",
                "data" => "successfull"
            );
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }
}
