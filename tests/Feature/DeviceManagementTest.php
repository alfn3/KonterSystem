<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index page displays a list of devices.
     */
    public function test_device_index_displays_list(): void
    {
        $device1 = Device::create([
            'agent_id' => 'agent_001',
            'device_id' => 'dev_abc123',
            'device_name' => 'HP Kasir 1',
            'is_active' => true,
        ]);

        $device2 = Device::create([
            'agent_id' => 'agent_002',
            'device_id' => 'dev_xyz789',
            'device_name' => 'HP Kasir 2',
            'is_active' => false,
        ]);

        $response = $this->get(route('device.index'));

        $response->assertStatus(200);
        $response->assertSee('agent_001');
        $response->assertSee('dev_abc123');
        $response->assertSee('HP Kasir 1');
        $response->assertSee('agent_002');
        $response->assertSee('dev_xyz789');
        $response->assertSee('HP Kasir 2');
    }

    /**
     * Test search functionality.
     */
    public function test_device_list_can_be_searched(): void
    {
        Device::create([
            'agent_id' => 'agent_target',
            'device_id' => 'dev_target',
            'device_name' => 'Target Device',
            'is_active' => true,
        ]);

        Device::create([
            'agent_id' => 'agent_other',
            'device_id' => 'dev_other',
            'device_name' => 'Other Device',
            'is_active' => true,
        ]);

        $response = $this->get(route('device.index', ['search' => 'target']));

        $response->assertStatus(200);
        $response->assertSee('agent_target');
        $response->assertSee('Target Device');
        $response->assertDontSee('agent_other');
    }

    /**
     * Test device creation.
     */
    public function test_device_can_be_created(): void
    {
        $deviceData = [
            'agent_id' => 'agent_new',
            'device_id' => 'dev_new_123',
            'device_name' => 'Device Baru Toko',
            'is_active' => '1',
        ];

        $response = $this->post(route('device.store'), $deviceData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Device baru berhasil ditambahkan.');

        $this->assertDatabaseHas('devices', [
            'agent_id' => 'agent_new',
            'device_id' => 'dev_new_123',
            'device_name' => 'Device Baru Toko',
            'is_active' => true,
        ]);
    }

    /**
     * Test duplicate device_id for same agent_id validation.
     */
    public function test_device_duplicate_prevention(): void
    {
        Device::create([
            'agent_id' => 'agent_dup',
            'device_id' => 'dev_dup',
            'device_name' => 'Original Device',
            'is_active' => true,
        ]);

        $deviceData = [
            'agent_id' => 'agent_dup',
            'device_id' => 'dev_dup',
            'device_name' => 'Duplicate Device',
            'is_active' => '1',
        ];

        $response = $this->post(route('device.store'), $deviceData);

        $response->assertSessionHasErrors('device_id');
    }

    /**
     * Test device can be updated.
     */
    public function test_device_can_be_updated(): void
    {
        $device = Device::create([
            'agent_id' => 'agent_upd',
            'device_id' => 'dev_upd',
            'device_name' => 'Old Name',
            'is_active' => true,
        ]);

        $updateData = [
            'agent_id' => 'agent_upd',
            'device_id' => 'dev_upd_new',
            'device_name' => 'Updated Name',
            'is_active' => '0',
        ];

        $response = $this->put(route('device.update', $device->id), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Detail device berhasil diperbarui.');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_id' => 'dev_upd_new',
            'device_name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    /**
     * Test device status toggle action.
     */
    public function test_device_status_can_be_toggled(): void
    {
        $device = Device::create([
            'agent_id' => 'agent_tog',
            'device_id' => 'dev_tog',
            'device_name' => 'Toggle Device',
            'is_active' => true,
        ]);

        $response = $this->put(route('device.update', $device->id), [
            'toggle_status' => '1'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $device->refresh();
        $this->assertFalse($device->is_active);

        // Toggle back to active
        $response = $this->put(route('device.update', $device->id), [
            'toggle_status' => '1'
        ]);
        $device->refresh();
        $this->assertTrue($device->is_active);
    }

    /**
     * Test device deletion.
     */
    public function test_device_can_be_deleted(): void
    {
        $device = Device::create([
            'agent_id' => 'agent_del',
            'device_id' => 'dev_del',
            'device_name' => 'To Delete',
            'is_active' => true,
        ]);

        $response = $this->delete(route('device.destroy', $device->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Device berhasil dihapus.');

        $this->assertDatabaseMissing('devices', [
            'id' => $device->id
        ]);
    }

    /**
     * Test OTP verify auto-registers first device.
     */
    public function test_api_verify_otp_auto_registers_first_device(): void
    {
        $this->bypassAuth = true;
        
        $user = User::factory()->create([
            'agent_id' => 'operator_new',
            'whatsapp' => '08122334455',
            'email' => null,
            'password' => null,
        ]);

        // Place OTP code in Cache
        Cache::put('otp_08122334455', '999888', 300);

        // Send OTP verification with device_id
        $response = $this->postJson('/api/login/verify-otp', [
            'agent_id' => 'operator_new',
            'whatsapp' => '08122334455',
            'otp' => '999888',
            'device_id' => 'hardware_uuid_111',
            'device_name' => 'Xiaomi Redmi Note 10',
        ]);

        $response->assertStatus(200);

        // Check if device was automatically registered in database and activated
        $this->assertDatabaseHas('devices', [
            'agent_id' => 'operator_new',
            'device_id' => 'hardware_uuid_111',
            'device_name' => 'Xiaomi Redmi Note 10',
            'is_active' => true,
        ]);
    }

    /**
     * Test OTP verify allows active registered device.
     */
    public function test_api_verify_otp_allows_registered_active_device(): void
    {
        $this->bypassAuth = true;

        $user = User::factory()->create([
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334456',
            'email' => null,
            'password' => null,
        ]);

        // Pre-register active device
        Device::create([
            'agent_id' => 'operator_reg',
            'device_id' => 'active_dev_id',
            'device_name' => 'Reg Active',
            'is_active' => true,
        ]);

        Cache::put('otp_08122334456', '123456', 300);

        $response = $this->postJson('/api/login/verify-otp', [
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334456',
            'otp' => '123456',
            'device_id' => 'active_dev_id',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test OTP verify registers unregistered device when devices exist.
     */
    public function test_api_verify_otp_registers_unregistered_device_when_devices_exist(): void
    {
        $this->bypassAuth = true;

        $user = User::factory()->create([
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334457',
            'email' => null,
            'password' => null,
        ]);

        // Pre-register another device
        $oldDevice = Device::create([
            'agent_id' => 'operator_reg',
            'device_id' => 'first_dev_id',
            'device_name' => 'Reg Active',
            'is_active' => true,
        ]);

        Cache::put('otp_08122334457', '123456', 300);

        $response = $this->postJson('/api/login/verify-otp', [
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334457',
            'otp' => '123456',
            'device_id' => 'new_unregistered_dev_id',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Assert new device is registered and active
        $this->assertDatabaseHas('devices', [
            'agent_id' => 'operator_reg',
            'device_id' => 'new_unregistered_dev_id',
            'is_active' => true,
        ]);

        // Assert old device is deactivated
        $oldDevice->refresh();
        $this->assertFalse($oldDevice->is_active);
    }

    /**
     * Test OTP verify reactivates deactivated device.
     */
    public function test_api_verify_otp_reactivates_deactivated_device(): void
    {
        $this->bypassAuth = true;

        $user = User::factory()->create([
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334458',
            'email' => null,
            'password' => null,
        ]);

        // Pre-register deactivated device
        $device = Device::create([
            'agent_id' => 'operator_reg',
            'device_id' => 'blocked_dev_id',
            'device_name' => 'Reg Blocked',
            'is_active' => false,
        ]);

        Cache::put('otp_08122334458', '123456', 300);

        $response = $this->postJson('/api/login/verify-otp', [
            'agent_id' => 'operator_reg',
            'whatsapp' => '08122334458',
            'otp' => '123456',
            'device_id' => 'blocked_dev_id',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $device->refresh();
        $this->assertTrue($device->is_active);
    }

    /**
     * Test checkSession API responses.
     */
    public function test_api_check_session_responses(): void
    {
        $this->bypassAuth = true;

        // Create active user
        User::factory()->create([
            'agent_id' => 'agent_check',
            'is_active' => true,
        ]);

        // Active device
        Device::create([
            'agent_id' => 'agent_check',
            'device_id' => 'active_id',
            'is_active' => true,
        ]);

        // Inactive device
        Device::create([
            'agent_id' => 'agent_check',
            'device_id' => 'inactive_id',
            'is_active' => false,
        ]);

        // 1. Valid Active Device Check
        $response = $this->getJson('/api/login/check-session?agent_id=agent_check&device_id=active_id');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
        ]);

        // 2. Inactive Device Check
        $response = $this->getJson('/api/login/check-session?agent_id=agent_check&device_id=inactive_id');
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'valid' => false,
            'message' => 'Device dinonaktifkan.'
        ]);

        // 3. Unregistered Device Check
        $response = $this->getJson('/api/login/check-session?agent_id=agent_check&device_id=unregistered_id');
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'valid' => false,
            'message' => 'Device tidak terdaftar.'
        ]);
    }

    /**
     * Test registerDevice API endpoints.
     */
    public function test_api_register_device_endpoints(): void
    {
        $this->bypassAuth = true;

        // 1. Register a completely new device
        $response = $this->postJson('/api/devices', [
            'agent_id' => 'agent_register_test',
            'device_id' => 'dev_register_test_uuid',
            'device_name' => 'Register Test Device',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
        ]);
        $this->assertDatabaseHas('devices', [
            'agent_id' => 'agent_register_test',
            'device_id' => 'dev_register_test_uuid',
            'is_active' => true,
        ]);

        // 2. Registering an active device updates details
        $response = $this->postJson('/api/devices', [
            'agent_id' => 'agent_register_test',
            'device_id' => 'dev_register_test_uuid',
            'device_name' => 'Updated Register Test Device',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('devices', [
            'agent_id' => 'agent_register_test',
            'device_id' => 'dev_register_test_uuid',
            'device_name' => 'Updated Register Test Device',
            'is_active' => true,
        ]);

        // 3. Registering a deactivated device returns 403
        Device::where('agent_id', 'agent_register_test')
            ->where('device_id', 'dev_register_test_uuid')
            ->update(['is_active' => false]);

        $response = $this->postJson('/api/devices', [
            'agent_id' => 'agent_register_test',
            'device_id' => 'dev_register_test_uuid',
            'device_name' => 'Attempt reactivate without OTP',
        ]);
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'valid' => false,
        ]);
    }
}
