{{-- resources/views/project/struktur3d/index.blade.php --}}
@extends('layouts.app')
@section('title','3D Struktur – Project')

@section('content')
    <div class="page-heading text-center">
        <h3>Data Klien 3D Struktur</h3>
    </div>

  {{-- ==================== TABEL AKTIF ==================== --}}
  <div class="card shadow mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien pada tahap 3D Struktur</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-struktur3d"
               class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.struktur3d.data') }}">
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

  {{-- ==================== TABEL CANCEL ==================== --}}
  <div class="card shadow">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien Cancel (3D Struktur)</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-struktur3d-cancel"
               class="table table-bordered table-striped"
               data-url="{{ route('project.struktur3d.cancel_data') }}">
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
  if (!window.jQuery || !$.fn || !$.fn.DataTable) return;

  const lang = {
    emptyTable: 'Tidak ada data.',
    processing: 'Memproses...',
    search: 'Cari:',
    lengthMenu: 'Show _MENU_ entries',
    paginate: { previous: 'Previous', next: 'Next' }
  };

  /* ======= TABEL AKTIF: STRUKTUR 3D ======= */
  const $aktif = $('#tbl-struktur3d');
  if ($aktif.length && !$.fn.dataTable.isDataTable($aktif)) {
    $aktif.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $aktif.data('url'), type: 'GET' },
      order: [[4, 'desc']], // kolom tanggal_masuk (index 4)
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',           name:'nama',           defaultContent:'-' },
        { data:'kode_proyek',    name:'kode_proyek',    defaultContent:'-' },
        { data:'lokasi_lahan',   name:'lokasi_lahan',   defaultContent:'-' },
        { data:'tanggal_masuk',  name:'tanggal_masuk',  searchable:false, defaultContent:'-' },
        { data:'aksi',           orderable:false, searchable:false, className:'text-center' },
      ],
      // bersihkan aksi agar hanya "lihat"
      drawCallback: function () {
        const api = this.api();
        api.cells(null, 5, { page: 'current' }).every(function () {
          const $cell = $(this.node());
          $cell.find('form').remove();
          const $links = $cell.find('a');
          if ($links.length > 1) {
            const $keep = $links.filter('.btn-view,[title*="Lihat"],[title*="View"], i.bi-eye').first();
            $links.not($keep.is('a') ? $keep : $keep.closest('a')).remove();
          }
        });
      },
      language: lang
    });
  }

  /* ======= TABEL CANCEL: STRUKTUR 3D ======= */
  const $cancel = $('#tbl-struktur3d-cancel');
  if ($cancel.length && !$.fn.dataTable.isDataTable($cancel)) {
    $cancel.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $cancel.data('url'), type: 'GET' },
      order: [[2,'desc']], // tanggal cancel
      columns: [
        { data:'DT_RowIndex',    orderable:false, searchable:false, className:'text-center' },
        { data:'nama',           name:'nama',           defaultContent:'-' },
        { data:'tanggal_cancel', name:'canceled_at',    searchable:false, defaultContent:'-' },
        { data:'lokasi_lahan',   name:'lokasi_lahan',   defaultContent:'-' },
        { data:'alasan_cancel',  name:'alasan_cancel',  defaultContent:'-' },
      ],
      language: lang
    });
  }
})();
</script>
@endpush
