<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
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
            $data = User::with('tenant')->get();
        } else {
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
        $data = Tenant::all();

        return view ('users.create', ['empresas' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUser = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6'
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
            'password'  => 'required|string|min:6'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,   
            'password' => Hash::make($request->password)
        ];

        if ($authUser->role === 'superadmin') {
            $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'role'      => 'required|in:admin,user,superadmin'
            ]);

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
