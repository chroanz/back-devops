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
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria' => 'required|string|max:255',
        ]);
        $curso = Cursos::create($validated);
        return response()->json($curso, 201);
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
    public function update(Request $request, Cursos $curso)
    {
    // Verifique se o modelo foi recuperado corretamente
    if (!$curso || !$curso->exists) {
        return response()->json(['error' => 'Curso não encontrado'], 404);
    }

    // Veja os dados que estão chegando
    $requestData = $request->all();
    
    $validated = $request->validate([
        'titulo' => 'sometimes|required|string|max:255',
        'descricao' => 'sometimes|required|string',
        'categoria' => 'sometimes|required|string|max:255',
    ]);
    
    // Salve os valores antigos para comparação
    $oldValues = $curso->toArray();
    
    // Tente atualizar de forma explícita
    $curso->titulo = $validated['titulo'] ?? $curso->titulo;
    $curso->descricao = $validated['descricao'] ?? $curso->descricao;
    $curso->categoria = $validated['categoria'] ?? $curso->categoria;
    
    // Force a atualização
    $saved = $curso->save();
    
    return response()->json([
        'success' => $saved,
        'old_values' => $oldValues,
        'new_values' => $curso->fresh()->toArray(),
        'request_data' => $requestData,
        'validated_data' => $validated
    ]);
    }  

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cursos $curso)
    {
        $curso->delete();
        return response()->json(null, 204);
    }
}
