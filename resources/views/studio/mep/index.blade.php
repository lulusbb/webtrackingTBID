@extends('layouts.app')
@section('title','MEP & Spek Material')

@section('content')
<div class="page-heading text-center"><h3>MEP & SPEK MATERIAL</h3></div>

<div class="page-content">
  <div class="card shadow mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien Pengerjaan</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-mep" class="table table-bordered table-striped"
               data-url="{{ route('studio.mep.data') }}">
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

  <div class="card shadow mt-4">
    <div class="card-header"><h5 class="card-title mb-0">Klien Cancel</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-mep-cancel" class="table table-bordered table-striped"
               data-url="{{ route('studio.mep_cancel.data') }}">
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
<script>
(function () {
  'use strict';

  // bersihkan state lama (kalau pernah simpan)
  Object.keys(localStorage).forEach(k=>{
    if (k.startsWith('DataTables_tbl-mep_')) localStorage.removeItem(k);
  });

  // ===== TABEL MEP (aktif) =====
  const $a = $('#tbl-mep');
  if ($a.length) {
    const dt = $a.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $a.data('url') },
      order: [[4,'desc']],              // urut berdasarkan 'created_fmt'
      columns: [
        { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data:'nama',        name:'nama' },
        { data:'kode',        name:'kode_proyek' },     // <-- harus 'kode'
        { data:'lokasi',      name:'lokasi_lahan' },    // <-- harus 'lokasi'
        { data:'created_fmt', name:'created_at', searchable:false },
        { data:'aksi',        orderable:false, searchable:false, className:'text-center' },
      ]
    });

    // debug response di console
    dt.on('xhr.dt', () => console.log('🟢 DT JSON (MEP):', dt.ajax.json()));
  }

  // ===== TABEL CANCEL =====
  const $b = $('#tbl-mep-cancel');
  if ($b.length) {
    const tc = $b.DataTable({
      processing:true, serverSide:true,
      ajax:{ url:$b.data('url') },
      order:[[2,'desc']],
      columns:[
        {data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center'},
        {data:'nama'},
        {data:'tgl_cancel', searchable:false},
        {data:'alamat_tinggal'},
        {data:'lokasi_lahan'},
        {data:'alasan_cancel'}
      ]
    });
    tc.on('xhr.dt', () => console.log('🟢 DT JSON (Cancel):', tc.ajax.json()));
  }
})();
</script>
@endpush
