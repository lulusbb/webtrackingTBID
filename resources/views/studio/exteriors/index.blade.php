@extends('layouts.app') {{-- atau ganti ke layouts.app-vertical jika itu layout utamamu --}}
@section('title','3D Exterior & Interior')

@section('content')
<div class="page-heading text-center">
  <h3>3D Desain</h3>
</div>

<div class="page-content">

  {{-- ====== KLIEN PENGERJAAN EXTERIOR ====== --}}
  <div class="card shadow mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien Pengerjaan 3D Desain</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-exteriors"
               class="table table-bordered table-striped"
               data-url="{{ route('studio.exteriors.data') }}">
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

  {{-- ====== KLIEN CANCEL (EXTERIOR) ====== --}}
  <div class="card shadow mt-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien Cancel</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-exterior-cancel"
               class="table table-bordered table-striped"
               data-url="{{ route('studio.exteriors.cancel_data') }}">
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

  // ===== TABEL AKTIF: EXTERIORS =====
  const $ta = $('#tbl-exteriors');
  if ($ta.length && !$.fn.dataTable.isDataTable($ta)) {
    $ta.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $ta.data('url') },
      order: [[4,'desc']], // urutkan Tgl Masuk
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',        name:'nama' },
        { data:'kode',        name:'kode_proyek' },
        { data:'lokasi',      name:'lokasi_lahan' },
        { data:'created_fmt', name:'created_at', searchable:false },
        { data:'aksi',        orderable:false, searchable:false, className:'text-center' },
      ],
    });
  }

  // ===== TABEL CANCEL: EXTERIORS =====
  const $tbl = $('#tbl-exterior-cancel');
  if ($tbl.length && !$.fn.dataTable.isDataTable($tbl)) {
    $tbl.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $tbl.data('url') },
      order: [[2,'desc']], // urutkan berdasarkan Tgl Cancel
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',            name:'nama' },
        { data:'tanggal_cancel',  name:'canceled_at', searchable:false },
        { data:'alamat_tinggal',  name:'alamat_tinggal' },
        { data:'lokasi_lahan',    name:'lokasi_lahan' },
        { data:'alasan_cancel',   name:'alasan_cancel' },
      ],
    });
  }

  // ===== KONFIRMASI: tombol Edit di kolom Aksi =====
  $(document).on('click', '#tbl-exteriors .btn-edit-exterior', async function(e) {
    e.preventDefault();
    const href = this.getAttribute('href');
    if (!href) return;

    const ok = await Swal.fire({
      icon: 'question',
      title: 'Edit data ini?',
      text: 'Anda akan membuka halaman edit.',
      showCancelButton: true,
      confirmButtonText: 'Ya, lanjut',
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
    if (ok.isConfirmed) window.location.href = href;
  });

})();
</script>
@endpush

