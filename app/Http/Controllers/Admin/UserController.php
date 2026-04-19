<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'role' => ['nullable', 'in:admin,customer,tailor'],
            'is_suspended' => ['nullable', 'boolean'],
        ]);

        $users = User::query()
            ->with('tailorProfile.category')
            ->when(isset($validated['role']), function ($query) use ($validated) {
                $query->where('role', $validated['role']);
            })
            ->when(array_key_exists('is_suspended', $validated), function ($query) use ($validated) {
                $query->where('is_suspended', $validated['is_suspended']);
            })
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $users]);
    }

    public function suspend(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'لا يمكن إيقاف حساب أدمن.',
            ], 422);
        }

        $user->update(['is_suspended' => true]);

        if ($user->role === 'tailor' && $user->tailorProfile) {
            $user->tailorProfile->update(['status' => 'offline']);
        }

        return response()->json([
            'message' => 'تم إيقاف الحساب بنجاح',
            'user' => $user->fresh('tailorProfile'),
        ]);
    }

    public function pendingTailors(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $tailors = User::query()
            ->where('role', 'tailor')
            ->whereNull('approved_at')
            ->with('tailorProfile.category')
            ->latest()
            ->paginate(30);

        return response()->json([
            'data' => $tailors,
        ]);
    }
}
