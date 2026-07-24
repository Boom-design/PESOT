<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ───────────────────────────────
    // REGISTER (Mobile — Jobseeker & Company)
    // ───────────────────────────────
    public function register(Request $request)
    {
        $base = [
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:jobseeker,company',
        ];

        if ($request->role === 'jobseeker') {
            $rules = array_merge($base, [
                'last_name'     => 'required|string',
                'first_name'    => 'required|string',
                'middle_name'   => 'nullable|string',
                'date_of_birth' => 'required|date',
                'gender'        => 'required|in:Male,Female,Other',
            ]);
        } else {
            $rules = array_merge($base, [
                'company_name'   => 'required|string',
                'contact_person' => 'required|string',
                'position_title' => 'required|string',
                'mobile_number'  => 'required|string',
            ]);
        }

        $request->validate($rules);

        if ($request->role === 'jobseeker') {
            $fullName = trim(
                $request->last_name . ', ' .
                $request->first_name . ' ' .
                ($request->middle_name ?? '')
            );
            $userData = [
                'name'          => $fullName,
                'email'         => $request->email,
                'password'      => bcrypt($request->password),
                'role'          => 'jobseeker',
                'status'        => 'approved',
                'last_name'     => $request->last_name,
                'first_name'    => $request->first_name,
                'middle_name'   => $request->middle_name,
                'date_of_birth' => $request->date_of_birth,
                'gender'        => $request->gender,
            ];
        } else {
            $userData = [
                'name'           => $request->contact_person,
                'email'         => $request->email,
                'password'      => bcrypt($request->password),
                'role'          => 'company',
                'status'        => 'approved',
                'company_name'   => $request->company_name,
                'contact_person' => $request->contact_person,
                'position_title' => $request->position_title,
                'mobile_number'  => $request->mobile_number,
                'phone'          => $request->mobile_number,
            ];
        }

        $user = User::create($userData);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful!',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // ───────────────────────────────
    // LOGIN (Mobile — Jobseeker & Company only)
    // ───────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Admins must login via web panel.'], 403);
        }

        if ($user->status === 'pending') {
            return response()->json(['message' => 'Your account is pending approval.'], 403);
        }

        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Your account has been rejected.'], 403);
        }

        if ($user->status === 'deactivated') {
            return response()->json(['message' => 'Your account has been deactivated. Please contact PESO.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    // ───────────────────────────────
    // LOGOUT
    // ───────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    // ───────────────────────────────
    // GET CURRENT USER (Me)
    // ───────────────────────────────
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}