<?php

namespace App\Http\Controllers;

use App\CustomModels\FileUpload;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\OauthRefreshToken;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\CustomModels\UserMethods;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class UserController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = Client::where('name', 'password')->first();
    }

    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function register(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|unique:utilisateurs|regex:/^(\+)[1-9]{1}[0-9]{3,17}$/',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        //Returns an error if a field is not filled
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        //retour depuis la function de register
        return UserMethods::register($request->all());
    }

    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function login(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        //Returns an error if a field is not filled
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        // appel de la fonction de login
        return UserMethods::login($request);
    }

    /** 
     * Social Register api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function registerSocial(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'email' => 'required|string|unique:utilisateurs',
            'source' => 'required|string',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return UserMethods::registerSocial($request);
    }

    /** 
     * Social Login API 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function loginSocial(Request $request)
    {
        // check if all fields are filled
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'source' => 'required',
        ]);

        //Returns an error if a field is not filled
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return UserMethods::loginSocial($request);
    }


    /** 
     * refres token api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function refreshToken(Request $request)
    {

        $this->validate($request, [
            'refresh_token' => 'required'
        ]);
        return UserMethods::refreshToken($request);
    }

    /** 
     * logout api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function logout(Request $request)
    {
        // Verifier si l'id de l'utilisateur est fourni 
        $validator = Validator::make($request->all(), [
            'userId' => 'required'
        ]);

        //Returns an error if a field is not filled
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return UserMethods::logout($request);
    }

    /** 
     * activate user api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function signupActivate($token)
    {
        return UserMethods::signupActivate($token);
    }

    /**
     * Get user by id
     * @param User $id
     * @return User
     */
    public function getUserById(Request $request)
    {
        return UserMethods::getUserById($request);
    }

    /**
     * Change password
     * @param $userID
     * @param $oldpwd
     * @param $newpwd
     * @return User
     */
    public function changePwd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID' => 'required',
            'oldpwd' => 'required',
            'newpwd' => 'required',
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return UserMethods::changePwd($request);
    }

    /**
     * Recover password
     * @param $phone
     * @return User
     */
    public function recoverePwd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);
        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }

        return UserMethods::recoverPwd($request);
    }

    /** 
     * Update User api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $err['errNo'] = 10;
            $err['errMsg'] = implode(", ", $validator->errors()->all());
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
        try {

            return UserMethods::updateUser($request);
        } catch (\Exception $e) {
            $err['errNo'] = 12;
            $err['errMsg'] = $e->getMessage();
            $error['status'] = 'NOK';
            $error['data'] = $err;
            return response()->json($error, 400);
        }
    }

    /**
     * desactiver un utilisateur
     */
    public function desactivate($user)
    {
        $user = UserMethods::desactiver($user);

        if ($user['status'] == 'OK') {
            return response()->json($user, 202);
        } else if ($user['data']['errNo'] == 14) {
            return response()->json($user, 425);
        } else {
            return response()->json($user, 404);
        }
    }


    public function markAsRead($user_id, $notification_id)
    {
        $user = UserMethods::markNotificationAsRead($user_id, $notification_id);

        if ($user['status'] == 'OK') {
            return response()->json($user, 202);
        } else if ($user['data']['errNo'] == 15) {
            return response()->json($user, 404);
        } else {
            return response()->json($user, 500);
        }
    }

    public function markMultipleAsRead($user_id, Request $request)
    {
        $user = UserMethods::markNotificationsAsRead($user_id, \json_decode($request->input('notifications')));

        if ($user['status'] == 'OK') {
            return response()->json($user, 202);
        } else if ($user['data']['errNo'] == 15) {
            return response()->json($user, 404);
        } else {
            return response()->json($user, 500);
        }
    }

    /**
     * resend code
     */
    public function resendCode(Request $request)
    {
        $user = UserMethods::resendCode($request->input('phone'));

        if ($user['status'] == 'OK') {
            return response()->json($user, 202);
        } else if ($user['data']['errNo'] == 13) {
            return response()->json($user, 400);
        } else {
            return response()->json($user, 500);
        }
    }

    public function upload(Request $request){
        $data = array();
        return response()->json($data, 200);
    }

    public function token() {
        return response()->json(auth()->user(), 200);
    }
}
