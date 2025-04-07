<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = $this->user->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['msg' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'msg' => 'Login successful.',
            'token' => $token
        ], 200);
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
                    return response()->json(["msg" => "User created successfully."], 200);
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
                "name" => "min:5|max:50|required",
                "password" => "required",
                "email" => "email|required"
            ];

            if($request->method() === "PUT" || $request->method() === "PATCH")
            {
                array_pop($rules);
            }
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

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        Password::sendResetLink($request->only('email'));

        return response()->json(status: Response::HTTP_OK);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required|string'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if($status == Password::PASSWORD_RESET)
        {
            return response()->json(status: Response::HTTP_OK);
        }

        return response()->json(status: Response::HTTP_BAD_REQUEST);

    }
}
