<?php

namespace App\Http\Controllers;

use App\Models\Leitura;
use App\Http\Requests\StoreLeituraRequest;
use App\Http\Requests\UpdateLeituraRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeituraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!$request->get('curso_id')){
            return response()->json(['msg' => 'Para buscar leituras deve ser informado um curso_id.', 400]);
        }
        return response()->json(Leitura::where('curso_id', $request->get('curso_id'))->get());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeituraRequest $request)
    {
        $data = $request->all();
        $leitura = Leitura::create($data);
        return response()->json($leitura, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Leitura $leitura)
    {
        return response()->json($leitura);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeituraRequest $request, Leitura $leitura)
    {
        $data = $request->all();
        $leitura->update($data);
        $leitura->refresh();
        return response()->json($leitura);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leitura $leitura)
    {
        $leitura->delete();
        return response()->json(null, 204);
    }

    public function marcarVisto(Leitura $leitura){
        $user = auth('api')->user();
        $leitura->users()->attach($user->id);
        return response()->json(['msg' => 'Leitura marcada como vista.']);
    }
}
