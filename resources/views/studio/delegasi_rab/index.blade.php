@extends('layouts.app')
@section('title','Delegasi RAB')

@section('content')
<div class="page-heading text-center"><h3>DELEGASI RAB</h3></div>

<div class="page-content">
  <div class="card shadow mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Daftar Delegasi RAB</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-delegasi-rab" class="table table-bordered table-striped align-middle"
               data-url="{{ route('studio.delegasirab.data') }}">
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
</div>
@endsection



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const $t = $('#tbl-delegasi-rab');
    if (!$t.length) return;

    // 1) Jika sudah pernah di-init, hancurkan biar tidak double wrapper
    if ($.fn.dataTable.isDataTable($t)) {
      $t.DataTable().clear().destroy();          // destroy instance
      // bersihkan kelas sorting yang tertinggal di header
      $t.find('thead th').removeClass('sorting sorting_asc sorting_desc');
      // bersihkan wrapper yang mungkin tertinggal
      $t.closest('.dataTables_wrapper').find('.row').remove();
    }

    // 2) Inisialisasi ulang dengan pengaturan yang bersih
    const dt = $t.DataTable({
      destroy: true,          // izinkan re-init tanpa duplikasi
      retrieve: false,        // jangan ambil instance lama
      processing: true,
      serverSide: true,
      ajax: { url: $t.data('url'), type: 'GET' },

      // Susunan elemen UI DataTables (1x saja)
      // top: length + filter, table: "t", bottom: info + pagination
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
           "t" +
           "<'row'<'col-sm-6'i><'col-sm-6'p>>",

      order: [[4,'desc']], // urut Tgl Masuk terbaru

      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',             name:'nama' },
        { data:'kode',             name:'kode_proyek' },   // kiriman server
        { data:'lokasi',           name:'lokasi_lahan' },  // kiriman server
        // kirim dari server sudah "Y-m-d", tidak perlu render di client
        { data:'tanggal_masuk',    name:'tanggal_masuk', searchable:false },
        { data:'aksi', orderable:false, searchable:false, className:'text-center' },
      ],

      language: {
        lengthMenu  : 'Show _MENU_ entries',
        search      : 'Search:',
        info        : 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty   : 'Showing 0 to 0 of 0 entries',
        emptyTable  : 'Tidak ada data.',
        paginate    : { previous: 'Previous', next: 'Next' },
        processing  : 'Memproses...'
      }
    });

    // (opsional) intip payload
    dt.on('xhr.dt', () => console.log('DT JSON (Delegasi RAB):', dt.ajax.json()));
  });
})();
</script>
@endpush



