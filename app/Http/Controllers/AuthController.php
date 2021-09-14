<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string',
            'type'      => 'required|string'
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $status = DB::transaction(function () use ($request) {
                $userId = User::insertGetId(array(
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'type'      => $request->type
                ));

                return User::findOrFail($userId);
            });

            if ($status) {
                return response()->json(['data' => $status], 200);
            } else {
                return response()->json(['message' => 'failed'], 400);
            }
        }
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $data = [
                'email' => $request->email,
                'password' => $request->password,
            ];
            if (Auth::attempt($data)) {
                $user = User::where('email', $request->email)->firstOrFail();
                $token = $user->createToken('LaravelAuthToken')->plainTextToken;
                return response()->json(['token' => $token], 200);
            } else {
                return response()->json(['message' => 'Email or password is invalid'], 400);
            }
        }
    }
}
