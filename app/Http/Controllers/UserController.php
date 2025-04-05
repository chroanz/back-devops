<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected User $user;

    // Fazendo a injeção de dependência no construtor
    // Prefiro trabalhar dessa forma
    public function __construct()
    {
        $this->user = new User();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->user->all();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
            // regras de validação de campos
            try
            {
                $rules = [
                    "email" => "email|required",
                    "name" => "min:5|max:50|required",
                    "password" => "required"
                ];
                $feedback = [
                    // Irei definir posteriormente
                ];
                
                $request->validate($rules);
                $user = $this->user->create($request->all());
                if($user)
                {
                    return response()->json($user);
                }
            }
            catch(Exception $e)
            {
                return response()->json(["msg" => $e->getMessage()], 422);
            }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try
        {
            $user = $this->user->find($id);
            if($user)
            {
                return response()->json($user);
            }
            else
            {
                return response()->json(["msg" => "Resource not found."], 404);
            }
        }
        catch(Exception $e)
        {
            return response()->json(["Error" => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try
        {
            $user = $this->user->find($id);
            if(!$user)
            {
                return response()->json(["msg" => "Resource not found."], 404);
            }
            // Sei que tá duplicado, pretendo melhorar isso dps.
            $rules = [
                "email" => "email|required",
                "name" => "min:5|max:50|required",
                "password" => "required"
            ];
            $request->validate($rules);
            $user->update($request->all());
            return response()->json($user);
            
        }
        catch(Exception $e)
        {
            return response()->json(["msg" => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = $this->user->find($id);
        if(!$user)
        {
            return response()->json(["msg" => "Resource not found."], 404);
        }
        $user->delete();
        return response()->json($user);
    }
}
