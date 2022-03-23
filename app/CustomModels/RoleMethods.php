<?php 

namespace App\CustomModels;

use App\Models\Privilege;
use App\Models\Role;

class RoleMethods{


    /**
     * ajouter un role à la base de donné
     */
    public static function addRole($role){

        try {
            $role = Role::create([
                'libelle' => $role
            ]);
            $success['status'] = 'OK';
            $success['data'] = $role; 
            return $success;

        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }

    /**
     * récupération de tout les roles
     */
    public static function allRoles(){
        $roles = Role::all();
        $success['status'] = 'OK';
        $success['data'] = $roles; 
        return $success;
    }


    /**
     * récupérer tout les roles d'un utilisateur
     */
    public static function getRolesForAssociation($user_id, $assocId){
       
        try {
                $privileges = Privilege::select('Roles_id')
                                ->where('utilisateurs_id', $user_id)
                                ->where('associations_id', $assocId)
                                ->get();
    
                $roles = array();
                foreach ($privileges as $key => $privilege) {
                    $role = Role::select('libelle')
                                    ->where('id', $privilege->Roles_id)
                                    ->first();
                    $roles[] = $role->libelle;
                }
    
                $success['status'] = 'OK';
                $success['data'] = $roles;    
                
                return $success;
           
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }

    /**
     * assigner un role à un utilisateur
     * @param $user_id
     * @param $assoc_id
     * @param $member_id
     * @param $role_id
     * 
     * on vérifie d'abord si c'est un compte membre de l'association,
     * ensuite on verifie si l'utilisateur est membre
     * et si tout est ok on lui assigne le role en question
     */
    public static function addRoleUser($user_id, $assoc_id, $member_id, $role_id){
        try {

            $hasAssociation = MembreMethods::hasAssociation($member_id, $assoc_id);

            if($hasAssociation){

                $member_has_user = MembresHasUserMethods::getMemberHasId($user_id, $member_id);

                if($member_has_user != "not found"){
                    // dd($member_has_user->id);
                    $privilege = Privilege::create([
                        "roles_id" => $role_id,
                        "membres_has_users_id" => $member_has_user->id,
                        "utilisateurs_id" => $user_id,
                        "associations_id" => $assoc_id 
                    ]);

                    $success['status'] = "OK";
                    $success['data'] = $privilege;
                    return $success;

                }else{
                    $err['errNo'] = 11;
                    $err['errMsg'] = "cet utilisateur n'est pas membre de l'association";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }else{
                $err['errNo'] = 11;
                $err['errMsg'] = "ce membre ne fait pas partie de l'association";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * assigner un role à un utilisateur
     * @param $user_id
     * @param $assoc_id
     * @param $member_id
     * @param $role_id
     * 
     * on vérifie d'abord si c'est un compte membre de l'association,
     * ensuite on verifie si l'utilisateur est membre
     * et si tout est ok on lui assigne le role en question
     */
    public static function addMultipleRolesUser($user_id, $assoc_id, $member_id, $role_id){
        try {

            foreach ($role_id as $key => $role) {
                $privilege = RoleMethods::addRoleUser($user_id, $assoc_id, $member_id, $role);
            }

            if($privilege['status'] == 'OK'){
                return response()->json($privilege, 201);
            }else if($privilege['data']['errNo'] == 11){
                return response()->json($privilege, 400);
            }else{
                return response()->json($privilege, 500);
            }
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * suppression d'un role chez un utilisateur
     * @param $user_id
     * @param $assoc_id
     * @param $member_id
     * @param $role_id
     * 
     * on vérifie d'abord si c'est un compte membre de l'association,
     * ensuite on verifie si l'utilisateur est membre
     * et si tout est ok on lui retire le role en question en suppriment l'enregistrement en question
     */
    public static function removeRoleUser($user_id, $assoc_id, $member_id, $role_id){
        try {

            $hasAssociation = MembreMethods::hasAssociation($member_id, $assoc_id);

            if($hasAssociation){

                $member_has_user = MembresHasUserMethods::getMemberHasId($user_id, $member_id);

                if($member_has_user != "not found"){
    
                    $privilege = Privilege::where("Roles_id", $role_id)
                                            ->where("membres_has_users_id", $member_has_user->id)
                                            ->delete();

                    $success['status'] = "OK";
                    $success['data'] = $privilege;

                    return $success;

                }else{
                    $err['errNo'] = 11;
                    $err['errMsg'] = "cet utilisateur n'est pas membre de l'association";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
            }else{
                $err['errNo'] = 11;
                $err['errMsg'] = "ce membre ne fait pas partie de l'association";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

      /**
     * suppression d'un role chez un utilisateur
     * @param $user_id
     * @param $assoc_id
     * @param $member_id
     * @param $role_id
     * 
     * on vérifie d'abord si c'est un compte membre de l'association,
     * ensuite on verifie si l'utilisateur est membre
     * et si tout est ok on lui retire le role en question en suppriment l'enregistrement en question
     */
    public static function removeMultipleRoleUser($user_id, $assoc_id, $member_id, $role_id){
        try {

            foreach ($role_id as $key => $role) {
                $privilege = RoleMethods::removeRoleUser($user_id, $assoc_id, $member_id, $role);
            }

            if($privilege['status'] == 'OK'){
                return response()->json($privilege, 203);
            }else if($privilege['data']['errNo'] == 11){
                return response()->json($privilege, 400);
            }else{
                return response()->json($privilege, 500);
            }

        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

}