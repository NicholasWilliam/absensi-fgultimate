<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $minggu = $request->input('minggu', now()->toDateString());
        $mulaiMinggu = Carbon::parse($minggu)->startOfWeek(Carbon::MONDAY);

        $tanggalMinggu = collect(range(0, 6))->map(fn ($i) => $mulaiMinggu->copy()->addDays($i));

        $karyawanList = User::where('role', 'karyawan')->orderBy('name')->get();

        $shiftMinggu = Shift::with('user')
            ->whereBetween('shift_date', [$tanggalMinggu->first()->toDateString(), $tanggalMinggu->last()->toDateString()])
            ->orderBy('shift_date')->orderBy('start_time')
            ->get()
            ->groupBy(fn ($s) => $s->shift_date->toDateString());

        return view('admin.schedule', compact('tanggalMinggu', 'karyawanList', 'shiftMinggu', 'minggu', 'mulaiMinggu'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            Shift::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            throw ValidationException::withMessages([
                'shift_date' => 'Karyawan ini sudah punya jadwal di tanggal tersebut.',
            ]);
        }

        return back();
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return back();
    }
}
