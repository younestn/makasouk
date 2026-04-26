<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Http\Resources\UserResource;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($user): void {
            $user->update(['is_suspended' => true]);

            if ($user->role === User::ROLE_TAILOR && $user->tailorProfile) {
                $user->tailorProfile->update(['status' => TailorProfile::STATUS_OFFLINE]);
            }
        });

        return response()->json([
            'message' => __('messages.admin.user_suspended_success'),
            'reason' => $request->validated('reason'),
            'user' => new UserResource($user->fresh('tailorProfile')),
        ]);
    }

    public function unsuspend(User $user): JsonResponse
    {
        $this->authorize('manage', User::class);

        $user->update(['is_suspended' => false]);

        return response()->json([
            'message' => __('messages.admin.user_unsuspended_success'),
            'user' => new UserResource($user->fresh('tailorProfile')),
        ]);
    }

    public function approveTailor(User $user): JsonResponse
    {
        $this->authorize('manage', User::class);

        if ($user->role !== User::ROLE_TAILOR) {
            return response()->json(['message' => __('messages.admin.only_tailor_accounts_approvable')], 422);
        }

        if ($user->approved_at !== null) {
            return response()->json(['message' => __('messages.admin.tailor_already_approved')], 422);
        }

        DB::transaction(function () use ($user): void {
            $user->update(['approved_at' => now()]);

            if ($user->tailorProfile) {
                $user->tailorProfile->update(['status' => TailorProfile::STATUS_OFFLINE]);
            }
        });

        return response()->json([
            'message' => __('messages.admin.tailor_approved_success'),
            'user' => new UserResource($user->fresh('tailorProfile.category')),
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
