<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use Illuminate\Http\Request;

class CursosController extends Controller
{

    protected Cursos $cursos;

    public function __construct(Cursos $cursos = new Cursos())
    {
        $this->cursos = $cursos;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cursos = $this->cursos->all();
        return response()->json($cursos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $curso = $this->cursos->find($id);

        return !empty($curso)
            ? response()->json($curso)
            : response()->json(status: 404);
    }

    public function search(string $search)
    {
        $search = htmlspecialchars(strip_tags($search));
        
        $cursos = $this->cursos->getByParam($search);

        return !empty($cursos)
            ? response()->json($cursos)
            : response()->json(status: 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cursos $cursos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cursos $cursos)
    {
        //
    }
}
