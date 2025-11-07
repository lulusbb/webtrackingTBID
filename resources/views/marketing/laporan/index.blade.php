@extends('layouts.app')

@section('title', 'Laporan Klien')

@section('content')
<div class="page-heading text-center">
    <h3>LAPORAN DATA KLIEN</h3>
</div>

{{-- ===================== KLIEN BARU (sumber: tabel kliens) ===================== --}}
<div class="card shadow mb-5">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Klien Baru</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
            <div class="row g-3 align-items-end flex-grow-1">
                <div class="col-auto">
                    <label for="lap_tanggal_awal" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Dari Tanggal</label>
                    <input type="date" id="lap_tanggal_awal" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="lap_tanggal_akhir" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Sampai Tanggal</label>
                    <input type="date" id="lap_tanggal_akhir" class="form-control">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1 d-block">&nbsp;</label>
                    <button type="button" id="lap_reset" class="btn btn-danger d-flex align-items-center justify-content-center"
                            style="width:38px;height:38px;padding:0" title="Reset Filter">
                        <i data-feather="rotate-ccw"></i>
                    </button>
                </div>
            </div>

            <div class="ms-auto mt-3 mt-lg-0">
                <a id="btn-export-aktif" href="#" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i> Export Data
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table-klien-laporan"
                   class="table table-bordered table-striped w-100"
                   data-url="{{ route('marketing.laporan.klien.data') }}">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Tgl Masuk</th>
                        <th>Lokasi Proyek</th>
                        <th>Budget Awal</th>
                        <th>Kelas</th>
                        <th>Kode</th>
                        <th>Keterangan</th>
                        <th style="width:70px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== KLIEN CANCEL ===================== --}}
