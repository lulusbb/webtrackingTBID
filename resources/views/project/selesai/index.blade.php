@extends('layouts.app')
@section('title','Proyek Selesai')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading text-center"><h3>Data Proyek Selesai</h3></div>

  <div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Daftar Proyek</h5></div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-proyek-selesai"
               class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.selesai.data') }}">
          <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th>Kode</th>
            <th>Lokasi</th>
            <th class="dt-date">Tgl Mulai</th>
            <th class="dt-date">Tgl Selesai</th> {{-- NEW --}}
            <th style="width:160px;">Status</th>
            <th style="width:90px;">Aksi</th>
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

  const $t = $('#tbl-proyek-selesai');
  if ($t.length && !$.fn.dataTable.isDataTable($t)) {
    $t.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $t.data('url'), type: 'GET' },
      order: [[4,'desc']], // urut default: Tgl Mulai
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',       name:'nama' },
        { data:'kode',       name:'kode_proyek' },
        { data:'lokasi',     name:'lokasi_lahan' },
        { data:'tgl_mulai',  name:'tanggal_mulai',   searchable:false },
        { data:'tgl_selesai',name:'tanggal_selesai', searchable:false }, // NEW
        { data:'status_badge', orderable:false, searchable:false },
        { data:'aksi', orderable:false, searchable:false, className:'text-center' },
      ],
      language:{ emptyTable:'Tidak ada data.', processing:'Memproses...' }
    });
  }
})();
</script>
@endpush
