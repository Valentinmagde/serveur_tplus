<?php


namespace App\CustomModels;

use App\Models\Association;
use App\Models\Membre;
use App\Models\MembresHasUser;
use App\Models\Utilisateur;
use App\Models\Invitation;
use App\Models\Privilege;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class AssociationMethods
{

    public static function setAssociationsDefaultAWallets(){
        DB::beginTransaction();
        try {
            $associations = AssociationMethods::getAll();
            foreach ($associations as $key => $association) {
                if(!$association->a_wallets_id){
                    $wallet = array(
                        "solde" => 0,
                        "devise" => $association->devise,
                        "nom" => "$association->nom - Wallet",
                        "description" => "",
                        "etat" => "init",
                        "type" => "a_wallet"
                    );
                    $wallet = WalletsMethods::storeAWallet($wallet, "a-wallet");
                    
                    if ($wallet['status'] == "OK"){
                        $association->a_wallets_id = $wallet['data']['a-wallet']['id'];
                        $association->save();
                    }else{
                        DB::rollback();
                        $err['errNo'] = $wallet['data']['errNo'];
                        $err['errMsg'] = $wallet['data']['errMsg'];
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500);
                    }
                }
            }

            DB::commit();
            $data = array(
                "status" => "OK",
                "data" => "successfull"
            );
            return response()->json($data, 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
       
    }

    public static function getAll()
    {

        $assoc = Association::all();

        return $assoc;
    }
    /**
     * function pour la récupération d'une association à partir de son ID
     * @param $id qui est l'id de l'association
     * 
     * 
     */
    public static function getById($id)
    {

        $assoc = Association::where('id', $id)->first();

        if ($assoc) {
            return $assoc;
        } else {
            return "not found";
        }
    }

    /**
     * récupérer la taille maximum qu'une association peut utiliser
     */
    public static function getMaxSize($assocId)
    {
        $max = Association::where('id', $assocId)
            ->select('max_size')
            ->first();
        if ($max) {
            return $max;
        } else {
            return "not found";
        }
    }


    /**
     * récupérer les associations d'un admin
     */
    public static function getAdminAssociation($admin_id)
    {
        $ass = Association::all()->where('admin_id', $admin_id);
        $success['status'] = 'OK';
        $success['data'] =  $ass;

        return $success;
    }

    /**
     * simple suppression d'une association en BD
     */
    public static function deleteSingleAssocitation($id)
    {
        return Association::find($id)->delete();
    }

    /**
     * creation d'une association
     */
    public static function createAssociation($request, $receiver)
    {

        DB::beginTransaction();

        try {
            $input = $request->all();
            $admin_id = $input['admin_id'];
            // receive the file
            $save = $receiver->receive();

            $input['max_size'] = Configuration::max_size_association_space();
            // check if the upload has finished (in chunk mode it will send smaller files)


            //date actuelle
            $datactu = Carbon::now();

            //transformer cette date en entier
            $datactu = strtotime($datactu);

            $input['create_at'] = $datactu;
            $wallet = array(
                "solde" => 0,
                "devise" => $input['devise'],
                "nom" => "{$input['nom']} - Wallet",
                "description" => "",
                "etat" => "init",
                "type" => "a_wallet"
            );
            $wallet = WalletsMethods::storeAWallet($wallet, "a-wallet");
            
            if ($wallet['status'] == "OK") $input['a_wallets_id'] = $wallet['data']['a-wallet']['id'];
            else {
                DB::rollback();
                return response()->json($wallet, 500);
            }

            if ($save !== false && $save->isFinished()) {

                $handler = $save->handler();
                //Create association
                $association = Association::create($input);

                // save the file and return any response you need
                $logo = FileUpload::associationFileUpload($save->getFile(), $association->id, "logo");
                if ($logo != "no space" && $logo != "error" && $logo != "folder creation failed" && $logo != "folder cleaning failed") {
                    $association->fill(['logo' => url($logo)]);
                    $association->save();
                } else {
                    DB::rollback();
                    $err['errNo'] = 11;
                    $err['errMsg'] = $logo;
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
                $user = UserMethods::getById($admin_id);
                $activite = [
                    "type" => "caisse",
                    "nom" => "Administration",
                    "etat" => "actif",
                    "created_by" => $user->firstName,
                    "taux_penalite" => 0,
                    "gestion_automatique_avoir" => 0,
                    "description" => "Gestion administrative de l'association",
                    'methode_decaissement' => "collectif"
                ];

                $activity = ActiviteMethods::createActivity($activite, $association->id);

                if ($activity['status'] == 'OK') {

                    DB::commit();
                    $success['status'] = 'OK';
                    $success['data'] =  $association;

                    return response()->json($success, 201);
                } else {

                    DB::rollback();
                    return response()->json($activity, 500);
                }
            } else {

                //Create association
                $association = Association::create($input);
                $user = UserMethods::getById($admin_id);
                $activite = [
                    "type" => "caisse",
                    "nom" => "Administration",
                    "etat" => "actif",
                    "created_by" => $user->firstName,
                    "taux_penalite" => 0,
                    "gestion_automatique_avoir" => 0,
                    "description" => "Gestion administrative de l'association",
                    'methode_decaissement' => "collectif"
                ];

                $activity = ActiviteMethods::createActivity($activite, $association->id);

                if ($activity['status'] == 'OK') {

                    DB::commit();
                    $success['status'] = 'OK';
                    $success['data'] =  $association;

                    return response()->json($success, 201);
                } else {

                    DB::rollback();
                    return response()->json($activity, 500);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * suppression d'une association
     */
    public static function deleteAssociation($request)
    {

        //Selectionner l'association en base par rapport à l'id saisi
        $assoc = AssociationMethods::getById($request->assocId);
        //Verfier si l'association existe
        if ($assoc == "not found") {

            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            //Verifier si l'admin est celui qui a l'identifiant {$request->adminId}
            if ($assoc->admin_id == $request->adminId) {
                //Recuperation de tous les membres de l'association s'ils existe
                $member = MembreMethods::getMemberAssociation($request->assocId);
                if ($member != "not found") {
                    foreach ($member as $value) {
                        //Suppression de la connexion entre les membres et les utilisateurs
                        MembresHasUserMethods::deleteMemberUserLink($value->id);
                        //Suppression du compte membre
                        MembreMethods::deleteMember($value->id);
                    }
                }
                //Suppression des cycle de l'association s'il en existe
                $cycle = CycleMethods::deleteSingleAssociationCycle($request->assocId);

                //Suppression de l'image physique de l'association
                if ($assoc->logo) {
                    $system = new FileSystem();
                    $path = $assoc->logo;
                    $path = parse_url($path);
                    $path = $path['path'];
                    $del = "";
                    $file = $system->exists(public_path($path));
                    if ($file) {
                        // system("rm ".public_path($path), $del);
                        $system->delete(public_path($path));
                    }
                }
                //Suppression de l'association
                $ass = AssociationMethods::deleteSingleAssocitation($request->assocId);
                if ($ass) {
                    $success['status'] = 'OK';
                    $success['data'] = 'The association was deleted successfully.';

                    return response()->json($success, 203);
                }
            } else {
                $err['errNo'] = 14;
                $err['errMsg'] = 'You do not have the right to delete this association';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        }
    }

    /**
     * récupération d'un membre de l'association
     */
    public static function getAssociationMemberById($id)
    {

        $memberHasUser = MembresHasUserMethods::getByUserIdMembers($id);
        $associations = [];
        $ass = [];
        $nombre = [];

        if ($memberHasUser == "not found") {
            $success['status'] = 'OK';
            $success['data'] = [];
            return response()->json($success, 200);
        }

        foreach ($memberHasUser as $key => $mh) {
            $member = MembreMethods::getById($mh->membres_id);
            if ($member != "not found") {
                $association = AssociationMethods::getById($member->associations_id);
                if ($association != "not found") {
                    $cycle = CycleMethods::checkActifCycle($association->id);
                    if ($cycle != "not found") {
                        $association['cycle'] = $cycle;
                        $ags = AgMethods::getByIdCycle($cycle->id);
                        if ($ags != "not found") {
                            $association['ags'] = $ags;
                        }
                    }
                    $wallet = WalletsMethods::getWalletByAWallet($association['a_wallets_id'] ?? 0);
                    if ($wallet != "not found") $association['wallet'] = $wallet;
                    $association['nombre'] = AssociationMethods::associationCountMembers($member->associations_id);
                    $association['membres_wallets_id'] = $member->default_u_wallets_id;
                    $association['jitsi_room'] = RapportMethods::getJitsiRoomByMemberId($member->id);
                    $associations[] = $association;
                }
            }
        }

        /*    if($memberHasUser != "not found"){
                foreach ($memberHasUser as $value){
                    $member = MembreMethods::getMember($value->membres_id);
                    if($member != "not found"){
                        foreach ($member as $value){ 
                            $association = AssociationMethods::getById($value->associations_id);

                            if($association != "not found"){
                                $cycle = CycleMethods::checkActifCycle($association->id);
                                if($cycle != "not found"){
                                    $association['cycle'] = $cycle;
                                    $ags = AgMethods::getByIdCycle($cycle->id);
                                    if($ags != "not found"){
                                        $association['ags'] = $ags;
                                    }
                                }
                                $associations[] = $association;
                            }

                            $assMem = MembreMethods::getMemberAssociation($value->associations_id);

                            if($assMem != "not found")  $nombre[] = count($assMem);
                        }
                    }
                    
                }
            }

            $incI = 0;
            foreach($associations AS $arrKey => $arrData){
                $ass[$incI]['id'] = $arrKey;
                $ass[$incI] = $arrData;
                $ass[$incI]['nombre'] = $nombre[$incI];
            
                $incI++;
            } 
        */

        $success['status'] = 'OK';
        $success['data'] =  $associations;
        return response()->json($success, 200);
    }

    /**
     * 
     */
    public static function getUser($request)
    {
        $association = AssociationMethods::getById($request->assocId);
        //Verifier si l'association existe
        if ($association != "not found") {
            //Selectionner toutes lecconnections
            $memberHasUser = MembresHasUserMethods::getByMemberIdMembers($request->membreId);
            //Verifier qu'il existe de connection
            if ($memberHasUser != "not found") {
                $users = [];
                foreach ($memberHasUser as $value) {
                    $user = UserMethods::getById($value->utilisateurs_id);

                    if ($users != "not found") $users[] = $user;
                }

                $success['status'] = 'OK';
                $success['data'] =  $users;
                return response()->json($success, 200);
            } else {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Member doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }
    }

    /**
     * 
     */

    public static function connect($request)
    {
        $association = Association::where('id', $request->assocID)->first();

        if ($association->admin_id == $request->admin_id) {
            $input['utilisateurs_id'] = $request->user_id;
            $input['membres_id'] = $request->id;


            $membersAssoc = MembreMethods::getMemberAssociation($request->assocID);
            $count = 0;

            foreach ($membersAssoc as $key => $value) {

                if ($membreHas = MembresHasUser::where([
                    ['utilisateurs_id', $request->user_id],
                    ['membres_id', $value->id]
                ])->first()) {
                    $count += 1;
                }
            }

            //Verifier que cet utilisateur ne pas déjà connecté
            if ($count > 0) {
                $err['errNo'] = 14;
                $err['errMsg'] = 'This user is already logged in to an account.';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }

            $membreHas = MembresHasUser::create($input);

            //Mise à jour de l'état du compte membre
            $member = Membre::find($request->id);
            $member->fill([
                'etat' => 'connect',
            ]);
            $member->save();

            //formatage de message de confirmation
            $success['status'] = 'OK';
            $success['data'] =  $membreHas;

            return response()->json($success, 201);
        } else {
            $err['errNo'] = 14;
            $err['errMsg'] = 'Unauthorized adminitrator';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 401);
        }
    }

    public static function connectFunc($request)
    {
        $association = Association::where('id', $request->assocID)->first();

        if ($association->admin_id == $request->admin_id) {
            $input['utilisateurs_id'] = $request->user_id;
            $input['membres_id'] = $request->id;


            $membersAssoc = MembreMethods::getMemberAssociation($request->assocID);

            $count = 0;

            foreach ($membersAssoc as $key => $value) {

                if ($membreHas = MembresHasUser::where([
                    ['utilisateurs_id', $request->user_id],
                    ['membres_id', $value->id]
                ])->first()) {
                    $count += 1;
                }
            }

            //Verifier que cet utilisateur ne pas déjà connecté
            if ($count > 0) {
                $err['errNo'] = 14;
                $err['errMsg'] = 'This user is already logged in to an account.';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }

            $membreHas = MembresHasUser::create($input);

            //Mise à jour de l'état du compte membre
            $member = Membre::find($request->id);
            $member->fill([
                'etat' => 'connect',
            ]);
            $member->save();

            //formatage de message de confirmation
            $success['status'] = 'OK';
            $success['data'] =  $membreHas;

            return $success;
        } else {
            $err['errNo'] = 14;
            $err['errMsg'] = 'Unauthorized adminitrator';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    public static function disconnect($request)
    {
        //Selection de l'association
        $association = Association::find($request->assocId);
        //Verifier si l'association existe
        if (!$association) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        //Verifier si le membre existe
        $member = Membre::find($request->id);
        if (!$member) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Account member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        DB::beginTransaction();
        try {
        
            $isAdmin = Privilege::where('associations_id', $request->assocId)->where("utilisateurs_id", $request->input('user_id'))->where("roles_id", 1)->first();
            $countAdmin = Privilege::where('associations_id', $request->assocId)->where('roles_id', 1)->count();
            if(($isAdmin && $countAdmin > 1) || !$isAdmin){
                //Deconnecte l'utilisateur de ce compte membre
                $res = MembresHasUser::where('membres_id', $member->id)
                    ->where('utilisateurs_id', $request->input('user_id'))
                    ->delete();

                $mh = MembresHasUser::where('membres_id', $member->id)->get();
                if (count($mh) == 0) {
                    $member->fill([
                        "etat" => "disconnect"
                    ]);
                    $member->save();
                }

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] = 'The association was deleted successfully.';

                return response()->json($success, 203);
            }else{
                DB::rollback();
                $err['errNo'] = 14;
                $err['errMsg'] = "vous ne pouvez pas quitter l'association";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }

        } catch (\Exception $e) {
            DB::rollback();
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }

        /*  if ($association->admin_id == $request->admin_id)
        {
            $input['utilisateurs_id'] = $request->user_id;
            $input['membres_id'] = $request->id;

            $membreHas = MembresHasUser::create($input);

            $success['status'] = 'OK'; 
            $success['data'] =  $membreHas;
            
            return response()->json($success,201);
        }else{
            $err['errNo'] = 15;
            $err['errMsg'] = 'Unauthorized adminitrator';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 401);    
        } */
    }

    public static function getAssociationById($request)
    {
        if (!$association = Association::find($request->id)) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }


        $success['status'] = 'OK';
        $success['data'] = $association;
        return response()->json($success, 200);
    }

    public static function updateAssociation($request)
    {

        try {
            //Verifier si l'association existe
            $association = Association::where('id', $request->id)->first();
            if (!$association) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist ';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }
            $param = $request->all();
            if ($request->file('file')) {
                $profil = FileUpload::associationFileUpload($request->file('file'), $association->id, "logo");

                if ($profil != "no space" && $profil != "error" && $profil != "folder creation failed" && $profil != "folder cleaning failed") {
                    $profil =  url($profil);
                    $param["logo"] = $profil;
                } else {
                    $err['errNo'] = 11;
                    $err['errMsg'] = $profil;
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
            }
            //Mise à jours des données de l'association
            $association->fill($param);
            //formatage de message de confirmation
            $association->save();
            $success['status'] = 'OK';
            $success['data'] =  $association;

            return response()->json($success, 202);
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    public static function rejoindreAssociation($request)
    {

        $invitation = Invitation::where('code', $request->input("code"))->first();
        if ($invitation) {

            $association = AssociationMethods::getById($invitation->associations_id);
            if ($association == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "association {$invitation->associations_id} doesn\'t exist ";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            $member = MembreMethods::hasAssociationAndGet($invitation->membres_id, $invitation->associations_id);
            if ($member == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "member {$invitation->membres_id} doesn\'t exist in association {$invitation->associations_id}";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            $user = UserMethods::getById($request->input('utilisateurs_id'));
            if ($user == "not found") {
                $err['errNo'] = 15;
                $err['errMsg'] = "user {$request->input('utilisateurs_id')} doesn\'t exist ";
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }
            DB::beginTransaction();
            try {
                $input = array();
                $input['utilisateurs_id'] = $request->input('utilisateurs_id');
                $input['membres_id'] = $invitation->membres_id;

                if (MembresHasUserMethods::userHasMember($request->input('utilisateurs_id'), $invitation->membres_id)) {
                    $err['errNo'] = 14;
                    $err['errMsg'] = 'This user is already connect to that member';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 400);
                }

                $membreHas = MembresHasUser::create($input);
                $member->fill([
                    'etat' => 'connect',
                ]);
                $member->save();
                $invitation->delete();

                DB::commit();
                $success['status'] = 'OK';
                $success['data'] =  $membreHas;

                return response()->json($success, 201);
            } catch (\Exception $e) {
                DB::rollback();
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        }

        $err['errNo'] = 15;
        $err['errMsg'] = "invitation with code {$request->input('code')} doesn\'t exist ";
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return response()->json($error, 404);

        /*  $decode = explode('|',base64_decode($request->code));
        Selection l'utilisateur en question
        if(isset($decode[3])){
            $user = Utilisateur::where('phone',$decode[3])->first();
            //Verifier si l'utilisateur existe
            if(!$user)
            {
                $err['errNo'] = 15;
                $err['errMsg'] = 'User doesn\'t exist ';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }else{
                //Connecter l'utilisateur au compte membre
                $request->request->add([
                    'assocID' => $decode[0],
                    'id' => $decode[1],
                    'admin_id' => $decode[2],
                    'user_id' => $user->id
                    ]);
                $connect = AssociationMethods::connectFunc($request);
                if($connect['status'] == "OK"){
                    //Formatage du message de confirmation
                    $success['status'] = 'OK';
                    $success['data'] = 'joinsuccessfully';
                    return response()->json($success, 200);
                }
                else{
                    $err['errNo'] = 14;
                    $err['errMsg'] = $connect['data']['errMsg'];
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 400);
                }
                
            } 
        }else{
            $err['errNo'] = 14;
            $err['errMsg'] = 'code incorrect';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        } */
    }

    public static function quitterAssociation($request)
    {
        try {
            // Verifier si l'association existe
            $association = Association::find($request->assocId);
            if (!$association) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t existe';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            } else {
                if ($association->admin_id == $request->admin_id) {
                    //Selectionner le compte membre correspondant à l'utilisateur
                    $member = Membre::where('associations_id', $request->assocId)->first();
                    //Selectionner de l'enregistrement membreHas correspondant au compte membre et le supprimer
                    $memberHasUser = MembresHasUser::where('membres_id', $member->id)->delete();
                    //Suppression du compte membre
                    $member->delete();

                    //Formatage du message de confirmation
                    $success['status'] = 'OK';
                    $success['data'] = 'Administration performed successfully';
                    return response()->json([$success, 203]);
                } else {
                    $err['errNo'] = 14;
                    $err['errMsg'] = 'Unauthorize';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;

                    return response()->json($error, 401);
                }
            }
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
    }

    public static function getAssociationMember($request)
    {
        // Verifier si l'association existe
        $association = Association::find($request->assocId);
        if (!$association) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t existe';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            //Selectionner tous les membres de l'association
            $member = Membre::where('associations_id', $request->assocId)->get();
            //Formatage du message de confirmation
            $success['status'] = 'OK';
            $success['data'] = $member;
            return response()->json($success, 200);
        }
    }

    /**
     * changer l'etat d'une association
     */
    public static function changeStateAsociation($assocId)
    {
        $assoc = AssociationMethods::getById($assocId);
        if ($assoc != "not found") {
            try {
                if ($assoc->etat != 0) {
                    $assoc->etat = 0;
                    MembreMethods::getByAssociationIdCreatedAndDesactive($assocId);
                } else {
                    $assoc->etat = 1;
                    MembreMethods::getByAssociationIdCreatedAndActive($assocId);
                }
                $assoc->save();

                $success['status'] = 'OK';
                $success['data'] = $assoc;

                return $success;
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return $error;
            }
        } else {

            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    public static function associationCountMembers($assoc_id)
    {
        $members = Membre::where('associations_id', $assoc_id)->get();
        return count($members);
    }
}
