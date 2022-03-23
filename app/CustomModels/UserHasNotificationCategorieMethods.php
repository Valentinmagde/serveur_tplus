<?php

namespace App\CustomModels;

use App\Models\UtilisateursHasCategoriesNotification;

class UserHasNotificationCategorieMethods{


    /**
     * a partir du user_id et d'un tableau de catégorie, on verifie si l'utilisateur en question possède ces catégories pour pouvoir lui envoyer des notifications
     * 
     */
    public static function HasCategorie($user_id, $cat){
        $has = false;
        $elt = UtilisateursHasCategoriesNotification::where('categories_notifications_id', $cat)
                                                    ->where('utilisateurs_id', $user_id)
                                                    ->first();
                                                    
        if($elt){
         $has = true;
        }

        return $has;
    }

}