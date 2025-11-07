{{-- resources/views/project/dashboard.blade.php --}}
@extends('layouts.app')
@section('title','Dashboard Project')

@section('content')
<div class="page-content">
  <section class="section dashM px-2 px-md-3 pt-2 pb-1">

    {{-- ===== Header + Range ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="mb-0 fw-bold">DASHBOARD PROJECT</h4>

      <div class="dropdown ms-auto" data-bs-auto-close="outside">
        @php
          $rl = request('range','30d');
          $label = [
            'today'  => 'Hari ini',
            '7d'     => '7 hari terakhir',
            '14d'    => '14 hari terakhir',
            '30d'    => '30 Hari Terakhir',
            'custom' => 'Rentang Kustom',
            'all'    => 'Semua waktu',
          ][$rl] ?? '30 Hari Terakhir';

          use Illuminate\Support\Carbon;
          $__start = isset($start) ? ($start instanceof Carbon ? $start : Carbon::parse($start)) : Carbon::today()->subDays(29);
          $__end   = isset($end)   ? ($end   instanceof Carbon ? $end   : Carbon::parse($end))   : Carbon::today();
        @endphp

        <button id="btnRange" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
          <span id="rangeLabel">{{ $label }}</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end p-0 overflow-hidden" style="min-width:220px;">
          <li><a href="#" class="dropdown-item js-range" data-range="today">Hari ini</a></li>
          <li><a href="#" class="dropdown-item js-range" data-range="7d">7 hari terakhir</a></li>
          <li><a href="#" class="dropdown-item js-range" data-range="14d">14 hari terakhir</a></li>
          <li><a href="#" class="dropdown-item js-range" data-range="30d">30 hari terakhir</a></li>
          <li><a href="#" class="dropdown-item js-range" data-range="all">Semua waktu</a></li>
          <li><hr class="dropdown-divider"></li>
          <li class="p-0">
            <a href="#" class="dropdown-item js-range-custom-toggle">Lainnya…</a>
            <div id="range-panel" class="p-3 d-none" style="width:300px;max-width:92vw;">
              <div class="small text-muted mb-2">Pilih rentang tanggal</div>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tanggal mulai</label>
                  <input id="date-start" type="date" value="{{ $__start->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                  <label class="form-label">Tanggal akhir</label>
                  <input id="date-end" type="date" value="{{ $__end->toDateString() }}" class="form-control form-control-sm">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-3">
                <button id="range-apply"  type="button" class="btn btn-primary btn-sm">Terapkan</button>
                <button id="range-cancel" type="button" class="btn btn-outline-secondary btn-sm">Batal</button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    {{-- ===== KPI: 5 item sejajar ===== --}}
    <div class="row kpi-row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3 align-items-stretch">
      <div class="col">
        <div class="kpi-card card h-100">
          <div class="kpi-body">
            <div class="kpi-icon cyan"><i class="bi bi-people-fill"></i></div>
            <div>
              <div class="kpi-title">Klien Sipil</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiSipil">{{ $kpi['sipil'] ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="kpi-card card h-100">
          <div class="kpi-body">
            <div class="kpi-icon red"><i class="bi bi-x-octagon"></i></div>
            <div>
              <div class="kpi-title">Klien Cancel</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiCancel">{{ $kpi['cancel'] ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="kpi-card card h-100">
          <div class="kpi-body">
            <div class="kpi-icon indigo"><i class="bi bi-journal"></i></div>
            <div>
              <div class="kpi-title">Klien MOU</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiMou">{{ $kpi['mou'] ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="kpi-card card h-100">
          <div class="kpi-body">
            <div class="kpi-icon purple"><i class="bi bi-rocket-takeoff"></i></div>
            <div>
              <div class="kpi-title">Proyek Berjalan</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiJalan">{{ $kpi['jalan'] ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="kpi-card card h-100">
          <div class="kpi-body">
            <div class="kpi-icon orange"><i class="bi bi-check2-square"></i></div>
            <div>
              <div class="kpi-title">Proyek Selesai</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiSelesai">{{ $kpi['selesai'] ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Charts + Step list ===== --}}
    <div class="row charts-row mt-3">
      <div class="col-12 col-xl-9">
        <div class="card shadow-sm border-0 card-tight mb-3">
          <div class="card-body">
            <div class="fw-semibold mb-2">Total Klien Project</div>
            <canvas id="lineChart" height="90"></canvas>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0 card-tight">
              <div class="card-body">
                <div class="fw-semibold mb-2">Traffic by Proyek</div>
                <canvas id="barProyek" height="120"></canvas>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0 card-tight">
              <div class="card-body">
                <div class="fw-semibold mb-2">Traffic by Kelas</div>
                <div class="d-flex align-items-center gap-3">
                  <div style="width:220px"><canvas id="donutKelas" height="220"></canvas></div>
                  <div class="small">
                    <div class="mb-1"><span id="dotA" class="legend-dot me-2"></span> Kelas A: <span id="kelasA">0%</span></div>
                    <div class="mb-1"><span id="dotB" class="legend-dot me-2"></span> Kelas B: <span id="kelasB">0%</span></div>
                    <div class="mb-1"><span id="dotC" class="legend-dot me-2"></span> Kelas C: <span id="kelasC">0%</span></div>
                    <div><span id="dotD" class="legend-dot me-2"></span> Kelas D: <span id="kelasD">0%</span></div>
                    <div class="text-muted mt-2">Total sample: <span id="kelasTot">{{ $ttlKls ?? 0 }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-3">
        <div class="card h-100 shadow-sm border-0 card-tight">
          <div class="card-body">
            <div class="fw-semibold mb-2">Traffic by Step</div>
            <ul class="list-group list-group-flush" id="stepList">
              @foreach(($steps ?? []) as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                  <span>{{ $s['label'] }}</span>
                  <span class="badge bg-primary-subtle text-primary">{{ $s['total'] }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>
@endsection

@push('styles')
<style>
.kpi-row .kpi-card{border:0;border-radius:14px}
.kpi-card .kpi-body{display:flex;align-items:center;gap:12px;padding:16px}
.kpi-card .kpi-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#0ea5e91a}
.kpi-card .kpi-icon.indigo{background:#6366f11a}
.kpi-card .kpi-icon.red{background:#ef44441a}
.kpi-card .kpi-icon.purple{background:#8b5cf61a}
.kpi-card .kpi-icon.orange{background:#f59e0b1a}
.kpi-card .kpi-title{font-size:.875rem;color:#94a3b8}
.kpi-card .kpi-value{line-height:1}
.card-tight .card-body{padding:16px}
.legend-dot{display:inline-block;width:10px;height:10px;border-radius:50%}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  /* ==== DATA AWAL ==== */
  let months    = @json($months ?? []);
  let lineData  = @json($line ?? []);
  let proyek    = @json(array_values($byProyek ?? []));
  let proyekLbl = @json(array_keys($byProyek ?? []));
    let kelasPct = @json((object) ($kelasPct ?? []));
  let ttlKls    = @json($ttlKls ?? 0);

  const rangeLabel = document.getElementById('rangeLabel');
  const panel      = document.getElementById('range-panel');
  const btnApply   = document.getElementById('range-apply');
  const btnCancel  = document.getElementById('range-cancel');
  const inputStart = document.getElementById('date-start');
  const inputEnd   = document.getElementById('date-end');

  const elSipil   = document.getElementById('kpiSipil');
  const elCancel  = document.getElementById('kpiCancel');
  const elMou     = document.getElementById('kpiMou');
  const elJalan   = document.getElementById('kpiJalan');
  const elSelesai = document.getElementById('kpiSelesai');
  const stepList  = document.getElementById('stepList');

  /* ==== CHARTS ==== */
  const ctxLine  = document.getElementById('lineChart');
  const ctxBar   = document.getElementById('barProyek');
  const ctxDonut = document.getElementById('donutKelas');

  const donutColors = ['#3B82F6','#22C55E','#FB7185','#F59E0B'];
  const barColorMap = { BA:'#3B82F6', RE:'#22C55E', DE:'#FB7185', IN:'#F59E0B' };
  const hexToRgba=(h,a=1)=>{let c=h.replace('#','');if(c.length===3)c=c.split('').map(x=>x+x).join('');const r=parseInt(c.slice(0,2),16),g=parseInt(c.slice(2,4),16),b=parseInt(c.slice(4,6),16);return `rgba(${r},${g},${b},${a})`};

  const valueLabelPlugin={id:'valueLabel',afterDatasetsDraw(c){const{ctx}=c,ds=c.data.datasets[0],m=c.getDatasetMeta(0);ctx.save();ctx.font='12px system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,Arial,"Helvetica Neue",sans-serif';ctx.fillStyle='#64748b';ctx.textAlign='center';ctx.textBaseline='bottom';m.data.forEach((b,i)=>{const v=ds.data[i];if(v==null)return;const p=b.tooltipPosition();ctx.fillText(v,p.x,p.y-6)});ctx.restore()}};

  const lineChart=new Chart(ctxLine,{
    type:'line',
    data:{labels:months,datasets:[{label:'Total',data:lineData,tension:.35,borderWidth:3,pointRadius:3}]},
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
  });

  const barProyek=new Chart(ctxBar,{
    type:'bar',
    data:{labels:proyekLbl,datasets:[{
      data:proyek,
      backgroundColor:proyekLbl.map(k=>hexToRgba(barColorMap[k]||'#9CA3AF',.35)),
      borderColor:proyekLbl.map(k=>barColorMap[k]||'#9CA3AF'),
      borderWidth:1
    }]},
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}},plugins:[valueLabelPlugin]
  });

  const donutKelas=new Chart(ctxDonut,{
    type:'doughnut',
    data:{labels:['A','B','C','D'],datasets:[{data:[kelasPct.A??0,kelasPct.B??0,kelasPct.C??0,kelasPct.D??0],backgroundColor:donutColors,borderWidth:0}]},
    options:{plugins:{legend:{display:false}},cutout:'55%'}
  });

  (function paintLegend(){
    const ids=['dotA','dotB','dotC','dotD']; const vals=[kelasPct.A??0,kelasPct.B??0,kelasPct.C??0,kelasPct.D??0];
    ids.forEach((id,i)=>{ const el=document.getElementById(id); if(el) el.style.backgroundColor=donutColors[i]; });
    const set=(id,v)=>{ const el=document.getElementById(id); if(el) el.textContent=(v??0)+'%';};
    set('kelasA',vals[0]); set('kelasB',vals[1]); set('kelasC',vals[2]); set('kelasD',vals[3]);
    const t=document.getElementById('kelasTot'); if(t) t.textContent=ttlKls??0;
  })();

  /* ==== RELOAD ==== */
  const rangeMap = {today:'Hari ini','7d':'7 hari terakhir','14d':'14 hari terakhir','30d':'30 Hari Terakhir','all':'Semua waktu',custom:'Rentang Kustom'};
  const panelShow=()=>{panel?.classList.remove('d-none')}
  const panelHide=()=>{panel?.classList.add('d-none')}

  async function reloadStats(q){
    const params = new URLSearchParams(q||{range:'30d'});
    const res = await fetch(`{{ route('project.dashboard.stats') }}?`+params.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}});
    const js = await res.json();

    // KPI
    elSipil.textContent   = js.kpi.sipil   ?? 0;
    elCancel.textContent  = js.kpi.cancel  ?? 0;
    elMou.textContent     = js.kpi.mou     ?? 0;
    elJalan.textContent   = js.kpi.jalan   ?? 0;
    elSelesai.textContent = js.kpi.selesai ?? 0;

    // Line (all-time)
    lineChart.data.labels = js.months ?? [];
    lineChart.data.datasets[0].data = js.line ?? [];
    lineChart.update();

    // Bar
    const keys = Object.keys(js.byProyek ?? {});
    barProyek.data.labels = keys;
    barProyek.data.datasets[0].data = keys.map(k=>js.byProyek[k]);
    barProyek.data.datasets[0].backgroundColor = keys.map(k=>hexToRgba(({'BA':'#3B82F6','RE':'#22C55E','DE':'#FB7185','IN':'#F59E0B'})[k]||'#9CA3AF',.35));
    barProyek.data.datasets[0].borderColor     = keys.map(k=>({'BA':'#3B82F6','RE':'#22C55E','DE':'#FB7185','IN':'#F59E0B'})[k]||'#9CA3AF');
    barProyek.update();

    // Donut
    donutKelas.data.datasets[0].data = [js.kelasPct.A??0,js.kelasPct.B??0,js.kelasPct.C??0,js.kelasPct.D??0];
    donutKelas.update();
    ['kelasA','kelasB','kelasC','kelasD'].forEach((id,i)=>{
      document.getElementById(id).textContent = [js.kelasPct.A??0,js.kelasPct.B??0,js.kelasPct.C??0,js.kelasPct.D??0][i]+'%';
    });
    const t=document.getElementById('kelasTot'); if(t) t.textContent = js.ttlKls??0;

    // Step list (all-time)
    if(stepList){
      stepList.innerHTML = '';
      (js.steps||[]).forEach(s=>{
        const li = document.createElement('li');
        li.className='list-group-item d-flex justify-content-between align-items-center px-0';
        li.innerHTML = `<span>${s.label}</span><span class="badge bg-primary-subtle text-primary">${s.total}</span>`;
        stepList.appendChild(li);
      });
    }
  }

  // Quick ranges
  document.querySelectorAll('.js-range').forEach(a=>{
    a.addEventListener('click',e=>{
      e.preventDefault();
      const r = a.dataset.range || '30d';
      rangeLabel.textContent = rangeMap[r] || 'Kustom';
      panelHide();
      reloadStats({range:r});
    });
  });

  // Custom panel
  document.querySelector('.js-range-custom-toggle')?.addEventListener('click', e=>{
    e.preventDefault(); e.stopPropagation();
    panel.classList.contains('d-none') ? panelShow() : panelHide();
  });
  panel?.addEventListener('click', e=>e.stopPropagation());

  // Apply custom range
  btnApply?.addEventListener('click', ()=>{
    const s=inputStart.value, e=inputEnd.value;
    if(!s||!e) return;
    if(s>e){ alert('Tanggal mulai tidak boleh melebihi tanggal akhir'); return; }
    rangeLabel.textContent = `${s} → ${e}`;
    panelHide();
    reloadStats({range:'custom', start:s, end:e});
  });

  btnCancel?.addEventListener('click', ()=>panelHide());
})();
</script>
@endpush
