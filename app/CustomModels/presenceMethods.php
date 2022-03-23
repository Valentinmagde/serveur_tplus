<?php


namespace App\CustomModels;


use App\Models\Presence;

class PresenceMethods{

    public static function getByAgAndMember($ag, $member){
        $presence = Presence::where('ags_id', $ag)
                            ->where('membres_id', $member)
                            ->first();
        if($presence){
            $membre = MembreMethods::getById($member);
            if($membre != "not found") $presence['membre'] = $membre->firstName.' '.$membre->lastName;    
            return $presence;
        }
        else return "not found";
    }


    public static function checkAssociationPresenceAll($assocId, $ag_id){
        $membres = MembreMethods::getByAssociationId($assocId);
        $ag = AgMethods::getById($ag_id);
        if($membres != "not found"){
            if($ag != "not found"){
                $presences = array();
                foreach ($membres as $key => $membre) {
                    $presence = PresenceMethods::getByAgAndMember($ag_id, $membre->id);

                    if($presence != "not found"){
                        $presence['membre'] = "$membre->firstName $membre->lastName";
                        $presences[] = $presence;
                    }
                }
                $success['status'] = "OK";
                $success['data'] = $presences;

                return $success;
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = "ag {$ag_id} doesn't exist";
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
    

    public static function checkPresence($assocId, $membre_id, $ag_id){
        $membre = MembreMethods::hasAssociationAndGet($membre_id, $assocId);
        $ag = AgMethods::getById($ag_id);

        if($membre != "not found"){
            if($ag != "not found"){
                $presence = PresenceMethods::getByAgAndMember($ag_id, $membre_id);
                if($presence != "not found"){
                    $presence['membre'] = "$membre->firstName $membre->lastName";
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
                $err['errMsg'] = "ag {$ag_id} doesn't exist";
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


    public static function addPresence($membre_id, $ag_id, $status, $raison = ""){
        $membre = MembreMethods::getById($membre_id);
        $ag = AgMethods::getById($ag_id);

        if($membre != "not found"){
            if($ag != "not found"){

                try {

                    $check = Presence::where('membres_id', $membre_id)
                                        ->where('ags_id', $ag_id)
                                        ->first();
                    if($check){
                        $presence = Presence::where('membres_id', $membre_id)
                                            ->where('ags_id', $ag_id)
                                            ->update([
                                                "status" => $status,
                                                "raison" => $raison
                                            ]);
                    }else{
                        $presence = Presence::create([
                            "membres_id" => $membre_id,
                            "ags_id" => $ag_id,
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
                $err['errMsg'] = "ag {$ag_id} doesn't exist";
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

    public static function updatePresence($membre_id, $ag_id, $status){
        $presence = PresenceMethods::getByAgAndMember($ag_id, $membre_id);
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

    /**
     * check la presence d'un membre dans les ags d'une association
     */
    public static function checkPresenceMemberAgs($assocId, $membre_id){
        $cycle = CycleMethods::checkActifCycle($assocId);
        if($cycle == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "active cycle doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $ags = AgMethods::getByIdCycle($cycle->id);
        if($ags == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "ags of cycle {$cycle->id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $member = MembreMethods::getById($membre_id);
        if($member == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = "member {$membre_id} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $presences = array();
        foreach ($ags as $key => $ag) {
            $presence = PresenceMethods::checkPresence($assocId, $membre_id, $ag->id);
            if($presence['status'] == "OK") $presences[] = $presence['data'];
        }

        $success['status'] = "OK";
        $success['data'] = $presences;

        return $success;
    }
}