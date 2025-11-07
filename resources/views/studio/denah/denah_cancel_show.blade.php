@extends('layouts.app')
@section('title','Detail Cancel Denah')
@section('content')
<div class="page-heading"><h3>Detail Cancel Denah</h3></div>
<div class="page-content">
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $row->nama }}</dd>
        <dt class="col-sm-3">Kode</dt><dd class="col-sm-9">{{ $row->kode_proyek }}</dd>
        <dt class="col-sm-3">Lokasi</dt><dd class="col-sm-9">{{ $row->lokasi_lahan }}</dd>
        <dt class="col-sm-3">Alasan</dt><dd class="col-sm-9">{{ $row->alasan_cancel ?? '-' }}</dd>
        <dt class="col-sm-3">Canceled At</dt><dd class="col-sm-9">{{ $row->canceled_at? $row->canceled_at->format('d-m-Y H:i'):'-' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
