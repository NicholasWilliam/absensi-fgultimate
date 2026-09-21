@extends('layouts.app')

@section('content')
<div class="bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <span class="font-bold text-slate-800">Admin — {{ config('app.name') }}</span>
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('admin.dashboard') ? 'font-semibold text-indigo-600' : '' }}">Log Absen</a>
                <a href="{{ route('admin.report') }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('admin.report') ? 'font-semibold text-indigo-600' : '' }}">Laporan</a>
                <a href="{{ route('admin.schedule') }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('admin.schedule') ? 'font-semibold text-indigo-600' : '' }}">Jadwal Shift</a>
                <a href="{{ route('admin.employees') }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('admin.employees') ? 'font-semibold text-indigo-600' : '' }}">Karyawan</a>
                <a href="{{ route('admin.settings') }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('admin.settings') ? 'font-semibold text-indigo-600' : '' }}">Pengaturan Lokasi</a>
            </nav>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-red-600 hover:underline">Keluar</button>
        </form>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-6">
    @yield('admin-content')
</div>
@endsection
