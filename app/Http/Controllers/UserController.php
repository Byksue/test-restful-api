<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request) : JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'npp' => 'required|string|max:255',
            'npp_supervisor' => 'string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|max:255',
        ],[
            'name.required' => 'Nama tidak boleh kosong',
            'npp.required' => 'NPP tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong',
            'role.required' => 'Role tidak boleh kosong',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'validation_error',
                'message' => $validator->errors()->first(),
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'npp' => $request->npp,
            'npp_supervisor' => $request->npp_supervisor,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil didaftarkan',
            'data' => [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]
        ], 201);
    }

    public function login(Request $request) : JsonResponse {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil masuk',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 200);
        }else{
            return response()->json([
                'status' => 'auth_failed',
                'message' => 'Email atau password salah',
                'data' => null
            ], 401);
        }
    }

    public function logout(Request $request) : JsonResponse {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil keluar',
            'data' => null
        ], 200);
    }
}
