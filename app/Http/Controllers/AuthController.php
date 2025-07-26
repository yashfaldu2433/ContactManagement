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

            // REGISTER USER
            User::create($postData);

            // ALL GOOD SO RETURN THE RESPONSE
            return $this->jsonResponseService->sendResponse(
                true,
                null,
                __('api-message.USER_REGISTERED_SUCCESS_MESSAGE'),
                200
            );
        } catch (\Exception $e) {
            // LOG ERROR MESSAGE
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('api-message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }

    /**
     * login user.
     */
    public function login(Login $request)
    {
        try {
            $email = $request->input('email');

            // GET USER DETAIL
            $user = User::where('email', $email)->first();

            // USER DOESN'T EXISTS IN OUR SYSTEM
            if (!$user) {
                return $this->jsonResponseService->sendResponse(false, null, __('api-message.USER_DOES_NOT_EXIST_WITH_THIS_EMAIL'), 404);
            }

            // USER IS INACTIVE
            if ($user->status == config('constant.INACTIVE_STATUS')) {
                return $this->jsonResponseService->sendResponse(false, null, __('api-message.USER_NOT_ACTIVE_MESSAGE'), 401);
            }

            // AUTHORIZE USER CREDENTIALS
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                // INVALID CREDENTIALS FOUND
                return $this->jsonResponseService->sendResponse(false, null, __('api-message.INVALID_CREDENTIALS'), 422);
            }

            $token = $user->createToken('api_token', ['*']);

            // ALL GOOD SO RETURN THE RESPONSE
            return $this->jsonResponseService->sendResponse(
                true,
                [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'bearer',
                ],
                __('api-message.USER_LOGGED_IN_SUCCESSFULLY')
            );
        } catch (\Exception $e) {
            // LOG ERROR MESSAGE
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('api-message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }

    /**
     * logout user.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            // ALL GOOD SO RETURN THE RESPONSE
            return $this->jsonResponseService->sendResponse(true, null, __('api-message.USER_LOG_OUT_SUCCESSFULLY'));
        } catch (\Exception $e) {
            // LOG ERROR MESSAGE
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return $this->jsonResponseService->sendResponse(false, null, __('api-message.DEFAULT_ERROR_MESSAGE'), 500);
        }
    }
}
