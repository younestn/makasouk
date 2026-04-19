<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validate([
            'role' => ['nullable', 'in:admin,customer,tailor'],
            'is_suspended' => ['nullable', 'boolean'],
        ]);

        $users = User::query()
            ->with('tailorProfile.category')
            ->when(isset($validated['role']), fn ($q) => $q->where('role', $validated['role']))
            ->when(array_key_exists('is_suspended', $validated), fn ($q) => $q->where('is_suspended', $validated['is_suspended']))
            ->latest()
            ->paginate(30);

        return response()->json(UserResource::collection($users));
    }

    public function suspend(SuspendUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('suspend', $user);

        $user->update(['is_suspended' => true]);

        if ($user->role === User::ROLE_TAILOR && $user->tailorProfile) {
            $user->tailorProfile->update(['status' => 'offline']);
        }

        return response()->json([
            'message' => 'تم إيقاف الحساب بنجاح',
            'reason' => $request->validated('reason'),
            'user' => new UserResource($user->fresh('tailorProfile')),
        ]);
    }

    public function pendingTailors(): JsonResponse
    {
        $this->authorize('manage', User::class);

        $tailors = User::query()
            ->where('role', User::ROLE_TAILOR)
            ->whereNull('approved_at')
            ->with('tailorProfile.category')
            ->latest()
            ->paginate(30);

        return response()->json(UserResource::collection($tailors));
    }
}
