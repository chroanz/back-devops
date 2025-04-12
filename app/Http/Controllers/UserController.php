<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFunction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller 
{
    protected User $user;
    protected UserFunction $uf;

    // Fazendo a injeção de dependência no construtor
    // Prefiro trabalhar dessa forma
    public function __construct()
    {
        $this->user = new User();
        $this->uf = new UserFunction();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->uf->where('function', 'default')->with('user')->get();
        return response()->json($users);
    }

    public function getAdmins()
    {
        $admins = $this->uf->where('function', 'admin')->with('user')->get();
        return response()->json($admins);
    }

    /**
     * Handle user login.
     */
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     $user = $this->user->where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json(['msg' => 'Invalid credentials.'], 401);
    //     }

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'msg' => 'Login successful.',
    //         'token' => $token
    //     ], 200);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['msg' => 'Credenciais inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
        
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
                if($user && $this->uf->create([
                    'user_id' => $user->id,
                    'function' => 'default'
                 ]))
                {
                    return response()->json(["msg" => "User created successfully."], 200);
                }
            }
            catch(Exception $e)
            {
                return response()->json(["msg" => $e->getMessage()], 422);
            }
    }

    public function storeAdmin(Request $request)
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

               $functionModel = new UserFunction();


               if($user && $functionModel->create([
                  'user_id' => $user->id,
                  'function' => 'admin'
               ]))
               {
                   return response()->json(["msg" => "Admin user created successfully."], 200);
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
        $this->uf->where("user_id", $user->id)->delete();
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

    public function updateAdmin(Request $request)
    {
        try
        {
            $user = $this->user->find($request->id);
            if(!$user)
            {
                return response()->json(["msg" => "Resource not found."], 404);
            }
            // Sei que tá duplicado, pretendo melhorar isso dps.
            $rules = [
                "id" => "required",
                "name" => "min:5|max:50|required",
                "password" => "required",
                "email" => "email|required",
                "function" => "required|min:5|max:10"
            ];

            if($request->method() === "PUT" || $request->method() === "PATCH")
            {
                array_pop($rules);
            }

            $request->validate($rules);

            $uf = (object) [
                "user_id" => $request->id,
                "function" => $request->function
            ];

            (new UserFunction())->where("user_id", $uf->user_id)->update(["function" => $uf->function]);

            $request->request->remove('id');
            $request->request->remove('function');

            $user->update($request->all());

            return response()->json(status: 200);

        }
        catch(Exception $e)
        {
            return response()->json(["msg" => $e->getMessage()], 422);
        }
    }

    // public function me(){
    //     $user = auth()->user();
    //     return response()->json($user, 200);
    // }

    public function me()
    {
        return response()->json(auth()->user());
    }

}
