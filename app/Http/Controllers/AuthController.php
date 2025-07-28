<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\Login;
use App\Http\Requests\Auth\Register;
use App\Models\User;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $jsonResponseService;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct(
        JsonResponseService $jsonResponseService
    ) {
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * register new user.
     */
    public function register(Register $request)
    {
        try {
            $postData = $request->only('first_name', 'last_name', 'email', 'password');
            User::create($postData);
            return $this->jsonResponseService->sendResponse(
                true,
                null,
                __('message.USER_REGISTERED_SUCCESS_MESSAGE'),
                200
            );
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }

    /**
     * login user.
     */
    public function login(Login $request)
    {
        try {
            $email = $request->input('email');
            $user = User::where('email', $email)->first();
            if (!$user) {
                return $this->jsonResponseService->sendResponse(false, null, __('message.USER_DOES_NOT_EXIST_WITH_THIS_EMAIL'), 404);
            }
            if ($user->status == config('constant.INACTIVE_STATUS')) {
                return $this->jsonResponseService->sendResponse(false, null, __('message.USER_NOT_ACTIVE_MESSAGE'), 401);
            }

            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return $this->jsonResponseService->sendResponse(false, null, __('message.INVALID_CREDENTIALS'), 422);
            }

            $token = $user->createToken('api_token', ['*']);

            return $this->jsonResponseService->sendResponse(
                true,
                [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'bearer',
                ],
                __('message.USER_LOGGED_IN_SUCCESSFULLY')
            );
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }
}
