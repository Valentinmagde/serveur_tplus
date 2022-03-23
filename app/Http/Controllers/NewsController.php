<?php

namespace App\Http\Controllers;

use Storage;
use File;
use Illuminate\Http\UploadedFile;
use App\Models\Association;
use App\CustomModels\NewsMethods;
use App\Models\Utilisateur;
use App\Models\Membre;
use App\Models\CommentaireNouvelle;
use App\Models\MembresHasUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Validator;
use Carbon\Carbon;
use App\Events\Nouvelle;

class NewsController extends Controller
{
    /** 
     * Create news api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function createNews(Request $request, FileReceiver $receiver)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'membres_id' => 'required',
            'titre' => 'required',
            'description' => 'required',
            'categorie' => 'required',
            'date_nouvelle' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        return NewsMethods::createNews($request, $receiver);
    }

    /**
     * Supprimer un cycle d'une association
     *  @param Association $id
     * @param Cycle $id
     * @return data
     */
    public function deleteNew(Request $request)
    {
        return NewsMethods::deleteNew($request);
    }

    /** 
     * Liker une nouvelle 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function likeNews(Request $request)
    {

        return NewsMethods::likeNews($request);
    }

    /** 
     * Disliker une nouvelle 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function disLikeNews(Request $request)
    {

        return NewsMethods::disLikeNews($request);
    }

    /** 
     * Liste des nouvelles d'une Association 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function getAssociationNews(Request $request)
    {
        return NewsMethods::getAssociationNews($request);
    }

    /** 
     * Update new
     * 
     * @return \Illuminate\Http\Response 
     */
    public function updateNew(Request $request)
    {
        return NewsMethods::updateNew($request);
    }



    public function publier($new)
    {
        $news = NewsMethods::publier($new);
        if ($news['status'] == "OK") {
            return response()->json($news, 201);
        } else {
            return response()->json($news, 500);
        }
    }
}
