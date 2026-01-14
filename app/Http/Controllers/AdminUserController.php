<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function index()
    {
        // jika user login lavel master maka tampilkan semua user
        if(Auth::user()->level == 'master'){
            $users = User::orderBy('id', 'desc')->get();
        } else {
            // selain itu tampilkan user yang di buat oleh user yang login
            $users = User::where('access', Auth::user()->username)->orderBy('id', 'desc')->get();
        }
        return response()->json($users);
    }

    public function manageUsers()
    {
        $accessUsers = User::select('id', 'name', 'username')->where('role','admin')->whereIn('level',['master','advanced'])->orderBy('id', 'desc')->get();
        return view('admin.manage_users', compact('accessUsers'));
    }

    public function show($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function store(Request $request)
    {
        try {
            // Conditional validation based on role
            $rules = [
                'name'         => 'required|string|max:255',
                'username'     => 'required|string|max:255|unique:tbl_user,username',
                'email'        => 'required|email|max:255|unique:tbl_user,email',
                'password'     => 'required|string|min:6',
                'address'      => 'nullable|string',
                'role'         => 'required|string',
                'level'        => 'required|string',
                'access'       => 'nullable|string',
            ];

            // If role is 'user', date_expired is required
            if ($request->input('role') === 'user') {
                $rules['date_expired'] = 'required|date';
            } else {
                $rules['date_expired'] = 'nullable|date';
            }

            $data = $request->validate(
                $rules,
                [],
                [],
                function ($validator) {
                    throw new HttpResponseException(
                        response()->json([
                            'message' => 'Validasi gagal',
                            'errors'  => $validator->errors()
                        ], 422)
                    );
                }
            );

            //jika acess tidak ada maka di set ambil dari username yang buat
            $access = $request->input('access', '');
            if (empty($access)) {
                $data['access'] = Auth::user()->username;
            } else {
                $data['access'] = $access;
            }

            $data['password'] = Hash::make($data['password']);
            $data['api_key'] = bin2hex(random_bytes(16));
            $user = User::create($data);

            return response()->json([
                'message' => 'User created',
                'data'    => $user
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Conditional validation based on role
        $rules = [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('tbl_user', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('tbl_user', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
            'access' => 'nullable|string',
        ];

        // If role is 'user', date_expired is required
        if ($request->input('role') === 'user') {
            $rules['date_expired'] = 'required|date';
        } else {
            $rules['date_expired'] = 'nullable|date';
        }

        $data = $request->validate($rules);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $access = $request->input('access', '');
        if (empty($access)) {
            $data['access'] = Auth::user()->username;
        } else {
            $data['access'] = $access;
        }

        $user->update($data);

        return response()->json(['message' => 'User updated', 'data' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function resetApiKey($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $newApiKey = bin2hex(random_bytes(16));
        $user->api_key = $newApiKey;
        $user->save();

        return response()->json([
            'message' => 'API key reset successfully',
            'api_key' => $newApiKey
        ]);
    }


}
