<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('limit', 10);
        $users = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success($users, 'Success', 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]);

            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);

            return $this->success($user, 'Berhasil membuat user', 200);
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }
    }

    public function show(int $id)
    {
        $user = User::findOrFail($id);

        return $this->success($user, 'Success', 200);
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
            ]);

            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return $this->success($user, 'Berhasil update user', 200);
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }
    }

    public function destroy(Request $request, int $id)
    {
        if ($request->user()?->id === $id) {
            return $this->error('Tidak bisa menghapus akun sendiri.', 422);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return $this->success(null, 'Berhasil menghapus user', 200);
    }
}
