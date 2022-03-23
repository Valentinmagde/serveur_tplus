<?php
namespace App\CustomModels;

use App\Models\Caiss;

class CaisseMethods{

    public static function store($caisse){
        return Caiss::create($caisse);
    }


    public static function update($caisse){

    }


    public static function getById($id){
        $caisse = Caiss::where('id', $id);
        
        if($caisse){
            return $caisse;
        }else{
            return "not found";
        }
    }

    public static function getByActivityId($id){
        $caisse = Caiss::where('activites_id1', $id);
        
        if($caisse){
            return $caisse;
        }else{
            return "not found";
        }
    }
}