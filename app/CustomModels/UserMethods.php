<?php

namespace App\CustomModels;

use App\CustomModels\SendCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use App\CustomModels\SendPassword;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\OauthRefreshToken;
use DateTime;
use Illuminate\Notifications\DatabaseNotification;

use App\Notifications\userNotifications;

class UserMethods
{

    private $client;

    public static function getById($id)
    {
        $user = Utilisateur::find($id);
        if ($user) {
            return $user;
        }

        return "not found";
    }


    public static function resendCode($phone)
    {

        $usr = Utilisateur::where('phone', $phone)->first();
        if (!$usr) {
            $err['errNo'] = 13;
            $err['errMsg'] = 'Incorrect phone or password !!';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }


        try {
            $code = rand(1111,9999);
            SendCode::sendCode($phone, $code);
            SendCode::sendChatApiCode($phone, $code);

            $usr->fill(["code" => $code]);
            $usr->save();

            return array(
                "status" => "OK",
                "data" => $usr
            );
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }
    }

    /**
     * fonction de register d'un utilisateur
     * @param $request qui est l'objet de la requête contenant les données de la requête
     * @return $success en  cas de reussite de la requête
     * @return $error en cas d'érreur de la requête
     */
    public static function register($request)
    {

        try {
            //date actuelle
            $datactu = Carbon::now();

            //transformer cette date en entier
            $datactu = strtotime($datactu);

            //formatage des elements en entrée
            $input = $request;
            // dd($input['phone']);
            $code = rand(1111,9999);
            
            $input['password'] = bcrypt($input['password']);       //Password encryption
            $input['activation_token'] = str_random(60);           //Generation of the account activation token
            $input['email'] = $request['phone'] . '@tontine.plus';   //default email assignment
            $input['code'] = $code;
            $input['created_at'] = $datactu;

            SendCode::sendCode($input['phone'], $code);  //Sending account activation code
            SendCode::sendChatApiCode($input['phone'], $code);
            
            try {
                $user = Utilisateur::create($input); //Creation of the user in database
                $success['status'] = 'OK';
                $success['data'] =  $user;
                return response()->json($success, 201);
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * fonction qui permettra à un utilisateur de se connecter
     * @param $request l'objet contenant les données utilisateurs
     * @return $success si le login reussi
     * @return $error dans le cas d'une erreur
     */
    public static function login($request)
    {
        $client =  Client::where('name', 'password')->first();
        // dd($client);
        $credentials['phone'] = $request->get('phone');
        $credentials['password'] = $request->get('password');
        $credentials['deleted_at'] = null;

        $usr = Utilisateur::where('phone', $request->phone)->first();
        if (!Auth::attempt($credentials) || !$usr->email) //user authenticity check
        {
            $err['errNo'] = 13;
            $err['errMsg'] = 'Incorrect phone or password !!';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return response()->json($error, 400);
        }

        if ($usr && $usr->active == 0) {
            $err['errNo'] = 20;
            $err['errMsg'] = 'le compte existe mais il est inactif';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return response()->json($error, 423);
        }

        $params = [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $usr->email,
            'password' => request('password'),
            'scope' => '*'
        ];


        $request->request->add($params);
        $proxy = Request::create('oauth/token', 'POST');
        $donnee = Route::dispatch($proxy);
        $donnees = json_decode($donnee->content(), true);

        $user = $request->user();
        $now = time();




        $user['notifications'] = $user->unreadNotifications;

        $data['bearerData'] = $donnees['access_token'];
        $data['refresh_token'] = $donnees['refresh_token'];
        $data['token_type'] = $donnees['token_type'];
        $data['expires_in'] = $donnees['expires_in'] + $now;
        $data['user'] = $user;
        $data['appInfo'] = 'Logo';
        $success['status'] = 'OK';
        $success['data'] = $data;

        return response()->json($success, 200);
    }

    /**
     * souscription au service avec un réseau social
     * @param $request l'ensemble des données de souscription
     * 
     * @return $success dans le cas ou la souscription s'est bien déroulé
     * @return $error dans le cas ou la souscription a échoué
     */
    public static function registerSocial($request)
    {
        try {

            //date actuelle
            $datactu = Carbon::now();

            //transformer cette date en entier
            $datactu = strtotime($datactu);

            $input = $request->all();
            $input['active'] = 1;
            $input['created_at'] = $datactu;
            $input['password'] = bcrypt('x');
            $input['activation_token'] = str_random(60);

            try {
                $user = Utilisateur::create($input);
                $success['status'] = 'OK';
                $success['data'] =  $user;

                return response()->json($success, 201);
            } catch (\Exception $e) {
                $err['errNo'] = 11;
                $err['errMsg'] = $e->getMessage();
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 500);
            }
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * fonction qui permettra à un utilisateur de se connecter
     * @param $request l'objet contenant les données utilisateurs
     * @return $success si le login reussi
     * @return $error dans le cas d'une erreur
     */
    public static function loginSocial($request)
    {

        $client =  Client::where('name', 'password')->first();

        $credentials['email'] = $request->get('email');
        $credentials['source'] = $request->get('source');
        $credentials['password'] = 'x';
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;

        if (!Auth::attempt($credentials) || !$request->get('email')) {
            $err['errNo'] = 13;
            $err['errMsg'] = 'Incorrect email or source !!!!';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return response()->json($error, 400);
        }

        $params = [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $request->get('email'),
            'password' => $credentials['password'],
            'scope' => '*'
        ];

        try {
            $request->request->add($params);
            $proxy = Request::create('oauth/token', 'POST');
            $donnee = Route::dispatch($proxy);
            $donnees = json_decode($donnee->content(), true);

            $user = $request->user();

            $user['notifications'] = $user->unreadNotifications;
            $data['bearerData'] = $donnees['access_token'];
            $data['refresh_token'] = $donnees['refresh_token'];
            $data['token_type'] = $donnees['token_type'];
            $data['expires_in'] = $donnees['expires_in'];



            $data['user'] = $user;
            $data['appInfo'] = 'Logo';


            $success['status'] = 'OK';
            $success['data'] = $data;

            return response()->json($success, 200);
        } catch (\Exception $e) {
            // $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }

    /**
     * fonction pour refresh le token d'un utilisateur qui s'est expiré
     * @param $request qui est l'ensemble des données utilisateurs dont on a besoin
     * 
     * @return $success
     */
    public static function refreshToken($request)
    {
        $client =  Client::where('name', 'password')->first();

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $client->id,
            'client_secret' => $client->secret
        ];

        $request->request->add($params);

        $proxy = Request::create('oauth/token', 'POST');
        $donnee = Route::dispatch($proxy);
        $donnees = json_decode($donnee->content(), true);

        if (isset($donnees['access_token'])) {
            $data['bearerData'] = $donnees['access_token'];
            $data['refresh_token'] = $donnees['refresh_token'];
            $data['token_type'] = $donnees['token_type'];
            $data['expires_in'] = $donnees['expires_in'];
            $data['appInfo'] = 'Logo';
            $success['status'] = 'OK';
            $success['data'] = $data;

            return response()->json($success, 200);
        }

        $err['errNo'] = 14;
        $err['errMsg'] = $donnees['message'];
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return response()->json($error, 405);
    }

    public static function logout($request)
    {
        //Verifier si l'utisateur connecté est celui qui envoie la requete
        if (Auth::id() == $request->userId) {
            //Selectionnez le token de l'utlisateur actuel
            $accessToken = Auth::user()->token();

            //Mettre à jour le token en base
            OauthRefreshToken::where('access_token_id', $accessToken->id)
                ->update(['revoked' => true]);

            $accessToken->revoke();

            $success['status'] = 'OK';
            $success['data'] =  $accessToken;

            return response()->json($success, 200);
        } else {
            $err['errNo'] = 14;
            $err['errMsg'] = 'User isn\'t allowed';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 405);
        }
    }

    /**
     * desactiver un compte utilisateur
     */
    public static function desactiver($id)
    {
        if (Auth::id() == $id) {
            $user = UserMethods::getById($id);
            if ($user != "not found") {
                $user->fill(['active' => 0]);
                $user->save();

                $success['status'] = "OK";
                $success['data'] = $user;

                return $success;
            }

            $err['errNo'] = 15;
            $err['errMsg'] = 'User not found';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return $error;
        }

        $err['errNo'] = 14;
        $err['errMsg'] = 'User isn\'t allowed';
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return $error;
    }

    /**
     * récupérer les données d'un utilisateur par son ID qui est dans un champs de type request
     */
    public static function getUserById($request)
    {
        if (!$user = Utilisateur::find($request->id)) {
            $err['errNo'] = 15;
            $err['errMsg'] = 'user doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 404);
        }

        $user['notifications'] = $user->unreadNotifications;
        $success['status'] = 'OK';
        $success['data'] =  $user;
        return response()->json($success, 200);
    }

    /**
     * reset le password
     */
    public static function recoverPwd($request)
    {
        if ($user = Utilisateur::whereRaw('phone = ?', array($request->phone))->first()) {
            $user->password = bcrypt(SendPassword::sendPwd($request->phone));
            $user->save();

            $success['status'] = 'OK';
            $success['data'] =  $user;
            return response()->json($success, 200);
        }
        $err['errNo'] = 15;
        $err['errMsg'] = 'user doesn\'t exist';
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return response()->json($error, 404);
    }

    /**
     * change the password of the user
     */
    public static function changePwd($request)
    {
        //Verifier si le nouveau mot de passe est à l'ancien
        if ($request->oldpwd === $request->newpwd) {
            $err['errNo'] = 13;
            $err['errMsg'] = "The new password must be different from the old password";
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        if ($user = Utilisateur::whereRaw('id = ?', array($request->userID))->first()) {
            if (Hash::check($request->oldpwd, $user->password)) {
                // The old password matches the hash in the database
                $user->password = bcrypt($request->newpwd);
                $user->save();

                $success['status'] = 'OK';
                $success['data'] =  $user;
                return response()->json($success, 200);
            }
        }
        $err['errNo'] = 15;
        $err['errMsg'] = 'user doesn\'t exist';
        $error['status'] = 'NOK';
        $error['data'] = $err;
        return response()->json($error, 404);
    }

    /**
     * update d'un utilisateur 
     * 
     * @param $request l'ensemble des données à update
     * 
     */
    public static function updateUser($request)
    {
        // dd($request);
        try {
            //Verifier si l'utisateur connecté est celui qui envoie la requete
            if (Auth::id() != $request->id) {
                $err['errNo'] = 14;
                $err['errMsg'] = 'User isn\'t allowed';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 405);
            }

            $user = Utilisateur::where('id', $request->id)->first();
            if (!$user) {
                $err['errNo'] = 15;
                $err['errMsg'] = 'user doesn\'t exist ';
                $error['status'] = 'NOK';
                $error['data'] = $err;
                return response()->json($error, 404);
            }

            $param = $request->all();

            //test si les photos existent et si oui alors on les envois dans le serveur en appelant la classe FileUpload
            if ($request->file('photo_profil')) {
                $profil = FileUpload::userFileUpload($request->file("photo_profil"), $request->get("id"), "profil");
                if ($profil != "error") {
                    $profil =  url($profil);
                    $param["photo_profil"] = $profil;
                }
            }
            if ($request->file("photo_couverture")) {
                $couverture = FileUpload::userFileUpload($request->file("photo_couverture"), $request->get("id"), "couverture");
                if ($couverture != "error") {
                    $couverture = url($couverture);
                    $param["photo_couverture"] = $couverture;
                }
            }
            //Mise à jours des données de l'association
            $user->fill($param);
            $user->save();

            $success['status'] = 'OK';
            $success['data'] =  $user;

            return response()->json($success, 202);
        } catch (\Exception $e) {
            $err['errNo'] = 11;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 500);
        }
    }


    /**
     * fonction pour activer une connexion d'un utilisateur
     * 
     * @param $token le token de l'utilisateur
     * 
     * @return $user qui est activé
     */
    public static function signupActivate($token)
    {
        $user = Utilisateur::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return $user;
    }


    public static function markNotificationAsRead($user_id, $id_notification)
    {

        $user = UserMethods::getById($user_id);

        if ($user != "not found") {
            $read = false;
            $notification = $user->unreadNotifications->find($id_notification);
            if ($notification) {
                $notification->markAsRead();
                $read = true;
            }

            if ($read) {
                $success['status'] = "OK";
                $success['data'] = "read successfully";
                return $success;
            } else {
                $err['errNo'] = 10;
                $err['errMsg'] = 'erreur de lecture de la notification';
                $error['status'] = 'NOK';
                $error['data'] = $err;

                return $error;
            }
        } else {
            $err['errNo'] = 15;
            $err['errMsg'] = 'user doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }

    public static function markNotificationsAsRead($user_id, $ids_notification)
    {

        $user = UserMethods::getById($user_id);
        if ($user != "not found") {
            try {
                foreach ($ids_notification as $key => $id_notification) {
                    $notification = $user->unreadNotifications->find($id_notification);
                    if ($notification) {
                        $notification->markAsRead();
                    }
                }

                $success['status'] = "OK";
                $success['data'] = "read successfully";
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
            $err['errMsg'] = 'user doesn\'t exist';
            $error['status'] = 'NOK';
            $error['data'] = $err;

            return $error;
        }
    }
}
