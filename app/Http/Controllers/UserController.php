<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plain;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userAuth = Auth::user();

        if ($userAuth->role === 'superadmin') {
            // pegando todos
            $data = User::with('tenant')->get();
        } else {
            // pegando apenas os da sua empresa
            $data = User::where('tenant_id', $userAuth->tenant_id)
                ->with('tenant')
                ->get();
        }

        return view ('users.index', ['usuarios' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = Tenant::all();
        $authUser = auth()->user();

        return view ('users.create', compact('empresas', 'authUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => [
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

        $isSuperAdmin = $authUser->role === 'superadmin';

        if ($isSuperAdmin) {
            $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'role'      => 'required|in:admin,user'
            ]);

            $tenantId = $request->tenant_id;
            $role = $request->role;
        } else {            
            $tenantId = $authUser->tenant_id;
            $role = 'user';
        }

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'tenant_id' => $tenantId,
            'role'      => $role
        ]);

        return redirect()->route('usuario.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = User::findOrFail($id);
        $empresas = Tenant::all();

        return view ('users.edit', compact('usuario', 'empresas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $authUser = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', 
                'regex:/[a-z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/'
            ],
            'tenant_id' => $authUser->role === 'superadmin' ? 'required|exists:tenants,id' : '',
            'role'      => $authUser->role === 'superadmin' ? 'required|in:admin,user,superadmin' : ''
        ], [
            'name.required'           => 'O campo nome é obrigatório.',
            'email.required'          => 'O campo e-mail é obrigatório.',
            'email.email'             => 'Informe um e-mail válido.',
            'email.unique'            => 'Já existe um usuário com este e-mail.',
            'password.regex'          => 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.',
            'password.min'            => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed'      => 'As senhas não coincidem.',
            'tenant_id.required'      => 'É obrigatório selecionar uma empresa.',
            'tenant_id.exists'        => 'A empresa selecionada não é válida.',
            'role.required'           => 'É obrigatório selecionar uma função.',
            'role.in'                 => 'O valor selecionado para função não é válido.'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($authUser->role === 'superadmin') {
            $data['tenant_id'] = $request->tenant_id;
            $data['role'] = $request->role;
        } else {
            if ($user->tenant_id !== $authUser->tenant_id) {
                abort(403, 'Você não tem permissão para editar este usuário.');
            }

            $data['tenant_id'] = $authUser->tenant_id;
            $data['role'] = $user->role;
        }

        $user->update($data);

        return redirect()->route('usuario.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->route('usuario.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
