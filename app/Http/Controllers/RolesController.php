<?php

namespace App\Http\Controllers;

use App\CustomModels\AssociationMethods;
use App\CustomModels\MembreMethods;
use App\CustomModels\RoleMethods;
use App\CustomModels\UserMethods;
use Illuminate\Http\Request;
use Validator;

class RolesController extends Controller
{
    //

    public function addRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'libelle' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        $role = RoleMethods::addRole($request->get('libelle'));

        if ($role['status'] == 'OK') {
            return response()->json($role, 201);
        } else {
            return response()->json($role, 500);
        }
    }


    public function allRoles()
    {
        $roles = RoleMethods::allRoles();
        return response()->json($roles, 200);
    }

    /**
     * assigner un role à un utilisateur 
     */
    public function assignRole($assoc_id, $member_id, $user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $member = MembreMethods::getMember($member_id);

        if ($member == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $user = UserMethods::getById($user_id);

        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'User doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        return  RoleMethods::addMultipleRolesUser($user_id, $assoc_id, $member_id, $request->get('role_id'));
    }

    /**
     * récupération de tout les roles d'un utilisateur
     */
    public function getRoles($assoc_id, $user_id)
    {
        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $user = UserMethods::getById($user_id);

        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'User doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $roles = RoleMethods::getRolesForAssociation($user_id, $assoc_id);

        if ($roles['status'] == 'OK') {
            return response()->json($roles, 200);
        } else {
            return response()->json($roles, 500);
        }
    }

    /**
     * retirer un role à un utilisateur
     */
    public function removeRole($assoc_id, $member_id, $user_id, Request  $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $member = MembreMethods::getMember($member_id);

        if ($member == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $user = UserMethods::getById($user_id);

        if ($user == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'User doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        return RoleMethods::removeMultipleRoleUser($user_id, $assoc_id, $member_id, $request->get('role_id'));
    }
}
