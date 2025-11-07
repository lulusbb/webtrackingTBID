@extends('layouts.app')

@section('title','Klien Masuk Survei')

@section('content')
<div class="page-heading text-center">
  <h3>KLIEN SURVEI</h3>
</div>


{{-- INBOX (Pending) --}}

  <div class="card shadow">
    <div class="card-header"><h5 class="card-title mb-0">Antrian Survei</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tbl-inbox"
              class="table table-bordered table-striped"
              data-url="{{ route('studio.survei_inbox.data') }}">
          <thead>
            <tr>
              <th style="width:5%; text-align:center;">No</th>
              <th>Nama</th>
              <th>Kode</th>
              <th class="dt-date">Tgl Masuk</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>  

{{-- JADWAL (Sudah Terjadwal) --}}
<div class="card shadow">
  <div class="card-header"><h5 class="card-title mb-0">Penjadwalan Survei</h5></div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="tbl-scheduled"
             class="table table-bordered table-striped align-middle"
             data-url="{{ route('studio.survei_scheduled.data') }}">
        <thead>
          <tr>
            <th style="width:5%; text-align:center;">No</th>
            <th>Nama</th>
            <th>Kode</th>
            <th>Lokasi</th>
            <th class="dt-datetime">Waktu Survei (WIB)</th>
            <th>Status</th>
            <th style="width:10%; text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Matikan alert error bawaan DataTables
  if ($.fn && $.fn.dataTable) $.fn.dataTable.ext.errMode = 'none';

  // Bersihkan jejak localStorage lama (jika pernah dipakai untuk status)
  try {
    Object.keys(localStorage).forEach(function (k) {
      if (k.indexOf('srvDone:') === 0) localStorage.removeItem(k);
    });
  } catch (e) {}

  /* ================= INBOX (Pending) ================= */
  const $inbox = $('#tbl-inbox');
  if ($inbox.length && !$.fn.dataTable.isDataTable($inbox)) {
    $inbox.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $inbox.data('url') },
      order: [[3, 'desc']], // Tgl Masuk
      columns: [
        { data: 'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data: 'nama',        name:'nama' },
        { data: 'kode',        name:'kode',        orderable:false, searchable:false },
        { data: 'tgl_masuk',   name:'tgl_masuk',   orderable:true,  searchable:false },
        { data: 'status',      name:'status',      orderable:false, searchable:false, className:'text-center' },
        { data: 'aksi',        name:'aksi',        orderable:false, searchable:false, className:'text-center' }
      ]
    });

    // Konfirmasi SETUJUI
    $inbox.on('click', 'form[action*="/approve"] button', function (e) {
      e.preventDefault();
      const form = this.closest('form'); if (!form) return;
      Swal.fire({
        icon: 'question',
        title: 'Setujui permintaan survei?',
        text: 'Status akan berubah menjadi Accepted.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(res => { if (res.isConfirmed) form.submit(); });
    });

    // Konfirmasi TOLAK
    $inbox.on('click', 'form[action*="/reject"] button', function (e) {
      e.preventDefault();
      const form = this.closest('form'); if (!form) return;
      Swal.fire({
        icon: 'warning',
        title: 'Tolak permintaan survei?',
        text: 'Status akan berubah menjadi Rejected.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(res => { if (res.isConfirmed) form.submit(); });
    });
  }

  /* ============= SCHEDULED (klienfixsurvei) ============= */
  const $scheduled = $('#tbl-scheduled');
  if ($scheduled.length && !$.fn.dataTable.isDataTable($scheduled)) {
    $scheduled.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: $scheduled.data('url') },
      order: [[4, 'desc']], // Waktu Survei
      columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
        { data: 'nama',        name:'nama' },
        { data: 'kode',        name:'kode' },
        { data: 'lokasi',      name:'lokasi' },
        { data: 'tgl_jadwal',  name:'tgl_jadwal', orderable:true,  searchable:false },

        // Ambil badge status dari server (HTML sudah dibangkitkan di controller)
        { data: 'status',      name:'status',     orderable:false, searchable:false, className:'text-center' },

        { data: 'aksi',        orderable:false,   searchable:false, className:'text-center' }
      ]
    });
  }
});
</script>

<style>
  /* Ratakan header & isi kolom Status/Aksi */
  #tbl-inbox thead th:nth-child(5),
  #tbl-inbox thead th:nth-child(6),
  #tbl-scheduled thead th:nth-child(6),
  #tbl-scheduled thead th:nth-child(7),
  #tbl-inbox tbody td:nth-child(5),
  #tbl-inbox tbody td:nth-child(6),
  #tbl-scheduled tbody td:nth-child(6),
  #tbl-scheduled tbody td:nth-child(7) {
    text-align: center;
  }
</style>
@endpush
