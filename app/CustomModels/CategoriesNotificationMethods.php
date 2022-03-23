<?php 

namespace App\CustomModels;

use App\Models\CategoriesNotification;
use App\Models\UtilisateursHasCategoriesNotification;
use Illuminate\Support\Facades\DB;

class CategoriesNotificationMethods{

    public static function getAll(){
        $cat = CategoriesNotification::all();
        $categories = array();
        foreach ($cat as $key => $value) {
            $categories[] = $value;
        }
        return array(
            "status" => "OK",
            "data" => $categories
        );
    }


    public static function setCategoriesToUser($user_id, $categories_array){
        DB::beginTransaction();
        $user = UserMethods::getById($user_id);
        if($user != "not found"){
            $arr = array();
           $cats = UtilisateursHasCategoriesNotification::where('utilisateurs_id', $user_id)->delete();
            foreach ($categories_array as $key => $categorie) {
               $cat = CategoriesNotification::find($categorie);
               if($cat){
                   try {
                    $cat = array(
                        "utilisateurs_id" => $user_id,
                        "categories_notifications_id" => $categorie
                       );
                    $arr[] = UtilisateursHasCategoriesNotification::create($cat);
                   }catch(\Exception $e){
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = $e->getMessage();
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return $error;
                }
               }else{
                DB::rollback();

                $err['errNo'] = 15;
                $err['errMsg'] = "category  {$categorie}  not found";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
               }
            }
            if(count($arr) != 0){
                DB::commit();
                $success['status'] = "OK";
                $success['data'] = $arr;
                return $success;
            }else{
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = "aucune catégorie n'a été ajouté à cet utilisateur";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        }else{
            DB::rollback();

            $err['errNo'] = 15;
            $err['errMsg'] = "user  {$user_id}  not found";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;

        }
    }

    public static function getCategoriesChecked($user_id){
        $cat = UtilisateursHasCategoriesNotification::where('utilisateurs_id', $user_id)->get();
        foreach ($cat as $key => $value) {
            $lib = CategoriesNotification::find($value->categories_notifications_id);
            if($lib){
                $value['libelle'] = $lib->libelle;
            }
        }
        return array(
            "status" => "OK",
            "data" => $cat
        );
    }

}