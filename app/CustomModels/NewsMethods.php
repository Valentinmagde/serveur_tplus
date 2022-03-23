<?php

namespace App\CustomModels;

use Storage;
use File;

use App\CustomModels\FileUpload; 
use Illuminate\Http\UploadedFile;
use App\Models\Association;
use App\Models\Nouvelle;
use App\Models\Utilisateur;
use App\Models\Membre; 
use App\Models\CommentaireNouvelle; 
use App\Models\MembresHasUser; 
use App\CustomModels\MembresHasUserMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Filesystem\Filesystem;
use Validator;
use Carbon\Carbon;
use App\Events\Nouvelle as NouvelleEvent;

class NewsMethods 
{
     /** 
     * Create news api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public static function createNews($request, $receiver) 
    { 
        try {
            $input = $request->all(); 
            $input['associations_id'] = $request->assocId; 

            // $membreHasUser = MembresHasUser::where('utilisateurs_id',$request->user_id)->first();
            //date actuelle
            $datactu = Carbon::now();

            //transformer cette date en entier
            $datactu = strtotime($datactu);

            // $input['membres_id'] = $membreHasUser->membres_id;
            $input['create_at'] = $datactu;

            $association = Association::find($request->assocId);
            // receive the file
            $save = $receiver->receive();
            
           if($association){
                // check if the upload has finished (in chunk mode it will send smaller files)
                if ($save !== false && $save->isFinished()) {
                    // save the file and return any response you need
                    $photo = FileUpload::associationFileUpload($save->getFile(), $association->id, "nouvelle");
                    if($photo != "no space" && $photo != "error" && $photo != "folder creation failed"){
                        $input['photo'] = url($photo); 
                    }else{
                        $err['errNo'] = 11;
                        $err['errMsg'] = $photo;
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500); 
                    }
                    $handler = $save->handler();
                    if($association->moderateur_contenu != 0) $input['etat'] = "ATTENTE";
                    else $input['etat'] = "PUBLIE";

                    //Création de la nouvelle
                    $news = Nouvelle::create($input);

                    $success['status'] = 'OK'; 
                    $success['data'] =  $news;
        
                    event(new NouvelleEvent($news, $news->membres_id, "une nouvelle a été créée", "creation d'une nouvelle"));
                    return response()->json($success,201); 
                }else{
                    //Création de la nouvelle
                    if($association->moderateur_contenu != 0) $input['etat'] = "ATTENTE";
                    else $input['etat'] = "PUBLIE";
                    $news= Nouvelle::create($input);

                    $success['status'] = 'OK'; 
                    $success['data'] =  $news;
        
                    event(new NouvelleEvent($news, $news->membres_id, "une nouvelle a été créée", "creation d'une nouvelle"));
                    return response()->json($success,201); 
                }
           }
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
     * Supprimer un cycle d'une association
     *  @param Association $id
     * @param Cycle $id
     * @return data
     */
    public static function deleteNew($request){
        //Selectionner l'association correspondant à assocId
        $association = Association::find($request->assocId);
        //Verifier si l'association existe
        if(!$association){
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }else{
            //Selectionner le cycle correspondant à l'id
            $new = Nouvelle::find($request->id);
            //Verifier si le membre existe
            if(!$new){
                $err['errNo'] = 15;
                $err['errMsg'] = 'New doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }else{
                //Supprimer les commentaires de cette nouvelle s'il en existe
                $res = CommentaireNouvelle::where('nouvelles_id', $new->id)->delete();
                //Suppression le compte membre
                $new->delete();

                $success['status'] = 'OK'; 
                $success['data'] = 'The new was deleted successfully.';

                return response()->json($success,203);
            }
        }
    }
    /** 
     * Liker une nouvelle - 
     * A la place du Membre ID, le front end envoie un User ID.
     * Il est donc de la responsabilite de cette fonction de trouver le membre ID 
     * associe au User ID
     * 
     * @return \Illuminate\Http\Response 
    */ 
    public static function likeNews($request) 
    {   
        //Verifier si l'association existe
        $association = Association::find($request->assocId);
        if($association)
        {
            //Vérifier si la nouvelle existe
            $new = Nouvelle::find($request->nouvId);

            if($new){
                $membre = MembreMethods::getById($request->membres_id);
                if($membre != "not found"){
                    //Mettre à jour le like
                    $like = CommentaireNouvelle::where([
                        ['membres_id', $request->membres_id],
                        ['nouvelles_id', $request->nouvId],
                        ['aime', '>=', '0']
                    ])
                    ->orWhere([
                        ['membres_id', $request->membres_id],
                        ['nouvelles_id', $request->nouvId],
                        ['aime_pas', '>=', '0']
                    ])
                    ->first();
    
                    if($like){
                        $bool1 = 1;
                        $bool2 = 0;
    
                        //date actuelle
                        $datactu = Carbon::now();
    
                        //transformer cette date en entier
                        $datactu = strtotime($datactu);
                        $like->fill([
                            'aime' => $bool1,
                            'aime_pas' => $bool2,
                            'updated_at' => DateMethods::getCurrentDateInt()
                        ]);
                        $like->save();
    
                        //Formatage du message de confirmation
                        $success['status'] = 'OK';
                        $success['data'] = $like;
                        return response()->json($success, 200);
                  
                } 
                else{
                        //date actuelle
                        $datactu = Carbon::now();
    
                        //transformer cette date en entier
                        $datactu = strtotime($datactu);
    
                        $like = CommentaireNouvelle::create([
                            'aime' => 1,
                            'membres_id' => $request->membres_id,
                            'nouvelles_id' => $request->nouvId,
                            'created_at' => DateMethods::getCurrentDateInt()
                        ]);
    
                        //Formatage du message de confirmation
                        $success['status'] = 'OK';
                        $success['data'] = $like;
                        return response()->json($success, 200);
                    }
                }else{
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Member doesn\'t existe';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 400);
                }
                  
            }
            else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'New doesn\'t existe';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t existe';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
            
        }
    }

    /** 
     * Disliker une nouvelle 
     * 
     * @return \Illuminate\Http\Response 
    */ 
    public static function disLikeNews($request) 
    {   
        
        //Verifier si l'association existe
        $association = Association::find($request->assocId);
        if($association)
        {
            //Vérifier si la nouvelle existe
            $new = Nouvelle::find($request->nouvId);
            if($new){
                $membre = MembreMethods::getById($request->membres_id);
                if($membre != "not found"){
                    //Mettre à jour le like
                $like = CommentaireNouvelle::where([
                    ['membres_id', $request->membres_id],
                    ['nouvelles_id', $request->nouvId],
                    ['aime', '>=', '0']
                ])
                ->orWhere([
                    ['membres_id', $request->membres_id],
                    ['nouvelles_id', $request->nouvId],
                    ['aime_pas', '>=', '0']
                ])
                ->first();

                if($like){
                    $bool1 = 1;
                    $bool2 = 0;
                   
                    //date actuelle
                    $datactu = Carbon::now();

                    //transformer cette date en entier
                    $datactu = strtotime($datactu);
                    $like->fill([
                        'aime' => $bool2,
                        'aime_pas' => $bool1,
                        'updated_at' => DateMethods::getCurrentDateInt()
                    ]);
                    $like->save();

                    //Formatage du message de confirmation
                    $success['status'] = 'OK';
                    $success['data'] = $like;
                    return response()->json($success, 200);
                } 
                else{
                    //date actuelle
                    $datactu = Carbon::now();

                    //transformer cette date en entier
                    $datactu = strtotime($datactu);
                    $like = CommentaireNouvelle::create([
                        'aime_pas' => 1,
                        'membres_id' => $membre,
                        'nouvelles_id' => $request->nouvId,
                        'created_at' => DateMethods::getCurrentDateInt()
                    ]);

                    //Formatage du message de confirmation
                    $success['status'] = 'OK';
                    $success['data'] = $like;
                    return response()->json($success, 200);
                }
                }else{
                    $err['errNo'] = 15;
                    $err['errMsg'] = "member not found";
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
                
                
            }
            else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'New doesn\'t existe';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t existe';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
            
        }
    }

    /** 
     * Liste des nouvelles d'une Association 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public static function getAssociationNews($request) 
    {   
        // Verifier si l'association existe
        $page = ($request->page && $request->page != 0 ) ? (int)$request->page : 1;
        
        $association = Association::find($request->assocId);
        if(!$association)
        {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t existe';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }else{
            //Selectionner toutes les nouvelles de l'association
            $count = Nouvelle::where('associations_id',$request->assocId)
                                ->where('etat', 'PUBLIE')
                                ->count();

            $news = Nouvelle::where('associations_id',$request->assocId)
                                ->where('etat', 'PUBLIE')
                                ->offset(($page-1) * 10)
                                ->limit(10)
                                ->orderBy('date_nouvelle', 'desc')
                                ->get();

            //Selectionner l'auteur de la nouvelle
            $incI = 0;
            $nouvelle = [];
            foreach($news as $arrKey => $arrData){

                $like = CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['aime', '>', '0']
                ])->get();
                
                $unlike =  CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['aime_pas', '>', '0']
                ])->get();

                $commentaires = [];
                $inc = 0;
                $comments = CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['commentaire', '!=', '']
                ])->get();
                $likeDetail = CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['aime', 1]
                ])->get();
                foreach($comments as $key => $value){
                    // $memberHas1 = MembresHasUser::where('membres_id',$value->membres_id)->first();
                    // if($memberHas1){
                        $userIds = MembresHasUser::where('membres_id', $value->membres_id)->get();
                        $userArray = array();
                        foreach ($userIds as $elt){
                            if ($item = Utilisateur::find($elt->utilisateurs_id))
                                array_push($userArray, $item);
                        }
                        $author = MembreMethods::getById($value->membres_id);
                        $author['users'] = $userArray;

                        $com = [];
                        $com = $value;
                        $com['author'] = $author;
                        $inc++;

                        $commentaires[] = $com;
                    // }
                }

                // $memberHas2 = MembresHasUser::where('membres_id',$arrData->membres_id)->first();
                // if($memberHas2){
                $userIds = MembresHasUser::where('membres_id', $arrData->membres_id)->get();
                $userArray = array();
                foreach ($userIds as $elt){
                    if ($item = Utilisateur::find($elt->utilisateurs_id))
                        array_push($userArray, $item);
                }
                $author = MembreMethods::getById($arrData->membres_id);
                $author['users'] = $userArray;

                $new = [];
                $new = $arrData;
                $new['author'] = $author;
                $new['like'] = count($like);
                $new['unlike'] = count($unlike);
                $new['commentaires'] = $commentaires;
                $new['commentaires_count'] = count($commentaires);
                $new['like_detail'] = $likeDetail;
                
                $nouvelle[] = $new;
                // }

                $incI++;
            }
            $pagination  = array(
                "page" => $page,
                "nombre_total_page" => ceil($count/10)
            );  

            //Formatage du message de confirmation
            $success['status'] = 'OK';
            $success['data'] = array(
                "nouvelles" => $nouvelle,
                "pages" => $pagination
            );
            return response()->json($success, 200);
        }
    }

    public static function getAssociationNewsReturnData($assocId) 
    {   
        // Verifier si l'association existe
        $association = Association::find($assocId);
        if(!$association)
        {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t existe';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }else{

            $cycle = CycleMethods::checkActifCycle($assocId);
            if($cycle == "not found"){
                $err['errNo'] = 15;
                $err['errMsg'] = 'Active cycle doesn\'t existe';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $firstAg = AgMethods::getCurrentCycle($cycle->id);

            if($firstAg == "not found"){
                $err['errNo'] = 15;
                $err['errMsg'] = 'current general assembly doesn\'t existe';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            $previousAg = AgMethods::getPreviousAgInCycle($firstAg, $cycle->id);
            if($previousAg == "not found"){
                $daga =date('Y-m-d', strtotime('+1 day'));
                $news = Nouvelle::where('associations_id',$assocId)
                                ->where('etat', 'PUBLIE')
                                ->where('create_at', '<=', strtotime($daga))
                                ->orderBy('date_nouvelle', 'desc')
                                ->get();
            }else{
                $date_ag = $previousAg->date_ag;
                // $date_ag =date('Y-m-d',  $date_ag);
                $daga =date('Y-m-d', strtotime('+1 day'));
                $news = Nouvelle::where('associations_id',$assocId)
                                ->where('etat', 'PUBLIE')
                                ->where('create_at', '>', $date_ag)
                                ->where('create_at', '<', strtotime($daga))
                                ->orderBy('date_nouvelle', 'desc')
                                ->get();
            }
            //Selectionner toutes les nouvelles de l'association
            $count = Nouvelle::where('associations_id',$assocId)
                                ->where('etat', 'PUBLIE')
                                ->count();

            

            //Selectionner l'auteur de la nouvelle
            $incI = 0;
            $nouvelle = [];
            foreach($news as $arrKey => $arrData){

                $like = CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['aime', '>', '0']
                ])->get();
                
                $unlike =  CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['aime_pas', '>', '0']
                ])->get();

                $commentaires = [];
                $inc = 0;
                $comments = CommentaireNouvelle::where([
                    ['nouvelles_id', $arrData->id],
                    ['commentaire', '!=', '']
                ])->get();
                foreach($comments as $key => $value){
                    // $memberHas1 = MembresHasUser::where('membres_id',$value->membres_id)->first();
                    // if($memberHas1){
                        $userIds = MembresHasUser::where('membres_id', $value->membres_id)->get();
                        $userArray = array();
                        foreach ($userIds as $elt){
                            if ($item = Utilisateur::find($elt->utilisateurs_id))
                                array_push($userArray, $item);
                        }
                        $author = MembreMethods::getById($value->membres_id);
                        $author['users'] = $userArray;

                        $com = [];
                        $com = $value;
                        $com['author'] = $author;
                        $inc++;

                        $commentaires[] = $com;
                    // }
                }

                // $memberHas2 = MembresHasUser::where('membres_id',$arrData->membres_id)->first();
                // if($memberHas2){
                    $userIds = MembresHasUser::where('membres_id', $arrData->membres_id)->get();
                    $userArray = array();
                    foreach ($userIds as $elt){
                        if ($item = Utilisateur::find($elt->utilisateurs_id))
                            array_push($userArray, $item);
                    }
                    $author = MembreMethods::getById($arrData->membres_id);
                    $author['users'] = $userArray;

                    $new = [];
                    $new = $arrData;
                    $new['author'] = $author;
                    $new['like'] = count($like);
                    $new['unlike'] = count($unlike);
                    $new['commentaires'] = $commentaires;
                    
                    $nouvelle[] = $new;
                // }

                $incI++;
            }

            //Formatage du message de confirmation
            $success['status'] = 'OK';
            $success['data'] =  $nouvelle;
            return $success;
        }
    }

    /**
     * Mettre à jour une nouvelle
     * 
     * @param $request l'ensemble des données à update
     * 
    */
	public static function updateNew($request){
        try {
            //Verifier si l'association existe
            $association = Association::find($request->assocId);
            if($association){
                $new = Nouvelle::find($request->id);
                if($new){
                    $param = $request->all();
                    //test si la photos existe et si oui alors on l'envoie dans le serveur en appelant la classe FileUpload
                    if($request->file('file')){

                        $path = $new->photo;
                        $path = parse_url($path);
                        $path = $path['path'];
                        $del = "";

                        $system = new Filesystem();
                        $file = $system->exists(public_path($path));
                        
                        if($file){
                            $system->delete(public_path($path));
                            if($del == 0){
                                /* $doc->delete(); */
                                $photo = FileUpload::associationFileUpload($request->file('file'), $association->id, "nouvelle");
                                if($photo != "error"){
                                    $photo =  url($photo);
                                    $param["photo"] = $photo;
                                }
                            }else{
                                $err['errNo'] = 9;
                                $err['errMsg'] = 'suppression du fichier echoué';
                                $error['status'] = 'NOK';
                                $error['data'] = $err;
            
                                return $error;
                            }
                        }else{
                            $photo = FileUpload::associationFileUpload($request->file('file'), $association->id, "nouvelle");
                            if($photo != "error"){
                                $photo =  url($photo);
                                $param["photo"] = $photo;
                            }
                        }
                        
                    }
                    //Mise à jour de la nouvelle
                    $new->fill($param);
                    $new->save();

                    //formatage de message de confirmation
                    $success['status'] = 'OK'; 
                    $success['data'] = $new;

                    return response()->json($success,202); 
                }else{
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'New doesn\'t exist ';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);
                }
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist ';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } 
        }
        catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }


    public static function getById($id){
        $new = Nouvelle::where('id', $id)->first();
        if($new){
            return $new;
        }

        return "not found";
    }


    /**
     * publier une nouvelle qui etait en attente
     */
    public static function publier($news_id){
        $new = NewsMethods::getById($news_id);
        if($new != "not found"){
            try {
                $new->etat = "PUBLIE";
                $new->save();

                $success['status'] = "OK";
                $success['data'] = $new;

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
            $err['errMsg'] = 'New doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;   
        }
    }
}