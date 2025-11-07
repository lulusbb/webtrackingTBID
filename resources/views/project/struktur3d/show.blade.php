@extends('layouts.app')
@section('title','Detail 3D Struktur')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail 3D Struktur</h3>
  </div>

  @php
    use Illuminate\Support\Str;
    $pick = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };
    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';
    $fileUrl = function (?string $p) { if (!$p) return null; if (Str::startsWith($p,['http://','https://','/storage'])) return $p; return asset('storage/'.$p); };
    $k = $struktur3d->klien ?? null;
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">
        {{-- ============= KIRI: Data Klien ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2">
            <small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($struktur3d->nama, optional($k)->nama, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $pick($struktur3d->email, optional($k)->email, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">No HP</small>
            <div class="fw-semibold">{{ $pick($struktur3d->no_hp, optional($k)->no_hp, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($struktur3d->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick($struktur3d->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pick($struktur3d->kelas, optional($k)->kelas, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small>
            <div class="fw-semibold">{{ $pick($struktur3d->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Share Lokasi</small>
            <div class="fw-semibold">{{ $pick($struktur3d->share_lokasi, optional($k)->share_lokasi, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick($struktur3d->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick($struktur3d->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick($struktur3d->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Preferensi & Aktivitas</h6>
          <div class="mb-2"><small class="text-muted">Hoby</small>
            <div class="fw-semibold">{{ $pick($struktur3d->hoby, optional($k)->hoby, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Aktivitas</small>
            <div class="fw-semibold">{{ $pick($struktur3d->aktivitas, optional($k)->aktivitas, '-') }}</div>
          </div>
        </div>

        {{-- ============= KANAN: Rincian & Berkas ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick($struktur3d->budget, optional($k)->budget)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Biaya Survei</small>
            <div class="fw-semibold">{{ $rupiah($pick($struktur3d->biaya_survei, optional($k)->biaya_survei)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Prioritas Ruang</small>
            <div class="fw-semibold">{{ $pick($struktur3d->prioritas_ruang, optional($k)->prioritas_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kendaraan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->kendaraan, optional($k)->kendaraan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Estimasi Start Pembangunan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->estimasi_start, optional($k)->estimasi_start, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ $pick(optional($struktur3d->tanggal_masuk)?->format('Y-m-d'), optional(optional($k)->tanggal_masuk)?->format('Y-m-d'), '-') }}
            </div>
          </div>

          <div class="mb-2"><small class="text-muted">Keterangan</small>
            <div class="fw-semibold">{{ $pick($struktur3d->keterangan, optional($k)->keterangan, '-') }}</div>
          </div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $u = $fileUrl($pick($struktur3d->sertifikat, optional($k)->sertifikat)); @endphp
            <div class="mb-1"><small class="text-muted">Sertifikat</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->foto_eksisting, optional($k)->foto_eksisting)); @endphp
            <div class="mb-1"><small class="text-muted">Foto Eksisting</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->referensi, optional($k)->referensi)); @endphp
            <div class="mb-1"><small class="text-muted">Referensi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->layout, optional($k)->layout)); @endphp
            <div class="mb-1"><small class="text-muted">Layout</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->desain_3d, optional($k)->desain_3d)); @endphp
            <div class="mb-1"><small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->rab_boq, optional($k)->rab_boq)); @endphp
            <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($struktur3d->gambar_kerja, optional($k)->gambar_kerja)); @endphp
            <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($struktur3d->lembar_survei); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Survei (PDF)</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
            </div>
          </div>

          <hr>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick($struktur3d->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
      <form id="form-to-skema" action="{{ route('project.struktur3d.to_skema', $struktur3d->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="button" id="btn-to-skema" class="btn btn-success">
          <i class="bi bi-check2-circle me-1"></i> Lanjut ke Skema
        </button>
      </form>

      <form id="form-struktur3d-cancel" action="{{ route('project.struktur3d.cancel', $struktur3d->id) }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="alasan_cancel" value="">
        <button type="button" id="btn-struktur3d-cancel" class="btn btn-danger">
          <i class="bi bi-x-circle me-1"></i> Klien Cancel
        </button>
      </form>
    </div>

  </div></div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  document.getElementById('btn-to-skema')?.addEventListener('click', async () => {
    const ok = await Swal.fire({icon:'question',title:'Lanjut ke Skema?',text:'Data akan dipindahkan.',showCancelButton:true,confirmButtonText:'Ya, lanjut',cancelButtonText:'Batal',reverseButtons:true});
    if (ok.isConfirmed) document.getElementById('form-to-skema').submit();
  });

  const btn = document.getElementById('btn-struktur3d-cancel');
  const form = document.getElementById('form-struktur3d-cancel');
  btn?.addEventListener('click', async () => {
    const {isConfirmed, value} = await Swal.fire({
      icon:'warning', title:'Batalkan 3D Struktur?',
      html:`<div class="text-start mb-2 fw-semibold">Alasan (opsional)</div><textarea id="alasan" class="swal2-textarea" placeholder="Tulis alasan..."></textarea>`,
      showCancelButton:true, confirmButtonText:'Ya, batalkan', cancelButtonText:'Batal',
      reverseButtons:true, preConfirm: () => (document.getElementById('alasan')?.value || '').trim()
    });
    if (!isConfirmed) return;
    try{
      const resp = await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:new URLSearchParams({alasan_cancel:value||''}),credentials:'same-origin'});
      if(!resp.ok) throw new Error(await resp.text());
      await Swal.fire({icon:'success',title:'Dibatalkan',timer:1200,showConfirmButton:false});
      window.location.href = "{{ route('project.struktur3d.index') }}";
    }catch(e){
      form.querySelector('input[name="alasan_cancel"]').value = value||'';
      form.submit();
    }
  });
})();
</script>
@endpush
