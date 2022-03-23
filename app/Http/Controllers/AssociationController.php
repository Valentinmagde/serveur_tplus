<?php

namespace App\Http\Controllers;

use App\CustomModels\AssociationMethods;
use App\CustomModels\FileManagementMethods;
use App\Models\Association;
use App\Models\Membre;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Validator;

class AssociationController extends Controller
{


    public function setAssociationsDefaultAWallets(){
        return AssociationMethods::setAssociationsDefaultAWallets();
    }

    /** 
     * Create association api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function createAssociation(Request $request, FileReceiver $receiver)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'description' => 'required',
            'date_creation' => 'required',
            'pays' => 'required',
            'ville' => 'required',
            'fuseau_horaire' => 'required',
            'devise' => 'required',
            'visibilite_financiere' => 'required',
            'admin_id' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return AssociationMethods::createAssociation($request, $receiver);
    }

    /**
     * Delete association
     * @param Association $id
     * @return data
     */
    public function deleteAssociation(Request $request)
    {
        // // check if all fields are filled
        // $validator = Validator::make($request->all(), [ 
        //     'assocId' => 'required',
        //     'adminId' => 'required'
        // ]);

        // //Returns an error if a field is not filled
        // if ($validator->fails()) { 
        //     $err['errNo'] = 10;
        //     $err['errMsg'] = implode(", ", $validator->errors()->all());
        //     $error['status'] = 'NOK';
        //     $error['data'] = $err;
        //     return response()->json($error, 400);             
        // }

        return AssociationMethods::deleteAssociation($request);
    }
    /**
     * Get member's associations
     * @param Member $id
     * @return Association
     */
    public function getAssociationMemberById(Request $request, $id)
    {
        return AssociationMethods::getAssociationMemberById($id);
    }

    /**
     * Liste des utilisateurs d'un compte
     * @param Member $id
     * @return Association
     */
    public function getUser(Request $request)
    {
        return AssociationMethods::getUser($request);
    }

    /**
     * Invite un utilisateur a rejoindre une association en tant que membre
     * @param Association $id
     * @return Association 
     */
    public function connect(Request $request)
    {
        //Selection de l'association
        return AssociationMethods::connect($request);
    }

    /**
     * Desinscrire un utilisateur à un compte membre
     * @param Association $id
     * @param Membre $id
     * @return data 
     */
    public function disconnect(Request $request)
    {
        //Selection de l'association
        return AssociationMethods::disconnect($request);
    }


    /**
     * Get Association by id
     * @param Association $id
     * @return Association 
     */
    public function getAssociationById(Request $request)
    {
        return AssociationMethods::getAssociationById($request);
    }

    /** 
     * Update Association api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function updateAssociation(Request $request)
    {
        try {
            //Verifier si les champs sont rempli
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);
            if ($validator->fails()) {
                $err['errNo'] = 10;
                $err['errMsg'] = implode(", ", $validator->errors()->all());
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }

            return AssociationMethods::updateAssociation($request);
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
    }

    /** 
     * Rejoindre une Association 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function rejoindreAssociation(Request $request)
    {
        //Verifier si les champs sont rempli
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'utilisateurs_id' => 'required'
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return AssociationMethods::rejoindreAssociation($request);
    }

    /** 
     * Quitter une Association 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function quitterAssociation(Request $request)
    {
        return AssociationMethods::quitterAssociation($request);
    }

    /** 
     * Liste des membres d'une Association 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function getAssociationMember(Request $request)
    {
        return AssociationController::getAssociationMember($request);
    }


    public function getAssociationAdmin($admin_id)
    {

        $ass = AssociationMethods::getAdminAssociation($admin_id);
        return response()->json($ass, 200);
    }

    /**
     * change state of association
     */
    public function changeStateAssociation($assocId)
    {
        $state = AssociationMethods::changeStateAsociation($assocId);

        if ($state['status'] == "OK") {
            return response()->json($state, 201);
        } else {
            if ($state['data']['errNo'] == 15) return response()->json($state, 404);
            else return response()->json($state, 500);
        }
    }

    /**
     * add size to association
     */
    public function addSize($assocId, Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'size' => 'required | integer',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $size = FileManagementMethods::addSizeToAssociation($assocId, $request->input('size'));
        if ($size['status'] == "OK") {
            return response()->json($size, 201);
        }
        if ($size['data']['errNo'] == 15) {
            return response()->json($size, 404);
        } else {
            return response()->json($size, 500);
        }
    }
}
