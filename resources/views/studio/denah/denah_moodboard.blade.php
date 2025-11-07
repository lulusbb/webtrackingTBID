@extends('layouts.app') {{-- atau layoutmu --}}
@section('title','Denah & Moodboard')

@section('content')
<div class="page-heading text-center">
  <h3>DENAH & MOODBOARD</h3>
</div>


<div class="page-content">

  {{-- Tabel Denah --}}
  <div class="card shadow mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien Pengerjaan Denah & Moodboard</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-denah"
               class="table table-bordered table-striped"
               data-url="{{ route('studio.denah.data') }}">
          <thead>
            <tr>
              <th style="width:5%;text-align:center;">No</th>
              <th>Nama</th>
              <th>Kode</th>
              <th>Lokasi</th>
              <th class="dt-date">Tgl Masuk</th>
              <th style="width:80px;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
  {{-- ====== KLIEN CANCEL (DENAH) ====== --}}
  <div class="card shadow mt-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien Cancel</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-denah-cancel"
              class="table table-bordered table-striped"
              data-url="{{ route('studio.denah_cancel.data') }}">
          <thead>
            <tr>
              <th style="width:5%">No</th>
              <th>Nama</th>
              <th>Tgl Cancel</th>
              <th>Alamat Tinggal</th>
              <th>Lokasi Lahan</th>
              <th>Alasan Cancel</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
  'use strict';

  const $tbl = $('#tbl-denah-cancel');
  if ($tbl.length && !$.fn.dataTable.isDataTable($tbl)) {
    $tbl.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $tbl.data('url') },
      order: [[2,'desc']], // urutkan berdasarkan Tgl Cancel
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',              name:'nama' },
        { data:'tanggal_cancel',    name:'canceled_at', searchable:false },
        { data:'alamat_tinggal',    name:'alamat_tinggal' },
        { data:'lokasi_lahan',      name:'lokasi_lahan' },
        { data:'alasan_cancel',     name:'alasan_cancel' },
      ],
    });
  }
})();
</script>
@endpush
