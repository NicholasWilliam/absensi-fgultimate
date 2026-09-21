<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $userIdFilter = $request->input('user_id');

        $logs = Attendance::with('user')
            ->whereDate('server_time', $tanggal)
            ->when($userIdFilter, fn ($q) => $q->where('user_id', $userIdFilter))
            ->orderByDesc('server_time')
            ->get();

        $karyawanList = User::where('role', 'karyawan')->orderBy('name')->get();

        // Karyawan yang punya jadwal hari itu tapi belum check-in
        $belumAbsen = Shift::with('user')
            ->where('shift_date', $tanggal)
            ->whereDoesntHave('attendances', function ($q) use ($tanggal) {
                $q->where('type', 'in')->whereDate('server_time', $tanggal);
            })
            ->get();

        return view('admin.dashboard', compact('logs', 'karyawanList', 'belumAbsen', 'tanggal', 'userIdFilter'));
    }
}
