<?php

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Helpers\ApiResponse;
use Modules\Auth\Services\UserService;
use Modules\Auth\Http\Requests\UserRequest;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Transformers\Api\UserResource;

class UserController extends Controller
{


    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Register a new user and send OTP for email verification.
     *
     * This method uses:
     * - A custom Form Request class for validating input data
     * - A Repository class to handle database updates
     * - A Resource class to format the response
     * - A Trait for handling and syncing media (such as profile images)
     *
     */
    public function register(UserRequest $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validated();

            // Get user image if uploaded
            $image = $request->hasFile('image') ? $request->file('image') : null;

            // Register user using the dedicated service layer
            $user = $this->userService->registerUser($validatedData, $image);

            // Return success response with user email
            return ApiResponse::success($user->email);
        } catch (\Exception $e) {
            // Rollback and return error response
            return ApiResponse::error(419, $e->getMessage(), $e->getMessage());
        }
    }


    /**
     * Authenticate the user using email and password.
     */
    public function login(Request $request)
    {
        try {
            $data = $this->userService->loginUser($request->only('email', 'password'));

            return ApiResponse::success([
                'user' => new UserResource($data['user']),
                'token' => $data['token'],
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error(422, $e->getMessage(), $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error(500, $e->getMessage());
        }
    }


    /**
     * Update the authenticated user's profile information.
     *
     * It updates the user's name, email, and profile image if provided.
     */

    public function updateProfile(UserRequest $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validated();

            // Get the uploaded image if provided
            $image = $request->hasFile('image') ? $request->file('image') : null;

            // Use the service to update user profile and sync image if available
            $user = $this->userService->updateProfile(Auth::user(), $validatedData, $image);

            // Return success response with updated user resource
            return ApiResponse::success(new UserResource($user));
        } catch (\Exception $e) {
            // Rollback and return error response in case of exception
            return ApiResponse::error(419, $e->getMessage(), $e->getMessage());
        }
    }


    /**
     * Log out the authenticated user by revoking the current access token.
     *
     * This method uses Passport to revoke the current token of the user,
     * ensuring that the token cannot be reused.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Get the current user's token and revoke it
            $request->user()->token()->revoke();

            return ApiResponse::success(null, 200, 'Logged out successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error(500, $e->getMessage(), 'Logout failed.');
        }
    }
}
