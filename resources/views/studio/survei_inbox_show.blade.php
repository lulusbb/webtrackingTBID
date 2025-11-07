{{-- resources/views/studio/survei_inbox_show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Permintaan Survei')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail Permintaan Survei</h3>
  </div>

  @php
    // ✅ Helper lokal (bukan function global)
    $rupiah  = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';
    $fileUrl = function ($path) {
      if (!$path) return null;
      if (\Illuminate\Support\Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };

    $badge = [
      'pending'  => 'warning text-dark',
      'accepted' => 'success',
      'rejected' => 'danger',
    ][$req->status] ?? 'secondary';

    $tglMasuk = $klien->tanggal_masuk ?: $klien->created_at;
  @endphp

  <div class="card">
    <div class="card-body">
      <div class="row g-4">

        {{-- KOLOM KIRI: Data Klien --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small><div class="fw-semibold">{{ $klien->nama ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Email</small><div class="fw-semibold">{{ $klien->email ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">No HP</small><div class="fw-semibold">{{ $klien->no_hp ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small><div class="fw-semibold">{{ $klien->alamat_tinggal ?? '-' }}</div></div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small><div class="fw-semibold">{{ $klien->kode_proyek ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kelas</small><div class="fw-semibold">{{ $klien->kelas ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small><div class="fw-semibold">{{ $klien->lokasi_lahan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Share Lokasi</small><div class="fw-semibold">{{ $klien->share_lokasi ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small><div class="fw-semibold">{{ $klien->luas_lahan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small><div class="fw-semibold">{{ $klien->luas_bangunan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small><div class="fw-semibold">{{ $klien->kebutuhan_ruang ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small><div class="fw-semibold">{{ $klien->konsep_bangunan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small><div class="fw-semibold">{{ $klien->arah_mata_angin ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small><div class="fw-semibold">{{ $klien->batas_keliling ?? '-' }}</div></div>

          <hr>

          <h6 class="mb-2">Preferensi & Aktivitas</h6>
          <div class="mb-2"><small class="text-muted">Hoby</small><div class="fw-semibold">{{ $klien->hoby ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Aktivitas</small><div class="fw-semibold">{{ $klien->aktivitas ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Prioritas Ruang</small><div class="fw-semibold">{{ $klien->prioritas_ruang ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kendaraan</small><div class="fw-semibold">{{ $klien->kendaraan ?? '-' }}</div></div>
        </div>

        {{-- KOLOM KANAN: Rincian & Request --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small><div class="fw-semibold">{{ $rupiah($klien->budget) }}</div></div>
          <div class="mb-2"><small class="text-muted">Biaya Survei</small><div class="fw-semibold">{{ $rupiah($klien->biaya_survei) }}</div></div>
          <div class="mb-2"><small class="text-muted">Estimasi Start Pembangunan</small><div class="fw-semibold">{{ $klien->estimasi_start ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">{{ optional($tglMasuk)->format('Y-m-d') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Keterangan</small><div class="fw-semibold">{{ $klien->keterangan ?? '-' }}</div></div>

          {{-- ====== BERKAS (di atas Informasi Request) ====== --}}
          <hr>

          <h5 class="mb-3">Berkas</h5>

          @php $u = $fileUrl($klien->sertifikat); @endphp
          <div class="mb-2">
            <small class="text-muted">Sertifikat</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->lembar_diskusi); @endphp
          <div class="mb-2">
            <small class="text-muted">Lembar Diskusi</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->foto_eksisting); @endphp
          <div class="mb-2">
            <small class="text-muted">Foto Eksisting</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->referensi); @endphp
          <div class="mb-2">
            <small class="text-muted">Referensi</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->layout); @endphp
          <div class="mb-2">
            <small class="text-muted">Layout</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->desain_3d); @endphp
          <div class="mb-2">
            <small class="text-muted">Desain 3D</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->rab_boq); @endphp
          <div class="mb-2">
            <small class="text-muted">RAB / BOQ</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>

          @php $u = $fileUrl($klien->gambar_kerja); @endphp
          <div class="mb-2">
            <small class="text-muted">Gambar Kerja</small>
            <div class="fw-semibold">
              @if($u)
                <a href="{{ $u }}" target="_blank" class="btn btn-sm btn-info text-dark">Lihat file</a>
              @else
                -
              @endif
            </div>
          </div>
          {{-- ====== /BERKAS ====== --}}
          
          <hr>

          <h5 class="mb-3">Informasi Request</h5>
          <div class="mb-2"><small class="text-muted">Status Request</small>
            <div><span class="badge bg-{{ $badge }}">{{ ucfirst($req->status) }}</span></div>
          </div>
          <div class="mb-2"><small class="text-muted">Dikirim Oleh (ID)</small><div class="fw-semibold">{{ $req->sent_by ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Waktu Kirim</small><div class="fw-semibold">{{ optional($req->sent_at)->format('Y-m-d H:i') ?? '-' }}</div></div>
          @if($req->status === 'accepted')
            <div class="mb-2"><small class="text-muted">Disetujui Oleh (ID)</small><div class="fw-semibold">{{ $req->approved_by ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Waktu Setuju</small><div class="fw-semibold">{{ optional($req->approved_at)->format('Y-m-d H:i') ?? '-' }}</div></div>
          @endif
          @if($req->status === 'rejected')
            <div class="mb-2"><small class="text-muted">Ditolak Oleh (ID)</small><div class="fw-semibold">{{ $req->rejected_by ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Waktu Tolak</small><div class="fw-semibold">{{ optional($req->rejected_at)->format('Y-m-d H:i') ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Alasan</small><div class="fw-semibold">{{ $req->reject_reason ?? '-' }}</div></div>
          @endif
        </div>
      </div>

      <hr class="my-4">

      {{-- Tombol aksi --}}
      @if($req->status === 'pending')
        <div class="d-flex gap-2">
          <button id="btn-approve" class="btn btn-primary">
            <i class="bi bi-check2-circle me-1"></i> Setujui & Jadwalkan Survei
          </button>

        </div>
      @else
        <div class="alert alert-info mb-0">
          Request sudah diproses: <strong>{{ ucfirst($req->status) }}</strong>
        </div>
      @endif

    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  function postFormEncoded(url, dataObj) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
      },
      body: new URLSearchParams(dataObj),
      credentials: 'same-origin'
    });
  }

  // Helper default tanggal & jam (dibulatkan ke 15 menit terdekat)
  function getDefaultDateTime() {
    const d = new Date();
    let m = d.getMinutes();
    const r = Math.ceil(m/15)*15;
    if (r === 60) { d.setHours(d.getHours()+1); m = 0; } else { m = r; }
    return {
      date: d.toISOString().slice(0,10),
      time: String(d.getHours()).padStart(2,'0') + ':' + String(m).padStart(2,'0')
    };
  }

  // === Setujui → jadwalkan ===
  document.getElementById('btn-approve')?.addEventListener('click', async () => {
    const def = getDefaultDateTime();
    const { value, isConfirmed } = await Swal.fire({
      title: 'Penjadwalan Survei',
      html: `
        <div class="text-start" style="padding:2px 2px 0">
          <label class="form-label" style="font-size:.9rem;">Tanggal</label>
          <input type="date" id="tgl-survei" class="swal2-input" style="width:100%;margin:0 0 10px 0;">
          <label class="form-label" style="font-size:.9rem;">Jam</label>
          <input type="time" id="jam-survei" class="swal2-input" step="900" style="width:100%;margin:0;">
          <small class="text-muted d-block mt-2">*Jam lokal (WIB)</small>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Ya, jadwalkan',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      width: 520,
      heightAuto: false,
      didOpen: (popup) => {
        const html = popup.querySelector('.swal2-html-container');
        if (html) { html.style.maxHeight = '60vh'; html.style.overflowY = 'auto'; }
        const tgl = popup.querySelector('#tgl-survei');
        const jam = popup.querySelector('#jam-survei');
        if (tgl && !tgl.value) tgl.value = def.date;
        if (jam && !jam.value) jam.value = def.time;
        tgl?.focus();
      },
      preConfirm: () => {
        const tgl = document.getElementById('tgl-survei')?.value;
        const jam = document.getElementById('jam-survei')?.value;
        if (!tgl || !jam) { Swal.showValidationMessage('Tanggal dan jam harus diisi'); return false; }
        return { tanggal: tgl, jam };
      }
    });
    if (!isConfirmed) return;

    const url = "{{ route('studio.survei_inbox.schedule', $req->id) }}";
    const resp = await postFormEncoded(url, { tanggal: value.tanggal, jam: value.jam });
    if (!resp.ok) {
      const t = await resp.text().catch(()=> '');
      return Swal.fire({icon:'error', title:'Gagal menjadwalkan', text: t || 'Silakan coba lagi.'});
    }
    await Swal.fire({icon:'success', title:'Berhasil dijadwalkan', timer:1400, showConfirmButton:false});
    window.location.href = "{{ route('studio.survei_inbox.index') }}";
  });

  // === Tolak ===
  document.getElementById('btn-reject')?.addEventListener('click', async () => {
    const {isConfirmed, value} = await Swal.fire({
      icon: 'warning',
      title: 'Tolak permintaan survei?',
      input: 'text',
      inputLabel: 'Alasan (opsional)',
      inputPlaceholder: 'Tulis alasan penolakan…',
      showCancelButton: true,
      confirmButtonText: 'Tolak',
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
    if (!isConfirmed) return;

    const url = "{{ route('studio.survei_inbox.reject', $req->id) }}";
    const resp = await postFormEncoded(url, { reason: value || '' });
    if (!resp.ok) {
      const t = await resp.text().catch(()=> '');
      return Swal.fire({icon:'error', title:'Gagal menolak', text: t || 'Silakan coba lagi.'});
    }
    await Swal.fire({icon:'success', title:'Ditolak', timer:1200, showConfirmButton:false});
    window.location.href = "{{ route('studio.survei_inbox.index') }}";
  });

})();
</script>
@endpush
