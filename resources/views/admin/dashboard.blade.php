{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')
@section('title','Dashboard Utama')

@section('content')
<div class="page-content">

@php
  $sumTotalKlien  = is_array($totalKlienSeries ?? null) ? array_sum($totalKlienSeries) : 0;
  $sumTotalCancel = is_array($totalCancelSeries ?? null) ? array_sum($totalCancelSeries) : 0;

  if (! function_exists('tbid_total_badge_kpi')) {
    /**
     * Tentukan class warna badge total berdasar thresholds pengaturan.
     */
    function tbid_total_badge_kpi($title, $total, $thresholds = []): string {
        $t = (int) ($total ?? 0);
        $map = [
            'klien masuk'        => 'klien_masuk',
            'klien survei'       => 'klien_survei',
            'denah & moodboard'  => 'denah',
            '3d desain'          => 'exterior',
            'mep & spek'         => 'mep',
            'serter desain'      => 'serter',
            '3d struktur'        => 'struktur3d',
            'skema plumbing'     => 'skema',
            'rab'                => 'rab',
            'mou'                => 'mou',
            'proyek berjalan'    => 'proyek',
        ];
        $keyTitle = strtolower(trim((string)$title));
        $setKey   = $map[$keyTitle] ?? null;
        if (!$setKey) return '';

        $defaultGeneric = ['red_lt'=>10, 'yellow_min'=>10, 'yellow_max'=>20, 'green_gt'=>20];
        $defaultMasuk   = ['red_lt'=>10, 'yellow_min'=>20, 'yellow_max'=>30, 'green_gt'=>30];
        $defaults = $setKey === 'klien_masuk' ? $defaultMasuk : $defaultGeneric;

        $cfg  = $thresholds[$setKey] ?? $defaults;
        $red  = (int)($cfg['red_lt']     ?? 0);
        $yMin = (int)($cfg['yellow_min'] ?? 0);
        $yMax = (int)($cfg['yellow_max'] ?? 0);

        if ($t < $red)                     return 'mini-total--red';
        if ($t >= $yMin && $t <= $yMax)    return 'mini-total--yellow';
        return '';
    }
  }
@endphp

{{-- ============== FILTER RENTANG TANGGAL (AJAX) ============== --}}
<div class="d-flex align-items-center mb-2">
  <h4 class="fw-bold mb-1">DASHBOARD UTAMA</h4>

  <div class="dropdown filter-range ms-auto">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
            data-bs-toggle="dropdown" aria-expanded="false">
      {{ $rangeLabel ?? '30 Hari Terakhir' }}
    </button>

    <div class="dropdown-menu dropdown-menu-end p-2 filter-menu" style="min-width:330px">
      {{-- Preset (tanpa reload) --}}
      <div class="d-grid" id="presetRange">
        <button type="button" class="dropdown-item" data-range="today">Hari ini</button>
        <button type="button" class="dropdown-item" data-range="7">7 hari terakhir</button>
        <button type="button" class="dropdown-item" data-range="14">14 hari terakhir</button>
        <button type="button" class="dropdown-item" data-range="30">30 hari terakhir</button>
        <button type="button" class="dropdown-item" data-range="all">Semua waktu</button>
      </div>

      <div class="dropdown-divider my-2"></div>

      {{-- Custom range (tanpa reload) --}}
      <div class="px-2 pb-2">
        <div class="fw-semibold small mb-2">Pilih rentang tanggal</div>
        <form id="customRangeForm" class="d-grid gap-2">
          <div>
            <label class="form-label small mb-1">Tanggal mulai</label>
            <input type="date" class="form-control form-control-sm" id="rangeStart"
                   value="{{ $rangeStart ?? '' }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Tanggal akhir</label>
            <input type="date" class="form-control form-control-sm" id="rangeEnd"
                   value="{{ $rangeEnd ?? '' }}" required>
          </div>
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- ============ /FILTER ============ --}}

