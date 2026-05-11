<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdatePasswordRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if (filled($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store("customers/{$user->id}/avatars", 'public');
        }

        $user->forceFill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'avatar_path' => $data['avatar_path'] ?? $user->avatar_path,
        ])->save();

        return response()->json([
            'message' => __('messages.profile.updated_success'),
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => __('messages.profile.current_password_invalid'),
                'errors' => [
                    'current_password' => [__('messages.profile.current_password_invalid')],
                ],
            ], 422);
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        return response()->json([
            'message' => __('messages.profile.password_updated_success'),
            'data' => new UserResource($user->fresh()),
        ]);
    }
}
