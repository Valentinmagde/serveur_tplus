<?php

namespace App\CustomModels;

use App\Models\Membre;
use App\Models\Association;
use App\CustomModels\SendInvitation;
use App\Models\CommentaireNouvelle;
use App\Models\Nouvelle;
use App\Models\MembresHasUser;
use App\Models\Privilege;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Models\Invitation;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;

class MembreMethods
{


    /**
     * vérifier si un membre fait partie d'une association
     */
    public static function hasAssociation($member, $assoc)
    {
        $member = Membre::where('id', $member)
            ->where('associations_id', $assoc)
            ->first();
        if ($member) {
            return true;
        }

        return false;
    }

    public static function hasAssociationAndGet($member, $assoc)
    {
        $member = Membre::where('id', $member)
            ->where('associations_id', $assoc)
            ->first();
        if ($member) {
            return $member;
        }

        return "not found";
    }

    public static function getById($id)
    {
        $user = Membre::where('id', $id)->first();
        if ($user) {
            return $user;
        }

        return "not found";
    }

    public static function getAll()
    {
        $user = Membre::all();
        return $user;
    }

    public static function getCountMemberAdded($previousAg)
    {
        $count = Membre::where('date_created', '>', $previousAg->date_ag)
                        ->where('date_created', '<=', DateMethods::getCurrentDateInt())
                        ->count();
        return $count;
    }

    public static function getByAssociationId($id)
    {
        $user = Membre::where('associations_id', $id)
            ->get();
        return $user;
    }

    public static function getByAssociationIdCreatedAndActive($id)
    {
        $members = Membre::where('associations_id', $id)
            ->get();
        foreach ($members as $key => $member) {

            $mh = MembresHasUser::where('membres_id', $member->id)->first();
            if ($mh && $member->etat != "connect") {
                $member->fill([
                    'etat' => 'connect'
                ]);
            } else if ($member->etat != "connect") {
                $member->fill([
                    'etat' => 'activate'
                ]);
            }
            $member->save();
        }
        return $members;
    }

    public static function getByAssociationIdCreatedAndDesactive($id)
    {
        $members = Membre::where('associations_id', $id)
            ->get();
        foreach ($members as $key => $member) {
            $member->fill([
                'etat' => 'desactivate'
            ]);
            $member->save();
        }
        return $members;
    }


    /**
     * changer l'état d'un membre
     */

