@extends('layouts.app')

@section('content')
    {{-- =======================
         DATA KLIEN BARU
    ======================== --}}
    <div class="page-heading text-center">
        <h3>DATA KLIEN MASUK</h3>
    </div>

<div class="card shadow">
  <div class="card-header"><h5 class="card-title mb-0">Klien Baru</h5></div>
  
    <div class="card-body">

      {{-- Toolbar filter & tombol tambah (rapi seperti cancel) --}}
      <div class="row g-2 align-items-center mb-3" id="toolbar-aktif">
        <div class="col-auto">
          <label for="tanggal_awal" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Dari Tanggal</label>
          <input type="date" id="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
        </div>

        <div class="col-auto">
          <label for="tanggal_akhir" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Sampai Tanggal</label>
          <input type="date" id="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
        </div>

        <div class="col-auto">
          <label class="form-label mb-1 d-block">&nbsp;</label>
          <button type="button"
                  id="reset-filter"
                  class="btn btn-danger d-flex align-items-center justify-content-center"
                  title="Reset Filter"
                  style="width:38px;height:38px;padding:0;">
            <i data-feather="rotate-ccw"></i>
          </button>
        </div>
        <div class="col-auto">
          <label class="form-label mb-1">Status Klien</label>
          <select id="status_filter" class="form-select">
            <option value="">Semua</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>
        {{-- NEW: Filter STATUS (pipeline) --}}
        <div class="col-auto">
          <label class="form-label mb-1">Status</label>
          <select id="pipeline_status" class="form-select">
            <option value="all">Semua</option>
            <option value="klien_baru">Klien Baru</option>

            <option value="in_survei">In Survei</option>
            <option value="cancel_survei">Cancel Survei</option>

            <option value="denah_moodboard">In Denah &amp; Moodboard</option>
            <option value="cancel_denah">Cancel Denah</option>

            <option value="in_3d_ext_int">In 3D Ext &amp; Int</option>
            <option value="cancel_in_3d_ext">Cancel 3D Ext &amp; Int</option>

            <option value="in_mep">In MEP &amp; Spek</option>
            <option value="cancel_in_mep">Cancel MEP</option>

            <!-- Tambahan baru -->
            <option value="in_delegasirab">Delegasi RAB</option>

            <option value="in_struktur">In Struktur 3D</option>
            <option value="cancel_struktur3d">Cancel Struktur 3D</option>

            <option value="in_skema">In Skema</option>
            <option value="cancel_skema">Cancel Skema</option>

            <option value="in_rab">In RAB</option>
            <option value="cancel_rab">Cancel RAB</option>

            <!-- Ganti nama Tahap Akhir -> Serter Desain -->
            <option value="in_serter_desain">In Serter Desain</option>
            <option value="cancel_serter_desain">Cancel Serter Desain</option>

            <option value="in_mou">In MOU</option>
            <option value="cancel_mou">Cancel MOU</option>

            <option value="in_proyekjalan">Progres Pembangunan</option>
            <option value="proyek_selesai">Proyek Selesai</option>
          </select>
        </div>      

        {{-- kanan: + Tambah Klien --}}
        <div class="col-auto ms-auto">
          <label class="form-label mb-1 d-block">&nbsp;</label>
          <a href="{{ route('marketing.klien.create') }}"
             class="btn btn-primary d-flex align-items-center"
             style="height:38px;padding:.375rem .8rem;">
            + Tambah Klien
          </a>
        </div>
      </div>

      {{-- Tabel Klien Baru (server-side) --}}
      <div class="table-responsive">
        <table id="table-klien"
               class="table table-bordered table-striped"
               data-url="{{ route('marketing.klien.data') }}">
          <thead>
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th class="dt-date">Tgl Masuk</th>
            <th>Lokasi Proyek</th>
            <th>Budget Awal</th>
            <th>Kelas</th>
            <th>Kode</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </section>
</div>


    {{-- =======================
         DATA KLIEN CANCEL
    ======================== --}}
        <section class="section">
        <div class="card shadow">
          <div class="card-header"><h5 class="card-title mb-0">Klien Cancel</h5></div>
            <div class="card-body">
            {{-- FILTER TANGGAL KLIEN CANCEL (MANDIRI) --}}
            <div class="row g-2 align-items-center mb-3" id="toolbar-cancel">
                <div class="col-auto">
                <label for="tanggal_awal_cancel" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Dari Tanggal</label>
                <input type="date" id="tanggal_awal_cancel" class="form-control">
                </div>

                <div class="col-auto">
                <label for="tanggal_akhir_cancel" class="form-label mb-1 {{ session('theme')==='dark'?'text-white':'' }}">Sampai Tanggal</label>
                <input type="date" id="tanggal_akhir_cancel" class="form-control">
                </div>

                <div class="col-auto">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="button"
                        id="reset-filter-cancel"
                        class="btn btn-danger d-flex align-items-center justify-content-center"
                        title="Reset Filter"
                        style="width:38px;height:38px;padding:0;">
                    <i data-feather="rotate-ccw"></i>
                </button>
                </div>
            </div>        
                        
                    <div class="table-responsive">
                    <table id="table-klien-cancel"
                            class="table table-bordered table-striped"
                            data-url="{{ route('marketing.klien_cancelled.data') }}">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th class="dt-date">Tanggal Cancel</th>
                            <th>Lokasi Proyek</th>
                            <th>Budget Awal</th>
                            <th>Kelas</th>
                            <th>Kode</th>
                            <th>Alasan Cancel</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody> {{-- Diisi otomatis oleh DataTables --}}
                    </table>
                </div>
        </div>
    </section>
