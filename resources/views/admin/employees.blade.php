@extends('layouts.admin')

@section('title', 'Karyawan')

@section('admin-content')

<div class="max-w-3xl">
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <p class="text-sm font-semibold text-slate-700 mb-3">Tambah Karyawan</p>
    @if ($errors->any())
        <div class="mb-3 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.employees.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Password</label>
            <input type="password" name="password" required minlength="6" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <button class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg h-fit">Tambah</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Nama</th>
                <th class="px-4 py-2">Username</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($karyawan as $k)
            <tr>
                <td class="px-4 py-2 font-medium text-slate-700">{{ $k->name }}</td>
                <td class="px-4 py-2 text-slate-500">{{ $k->username }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $k->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $k->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-2 text-right">
                    <form method="POST" action="{{ route('admin.employees.toggle', $k) }}" onsubmit="return confirm('Ubah status aktif karyawan ini?')">
                        @csrf @method('PATCH')
                        <button class="text-indigo-600 text-xs hover:underline">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection
