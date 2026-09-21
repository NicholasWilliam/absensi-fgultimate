@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-md p-8">
        <h1 class="text-xl font-bold text-slate-800 mb-1">{{ config('app.name') }}</h1>
        <p class="text-sm text-slate-500 mb-6">Login untuk melanjutkan</p>

        @if ($errors->any())
            <div class="mb-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition">
                Masuk
            </button>
        </form>

        <p class="text-xs text-slate-400 mt-6">
            Contoh akun — Admin: <code>admin / admin123</code> · Karyawan: <code>karyawan1 / karyawan123</code>
        </p>
    </div>
</div>
@endsection
