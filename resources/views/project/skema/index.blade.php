{{-- resources/views/project/skema/index.blade.php --}}
@extends('layouts.app')
@section('title','Skema – Project')

@section('content')
    <div class="page-heading text-center">
        <h3>Data Klien Skema & Plumbing</h3>
    </div>

  {{-- ======== TABEL AKTIF ======== --}}
  <div class="card shadow mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien pada tahap Skema</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-skema" class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.skema.data') }}">
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

  {{-- ======== TABEL CANCEL ======== --}}
  <div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Klien Cancel (Skema)</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-skema-cancel" class="table table-bordered table-striped"
               data-url="{{ route('project.skema.cancel_data') }}">
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

  // ===== AKTIF: Skema =====
  const $aktif = $('#tbl-skema');
  if ($aktif.length && !$.fn.dataTable.isDataTable($aktif)) {
    const baseShow = @json(url('/project/skema')); // untuk link show

    $aktif.DataTable({
      processing: true,
      serverSide: true,
      stateSave: false,
      ajax: { url: $aktif.data('url'), type: 'GET' },
      order: [[4, 'desc']],
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',         name:'nama' },
        { data:'kode_proyek',  name:'kode_proyek' },
        { data:'lokasi_lahan', name:'lokasi_lahan' },
        { data:'created_fmt',  name:'created_fmt', searchable:false },

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
  }

  // ===== CANCEL: Skema =====
  const $cancel = $('#tbl-skema-cancel');
  if ($cancel.length && !$.fn.dataTable.isDataTable($cancel)) {
    $cancel.DataTable({
      processing: true,
      serverSide: true,
      stateSave: false,
      ajax: { url: $cancel.data('url'), type: 'GET' },
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
  }
})();
</script>
@endpush
