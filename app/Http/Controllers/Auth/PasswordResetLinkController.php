<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Customer;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        
        // Check if email exists in users table (admins, regular users)
        $user = User::where('email', $email)->first();
        
        // Check if email exists in customers table (wholesale/retail customers)
        $customer = Customer::where('email', $email)->first();
        
        if (!$user && !$customer) {
            // Email doesn't exist in either table
            throw ValidationException::withMessages([
                'email' => [trans('passwords.user')],
            ]);
        }
        
        // Determine which password broker to use based on where the email was found
        $broker = $user ? 'users' : 'customers';
        
        // Send the password reset link using the appropriate broker
        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        // If an error was returned by the password broker, we will get this message
        // translated so we can notify a user of the problem. We'll redirect back
        // to where the users came from with their error message.
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
