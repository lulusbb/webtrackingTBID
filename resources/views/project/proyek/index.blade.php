@extends('layouts.app')
@section('title','Proyek Berjalan')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading text-center"><h3>Data Proyek Berjalan</h3></div>

  <div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Daftar Proyek</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-proyek" class="table table-bordered table-striped align-middle"
               data-url="{{ route('project.proyek.data') }}">
          <thead>
          <tr>
            <th style="width:5%;text-align:center;">No</th>
            <th>Nama</th>
            <th>Kode</th>
            <th>Lokasi</th>
            <th class="dt-date">Tgl Mulai</th>
            <th style="width:220px;">Status Progres</th>
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

@push('styles')
<style>
  /* persen di kanan bar */
  .pct-cell{
    min-width: 36px;
    text-align: right;
    font-weight: 600;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  const $t = $('#tbl-proyek');
  if (!$t.length || $.fn.dataTable.isDataTable($t)) return;

  $t.DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: $t.data('url'), type: 'GET' },
    order: [[4,'desc']],
    columns: [
      { data:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
      { data:'nama',       name:'nama' },
      { data:'kode',       name:'kode_proyek' },
      { data:'lokasi',     name:'lokasi_lahan' },
      { data:'tgl_mulai',  name:'tanggal_mulai', searchable:false },

      {
        data: null, orderable:false, searchable:false,
        render: function (data, type, row) {
          let pg = parseInt(row.status_progres ?? row.status ?? 0, 10);
          if (isNaN(pg)) pg = 0;

          // Warna sama seperti di show.blade
          let barClass = 'bg-success';
          if (pg < 25)      barClass = 'bg-danger';
          else if (pg < 50) barClass = 'bg-warning';
          else if (pg < 75) barClass = 'bg-info';
          else              barClass = 'bg-success';

          // TIPIS BANGET: 4px — pakai inline style supaya menang dari tema
        const trackStyle = 'style="--bs-progress-height:8px;height:8px;border-radius:999px;overflow:hidden"';
        const barStyle   = `style="width:${pg}%;height:8px"`;

          return `
            <div class="d-flex align-items-center gap-3">
              <div class="progress flex-grow-1" ${trackStyle}>
                <div class="progress-bar ${barClass}" role="progressbar"
                     ${barStyle} aria-valuenow="${pg}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <div class="pct-cell">${pg}%</div>
            </div>`;
        }
      },

      { data:'aksi', orderable:false, searchable:false, className:'text-center' },
    ],
    language:{ emptyTable:'Tidak ada data.', processing:'Memproses...' }
  });
})();
</script>
@endpush