    public static function setStateMember($id, $state)
    {

        $member = MembreMethods::getById($id);
        try {
            if ($member != "not found") {
                $member->fill(['etat' => $state]);
                $member->save();

                $success['status'] = 'OK';
                $success['data'] = $member;
                return $success;
            }

            $err['errNo'] = 15;
            $err['errMsg'] = "member {$member} doesn't exist";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }
    /**
     * Créer un compte apartir d'un formulaire
     * @return data
     */
    public static function createMember($request)
    {
        try {

            $input = $request->all();
            if (!$association = Association::find($request->assocId)) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Association doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            DB::beginTransaction();


            try {
                if (isset($input['id'])) {
                    //date actuelle
                    $datactu = Carbon::now();
                    //transformer cette date en entier
                    $datactu = strtotime($datactu);
                    $member = MembreMethods::getById($input['id']);
                    $input['update_at'] = $datactu;
                    //Mise à jours des données de l'association
                    if ($member != "nof found") {
                        $member->fill($input);
                        $member->save();

                        $success['status'] = 'OK';
                        $success['data'] =  $member;
                        DB::commit();
                        return response()->json($success, 202);
                    } else {
                        DB::rollback();
                        $err['errNo'] = 15;
                        $err['errMsg'] = 'member with id ' . $input['id'] . ' doesn\'t exist';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 404);
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

            //date actuelle
            $datactu = Carbon::now();
            //transformer cette date en entier
            $datactu = strtotime($datactu);
            // Vérifier si le nom du compte precedent est different de celui qu'on veut créer
            $memberHasUser = Membre::where([
                ['associations_id', $association->id],
                ['firstName', $request->firstName]
            ])->first();

            if (!$memberHasUser) {
                try {
                    $member = null;
                    //Mettre à jour le compte membre si la requête contient l'id du membre

                    $input['associations_id'] = $request->assocId;
                    $input['etat'] = 'init';
                    $input['date_created'] = $datactu;
                    $input['create_at'] = $datactu;

                    //Création du compte membre
                    $member = Membre::create($input);

                    $activity = ActiviteMethods::getActivitiesByAssociationsType($request->assocId, "caisse");
                    if ($member) {
                        if ($activity != "not found") {
                            $data = array(
                                "nombre_noms" => 1,
                                "montant_cotisation" => 0,
                                "solde" => 0,
                                "dette" => 0
                            );
                            $compte = CompteMethods::store($data, $member->id, $activity->id);
                            if ($compte['status'] == 'OK') {
                                DB::commit();
                                $success['status'] = 'OK';
                                $success['data'] =  $member;

                                return response()->json($success, 201);
                            } else {
                                DB::rollback();
                                return response()->json($compte, 500);
                            }
                        } else {
                            DB::rollback();
                            $err['errNo'] = 16;
                            $err['errMsg'] = 'activity doesn\'t exists.';
                            $error['status'] = 'NOK';
                            $error['data'] = $err;
                            return response()->json($error, 500);
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 16;
                        $err['errMsg'] = 'erreur de creation du membre';
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
            } else {
                DB::rollback();
                $err['errNo'] = 16;
                $err['errMsg'] = 'This account already exists.';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }


    /**
     * Créer plusieurs comptes apartir d'un formulaire
     * @return data
     */
    public static function createMembersMass($request)
    {
        //Verifier si l'association existe
        if (!$association = Association::find($request->assocId)) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        DB::beginTransaction();
        $members = [];
        foreach ($request->data as $key => $data) {
            // Vérifier si le nom du compte n'existe pas
            $compte = Membre::where('firstName', $data['firstName'])->first();
            if (!$compte && strlen($data['firstName']) > 0) {
                $input['associations_id'] = $request->assocId;
                $input['firstName'] = $data['firstName'];
                $input['phone'] = $data['phone'];
                $input['created_by'] = $request->created_by;
                $input['date_created'] = strtotime(Carbon::now());
                $input['etat'] = 'init';

                //Creation de compte
                $member = Membre::create($input);

                $activity = ActiviteMethods::getActivitiesByAssociationsType($request->assocId, "caisse");

                if ($activity != "not found") {
                    $data = array(
                        "nombre_noms" => 1,
                        "montant_cotisation" => 0,
                        "solde" => 0,
                        "dette" => 0
                    );
                    $comptes = CompteMethods::store($data, $member->id, $activity->id);
                    if ($comptes['status'] == 'OK') {
                        $members[] = $member;
                    } else {
                        DB::rollback();
                        return response()->json($comptes, 500);
                    }
                } else {
                    DB::rollback();
                    $err['errNo'] = 16;
                    $err['errMsg'] = 'activity doesn\'t exists.';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 500);
                }
            }
        }

        if (count($members) > 0) {
            DB::commit();
            $success['status'] = 'OK';
            $success['data'] =  $members;
            return response()->json($success, 201);
        } else {
            DB::rollback();
            $err['errNo'] = 16;
            $err['errMsg'] = 'No account has been created';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
    }


    /**
     * Créer plusieurs comptes apartir d'un fichier csv
     * @param UploadedFile $file
     * @return string
     */
    public static function createMembersCsv($request, $receiver)
    {
        //Verifier si l'association existe
        if (!$association = Association::find($request->assocId)) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        DB::beginTransaction();
        // receive the file
        $save = $receiver->receive();
        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save !== false && $save->isFinished()) {
            // save the file and return any response you need
            $handler = $save->handler();

            $csv_data = self::csvToArray($save->getFile());
            if ($csv_data) {
                $members = [];
                for ($i = 0; $i < count($csv_data); $i++) {
                    $row = implode(';', $csv_data[$i]);
                    $member = explode(';', $row);

                    if (isset($member[1])) $input['adresse'] = $member[1];
                    $input['associations_id'] = $request->assocId;
                    $input['firstName'] = $member[0];
                    $input['created_by'] = $request->created_by;
                    $input['date_created'] = strtotime(Carbon::now());
                    $input['etat'] = 'init';

                    //Creation de compte
                    $member = Membre::create($input);

                    $activity = ActiviteMethods::getActivitiesByAssociationsType($request->assocId, "caisse");

                    if ($activity != "not found") {
                        $data = array(
                            "nombre_noms" => 1,
                            "montant_cotisation" => 0,
                            "solde" => 0,
                            "dette" => 0
                        );
                        $comptes = CompteMethods::store($data, $member->id, $activity->id);
                        if ($comptes['status'] == 'OK') {
                            $members[] = $member;
                        } else {
                            DB::rollback();
                            return response()->json($comptes, 500);
                        }
                    } else {
                        DB::rollback();
                        $err['errNo'] = 16;
                        $err['errMsg'] = 'activity doesn\'t exists.';
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500);
                    }
                }

                if (count($members) > 0) {
                    DB::commit();
                    $success['status'] = 'OK';
                    $success['data'] =  $members;
                    return response()->json($success, 201);
                } else {
                    DB::rollback();
                    $err['errNo'] = 16;
                    $err['errMsg'] = 'No account has been created';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 400);
                }
            } else {
                DB::rollback();
                $err['errNo'] = 15;
                $err['errMsg'] = 'the file is empty';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 400);
            }
        } else {
            DB::rollback();
            $err['errNo'] = 10;
            $err['errMsg'] = 'the file is missing';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
    }

    protected static function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    /* $data[] = array_combine($header, $row); */
                    $data[] = $row;
            }
            fclose($handle);
        }

        return $data;
    }

    /*
     * Get member by id
     * @param Membre $id
     * @return Membre
     */
    public static function getMemberById($request)
    {
        if (!$member = Membre::find($request->id)) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $success['status'] = 'OK';
        $success['data'] =  $member;

        return response()->json($success, 200);
    }

    /**
     * Supprimer plusieurs comptes membre d'une association
     *  @param Association $id
     *  @param Member $id
     *  @return data
     */
    public static function deleteAssociationMembers($request)
    {
        //Selectionner l'association correspondant à assocId
        $association = Association::find($request->assocId);
        //Verifier si l'association existe
        if (!$association) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            $id = $request->id;
            for ($i = 0; $i < count($id); $i++) {
                //Selectionner le membre correspondant à l'id
                $member = Membre::find($id[$i]);

                //Verifier si le membre existe
                if (!$member) {
                    $err['errNo'] = 15;
                    $err['errMsg'] = 'Account doesn\'t exist';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;
                    return response()->json($error, 404);
                } else {
                    $memberhasid = MembresHasUser::where('membres_id', $member->id)->first();
                    if ($memberhasid)  $privilege = Privilege::where('membres_has_users_id', $memberhasid->id)->delete();
                    //Deconnecte l'utilisateur de ce compte membre s'il en existe
                    $res = MembresHasUser::where('membres_id', $member->id)->delete();
                    //Suppression le compte membre
                    $member->delete();
                }
            }

            $success['status'] = 'OK';
            $success['data'] = 'The accounts was deleted successfully.';

            return response()->json($success, 203);
        }
    }


    /**
     * Supprimer un compte membre d'une association
     *  @param Association $id
     * @param Member $id
     * @return data
     */
    public static  function deleteAssociationMember($request)
    {
        //Selectionner l'association correspondant à assocId
        $association = Association::find($request->assocId);
        //Verifier si l'association existe
        if (!$association) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Assocition doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            //Selectionner le membre correspondant à l'id
            $member = Membre::find($request->id);
            //Verifier si le membre existe
            if (!$member) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Account doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                $memberhasid = MembresHasUser::where('membres_id', $member->id)->first();
                //Supprimer ses nouvelles et leurs commentaires
                //Suppression des commentaires
                $comments = CommentaireNouvelle::where('membres_id', $member->id)->delete();
                //Suppression des nouvelles
                $news = Nouvelle::where('membres_id', $member->id)->delete();
                if ($memberhasid) $privilege = Privilege::where('membres_has_users_id', $memberhasid->id)->delete();
                //Deconnecte l'utilisateur de ce compte membre s'il en existe
                $res = MembresHasUser::where('membres_id', $member->id)->delete();
                //Suppression du compte membre
                $member->delete();

                $success['status'] = 'OK';
                $success['data'] = 'The account was deleted successfully.';

                return response()->json($success, 203);
            }
        }
    }

    /**
     * Inviter un Utilisateur à rejoindre un compte membre
     *  @param Association $id
     * @param Member $id
     * @return data
     */
    public static  function inviteUser($request)
    {
        //Verifier si l'association existe
        $association = Association::find($request->assocId);
        if (!$association) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'Association doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        } else {
            //Selectionner le membre correspondant à l'id
            $member = Membre::find($request->id);
            //Verifier si le membre existe
            if (!$member) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'Member doesn\'t exist';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            } else {
                //Verifier si l'admin est valide
                if ($association->admin_id == $request->admin_id) {
                    //Encodage du code pin
                    // $data = base64_encode($request->assocId.'|'.$request->id.'|'.$request->admin_id.'|'.$request->phone);
                    $code = rand(1111, 999999);

                    try {

                        $invitation = Invitation::where('membres_id', $request->id)
                                                ->where('associations_id', $request->assocId)
                                                ->count();
                        if($invitation > 0){
                            $success['status'] = 'OK';
                            $success['data'] = 'member is already invited';
        
                            return response()->json($success, 200);
                        }

                        $invitation = Invitation::create([
                            "membres_id" => $request->id,
                            "associations_id" => $request->assocId,
                            "code" => $code
                        ]);
                        //Envoyer l'invitation
                        SendInvitation::sendInvitation($request->phone, $association->nom, $code);
                        SendInvitation::sendChatApiInvitation($request->phone, $association->nom, $code);
                    } catch (\Exception $e) {
                        $err['errNo'] = 11;
                        $err['errMsg'] = $e->getMessage();
                        $error['status'] = 'NOK';
                        $error['data'] = $err;
                        return response()->json($error, 500);
                    }


                    //Formatage du message de confirmation
                    $success['status'] = 'OK';
                    $success['data'] = 'invitation send successfully';

                    return response()->json($success, 200);
                } else {
                    $err['errNo'] = 14;
                    $err['errMsg'] = 'Unauthorize';
                    $error['status'] = 'NOK';
                    $error['data'] = $err;

                    return response()->json($error, 401);
                }
            }
        }
    }
    /**
     * Get members from an association
     * @param Membre $id
     * @return Membre
     */
    public static function getMembers($request)
    {
        $membres = Membre::where('associations_id', $request->associationId)->get();

        $ret = array();
        foreach ($membres as $value) {
            $userIds = MembresHasUser::where('membres_id', $value->id)->get();
            $invitation = Invitation::where('associations_id', $request->associationId)->where('membres_id', $value->id)->first();
            $userArray = array();
            foreach ($userIds as $elt) {
                if ($item = Utilisateur::find($elt->utilisateurs_id)) {
                    $privileges = Privilege::where('membres_has_users_id', $elt->id)->get();
                    $roles = array();
                    foreach ($privileges as $key => $privilege) {
                        if ($privilege->associations_id == $request->associationId && $privilege->utilisateurs_id == $item->id) {
                            $role = Role::find($privilege->roles_id);
                            if ($role) {
                                $roles[] = $role->libelle;
                            }
                        }
                    }
                    $item['roles'] = $roles;
                    array_push($userArray, $item);
                }
            }
            $item = array(
                'id'                => $value->id,
                'associations_id'   => $value->associations_id,
                'firstName'         => $value->firstName,
                'lastName'          => $value->lastName,
                'phone'             => $value->phone,
                'date_created'      => $value->date_created,
                'etat'              => $value->etat,
                'create_by'         => $value->create_by,
                'adresse'           => $value->adresse,
                'code'              => $invitation->code ?? null,
                'default_u_wallets_id'              => $value->default_u_wallets_id,
                'users'             => $userArray,
            );
            array_push($ret, $item);
        }
        $result['status'] = 'OK';
        $result['data'] = $ret;

        return response()->json($result, 200);
    }

    /**
     * retourner tout les membres d'une association s'ils existent
     */
    public static function getMemberAssociation($id)
    {
        $member = Membre::where('associations_id', $id)->get();

        return $member;
    }

    public static function getMemberAssociationActif($id)
    {
        $member = Membre::select('id')->where('associations_id', $id)->where('etat', '!=', 'actif')->get();

        if ($member) {
            return $member;
        }

        return "not found";
    }


    /**
     * delete un compte membre
     */
    public static function deleteMember($id)
    {
        return Membre::where('id', $id)->delete();
    }

    public static function getMember($id)
    {
        $member = Membre::where('id', $id)->get();

        if ($member) {
            return $member;
        }

        return "not found";
    }

    public static function getStatistiqueMembre($member)
    {
        $comptes = CompteMethods::getComptesByMember($member);
        $ag = AgMethods::getNextAgDueDate($member);
        $data = array(
            "solde" => 0,
            "interet" => 0,
            "dettes" => 0,
            "avoirs" => 0,
            "dettes_cotisations" => 0,
            "dettes_acquitements" => 0,
            'decaissement' => 0,
            'encaissement' => 0,
            'details' => array(),
            'a_payer' => 0,
            'a_retirer' => 0
        );
        foreach ($comptes as $key => $compte) {
            $data['solde'] += $compte->solde;
            $data['interet'] += $compte->interet;
            $data['dettes'] += $compte->dette;
            $data['avoirs'] += $compte->avoir;
            $data['dettes_cotisations'] += $compte->dette_c;
            $data['dettes_acquitements'] += $compte->dette_a;
            if ($ag != "not found") {
                $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDate($member, $compte->id, $ag);
                $activite = ActiviteMethods::getActivityById($compte->activites_id);
                if ($activite != "not found") {
                    if ($activite->etat != "cloture") {
                        $activite["compte"] = $compte;
                        $act = $activite;
                        $echeanceArrEnc = array();
                        $echeanceArrDec = array();
                        foreach ($echeances as $key => $echeance) {
                            if ($echeance->etat != "cloture") {
                                if ($echeance->debit_credit != "decaissement") {
                                    $data['encaissement'] += ($echeance->montant - $echeance->montant_realise);
                                    $echeanceArrEnc[] = $echeance;
                                } else {
                                    $data['decaissement'] += ($echeance->montant - $echeance->montant_realise);
                                    $echeanceArrDec[] = $echeance;
                                }
                            }
                        }
                        if (count($echeanceArrDec) > 0) {
                            $act['echeances_decaissement'] = $echeanceArrDec;
                        }

                        if (count($echeanceArrEnc) > 0) {
                            $act['echeances_encaissement'] = $echeanceArrEnc;
                        }

                        $data['details'][] = $act;
                    }
                }
            }
            $data['encaissement'] += $compte->dette_c + $compte->dette_a;
            $data['a_payer'] = $data['encaissement'];
            $data['a_retirer'] = $data['decaissement'];
        }

        $success['status'] = "OK";
        $success['data'] = $data;
        return $success;
    }

    public static function getStatistiqueMembreActivity($member, $activite)
    {
        $compte = CompteMethods::getByIdMA($activite, $member);
        $ag = AgMethods::getNextAgDueDate($member);
        $data = array(
            "solde" => 0,
            "interet" => 0,
            "dettes" => 0,
            "avoirs" => 0,
            "dettes_cotisations" => 0,
            "dettes_acquitements" => 0,
            'decaissement' => 0,
            'encaissement' => 0,
            'details' => array(),
            'a_payer' => 0,
            'a_retirer' => 0
        );
        if ($compte != "not found") {
            $data['solde'] += $compte->solde;
            $data['interet'] += $compte->interet;
            $data['dettes'] += $compte->dette;
            $data['avoirs'] += $compte->avoir;
            $data['dettes_cotisations'] += $compte->dette_c;
            $data['dettes_acquitements'] += $compte->dette_a;
            if ($ag != "not found") {
                $echeances = EcheancesMethods::getNextEcheancesByMemberCompteDate($member, $compte->id, $ag);
                $activite = ActiviteMethods::getActivityById($compte->activites_id);
                if ($activite != "not found") {
                    if ($activite->etat != "cloture") {
                        $activite["compte"] = $compte;
                        $act = $activite;
                        $echeanceArrEnc = array();
                        $echeanceArrDec = array();
                        foreach ($echeances as $key => $echeance) {
                            if ($echeance->etat != "cloture") {
                                if ($echeance->debit_credit != "decaissement") {
                                    $data['encaissement'] += ($echeance->montant - $echeance->montant_realise);
                                    $echeanceArrEnc[] = $echeance;
                                } else {
                                    $data['decaissement'] += ($echeance->montant - $echeance->montant_realise);
                                    $echeanceArrDec[] = $echeance;
                                }
                            }
                        }

                        if (count($echeanceArrDec) > 0) {
                            $act['echeances_decaissement'] = $echeanceArrDec;
                        }

                        if (count($echeanceArrEnc) > 0) {
                            $act['echeances_encaissement'] = $echeanceArrEnc;
                        }

                        $data['details'][] = $act;
                    }
                }
            }
            $data['encaissement'] += $compte->dette_c + $compte->dette_a;
            $data['a_payer'] = $data['encaissement'];
            $data['a_retirer'] = $data['decaissement'];
        }

        $success['status'] = "OK";
        $success['data'] = $data;
        return $success;
    }

    public static function desactivate($id)
    {
        $member = Membre::find($id);
        if (!$member) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'member doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $member->fill(["etat" => "desactive"]);
        $member->save();

        return array(
            "status" => "OK",
            "data" => $member
        );
    }
}
