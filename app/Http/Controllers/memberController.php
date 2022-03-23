<?php

namespace App\Http\Controllers;

use App\CustomModels\AssociationMethods;
use Illuminate\Http\Request;
use App\CustomModels\MembreMethods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Validator;
use Carbon\Carbon;

class memberController extends Controller
{
    /** 
     * Create a member api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function createMember(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'created_by' => 'required',
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        //Check the current user
        // if(Auth::id() != $request->created_by){
        //     $err['errNo'] = 15;
        //     $err['errMsg'] = 'User isn\'t allowed';
        //     $error['status'] = 'NOK';
        //     $error['data'] = $err;
        //     return response()->json($error, 401);
        // }

        return MembreMethods::createMember($request);
    }

    /**
     * Créer plusieurs comptes apartir d'un formulaire
     * @return data
     */
    protected function createMembersMass(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'created_by' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        //Check the current user
        if (Auth::id() != $request->created_by) {
            $err['errNo'] = 14;
            $err['errMsg'] = 'User isn\'t allowed';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return MembreMethods::createMembersMass($request);
    }


    /**
     * Créer plusieurs comptes apartir d'un fichier csv
     * @param UploadedFile $file
     * @return string
     */
    protected function createMembersCsv(Request $request, FileReceiver $receiver)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'created_by' => 'required',
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        //Check the current user
        if (Auth::id() != $request->created_by) {
            $err['errNo'] = 14;
            $err['errMsg'] = 'User isn\'t allowed';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        return MembreMethods::createMembersCsv($request, $receiver);
    }

    /**
     * Get member by id
     * @param Membre $id
     * @return Membre
     */
    public function getMemberById(Request $request)
    {
        return MembreMethods::getMemberById($request);
    }
    /**
     * Supprimer plusieurs comptes membre d'une association
     *  @param Association $id
     * @param Member $id
     * @return data
     */
    public function deleteAssociationMembers(Request $request)
    {
        return MembreMethods::deleteAssociationMembers($request);
    }


    /**
     * Supprimer un compte membre d'une association
     *  @param Association $id
     * @param Member $id
     * @return data
     */
    public function deleteAssociationMember(Request $request)
    {
        return MembreMethods::deleteAssociationMember($request);
    }

    /**
     * Inviter un Utilisateur à rejoindre un compte membre
     *  @param Association $id
     * @param Member $id
     * @return data
     */
    public function inviteUser(Request $request)
    {
        //Verifier si le champ user_id et admin_id est rempli
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'phone' => 'required',
            'admin_id' => 'required'
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return MembreMethods::inviteUser($request);
    }
    /**
     * Get members from an association
     * @param Membre $id
     * @return Membre
     */
    public function getMembers(Request $request)
    {
        return MembreMethods::getMembers($request);
    }

    public function changeState($assoc_id, $membre, $state)
    {

        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $membre = MembreMethods::setStateMember($membre, $state);
        if ($membre['status'] == "OK") {
            return response()->json($membre, 201);
        } else if ($membre['data']['errNo'] == 15) {
            return response()->json($membre, 404);
        } else {
            return response()->json($membre, 500);
        }
    }


    public function getStatistiques($assoc_id, $membres_id)
    {
        $association = AssociationMethods::getById($assoc_id);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $stat = MembreMethods::getStatistiqueMembre($membres_id);
        return response()->json($stat, 200);
    }
}
