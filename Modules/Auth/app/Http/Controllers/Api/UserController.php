<?php

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Services\OtpService;
use Modules\Core\Helpers\ApiResponse;
use Modules\Core\Traits\HasMediaSync;
use Modules\Auth\Http\Requests\UserRequest;
use Modules\Auth\Repositories\UserRepository;
use Modules\Auth\Transformers\Api\UserResource;

class UserController extends Controller
{
    use HasMediaSync;

    public function __construct(
        protected UserRepository $userRepository,
        protected OtpService $otpService
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
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Create new user
            $user = $this->userRepository->create($validatedData);

            // Upload profile image (if provided)
            if ($request->hasFile('image')) {
                $this->syncMedia(
                    $user,
                    $request->file('image'),
                    'avatars', // media collection name
                    false       // don't replace previous image
                );
            }
            
            // Generate and send OTP
            $this->otpService->generateAndSend($user);

            DB::commit();

            return ApiResponse::success($user->email);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(419, $e->getMessage(), $e->getMessage());
        }
    }

    /**
     * Authenticate the user using email and password.
     */
    public function login(Request $request)
    {
        try {
            // Try to find the user by email
            $user = $this->userRepository->findByEmail($request->email);

            // If user not found, return a specific message
            if (!$user) {
                return ApiResponse::error(404, 'No account found with this email address.');
            }

            // If password is incorrect, return a different message
            if (!Hash::check($request->password, $user->password)) {
                return ApiResponse::error(401, 'Incorrect password. Please try again.');
            }

            // Prevent login if email not verified
            if (! $user->hasVerifiedEmail()) {
                return ApiResponse::error(403, 'Email not verified. Please verify your email first.');
            }

            // Generate access token
            $token = $user->createToken('PassportToken')->accessToken;

            $data = (new UserResource($user))->toArray(request());
            $data['token'] = $token;

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error(500, $e->getMessage(), $e->getMessage());
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
            DB::beginTransaction();

            $validatedData = $request->validated();
            $user = $this->userRepository->update(Auth::user(), $validatedData);

            // If a new profile image is uploaded, sync it
            if ($request->hasFile('image')) {
                $this->syncMedia(
                    $user,
                    $request->file('image'),
                    'avatars', // media collection name
                    true       // replace previous image
                );
            }
            DB::commit();

            return ApiResponse::success(new UserResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
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