<div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Data Klien Cancel</h5></div>
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
            <div class="row g-3 align-items-end flex-grow-1">
                <div class="col-auto">
                    <label for="lap_cancel_awal" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Dari Tanggal</label>
                    <input type="date" id="lap_cancel_awal" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="lap_cancel_akhir" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Sampai Tanggal</label>
                    <input type="date" id="lap_cancel_akhir" class="form-control">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1 d-block">&nbsp;</label>
                    <button type="button" id="lap_cancel_reset" class="btn btn-danger d-flex align-items-center justify-content-center"
                            style="width:38px;height:38px;padding:0" title="Reset Filter">
                        <i data-feather="rotate-ccw"></i>
                    </button>
                </div>
            </div>

            <div class="ms-auto mt-3 mt-lg-0">
                <a id="btn-export-cancel" href="#" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i> Export Data
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table-klien-cancel-laporan"
                   class="table table-bordered table-striped w-100"
                   data-url="{{ route('marketing.laporan.klien_cancel.data') }}">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Tgl Masuk</th>
                        <th>Lokasi Proyek</th>
                        <th>Budget Awal</th>
                        <th>Kelas</th>
                        <th>Kode</th>
                        <th>Keterangan</th>
                        <th style="width:70px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const $  = window.jQuery;
  const qs = (id) => document.getElementById(id);

  // =============== KLIEN AKTIF (LAPORAN) ===============
  const tblAktif = $('#table-klien-laporan').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 5,
    searchDelay: 300,
    order: [[2,'desc']], // kolom tanggal
    ajax: {
      url: $('#table-klien-laporan').data('url'),
      data: function (d) {
        d.tanggal_awal  = qs('lap_tanggal_awal').value  || '';
        d.tanggal_akhir = qs('lap_tanggal_akhir').value || '';
      }
    },
    columns: [
      { data:'DT_RowIndex', orderable:false, searchable:false },

      // searchable ke kolom DB asli
      { data:'nama',           name:'kliens.nama' },

      // tanggal untuk sorting saja
      { data:'tanggal_masuk',  name:'tanggal_masuk_sort', searchable:false },

      // lokasi dirender dari beberapa field -> jangan di-search
      {
        data:null,
        name:'lokasi_render',
        render:function(row){ return row.lokasi_proyek ?? row.lokasi_lahan ?? '-'; },
        orderable:false,
        searchable:false
      },

      { data:'budget_fmt',     name:'budget_fmt',      orderable:false, searchable:false },

      // searchable ke kolom DB asli
      { data:'kelas',          name:'kliens.kelas' },
      { data:'kode_proyek',    name:'kliens.kode_proyek' },

      // badge -> jangan ikut search
      { data:'keterangan_badge', name:'keterangan_badge', orderable:false, searchable:false },

      { data:'aksi', orderable:false, searchable:false }
    ],
    drawCallback: function(){ if (window.feather) feather.replace(); }
  });

  qs('lap_tanggal_awal').addEventListener('change', () => tblAktif.ajax.reload());
  qs('lap_tanggal_akhir').addEventListener('change', () => tblAktif.ajax.reload());
  qs('lap_reset').addEventListener('click', function(){
    qs('lap_tanggal_awal').value  = '';
    qs('lap_tanggal_akhir').value = '';
    tblAktif.ajax.reload();
  });

  // =============== KLIEN CANCEL (LAPORAN) ===============
  const tblCancel = $('#table-klien-cancel-laporan').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 5,
    searchDelay: 300,
    order: [[2,'desc']],
    ajax: {
      url: $('#table-klien-cancel-laporan').data('url'),
      data: function (d) {
        d.tanggal_awal  = qs('lap_cancel_awal').value  || '';
        d.tanggal_akhir = qs('lap_cancel_akhir').value || '';
      }
    },
    columns: [
      { data:'DT_RowIndex', orderable:false, searchable:false },

      // searchable ke kolom DB asli
      { data:'nama',            name:'nama' },

      // kolom tanggal cancel untuk sorting saja (samakan dengan field yang server kirim)
      { data:'tanggal_masuk',   name:'tanggal_masuk', searchable:false },

      {
        data:null,
        name:'lokasi_render',
        render:function(row){ return row.lokasi_proyek ?? row.lokasi_lahan ?? '-'; },
        orderable:false,
        searchable:false
      },

      { data:'budget_fmt',      name:'budget_fmt',      orderable:false, searchable:false },
      { data:'kelas',           name:'kelas' },
      { data:'kode_proyek',     name:'kode_proyek' },

      { data:'keterangan_badge', name:'keterangan_badge', orderable:false, searchable:false },

      { data:'aksi', orderable:false, searchable:false }
    ],
    drawCallback: function(){ if (window.feather) feather.replace(); }
  });

  qs('lap_cancel_awal').addEventListener('change',  () => tblCancel.ajax.reload());
  qs('lap_cancel_akhir').addEventListener('change', () => tblCancel.ajax.reload());
  qs('lap_cancel_reset').addEventListener('click', function(){
    qs('lap_cancel_awal').value  = '';
    qs('lap_cancel_akhir').value = '';
    tblCancel.ajax.reload();
  });

  // ========== DOWNLOAD HELPER ==========
  function startDownload(url){
    window.open(url, '_blank'); // jangan blok UI utama
    try { tblAktif.processing(false); }  catch(e){}
    try { tblCancel.processing(false); } catch(e){}
    const ov = document.getElementById('loading-overlay');
    if (ov) ov.style.display = 'none';
  }

  // Export Aktif
  qs('btn-export-aktif').addEventListener('click', function(e){
    e.preventDefault();
    const a = qs('lap_tanggal_awal').value  || '';
    const b = qs('lap_tanggal_akhir').value || '';
    const url = `{{ route('marketing.laporan.klien.export') }}?tanggal_awal=${encodeURIComponent(a)}&tanggal_akhir=${encodeURIComponent(b)}`;
    startDownload(url);
  });

  // Export Cancel
  qs('btn-export-cancel').addEventListener('click', function(e){
    e.preventDefault();
    const a = qs('lap_cancel_awal').value  || '';
    const b = qs('lap_cancel_akhir').value || '';
    const url = `{{ route('marketing.laporan.klien_cancel.export') }}?tanggal_awal_cancel=${encodeURIComponent(a)}&tanggal_akhir_cancel=${encodeURIComponent(b)}`;
    startDownload(url);
  });

  // Safety net
  $('#table-klien-laporan, #table-klien-cancel-laporan')
    .on('error.dt xhr.dt draw.dt', function(){
      try { tblAktif.processing(false); }  catch(e){}
      try { tblCancel.processing(false); } catch(e){}
    });
});
</script>
@endpush

