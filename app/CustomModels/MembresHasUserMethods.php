<?php


namespace App\CustomModels;


use App\Models\MembresHasUser;

class MembresHasUserMethods{

/**
 * récuperer par l'id d'un utilisateur la correspondance membre-user
 */
    public static function getByUserId($id){

        $membreHas = MembresHasUser::where('utilisateurs_id', $id)->first();
    
        if($membreHas){
            return $membreHas;
        }else{
            return "not found";
        }

    }

    /**
     * récupération d'un membre à partir du user ID
     */

    public static function getByUserIdAssocId($user_id, $assoc_id){

        $membre = MembresHasUser::join('membres', 'membres.id', 'membres_has_users.membres_id')
                                    ->where('membres_has_users.utilisateurs_id', $user_id)
                                    ->where('membres.associations_id', $assoc_id)
                                    ->select('membres.id')
                                    ->first();
                                    

        if($membre){
            return $membre;
        }else{
        return "not found";
        }
    }

    /**
     * récupérer l'id du member_has_user à partirde l'utilisateur et du membre
     */
    public static function getMemberHasId($user_id, $member_id){
        $membreHas = MembresHasUser::select('id')
                                    ->where('utilisateurs_id', $user_id)
                                    ->where('membres_id', $member_id)
                                    ->first();
    
        if($membreHas){
            return $membreHas;
        }else{
            return "not found";
        }
    }


    public static function userHasMember($user_id, $member_id){
        $has = false;
        $membreHas = MembresHasUser::where('utilisateurs_id', $user_id)
                                    ->where('membres_id', $member_id)
                                    ->first();
        if($membreHas){
            $has = true;
        }

        return $has;
    }

    /**
     * récupération d'un membre à partir du user ID
     */

    public static function getByUserIdAssociationId($user_id, $assoc_id){

        $membre = MembresHasUser::join('membres', 'membres.id', 'membres_has_users.membres_id')
                                    ->where('membres_has_users.utilisateurs_id', $user_id)
                                    ->where('membres.associations_id', $assoc_id)
                                    ->select('membres.id')
                                    ->first();
                                    

        if($membre){
            return $membre;
        }else{
            return "not found";
        }
    }


    /**
     * récuperer par l'id d'un utilisateur la correspondance membre-user
     */
    public static function getByUserIdMembers($id){
        
        $membreHas = MembresHasUser::where('utilisateurs_id', $id)->get();
        if($membreHas){
            return $membreHas;
        }else{
            return "not found";
        }

    }

public static function getByMemberIdMembers($id){

    $membreHas = MembresHasUser::where('membres_id', $id)->get();

    if($membreHas){
        return $membreHas;
    }else{
        return "not found";
    }

}



public static function getUserByMemberId($id){

    $membreHas = MembresHasUser::where('membres_id', $id)->first();

    if($membreHas){
        $user = UserMethods::getById($membreHas->utilisateurs_id);
        if($user){
            return $user;
        }


        return "not found";
    }else{
        return "not found";
    }

}


/**
 * création
 */
    public static function createMemberHasUser($request){
        $input = $request->all(); 
        try {
            $member = MembresHasUser::create($input);
            $success['status'] = 'OK'; 
            $success['data'] =  $member;

            return response()->json($success,200); 
        }
        catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
       }
    }

    /**
     * récupération d'un membre à partir du user ID
     */

    public static function getMemberHasUserByid($request){

        if(!$memberHas = MembresHasUser::find($request->id)){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);    
        }

        return response()->json($memberHas, 200);
    }


    /**
     * suppression du lien entre un utilisateur et un membre
     */
    public static function deleteMemberUserLink($idMember){
         return MembresHasUser::where('membres_id', $idMember)->delete();
    }
    
}