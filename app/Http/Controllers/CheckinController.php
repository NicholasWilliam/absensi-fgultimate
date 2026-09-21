<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CheckinController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $shiftHariIni = Shift::where('user_id', $user->id)->where('shift_date', $today)->first();

        $absenHariIni = Attendance::where('user_id', $user->id)
            ->whereDate('server_time', $today)
            ->orderBy('server_time')
            ->get();

        $sudahCheckin = $absenHariIni->contains('type', 'in');
        $sudahCheckout = $absenHariIni->contains('type', 'out');

        return view('checkin', compact('user', 'shiftHariIni', 'absenHariIni', 'sudahCheckin', 'sudahCheckout'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type' => ['required', 'in:in,out'],
            'photo_data' => ['required', 'string', 'starts_with:data:image'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $type = $validated['type'];
        $today = now()->toDateString();

        // Cegah double submit
        $sudahAda = Attendance::where('user_id', $user->id)
            ->where('type', $type)
            ->whereDate('server_time', $today)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['photo_data' => 'Kamu sudah absen '.($type === 'in' ? 'masuk' : 'pulang').' hari ini.']);
        }

        // Simpan foto (base64 dari kamera) ke storage/app/public/absen
        $base64 = explode(',', $validated['photo_data'])[1] ?? '';
        $imageBinary = base64_decode($base64);

        if ($imageBinary === false) {
            return back()->withErrors(['photo_data' => 'Gagal memproses foto.']);
        }

        $filename = "absen_{$user->id}_{$type}_".now()->format('Ymd_His').'_'.substr(md5(uniqid()), 0, 6).'.jpg';
        Storage::disk('public')->put("absen/{$filename}", $imageBinary);

        // Waktu diambil dari SERVER, bukan dari device karyawan
        $serverTime = now();

        // Geofencing
        $rentalLat = (float) Setting::get('rental_latitude', 0);
        $rentalLng = (float) Setting::get('rental_longitude', 0);
        $radius = (int) Setting::get('rental_radius_meters', 100);

        $distance = null;
        $withinRadius = false;

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $distance = round($this->hitungJarakMeter(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $rentalLat,
                $rentalLng
            ));
            $withinRadius = $distance <= $radius;
        }

        // Cocokkan dengan jadwal shift hari ini
        $shift = Shift::where('user_id', $user->id)->where('shift_date', $today)->first();
        $status = 'tidak_ada_jadwal';

        if ($shift) {
            $toleransiMenit = 10;

            if ($type === 'in') {
                $batasTelat = Carbon::parse($today.' '.$shift->start_time)->addMinutes($toleransiMenit);
                $status = $serverTime->greaterThan($batasTelat) ? 'telat' : 'tepat_waktu';
            } else {
                $jamPulang = Carbon::parse($today.' '.$shift->end_time);
                $status = $serverTime->lessThan($jamPulang) ? 'lebih_awal' : 'tepat_waktu';
            }
        }

        Attendance::create([
            'user_id' => $user->id,
            'shift_id' => $shift?->id,
            'type' => $type,
            'photo_path' => $filename,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'distance_meters' => $distance,
            'within_radius' => $withinRadius,
            'status' => $status,
            'server_time' => $serverTime,
        ]);

        return redirect()->route('checkin');
    }

    /** Formula Haversine, hasil dalam meter */
    private function hitungJarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
