@extends('layouts.app')
@section('title','Tahap Akhir')

@section('content')
<div class="page-heading text-center"><h3>SERAH TERIMA DESAIN</h3></div>

<div class="page-content">
  <div class="card shadow mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Klien Serter Desain</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-tahapakhir" class="table table-bordered table-striped"
               data-url="{{ route('studio.akhir.data') }}">
          <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th>Kode</th>
            <th>Lokasi</th>
            <th class="dt-date">Tgl Masuk</th>
            <th style="width:130px;">Status</th>
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
@if (session('success'))
  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: @json(session('success')),
      timer: 1400,
      showConfirmButton: false,
      position: 'center'
    });
  </script>
  @endpush
@endif

@push('scripts')
<script>
(function () {
  'use strict';

  const $a = $('#tbl-tahapakhir');
  if ($a.length && !$.fn.dataTable.isDataTable($a)) {
    const dtAkhir = $a.DataTable({
      processing: true,
      serverSide: true,
      stateSave: false,
      ajax: { url: $a.data('url'), type: 'GET' },
      order: [[4, 'desc']], // index 4 = kolom Tgl Masuk
      columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
        { data: 'nama',     name: 'nama' },
        { data: 'kode',     name: 'kode_proyek' },
        { data: 'lokasi',   name: 'lokasi_lahan' },
        { data: 'tanggal',  name: 'tanggal', searchable: false },
        { data: 'status',   orderable: false, searchable: false, className: 'text-center' },
        { data: 'aksi',     orderable: false, searchable: false, className: 'text-center' },
      ],
      language: {
        emptyTable: 'Tidak ada data.',
        processing:  'Memproses...'
      }
    });

    // (opsional) Debug respons server
    dtAkhir.on('xhr.dt', function () {
      console.log('TahapAkhir JSON:', dtAkhir.ajax.json());
    }).on('error.dt', function (e, settings, techNote, message) {
      console.error('DataTables error (TahapAkhir):', message);
    });
  }
})();
</script>
@endpush
