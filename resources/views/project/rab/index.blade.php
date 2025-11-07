{{-- resources/views/project/rab/index.blade.php --}}
@extends('layouts.app')
@section('title','RAB – Project')

@section('content')
    <div class="page-heading text-center">
        <h3>Data Klien RAB</h3>
    </div>

  <div class="card shadow mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien pada tahap RAB</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-rab" class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.rab.data') }}">
          <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th>Kode</th>
            <th>Lokasi</th>
            <th class="dt-date">Tgl Masuk</th>
            <th style="width:90px;">Aksi</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Klien Cancel (RAB)</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-rab-cancel" class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.rab.cancel_data') }}">
          <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th class="dt-date">Tgl Cancel</th>
            <th>Lokasi Lahan</th>
            <th>Alasan Cancel</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
  'use strict';
  if (typeof $ === 'undefined' || !$.fn || !$.fn.DataTable) return;

  // ====== Tabel RAB (aktif) ======
  const $aktif = $('#tbl-rab');
  if ($aktif.length && !$.fn.dataTable.isDataTable($aktif)) {
    const baseShow = @json(url('/project/rab')); // link dasar untuk halaman show

    const dt = $aktif.DataTable({
      processing: true,
      serverSide: true,
      stateSave: false,
      pageLength: 10,
      displayStart: 0,
      deferRender: true,
      ajax: {
        url: $aktif.data('url'),
        type: 'GET',
        data: d => { d._ = Date.now(); } // cegah cache
      },
      order: [[4, 'desc']],
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',         name:'nama',         defaultContent:'-' },
        { data:'kode_proyek',  name:'kode_proyek',  defaultContent:'-' },
        { data:'lokasi_lahan', name:'lokasi_lahan', defaultContent:'-' },
        { data:'created_fmt',  name:'created_fmt',  searchable:false, defaultContent:'-' },

        // Kolom Aksi: hanya tombol View
        {
          data: null, orderable:false, searchable:false, className:'text-center',
          render: function (row) {
            const href = baseShow + '/' + row.id;
            return '<a href="'+href+'" class="btn btn-sm btn-info" title="Lihat"><i class="bi bi-eye"></i></a>';
          }
        },
      ],
      language:{ emptyTable:'Tidak ada data.', processing:'Memproses...' }
    });

    // Pastikan balik ke halaman pertama kalau page sekarang tidak valid
    let fixingPage = false;
    const ensureFirstPage = () => {
      if (fixingPage) return;
      const info = dt.page.info();
      if (info.pages && info.page >= info.pages) {
        fixingPage = true;
        dt.page('first').draw('page');
        fixingPage = false;
      }
    };
    dt.on('xhr.dt draw.dt', ensureFirstPage);
  }

  // ====== Tabel Cancel RAB ======
  const $cancel = $('#tbl-rab-cancel');
  if ($cancel.length && !$.fn.dataTable.isDataTable($cancel)) {
    const dCancel = $cancel.DataTable({
      processing: true,
      serverSide: true,
      stateSave: false,
      pageLength: 10,
      ajax: {
        url: $cancel.data('url'),
        type: 'GET',
        data: d => { d._ = Date.now(); }
      },
      order: [[2,'desc']],
      columns: [
        { data:'DT_RowIndex',    orderable:false, searchable:false, className:'text-center' },
        { data:'nama',           defaultContent:'-' },
        { data:'tanggal_cancel', defaultContent:'-', searchable:false },
        { data:'lokasi_lahan',   defaultContent:'-' },
        { data:'alasan_cancel',  defaultContent:'-' },
      ],
      language:{ emptyTable:'Tidak ada data.', processing:'Memproses...' }
    });

    // (opsional) sama-sama jaga halaman
    let fixingCancel = false;
    dCancel.on('xhr.dt draw.dt', function(){
      if (fixingCancel) return;
      const info = dCancel.page.info();
      if (info.pages && info.page >= info.pages) {
        fixingCancel = true;
        dCancel.page('first').draw('page');
        fixingCancel = false;
      }
    });
  }
})();
</script>
@endpush
