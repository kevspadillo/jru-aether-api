<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserStatuses;
use App\Models\UserTypes;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(RegisterRequest $Request)
    {
        $defaultUserType = UserTypes::where('is_default', 1)->first();
        $defaultUserStatus = UserStatuses::where('is_default', 1)->first();

        $user = $Request->validated();

        $user['status_id'] = $defaultUserStatus->id;
        $user['user_type_id'] = $defaultUserType->id;
        $user['password'] = Hash::make($user['password']);

        $User = User::create($user);
        
        $User->assignRole('user');

        $token = JWTAuth::fromUser($User);

        return $this->respondWithToken($token, $User->toArray());
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!($token = auth()->attempt($credentials))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        return $this->respondWithToken(
            auth()
                ->getToken()
                ->get()
        );
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $userData = null)
    {
        if (empty($userData)) {
            $userData = auth()
                ->user()
                ->load(['userType', 'userStatus', 'roles'])
                ->toArray();
        }

        $user = [
            'role' => $userData['roles'][0]['name'],
            'data' => [
                'displayName' => sprintf(
                    '%s %s',
                    $userData['firstname'],
                    $userData['lastname']
                ),
                'photoURL' => 'assets/images/avatars/profile.jpg',
                'email' => $userData['email'],
                'settings' => [
                    'layout' => [
                        'style' => 'layout1',
                        'config' => [
                            'mode' => 'fullwidth',
                            'scroll' => 'content',
                            'navbar' => [
                                'display' => true,
                                'folded' => true,
                                'position' => 'left',
                            ],
                            'toolbar' => [
                                'display' => true,
                                'position' => 'below',
                            ],
                            'footer' => [
                                'display' => false,
                                'style' => 'fixed',
                            ],
                        ],
                    ],
                    'customScrollbars' => true,
                    'theme' => [
                        'main' => 'greeny',
                        'navbar' => 'mainThemeDark',
                        'toolbar' => 'mainThemeDark',
                        'footer' => 'mainThemeDark',
                    ],
                ],
                'shortcuts' => ['calendar', 'mail', 'contacts', 'todo'],
            ],
        ];

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>
                auth()
                    ->factory()
                    ->getTTL() * 60,
        ]);
    }
}
