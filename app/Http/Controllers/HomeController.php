<?php

namespace App\Http\Controllers;

use App\Models\Home;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view ('home.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'home_email_duvida' => 'required|email'
        ]);

        $email = 'dev@hiatocomunica.com.br';

        try {
            Mail::raw("Novo contato recebido: {$request->home_email_duvida}", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Novo contato do site');
            });
            return back()->with('success', 'E-mail enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Erro ao enviar e-mail: ' . $e->getMessage()]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Home $home)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Home $home)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Home $home)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Home $home)
    {
        //
    }
}
