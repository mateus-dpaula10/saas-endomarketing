<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\ResetPasswordRequestNotification;

class AuthController extends Controller
{
    public function index() {
        return view ('auth.index');
    }

    public function login(Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não são válidas.'
        ]);
    }

    public function passwordReset(Request $request) {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado!']);
        }

        if (!$user->tenant) {
            return back()->withErrors(['email' => 'Usuário não vínculado a uma empresa.']);
        }

        $admin = $user->tenant->users()->where('role', 'admin')->first();

        if (!$admin) {
            return back()->withErrors(['email' => 'Nenhum administrador encontrado para empresa do usuário fornecido.']);
        }

        $admin->notify(new ResetPasswordRequestNotification($user));

        return back()->with('success', 'Administrador da sua empresa foi notificado para redefinir sua senha.');
    }

    public function showResetForm(User $user) {
        return view('administration.index', compact('user'));
    }

    public function resetPassword(Request $request, User $user) {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        // return redirect()->route()
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login.index');
    }
}
