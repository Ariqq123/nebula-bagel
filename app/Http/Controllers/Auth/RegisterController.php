<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Rules\Username;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Notifications\WelcomeSetPasswordNotification;

class RegisterController extends Controller
{
    /**
     * Handle a registration request.
     */
    public function register(Request $request): JsonResponse
    {
        if (!config('pterodactyl.auth.registration_enabled')) {
            abort(403, 'Registration is not enabled.');
        }

        $request->validate([
            'email' => 'required|email|between:1,191',
            'username' => ['required', 'between:1,191', new Username()],
            'name_first' => 'required|string|between:1,191',
            'name_last' => 'required|string|between:1,191',
        ]);

        // Check if a user with the same email already exists.
        $existingUser = User::query()->where('email', $request->input('email'))->first();

        if ($existingUser) {
            // If the user exists but has not verified their email, resend the notification.
            if ($existingUser->email_verified_at === null) {
                $token = Password::broker()->createToken($existingUser);
                $existingUser->notify(new WelcomeSetPasswordNotification($existingUser, $token));

                return new JsonResponse([
                    'data' => [
                        'complete' => true,
                    ],
                ]);
            }

            // If verified, the unique validation will catch it below - but since we
            // want to avoid enumeration, return the same generic success response.
            return new JsonResponse([
                'data' => [
                    'complete' => true,
                ],
            ]);
        }

        // Validate username uniqueness separately (unverified accounts still claim their username).
        $request->validate([
            'username' => 'unique:users,username',
        ]);

        $user = User::create([
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'name_first' => $request->input('name_first'),
            'name_last' => $request->input('name_last'),
            'password' => null,
            'email_verified_at' => null,
        ]);

        $token = Password::broker()->createToken($user);
        $user->notify(new WelcomeSetPasswordNotification($user, $token));

        return new JsonResponse([
            'data' => [
                'complete' => true,
            ],
        ]);
    }
}