@endsection
<style>
  .row-in-survey { opacity: .6; }
  .row-in-survey .btn.disabled { pointer-events: none; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  'use strict';

  // … helper Swal & wiring form biarkan tetap …

  $(function(){

    // ============= TABEL KLIEN AKTIF =============
    const tblAktif = $('#table-klien').DataTable({
      processing: true,
      serverSide: true,
      searchDelay: 300,
      order: [[2,'desc']], // kolom tanggal
      ajax: {
        url: $('#table-klien').data('url'),
        data: function (d) {
          d.tanggal_awal    = $('#tanggal_awal').val();
          d.tanggal_akhir   = $('#tanggal_akhir').val();
          d.status_filter   = $('#status_filter').val();
          d.pipeline_status = $('#pipeline_status').val();
        }
      },
      columns: [
        { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },

        // searchable ke kolom DB asli
        { data:'nama',           name:'kliens.nama' },
        // tanggal: untuk sorting kita map ke *_sort, search by date biarkan via filter custom di controller
        { data:'tanggal_masuk',  name:'tanggal_masuk_sort', searchable:false },

        // lokasi dirender dari 2 field -> jangan ikut disearch
        {
          data:null,
          name:'lokasi_render',
          render:function(row){ return row.lokasi_proyek ?? row.lokasi_lahan ?? '-'; },
          orderable:false,
          searchable:false
        },

        // kolom format → jangan disearch
        { data:'budget_fmt',      name:'budget_fmt',      orderable:false, searchable:false },

        // searchable ke DB
        { data:'kelas',           name:'kliens.kelas' },
        { data:'kode_proyek',     name:'kliens.kode_proyek' },

        // badge/aksi → non-searchable
        { data:'status_badge',    name:'status_raw',      orderable:true,  searchable:false },
        { data:'keterangan_badge',name:'keterangan_badge',orderable:false, searchable:false },
        { data:'aksi',            name:'aksi',            orderable:false, searchable:false }
      ],
      drawCallback: function(){ if (window.feather) feather.replace(); }
    });

    // ============= TABEL KLIEN CANCEL =============
    const tblCancel = $('#table-klien-cancel').DataTable({
      processing: true,
      serverSide: true,
      searchDelay: 300,
      order: [[2,'desc']],
      ajax: {
        url: $('#table-klien-cancel').data('url'),
        data: function (d) {
          d.tanggal_awal  = $('#tanggal_awal_cancel').val();
          d.tanggal_akhir = $('#tanggal_akhir_cancel').val();
        }
      },
      columns: [
        { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },

        // aman disearch ke kolom nama
        { data:'nama',            name:'nama' },

        // tanggal cancel biasanya sudah diformat → jangan disearch
        { data:'tanggal_cancel',  name:'tanggal_cancel', searchable:false },

        // lokasi render → jangan disearch
        {
          data:null,
          name:'lokasi_render',
          render:function(row){ return row.lokasi_proyek ?? row.lokasi_lahan ?? '-'; },
          orderable:false,
          searchable:false
        },

        { data:'budget_fmt',      name:'budget_fmt',      orderable:false, searchable:false },

        // kalau dataset cancel kamu membawa kelas/kode dari tabel cancel, biarkan name:'kelas'/'kode_proyek'
        { data:'kelas',           name:'kelas' },
        { data:'kode_proyek',     name:'kode_proyek' },

        { data:'keterangan_badge',name:'keterangan_badge',orderable:false, searchable:false },
        { data:'aksi',            name:'aksi',            orderable:false, searchable:false }
      ],
      drawCallback: function(){ if (window.feather) feather.replace(); }
    });

    // ========= Handlers (reload) =========
    $('#tanggal_awal, #tanggal_akhir, #status_filter, #pipeline_status').on('change', function(){
      tblAktif.ajax.reload();
    });
    $('#reset-filter').on('click', function(){
      $('#tanggal_awal').val('');
      $('#tanggal_akhir').val('');
      $('#status_filter').val('');
      $('#pipeline_status').val('all');
      tblAktif.ajax.reload();
    });

    $('#tanggal_awal_cancel, #tanggal_akhir_cancel').on('change', function(){
      tblCancel.ajax.reload();
    });
    $('#reset-filter-cancel').on('click', function(){
      $('#tanggal_awal_cancel').val('');
      $('#tanggal_akhir_cancel').val('');
      tblCancel.ajax.reload();
    });

  });
})();
</script>
@endpush


@push('styles')
<style>
  .filter-toolbar .form-label{ font-size:.875rem; }
  /* samakan tinggi tombol dengan .form-control default */
  .btn-eq{ height: calc(2.375rem + 2px); } /* ~38px */
  /* opsional: lebarkan input sedikit biar enak dipakai */
  .filter-toolbar .form-control{ min-width: 210px; }
</style>
@endpush