{{-- ====== REL KARTU HORIZONTAL (scroll-x) ====== --}}
<div class="dash-rail" id="admin-rail">

  {{-- ========== MARKETING ========== --}}
  <span class="dash-chip">MARKETING</span>
  @foreach(($groups[0]['cards'] ?? []) as $c)
    <div class="stack">
      <div class="mini-card js-card">
        <div class="mini-head">
          <div class="mini-title">{{ $c['title'] }}</div>
          <span class="mini-total {{ tbid_total_badge_kpi($c['title'] ?? '', $c['total'] ?? 0, $thresholds ?? []) }}">
            {{ $c['total'] ?? 0 }}
          </span>
        </div>

        <ul class="mini-list">
          @forelse(($c['items'] ?? []) as $it)
            <li class="mini-item">
              <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
              <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
            </li>
          @empty
            <li class="mini-empty">Belum ada data</li>
          @endforelse
        </ul>

        <button type="button" class="mini-toggle js-more" aria-label="Toggle">
          <span class="caret">▾</span>
        </button>
      </div>

      @if(!empty($c['cancel']))
        <div class="mini-card js-card cancel">
          <div class="mini-head">
            <div class="mini-title">Cancel</div>
            <span class="mini-total orange">{{ $c['cancel']['total'] ?? 0 }}</span>
          </div>

          <ul class="mini-list">
            @forelse(($c['cancel']['items'] ?? []) as $it)
              <li class="mini-item">
                <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
                <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
              </li>
            @empty
              <li class="mini-empty">Belum ada data</li>
            @endforelse
          </ul>

          <button type="button" class="mini-toggle js-more" aria-label="Toggle">
            <span class="caret">▾</span>
          </button>
        </div>
      @endif
    </div>
  @endforeach

  {{-- ========== STUDIO ========== --}}
  <span class="dash-chip">STUDIO</span>
  @foreach(($groups[1]['cards'] ?? []) as $c)
    <div class="stack">
      <div class="mini-card js-card">
        <div class="mini-head">
          <div class="mini-title">{{ $c['title'] }}</div>
          <span class="mini-total {{ tbid_total_badge_kpi($c['title'] ?? '', $c['total'] ?? 0, $thresholds ?? []) }}">
            {{ $c['total'] ?? 0 }}
          </span>
        </div>

        <ul class="mini-list">
          @forelse(($c['items'] ?? []) as $it)
            <li class="mini-item">
              <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
              <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
            </li>
          @empty
            <li class="mini-empty">Belum ada data</li>
          @endforelse
        </ul>

        <button type="button" class="mini-toggle js-more" aria-label="Toggle">
          <span class="caret">▾</span>
        </button>
      </div>

      @if(!empty($c['cancel']))
        <div class="mini-card js-card cancel">
          <div class="mini-head">
            <div class="mini-title">Cancel</div>
            <span class="mini-total orange">{{ $c['cancel']['total'] ?? 0 }}</span>
          </div>

          <ul class="mini-list">
            @forelse(($c['cancel']['items'] ?? []) as $it)
              <li class="mini-item">
                <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
                <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
              </li>
            @empty
              <li class="mini-empty">Belum ada data</li>
            @endforelse
          </ul>

          <button type="button" class="mini-toggle js-more" aria-label="Toggle">
            <span class="caret">▾</span>
          </button>
        </div>
      @endif
    </div>
  @endforeach

  {{-- ========== PROJECT ========== --}}
  <span class="dash-chip">PROJECT</span>
  @foreach(($groups[2]['cards'] ?? []) as $c)
    <div class="stack">
      <div class="mini-card js-card">
        <div class="mini-head">
          <div class="mini-title">{{ $c['title'] }}</div>
          <span class="mini-total {{ tbid_total_badge_kpi($c['title'] ?? '', $c['total'] ?? 0, $thresholds ?? []) }}">
            {{ $c['total'] ?? 0 }}
          </span>
        </div>

        <ul class="mini-list">
          @forelse(($c['items'] ?? []) as $it)
            <li class="mini-item">
              <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
              <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
            </li>
          @empty
            <li class="mini-empty">Belum ada data</li>
          @endforelse
        </ul>

        <button type="button" class="mini-toggle js-more" aria-label="Toggle">
          <span class="caret">▾</span>
        </button>
      </div>

      @if(!empty($c['cancel']))
        <div class="mini-card js-card cancel">
          <div class="mini-head">
            <div class="mini-title">Cancel</div>
            <span class="mini-total orange">{{ $c['cancel']['total'] ?? 0 }}</span>
          </div>

          <ul class="mini-list">
            @forelse(($c['cancel']['items'] ?? []) as $it)
              <li class="mini-item">
                <span class="mini-name">{{ $it['nama'] ?? '(Tanpa nama)' }}</span>
                <span class="mini-code">{{ $it['kode'] ?? '–' }}</span>
              </li>
            @empty
              <li class="mini-empty">Belum ada data</li>
            @endforelse
          </ul>

          <button type="button" class="mini-toggle js-more" aria-label="Toggle">
            <span class="caret">▾</span>
          </button>
        </div>
      @endif
    </div>
  @endforeach

