<?php

namespace App\CustomModels;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUpload{

    private $system;

    /**
     * function pour upload un fichier d'une association précise
     * 
     * @param $file le fichier à upload
     * @param $admin_id l'id de l'administrateur en question
     * @param $association_name le nom de l'association qui est utilisé
     * @param $categorie la catégorie à laquelle le fichier appartient
     * 
     * @return $url qui l'url du fichier depuis le serveur
    */
    public static function associationFileUpload(UploadedFile $file, $associationId, $category){
        
        $system = new Filesystem();
        $path = "upload/association/{$associationId}";
        $filename = FileUpload::createFilename($file);
        //test si le dossier existe
        $isDir = $system->isDirectory(storage_path("app/public/{$path}"));
        if($isDir){ //si c'est un dossier 
            $max_size = AssociationMethods::getMaxSize($associationId);
            if($category == "logo"){//si photo de profil de l'association
                $cat = $system->isDirectory(storage_path("app/public/{$path}/{$category}"));
                if($cat){
                    //vider le dossier des profils
                    $clean = $system->cleanDirectory(storage_path("app/public/{$path}/{$category}/"));
                    if($clean){//si vidé
                        $size = FileManagementMethods::folderSize(storage_path("app/public/{$path}"));
                        $size = filesize($file) + $size;
                        if($max_size != "not found" && $max_size->max_size >= $size){
                            $file->move(storage_path("app/public/{$path}/{$category}/"), $filename);
                            return Storage::url("{$path}/{$category}/".$filename);
                        }else{
                            return "no space";
                        }
                    }else{
                        return "folder cleaning failed";
                    }
                }else{
                     //création du dossier si ca n'existe pas
                    $dir = $system->makeDirectory(storage_path("app/public/{$path}/{$category}"), 0777, true, true);
                    if($dir){//si créé
                        //vider le dossier en question
                        $clean = $system->cleanDirectory(storage_path("app/public/{$path}/{$category}/"));
                        //si ca s'est bien vidé
                        if($clean){
                            
                            $file->move(storage_path("app/public/{$path}/{$category}/"), $filename);
                            return Storage::url("{$path}/{$category}/".$filename);
                        }else{
                            return "folder cleaning failed";
                        }
                    }else{
                        return "folder creation failed";
                    }

                }
            }
            else{
                $cat = $system->isDirectory(storage_path("app/public/{$path}/{$category}"));
                if($cat){
                    //vider le dossier des profils
                    $size = FileManagementMethods::folderSize(storage_path("app/public/{$path}"));
                    $size = filesize($file) + $size;
                    if($max_size != "not found" && $max_size->max_size >= $size){
                        $file->move(storage_path("app/public/{$path}/{$category}/"), $filename);
                        return Storage::url("{$path}/{$category}/".$filename);
                    }else{
                        return "no space";
                    }
                    
                }else{
                     //création du dossier si ca n'existe pas
                    $dir = $system->makeDirectory(storage_path("app/public/{$path}/{$category}"), 0777, true, true);
                    if($dir){//si créé
                        $size = FileManagementMethods::folderSize(storage_path("app/public/{$path}"));
                        $size = filesize($file) + $size;
                        if($max_size != "not found" && $max_size->max_size >= $size){
                            $file->move(storage_path("app/public/{$path}/{$category}/"), $filename);
                            return Storage::url("{$path}/{$category}/".$filename);
                        }else{
                            return "no space";
                        }
                    }else{
                        return "folder creation failed";
                    }

                }
                
            }
       }else{
            //création du dossier si ca n'existe pas
            $dir = $system->makeDirectory(storage_path("app/public/{$path}/{$category}"), 0777, true, true);
            if($dir){//si créé
                //vider le dossier en question
                $clean = $system->cleanDirectory(storage_path("app/public/{$path}/{$category}/"));
                //si ca s'est bien vidé
                if($clean){
                    
                    $file->move(storage_path("app/public/{$path}/{$category}/"), $filename);
                    return Storage::url("{$path}/{$category}/".$filename);
                }
            }else{
                return "folder creation failed";
            }

       }

        return "error";
    }

    /**
     * upload le fichier de preuve d'une operation
     */
    public static function operationFileUpload(UploadedFile $file, $member_id, $operation_id){
        $system = new Filesystem();
        $path = "upload/membre/{$member_id}/operation/{$operation_id}";
        $filename = FileUpload::createFilename($file);
    
        //test si le dossier existe
        $isDir = $system->isDirectory(storage_path("app/public/{$path}"));
        if($isDir){ //si c'est un dossier 
          
            $file->move(storage_path("app/public/{$path}/"), $filename);
            return Storage::url("{$path}/".$filename);
               
       }else{
            //création du dossier si ca n'existe pas
            
            $dir = $system->makeDirectory(storage_path("app/public/{$path}/"), 0777, true, true);
            if($dir){//si créé
                //vider le dossier en question
                $clean = $system->cleanDirectory(storage_path("app/public/{$path}/"));
                //si ca s'est bien vidé
                if($clean){
                    
                    $file->move(storage_path("app/public/{$path}/"), $filename);
                    return Storage::url("{$path}/".$filename);
                }
            }

       }

        return "error";
    }


    /**
     * retourner le chemin des fichiers d'une association
     */
    public static function getAssociationPath($assocId){
        $path = "upload/association/{$assocId}";

        return storage_path("app/public/{$path}");
    }


    /**
     * function qui permettra d'upload un fichier d'un utilisateur dans la base de donnée
     * 
     * @param $file le fichier à upload
     * @param $user_id qui est l'id de l'utilisateur
     * @param $category qui est la catégorie dans laquelle sera sauvegardé l'image 
     * 
     * @return $url qui est l'url vers le serveur de l'image  
     */

    public static function userFileUpload(UploadedFile $file, $user_id, $category){
        
        $system = new Filesystem();
        $file_in_path = "upload/utilisateur/{$user_id}/{$category}/";
        $name = FileUpload::createFilename($file);

        //test si le dossier existe
        $isDir = $system->isDirectory(storage_path("app/public/{$file_in_path}"));
        if($isDir){       //si c'est un dossier 
            //vider le dossier en question
            $clean = $system->cleanDirectory(storage_path("app/public/{$file_in_path}"));
            //si ca s'est bien vidé
            if($clean){
                // dd($file_in_path);
                $file->move(storage_path("app/public/{$file_in_path}"), $name);
                return Storage::url($file_in_path.$name);
            }
       }else{
            //création du dossier si ca n'existe pas
            $dir = $system->makeDirectory(storage_path("app/public/upload/utilisateur/{$user_id}/{$category}/"), 0777, true, true);
            if($dir){//si créé
                //vider le dossier en question
                $clean = $system->cleanDirectory(storage_path("app/public/{$file_in_path}"));
                //si ca s'est bien vidé
                if($clean){
                    $file->move(storage_path("app/public/{$file_in_path}"), $name);
                    return Storage::url($file_in_path.$name);
                }
            }

       }
        
        return "error";
    }

    /**
     * function pour définir le nom d'un utilisateur
     * 
     * @param $file qui est le fichier à uploader
     * 
     * @return 
     */
    public static function createFilename(UploadedFile $file){
        return implode([
            time(),
            mt_rand(100,999),
            '.',
            $file->getClientOriginalExtension()
        ]);
    }


}