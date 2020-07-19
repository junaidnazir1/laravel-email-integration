<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\VerifyMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Log;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
        $this->middleware('guest')->except('verify');

        
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        log::info("hello");
        $user= User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'remember_token' => sha1(time())
        ]);

        Mail::to($user['email'])->send(new VerifyMail($user));

        return $user;
    }

    public function verify($token)
    {
          log::info($token);
        log::info("verify");
        $verifyUser = user::where('remember_token', $token)->first();
        log::info($verifyUser);
        if (!$verifyUser['email_verified_at']) {
            $verifyUser['email_verified_at'] = Carbon::now();
            log::info(Carbon::now());
            log::info( $verifyUser['email_verified_at']);
            $verifyUser->save();
            $status = "Your e-mail is verified. ";
        } else {
            $status = "Your e-mail is already verified. ";
        }
        return view('emails.emailStatus')->with('status', $status);
    }
}
