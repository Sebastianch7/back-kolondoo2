<?php

namespace App\Http\Controllers;

use App\Models\Operadoras;
use Illuminate\Http\Request;

class OperadorasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Operadoras/index', [
            'titulo' => 'hola Sebas'
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Operadoras $operadoras)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operadoras $operadoras)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Operadoras $operadoras)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operadoras $operadoras)
    {
        //
    }
}
