<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController
{
    public function auth(ServerRequestInterface $request)
    {
            $tokenResponse = parent::issueToken($request);
            $token = $tokenResponse->getContent();

            // $tokenInfo will contain the usual Laravel Passort token response.
            $tokenInfo = json_decode($token, true);

            // Then we just add the user to the response before returning it.
            $username = $request->getParsedBody()['email'];
            $user = Utilisateur::whereEmail($username)->first();
            $tokenInfo = collect($tokenInfo);
            $tokenInfo->put('user', $user);

            return $tokenInfo;
    }
}
