<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    // public function index($user)
    // {
    //     $token = $user->createToken( Str::random(40) )->plainTextToken;

    //     return response()->json([
    //         'user'=>$user,
    //         'token'=>$token,
    //         'token_type'=>'Bearer'
    //     ]);
    // }

    public function index()
    {
        try {
            $user = User::orderBy('updated_at', 'DESC')->get();

            $response = [
                'message' => 'List user order by time',
                'data' => $user
            ];
    
            return response()->json($response, Response::HTTP_OK);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Failed ' . $e->errorInfo
            ]);
        }
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'name'=>'required|min:3',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:4|confirmed',
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors(), 
            Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = User::create([
                'name'=>ucwords($request->name),
                'email'=>$request->email,
                'password'=> bcrypt($request->password)
            ]);

            return response()->json($user, Response::HTTP_CREATED);


        } catch (QueryException $e) {
            //throw $th;
            return response()->json([
                'message' => 'Failed ' . $e->errorInfo
            ]);
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response() ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'login success', 
            'user' => $user, 
            'access_token' => $token, 
            'token_type' => 'Bearer', 
        ]);
    }

    // public function logout()
    // {
    //     Auth::user()->tokens()->delete();

    //     return response()->json([
    //         'message'=>'Logout success'
    //     ]);
    // }

    public function logout(User $user){

        $user->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }
}
