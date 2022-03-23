<?php


namespace App\CustomModels;


use App\Models\Ag;
use App\Models\Utilisateur;
use App\Models\MembresHasUser;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\VarDumper;

class AgMethods
{

    /**
     * update tout les ags avec une date en UTC
     */
    public static function updateToUTCTimeAllDateAg()
    {
        DB::beginTransaction();
        $all = Ag::all();
        $ags = array();
        try {
            foreach ($all as $key => $ag) {

                $cycle = CycleMethods::getById($ag->cycles_id);
                if ($cycle != "not found") {
                    $association = AssociationMethods::getById($cycle->associations_id);
                    if ($association != "not found") {
                        $offset = (int) $association->fuseau_horaire * 3600;
                        $datetime = $ag->date_ag - $offset;
                        $ag->fill([
                            "date_ag" => $datetime
                        ]);
                        $ag->save();
                        $ags[] = $ag;
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = "Association {$cycle->associations_id} doesn't exist";
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return $error;
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = "cycle {$cycle->id} doesn't exist";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $ags
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

    /**
     * update la date de cloture de l'ag pour mettre au même niveau que celle de l'ag
     */
    public static function updateDateClotureAg()
    {
        DB::beginTransaction();
        $all = Ag::all();
        $ags = array();
        try {
            foreach ($all as $key => $ag) {
                $ag->fill([
                    "date_cloture" => $ag->date_ag
                ]);
                $ag->save();
                $ags[] = $ag;
            }

            DB::commit();
            return array(
                "status" => "OK",
                "data" => $ags
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


    public static function getDueFirstBillDate($cycle)
    {
        $ags = Ag::where('cycles_id', $cycle)
                    ->where('etat', '!=', 'past')
                    ->orderBy('id', 'asc')
                    ->offset(2)
                    ->first();

        if ($ags) return $ags->date_ag;
        else{
            $lastAg = AgMethods::getLatestAg($cycle);  
            return $lastAg->date_ag; 
        }
    }

    
    public static function getDueSecondBillDate($ag)
    {
        $ags = Ag::where('id', $ag)
                ->orderBy('id', 'asc')
                ->offset(2)
                ->first();

        if ($ags) return $ags->date_ag;
        else{
            $ag = Ag::where('id', $ag)->first();
            $lastAg = AgMethods::getLatestAg($ag->cycles_id);  
            return $lastAg->date_ag; 
        }
    }

    public static function countNumberOfAgLessThan($cycle_id, $ag_date)
    {
        $ags = Ag::where('cycles_id', $cycle_id)
            ->where('date_ag', '<', $ag_date)
            ->get();
        return count($ags);
    }

    public static function getNextAgDueDate($member_id)
    {
        $member = MembreMethods::getById($member_id);

        if ($member != "not found") {
            $cycle = CycleMethods::checkActifCycle($member->associations_id);
            if ($cycle != "not found") {
                $nextAg = AgMethods::getNextAg($cycle->id);
                if ($nextAg != "not found") {
                    return $nextAg->date_ag;
                }
            }
            return "not found";
        } else {
            return "not found";
        }
    }

    public static function getNextAgMemberUntilDate($member_id, $date)
    {
        $member = MembreMethods::getById($member_id);

        if ($member != "not found") {
            $cycle = CycleMethods::checkActifCycle($member->associations_id);
            if ($cycle != "not found") {
                $nextAg = AgMethods::getAllNextAgsUntil($cycle->id, $date);

                return $nextAg;
            }
        }
        return "not found";
    }

    public static function getNextAgMemberNumber($member_id, $date_debut, $number)
    {
        $member = MembreMethods::getById($member_id);

        if ($member != "not found") {
            $cycle = CycleMethods::checkActifCycle($member->associations_id);
            if ($cycle != "not found") {
                $nextAg = AgMethods::getAllNextAgsNumber($cycle->id, $date_debut,  $number);

                return $nextAg;
            }
        }
        return "not found";
    }


    public static function getCurrentAg($member_id)
    {
        $member = MembreMethods::getById($member_id);

        if ($member != "not found") {
            $cycle = CycleMethods::checkActifCycle($member->associations_id);
            // dd($cycle->id);
            if ($cycle != "not found") {
                $nextAg = Ag::where('etat', 'current')->where('cycles_id', $cycle->id)->first();
                if ($nextAg)
                    return $nextAg;
            }
        }
        return "not found";
    }

    public static function getCurrentCycle($cycle_id)
    {

        $nextAg = Ag::where('etat', 'current')->where('cycles_id', $cycle_id)->first();
        if ($nextAg)
            return $nextAg;

        return "not found";
    }

    public static function getNextAg($cycle)
    {
        $ags = Ag::where("cycles_id", $cycle)
            ->where('etat', 'current')
            ->first();

        // foreach ($ags as $key => $ag) {

        //     $daga = gmdate("Y-m-d", $ag->date_ag);
        //     $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt());

        //     if($daga >= $da){
        //         return $ag;
        //     }
        // }
        if ($ags) {
            return $ags;
        }
        return "not found";
    }

    public static function getProchainAg($cycle)
    {
        $ags = Ag::where("cycles_id", $cycle)
            ->get();

        foreach ($ags as $key => $ag) {

            $daga = gmdate("Y-m-d", $ag->date_ag);
            $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt());

            if ($daga > $da) {
                return $ag;
            }
        }
        return "not found";
    }





    public static function getAllNextAgs($cycle)
    {
        $ag = Ag::where("cycles_id", $cycle)
            ->where('date_ag', '>=', DateMethods::getCurrentDateInt())
            ->get();

        if ($ag) {
            return $ag;
        } else {
            return "not found";
        }
    }

    public static function getAllNextAgsAfterCurrent($cycle, $ag)
    {
        $ag = Ag::where("cycles_id", $cycle)
            ->where('date_ag', '>', $ag->date_ag)
            ->get();

        return $ag;
    }

    public static function getAllNextAgsUntil($cycle, $date)
    {
        $all = Ag::where("cycles_id", $cycle)
            ->where('date_ag', '>=', DateMethods::getCurrentDateInt())
            ->where('date_ag', '<=', $date)
            ->get();

        return $all;
    }

    public static function checkAgDateExist($cycle, $date)
    {
        
        $all = Ag::where("cycles_id", $cycle)
            ->where('date_ag', $date)
            ->first();
        if($all){
           return true;
        }
        return false;
    }

    public static function getAllNextAgsNumber($cycle, $date_debut, $number)
    {


        $all = Ag::where("cycles_id", $cycle)
            ->where('date_ag', '>', $date_debut)
            ->get();

        if (count($all) < $number) {
            return $all;
        }


        $ags = array();
        $i = $number;
        foreach ($all as $key => $ag) {
            if ($ag->date_ag > $date_debut && $i > 0) {
                $ags[] = $ag;
                $i--;
            }
        }

        return $ags;
    }

    public static function getAllNextAgsNumberIncludeDateDebut($cycle, $date_debut, $number)
    {

        $all = Ag::where("cycles_id", $cycle)
            ->get();

        $ags = array();
        $i = $number;
        foreach ($all as $key => $ag) {
            if ($ag->date_ag >= $date_debut && $i > 0) {
                $ags[] = array(
                    "key" => $key + 1,
                    "ag" => $ag
                );
                $i--;
            }
        }

        return $ags;
    }

    public static function getAllNextAgsIncludeDateDebutUntilDateFin($cycle, $date_debut, $date_fin)
    {

        $all = Ag::where("cycles_id", $cycle)
                    ->where('date_ag','>=',$date_debut)
                    ->where('date_ag','<=', $date_fin)
                    ->get();

        return $all;
    }


    public static function getLatestAg($cycle)
    {
        $ag = Ag::latest('date_ag')->where('cycles_id', $cycle)
            ->first();

        if ($ag) {
            return $ag;
        } else {
            return "not found";
        }
    }

    public static function getFirstAg($cycle)
    {
        $ag = Ag::where('cycles_id', $cycle)
            ->first();

        if ($ag) {
            return $ag;
        } else {
            return "not found";
        }
    }

    public static function getFirstAgNonCloture($cycle)
    {
        $ag = Ag::where('cycles_id', $cycle)
                    ->where('etat', '!=', 'past')
                    ->orderBy('id', 'asc')
                    ->first();
       if($ag){
           return $ag;
       }else{
           return "not found";
       }
    }

    public static function getCycleEffectiveLength($cycle){
        $ag = Ag::where('cycles_id', $cycle)
                ->where('etat', '!=', 'past')
                ->count();
        return $ag;
    }

    public static function getNextAgByAg($ag, $cycles_id)
    {
        $ag = Ag::where('id', '>', $ag->id)
            ->where('cycles_id', $cycles_id)
            ->first();

        if ($ag) {
            return $ag;
        } else {
            return "not found";
        }
    }

    public static function getPreviousAg($ag)
    {

        $cycle = CycleMethods::getById($ag->cycles_id);
        if ($cycle != "not found") {
            $ags = Ag::where('cycles_id', $cycle->id)
                ->where('id', '<', $ag->id)
                ->orderby('id', 'desc')
                ->first();

            if ($ags)
                return $ags;
        }

        return "not found";
    }

    public static function getPreviousAgInCycle($ag, $cycle)
    {
        $ags = Ag::where('id', '<', $ag->id)
            ->where('cycles_id', $cycle)
            ->orderby('id', 'desc')
            ->first();
        if ($ags) {
            return $ags;
        } else {
            return "not found";
        }
    }

    /**
     * function pour la récupération d'une association à partir de son ID
     * @param $id qui est l'id de l'association
     * 
     * 
     */
    public static function getById($id)
    {

        $assoc = Ag::where('id', $id)->first();

        if ($assoc) {
            return $assoc;
        } else {
            return "not found";
        }
    }

    public static function getByIdCycle($id)
    {

        $ags = Ag::where('cycles_id', $id)->get();

        foreach ($ags as $key => $value) {
            $member = MembreMethods::getById($value->membres_id);
            if ($member != "not found") {
                $value['membre'] = $member->firstName . ' ' . $member->lastName;
            }
        }
        return $ags;
    }


    /**
     * supprimer un ag avec l'id du cycle
     */
    public static function deleteAgWithCycleId($cycle)
    {
        Ag::where('cycles_id', $cycle)->delete();
    }


    /**
     * function qui permettra d'avoir un ags d'un cycle
     */
    public static function getCycleAssociationAgs($request)
    {
        $association = AssociationMethods::getById($request->assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            $cycle = CycleMethods::getById($request->cycleId);

            if ($cycle == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                $ags = AgMethods::getByIdCycle($request->cycleId);
                if ($ags == "not found") {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Ags doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);
                } else {
                    $success['status'] = 'OK';
                    $success['data'] = $ags;
                    return response()->json($success, 200);
                }
            }
        }
    }

    /**
     * function pour récupérer un ag par son id et le retourner au client
     */
    public static function getAgById($request)
    {

        $association = AssociationMethods::getById($request->assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            $cycle = CycleMethods::getById($request->cycleId);

            if ($cycle == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                $ags = AgMethods::getById($request->cycleId);
                if ($ags == "not found") {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Ags doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);
                } else {
                    foreach ($ags as $key => $value) {
                        $member = MembreMethods::getById($value->membres_id);
                        if ($member != "not found") {
                            $value['membre'] = $member->firstName . ' ' . $member->lastName;
                        }
                    }
                    $success['status'] = 'OK';
                    $success['data'] = $ags;
                    return response()->json($success, 200);
                }
            }
        }
    }

    /**
     * récupérer le lieu d'une AG
     */
    public static function getLieuAg($membre)
    {
        $user = MembresHasUser::where('membres_id', $membre->id)->first();
        if ($user) {
            $us = Utilisateur::where('id', $user->utilisateurs_id)->first();
            if ($us && $us->adresse != null)
                return $us->adresse;
        } else if ($membre->adresse != null) {
            return $membre->adresse;
        }
    }

    /**
     * création d'un Ag
     */
    public static function createAg($request)
    {

        DB::beginTransaction();
        $association = AssociationMethods::getById($request->assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            $cycle = CycleMethods::getById($request->cycleId);

            if ($cycle == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Cycle doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                try {
                    $input = $request->all();

                    $input['cycles_id'] = $request->cycleId;
                    $input['create_at'] = strtotime(Carbon::now());

                    //Jours de la semaine
                    $semaine = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU', 'SU,MO,TU,WE,TH,FR,SA'];
                    $ordre = ['first', 'second', 'third', 'fourth', 'fifth', 'last',];

                    $jour_semaine = $cycle->jour_semaine; //Jour de la semaine
                    $jour_mois = $cycle->jour_mois; //Jour du mois
                    $ordre_semaine = $cycle->ordre_semaine; //numro d'ordre de la semaine
                    $frequence_seance = $cycle->frequence_seance; //Frequence seance

                    //date de départ
                    $dateDepart = $cycle->date_premiere_assemblee;
                    //durée à rajouter;
                    $duree = $cycle->duree_cycle;

                    //on calcule la date de fin
                    $dateFin = date('d-m-Y H:i:s', strtotime('+' . $duree . ' month', $dateDepart));
                    $dateDepart = date('d-m-Y H:i:s', $dateDepart);

                    $proprietes = $association;

                    // $dateStart = date("Y-m-d H:i",$proprietes->dateDebutCycle);
                    $dstart = new DateTime($dateDepart, new DateTimeZone('UTC'));
                    $until = new DateTime($dateDepart, new DateTimeZone('UTC'));
                    $dstart->setTimeZone(new DateTimeZone('+0'));
                    $until->setTimeZone(new DateTimeZone('+0'));
                    $offset = 'P' . $duree . 'M';
                    $until->add(new \DateInterval($offset));
                    $dfin = $until->format('d-m-Y H:i:s');
                    $ags = [];
                    $interval = "";
                    switch ($frequence_seance) {
                        case '1-MONTHLY':
                            $interval = 1;
                            break;
                        case '2-MONTHLY':
                            $interval = 2;
                            break;
                        case '3-MONTHLY':
                            $interval = 3;
                            break;
                        case '4-MONTHLY':
                            $interval = 4;
                            break;
                        case '5-MONTHLY':
                            $interval = 5;
                            break;
                        case '6-MONTHLY':
                            $interval = 6;
                            break;
                        case '1-WEEKLY':
                            $interval = 1;
                            break;
                        case '2-WEEKLY':
                            $interval = 2;
                            break;

                        default:
                            break;
                    }
                    $members = MembreMethods::getMemberAssociationActif($request->assocId);
                    $array = array();
                    $otherArray = array();
                    foreach ($members as $key => $value) {
                        $array[] = $value;
                    }
                    if ($ordre_semaine > 0 || $ordre_semaine == -1) {
                        $jour_semaine -= 1;
                        if ($ordre_semaine == -1 && $jour_semaine == 7) {
                            $byday = $semaine[$jour_semaine];
                            $rule = new \Recurr\Rule("FREQ=MONTHLY;BYMONTHDAY=-1;INTERVAL=$interval;COUNT=$duree", $dstart);
                        } else {
                            $byday = $ordre_semaine . $semaine[$jour_semaine];
                            $rule = new \Recurr\Rule("FREQ=MONTHLY;INTERVAL=$interval;BYDAY=$byday;COUNT=$duree",  $dstart);
                        }
                        // dd($byday);
                        $transformer = new \Recurr\Transformer\ArrayTransformer();
                        $constraint = new \Recurr\Transformer\Constraint\BeforeConstraint(new \DateTime($dfin), $inc = true);
                        $donnees = $transformer->transform($rule, $constraint);

                        $nxt = 0;
                        foreach ($donnees as $dt) {
                            $getdate = strtotime($dt->getStart()->format('Y-m-d H:i:s'));
                            $input['date_ag'] = $getdate;
                            $input['date_cloture'] = $getdate;


                            $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt());
                            $daga = gmdate("Y-m-d", $getdate);

                            if (strtotime($daga) < strtotime($da)) $input['etat'] = "past";
                            else if (strtotime($daga) >= strtotime($da) && $nxt == 0) {
                                $input['etat'] = "current";
                                $nxt = 1;
                            } else if (strtotime($daga) > strtotime($da)) $input['etat'] = "future";

                            if (strtotime($daga) >= strtotime($da)) {
                                if (count($array) == 0) $array = $otherArray;
                                $ind = array_rand($array);
                                $input['membres_id'] = $array[$ind]->id;
                                if ($cycle->type_assemblee == "physique") {
                                    $input['lieu_ag'] = AgMethods::getLieuAg($array[$ind]);
                                } else if ($cycle->type_assemblee == "fixe") {
                                    $input['lieu_ag'] = $cycle->lieu_fixe_ag;
                                } else {
                                    $input['lieu_ag'] = "En ligne";
                                }
                                $otherArray[] = $array[$ind];
                                unset($array[$ind]);
                            }
                            //Creation d'une ag
                            $ags[] = Ag::create($input);
                        }
                    } else if ($jour_mois > 0) {
                        $rule        = new \Recurr\Rule("FREQ=MONTHLY;INTERVAL=$interval;BYMONTHDAY=$jour_mois;COUNT=$duree",  $dstart);
                        $transformer = new \Recurr\Transformer\ArrayTransformer();
                        $constraint = new \Recurr\Transformer\Constraint\BeforeConstraint(new \DateTime($dfin), $inc = true);
                        $donnees = $transformer->transform($rule, $constraint);
                        $nxt = 0;
                        foreach ($donnees as $dt) {
                            $getdate = strtotime($dt->getStart()->format('Y-m-d H:i:s'));
                            $input['date_ag'] = $getdate;
                            $input['date_cloture'] = $getdate;

                            $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt());
                            $daga = gmdate("Y-m-d", $getdate);

                            if (strtotime($daga) < strtotime($da)) $input['etat'] = "past";
                            else if (strtotime($daga) >= strtotime($da) && $nxt == 0) {
                                $input['etat'] = "current";
                                $nxt = 1;
                            } else if (strtotime($daga) > strtotime($da)) $input['etat'] = "future";

                            if (strtotime($daga) >= strtotime($da)) {
                                if (count($array) == 0) $array = $otherArray;
                                $ind = array_rand($array);
                                $input['membres_id'] = $array[$ind]->id;
                                if ($cycle->type_assemblee == "physique") {
                                    $input['lieu_ag'] = AgMethods::getLieuAg($array[$ind]);
                                } else if ($cycle->type_assemblee == "fixe") {
                                    $input['lieu_ag'] = $cycle->lieu_fixe_ag;
                                } else {
                                    $input['lieu_ag'] = "En ligne";
                                }

                                $otherArray[] = $array[$ind];
                                unset($array[$ind]);
                            }

                            //Creation d'une ag
                            $ags[] = Ag::create($input);
                        }
                    } else if ($jour_semaine > 0) {
                        $jour_semaine -= 1;
                        $byday = $semaine[$jour_semaine];

                        $rule = new \Recurr\Rule("FREQ=WEEkLY;INTERVAL=$interval;BYDAY=$byday",  $dstart);
                        $transformer = new \Recurr\Transformer\ArrayTransformer();
                        $constraint = new \Recurr\Transformer\Constraint\BeforeConstraint(new \DateTime($dfin), $inc = true);
                        $donnees = $transformer->transform($rule, $constraint);

                        $nxt = 0;
                        foreach ($donnees as $dt) {
                            $getdate = strtotime($dt->getStart()->format('Y-m-d H:i:s'));


                            $input['date_ag'] = $getdate;
                            $input['date_cloture'] = $getdate;

                            $da = gmdate("Y-m-d", DateMethods::getCurrentDateInt());
                            $daga = gmdate("Y-m-d", $getdate);

                            if (strtotime($daga) < strtotime($da)) $input['etat'] = "past";
                            else if (strtotime($daga) >= strtotime($da) && $nxt == 0) {
                                $input['etat'] = "current";
                                $nxt = 1;
                            } else if (strtotime($daga) > strtotime($da)) $input['etat'] = "future";

                            if (strtotime($daga) >= strtotime($da)) {
                                if (count($array) == 0) $array = $otherArray;
                                $ind = array_rand($array);
                                $input['membres_id'] = $array[$ind]->id;
                                if ($cycle->type_assemblee == "physique") {
                                    $input['lieu_ag'] = AgMethods::getLieuAg($array[$ind]);
                                } else if ($cycle->type_assemblee == "fixe") {
                                    $input['lieu_ag'] = $cycle->lieu_fixe_ag;
                                } else {
                                    $input['lieu_ag'] = "En ligne";
                                }
                                $otherArray[] = $array[$ind];
                                unset($array[$ind]);
                            }
                            //Creation d'une ag
                            $ags[] = Ag::create($input);
                        }
                    }


                    $creditEcheances = EcheancesMethods::setEcheancesCreditWithNoDate($cycle->associations_id, $cycle->id);
                    if ($creditEcheances['status'] == "NOK") {
                        DB::rollback();
                        $err['errNo'] = $creditEcheances['data']['errNo'];
                        $err['errMsg'] = $creditEcheances['data']['errMsg'];
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500);
                    }

                    DB::commit();
                    $success['status'] = 'OK';
                    $success['data'] = $ags;
                    return response()->json($success, 201);
                } catch (\Exception $e) {
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
            }
        }
    }


    /**
     * Mettre à jour une Ag
     */
    public static function updateAg($request)
    {

        try {
            $association = AssociationMethods::getById($request->assocId);

            if ($association == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                $cycle = CycleMethods::getById($request->cycleId);

                if ($cycle == "not found") {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Cycle doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);
                } else {
                    $ags = AgMethods::getById($request->id);
                    if ($ags == "not found") {
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'Ags doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 404);
                    } else {
                        //Mise à jours des données de l'ag
                        $ags->fill([
                            'date_ag' => $request->date_ag,
                            'lieu_ag' => $request->lieu_ag
                        ]);
                        //formatage de message de confirmation
                        $ags->save();
                        $success['status'] = 'OK';
                        $success['data'] =  $ags;

                        return response()->json($success, 200);
                    }
                }
            }
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * changer l'hote d'une ag
     */
    public static function changeHote($ag_id, $member_id)
    {
        $ag = AgMethods::getById($ag_id);
        if ($ag != "not found") {
            $member = MembreMethods::getById($member_id);
            if ($member != "not found") {
                try {
                    $ag->fill([
                        "membres_id" => $member_id
                    ]);
                    $ag->save();
                    $success["status"] = "OK";
                    $success["data"] = $ag;

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
                $err['errMsg'] = 'Member doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {

            $err['errNo'] = 15;
            $err['errMsg'] = 'Ag doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * permutter deux ags
     */
    public static function permuterAg($ag1_id, $ag2_id)
    {
        $ag1 = AgMethods::getById($ag1_id);
        $ag2 = AgMethods::getById($ag2_id);

        if ($ag1 != "not found" && $ag2 != "not found") {
            try {
                $ag = $ag1->membres_id;

                $ag1->fill([
                    "membres_id" => $ag2->membres_id
                ]);
                $ag1->save();


                $ag2->fill([
                    "membres_id" => $ag
                ]);
                $ag2->save();

                $success['status'] = "OK";
                $success['data'] = "successfull";

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
            $err['errMsg'] = 'one of AGs doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function getAgByCycleId($cycle_id)
    {
        $ags = Ag::where('cycles_id', $cycle_id)
            ->get();
        foreach ($ags as $key => $value) {
            $member = MembreMethods::getById($value->membres_id);
            if ($member != "not found") {
                $value['membre'] = $member->firstName . ' ' . $member->lastName;
            }
        }
        $success['status'] = 'OK';
        $success['data'] = $ags;

        return $success;
    }

    //cloture d'ag
    public static function clotureAg($ag_id)
    {
        DB::beginTransaction();
        try {
            $ag = AgMethods::getById($ag_id);
            if ($ag != "not found") {

                if ($ag->etat == "cloture") {
                    $err['errNo'] = 12;
                    $err['errMsg'] = "this general assembly is already closed";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                $cycle = CycleMethods::getById($ag->cycles_id);
                if ($cycle == "not found") {
                    DB::rollback();
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'cycle doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $first = AgMethods::getFirstAgNonCloture($ag->cycles_id);
                if ($first != "not found") {
                    if ($ag->id == $first->id) {
                        $cycle = CycleMethods::activateCycle($ag->cycles_id);
                        if ($cycle['status'] == "NOK") {
                            DB::rollback();
                            $err['errNo'] = $cycle['data']['errNo'];
                            $err['errMsg'] = $cycle['data']['errMsg'];
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                        $facture = FactureMethods::create($ag->cycles_id);
                        if ($facture['status'] == "NOK") {
                            DB::rollback();
                            $err['errNo'] = $facture['data']['errNo'];
                            $err['errMsg'] = $facture['data']['errMsg'];
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    }
                }

                $previousAg = AgMethods::getPreviousAg($ag);
                if($previousAg != "not found"){
                    $newMembers = MembreMethods::getCountMemberAdded($previousAg);
                    if($newMembers > 0){
                        $factureOther = FactureMethods::createOther($ag->cycles_id, $ag_id, $newMembers);
                        if ($factureOther['status'] == "NOK") {
                            DB::rollback();
                            $err['errNo'] = $factureOther['data']['errNo'];
                            $err['errMsg'] = $factureOther['data']['errMsg'];
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return $error;
                        }
                    }
                }

                $cycle = CycleMethods::getById($ag->cycles_id);
                if ($cycle != "not found") {
                    SanctionMethods::createSanctionFermetureAg($cycle->associations_id, $ag_id);
                }

                $checkBill = FactureMethods::getByCycleIdNotDue($cycle->id);
                if(count($checkBill) != 0){
                    foreach ($checkBill as $key => $value) {
                        if($value->date_limite >= $ag->date_ag ) {
                            AssociationMethods::changeStateAsociation($cycle->associations_id);
                            break;
                        }
                    }
                   
                }

                $cloture = EcheancesMethods::cloturerEcheances($cycle->associations_id, $ag_id);
                if ($cloture['status'] == "NOK") {

                    DB::rollback();
                    $err['errNo'] = $cloture['data']['errNo'];
                    $err['errMsg'] = $cloture['data']['errMsg'];
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }


                $majs = CompteMethods::MAJSoldes($ag->cycles_id);
                if ($majs['status'] == "NOK") {

                    DB::rollback();
                    $err['errNo'] = $majs['data']['errNo'];
                    $err['errMsg'] = $majs['data']['errMsg'];
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }

                $ag->fill([
                    'etat' => 'cloture',
                    'date_cloture' => DateMethods::getCurrentDateInt()
                ]);
                $ag->save();

                $next = AgMethods::getNextAgByAg($ag, $ag->cycles_id);
                if ($next != "not found") {
                    $next->fill(['etat' => 'current']);
                    $next->save();
                }

                $ag['facture'] = $facture ?? null;
                $ag['facture_other'] = $factureOther ?? null;
                DB::commit();
                return array(
                    "status" => "OK",
                    "data" => $ag
                );
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'Ag doesn\'t exist';
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
}
