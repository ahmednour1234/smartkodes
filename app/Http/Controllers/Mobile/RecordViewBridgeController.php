<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RecordViewBridgeController extends Controller
{
    public function show(Request $request, Record $record)
    {
        $token = $request->bearerToken() ?: $request->query('token');

        if (!$token) {
            abort(Response::HTTP_UNAUTHORIZED, 'Missing token');
        }

        try {
            /** @var mixed $guard */
            $guard = auth('api');
            $user = $guard->setToken($token)->user();
        } catch (\Throwable $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid token');
        }

        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid token');
        }

        if ($user->tenant_id !== null && (string) $record->tenant_id !== (string) $user->tenant_id) {
            abort(Response::HTTP_FORBIDDEN, 'Not allowed to view this record');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if ($user->tenant_id === null) {
            return redirect()->route('admin.records.show', ['record' => $record->id]);
        }

        return redirect()->route('tenant.records.show', ['record' => $record->id]);
    }
}
