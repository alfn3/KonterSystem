<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index page displays a list of users.
     */
    public function test_user_index_displays_list(): void
    {
        // One user is auto-created in TestCase::setUp(), let's create two more
        $user1 = User::factory()->create(['name' => 'Budi Utomo', 'email' => 'budi@example.com']);
        $user2 = User::factory()->create(['name' => 'Citra Lestari', 'email' => 'citra@example.com']);

        $response = $this->get(route('user.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Utomo');
        $response->assertSee('budi@example.com');
        $response->assertSee('Citra Lestari');
        $response->assertSee('citra@example.com');
    }

    /**
     * Test index page search functionality.
     */
    public function test_user_list_can_be_searched(): void
    {
        $user1 = User::factory()->create(['name' => 'Alexander', 'email' => 'alex@example.com']);
        $user2 = User::factory()->create(['name' => 'Robert', 'email' => 'robert@example.com']);

        $response = $this->get(route('user.index', ['search' => 'Alex']));

        $response->assertStatus(200);
        $response->assertSee('Alexander');
        $response->assertDontSee('Robert');
    }

    /**
     * Test that user can be created.
     */
    public function test_user_can_be_created(): void
    {
        $userData = [
            'name' => 'Doni Pratama',
            'email' => 'doni@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'User berhasil ditambahkan.');

        $this->assertDatabaseHas('users', [
            'name' => 'Doni Pratama',
            'email' => 'doni@example.com',
        ]);
        
        $user = User::where('email', 'doni@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test password confirmation validation.
     */
    public function test_user_creation_requires_matching_password_confirmation(): void
    {
        $userData = [
            'name' => 'Doni Pratama',
            'email' => 'doni@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'doni@example.com']);
    }

    /**
     * Test email uniqueness validation.
     */
    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'doni@example.com']);

        $userData = [
            'name' => 'Doni Baru',
            'email' => 'doni@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that a user can be updated without changing password.
     */
    public function test_user_can_be_updated_without_password_change(): void
    {
        $user = User::factory()->create([
            'name' => 'Eka Saputra',
            'email' => 'eka@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $updateData = [
            'name' => 'Eka Saputra Updated',
            'email' => 'eka.updated@example.com',
        ];

        $response = $this->put(route('user.update', $user->id), $updateData);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'User berhasil diperbarui.');

        $user->refresh();
        $this->assertEquals('Eka Saputra Updated', $user->name);
        $this->assertEquals('eka.updated@example.com', $user->email);
        $this->assertTrue(Hash::check('old-password', $user->password)); // Password remains unchanged
    }

    /**
     * Test that user password can be updated.
     */
    public function test_user_password_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Eka Saputra',
            'email' => 'eka@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $updateData = [
            'name' => 'Eka Saputra',
            'email' => 'eka@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ];

        $response = $this->put(route('user.update', $user->id), $updateData);

        $response->assertRedirect(route('user.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
    }

    /**
     * Test user deletion.
     */
    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create(['email' => 'target@example.com']);

        $response = $this->delete(route('user.destroy', $user->id));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'User berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['email' => 'target@example.com']);
    }

    /**
     * Test self-deletion restriction.
     */
    public function test_user_cannot_delete_themselves(): void
    {
        // Currently logged-in user is $this->actingAs() user
        $myUser = auth()->user();

        $response = $this->delete(route('user.destroy', $myUser->id));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $myUser->id]);
    }

    /**
     * Test creating a mobile-only user (no email/password, has whatsapp and agent_id).
     */
    public function test_mobile_only_user_can_be_created(): void
    {
        $userData = [
            'name' => 'Mobile Operator',
            'agent_id' => 'operator2',
            'whatsapp' => '081234567890',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'User berhasil ditambahkan.');

        $this->assertDatabaseHas('users', [
            'name' => 'Mobile Operator',
            'agent_id' => 'operator2',
            'whatsapp' => '081234567890',
            'email' => null,
            'password' => null,
        ]);
    }

    /**
     * Test creating user fails when both email and whatsapp are missing.
     */
    public function test_user_creation_fails_when_both_email_and_whatsapp_missing(): void
    {
        $userData = [
            'name' => 'Invalid User',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertSessionHasErrors(['email', 'whatsapp']);
    }

    /**
     * Test unique whatsapp number validation.
     */
    public function test_whatsapp_number_must_be_unique(): void
    {
        User::factory()->create(['whatsapp' => '081234567890', 'agent_id' => 'op1', 'email' => null, 'password' => null]);

        $userData = [
            'name' => 'Another User',
            'agent_id' => 'op2',
            'whatsapp' => '081234567890',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertSessionHasErrors('whatsapp');
    }

    /**
     * Test unique agent_id validation.
     */
    public function test_agent_id_must_be_unique(): void
    {
        User::factory()->create(['whatsapp' => '081234567891', 'agent_id' => 'op1', 'email' => null, 'password' => null]);

        $userData = [
            'name' => 'Another User',
            'agent_id' => 'op1',
            'whatsapp' => '081234567892',
        ];

        $response = $this->post(route('user.store'), $userData);

        $response->assertSessionHasErrors('agent_id');
    }

    /**
     * Test mobile API login with valid WhatsApp number.
     */
    public function test_mobile_api_login_success(): void
    {
        $user = User::factory()->create(['whatsapp' => '08999999999', 'email' => null, 'password' => null]);

        $response = $this->postJson('/api/login', [
            'whatsapp' => '08999999999',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'user' => [
                         'id' => $user->id,
                         'name' => $user->name,
                         'whatsapp' => $user->whatsapp,
                     ]
                 ]);
    }

    /**
     * Test mobile API login with invalid/unregistered WhatsApp number.
     */
    public function test_mobile_api_login_fails_when_unregistered(): void
    {
        $response = $this->postJson('/api/login', [
            'whatsapp' => '08000000000',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Nomor WhatsApp tidak terdaftar atau tidak aktif.'
                 ]);
    }

    /**
     * Test OTP request success.
     */
    public function test_request_otp_success(): void
    {
        User::factory()->create(['whatsapp' => '081234567890', 'agent_id' => 'operator_test', 'email' => null, 'password' => null]);

        $response = $this->postJson('/api/login/request-otp', [
            'agent_id' => 'operator_test',
            'whatsapp' => '081234567890',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda.'
                 ]);
    }

    /**
     * Test OTP verification success.
     */
    public function test_verify_otp_success(): void
    {
        $user = User::factory()->create(['whatsapp' => '081234567890', 'agent_id' => 'operator_test', 'email' => null, 'password' => null]);

        // Request OTP to generate it in cache
        $this->postJson('/api/login/request-otp', [
            'agent_id' => 'operator_test',
            'whatsapp' => '081234567890',
        ]);

        // Get OTP from Cache
        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_081234567890');

        $response = $this->postJson('/api/login/verify-otp', [
            'agent_id' => 'operator_test',
            'whatsapp' => '081234567890',
            'otp' => $cachedOtp,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'user' => [
                         'id' => $user->id,
                         'name' => $user->name,
                         'whatsapp' => $user->whatsapp,
                     ]
                 ]);
    }

    /**
     * Test user active status toggle.
     */
    public function test_user_is_active_status_can_be_toggled(): void
    {
        $user = User::factory()->create([
            'email' => 'toggle_user@example.com',
            'is_active' => true,
        ]);

        $response = $this->put(route('user.update', $user->id), [
            'toggle_status' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->is_active);

        // Toggle back to active
        $response = $this->put(route('user.update', $user->id), [
            'toggle_status' => '1',
        ]);
        $user->refresh();
        $this->assertTrue($user->is_active);
    }

    /**
     * Test that deactivated user cannot login via web portal.
     */
    public function test_deactivated_user_cannot_login_web(): void
    {
        // Deactivate user
        $user = User::factory()->create([
            'email' => 'deact@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        // Logout default authenticated user to allow guest middleware to pass
        auth()->logout();

        // Attempt login
        $response = $this->post('/login', [
            'email' => 'deact@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    /**
     * Test that deactivated user cannot request OTP / login via mobile API.
     */
    public function test_deactivated_user_cannot_login_mobile_api(): void
    {
        $user = User::factory()->create([
            'agent_id' => 'operator_deact',
            'whatsapp' => '081234567899',
            'email' => null,
            'password' => null,
            'is_active' => false,
        ]);

        // Attempt mobileLogin
        $response = $this->postJson('/api/login', [
            'whatsapp' => '081234567899',
        ]);
        $response->assertStatus(403);

        // Attempt request OTP
        $response = $this->postJson('/api/login/request-otp', [
            'agent_id' => 'operator_deact',
            'whatsapp' => '081234567899',
        ]);
        $response->assertStatus(403);

        // Attempt check-session
        $response = $this->getJson('/api/login/check-session?agent_id=operator_deact&device_id=dev_id');
        $response->assertStatus(403);
    }
}
