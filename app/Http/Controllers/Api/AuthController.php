<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register user baru
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Set email_verified_at agar user dianggap sudah terverifikasi
            'remember_token' => 'berhasil_register', // Set remember_token untuk menghindari error "Token not provided"',
        ]);
 
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'User successfully registered',
            'data'    => [
                'user'  => $user,
                'token' => $this->respondWithToken($token),
            ]
        ], 201);
    }

    // Update data user yang sedang login
    public function update(Request $request)
    {
    // 1. Ambil data user yang sedang login berdasarkan token JWT
    $user = auth()->user(); 

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User tidak ditemukan atau belum login'
        ], 401);
    }

    // 2. Validasi input dari Postman
    $validator = Validator::make($request->all(), [
        'name'         => 'sometimes|required|string|max:255',
        'email'         => 'sometimes|nullable|string|max:50|email|unique:users,email,' . $user->id,
        'password'     => 'sometimes|required|string|min:6', // Jika ingin sekalian bisa ubah password
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 400);
    }

    // 3. Update field yang dikirim saja (menggunakan fill)
    if ($request->has('name')) {
        $user->name = $request->name;
    }
    
    if ($request->has('email')) {
        $user->email = $request->email;
    }

    // Khusus password, harus di-hash dulu sebelum disimpan
    if ($request->has('password')) {
        $user->password = Hash::make($request->password);
    }
    if($request->has('email_verified_at')) {
        $user->email_verified_at = now(); // Set email_verified_at agar user dianggap sudah terverifikasi
    }


    // 4. Simpan perubahan ke database
    $user->save();

    return response()->json([
        'status'  => 'success',
        'message' => 'Data berhasil diperbarui',
        'data'    => $user
    ], 200);
}


    /**
     * Login user
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not create token',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'data'    => $this->respondWithToken($token),
        ]);
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status'  => 'success',
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to logout, token invalid',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status'  => 'success',
                'message' => 'Token refreshed',
                'data'    => $this->respondWithToken($newToken),
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to refresh token',
                'error'   => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get user profile
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'status'  => 'success',
                'message' => 'User profile fetched',
                'data'    => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token is invalid or expired',
                'error'   => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Helper: format token response
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60, // dalam detik
        ];
    }
}

