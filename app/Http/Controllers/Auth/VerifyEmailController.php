<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $url = $request->user()->tenant_id === null ? route('admin.dashboard') : route('tenant.dashboard');
            $url .= '?verified=1';
            return redirect()->intended($url);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $url = $request->user()->tenant_id === null ? route('admin.dashboard') : route('tenant.dashboard');
        $url .= '?verified=1';
        return redirect()->intended($url);
    }
}
