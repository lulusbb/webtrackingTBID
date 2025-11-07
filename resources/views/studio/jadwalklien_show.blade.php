@extends('layouts.app')

@section('title','Detail Jadwal Survei')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail Jadwal Survei</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    $rupiahJadwal = fn ($n) => $n !== null ? 'Rp. '.number_format($n, 0, ',', '.') : '-';

    $fileUrl = function (?string $path) {
        if (!$path) return null;
        if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
        return asset('storage/'.$path);
    };

    $jadwalWIB = optional($fix->schedule_at)?->timezone('Asia/Jakarta')->format('d-m-Y H:i');
  @endphp

  <div class="card">
    <div class="card-body">
      <div class="row g-4">

        {{-- KIRI: Data Klien --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small><div class="fw-semibold">{{ $fix->nama ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Email</small><div class="fw-semibold">{{ $fix->email ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">No HP</small><div class="fw-semibold">{{ $fix->no_hp ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small><div class="fw-semibold">{{ $fix->alamat_tinggal ?? '-' }}</div></div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small><div class="fw-semibold">{{ $fix->kode_proyek ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kelas</small><div class="fw-semibold">{{ $fix->kelas ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small><div class="fw-semibold">{{ $fix->lokasi_lahan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Share Lokasi</small><div class="fw-semibold">{{ $fix->share_lokasi ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small><div class="fw-semibold">{{ $fix->luas_lahan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small><div class="fw-semibold">{{ $fix->luas_bangunan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small><div class="fw-semibold">{{ $fix->kebutuhan_ruang ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small><div class="fw-semibold">{{ $fix->konsep_bangunan ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small><div class="fw-semibold">{{ $fix->arah_mata_angin ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small><div class="fw-semibold">{{ $fix->batas_keliling ?? '-' }}</div></div>

          <hr>

          <h6 class="mb-2">Preferensi & Aktivitas</h6>
          <div class="mb-2"><small class="text-muted">Hoby</small><div class="fw-semibold">{{ $fix->hoby ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Aktivitas</small><div class="fw-semibold">{{ $fix->aktivitas ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Prioritas Ruang</small><div class="fw-semibold">{{ $fix->prioritas_ruang ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Kendaraan</small><div class="fw-semibold">{{ $fix->kendaraan ?? '-' }}</div></div>
        </div>

        {{-- KANAN: Rincian & Jadwal --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small><div class="fw-semibold">{{ $rupiahJadwal($fix->budget) }}</div></div>
          <div class="mb-2"><small class="text-muted">Biaya Survei</small><div class="fw-semibold">{{ $rupiahJadwal($fix->biaya_survei) }}</div></div>
          <div class="mb-2"><small class="text-muted">Estimasi Start Pembangunan</small><div class="fw-semibold">{{ $fix->estimasi_start ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ optional($fix->tanggal_masuk)?->format('Y-m-d') ?? '-' }}
            </div>
          </div>
          <div class="mb-2"><small class="text-muted">Keterangan</small><div class="fw-semibold">{{ $fix->keterangan ?? '-' }}</div></div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $url = $fileUrl($fix->sertifikat); @endphp
            <div class="mb-1">
              <small class="text-muted">Sertifikat</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->lembar_diskusi); @endphp
            <div class="mb-1">
              <small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->foto_eksisting); @endphp
            <div class="mb-1">
              <small class="text-muted">Foto Eksisting</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->referensi); @endphp
            <div class="mb-1">
              <small class="text-muted">Referensi</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->layout); @endphp
            <div class="mb-1">
              <small class="text-muted">Layout</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->desain_3d); @endphp
            <div class="mb-1">
              <small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->rab_boq); @endphp
            <div class="mb-1">
              <small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>

            @php $url = $fileUrl($fix->gambar_kerja); @endphp
            <div class="mb-1">
              <small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">
                @if($url)<a href="{{ $url }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif
              </div>
            </div>
          </div>

          <hr>

          <h5 class="mb-2">Informasi Jadwal</h5>
          <div class="mb-2"><small class="text-muted">Waktu Survei (WIB)</small><div class="fw-semibold">{{ $jadwalWIB ?? '-' }}</div></div>
          <div class="mb-2"><small class="text-muted">Dijadwalkan Oleh (ID)</small><div class="fw-semibold">{{ $fix->scheduled_by ?? '-' }}</div></div>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea id="catatan-survei" class="form-control" rows="4" placeholder="Tulis catatan singkat saat survei..." readonly>{{ old('catatan_survei', $fix->catatan_survei) }}</textarea>
          <div class="mt-2 d-flex gap-2">
            <button id="btn-edit" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-pencil-square me-1"></i>Edit
            </button>
            <button id="btn-save" class="btn btn-primary btn-sm d-none">
              <i class="bi bi-save2 me-1"></i>Simpan
            </button>
          </div>

          {{-- ====== UPLOAD LEMBAR SURVEI (PDF) ====== --}}
          <hr class="my-4">
          <h5 class="mb-3">Data Lembar Survei (PDF)</h5>

          @if($fix->lembar_survei)
            <div class="mb-2">
              <a href="{{ asset('storage/'.$fix->lembar_survei) }}" target="_blank" class="btn btn-sm btn-primary">
                <i class="bi bi-filetype-pdf"></i> Lihat PDF
              </a>
            </div>
          @endif

          <form id="form-lembar"
                action="{{ route('studio.survei_scheduled.lembar', $fix->id) }}"
                method="POST"
                enctype="multipart/form-data"
                class="d-flex align-items-center gap-2 flex-wrap">
            @csrf
            @method('PATCH')

            <input type="file"
                  id="lembar_survei"
                  name="lembar_survei"
                  accept="application/pdf"
                  class="form-control"
                  style="max-width:360px;"
                  @if($fix->lembar_survei) disabled @endif
                  required>

            <button type="submit"
                    id="btn-upload-lembar"
                    class="btn btn-success"
                    @if($fix->lembar_survei) disabled @endif>
              <i class="bi bi-upload"></i> Upload
            </button>
            <button type="button"
        id="btn-srv-done"
        class="btn btn-outline-success"
        data-fixid="{{ $fix->id }}">
  Survei Done
</button>
          </form>
          <div class="form-text mt-1">Hanya PDF, maksimal 10 MB.</div>
          {{-- ====== /UPLOAD LEMBAR SURVEI ====== --}}

        </div>

      </div>

{{-- Tombol bawah --}}
<form id="to-denah-form" action="{{ route('studio.survei_scheduled.to_denah', $fix->id) }}" method="POST" class="d-inline">
  @csrf
  <button type="button" id="btn-to-denah" class="btn btn-success">
    <i class="bi bi-check2-circle"></i> Lanjut Denah & Moodboard
  </button>
</form>


        {{-- Klien Cancel --}}
        <button type="button"
                class="btn btn-danger"
                id="btn-klien-cancel"
                data-cancel-url="{{ route('studio.survei_scheduled.cancel', $fix->id) }}">
          <i class="bi bi-x-circle me-1"></i> Klien Cancel
        </button>

        {{-- Fallback form (non-JS) --}}
        <form id="form-cancel-jadwal"
              action="{{ route('studio.survei_scheduled.cancel', $fix->id) }}"
              method="POST" style="display:none;">
          @csrf
          <input type="hidden" name="alasan_cancel" value="">
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ========= CATATAN SURVEI ========= */
  const noteEl  = document.getElementById('catatan-survei');
  const btnEdit = document.getElementById('btn-edit');
  const btnSave = document.getElementById('btn-save');

  function setReadonly(state){
    if (!noteEl) return;
    noteEl.readOnly = state;
    noteEl.classList.toggle('border-primary', !state);
    if (!state) noteEl.focus();
  }

  btnEdit?.addEventListener('click', () => {
    setReadonly(false);
    btnEdit.classList.add('d-none');
    btnSave.classList.remove('d-none');
  });

  btnSave?.addEventListener('click', async () => {
    try {
      const url  = "{{ route('studio.survei_scheduled.note', $fix->id) }}";
      const body = new URLSearchParams({ catatan_survei: noteEl.value });

      const resp = await fetch(url, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        },
        body,
        credentials: 'same-origin'
      });

      if (!resp.ok) throw new Error(await resp.text());

      setReadonly(true);
      btnSave.classList.add('d-none');
      btnEdit.classList.remove('d-none');
      Swal.fire({ icon:'success', title:'Tersimpan', timer:1200, showConfirmButton:false });
    } catch (e) {
      console.error(e);
      Swal.fire({ icon:'error', title:'Gagal menyimpan', text:String(e).slice(0,200) });
    }
  });

  setReadonly(true);

  /* ========= UPLOAD LEMBAR SURVEI (PDF) ========= */
  const formLembar = document.getElementById('form-lembar');
  const inputPdf   = document.getElementById('lembar_survei');
  const btnUpload  = document.getElementById('btn-upload-lembar');

  formLembar?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!inputPdf?.files?.length) return;

    const f = inputPdf.files[0];
    if (f.type !== 'application/pdf') {
      return Swal.fire({ icon:'warning', title:'File harus PDF' });
    }
    if (f.size > 10 * 1024 * 1024) {
      return Swal.fire({ icon:'warning', title:'Maksimal 10 MB' });
    }

    try {
      btnUpload.disabled = true;

      const resp = await fetch(formLembar.action, {
        method: 'POST', // PATCH via @method('PATCH') di form
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(formLembar),
        credentials: 'same-origin'
      });

      if (!resp.ok) throw new Error(await resp.text());

      await Swal.fire({ icon:'success', title:'Berhasil upload', timer:2000, showConfirmButton:false });
      inputPdf.disabled = true;
      btnUpload.disabled = true;
      // location.reload(); // kalau mau auto refresh link "Lihat PDF"
    } catch (e) {
      console.error(e);
      btnUpload.disabled = false;
      Swal.fire({ icon:'error', title:'Gagal upload', text:String(e).slice(0,200) });
    }
  });

  /* ========= TANDAI SUDAH DISURVEI (POST ke server) ========= */
  const btnDone   = document.getElementById('btn-srv-done');
  if (btnDone) {
    const fixId     = btnDone.dataset.fixid || '{{ $fix->id }}';
    const urlDone   = "{{ route('studio.survei_scheduled.done', $fix->id) }}";
    const lsKey     = 'srvDone:' + fixId;
    const already   = {{ $fix->survey_done_at ? 'true' : 'false' }};

    const setDoneState = () => {
      btnDone.disabled = true;
      btnDone.classList.remove('btn-outline-success');
      btnDone.classList.add('btn-success');
      btnDone.textContent = 'Sudah diSurvei';
    };

    // Jika sudah done di DB, disable tombol
    if (already) setDoneState();

    btnDone.addEventListener('click', async () => {
      const ask = await Swal.fire({
        icon:'question',
        title:'Tandai sebagai “Sudah diSurvei”?',
        text:'Status akan diubah di database.',
        showCancelButton:true,
        confirmButtonText:'Ya, tandai',
        cancelButtonText:'Batal',
        reverseButtons:true
      });
      if (!ask.isConfirmed) return;

      try {
        btnDone.disabled = true;

        const resp = await fetch(urlDone, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        });

        if (!resp.ok) throw new Error(await resp.text());

        // Update UI & beri sinyal ke halaman list
        setDoneState();
        try { localStorage.setItem(lsKey, '1'); } catch (_) {}

        Swal.fire({icon:'success', title:'Berhasil', text:'Status ditandai: Sudah diSurvei.', timer:1600, showConfirmButton:false});
      } catch (e) {
        console.error(e);
        btnDone.disabled = false;
        Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat menyimpan. Coba lagi.'});
      }
    });
  }

  /* ========= KLIEN CANCEL (arsip ke survei_cancel) ========= */
  const btnCancel = document.getElementById('btn-klien-cancel');
  if (btnCancel) {
    btnCancel.addEventListener('click', async () => {
      const url = btnCancel.getAttribute('data-cancel-url');
      const {isConfirmed, value} = await Swal.fire({
        icon: 'warning',
        title: 'Batalkan jadwal survei?',
        html: `
          <div class="fw-semibold text-center mb-2">Alasan (opsional)</div>
          <div class="d-flex justify-content-center">
            <textarea id="alasan-denah-cancel"
                      class="swal2-textarea"
                      placeholder="Tulis alasan pembatalan..."
                      style="width:90%;max-width:520px;"></textarea>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusConfirm: false,
        preConfirm: () => (document.getElementById('alasan-cancel')?.value || '').trim()
      });
      if (!isConfirmed || !url) return;

      try {
        const formData = new URLSearchParams();
        formData.append('alasan_cancel', value || '');

        const resp = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          },
          body: formData,
          credentials: 'same-origin'
        });

        if (!resp.ok) throw new Error(await resp.text());

        await Swal.fire({ icon:'success', title:'Jadwal dibatalkan', timer:1600, showConfirmButton:false });
        window.location.href = "{{ route('studio.survei_inbox.index') }}";
      } catch (e) {
        console.error(e);
        // fallback: submit form tersembunyi bila ada
        const fallback = document.getElementById('form-cancel-jadwal');
        if (fallback) {
          fallback.querySelector('input[name="alasan_cancel"]').value = (value || '');
          fallback.submit();
        } else {
          Swal.fire({ icon:'error', title:'Gagal', text:'Silakan coba lagi.' });
        }
      }
    });
  }
})();
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var btn  = document.getElementById('btn-to-denah');
  var form = document.getElementById('to-denah-form');

  if (btn && form && window.Swal) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      Swal.fire({
        title: 'Kirim ke Denah & Moodboard?',
        text: 'Data jadwal akan dipindahkan dan dihapus dari daftar penjadwalan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, kirim',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(function (res) {
        if (res.isConfirmed) form.submit();
      });
    });
  }
});
</script>
@endpush


