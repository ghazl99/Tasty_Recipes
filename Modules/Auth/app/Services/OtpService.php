<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\User;
use Modules\Auth\Emails\OtpMail;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate a one-time password (OTP) for the user and send it via email.
     *
     * @param  \App\Models\User  $user  The user to generate the OTP for.
     * @return void
     */
    public function generateAndSend(User $user): void
    {
        // Generate a new one-time password and retrieve the value
        $otp = $user->createOneTimePassword()->password;

        // Send the OTP to the user's email using a queued mail job
        Mail::to($user->email)->queue(new OtpMail($otp));
    }
}
