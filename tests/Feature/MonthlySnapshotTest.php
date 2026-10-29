<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MonthlySnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonthlySnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_snapshots_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('snapshots.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Snapshot Bulanan');
    }

    public function test_admin_can_create_monthly_snapshot(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        Employee::create([
            'personnel_number' => '10001',
            'nama' => 'Budi Santoso',
            'status_karyawan' => 'Karpim',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('snapshots.store'), [
                'bulan' => 10,
                'tahun' => 2026,
                'catatan' => 'Snapshot penutupan Oktober 2026',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('snapshots.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('monthly_snapshots', [
            'bulan' => 10,
            'tahun' => 2026,
            'jumlah_karyawan' => 1,
        ]);
    }

    public function test_admin_can_create_snapshot_without_batih(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        Employee::create([
            'personnel_number' => '10002',
            'nama' => 'Agus Harian',
            'status_karyawan' => 'BHL',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('snapshots.store'), [
                'bulan' => 10,
                'tahun' => 2026,
                'include_family' => 0,
                'categories' => ['BHL'],
                'catatan' => 'Snapshot BHL tanpa batih',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('snapshots.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('monthly_snapshots', [
            'bulan' => 10,
            'tahun' => 2026,
            'include_family' => false,
            'jumlah_karyawan' => 1,
        ]);
    }

    public function test_user_can_download_snapshot(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $fakePath = 'snapshots/2026/10_Snapshot_Karyawan_Oktober_2026.xlsx';
        Storage::disk('local')->put($fakePath, 'fake-excel-content');

        $snapshot = MonthlySnapshot::create([
            'bulan' => 10,
            'tahun' => 2026,
            'nama_file' => 'Snapshot_Karyawan_Oktober_2026.xlsx',
            'path' => $fakePath,
            'jumlah_karyawan' => 10,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('snapshots.download', $snapshot));

        $response->assertOk();
        $this->assertStringContainsString('Snapshot_Karyawan_Oktober_2026.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_delete_snapshot(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $fakePath = 'snapshots/2026/10_Snapshot_Karyawan_Oktober_2026.xlsx';
        Storage::disk('local')->put($fakePath, 'dummy');

        $snapshot = MonthlySnapshot::create([
            'bulan' => 10,
            'tahun' => 2026,
            'nama_file' => 'Snapshot_Karyawan_Oktober_2026.xlsx',
            'path' => $fakePath,
            'jumlah_karyawan' => 5,
            'user_id' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('snapshots.destroy', $snapshot));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('snapshots.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('monthly_snapshots', ['id' => $snapshot->id]);
        Storage::disk('local')->assertMissing($fakePath);
    }

    public function test_non_admin_cannot_create_or_delete_snapshot(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $responseStore = $this
            ->actingAs($user)
            ->post(route('snapshots.store'), [
                'bulan' => 5,
                'tahun' => 2026,
            ]);

        $responseStore->assertForbidden();

        $snapshot = MonthlySnapshot::create([
            'bulan' => 5,
            'tahun' => 2026,
            'nama_file' => 'Snapshot_Mei_2026.xlsx',
            'path' => 'snapshots/2026/5_Snapshot.xlsx',
            'jumlah_karyawan' => 2,
            'user_id' => $user->id,
        ]);

        $responseDelete = $this
            ->actingAs($user)
            ->delete(route('snapshots.destroy', $snapshot));

        $responseDelete->assertForbidden();
    }
}
