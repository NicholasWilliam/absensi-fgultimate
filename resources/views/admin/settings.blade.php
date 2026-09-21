@extends('layouts.admin')

@section('title', 'Pengaturan Lokasi')

@section('admin-content')
<div class="max-w-md">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-sm font-semibold text-slate-700 mb-1">Titik Lokasi Rental (Geofencing)</p>
        <p class="text-xs text-slate-400 mb-4">
            Karyawan hanya dianggap "di lokasi" kalau posisi GPS mereka berada dalam radius ini saat absen.
            Cara dapat koordinat: buka Google Maps di lokasi rental, klik kanan → koordinat akan tersalin.
        </p>

        @if (session('success'))
            <div class="mb-4 text-sm bg-green-50 text-green-700 border border-green-200 rounded-lg px-3 py-2">Pengaturan disimpan.</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                <input type="text" name="rental_latitude" value="{{ old('rental_latitude', $lat) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                <input type="text" name="rental_longitude" value="{{ old('rental_longitude', $lng) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Radius Toleransi (meter)</label>
                <input type="number" name="rental_radius_meters" value="{{ old('rental_radius_meters', $radius) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="w-full bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-medium">Simpan</button>
        </form>
    </div>
</div>
@endsection
