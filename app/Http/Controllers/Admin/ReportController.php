<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end] = $this->resolveRange($request);

        $userIdFilter = $request->input('user_id');
        $karyawanList = User::where('role', 'karyawan')->orderBy('name')->get();

        // Log lengkap untuk rentang tanggal ini
        $logs = Attendance::with('user')
            ->whereBetween('server_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($userIdFilter, fn ($q) => $q->where('user_id', $userIdFilter))
            ->orderByDesc('server_time')
            ->paginate(50)
            ->withQueryString();

        // Rekap per karyawan dalam rentang tanggal ini
        $rekap = $karyawanList
            ->when($userIdFilter, fn ($list) => $list->where('id', $userIdFilter))
            ->map(function (User $k) use ($start, $end) {
                $attendancesIn = Attendance::where('user_id', $k->id)
                    ->where('type', 'in')
                    ->whereBetween('server_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->get();

                $totalShift = Shift::where('user_id', $k->id)
                    ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
                    ->count();

                return (object) [
                    'user' => $k,
                    'total_shift' => $totalShift,
                    'total_masuk' => $attendancesIn->count(),
                    'total_telat' => $attendancesIn->where('status', 'telat')->count(),
                    'total_tepat_waktu' => $attendancesIn->where('status', 'tepat_waktu')->count(),
                    'total_tidak_hadir' => max(0, $totalShift - $attendancesIn->count()),
                ];
            });

        return view('admin.report', compact('logs', 'rekap', 'karyawanList', 'userIdFilter', 'start', 'end'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolveRange($request);
        $userIdFilter = $request->input('user_id');

        $logs = Attendance::with('user')
            ->whereBetween('server_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($userIdFilter, fn ($q) => $q->where('user_id', $userIdFilter))
            ->orderBy('server_time')
            ->get();

        $filename = 'laporan_absen_'.$start->format('Ymd').'_'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'Tanggal', 'Jam', 'Tipe', 'Status', 'Jarak (meter)', 'Dalam Radius']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->user->name,
                    $log->server_time->format('Y-m-d'),
                    $log->server_time->format('H:i:s'),
                    $log->type === 'in' ? 'Masuk' : 'Pulang',
                    $log->status,
                    $log->distance_meters,
                    $log->within_radius ? 'Ya' : 'Tidak',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function resolveRange(Request $request): array
    {
        $preset = $request->input('preset', 'minggu_ini');

        if ($request->filled('start') && $request->filled('end')) {
            return [Carbon::parse($request->input('start')), Carbon::parse($request->input('end'))];
        }

        return match ($preset) {
            'bulan_ini' => [now()->startOfMonth(), now()->endOfMonth()],
            'minggu_lalu' => [now()->subWeek()->startOfWeek(Carbon::MONDAY), now()->subWeek()->endOfWeek(Carbon::SUNDAY)],
            default => [now()->startOfWeek(Carbon::MONDAY), now()->endOfWeek(Carbon::SUNDAY)],
        };
    }
}
