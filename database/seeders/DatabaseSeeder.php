<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Admin Rental',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Contoh Karyawan',
            'username' => 'karyawan1',
            'password' => Hash::make('karyawan123'),
            'role' => 'karyawan',
        ]);

        // Ganti koordinat ini lewat menu Admin > Pengaturan Lokasi setelah login
        Setting::create(['setting_key' => 'rental_latitude', 'setting_value' => '-6.200000']);
        Setting::create(['setting_key' => 'rental_longitude', 'setting_value' => '106.816666']);
        Setting::create(['setting_key' => 'rental_radius_meters', 'setting_value' => '100']);
    }
}
