<?php


namespace App\CustomModels;

use App\Models\Document;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class FileManagementMethods{

    /**
     * fonction permettant de retourner la taille d'un dossier
     */
    public static function folderSize($dir){
        $path = opendir($dir);
        $size = 0;

        while($folder = readdir($path)){
            if($folder != ".." && $folder != '.'){
                if(is_dir($dir.'/'.$folder)) $size += FileManagementMethods::folderSize($dir.'/'.$folder);

                else $size += filesize($dir.'/'.$folder);
            }
        }

        closedir($path);
        return $size;
    }

    /**
     * ajouter un fichier à une association
     * @param $file qui est le fichier
     * @param $assocId qui est l'id de l'association
     * @param $type qui est le type de fichier à sauvegarder 
     */
    public static function addAssociationFile(UploadedFile $file, $assocId, $intitule, $description, $type){


        try {
            $file_uploaded = FileUpload::associationFileUpload($file, $assocId, $type);

            if($file_uploaded != "error"){
    
               if($file_uploaded != "no space"){
                    $datactu = Carbon::now();
                    $datactu = strtotime($datactu);
        
                    $document = Document::create([
                        "create_at"=> $datactu,
                        "intitule" => $intitule,
                        "description" => $description,
                        "associations_id" => $assocId,
                        "path" => url($file_uploaded)
                    ]);
        
                    $success['status'] = "OK";
                    $success['data'] = $document;
        
                    return $success;
               }else{
                $err['errNo'] = 18;
                $err['errMsg'] = "plus d'espace de données";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
               }

            }else{
                $err['errNo'] = 11;
                $err['errMsg'] = "erreur de sauvegarde du fichier";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
    
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }

    public static function addAssociationCustomFile($path, $assocId, $intitule, $description, $type){


        try {

            $datactu = Carbon::now();
            $datactu = strtotime($datactu);

            $document = Document::create([
                "create_at"=> $datactu,
                "intitule" => $intitule,
                "description" => $description,
                "associations_id" => $assocId,
                "path" => url($path)
            ]);

            $success['status'] = "OK";
            $success['data'] = $document;

            return $success;
    
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
       
    }


    /**
     * récupérer tout les documents d'une association
     * 
     * 
     * @param $assocId qui est l'association en question
     */
   public static function getAllAssociationFiles($assocId){

        $files = Document::where("associations_id", $assocId)->get();
        
        $success['status'] = "OK";
        $success['data'] = $files;

        return $success;
   }

   /**
    * récupération d'un fichierpar son id dans la base de donnée
    */
   public static function getDocumentFile($id){

    $file = Document::where("id", $id)->first();
    if($file){

        $success['status'] = "OK";
        $success['data'] = $file;
    
        return $success;
    }else{

    $success['status'] = "NOK";
    $success['data'] = "not found";

    return $success;
    }

   }

   /**
    * download file
    */
   public static function downloadFile($id){
    $system = new Filesystem();
    $doc = Document::where('id', $id)->first();
    if($doc){
        try {
            $path = $doc->path;
            $path = parse_url($path);
            $path = $path['path'];
            $del = "";
            $file = $system->exists(public_path($path));
            if($file){

                $success['status'] = "OK";
                $success['data']['file'] = public_path($path);
                $success['data']['name'] = strrchr($path, '/');
                return $success;
               
            }else{
                $err['errNo'] = 15;
                $err['errMsg'] = 'le document n\'existe pas';
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }
           
        } catch (\Exception $e) {
            $err['errNo'] = 10;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
        
    }else{
        $err['errNo'] = 15;
        $err['errMsg'] = 'document doesn\'t exist';
        $error['status'] = 'NOK';
        $error['data'] = $err;

        return $error;
    }

   }

   /**
    * suppression d'un document
    */
   public static function deleteFile($docId){
        $system = new Filesystem();
        $doc = Document::where('id', $docId)->first();
        if($doc){
            try {
                $path = $doc->path;
                $path = parse_url($path);
                $path = $path['path'];
                $del = "";
                $file = $system->exists(public_path($path));
                if($file){
                    // system("rm ".public_path($path), $del);
                    
                    if($system->delete(public_path($path))){
                        $doc->delete();
                        $success['status'] = "OK";
                        $success['data'] = "suppression réussi";
                        return $success;
                    }else{
                        $err['errNo'] = 9;
                        $err['errMsg'] = 'suppression echoué';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
    
                        return $error;
                    }
                }else{
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'le document n\'existe pas';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;

                    return $error;
                }
               
            } catch (\Exception $e) {
                $err['errNo'] = 10;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
            
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'document doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
   }

   /**
    * create array with file type csv
    */
   public static function csvToArray($filename = '', $delimiter = ',')
   {
       if (!file_exists($filename) || !is_readable($filename))
       return false;

       $header = array();
       $data = array();
       if (($handle = fopen($filename, 'r')) !== false)
       {
           while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
           {
               if (empty($header))
                   $header = $row;
               else
                {
                    array_splice($row, count($header));
                    $data[] = array_combine($header, $row);
                }
           }
           fclose($handle);
       }

       return $data;
   }


   /**
    * add size to association
    */
   public static function addSizeToAssociation($assocId, $size){
       $association = AssociationMethods::getById($assocId);
       if($association == "not found"){
            $err['errNo'] = 15;
            $err['errMsg'] = 'association not found';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
       }

       try {
           
            $max_size = $association->max_size + $size;
            $association->fill([
                "max_size" => $max_size
            ]);
            $association->save();

            $success['status'] = "OK";
            $success['data'] = "successfull updated";

            return $success;
        
        } catch(\Exception $e){
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
   }
 

}