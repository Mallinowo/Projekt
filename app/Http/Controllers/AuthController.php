<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\CityLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(private readonly CityLocationService $cityLocationService)
    {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Log::info('User logged in', ['user_id' => Auth::id(), 'email' => Auth::user()->email]);
            if (!Auth::user()->onboarding_completed) {
                return redirect()->route('onboarding.show');
            }
            return redirect()->route('discover');
        }

        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:30|unique:users',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6|confirmed',
            'age'        => 'required|integer|min:18|max:99',
            'city'       => 'required|string|max:60',
            'gender'     => ['required', Rule::in(User::GENDER_VALUES)],
            'orientation' => ['required', Rule::in(User::ORIENTATION_VALUES)],
            'subculture' => ['required', Rule::in(User::SUBCULTURE_VALUES)],
        ]);

        $coords = $this->cityLocationService->resolve((string) $data['city']);
        if ($coords) {
            $data['city_lat'] = $coords['lat'];
            $data['city_lng'] = $coords['lng'];
        }

        $user = User::create($data);

        Auth::login($user);
        Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::warning('Welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('onboarding.show');
    }

    public function logout(Request $request)
    {
        Log::info('User logged out', ['user_id' => Auth::id()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function setLocale(Request $request, string $locale)
    {
        if (!in_array($locale, ['pl', 'en'])) abort(404);
        $request->session()->put('locale', $locale);
        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }
        return back();
    }
}
