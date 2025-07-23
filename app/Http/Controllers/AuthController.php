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

        $admins = $user->tenant->users()->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            if (!$admin) {
                return back()->withErrors(['email' => 'Nenhum administrador encontrado para empresa do usuário fornecido.']);
            }

            $existingNotification = $admin->notifications()
                ->where('notifiable_id', $admin->id)
                ->where('type', 'App\Notifications\ResetPasswordRequestNotification')
                ->whereJsonContains('data->user_id', $user->id) 
                ->first();

            if ($existingNotification) {
                $existingNotification->delete();
            }
    
            $admin->notify(new ResetPasswordRequestNotification($user));
        }

        return back()->with('success', 'Administrador da sua empresa foi notificado para redefinir sua senha.');
    }

    public function showResetForm(User $user) {
        return view('administration.index', compact('user'));
    }

    public function resetPassword(Request $request, User $user) {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', 
                'regex:/[a-z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/'
            ]
        ], [
            'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        try {            
            $user->update([
                'password' => bcrypt($request->password)
            ]);

            $admins = $user->tenant->users()->where('role', 'admin')->get();

            foreach ($admins as $admin) {
                $admin->notifications()
                    ->where('notifiable_id', $admin->id)
                    ->where('type', 'App\Notifications\ResetPasswordRequestNotification')
                    ->whereJsonContains('data->user_id', $user->id)  
                    ->delete();
            }
            
            return redirect()->route('administration.index')->with('success', 'Senha redefinida com sucesso.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocorreu um erro ao tentar redefinir a senha. Tente novamente.'])->withInput();
        }
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login.index');
    }
}
