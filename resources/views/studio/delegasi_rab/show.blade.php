@extends('layouts.app')

@section('title','Detail Delegasi RAB')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none" aria-label="Kembali">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">DELEGASI RAB</h3>
  </div>

  @php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    /** @var \App\Models\DelegasiRab|null $delegasiRab */
    $delegasiRab = $delegasiRab ?? request()->route('delegasiRab');
    $rab = $delegasiRab;

    // Helpers
    $pick = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };
    $rupiah = fn($n) => is_numeric($n) ? 'Rp. '.number_format($n,0,',','.') : ($n ?: '-');
    $fileUrl = function (?string $path) {
      if (!$path) return null;
      if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };

    // Format tanggal ke Y-m-d (WIB). Jika null/invalid -> '-'
    $fmtDate = function ($val) {
      if (blank($val)) return '-';
      try {
        return Carbon::parse($val)->timezone('Asia/Jakarta')->format('Y-m-d');
      } catch (\Throwable $e) {
        return is_string($val) ? substr($val, 0, 10) : '-';
      }
    };

    // tanggal masuk: pakai tanggal_masuk, fallback ke created_at
    $tglMasuk = $fmtDate($pick(optional($rab)->tanggal_masuk, optional($rab)->created_at));

    // (opsional) relasi klien
    $k = optional($rab)->klien;
  @endphp

  @if(!$rab)
    <div class="alert alert-warning">Data Delegasi RAB tidak ditemukan.</div>
  @else
  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">

        {{-- ==================== KIRI ==================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2">
            <small class="text-muted d-block">Nama</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->nama, optional($k)->nama, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Email</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->email, optional($k)->email, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">No HP</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->no_hp, optional($k)->no_hp, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr class="my-3">

          <h6 class="mb-2">Data Proyek</h6>

          <div class="mb-2">
            <small class="text-muted d-block">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Kelas</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->kelas, optional($k)->kelas, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Lokasi Lahan</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Share Lokasi</small>
            <div class="fw-semibold">
              @if(optional($rab)->share_lokasi)
                <a href="{{ optional($rab)->share_lokasi }}" target="_blank" class="text-primary text-decoration-underline">Buka</a>
              @else
                -
              @endif
            </div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Sertifikat</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->sertifikat, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Status</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->status_mep, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Hobi</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->hoby, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Aktivitas</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->aktivitas, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Prioritas Ruang</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->prioritas_ruang, '-') }}</div>
          </div>
        </div>

        {{-- ==================== KANAN ==================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2">
            <small class="text-muted d-block">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick(optional($rab)->budget, optional($k)->budget)) }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Biaya Survei</small>
            <div class="fw-semibold">{{ $rupiah(optional($rab)->biaya_survei) }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Estimasi Mulai</small>
            <div class="fw-semibold">{{ $fmtDate(optional($rab)->estimasi_start) }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Tanggal Masuk</small>
            <div class="fw-semibold">{{ $tglMasuk }}</div>
          </div>



          <div class="mb-2">
            <small class="text-muted d-block">Kendaraan</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->kendaraan, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Target User Kos</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->target_user_kos, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted d-block">Fasilitas Kos</small>
            <div class="fw-semibold">{{ $pick(optional($rab)->fasilitas_kos, '-') }}</div>
          </div>

          <hr class="my-3">

          <h6 class="mb-2">Berkas</h6>

          @php $u = $fileUrl($pick(optional($rab)->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Lembar Diskusi</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick($rab->sertifikat, optional($k)->sertifikat)); @endphp
          <div class="mb-1">
            <small class="text-muted">Sertifikat</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->layout, optional($k)->layout)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Layout</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick($rab->foto_eksisting, optional($k)->foto_eksisting)); @endphp
          <div class="mb-1">
            <small class="text-muted">Foto Eksisting</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->desain_3d, optional($k)->desain_3d)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Desain 3D</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->rab_boq, optional($k)->rab_boq)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">RAB / BOQ</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->gambar_kerja, optional($k)->gambar_kerja)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Gambar Kerja</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->referensi, optional($k)->referensi)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Referensi</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-secondary">Lihat</a>@else-@endif</div>
          </div>

          @php $u = $fileUrl($pick(optional($rab)->lembar_survei, optional($k)->lembar_survei)); @endphp
          <div class="mb-1">
            <small class="text-muted d-block">Lembar Survei (PDF)</small>
            <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
          </div>

          <hr class="my-3">

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick(optional($rab)->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

      {{-- ====== FOOTER ACTIONS ====== --}}
      <hr class="my-4">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="button" id="btn-ceklist" class="btn btn-outline-secondary">
          <i class="bi bi-list-check me-1"></i> Ceklist Data
          <span id="badge-done" class="badge bg-secondary ms-2 d-none"></span>
        </button>

        {{-- Lanjut Delegasi --}}
        <form id="form-lanjut"
              action="{{ route('studio.delegasirab.lanjut', ['delegasiRab' => $delegasiRab->id]) }}"
              method="POST" class="d-inline">
          @csrf
          <button type="button" id="btn-lanjut" class="btn btn-success" disabled>
            <i class="bi bi-send-check me-1"></i> Lanjut Delegasi
          </button>
        </form>

      </div>
    </div>
  </div>
  @endif
</section>

{{-- ===== Modal Ceklist ===== --}}
<div class="modal fade" id="ceklistModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ceklist Kelengkapan Delegasi RAB</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="small text-muted">Centang semua poin di bawah ini sebelum menyimpan.</div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="check-all">
            <label class="form-check-label" for="check-all">Centang semua</label>
          </div>
        </div>

        <div id="ceklist-grid" class="row g-3"></div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button type="button" id="btn-save-checklist" class="btn btn-primary" disabled>
          <i class="bi bi-save2 me-1"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
  'use strict';

  // Checklist config (tanpa huruf A/B/C...)
  const CHECK_ITEMS = [
    { id:'foto_survey',      label:'FOTO SURVEY' },
    { id:'file_survey',      label:'FILE SURVEY' },
    { id:'file_diskusi',     label:'FILE DISKUSI' },
    { id:'sketchup_exist',   label:'3D SKETCHUP EKSISTING' },
    { id:'sketchup_baru',    label:'3D SKETCHUP BARU' },
    { id:'skema_elektrikal', label:'SKEMA ELEKTRIKAL' },
    { id:'skema_plumbing',   label:'SKEMA PLUMBING' },
    { id:'spesifikasi',      label:'SPESIFIKASI MATERIAL' },
    { id:'story_telling',    label:'STORY TELLING' },
  ];
  const TOTAL = CHECK_ITEMS.length;

  const el = {
    modal:      document.getElementById('ceklistModal'),
    grid:       document.getElementById('ceklist-grid'),
    note:       document.getElementById('ceklist-note'),
    btnOpen:    document.getElementById('btn-ceklist'),
    btnSave:    document.getElementById('btn-save-checklist'),
    btnLanjut:  document.getElementById('btn-lanjut'),
    formLanjut: document.getElementById('form-lanjut'),
    badge:      document.getElementById('badge-done'),
    checkAll:   document.getElementById('check-all'),
  };
  const bsModal = el.modal ? new bootstrap.Modal(el.modal) : null;

  const RECORD_ID = @json(optional($delegasiRab)->id ?? optional($rab)->id ?? 0);
  const LS_KEY    = `rab_checklist_${RECORD_ID}`;

  const loadChecklist = () => {
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '{}'); }
    catch { return {}; }
  };
  const saveChecklist = (payload) => localStorage.setItem(LS_KEY, JSON.stringify(payload));
  const getOkCount   = () => {
    const saved = loadChecklist();
    return Object.values(saved.items || {}).filter(Boolean).length;
  };

  const setSaveEnabled = () => {
    if (!el.btnSave || !el.grid) return;
    const items = [...el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]')];
    el.btnSave.disabled = !(items.length && items.every(cb => cb.checked));
  };

  const refreshBadgeAndButton = () => {
    if (!el.badge || !el.btnLanjut) return;
    const ok = getOkCount();
    el.badge.textContent = `${ok}/${TOTAL}`;
    el.badge.classList.remove('d-none');
    el.badge.classList.toggle('bg-success', ok === TOTAL);
    el.badge.classList.toggle('bg-secondary', ok !== TOTAL);
    el.btnLanjut.disabled = (ok !== TOTAL);
  };

  const buildGrid = () => {
    if (!el.grid) return;
    const saved  = loadChecklist();
    const values = saved.items || {};

    el.grid.innerHTML = CHECK_ITEMS.map(it => `
      <div class="col-12 col-md-6">
        <label class="form-check d-flex align-items-center gap-2">
          <input id="ck-${it.id}" class="form-check-input" type="checkbox" data-item="1" ${values[it.id] ? 'checked' : ''}>
          <span class="form-check-label">${it.label}</span>
        </label>
      </div>
    `).join('');

    if (el.note) el.note.value = saved.note || '';

    el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]').forEach(cb => {
      cb.addEventListener('change', () => {
        if (el.checkAll && !cb.checked) el.checkAll.checked = false;
        if (el.checkAll) {
          const arr = [...el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]')];
          el.checkAll.checked = arr.length && arr.every(x => x.checked);
        }
        setSaveEnabled();
      });
    });

    if (el.checkAll) {
      const arr = [...el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]')];
      el.checkAll.checked = arr.length && arr.every(x => x.checked);
    }
    setSaveEnabled();
  };

  el.btnOpen?.addEventListener('click', () => { buildGrid(); bsModal?.show(); });
  el.checkAll?.addEventListener('change', () => {
    el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]').forEach(cb => { cb.checked = el.checkAll.checked; });
    setSaveEnabled();
  });

  el.btnSave?.addEventListener('click', async () => {
    const items = [...el.grid.querySelectorAll('input[type="checkbox"][data-item="1"]')];
    const complete = items.length && items.every(cb => cb.checked);
    if (!complete) {
      await Swal.fire({ icon:'warning', title:'Belum lengkap', text:'Semua poin checklist wajib dicentang.' });
      return;
    }
    const payload = {
      items: Object.fromEntries(items.map(cb => [cb.id.replace('ck-',''), cb.checked])),
      note: (el.note?.value || ''),
      saved_at: new Date().toISOString(),
    };
    saveChecklist(payload);
    bsModal?.hide();
    refreshBadgeAndButton();
    await Swal.fire({ icon:'success', title:'Tersimpan', text:'Checklist berhasil disimpan.', timer:1400, showConfirmButton:false });
  });

  el.btnLanjut?.addEventListener('click', async () => {
    if (getOkCount() !== TOTAL) return;
    const res = await Swal.fire({
      icon:'question', title:'Lanjut ke tahap berikutnya?',
      text:'Data akan dipindahkan ke 3D Struktur.',
      showCancelButton:true, confirmButtonText:'Ya, lanjut', cancelButtonText:'Batal', reverseButtons:true
    });
    if (res.isConfirmed) { el.btnLanjut.disabled = true; el.formLanjut?.submit(); }
  });

  // Init
  refreshBadgeAndButton();
})();
</script>
@endpush
