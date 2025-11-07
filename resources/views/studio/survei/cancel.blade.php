@extends('layouts.app') {{-- sesuaikan layout Anda --}}

@section('content')
<div class="page-heading text-center">
  <h3>Klien Cancel</h3>
</div>

<div class="card shadow">
  <div class="card-body">
    <div class="table-responsive">
      <table id="table-cancel" class="table table-bordered table-striped"
             data-url="{{ route('studio.survei_cancel.data') }}">
        <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th class="dt-date">Tgl Cancel</th>
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
@endsection

@push('scripts')
<script>
$(function () {
  var $tbl = $('#table-cancel');
  if ($tbl.length && !$.fn.dataTable.isDataTable($tbl)) {
    $tbl.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $tbl.data('url') },
      order: [[2, 'desc']], // urut berdasarkan Tgl Cancel
      columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data: 'nama',               name: 'nama' },
        { data: 'tanggal_cancel',     name: 'tanggal_cancel', className:'dt-date' },
        { data: 'alamat_tinggal',     name: 'alamat_tinggal' },
        { data: 'lokasi_lahan',       name: 'lokasi_lahan' },
        { data: 'alasan_cancel',      name: 'alasan_cancel' },
      ],
      pageLength: 5,
      lengthMenu: [[5,10,25,50,100],[5,10,25,50,100]],
      language: {
        lengthMenu: 'Tampilkan _MENU_ data per halaman',
        search: 'Search:',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 data',
        zeroRecords: 'Tidak ada data',
        paginate: { previous: 'Previous', next: 'Next' },
        processing: 'Memproses...'
      }
    });
  }
});
</script>
@endpush
