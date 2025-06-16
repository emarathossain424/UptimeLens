<?php
namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SocialLoginController extends Controller
{

    /**
     * Redirect the user to the social provider's authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the social provider and log them in.
     *
     * @param string $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create the user
        $user = User::firstOrCreate([
            'email' => $socialUser->getEmail(),
        ], [
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'email_verified_at' => now(),
            'password' => bcrypt(Str::random(16)),
        ]);

        // Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
