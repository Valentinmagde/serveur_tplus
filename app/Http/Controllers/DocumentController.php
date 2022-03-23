<?php

namespace App\Http\Controllers;

use App\CustomModels\AssociationMethods;
use App\CustomModels\FileManagementMethods;
use App\CustomModels\FileUpload;
use Illuminate\Http\Request;
use Validator;

class DocumentController extends Controller
{
    //
    /**
     * récupérer tout les fichiers d'une association
     * 
     * @param $assocId qui est l'id de l'association
     */
    public function index($assocId)
    {

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $files = FileManagementMethods::getAllAssociationFiles($assocId);

        return response()->json($files, 200);
    }


    /**
     * récupérer un document d'association par son id
     * @param $assocId
     * @param $document qui est l'id de document
     */
    public function show($assocId, $document)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $file = FileManagementMethods::getDocumentFile($document);

        if ($file['status'] == "OK") {
            return response()->json($file, 200);
        } else {
            return response()->json($file, 404);
        }
    }


    /**
     * ajout d'un fichier à une association
     * 
     * @param $assocId l'id de l'association
     * @param $request qui doit contenir les données à sauvegarder
     * 
     *      les données sont : 
     *          -> l'intitule du fichier
     *          -> le fichier 
     *          -> le type auquel le fichier appartient (Nouvelle, ...)
     */
    public function store($assocId, Request $request)
    {
        //Verifier si les champs sont rempli
        $validator = Validator::make($request->all(), [
            'intitule' => 'required',
            'description' => 'required',
            'file' => 'required|file',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $file = FileManagementMethods::addAssociationFile($request->file('file'), $assocId, $request->get('intitule'), $request->get('description'), $request->get('type'));

        if ($file['status'] == 'OK') {
            return response()->json($file, 201);
        } else {
            return response()->json($file, 500);
        }
    }

    /**
     * récupération de l'espace utilisé par le dossié d'une application
     */
    public function getUsedSize($assocId)
    {

        $path = FileUpload::getAssociationPath($assocId);

        $size = FileManagementMethods::folderSize($path);
        $success['status'] = "OK";
        $success['data']['O'] = $size;
        $success['data']['KO'] = $size / 1024;
        $success['data']['MO'] = $size / 1048576;
        $success['data']['GO'] = $size / 1073741824;

        return response()->json($success, 200);
    }


    public function destroy($assocId, $document)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $del = FileManagementMethods::deleteFile($document);

        if ($del['status'] == "OK") {
            return response()->json($del, 203);
        } else if ($del['data']['errNo'] == 15) {
            return response()->json($del, 404);
        } else {
            return response()->json($del, 500);
        }
    }

    public function download($assocId, $document)
    {
        $association = AssociationMethods::getById($assocId);

        if ($association == "not found") {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $file = FileManagementMethods::downloadFile($document);

        if ($file['status'] == "OK") {
            return response()->download($file['data']['file'], explode('/', $file['data']['name'])[1]);
        } else {
            return response()->json($file, 404);
        }
    }
}