</div> {{-- /dash-rail --}}

{{-- ====== BAGIAN GRAFIK ====== --}}
<div class="dash-graphs mt-2">
  <div class="row g-3 equal-row">
    <div class="col-lg-8">
      <div class="chart-card h-100">
        <div class="chart-head">
          <span>Total Klien TBID</span>
          <span class="chart-total" id="sumTotalKlien">Total: {{ number_format($sumTotalKlien) }}</span>
        </div>
        <div class="chart-box fill"><canvas id="totalKlienChart" width="800" height="360"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="chart-card h-100">
        <div class="chart-head">Traffic by Kelas</div>
        <div class="chart-row chart-row--kelas">
          <div class="chart-canvas">
            <canvas id="kelasDonut" width="320" height="320"></canvas>
          </div>
          <ul id="kelasLegend" class="chart-legend"></ul>
        </div>
        <div class="chart-caption" id="kelasSampleCap">
          Total sample: {{ number_format($kelasSample ?? 0) }}
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1 equal-row">
    <div class="col-lg-8">
      <div class="chart-card h-100">
        <div class="chart-head">
          <span>Total Klien Cancel TBID</span>
          <span class="chart-total" id="sumTotalCancel">Total: {{ number_format($sumTotalCancel) }}</span>
        </div>
        <div class="chart-box fill"><canvas id="totalCancelChart" width="800" height="360"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="chart-card h-100">
        <div class="chart-head">Traffic by Proyek</div>
        <div class="chart-box-sm fill"><canvas id="proyekBar" width="420" height="320"></canvas></div>
      </div>
    </div>
  </div>
</div>

</div> {{-- /.page-content --}}

