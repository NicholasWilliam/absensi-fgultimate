@extends('layouts.admin')

@section('title', 'Jadwal Shift')

@section('admin-content')

<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <p class="text-sm font-semibold text-slate-700 mb-3">Tambah Jadwal Shift</p>
    @if ($errors->any())
        <div class="mb-3 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.schedule.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">Karyawan</label>
            <select name="user_id" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                @foreach ($karyawanList as $k)
                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
            <input type="date" name="shift_date" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Jam Mulai</label>
            <input type="time" name="start_time" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Jam Selesai</label>
            <input type="time" name="end_time" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs text-slate-500 mb-1">Catatan (opsional)</label>
            <input type="text" name="note" placeholder="mis. Shift pengganti" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <button class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg h-fit">Simpan</button>
    </form>
</div>

<div class="flex items-center justify-between mb-3">
    <p class="text-sm font-semibold text-slate-700">
        Minggu: {{ $tanggalMinggu->first()->format('d M') }} – {{ $tanggalMinggu->last()->format('d M Y') }}
    </p>
    <div class="flex gap-2 text-sm">
        <a href="?minggu={{ $mulaiMinggu->copy()->subDays(7)->toDateString() }}" class="px-3 py-1 bg-white rounded-lg shadow-sm">← Minggu Lalu</a>
        <a href="?minggu={{ $mulaiMinggu->copy()->addDays(7)->toDateString() }}" class="px-3 py-1 bg-white rounded-lg shadow-sm">Minggu Depan →</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-7 gap-3">
    @foreach ($tanggalMinggu as $tgl)
        @php $key = $tgl->toDateString(); @endphp
        <div class="bg-white rounded-xl shadow-sm p-3 min-h-[140px]">
            <p class="text-xs font-semibold text-slate-600 mb-2">{{ $tgl->translatedFormat('D, d M') }}</p>
            @if (!empty($shiftMinggu[$key]))
                <div class="space-y-2">
                    @foreach ($shiftMinggu[$key] as $s)
                        <div class="bg-indigo-50 rounded-lg px-2 py-1.5 text-xs relative group">
                            <p class="font-medium text-indigo-800">{{ $s->user->name }}</p>
                            <p class="text-indigo-600">{{ \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') }}-{{ \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i') }}</p>
                            <form method="POST" action="{{ route('admin.schedule.destroy', $s) }}" onsubmit="return confirm('Hapus jadwal ini?')" class="absolute top-1 right-1">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100">✕</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-300 italic">Kosong</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
