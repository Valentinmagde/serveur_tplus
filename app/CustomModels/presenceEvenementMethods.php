<?php


namespace App\CustomModels;


use App\Models\PresenceEvenement;

class PresenceEvenementMethods{

    public static function getByEvenementAndMember($evt, $member){
        $presence = PresenceEvenement::where('evenements_id', $evt)
                            ->where('membres_id', $member)
                            ->first();
        if($presence){
            $membre = MembreMethods::getById($member);
            if($membre != "not found") $presence['membre'] = $membre->firstName.' '.$membre->lastName; 
            return $presence;
        }
        else return "not found";
    }


    public static function checkAssociationPresenceAll($assocId, $evt_id){
        $membres = MembreMethods::getByAssociationId($assocId);
        $evt = ActiviteMethods::getSomeActivityById("Evenement", $evt_id);
        if($membres != "not found"){
            if($evt != "not found"){
                $presences = array();
                foreach ($membres as $key => $membre) {
                    $presence = PresenceEvenementMethods::getByEvenementAndMember($evt_id, $membre->id);
                    if($presence != "not found") $presences[] = $presence;
                }
                $success['status'] = "OK";
                $success['data'] = $presences;

                return $success;
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = "ag {$evt_id} doesn't exist";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "members doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function checkPresence($assocId, $membre_id, $evt_id){
        $membre = MembreMethods::hasAssociationAndGet($membre_id, $assocId);
        $evt = ActiviteMethods::getSomeActivityById("Evenement", $evt_id);

        if($membre != "not found"){
            if($evt != "not found"){
                $presence =PresenceEvenementMethods::getByEvenementAndMember($evt_id, $membre_id);
                if($presence != "not found"){
                    $success['status'] = "OK";
                    $success['data'] = $presence;

                    return $success;
                }else{
                    $err['errNo'] = 15;
                    $err['errMsg'] = "status not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = "ag {$evt_id} doesn't exist";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "member {$membre_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function addPresence($membre_id, $evt_id, $status, $raison = ""){
        $membre = MembreMethods::getById($membre_id);
        $evt = ActiviteMethods::getSomeActivityById("Evenement", $evt_id);

        if($membre != "not found"){
            if($evt != "not found"){

                try {
                    $check = PresenceEvenement::where('membres_id', $membre_id)
                                                ->where('evenements_id', $evt_id)
                                                ->first();
                    if($check){
                        $presence = PresenceEvenement::where('membres_id', $membre_id)
                                                        ->where('evenements_id', $evt_id)
                                                        ->update([
                                                            "status" => $status,
                                                            "raison" => $raison
                                                        ]);
                    }else{
                        $presence = PresenceEvenement::create([
                            "membres_id" => $membre_id,
                            "evenements_id" => $evt_id,
                            "status" => $status,
                            "raison" => $raison
                        ]);
                    }

                    $success['status'] = "OK";
                    $success['data'] = $presence;

                    return $success;

                } catch (\Exception $e) {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;  
                }

            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = "ag {$evt_id} doesn't exist";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "member {$membre_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function updatePresence($membre_id, $evt_id, $status){
        $presence = PresenceEvenementMethods::getByEvenementAndMember($evt_id, $membre_id);
        if($presence != "not found"){
            $presence->fill([
                "status" => $status
            ]);
            $presence->save();

            $success['status'] = "OK";
            $success['data'] = $presence;

            return $success;
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = "presence doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function checkPresenceMemberEvt($assocId, $membre_id){
        $cycle = CycleMethods::checkActifCycle($assocId);
        if($cycle == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "active cycle doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $evts = ActiviteMethods::getActivitiesByType($assocId, "Evenement");
        
        $member = MembreMethods::getById($membre_id);
        if($member == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "member {$membre_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $presences = array();
        foreach ($evts['data'] as $key => $evt) {
            $presence = PresenceEvenementMethods::checkPresence($assocId, $membre_id, $evt->id);
            if($presence['status'] == "OK") $presences[] = $presence['data'];
        }

        $success['status'] = "OK";
        $success['data'] = $presences;

        return $success;
    }
}