<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\MailSetting;
use App\Models\TailorProfile;
use App\Models\User;
use App\Services\Mail\MailConfigurationService;
use App\Services\PhoneVerification\PhoneVerificationService;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        PhoneVerificationService $phoneVerificationService,
        MailConfigurationService $mailConfigurationService,
    ): JsonResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? User::ROLE_CUSTOMER;

        /** @var User $user */
        $user = DB::transaction(function () use ($request, $data, $role): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $role,
                'approved_at' => $role === User::ROLE_TAILOR ? null : now(),
                'is_suspended' => false,
                'email_verified_at' => $role === User::ROLE_TAILOR ? null : now(),
                'phone_verified_at' => null,
            ]);

            if ($role === User::ROLE_TAILOR) {
                $commercialRegisterPath = null;
                $mappedCategoryId = Category::query()
                    ->where('tailor_specialization', $data['specialization'] ?? null)
                    ->value('id');

                if ($request->hasFile('commercial_register_file')) {
                    $commercialRegisterPath = $request->file('commercial_register_file')
                        ->store("tailors/{$user->id}/commercial-registers", 'public');
                }

                $user->tailorProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'category_id' => $mappedCategoryId,
                        'specialization' => $data['specialization'] ?? null,
                        'work_wilaya' => $data['work_wilaya'] ?? null,
                        'years_of_experience' => $data['years_of_experience'] ?? null,
                        'gender' => $data['gender'] ?? null,
                        'workers_count' => $data['workers_count'] ?? null,
                        'commercial_register_path' => $commercialRegisterPath,
                        'status' => TailorProfile::STATUS_OFFLINE,
                    ],
                );
            }

            return $user;
        });

        $user->load('tailorProfile.category');
        $token = $user->createToken($data['device_name'] ?? 'api-token')->plainTextToken;

        $requiresPhoneVerification = $this->requiresTailorPhoneVerification($user);
        $requiresEmailVerification = $this->requiresTailorEmailVerification($user);
        $verificationMeta = null;
        $dispatchFailed = false;
        $emailVerificationDispatchFailed = false;

        if ($requiresEmailVerification) {
            try {
                $mailConfigurationService->applyRuntimeConfiguration();

                if ($mailConfigurationService->canSend()) {
                    $user->sendEmailVerificationNotification();
                } else {
                    $mailConfigurationService->logSkipped('tailor-email-verification', ['user_id' => $user->id]);
                    $emailVerificationDispatchFailed = true;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $emailVerificationDispatchFailed = true;
            }
        }

        if ($requiresPhoneVerification) {
            try {
                $verificationMeta = $phoneVerificationService->send($user);
            } catch (\Throwable $exception) {
                report($exception);
                $dispatchFailed = true;
            }
        }

        return response()->json([
            'message' => __('messages.auth.registered_success'),
            'token' => $token,
            'data' => new UserResource($user),
            'meta' => [
                'requires_phone_verification' => $requiresPhoneVerification,
                'requires_email_verification' => $requiresEmailVerification,
                'phone_verification' => $verificationMeta,
                'phone_verification_dispatch_failed' => $dispatchFailed,
                'email_verification_dispatch_failed' => $emailVerificationDispatchFailed,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()
            ->where('email', $data['email'])
            ->with('tailorProfile.category')
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => __('messages.auth.invalid_credentials')], 422);
        }

        if ($user->is_suspended) {
            return response()->json(['message' => __('messages.auth.user_suspended')], 403);
        }

        $token = $user->createToken($data['device_name'] ?? 'api-token')->plainTextToken;

        return response()->json([
            'message' => __('messages.auth.logged_in_success'),
            'token' => $token,
            'data' => new UserResource($user),
            'meta' => [
                'requires_phone_verification' => $this->requiresTailorPhoneVerification($user),
                'requires_email_verification' => $this->requiresTailorEmailVerification($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('tailorProfile.category');

        return response()->json(['data' => new UserResource($user)]);
    }

    public function sendEmailVerification(Request $request, MailConfigurationService $mailConfigurationService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== User::ROLE_TAILOR) {
            return response()->json(['message' => __('messages.auth.email_verification_tailor_only')], 403);
        }

        if (! MailSetting::tailorEmailVerificationEnabled()) {
            return response()->json(['message' => __('messages.auth.email_verification_disabled')], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('messages.auth.email_already_verified'),
                'data' => new UserResource($user->fresh('tailorProfile.category')),
                'meta' => [
                    'requires_email_verification' => false,
                    'requires_phone_verification' => $this->requiresTailorPhoneVerification($user),
                ],
            ]);
        }

        $mailConfigurationService->applyRuntimeConfiguration();

        if (! $mailConfigurationService->canSend()) {
            return response()->json(['message' => __('messages.auth.email_verification_mail_disabled')], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('messages.auth.email_verification_link_sent'),
            'data' => new UserResource($user->fresh('tailorProfile.category')),
            'meta' => [
                'requires_email_verification' => $this->requiresTailorEmailVerification($user),
                'requires_phone_verification' => $this->requiresTailorPhoneVerification($user),
            ],
        ]);
    }

    public function sendPhoneVerificationCode(Request $request, PhoneVerificationService $phoneVerificationService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== User::ROLE_TAILOR) {
            return response()->json(['message' => __('messages.auth.phone_verification_tailor_only')], 403);
        }

        if (! MailSetting::tailorPhoneVerificationEnabled()) {
            return response()->json(['message' => __('messages.auth.phone_verification_disabled')], 422);
        }

        $verificationMeta = $phoneVerificationService->send($user);
        $user = $user->fresh('tailorProfile.category');

        return response()->json([
            'message' => __('messages.auth.phone_verification_code_sent'),
            'data' => new UserResource($user),
            'meta' => [
                'requires_phone_verification' => $this->requiresTailorPhoneVerification($user),
                'phone_verification' => $verificationMeta,
            ],
        ]);
    }

    public function verifyPhoneCode(VerifyPhoneRequest $request, PhoneVerificationService $phoneVerificationService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== User::ROLE_TAILOR) {
            return response()->json(['message' => __('messages.auth.phone_verification_tailor_only')], 403);
        }

        $phoneVerificationService->verify($user, $request->validated('code'));
        $user = $user->fresh('tailorProfile.category');

        return response()->json([
            'message' => __('messages.auth.phone_verified_success'),
            'data' => new UserResource($user),
            'meta' => [
                'requires_phone_verification' => $this->requiresTailorPhoneVerification($user),
            ],
        ]);
    }

    public function tailorRegistrationMetadata(): JsonResponse
    {
        return response()->json([
            'data' => [
                'specializations' => TailorOnboardingOptions::SPECIALIZATIONS,
                'genders' => TailorOnboardingOptions::genderOptions(),
                'wilayas' => TailorOnboardingOptions::WILAYAS,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->boolean('all_devices')) {
            $request->user()?->tokens()->delete();
        } else {
            $request->user()?->currentAccessToken()?->delete();
        }

        return response()->json(['message' => __('messages.auth.logged_out_success')]);
    }

    private function requiresTailorPhoneVerification(User $user): bool
    {
        return $user->role === User::ROLE_TAILOR
            && MailSetting::tailorPhoneVerificationEnabled()
            && filled($user->phone)
            && $user->phone_verified_at === null;
    }

    private function requiresTailorEmailVerification(User $user): bool
    {
        return $user->role === User::ROLE_TAILOR
            && MailSetting::tailorEmailVerificationEnabled()
            && ! $user->hasVerifiedEmail();
    }
}
