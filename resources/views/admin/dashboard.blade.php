@extends('layouts.admin')

@section('title', 'Log Absen')

@section('admin-content')

@php
$statusColor = [
    'tepat_waktu' => 'bg-green-100 text-green-700',
    'telat' => 'bg-red-100 text-red-700',
    'lebih_awal' => 'bg-amber-100 text-amber-700',
    'tidak_ada_jadwal' => 'bg-slate-100 text-slate-500',
];
$statusLabel = [
    'tepat_waktu' => 'Tepat waktu',
    'telat' => 'Telat',
    'lebih_awal' => 'Pulang lebih awal',
    'tidak_ada_jadwal' => 'Tanpa jadwal',
];
@endphp

<div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-bold text-slate-800">Log Absen Harian</h1>
    <a href="{{ route('admin.report') }}" class="text-sm text-indigo-600 hover:underline">Lihat Laporan Mingguan/Bulanan →</a>
</div>

@if ($belumAbsen->count())
<div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
    <p class="text-sm font-semibold text-red-700 mb-2">⚠️ Punya jadwal tapi belum check-in ({{ $tanggal }})</p>
    <ul class="text-sm text-red-700 list-disc list-inside">
        @foreach ($belumAbsen as $b)
            <li>{{ $b->user->name }} — jadwal {{ \Illuminate\Support\Carbon::parse($b->start_time)->format('H:i') }}-{{ \Illuminate\Support\Carbon::parse($b->end_time)->format('H:i') }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="GET" class="flex flex-wrap items-end gap-3 mb-5 bg-white p-4 rounded-xl shadow-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
        <input type="date" name="tanggal" value="{{ $tanggal }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Karyawan</label>
        <select name="user_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Semua</option>
            @foreach ($karyawanList as $k)
                <option value="{{ $k->id }}" @selected($userIdFilter == $k->id)>{{ $k->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Foto</th>
                <th class="px-4 py-2">Karyawan</th>
                <th class="px-4 py-2">Tipe</th>
                <th class="px-4 py-2">Jam</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Lokasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
            <tr>
                <td class="px-4 py-2">
                    <a href="{{ asset('storage/absen/'.$log->photo_path) }}" target="_blank">
                        <img src="{{ asset('storage/absen/'.$log->photo_path) }}" class="w-12 h-12 object-cover rounded-lg">
                    </a>
                </td>
                <td class="px-4 py-2 font-medium text-slate-700">{{ $log->user->name }}</td>
                <td class="px-4 py-2">{{ $log->type === 'in' ? 'Masuk' : 'Pulang' }}</td>
                <td class="px-4 py-2">{{ $log->server_time->format('H:i:s') }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColor[$log->status] }}">
                        {{ $statusLabel[$log->status] }}
                    </span>
                </td>
                <td class="px-4 py-2">
                    @if ($log->distance_meters !== null)
                        <span class="{{ $log->within_radius ? 'text-green-600' : 'text-red-600' }}">
                            {{ $log->distance_meters }}m dari lokasi {{ $log->within_radius ? '✓' : '✗ (di luar radius)' }}
                        </span>
                    @else
                        <span class="text-slate-400">Tidak ada data GPS</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Tidak ada data absen untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
