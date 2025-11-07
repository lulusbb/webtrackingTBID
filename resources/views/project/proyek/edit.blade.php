{{-- resources/views/project/proyek/edit.blade.php --}}
@extends('layouts.app')
@section('title','Edit Proyek Berjalan')

@section('content')
<section class="section px-2 px-md-3">
  {{-- Heading --}}
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ route('project.proyek.index') }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Edit Proyek Berjalan</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('project.proyek.update', $proyek->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- <div class="row g-3">
          {{-- Tanggal Mulai --}}
          <div class="col-md-6">
            <label class="form-label">Tanggal Mulai</label>
            @php
              $tm = $proyek->tanggal_mulai ?? null;
              $valMulai = '';
              if ($tm instanceof \Carbon\CarbonInterface) {
                $valMulai = $tm->timezone('Asia/Jakarta')->format('Y-m-d\TH:i');
              } elseif (!empty($tm)) {
                try { $valMulai = \Carbon\Carbon::parse($tm)->timezone('Asia/Jakarta')->format('Y-m-d\TH:i'); } catch (\Throwable $e) { $valMulai = ''; }
              }
            @endphp
            <input type="datetime-local" name="tanggal_mulai" class="form-control"
                   value="{{ old('tanggal_mulai', $valMulai) }}">
            @error('tanggal_mulai') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div> -->

          {{-- Progres (%) --}}
          <div class="col-md-6">
            <label class="form-label">Status Progres (%)</label>
            @php $prog = (int) old('status_progres', (int)($proyek->status_progres ?? 0)); @endphp
            <input type="range" min="0" max="100" step="1" name="status_progres" id="status_progres"
                   class="form-range" value="{{ $prog }}">
            <div class="small text-muted">Nilai: <span id="progVal">{{ $prog }}</span>%</div>
            @error('status_progres') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          {{-- Keterangan --}}
          <div class="col-12">
            <label class="form-label">Keterangan Progres</label>
            <textarea name="keterangan" rows="4" class="form-control"
                      placeholder="Tuliskan ringkas perkembangan terakhir...">{{ old('keterangan', $proyek->keterangan) }}</textarea>
            @error('keterangan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 d-flex gap-2 mt-2">

            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> Simpan Perubahan
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  (function(){
    const r = document.getElementById('status_progres');
    const o = document.getElementById('progVal');
    if (r && o){
      const upd = ()=> o.textContent = r.value;
      r.addEventListener('input', upd);
      r.addEventListener('change', upd);
    }
  })();
</script>
@endpush
