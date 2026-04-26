<?php

namespace Tests\Feature;

use App\Models\PhoneVerificationCode;
use App\Models\MailSetting;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_logout_flow(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Customer One',
            'email' => 'customer1@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $register->assertCreated()->json('token');

        $this->getJson('/api/auth/me', ['Authorization' => 'Bearer '.$token])->assertOk();
        $this->postJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$token])->assertOk();
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => 'password',
            'is_suspended' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ])->assertStatus(403);
    }

    public function test_tailor_registration_requires_extended_fields(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Tailor Required Fields',
            'email' => 'tailor-required@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_TAILOR,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'phone',
                'specialization',
                'work_wilaya',
                'years_of_experience',
                'gender',
                'workers_count',
            ]);
    }

    public function test_tailor_registration_creates_profile_and_requires_phone_verification(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Tailor One',
            'email' => 'tailor1@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_TAILOR,
            'phone' => '+213550010000',
            'specialization' => 'Traditionnel',
            'work_wilaya' => 'Algiers',
            'years_of_experience' => 6,
            'gender' => 'male',
            'workers_count' => 3,
        ])->assertCreated();

        $token = $register->json('token');
        $userId = (int) $register->json('data.id');

        $this->assertTrue((bool) $register->json('meta.requires_phone_verification'));
        $this->assertDatabaseHas('tailor_profiles', [
            'user_id' => $userId,
            'specialization' => 'Traditionnel',
            'work_wilaya' => 'Algiers',
            'years_of_experience' => 6,
            'gender' => 'male',
            'workers_count' => 3,
        ]);

        $this->assertDatabaseHas('phone_verification_codes', [
            'user_id' => $userId,
            'phone' => '+213550010000',
        ]);

        $verifyRecord = PhoneVerificationCode::query()->where('user_id', $userId)->latest('id')->firstOrFail();
        $verifyRecord->forceFill([
            'code_hash' => hash('sha256', '+213550010000|123456|'.config('app.key')),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ])->save();

        $this->actingAs(User::query()->findOrFail($userId), 'sanctum')
            ->postJson('/api/auth/phone-verification/verify', [
                'code' => '123456',
            ], ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('meta.requires_phone_verification', false);

        $this->assertNotNull(User::query()->findOrFail($userId)->phone_verified_at);
    }

    public function test_unverified_tailor_with_phone_cannot_access_tailor_workspace_routes(): void
    {
        $tailor = User::factory()->tailor()->create([
            'approved_at' => now(),
            'phone' => '+213550020000',
            'phone_verified_at' => null,
        ]);

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->getJson('/api/tailor/profile')
            ->assertForbidden()
            ->assertJsonPath('message', 'Phone verification is required before accessing tailor workspace.');
    }

    public function test_tailor_phone_verification_can_be_disabled_from_settings(): void
    {
        MailSetting::current()->forceFill([
            'tailor_phone_verification_enabled' => false,
        ])->save();

        $register = $this->postJson('/api/auth/register', [
            'name' => 'Tailor Phone Toggle',
            'email' => 'tailor-phone-toggle@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_TAILOR,
            'phone' => '+213550040000',
            'specialization' => 'Traditionnel',
            'work_wilaya' => 'Algiers',
            'years_of_experience' => 4,
            'gender' => 'male',
            'workers_count' => 2,
        ])->assertCreated();

        $this->assertFalse((bool) $register->json('meta.requires_phone_verification'));
        $this->assertDatabaseMissing('phone_verification_codes', [
            'user_id' => (int) $register->json('data.id'),
        ]);
    }

    public function test_tailor_email_verification_gate_is_controlled_by_settings(): void
    {
        MailSetting::current()->forceFill([
            'tailor_email_verification_enabled' => true,
            'tailor_phone_verification_enabled' => false,
        ])->save();

        $tailor = User::factory()->tailor()->create([
            'approved_at' => now(),
            'email_verified_at' => null,
            'phone' => '+213550050000',
            'phone_verified_at' => null,
        ]);

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->getJson('/api/tailor/profile')
            ->assertForbidden()
            ->assertJsonPath('message', 'Email verification is required before accessing tailor workspace.');

        $tailor->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($tailor, 'sanctum')
            ->getJson('/api/tailor/profile')
            ->assertOk();
    }

    public function test_tailor_registration_can_upload_optional_commercial_register_file(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/auth/register', [
            'name' => 'Tailor Upload',
            'email' => 'tailor-upload@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_TAILOR,
            'phone' => '+213550030000',
            'specialization' => 'Classique',
            'work_wilaya' => 'Oran',
            'years_of_experience' => 10,
            'gender' => 'female',
            'workers_count' => 6,
            'commercial_register_file' => UploadedFile::fake()->image('register-proof.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $userId = (int) $response->assertCreated()->json('data.id');
        $path = TailorProfile::query()->where('user_id', $userId)->value('commercial_register_path');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists((string) $path);
    }
}
