<?php

namespace Tests\Feature;

use App\Events\AntrianDiperbarui;
use App\Exports\ObatExport;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\BuktiPembayaranDiunggah;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PoliklinikFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_pasien_menampilkan_banner_antrian_aktif_dan_tabel_jadwal(): void
    {
        $poli = Poli::factory()->create(['nama_poli' => 'Poli Gigi']);
        $dokter = User::factory()->dokter()->create(['nama' => 'Budi Santoso', 'id_poli' => $poli->id]);
        $jadwal = JadwalPeriksa::factory()->create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
        ]);

        $pasienSedangDilayani = User::factory()->pasien()->create();
        DaftarPoli::factory()->diperiksa()->create([
            'id_pasien' => $pasienSedangDilayani->id,
            'id_jadwal' => $jadwal->id,
            'no_antrian' => 1,
        ]);

        $pasien = User::factory()->pasien()->create();
        DaftarPoli::factory()->create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'no_antrian' => 2,
            'status' => DaftarPoli::STATUS_MENUNGGU,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));

        $response->assertOk()
            ->assertSee('Antrian Aktif Anda')
            ->assertSee('Poli Gigi')
            ->assertSee('Budi Santoso')
            ->assertSee('id="bannerAntrianAktif"', false);
    }

    public function test_dashboard_pasien_menyembunyikan_banner_jika_tidak_ada_antrian_aktif(): void
    {
        $pasien = User::factory()->pasien()->create();

        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));

        $response->assertOk()
            ->assertSee('Belum Ada Antrian Aktif')
            ->assertDontSee('id="bannerAntrianAktif"', false);
    }

    public function test_pasien_tidak_dapat_mendaftar_dua_poli_sekaligus_dan_bisa_daftar_setelah_selesai(): void
    {
        $poli1 = Poli::factory()->create();
        $poli2 = Poli::factory()->create();
        $dokter1 = User::factory()->dokter()->create(['id_poli' => $poli1->id]);
        $dokter2 = User::factory()->dokter()->create(['id_poli' => $poli2->id]);
        $jadwal1 = JadwalPeriksa::factory()->create(['id_dokter' => $dokter1->id]);
        $jadwal2 = JadwalPeriksa::factory()->create(['id_dokter' => $dokter2->id]);
        $pasien = User::factory()->pasien()->create();

        // Pendaftaran pertama berhasil
        $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $poli1->id,
            'id_jadwal' => $jadwal1->id,
            'keluhan' => 'Sakit kepala',
        ])->assertSessionHas('success');

        $this->assertDatabaseCount('daftar_poli', 1);

        // Pendaftaran kedua ditolak karena pemeriksaan pertama belum selesai
        $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $poli2->id,
            'id_jadwal' => $jadwal2->id,
            'keluhan' => 'Sakit gigi',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('daftar_poli', 1);

        // Setelah pemeriksaan pertama selesai, pasien dapat mendaftar kembali
        $daftarPertama = DaftarPoli::first();
        $daftarPertama->update(['status' => DaftarPoli::STATUS_SELESAI]);
        Periksa::factory()->create(['id_daftar_poli' => $daftarPertama->id]);

        $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $poli2->id,
            'id_jadwal' => $jadwal2->id,
            'keluhan' => 'Sakit gigi',
        ])->assertSessionHas('success');

        $this->assertDatabaseCount('daftar_poli', 2);
    }

    public function test_pemanggilan_dan_penyelesaian_antrian_memancarkan_event_realtime(): void
    {
        Event::fake([AntrianDiperbarui::class]);

        $poli = Poli::factory()->create();
        $dokter = User::factory()->dokter()->create(['id_poli' => $poli->id]);
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $dokter->id]);
        $daftar = DaftarPoli::factory()->create([
            'id_jadwal' => $jadwal->id,
            'no_antrian' => 3,
            'status' => DaftarPoli::STATUS_MENUNGGU,
        ]);

        $this->actingAs($dokter)
            ->patch(route('periksa-pasien.panggil', $daftar->id))
            ->assertRedirect(route('periksa-pasien.index'));

        $this->assertDatabaseHas('daftar_poli', [
            'id' => $daftar->id,
            'status' => DaftarPoli::STATUS_DIPERIKSA,
        ]);

        Event::assertDispatched(AntrianDiperbarui::class, function (AntrianDiperbarui $event) use ($jadwal): bool {
            return $event->idJadwal === $jadwal->id && $event->nomorDilayani === 3;
        });

        $this->actingAs($dokter)
            ->post(route('periksa-pasien.store'), [
                'id_daftar_poli' => $daftar->id,
                'catatan' => 'Istirahat cukup',
                'obat_json' => '[]',
                'biaya_periksa' => 0,
            ])
            ->assertRedirect(route('periksa-pasien.index'));

        $this->assertDatabaseHas('daftar_poli', [
            'id' => $daftar->id,
            'status' => DaftarPoli::STATUS_SELESAI,
        ]);
    }

    public function test_upload_bukti_pembayaran_dan_verifikasi_admin_mengirim_notifikasi_tanpa_duplikat(): void
    {
        Storage::fake('local');

        $poli = Poli::factory()->create(['nama_poli' => 'Poli Anak']);
        $dokter = User::factory()->dokter()->create(['id_poli' => $poli->id]);
        $pasien = User::factory()->pasien()->create(['nama' => 'Siti Aminah']);
        $admin = User::factory()->admin()->create();
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $dokter->id]);
        $daftar = DaftarPoli::factory()->selesai()->create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
        ]);
        $periksa = Periksa::factory()->create([
            'id_daftar_poli' => $daftar->id,
            'biaya_periksa' => 175000,
            'status_bayar' => Periksa::BAYAR_BELUM,
        ]);

        $file = UploadedFile::fake()->image('bukti.jpg');

        // Pasien mengunggah bukti pembayaran
        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $periksa->id), [
                'bukti_bayar' => $file,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('periksa', [
            'id' => $periksa->id,
            'status_bayar' => Periksa::BAYAR_MENUNGGU_VERIFIKASI,
        ]);

        // Dokter menerima tepat 1 notifikasi database
        $this->assertSame(1, $dokter->notifications()->where('type', BuktiPembayaranDiunggah::class)->count());

        // Percobaan upload ulang tidak mengirim notifikasi ganda
        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $periksa->id), [
                'bukti_bayar' => UploadedFile::fake()->image('bukti-2.jpg'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(1, $dokter->notifications()->where('type', BuktiPembayaranDiunggah::class)->count());

        // Admin mengonfirmasi pembayaran menjadi Lunas
        $this->actingAs($admin)
            ->patch(route('admin.pembayaran.verifikasi', $periksa->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('periksa', [
            'id' => $periksa->id,
            'status_bayar' => Periksa::BAYAR_LUNAS,
        ]);

        // Pasien menerima tepat 1 notifikasi database
        $this->assertSame(1, $pasien->notifications()->where('type', PembayaranDiverifikasi::class)->count());

        // Verifikasi ganda dicegah
        $this->actingAs($admin)
            ->patch(route('admin.pembayaran.verifikasi', $periksa->id))
            ->assertSessionHas('error');

        $this->assertSame(1, $pasien->notifications()->where('type', PembayaranDiverifikasi::class)->count());
    }

    public function test_otorisasi_notifikasi_dan_fitur_tandai_sudah_dibaca(): void
    {
        $pasien1 = User::factory()->pasien()->create();
        $pasien2 = User::factory()->pasien()->create();
        $periksa = Periksa::factory()->create();

        $pasien1->notify(new PembayaranDiverifikasi($periksa));
        $notif = $pasien1->notifications()->firstOrFail();

        // Pasien lain tidak berhak menandai notifikasi milik pasien1
        $this->actingAs($pasien2)
            ->patch(route('notifikasi.read', $notif->id))
            ->assertNotFound();

        $this->assertNull($notif->fresh()->read_at);

        // Pemilik notifikasi dapat menandai sebagai sudah dibaca
        $this->actingAs($pasien1)
            ->patch(route('notifikasi.read', $notif->id))
            ->assertSessionHas('success');

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_riwayat_pendaftaran_dan_detail_pemeriksaan_bebas_n_plus_one(): void
    {
        $poli = Poli::factory()->create(['nama_poli' => 'Poli Mata']);
        $dokter = User::factory()->dokter()->create(['nama' => 'Dr. Andi', 'id_poli' => $poli->id]);
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $dokter->id]);
        $pasien = User::factory()->pasien()->create();
        $obat = Obat::factory()->create(['nama_obat' => 'Paracetamol', 'harga' => 15000]);

        foreach (range(1, 5) as $no) {
            $daftar = DaftarPoli::factory()->selesai()->create([
                'id_pasien' => $pasien->id,
                'id_jadwal' => $jadwal->id,
                'no_antrian' => $no,
            ]);
            $periksa = Periksa::factory()->create([
                'id_daftar_poli' => $daftar->id,
                'catatan' => 'Catatan pemeriksaan '.$no,
                'biaya_periksa' => 165000,
            ]);
            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat' => $obat->id,
            ]);
        }

        DB::enableQueryLog();
        $this->actingAs($pasien)
            ->get(route('pasien.riwayat.index'))
            ->assertOk()
            ->assertSee('Poli Mata')
            ->assertSee('Dr. Andi');
        $jumlahQuery = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Eager loading menjaga jumlah query tetap konstan (<= 8 query termasuk notifikasi header)
        $this->assertLessThanOrEqual(8, $jumlahQuery);

        $daftarTerakhir = DaftarPoli::where('id_pasien', $pasien->id)->latest('id')->firstOrFail();

        $this->actingAs($pasien)
            ->get(route('pasien.riwayat.show', $daftarTerakhir->id))
            ->assertOk()
            ->assertSee('Catatan pemeriksaan 5')
            ->assertSee('Paracetamol')
            ->assertSee('165.000');
    }

    public function test_export_excel_untuk_admin_dan_dokter(): void
    {
        Excel::fake();

        $admin = User::factory()->admin()->create();
        $dokter = User::factory()->dokter()->create();
        $obat = Obat::factory()->create([
            'nama_obat' => 'Amoxicillin',
            'kemasan' => 'Strip',
            'stok' => 45,
            'harga' => 25000,
        ]);

        // Verifikasi ObatExport menyertakan kolom stok dan harga
        $exportObat = new ObatExport;
        $this->assertContains('Stok', $exportObat->headings());
        $this->assertContains('Harga (Rp)', $exportObat->headings());
        $this->assertSame([1, 'Amoxicillin', 'Strip', 45, 25000], $exportObat->map($obat));

        $this->actingAs($admin)->get(route('admin.export.dokter'))->assertOk();
        $this->actingAs($admin)->get(route('admin.export.pasien'))->assertOk();
        $this->actingAs($admin)->get(route('admin.export.obat'))->assertOk();

        $this->actingAs($dokter)->get(route('dokter.export.jadwal-periksa'))->assertOk();
        $this->actingAs($dokter)->get(route('dokter.export.riwayat-pasien'))->assertOk();
    }

    public function test_ganti_password_memvalidasi_dan_memperbarui_password_dengan_hash(): void
    {
        $user = User::factory()->pasien()->create([
            'password' => Hash::make('passwordLama123'),
        ]);

        // Gagal jika password lama salah
        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'salahPassword',
                'password' => 'passwordBaru123',
                'password_confirmation' => 'passwordBaru123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        // Gagal jika password baru sama dengan password lama
        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'passwordLama123',
                'password' => 'passwordLama123',
                'password_confirmation' => 'passwordLama123',
            ])
            ->assertSessionHasErrors('password');

        // Gagal jika kurang dari 8 karakter atau konfirmasi tidak cocok
        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'passwordLama123',
                'password' => 'pendek',
                'password_confirmation' => 'berbeda',
            ])
            ->assertSessionHasErrors('password');

        // Berhasil mengganti password
        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'passwordLama123',
                'password' => 'passwordBaruMinimal8',
                'password_confirmation' => 'passwordBaruMinimal8',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordBaruMinimal8', $user->fresh()->password));
    }
}
