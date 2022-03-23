<?php 

namespace App\CustomModels;

use Carbon\Carbon;

class DateMethods{

    /**
     * 
     */
    public static function getCurrentDateInt(){
          //date actuelle
          $dateactu = Carbon::now();

          //transformer cette date en entier
          $dateactu = strtotime($dateactu);

        return $dateactu;
    }


    /**
     * 
     */
    public static function getDateInt($date){
        return strtotime($date);
    }
}