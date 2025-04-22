<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cursos;

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
        $cursos = $this->cursos->with(['aulas' => function($query) {
            $query->orderBy('sequencia');
        }, 'leituras' => function($query) {
            $query->orderBy('sequencia');
        }])->get();
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
        $user = auth('api')->user();
        $curso = $this->cursos->with([
            'aulas' => function($query) use($user) {
            $query->with(['users' => function ($q) use ($user){
                $q->where('user_id', $user?->id);
            }])->orderBy('sequencia');
        },
            'leituras' => function($query) use ($user) {
            $query->with(['users' => function ($q) use ($user) {
                $q->where('user_id', $user?->id);
            }])->orderBy('sequencia');
        }
        ])->find($id);
        $curso->aulas->each(function ($aula){
            $aula->visto = $aula->users->isNotEmpty();
            unset($aula->users);
        });

        $curso->leituras->each(function ($leitura){
            $leitura->visto = $leitura->users->isNotEmpty();
            unset($leitura->users);
        });

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

    public function subscribe(Cursos $cursos)
    {
        /**
         * @var User $user
         */
        $user = auth('api')->user();

        // Verifica se o usuário já está matriculado no curso
        if ($user->cursos()->where('cursos_id', $cursos->id)->exists()) {
            return response()->json([
                'msg' => 'Usuário já está matriculado neste curso'
            ], 400);
        }

        $user->cursos()->attach($cursos->id);

        return response()->json([
            'msg' => 'Matrícula realizada com sucesso'
        ], 201);
    }

    public function meusCursos(){
        /**
         * @var User $user
         */
        $user = auth('api')->user();
        $cursos = $user->cursos()->with(['aulas', 'leituras'])->get()->map(function ($curso) use ($user) {
            // Obtém total de aulas e leituras
            $totalAulas = $curso->aulas->count();
            $totalLeituras = $curso->leituras->count();
            $total = $totalAulas + $totalLeituras;

            // Marca quais aulas foram vistas
            $curso->aulas->map(function ($aula) use ($user) {
                $aula->visto = $aula->users()->where('user_id', $user->id)->exists();
                return $aula;
            });

            // Marca quais leituras foram vistas
            $curso->leituras->map(function ($leitura) use ($user) {
                $leitura->visto = $leitura->users()->where('user_id', $user->id)->exists();
                return $leitura;
            });

            // Calcula quantidade de itens vistos
            $aulasVistas = $curso->aulas->where('visto', true)->count();
            $leiturasVistas = $curso->leituras->where('visto', true)->count();
            $totalVistos = $aulasVistas + $leiturasVistas;

            // Calcula percentual de conclusão
            $curso->percentual_conclusao = $total > 0 ? round(($totalVistos / $total) * 100) : 0;

            return $curso;
        });

        return response()->json($cursos);
    }
}
