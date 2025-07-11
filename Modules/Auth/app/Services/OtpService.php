<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\User;
use Modules\Auth\Emails\OtpMail;
use Illuminate\Support\Facades\Mail;

class OtpService
{

    public function generateAndSend(User $user): void
    {
        $otp = $user->createOneTimePassword()->password;
        Mail::to($user->email)->queue(new OtpMail($otp));
    }
}
