<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Exception;
use DateTime;
use App\Models\Aulas;

class AulasController extends Controller
{
    protected Aulas $aulas;

    public function __construct(Aulas $aulas = new Aulas())
    {
        $this->aulas = $aulas;
    }

    public function index()
    {
        try {
            $aulas = $this->aulas::all();
            return response()->json($aulas, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao listar aulas.', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sequencia' => 'required|integer',
                'titulo' => 'required|string|max:255|min:10',
                'duracaoMinutos' => 'required|integer',
                'videoUrl' => 'required|string|max:255',
                'curso_id' => 'required|exists:cursos,id',
            ]);

            // Validação manual da unicidade da sequência no curso usando Validator
            $validator = Validator::make($validated, []);

            if ($this->aulas::where('curso_id', $validated['curso_id'])
                ->where('sequencia', $validated['sequencia'])->exists()) {

                $validator->errors()->add('sequencia', 'A sequência já está sendo usada neste curso.');

                throw new ValidationException($validator);
            }

            $aula = $this->aulas::create($validated);

            if ($aula) {
                return response()->json([
                    'message' => 'Aula criada com sucesso.',
                    'aula' => $aula,
                ], 201);
            }

            return response()->json(['error' => 'Erro ao criar aula.'], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao processar a solicitação.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $aula = $this->aulas->find($id);

            return !empty($aula)
                ? response()->json($aula->makeVisible('videoUrl'), 200)
                : response()->json(['error' => 'Aula não encontrada.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao buscar aula.', 'details' => $e->getMessage()], 500);
        }
    }

    public function search(string $search)
    {
        try {
            $search = htmlspecialchars(strip_tags($search));
            $aulas = $this->aulas->where('titulo', 'LIKE', "%{$search}%")
                ->orWhere('videoUrl', 'LIKE', "%{$search}%")
                ->get();

            return $aulas->isNotEmpty()
                ? response()->json($aulas, 200)
                : response()->json(['error' => 'Nenhuma aula encontrada.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao realizar a busca.', 'details' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $aula = $this->aulas->find($id);
            if (!$aula || !$aula->exists) {
                return response()->json(['error' => 'Aula não encontrada.'], 404);
            }

            $validated = $request->validate([
                'sequencia' => 'sometimes|required|integer',
                'titulo' => 'sometimes|required|string|max:255|min:10',
                'duracaoMinutos' => 'sometimes|required|integer',
                'videoUrl' => 'sometimes|required|string|max:255',
                'curso_id' => 'sometimes|required|exists:cursos,id',
            ]);

            $oldValues = $aula->toArray();
            $aula->update($validated);

            return response()->json([
                'message' => 'Aula atualizada com sucesso.',
                'old_values' => $oldValues,
                'new_values' => $aula->fresh()->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar aula.', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy(Aulas $aula)
    {
        try {
            if (!$aula || !$aula->exists) {
                return response()->json(['error' => 'Aula não encontrada.'], 404);
            }

            $aula->delete();

            return response()->json(['message' => 'Aula removida com sucesso.'], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Erro ao remover aula devido a restrições de banco de dados.',
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao remover aula.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function marcarVisto(Aulas $aulas)
    {
        $user = auth('api')->user();
        if (!$aulas->users()->where('user_id', $user->id)->exists()) {
            $aulas->users()->attach($user->id, ['visto' => new DateTime()]);
            return response()->json(['msg' => 'Aula marcada como vista.']);
        }
        return response()->json(['msg' => 'Aula já estava marcada como vista.'], 400);
    }
}
