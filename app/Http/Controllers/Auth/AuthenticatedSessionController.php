<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use GuzzleHttp\Client;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validar reCAPTCHA
        $request->validate([
            'g-recaptcha-response' => 'required'
        ]);

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $secretKey = '6Leg8LEqAAAAANiZfYY61diAwLWBseVUlumbJG36'; // Clave secreta

        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $secretKey,
                'response' => $recaptchaResponse
            ]
        ]);

        $responseBody = json_decode((string) $response->getBody());

        if (!$responseBody->success) {
            return back()->withErrors(['captcha' => 'ReCAPTCHA verification failed. Please try again.']);
        }

        // Continuar con la autenticación estándar
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
