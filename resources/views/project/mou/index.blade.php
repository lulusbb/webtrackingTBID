{{-- resources/views/project/mou/index.blade.php --}}
@extends('layouts.app')
@section('title','MOU – Project')

@section('content')
    <div class="page-heading text-center">
        <h3>Data Klien Mou</h3>
    </div>

  <div class="card shadow mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien tahap MOU</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-mou" class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.mou.data') }}">
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
</section>
@endsection

@push('scripts')
<script>
(function () {
  'use strict';
  if (typeof $ === 'undefined' || !$.fn || !$.fn.DataTable) return;

  const $aktif = $('#tbl-mou');
  if ($aktif.length && !$.fn.dataTable.isDataTable($aktif)) {
    $aktif.DataTable({
      processing: true, serverSide: true, stateSave: false,
      ajax: { url: $aktif.data('url'), type: 'GET' },
      order: [[4, 'desc']],
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',         name:'nama' },
        { data:'kode_proyek',  name:'kode_proyek' },
        { data:'lokasi_lahan', name:'lokasi_lahan' },
        { data:'created_fmt',  name:'created_fmt', searchable:false },
        { data:'aksi',         orderable:false, searchable:false, className:'text-center' },
      ],
      language:{ emptyTable:'Tidak ada data.', processing:'Memproses...' }
    });
  }
})();
</script>
@endpush
