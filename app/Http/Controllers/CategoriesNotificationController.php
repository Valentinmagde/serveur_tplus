<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomModels\CategoriesNotificationMethods;
class CategoriesNotificationController extends Controller
{
    //

    public function index(){
        $cat = CategoriesNotificationMethods::getAll();

        return response()->json($cat, 200);
    }

    public function setCategoriesToUser($user_id, Request $request){
        $cat = CategoriesNotificationMethods::setCategoriesToUser($user_id, \json_decode($request->input('ids')));
        if($cat['status'] == "OK"){
            return response()->json($cat, 201);
        }if($cat['data']['errNo'] == 15){
            return response()->json($cat, 404);
        }else{
            return response()->json($cat, 500);
        }
    }

    public function show($user_id){
        $cat = CategoriesNotificationMethods::getCategoriesChecked($user_id);

        return response()->json($cat, 200);
    }
}
