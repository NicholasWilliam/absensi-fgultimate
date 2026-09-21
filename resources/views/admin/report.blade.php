@extends('layouts.admin')

@section('title', 'Laporan')

@section('admin-content')

@php
$statusLabel = [
    'tepat_waktu' => 'Tepat waktu',
    'telat' => 'Telat',
    'lebih_awal' => 'Pulang lebih awal',
    'tidak_ada_jadwal' => 'Tanpa jadwal',
];
$statusColor = [
    'tepat_waktu' => 'bg-green-100 text-green-700',
    'telat' => 'bg-red-100 text-red-700',
    'lebih_awal' => 'bg-amber-100 text-amber-700',
    'tidak_ada_jadwal' => 'bg-slate-100 text-slate-500',
];
@endphp

<h1 class="text-lg font-bold text-slate-800 mb-4">Laporan Absensi</h1>

<form method="GET" class="flex flex-wrap items-end gap-3 mb-3 bg-white p-4 rounded-xl shadow-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Periode Cepat</label>
        <select name="preset" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <option value="minggu_ini" {{ request('preset', 'minggu_ini') === 'minggu_ini' && !request('start') ? 'selected' : '' }}>Minggu Ini</option>
            <option value="minggu_lalu" {{ request('preset') === 'minggu_lalu' ? 'selected' : '' }}>Minggu Lalu</option>
            <option value="bulan_ini" {{ request('preset') === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Atau Rentang Manual — Dari</label>
        <input type="date" name="start" value="{{ request('start') }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Sampai</label>
        <input type="date" name="end" value="{{ request('end') }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
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
    <button class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg">Terapkan</button>
    <a href="{{ route('admin.report.export', request()->query()) }}" class="bg-green-600 text-white text-sm px-4 py-1.5 rounded-lg">⬇ Export CSV</a>
</form>

<p class="text-xs text-slate-400 mb-4">Periode: {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p>

<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Karyawan</th>
                <th class="px-4 py-2">Total Jadwal</th>
                <th class="px-4 py-2">Total Masuk</th>
                <th class="px-4 py-2">Tepat Waktu</th>
                <th class="px-4 py-2">Telat</th>
                <th class="px-4 py-2">Tidak Hadir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($rekap as $r)
            <tr>
                <td class="px-4 py-2 font-medium text-slate-700">{{ $r->user->name }}</td>
                <td class="px-4 py-2">{{ $r->total_shift }}</td>
                <td class="px-4 py-2">{{ $r->total_masuk }}</td>
                <td class="px-4 py-2 text-green-600">{{ $r->total_tepat_waktu }}</td>
                <td class="px-4 py-2 text-red-600">{{ $r->total_telat }}</td>
                <td class="px-4 py-2 {{ $r->total_tidak_hadir > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">{{ $r->total_tidak_hadir }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Tidak ada karyawan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-sm font-semibold text-slate-700 mb-2">Log Lengkap Periode Ini</p>
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Karyawan</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Jam</th>
                <th class="px-4 py-2">Tipe</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Lokasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
            <tr>
                <td class="px-4 py-2 font-medium text-slate-700">{{ $log->user->name }}</td>
                <td class="px-4 py-2">{{ $log->server_time->format('d M Y') }}</td>
                <td class="px-4 py-2">{{ $log->server_time->format('H:i:s') }}</td>
                <td class="px-4 py-2">{{ $log->type === 'in' ? 'Masuk' : 'Pulang' }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColor[$log->status] }}">{{ $statusLabel[$log->status] }}</span>
                </td>
                <td class="px-4 py-2">
                    @if ($log->distance_meters !== null)
                        <span class="{{ $log->within_radius ? 'text-green-600' : 'text-red-600' }}">{{ $log->distance_meters }}m {{ $log->within_radius ? '✓' : '✗' }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Tidak ada log di periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $logs->links() }}
@endsection
