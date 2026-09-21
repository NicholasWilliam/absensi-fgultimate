@extends('layouts.app')

@section('title', 'Absen')

@section('content')
<div class="max-w-md mx-auto px-4 py-6" x-data="checkinPage()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500">Halo,</p>
            <h1 class="text-lg font-bold text-slate-800">{{ $user->name }}</h1>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-red-600 hover:underline">Keluar</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Jadwal Hari Ini</p>
        @if ($shiftHariIni)
            <p class="text-slate-800 font-medium">
                {{ \Illuminate\Support\Carbon::parse($shiftHariIni->start_time)->format('H:i') }} — {{ \Illuminate\Support\Carbon::parse($shiftHariIni->end_time)->format('H:i') }}
            </p>
            @if ($shiftHariIni->note)
                <p class="text-sm text-slate-500 mt-1">{{ $shiftHariIni->note }}</p>
            @endif
        @else
            <p class="text-slate-400 italic">Tidak ada jadwal shift hari ini</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Status Hari Ini</p>
        <div class="flex gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $sudahCheckin ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                Check-in {{ $sudahCheckin ? '✓' : '—' }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $sudahCheckout ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                Check-out {{ $sudahCheckout ? '✓' : '—' }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-2">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($sudahCheckin && $sudahCheckout)
        <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
            <p class="text-slate-600">Kamu sudah absen masuk dan pulang hari ini. Sampai jumpa besok! 👋</p>
        </div>
    @else

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-medium text-slate-700 mb-3">
                {{ $sudahCheckin ? 'Absen Pulang (Check-out)' : 'Absen Masuk (Check-in)' }}
            </p>

            <div class="relative rounded-xl overflow-hidden bg-slate-900 aspect-[3/4]" x-show="!photoDataUrl">
                <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
            </div>
            <img :src="photoDataUrl" x-show="photoDataUrl" x-cloak class="w-full rounded-xl aspect-[3/4] object-cover">
            <canvas x-ref="canvas" class="hidden"></canvas>

            <p class="text-xs text-slate-400 mt-2" x-text="statusText"></p>

            <div class="flex gap-2 mt-4">
                <button type="button" x-show="!photoDataUrl" @click="startCamera()"
                    class="flex-1 bg-slate-800 text-white py-2.5 rounded-lg text-sm font-medium">
                    Aktifkan Kamera
                </button>
                <button type="button" x-show="cameraReady && !photoDataUrl" @click="ambilFoto()"
                    class="flex-1 bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-medium">
                    📸 Ambil Foto
                </button>
                <button type="button" x-show="photoDataUrl" @click="ulangiFoto()"
                    class="flex-1 bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium">
                    Ulangi
                </button>
            </div>

            <form method="POST" action="{{ route('checkin.store') }}" class="mt-4" @submit="onSubmit">
                @csrf
                <input type="hidden" name="type" value="{{ $sudahCheckin ? 'out' : 'in' }}">
                <input type="hidden" name="photo_data" x-ref="photoInput">
                <input type="hidden" name="latitude" x-ref="latInput">
                <input type="hidden" name="longitude" x-ref="lngInput">

                <button type="submit" :disabled="!photoDataUrl || !locationReady" :class="(!photoDataUrl || !locationReady) ? 'opacity-40 cursor-not-allowed' : ''"
                    class="w-full mt-2 bg-green-600 text-white py-3 rounded-lg text-sm font-semibold">
                    Kirim Absen
                </button>
            </form>
        </div>

    @endif

    @if ($absenHariIni->count())
    <div class="bg-white rounded-2xl shadow-sm p-5 mt-4">
        <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Riwayat Hari Ini</p>
        <ul class="space-y-1 text-sm">
            @foreach ($absenHariIni as $a)
                <li class="flex justify-between text-slate-600">
                    <span>{{ $a->type === 'in' ? 'Masuk' : 'Pulang' }}</span>
                    <span>{{ $a->server_time->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<script>
function checkinPage() {
    return {
        cameraReady: false,
        locationReady: false,
        photoDataUrl: null,
        statusText: 'Klik "Aktifkan Kamera" lalu ambil foto buat absen.',
        stream: null,

        startCamera() {
            this.statusText = 'Meminta izin kamera...';
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                .then(stream => {
                    this.stream = stream;
                    this.$refs.video.srcObject = stream;
                    this.cameraReady = true;
                    this.statusText = 'Kamera aktif. Posisikan wajah, lalu ambil foto.';
                    this.mintaLokasi();
                })
                .catch(() => {
                    this.statusText = 'Gagal mengakses kamera: izin ditolak atau tidak didukung browser ini.';
                });
        },

        mintaLokasi() {
            if (!navigator.geolocation) {
                this.statusText = 'Browser tidak mendukung GPS.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    this.$refs.latInput.value = pos.coords.latitude;
                    this.$refs.lngInput.value = pos.coords.longitude;
                    this.locationReady = true;
                },
                () => {
                    this.statusText = 'Gagal mengambil lokasi GPS. Aktifkan izin lokasi lalu coba lagi.';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        ambilFoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const now = new Date();
            const label = now.toLocaleString('id-ID');
            ctx.font = Math.max(14, canvas.width * 0.03) + 'px sans-serif';
            ctx.fillStyle = 'rgba(0,0,0,0.5)';
            ctx.fillRect(0, canvas.height - 34, canvas.width, 34);
            ctx.fillStyle = '#fff';
            ctx.fillText(label, 10, canvas.height - 10);

            this.photoDataUrl = canvas.toDataURL('image/jpeg', 0.85);
            this.$refs.photoInput.value = this.photoDataUrl;

            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
            }
        },

        ulangiFoto() {
            this.photoDataUrl = null;
            this.$refs.photoInput.value = '';
            this.startCamera();
        },

        onSubmit(e) {
            if (!this.photoDataUrl || !this.locationReady) {
                e.preventDefault();
                this.statusText = 'Foto dan lokasi wajib ada sebelum absen dikirim.';
            }
        }
    }
}
</script>
@endsection
