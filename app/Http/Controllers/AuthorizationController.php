<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthorizationController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function jwt(User $user)
    {
        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60
        ];
        return JWT::encode($payload, env('JWT_LOCAL_SECRET'), 'HS256');
    }

    public function authenticate(User $user)
    {
        $this->validate($this->request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $this->request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'error' => 403,
                'message' => 'Email does not exist.'
            ], 400);
        }

        if (Hash::check($this->request->input('password'), $user->password)) {
            $token = $this->jwt($user);
            $refreshToken = app('hash')->make(uniqid());
            $user->token = $refreshToken;
            $user->save();
            return response()->json([
                'token' => $token,
                'refreshToken' => $refreshToken,
            ], 200);
        }

        return response()->json([
            'error' => 403,
            'message' => 'Email or password is wrong.'
        ], 400);
    }

    public function refreshToken(User $user)
    {
        // $this->newUser();
        $this->validate($this->request, [
            'refreshToken' => 'required',
        ]);
        $user = User::where('token', $this->request->input('refreshToken'))->first();

        if (!$user) {
            return response()->json([
                'error' => 403,
                'message' => 'Refresh token is not valid.'
            ], 400);
        }

        if ($this->request->input('refreshToken') == $user->token) {
            $token = $this->jwt($user);
            $refreshToken = app('hash')->make(uniqid());
            $user->token = $refreshToken;
            $user->save();
            return response()->json([
                'token' => $token,
                'refreshToken' => $refreshToken,
            ], 200);
        }

        return response()->json([
            'error' => 403,
            'message' => 'Refresh token is not valid.'
        ], 400);
    }
    
}
