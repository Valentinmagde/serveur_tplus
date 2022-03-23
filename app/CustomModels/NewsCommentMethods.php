<?php

namespace App\CustomModels;

use App\Models\CommentaireNouvelle;
use Illuminate\Support\Facades\Auth;
use App\Models\MembresHasUser; 
use App\Models\Association;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

class NewsCommentMethods
{
    /** 
    * Create a comment api 
    * 
    * @return \Illuminate\Http\Response 
    */ 
   public static function createComment($request) 
   {    
        $input = $request->all();
        if($association = Association::find($request->assocId)){
                
            try {
                $membreHasUser = MembresHasUser::where('utilisateurs_id',$request->user_id)->first();
                //date actuelle
                $datactu = Carbon::now();
        
                //transformer cette date en entier
                $datactu = strtotime($datactu);
                

                $input['created_at'] = DateMethods::getCurrentDateInt();
                $input['nouvelles_id'] = $request->nouvId;
                $input['membres_id'] = $membreHasUser->membres_id;

                $comment = CommentaireNouvelle::create($input);
                $success['status'] = 'OK'; 
                $success['data'] =  $comment;
        
                return response()->json($success,201); 
            }
            catch(\Exception $e){
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);    
        }
        
    }
}