<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Services\OtpService;
use Modules\Core\Traits\HasMediaSync;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Repositories\UserRepositoryInterface;

class UserService
{
    use HasMediaSync;
    protected $userRepository;
    protected $otpService;

    public function __construct(UserRepositoryInterface  $userRepository, OtpService $otpService)
    {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
    }

    /**
     * Handle user registration logic including:
     * - Password hashing
     * - Image upload
     * - OTP generation and sending
     *
     * @param array $data User registration data
     * @param \Illuminate\Http\UploadedFile|null $image Optional profile image
     * @return \Modules\Auth\Models\User
     */
    public function registerUser(array $data, $image = null)
    {
        return DB::transaction(function () use ($data, $image) {
            // Manually hash the password (instead of relying on mutator)
            $data['password'] = Hash::make($data['password']);

            // Create the user using the repository
            $user = $this->userRepository->create($data);

            // Upload profile image if provided
            if ($image) {
                $this->syncMedia(
                    $user,
                    $image,
                    'avatars', // media collection name
                    false       // don't replace previous image
                );
            }

            // Generate and send One-Time Password (OTP)
            $this->otpService->generateAndSend($user);

            return $user;
        });
    }

    /**
     * Handle user login process.
     *
     * This service method verifies the user's credentials (email and password),
     * ensures the email is verified, and generates a Passport access token if valid.
     *
     * @param array $credentials ['email' => ..., 'password' => ...]
     * @return array ['user' => User, 'token' => string]
     * @throws \Illuminate\Validation\ValidationException
     */
    public function loginUser(array $credentials): array
    {
        // Retrieve user by email
        $user = $this->userRepository->findByEmail($credentials['email']);

        // If user doesn't exist
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email address.']
            ]);
        }

        // If password is incorrect
        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password. Please try again.']
            ]);
        }

        // If email is not verified
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email not verified. Please verify your email first.']
            ]);
        }

        // Generate passport token
        $token = $user->createToken('PassportToken')->accessToken;

        // You can also add "token_type" or "expires_in" if needed
        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Update user profile data with optional avatar image.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  array  $data
     * @param  \Illuminate\Http\UploadedFile|null  $image
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateProfile($user, array $data, $image = null)
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $updatedUser = $this->userRepository->update($user, $data);

            if ($image) {
                $this->syncMedia(
                    $user,
                    $image,
                    'avatars', // media collection name
                    true       // replace previous image
                );
            }

            return $updatedUser;
        });
    }
}
