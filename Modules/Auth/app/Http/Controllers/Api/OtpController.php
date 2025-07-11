<?php

namespace Modules\Auth\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Auth\Services\OtpService;
use Modules\Core\Helpers\ApiResponse;
use Modules\Auth\Http\Requests\OtpRequest;
use Modules\Auth\Repositories\UserRepository;
use Modules\Auth\Transformers\Api\UserResource;

class OtpController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository,
        protected OtpService $otpService
    ) {}

    /**
     * Verify OTP and authenticate the user.
     */
    public function verifyOtp(OtpRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $user = $this->userRepository->findByEmail($validatedData['email']);

            // Attempt OTP authentication
            $authed = $user->attemptLoginUsingOneTimePassword($validatedData['otp']);

            if (! $user || ! $authed) {
                return response()->json(['message' => 'Invalid or expired OTP'], 401);
            }

            // Mark user as verified
            $this->userRepository->verify($user);

            // Generate access token
            $token = $user->createToken('passportToken')->accessToken;

            $data = (new UserResource($user))->toArray(request());
            $data['token'] = $token;

            DB::commit();

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(419, $e->getMessage(), $e->getMessage());
        }
    }

    /**
     * Resend OTP to the user's email if not verified yet.
     */
    public function resendOtp(OtpRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $user = $this->userRepository->findByEmail($validatedData['email']);

            // Prevent resending if already verified
            if ($user->hasVerifiedEmail()) {
                return ApiResponse::error(409, 'Email already verified.');
            }

            // Send new OTP
            $this->otpService->generateAndSend($user);

            DB::commit();

            return ApiResponse::success($user->email);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(419, $e->getMessage(), $e->getMessage());
        }
    }
}
