<?php

namespace App\Http\Controllers;

use App\CustomModels\NewsCommentMethods;
use App\Models\CommentaireNouvelle;
use Illuminate\Support\Facades\Auth;
use App\Models\MembresHasUser;
use App\Models\Association;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

class NewsCommentController extends Controller
{
    /** 
     * Create a comment api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function createComment(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        return NewsCommentMethods::createComment($request);
    }
}