{{-- ================== CSS RINGKAS ================== --}}
<style>
.dash-rail{display:flex;flex-wrap:nowrap;align-items:flex-start;gap:24px;overflow-x:auto;padding:.25rem .25rem 1rem;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch}
.dash-rail::-webkit-scrollbar{height:8px}.dash-rail::-webkit-scrollbar-thumb{background:#33415566;border-radius:20px}
.group-inline{display:flex;flex-direction:column;gap:8px;flex:0 0 auto}
.group-row{display:flex;flex-wrap:nowrap;gap:16px}
.group-row>.stack{flex:0 0 258px}
.dash-chip{display:inline-block;padding:.28rem .6rem;font-size:.72rem;letter-spacing:.4px;border-radius:999px;background:#e2e8f01a;color:#98a2b3;border:1px solid #3341553d}
.filter-range{position:relative;z-index:2100}.filter-range .filter-menu{z-index:2101}
.stack{display:flex;flex-direction:column;gap:10px;min-width:258px}
.mini-card{scroll-snap-align:start;background:var(--bs-body-bg);border:1px solid #33415540;border-radius:14px;box-shadow:0 1px 0 0 #0b12251a,0 8px 24px -12px #0b122533;width:258px;padding:12px 12px 8px;position:relative}
.mini-card.cancel{background:var(--bs-body-bg)}
.mini-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.mini-title{font-weight:700;font-size:.9rem}
.mini-total{min-width:28px;height:22px;padding:0 .5rem;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;border-radius:9px;color:#10b981;background:#10b9811a}
.mini-total.orange{color:#fb923c;background:#fb923c1a}
.mini-total--yellow{color:#f59e0b;background:#f59e0b1a;border-color:#f59e0b33}
.mini-total--red{color:#ef4444;background:#ef44441a;border-color:#ef444433}
.mini-list{list-style:none;padding:0;margin:0}
.mini-item{display:flex;align-items:center;justify-content:space-between;gap:8px;background:#e5e7eb0d;border:1px solid #3341552a;border-radius:8px;padding:8px 10px;margin-bottom:6px;height:34px}
.mini-name{flex:1 1 auto;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:.86rem}
.mini-code{flex:0 0 auto;font-size:.72rem;font-weight:700;background:#11182714;border:1px solid #3341552a;padding:.18rem .48rem;border-radius:7px}
.mini-empty{padding:6px 8px;color:#94a3b8;font-style:italic;background:#0ea5e90a;border:1px dashed #3341552a;border-radius:8px}
.mini-toggle{display:flex;align-items:center;justify-content:center;width:30px;height:26px;border-radius:8px;border:1px solid #3341552a;background:#0b12250b;color:#94a3b8;margin:10px auto 0;line-height:1}
.mini-toggle:hover{background:#0b12251a}.mini-toggle .caret{display:inline-block;transition:transform .18s ease}
.mini-toggle.is-hidden{display:none!important;visibility:hidden!important;pointer-events:none!important}
.js-card.collapsed .mini-item:nth-child(n+6){display:none}
.js-card.expanded .mini-list{max-height:230px;overflow:auto;padding-right:4px}
.js-card.expanded .mini-list::-webkit-scrollbar{width:6px}.js-card.expanded .mini-list::-webkit-scrollbar-thumb{background:#33415555;border-radius:10px}
.js-card.expanded .mini-toggle .caret{transform:rotate(180deg)}
.chart-card{background:var(--bs-body-bg);border:1px solid #33415540;border-radius:14px;box-shadow:0 1px 0 0 #0b12251a,0 8px 24px -12px #0b122533;padding:14px}
.chart-head{font-weight:700;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;gap:10px}
.chart-total{font-weight:600;font-size:.85rem;color:#64748b;background:#64748b1a;border:1px solid #64748b40;padding:.18rem .55rem;border-radius:8px}
.chart-box{height:300px}.chart-box-sm{height:260px}
.equal-row>[class^="col-"],.equal-row>[class*=" col-"]{display:flex}.equal-row .chart-card{display:flex;flex-direction:column;width:100%}.chart-card .fill{flex:1 1 auto}
@media (min-width:992px){.equal-row .chart-box,.equal-row .chart-box-sm,.equal-row .chart-row{min-height:360px}}
.chart-row--kelas .chart-canvas{flex:1 1 0;min-width:240px;display:flex;justify-content:center;align-items:center}
.chart-row--kelas .chart-canvas canvas{max-width:280px;height:260px!important}
.chart-legend{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px}
.chart-legend li{display:flex;align-items:center;gap:8px;color:var(--bs-body-color);font-size:.95rem}
.chart-legend .dot{width:12px;height:12px;border-radius:50%}
.chart-caption{font-size:.9rem;color:#64748b;margin-top:8px}
html[data-bs-theme="dark"] .mini-card{background:#1e1e2d;border:1px solid #2a2f45;box-shadow:0 1px 0 0 rgba(0,0,0,.35),0 8px 24px -12px rgba(0,0,0,.55)}
html[data-bs-theme="dark"] .dash-chip{background:#ffffff0a;border:1px solid #2a2f45;color:#a9b4c6}
html[data-bs-theme="dark"] .mini-item{background:#ffffff0d;border-color:#2f3246}
html[data-bs-theme="dark"] .mini-name{color:#e5e7eb}
html[data-bs-theme="dark"] .mini-code{background:#00000033;border-color:#2f3246;color:#cbd5e1}
html[data-bs-theme="dark"] .mini-total{background:#10b98126;color:#22d3a0}
html[data-bs-theme="dark"] .mini-total.orange{background:#fb923c26;color:#fbbf24}
html[data-bs-theme="dark"] .mini-toggle{background:#ffffff0a;border-color:#2f3246;color:#9aa6b2}
html[data-bs-theme="dark"] .mini-toggle:hover{background:#ffffff12}

/* === Fix spacing mini-item: kiri–kanan sama === */
.mini-card .mini-list{
  padding: 0 !important;          /* hilangkan padding default ul */
  margin: 0 !important;
}
.mini-card .mini-item{
  box-sizing: border-box;
  width: 100%;
  padding-left: 12px !important;  /* kiri */
  padding-right: 12px !important; /* kanan */
}
.mini-card .mini-name{
  margin: 0 !important;
  padding: 0 !important;          /* pastikan teks tidak nambah indent */
}
.mini-card .mini-code{
  margin-right: 0 !important;     /* chip rapat sesuai padding kanan item */
}
/* === KPI badge overrides (dark mode) === */
html[data-bs-theme="dark"] .mini-total.mini-total--red{
  color:#ef4444 !important;
  background:#ef44441a !important;
  border-color:#ef444433 !important;
}
html[data-bs-theme="dark"] .mini-total.mini-total--yellow{
  color:#f59e0b !important;
  background:#f59e0b1a !important;
  border-color:#f59e0b33 !important;
}
/* === Center toggle button like light theme === */
.mini-card{ position: relative; display:flex; flex-direction:column; }
.mini-card .mini-list{ flex:1 1 auto; }          /* biar tombol selalu di bawah */

.mini-toggle{
  position: relative !important;                  /* jangan absolute dari theme lain */
  left: auto !important; right: auto !important;  /* reset */
  transform: none !important;

  display: flex !important;
  align-items: center !important;
  justify-content: center !important;

  width: 30px !important;
  height: 26px !important;
  padding: 0 !important;

  margin: 10px auto 0 !important;                 /* ← center horizontal */
  line-height: 1 !important;
}
.mini-toggle .caret{
  display: inline-block !important;
  transform-origin: center !important;
}

</style>

{{-- ================== JS ================== --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  /* ========= UI rail: grouping & toggle ========= */
  (function groupInline(){
    const rail = document.getElementById('admin-rail'); if (!rail) return;
    const nodes = Array.from(rail.children);
    const isChip = el => el?.classList?.contains('dash-chip');
    const frag = document.createDocumentFragment();
    for (let i=0;i<nodes.length;) {
      if (!isChip(nodes[i])) { frag.appendChild(nodes[i++]); continue; }
      const chip = nodes[i++], wrap = document.createElement('div'); wrap.className='group-inline';
      const head = document.createElement('div'); head.appendChild(chip);
      const row  = document.createElement('div'); row.className='group-row';
      while (i<nodes.length && !isChip(nodes[i])) row.appendChild(nodes[i++]);
      wrap.appendChild(head); wrap.appendChild(row); frag.appendChild(wrap);
    }
    rail.replaceChildren(frag);
  })();

  function setupCard(card){
    const list = card.querySelector('.mini-list');
    const btn  = card.querySelector('.js-more');
    if (!list || !btn) return;
    const items = list.querySelectorAll('.mini-item');
    const many  = items.length > 5;
    const clean = btn.cloneNode(true); btn.replaceWith(clean);
    if (many){
      card.classList.add('collapsed'); card.classList.remove('expanded');
      clean.hidden = false; clean.classList.remove('is-hidden');
      clean.addEventListener('click', ()=>{
        const ex = card.classList.toggle('expanded');
        card.classList.toggle('collapsed', !ex);
      });
    } else {
      clean.hidden = true; clean.classList.add('is-hidden');
      card.classList.remove('collapsed','expanded');
    }
  }
  document.querySelectorAll('.js-card').forEach(setupCard);

  // jangan tutup dropdown saat klik preset
  document.getElementById('presetRange')?.addEventListener('click', e => e.stopPropagation());

  /* ========= CHARTS: init + update ========= */
  const DATA_URL = @json(route('admin.dashboard.data'));
  const sumKlienEl  = document.getElementById('sumTotalKlien');
  const sumCancelEl = document.getElementById('sumTotalCancel');
  const rangeBtn    = document.querySelector('.filter-range .dropdown-toggle');
  const railEl      = document.getElementById('admin-rail');

  const css = getComputedStyle(document.documentElement);
  const tickColor = css.getPropertyValue('--bs-body-color')?.trim() || '#334155';

  const lineBase = {
    type:'line',
    options:{
      responsive:true, maintainAspectRatio:false,
      elements:{ line:{ tension:.25, borderWidth:2.25 }, point:{ radius:2.5, hoverRadius:5, hitRadius:6 }},
      scales:{
        x:{ grid:{ display:false, drawBorder:false }, border:{ display:false }, ticks:{ color:tickColor }},
        y:{ grid:{ display:false, drawBorder:false }, border:{ display:false }, ticks:{ color:tickColor, precision:0 }}
      },
      plugins:{ legend:{ display:false } }
    }
  };

  // buat charts pertama kali (pakai data dari blade)
  let chKlien, chCancel, chKelas, chProyek;
  (function bootInitial(){
    const labels       = @json($chartLabels ?? []);
    const totalSeries  = @json($totalKlienSeries ?? []);
    const cancelSeries = @json($totalCancelSeries ?? []);
    const kelasLabels  = @json($kelasLabels ?? []);
    const kelasData    = @json($kelasData ?? []);
    const proyekLabels = @json($proyekLabels ?? []);
    const proyekData   = @json($proyekData ?? []);

    chKlien  = new Chart(document.getElementById('totalKlienChart'),  { ...lineBase, data:{ labels, datasets:[{ label:'Klien',  data:totalSeries,  borderColor:'#3b82f6', backgroundColor:'#3b82f6', fill:false }] }});
    chCancel = new Chart(document.getElementById('totalCancelChart'), { ...lineBase, data:{ labels, datasets:[{ label:'Cancel', data:cancelSeries, borderColor:'#ef4444', backgroundColor:'#ef4444', fill:false }] }});

    chKelas  = new Chart(document.getElementById('kelasDonut'), {
      type:'doughnut',
      data:{ labels: (kelasLabels||[]).map(k=>'Kelas '+k), datasets:[{ data:kelasData||[], borderWidth:0, hoverOffset:3 }] },
      options:{ responsive:true, maintainAspectRatio:false, cutout:'58%', plugins:{ legend:{ display:false } } }
    });

    chProyek = new Chart(document.getElementById('proyekBar'), {
      type:'bar',
      data:{ labels: proyekLabels||[], datasets:[{ data:proyekData||[], borderWidth:0 }] },
      options:{
        responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false }},
        scales:{
          x:{ grid:{ display:false, drawBorder:false }, border:{ display:false }, ticks:{ color:tickColor }},
          y:{ grid:{ display:false, drawBorder:false }, border:{ display:false }, ticks:{ color:tickColor, precision:0, stepSize:1 }}
        }
      }
    });
  })();

  // ===== RENDER RAIL DARI JSON =====
  function badgeClass(title, total, thresholds){
    const map = {'klien masuk':'klien_masuk','klien survei':'klien_survei','denah & moodboard':'denah','3d desain':'exterior','mep & spek':'mep','serter desain':'serter','3d struktur':'struktur3d','skema plumbing':'skema','rab':'rab','mou':'mou','proyek berjalan':'proyek'};
    const key = map[(title||'').toLowerCase()] || null;
    if (!key) return '';
    const cfg = thresholds?.[key] || {red_lt:10,yellow_min:10,yellow_max:20,green_gt:20};
    const t = +total||0;
    if (t < +cfg.red_lt) return 'mini-total--red';
    if (t >= +cfg.yellow_min && t <= +cfg.yellow_max) return 'mini-total--yellow';
    return '';
  }
  const listHtml = items => (!items?.length)
    ? '<li class="mini-empty">Belum ada data</li>'
    : items.map(it=>`<li class="mini-item"><span class="mini-name">${it.nama||'(Tanpa nama)'}</span><span class="mini-code">${it.kode||'–'}</span></li>`).join('');

  function renderRail(groups, thresholds){
    const out = [];
    (groups||[]).forEach(g => {
      out.push(`<span class="dash-chip">${g.label||''}</span>`);
      (g.cards||[]).forEach(c => {
        out.push(`
          <div class="stack">
            <div class="mini-card js-card">
              <div class="mini-head">
                <div class="mini-title">${c.title||''}</div>
                <span class="mini-total ${badgeClass(c.title,c.total,thresholds)}">${c.total||0}</span>
              </div>
              <ul class="mini-list">${listHtml(c.items)}</ul>
              <button type="button" class="mini-toggle js-more" aria-label="Toggle"><span class="caret">▾</span></button>
            </div>
            ${c.cancel ? `
              <div class="mini-card js-card cancel">
                <div class="mini-head">
                  <div class="mini-title">Cancel</div>
                  <span class="mini-total orange">${c.cancel.total||0}</span>
                </div>
                <ul class="mini-list">${listHtml(c.cancel.items)}</ul>
                <button type="button" class="mini-toggle js-more" aria-label="Toggle"><span class="caret">▾</span></button>
              </div>` : ``}
          </div>`);
      });
    });
    return out.join('');
  }

  function hydrateRail(){
    // group ulang chip + rows
    (function groupInline(){
      const rail = document.getElementById('admin-rail'); if (!rail) return;
      const nodes = Array.from(rail.children);
      const isChip = el => el?.classList?.contains('dash-chip');
      const frag = document.createDocumentFragment();
      for (let i=0;i<nodes.length;){
        if (!isChip(nodes[i])) { frag.appendChild(nodes[i++]); continue; }
        const chip = nodes[i++], wrap = document.createElement('div'); wrap.className='group-inline';
        const head = document.createElement('div'); head.appendChild(chip);
        const row  = document.createElement('div'); row.className='group-row';
        while (i<nodes.length && !isChip(nodes[i])) row.appendChild(nodes[i++]);
        wrap.appendChild(head); wrap.appendChild(row); frag.appendChild(wrap);
      }
      rail.replaceChildren(frag);
    })();
    document.querySelectorAll('.js-card').forEach(setupCard);
  }

  // Terapkan JSON ke UI & charts
  function applyJson(d){
    if (rangeBtn) rangeBtn.textContent = d.rangeLabel || rangeBtn.textContent;
    railEl.innerHTML = renderRail(d.groups, d.thresholds);
    hydrateRail();

    if (sumKlienEl)  sumKlienEl.textContent  = 'Total: ' + (d.sum?.totalKlien ?? 0).toLocaleString();
    if (sumCancelEl) sumCancelEl.textContent = 'Total: ' + (d.sum?.totalCancel ?? 0).toLocaleString();

    if (chKlien)  { chKlien.data.labels = d.chart.labels; chKlien.data.datasets[0].data = d.chart.totalKlien; chKlien.update(); }
    if (chCancel) { chCancel.data.labels = d.chart.labels; chCancel.data.datasets[0].data = d.chart.totalCancel; chCancel.update(); }
    if (chKelas)  { chKelas.data.labels = (d.chart.kelasLabels||[]).map(k=>'Kelas '+k); chKelas.data.datasets[0].data = d.chart.kelasData||[]; chKelas.update(); }
    if (chProyek) { chProyek.data.labels = d.chart.proyekLabels||[]; chProyek.data.datasets[0].data = d.chart.proyekData||[]; chProyek.update(); }

    const cap = document.getElementById('kelasSampleCap');
    if (cap) cap.textContent = 'Total sample: ' + (d.chart.kelasSample ?? 0);
  }

  async function loadRange(params){
    const url = DATA_URL + '?' + new URLSearchParams(params).toString();
    rangeBtn?.classList.add('disabled');
    try{
      const res = await fetch(url, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, cache:'no-store'});
      const json = await res.json();
      applyJson(json);
    } finally {
      rangeBtn?.classList.remove('disabled');
    }
  }

  // Preset click
  document.getElementById('presetRange')?.addEventListener('click', (e)=>{
    const el = e.target.closest('[data-range]'); if (!el) return;
    e.preventDefault(); e.stopPropagation();
    loadRange({range: el.dataset.range});
  });

  // Custom submit
  document.getElementById('customRangeForm')?.addEventListener('submit', (e)=>{
    e.preventDefault(); e.stopPropagation();
    const s = document.getElementById('rangeStart')?.value;
    const t = document.getElementById('rangeEnd')?.value;
    if (!s || !t) return;
    loadRange({range:'custom', start:s, end:t});
  });
})();
</script>
@endpush
@endsection
