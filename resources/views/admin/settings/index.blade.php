{{-- resources/views/admin/settings/index.blade.php --}}
@extends('layouts.app')
@section('title','Pengaturan')

@section('content')
<div class="page-content">
  <div class="d-flex align-items-center mb-3">
    <h4 class="fw-bold mb-0">Pengaturan</h4>
  </div>

  @if(session('ok'))   <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if(session('warn')) <div class="alert alert-warning">{{ session('warn') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    // Fallback jika $cards belum dikirim dari controller
    $cards = $cards ?? [
      'klien_masuk'  => 'Klien Masuk',
      'klien_survei' => 'Klien Survei',
      'denah'        => 'Denah & Moodboard',
      'exterior'     => '3D Desain',
      'mep'          => 'MEP & Spek',
      'serter'       => 'Serter Desain',
      'struktur3d'   => '3D Struktur',
      'skema'        => 'Skema Plumbing',
      'rab'          => 'RAB',
      'mou'          => 'MOU',
      'proyek'       => 'Proyek Berjalan',
    ];
  @endphp

  {{-- ===== Indikator Dashboard ===== --}}
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div>
        <strong>Indikator Dashboard</strong>
        <div class="text-muted small">Atur batas warna badge total per kartu.</div>
      </div>
      <i class="bi bi-traffic-cone"></i>
    </div>

    <div class="card-body">
      <form method="POST" action="{{ route('settings.thresholds.update') }}">
        @csrf
        <div class="row g-4">
          @foreach($cards as $key => $label)
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">{{ $label }}</h6>
                  @if($key === 'klien_masuk')
                    <span class="badge bg-secondary">Contoh: &lt;10 (Merah), 10–30 (Kuning), &gt;30 (Hijau)</span>
                  @else
                    <span class="badge bg-secondary">Contoh: &lt;10 (Merah), 10–20 (Kuning), &gt;20 (Hijau)</span>
                  @endif
                </div>

                <div class="card-body row g-3">
                  <div class="col-12">
                    <label class="form-label">Merah: nilai &lt;</label>
                    <input type="number" min="0"
                      class="form-control @error($key.'.red_lt') is-invalid @enderror"
                      name="{{ $key }}[red_lt]"
                      value="{{ old($key.'.red_lt', $thresholds[$key]['red_lt'] ?? 10) }}">
                    @error($key.'.red_lt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="col-sm-6">
                    <label class="form-label">Kuning: min</label>
                    <input type="number" min="0"
                      class="form-control @error($key.'.yellow_min') is-invalid @enderror"
                      name="{{ $key }}[yellow_min]"
                      value="{{ old($key.'.yellow_min', $thresholds[$key]['yellow_min'] ?? 10) }}">
                    @error($key.'.yellow_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="col-sm-6">
                    <label class="form-label">Kuning: max</label>
                    <input type="number" min="0"
                      class="form-control @error($key.'.yellow_max') is-invalid @enderror"
                      name="{{ $key }}[yellow_max]"
                      value="{{ old($key.'.yellow_max', $thresholds[$key]['yellow_max'] ?? 20) }}">
                    @error($key.'.yellow_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="col-12">
                    <label class="form-label">Hijau: nilai &gt;</label>
                    <input type="number" min="0"
                      class="form-control @error($key.'.green_gt') is-invalid @enderror"
                      name="{{ $key }}[green_gt]"
                      value="{{ old($key.'.green_gt', $thresholds[$key]['green_gt'] ?? 20) }}">
                    @error($key.'.green_gt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>
            </div>
          @endforeach

          <div class="text-end mt-3">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pengaturan</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ==================== RESET DATABASE ==================== --}}
  <div class="col-12">
    <div class="card border-danger">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="text-danger">Area Berbahaya: Reset Database</strong>
        <i class="bi bi-exclamation-triangle text-danger"></i>
      </div>

      <div class="card-body">
        <p class="text-muted mb-3">
          Pilih tabel yang akan dihapus dan (opsional) batasi dengan rentang tanggal.
          Tabel yang <em>tidak</em> punya kolom tanggal akan dihapus seluruhnya saat Anda menekan tombol eksekusi.
        </p>

        <form method="POST" action="{{ route('settings.resetdb') }}" onsubmit="return confirmResetDB();">
          @csrf

          <div class="row g-3">
            {{-- Pilih tabel --}}
            <div class="col-lg-8">
              <div class="border rounded p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="fw-semibold">Pilih Tabel</div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label for="select-all" class="form-check-label">Pilih semua</label>
                  </div>
                </div>

                <div class="row">
                  @forelse(($resetInfos ?? []) as $info)
                    <div class="col-md-6 mb-2">
                      <div class="form-check">
                        <input class="form-check-input table-check" type="checkbox"
                               name="tables[]" id="tb-{{ $info['table'] }}"
                               value="{{ $info['table'] }}">
                        <label class="form-check-label" for="tb-{{ $info['table'] }}">
                          {{ $info['label'] }}
                          <span class="badge bg-secondary ms-1">{{ number_format($info['count']) }}</span>
                          @if($info['dateCol'])
                            <small class="text-muted d-block">
                              {{ $info['dateCol'] }}:
                              {{ $info['min'] ? \Carbon\Carbon::parse($info['min'])->format('d/m/Y') : '-' }}
                              –
                              {{ $info['max'] ? \Carbon\Carbon::parse($info['max'])->format('d/m/Y') : '-' }}
                            </small>
                          @else
                            <small class="text-warning d-block">tanpa kolom tanggal</small>
                          @endif
                        </label>
                      </div>
                    </div>
                  @empty
                    <div class="col-12"><em>Tidak ada tabel yang terdaftar.</em></div>
                  @endforelse
                </div>
              </div>
            </div>

            {{-- Filter tanggal + mode --}}
            <div class="col-lg-4">
              <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">Filter Tanggal (opsional)</div>

                <div class="mb-2">
                  <label class="form-label small mb-1">Tanggal mulai</label>
                  <input type="date" class="form-control form-control-sm"
                         name="start_date" value="{{ old('start_date') }}">
                </div>
                <div class="mb-3">
                  <label class="form-label small mb-1">Tanggal akhir</label>
                  <input type="date" class="form-control form-control-sm"
                         name="end_date" value="{{ old('end_date') }}">
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="truncate_all" id="truncate_all">
                  <label for="truncate_all" class="form-check-label">
                    Hapus semua (TRUNCATE) &nbsp;<small class="text-muted">(abaikan filter tanggal)</small>
                  </label>
                </div>

                <div class="mb-2">
                  <label class="form-label small">Ketik <code>RESET</code> untuk konfirmasi:</label>
                  <input type="text" id="confirm-text" name="confirm_text"
                         class="form-control form-control-sm" placeholder="RESET">
                </div>

                <button class="btn btn-outline-danger w-100" type="submit" id="btn-exec" disabled>
                  <i class="bi bi-trash3 me-1"></i> Eksekusi Reset
                </button>
                <small class="text-muted d-block mt-2">* Tindakan ini tidak dapat dibatalkan.</small>
              </div>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // pilih semua
  document.getElementById('select-all')?.addEventListener('change', (e) => {
    document.querySelectorAll('.table-check').forEach(cb => cb.checked = e.target.checked);
  });

  // tombol aktif jika ketik RESET
  function refreshExecBtn(){
    const ok = (document.getElementById('confirm-text')?.value || '').trim().toUpperCase() === 'RESET';
    document.getElementById('btn-exec').disabled = !ok;
  }
  document.getElementById('confirm-text')?.addEventListener('input', refreshExecBtn);
  refreshExecBtn();

  // confirm final
  window.confirmResetDB = function(){
    if (document.getElementById('btn-exec').disabled) return false;
    return confirm('Yakin melakukan reset? Tindakan ini tidak dapat dibatalkan.');
  }
</script>
@endpush
