{{-- resources/views/marketing/dashboard.blade.php --}}
@extends('layouts.app')




@section('content')
<div class="page-content">
  <section class="section dashM px-2 px-md-3 pt-2 pb-1">

    {{-- ===== Header + Range ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h4 class="mb-0 fw-bold">DASHBOARD MARKETING</h4>

      <div class="dropdown ms-auto" data-bs-auto-close="outside">
        @php
          $rl = request('range','30d');
          $label = [
            'today'  => 'Hari ini',
            '7d'     => '7 hari terakhir',
            '14d'    => '14 hari terakhir',
            '30d'    => '30 Hari Terakhir',
            'all'    => 'Semua waktu',
            'custom' => 'Rentang Kustom',
          ][$rl] ?? '30 Hari Terakhir';
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
            <div id="range-panel" class="p-3" style="width:300px;max-width:92vw;display:none;">
              <div class="small text-muted mb-2">Pilih rentang tanggal</div>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tanggal mulai</label>
                  <input id="date-start" type="date" class="form-control form-control-sm" value="{{ $start->toDateString() }}">
                </div>
                <div class="col-12">
                  <label class="form-label">Tanggal akhir</label>
                  <input id="date-end" type="date" class="form-control form-control-sm" value="{{ $end->toDateString() }}">
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

    {{-- ===== KPI row: 3 putih + 1 biru ===== --}}
    <div class="row kpi-row align-items-stretch">
      <div class="col-12 col-md-6 col-lg-3">
        <div class="kpi-card kpi-white card">
          <div class="kpi-body">
            <div class="kpi-icon indigo"><i class="bi bi-people"></i></div>
            <div>
              <div class="kpi-title">Klien Masuk</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiKliens">{{ $kpi['kliens'] }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="kpi-card kpi-white card">
          <div class="kpi-body">
            <div class="kpi-icon red"><i class="bi bi-x-octagon"></i></div>
            <div>
              <div class="kpi-title">Klien Cancel</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiCancel">{{ $kpi['cancel'] }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="kpi-card kpi-white card">
          <div class="kpi-body">
            <div class="kpi-icon cyan"><i class="bi bi-clipboard-check"></i></div>
            <div>
              <div class="kpi-title">Klien Survei</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiSurvei">{{ $kpi['survei'] }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="kpi-card kpi-proyek card">
          <div class="kpi-body">
            <div class="kpi-icon"><i class="bi bi-rocket-takeoff"></i></div>
            <div>
              <div class="kpi-title">Proyek Berjalan</div>
              <p class="kpi-value display-6 fw-bold mb-0" id="kpiProyek">{{ $kpi['proyek_berjalan'] }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Charts + Step list ===== --}}
    <div class="row charts-row">
      <div class="col-12 col-xl-9">
        <div class="card shadow-sm border-0 card-tight">
          <div class="card-body">
            <div class="fw-semibold mb-2">Total Klien Masuk</div>
            <canvas id="lineChart" height="90"></canvas>
          </div>
        </div>

        <div class="row g-2" style="margin-top: var(--dash-gap);">
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
                    <div class="text-muted mt-2">Total sample: <span id="kelasTot">0</span></div>
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
              @foreach($steps as $s)
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



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  /* ================== DATA AWAL dari server ================== */
  let months    = @json($months);
  let lineData  = @json($line);
  let proyek    = @json(array_values($byProyek));
  let proyekLbl = @json(array_keys($byProyek));
  let kelasPct  = @json($kelasPct);
  let ttlKls    = @json($ttlKls);

  /* ================== ELEMENTS ================== */
  const rangeLabel = document.getElementById('rangeLabel');
  const panel      = document.getElementById('range-panel');
  const btnApply   = document.getElementById('range-apply');
  const btnCancel  = document.getElementById('range-cancel');
  const inputStart = document.getElementById('date-start');
  const inputEnd   = document.getElementById('date-end');

  const elKliens = document.getElementById('kpiKliens');
  const elCancel = document.getElementById('kpiCancel');
  const elSurvei = document.getElementById('kpiSurvei');
  const elProyek = document.getElementById('kpiProyek');

  const stepList = document.getElementById('stepList');

  /* ================== CHARTS ================== */
  const ctxLine  = document.getElementById('lineChart');
  const ctxBar   = document.getElementById('barProyek');
  const ctxDonut = document.getElementById('donutKelas');

  const donutColors = ['#3B82F6','#22C55E','#FB7185','#F59E0B'];
  const barColorMap = { BA:'#3B82F6', RE:'#22C55E', DE:'#FB7185', IN:'#F59E0B' };
  const hexToRgba=(h,a=1)=>{let c=h.replace('#','');if(c.length===3)c=c.split('').map(x=>x+x).join('');const r=parseInt(c.slice(0,2),16),g=parseInt(c.slice(2,4),16),b=parseInt(c.slice(4,6),16);return `rgba(${r},${g},${b},${a})`};

  const valueLabelPlugin={id:'valueLabel',afterDatasetsDraw(c){const{ctx}=c,ds=c.data.datasets[0],m=c.getDatasetMeta(0);ctx.save();ctx.font='12px system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,Arial,"Helvetica Neue",sans-serif';ctx.fillStyle='#64748b';ctx.textAlign='center';ctx.textBaseline='bottom';m.data.forEach((b,i)=>{const v=ds.data[i];if(v==null)return;const p=b.tooltipPosition();ctx.fillText(v,p.x,p.y-6)});ctx.restore()}};

  const lineChart=new Chart(ctxLine,{
    type:'line',
    data:{labels:months,datasets:[{label:'Klien',data:lineData,tension:.35,borderWidth:3,pointRadius:3}]},
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
    options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}},
    plugins:[valueLabelPlugin]
  });

  const donutKelas=new Chart(ctxDonut,{
    type:'doughnut',
    data:{labels:['A','B','C','D'],datasets:[{data:[kelasPct.A,kelasPct.B,kelasPct.C,kelasPct.D],backgroundColor:donutColors,borderWidth:0}]},
    options:{plugins:{legend:{display:false}},cutout:'55%'}
  });

  // set legend text
  (function paintLegend(){
    const ids=['dotA','dotB','dotC','dotD']; const vals=[kelasPct.A,kelasPct.B,kelasPct.C,kelasPct.D];
    ids.forEach((id,i)=>{ let el=document.getElementById(id); if(el) el.style.backgroundColor=donutColors[i]; });
    const set=(id,v)=>{ const el=document.getElementById(id); if(el) el.textContent=(v??0)+'%';};
    set('kelasA',vals[0]); set('kelasB',vals[1]); set('kelasC',vals[2]); set('kelasD',vals[3]);
    const t=document.getElementById('kelasTot'); if(t) t.textContent=ttlKls??0;
  })();

  /* ================== HELPERS ================== */
  const rangeMap = {today:'Hari ini','7d':'7 hari terakhir','14d':'14 hari terakhir','30d':'30 Hari Terakhir',custom:'Rentang Kustom'};

  // show/hide panel (support style.display atau .d-none)
  function panelShow(){ if(panel){ panel.style.display='block'; panel.classList.remove('d-none'); } }
  function panelHide(){ if(panel){ panel.style.display='none'; panel.classList.add('d-none'); } }

  async function reloadStats(q){
    const params = new URLSearchParams(q||{range:'30d'});
    const res = await fetch(`{{ route('marketing.dashboard.stats') }}?`+params.toString(),{
      headers:{'X-Requested-With':'XMLHttpRequest'}
    });
    const js = await res.json();
    // KPI
    if(elKliens) elKliens.textContent = js.kpi.kliens;
    if(elCancel) elCancel.textContent = js.kpi.cancel;
    if(elSurvei) elSurvei.textContent = js.kpi.survei;
    if(elProyek) elProyek.textContent = js.kpi.proyek_berjalan;

    // Line
    lineChart.data.labels = js.months;
    lineChart.data.datasets[0].data = js.line;
    lineChart.update();

    // Bar
    const keys = Object.keys(js.byProyek);
    barProyek.data.labels = keys;
    barProyek.data.datasets[0].data = keys.map(k=>js.byProyek[k]);
    barProyek.data.datasets[0].backgroundColor = keys.map(k=>hexToRgba(barColorMap[k]||'#9CA3AF',.35));
    barProyek.data.datasets[0].borderColor = keys.map(k=>barColorMap[k]||'#9CA3AF');
    barProyek.update();

    // Donut
    donutKelas.data.datasets[0].data = [js.kelasPct.A,js.kelasPct.B,js.kelasPct.C,js.kelasPct.D];
    donutKelas.update();
    // legend text
    const set=(id,v)=>{ const el=document.getElementById(id); if(el) el.textContent=(v??0)+'%';};
    set('kelasA',js.kelasPct.A); set('kelasB',js.kelasPct.B); set('kelasC',js.kelasPct.C); set('kelasD',js.kelasPct.D);
    const t=document.getElementById('kelasTot'); if(t) t.textContent = js.ttlKls??0;

    // Step list
    if(stepList){
      stepList.innerHTML = '';
      js.steps.forEach(s=>{
        const li = document.createElement('li');
        li.className='list-group-item d-flex justify-content-between align-items-center px-0';
        li.innerHTML = `<span>${s.label}</span><span class="badge bg-primary-subtle text-primary">${s.total}</span>`;
        stepList.appendChild(li);
      });
    }
  }

  /* ================== EVENTS ================== */
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

  // Toggle custom panel
  document.querySelector('.js-range-custom-toggle')?.addEventListener('click', e=>{
    e.preventDefault(); e.stopPropagation();
    // toggle
    if(panel.style.display==='block' || !panel.classList.contains('d-none') && panel.offsetParent!==null){
      panelHide();
    }else{
      panelShow();
    }
  });
  // keep dropdown open when clicking inside panel
  panel?.addEventListener('click', e=>e.stopPropagation());

  // Apply custom range
  btnApply?.addEventListener('click', ()=>{
    const s=inputStart.value, e=inputEnd.value;
    if(!s || !e){ return; }
    if(s > e){ alert('Tanggal mulai tidak boleh melebihi tanggal akhir'); return; }
    rangeLabel.textContent = `${s} → ${e}`;
    panelHide();
    reloadStats({range:'custom', start:s, end:e});
  });

  // Cancel custom range
  btnCancel?.addEventListener('click', ()=>panelHide());
})();
</script>
@endpush
