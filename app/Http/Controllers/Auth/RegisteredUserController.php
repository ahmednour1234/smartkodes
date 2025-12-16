<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'company_name' => ['required', 'string', 'max:255'],
            'field_of_work' => ['required', 'string', 'max:255'],
            'num_users' => ['required', 'integer', 'min:1', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'captcha' => ['required', 'captcha'],
            'terms' => ['accepted'],
        ]);

        DB::beginTransaction();
        try {
            // Calculate monthly price
            $monthlyPrice = $request->num_users * 10; // $10 per user

            // Create tenant
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'slug' => Str::slug($request->company_name) . '-' . Str::random(6),
                'company_name' => $request->company_name,
                'field_of_work' => $request->field_of_work,
                'num_users' => $request->num_users,
                'monthly_price' => $monthlyPrice,
                'status' => 0, // Pending payment
            ]);

            // Create user (tenant admin)
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => $request->country,
                'password' => Hash::make($request->password),
            ]);

            // Store registration data in session for payment
            session([
                'pending_registration' => [
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'amount' => $monthlyPrice,
                    'num_users' => $request->num_users,
                ]
            ]);

            DB::commit();

            event(new Registered($user));

            Auth::login($user);

            // Redirect to payment gateway
            // For now, redirect to a payment page (you'll need to create this)
            return redirect()->route('payment.checkout')
                ->with('message', 'Please complete payment to activate your account.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}
