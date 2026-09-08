<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Handle the deep link from the verification email.
     *
     * Deliberately does not require an active session: the signed URL plus
     * the email hash are enough proof, so the link works no matter which
     * device or browser the user opens it on.
     */
    public function verify(Request $request, string $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            return redirect('/email/verify?status=invalid');
        }

        $user = User::find($id);

        if (! $user || ! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect('/email/verify?status=invalid');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect('/email/verify?status=success');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }
}
