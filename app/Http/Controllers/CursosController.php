<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cursos;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $cursos = $this->cursos->with(['aulas' => function ($query) {
            $query->orderBy('sequencia');
        }, 'leituras' => function ($query) {
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
            'capa' => 'nullable|string'
        ]);

        $data = $validated;

        if (!empty($validated['capa'])) {
            // Extrai o conteúdo base64 removendo cabeçalho se existir
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['capa']);

            try {
                // Decodifica o base64
                $imageData = base64_decode($base64Image);

                if ($imageData === false) {
                    return response()->json(['message' => 'Imagem inválida'], 400);
                }

                // Verifica o tamanho (5MB)
                if (strlen($imageData) > 5 * 1024 * 1024) {
                    return response()->json(['message' => 'A imagem deve ter menos de 5MB'], 400);
                }

                // Cria um recurso de imagem temporário
                $img = imagecreatefromstring($imageData);
                if (!$img) {
                    return response()->json(['message' => 'Formato de imagem inválido'], 400);
                }

                // Verifica dimensões
                $width = imagesx($img);
                $height = imagesy($img);

                if ($width > 2560 || $height > 2560) {
                    imagedestroy($img);
                    return response()->json(['message' => 'A imagem não pode ter dimensão superior a 2560px'], 400);
                }

                imagedestroy($img);

                // Gera nome único para o arquivo
                $filename = Str::uuid() . '.jpg';

                // Salva o arquivo
                $fullPath = 'imagens/cursos/capas/' . $filename;
                $upload = Storage::disk('s3')->put($fullPath, $imageData);
                if (!$upload) {
                    return response()->json(['message' => 'Erro ao salvar imagem'], 400);
                }

                // Gera URL temporária inicial
                $path = Storage::disk('s3')->temporaryUrl($fullPath, now()->addDays(7));

                $data['capa'] = $fullPath;
                $data['capa_url'] = $path;
                $data['capa_expiration'] = now()->addDays(7);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro ao processar imagem'], 400);
            }
        }

        $curso = Cursos::create($data);
        return response()->json($curso, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth('api')->user();
        $curso = $this->cursos->with([
            'aulas' => function ($query) use ($user) {
                $query->with(['users' => function ($q) use ($user) {
                    $q->where('user_id', $user?->id);
                }])->orderBy('sequencia');
            },
            'leituras' => function ($query) use ($user) {
                $query->with(['users' => function ($q) use ($user) {
                    $q->where('user_id', $user?->id);
                }])->orderBy('sequencia');
            }
        ])->find($id);
        $curso->aulas->each(function ($aula) {
            $aula->visto = $aula->users->isNotEmpty();
            unset($aula->users);
        });

        $curso->leituras->each(function ($leitura) {
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

        if ($request->has('capa')) {
            // Processa nova imagem como no store
            // Atualiza capa, capa_url e capa_expiration
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $request->capa);

            try {
                // Processo similar ao store para salvar nova imagem
                $imageData = base64_decode($base64Image);
                $filename = Str::uuid() . '.jpg';
                $fullPath = 'imagens/cursos/capas/' . $filename;

                if (Storage::disk('s3')->put($fullPath, $imageData)) {
                    $path = Storage::disk('s3')->temporaryUrl($fullPath, now()->addDays(7));
                    $curso->capa = $fullPath;
                    $curso->capa_url = $path;
                    $curso->capa_expiration = now()->addDays(7);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro ao processar nova imagem'], 400);
            }
        }

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

    public function meusCursos()
    {
        /**
         * @var User $user
         */
        $user = auth('api')->user();
        $cursos = $user->cursos()->with(['aulas', 'leituras'])->get()->map(function ($curso) use ($user) {
            // Marca quais aulas foram vistas
            $curso->aulas->map(function ($aula) use ($user) {
                $aula->setAttribute('visto', $aula->users()->where('user_id', $user->id)->exists());
                return $aula;
            });

            // Marca quais leituras foram vistas
            $curso->leituras->map(function ($leitura) use ($user) {
                $leitura->setAttribute('visto', $leitura->users()->where('user_id', $user->id)->exists());
                return $leitura;
            });
            $curso->percentual_conclusao = $curso->calcularPercentualConclusao($user);

            return $curso;
        });

        return response()->json($cursos);
    }
}
