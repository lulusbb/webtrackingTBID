/* public/mazer/assets/js/custom.js
   Custom helpers for Mazer + Laravel + DataTables (enhanced)
*/
(function () {
  'use strict';

  // ====================
  // Loading overlay helpers
  // ====================
  function showLoading() {
    var ov = document.getElementById('loading-overlay');
    if (ov) ov.style.display = 'flex';
  }
  function hideLoading() {
    var ov = document.getElementById('loading-overlay');
    if (ov) ov.style.display = 'none';
  }
  window.showLoading = showLoading;
  window.hideLoading = hideLoading;

  // ====================
  // Feather helper (safe)
  // ====================
  function renderFeatherIcons(scope) {
    try {
      if (typeof feather !== 'undefined' && feather && typeof feather.replace === 'function') {
        if (scope && scope.querySelectorAll) {
          scope.querySelectorAll('[data-feather]').forEach(function (el) {
            var name = el.getAttribute('data-feather');
            var svg = feather.icons[name] ? feather.icons[name].toSvg() : null;
            if (svg) el.outerHTML = svg;
          });
        } else {
          feather.replace();
        }
      }
    } catch (e) { /* ignore */ }
  }

  /* ====== GLOBAL DATE SORT FOR DATATABLES ====== */
  (function () {
    if (typeof $ === 'undefined' || !$.fn || !$.fn.dataTable) return;

    // Matikan alert bawaan DataTables
    if ($.fn.dataTable && $.fn.dataTable.ext) {
      $.fn.dataTable.ext.errMode = 'none';
    }

    // --- (A) Jika moment.js tersedia, pakai plugin DataTables+moment
    if (window.moment && $.fn.dataTable.moment) {
      $.fn.dataTable.moment('YYYY-MM-DD');
      $.fn.dataTable.moment('DD-MM-YYYY');
      $.fn.dataTable.moment('YYYY-MM-DD HH:mm');
      $.fn.dataTable.moment('DD-MM-YYYY HH:mm');
    } else {
      // --- (B) Parser ringan tanpa moment.js
      var _toText = function (d) {
        if (d == null) return '';
        if (typeof d === 'string') return d;
        try { return String(d).replace(/<.*?>/g, '').trim(); } catch (e) { return String(d); }
      };

      var _parseDate = function (s) {
        s = _toText(s);
        if (!s) return NaN;

        var t1 = Date.parse(s);
        if (!isNaN(t1)) return t1;

        var m = s.match(/^(\d{2})-(\d{2})-(\d{4})(?:[ T](\d{2}):(\d{2}))?$/);
        if (m) {
          var dd = Number(m[1]);
          var mm = Number(m[2]) - 1;
          var yyyy = Number(m[3]);
          var HH = Number(m[4] || '00');
          var MM = Number(m[5] || '00');
          var date = new Date(yyyy, mm, dd, HH, MM, 0, 0);
          return date.getTime();
        }
        return NaN;
      };

      $.fn.dataTable.ext.type.detect.unshift(function (d) {
        return isNaN(_parseDate(d)) ? null : 'smart-date';
      });
      $.fn.dataTable.ext.type.order['smart-date-pre'] = function (d) {
        var ts = _parseDate(d);
        return isNaN(ts) ? 0 : ts;
      };
    }

    // Default global
    $.extend(true, $.fn.dataTable.defaults, {
      columnDefs: [
        { targets: 'dt-date',     type: (window.moment && $.fn.dataTable.moment ? 'date' : 'smart-date') },
        { targets: 'dt-datetime', type: (window.moment && $.fn.dataTable.moment ? 'date' : 'smart-date') }
      ]
    });
  })();

  // ====================
  // Helpers UI kecil
  // ====================
  function debounce(fn, wait) {
    var t;
    return function () {
      clearTimeout(t);
      var args = arguments, ctx = this;
      t = setTimeout(function () { fn.apply(ctx, args); }, wait);
    };
  }
  function initTooltips(container) {
    try {
      (container || document).querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        bootstrap.Tooltip.getOrCreateInstance(el);
      });
    } catch (e) { /* ignore */ }
  }
  function renderChip(containerId, name, value, onRemove) {
    var wrap = document.getElementById(containerId);
    if (!wrap) return;
    var id = 'chip-' + name;
    var old = document.getElementById(id);
    if (old) old.remove();
    if (!value) return;

    var span = document.createElement('span');
    span.className = 'filter-chip';
    span.id = id;
    span.innerHTML = '<i class="bi bi-funnel"></i> ' + name + ': ' + value +
                     ' <i class="bi bi-x" role="button" aria-label="remove"></i>';
    span.querySelector('.bi-x').addEventListener('click', function () {
      if (typeof onRemove === 'function') onRemove();
      span.remove();
    });
    wrap.appendChild(span);
  }

  document.addEventListener('DOMContentLoaded', function () {
    // ====================
    // THEME (dark / light)
    // ====================
    var html = document.documentElement;
    var toggle = document.getElementById('theme-toggle');
    var savedTheme = localStorage.getItem('bs-theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    if (toggle) toggle.checked = savedTheme === 'dark';
    if (toggle) {
      toggle.addEventListener('change', function () {
        var newTheme = toggle.checked ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('bs-theme', newTheme);
      });
    }

    // ====================
    // SIDEBAR COLLAPSE (desktop)
    // ====================
    var sidebarToggle = document.getElementById('toggleSidebar');
    var body = document.body;
    var collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (collapsed) body.classList.add('sidebar-collapsed');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', function (e) {
        e.preventDefault();
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
      });
    }

    // ====================
    // DATATABLES: Global defaults + semua tabel
    // ====================
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
      $.extend(true, $.fn.dataTable.defaults, {
        dom: 'lfrtip',
        pageLength: 5,
        lengthMenu: [[5,10,25,50,100],[5,10,25,50,100]],
        language: {
          lengthMenu: 'Tampilkan _MENU_ data per halaman',
          search: 'Search:',
          info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
          infoEmpty: 'Menampilkan 0 data',
          zeroRecords: 'Tidak ada data',
          paginate: { previous: 'Previous', next: 'Next' },
          processing: 'Memproses...'
        }
      });

      $(function () {
        // ===== 1) KLIEN AKTIF
        var $tblAktif  = $('#table-klien');
        if ($tblAktif.length && !$.fn.dataTable.isDataTable($tblAktif)) {
          var dtAktif = $tblAktif.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
              url: $tblAktif.data('url'),
              data: function (d) {
                d.tanggal_awal  = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
                d.status_filter = $('#status_filter').val();
              }
            },
            order: [[2,'desc']],
            columns: [
              { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',            name:'nama' },
              { data: 'tanggal_masuk',   name:'tanggal_masuk', orderable:true,  searchable:false },
              { data: 'lokasi_lahan',    name:'lokasi_lahan' },
              { data: 'budget_fmt',      name:'budget_fmt', searchable:false },
              { data: 'kelas',           name:'kelas' },
              { data: 'kode_proyek',     name:'kode_proyek' },
              { data: 'status_badge',    orderable:false, searchable:false },
              { data: 'keterangan_badge',orderable:false, searchable:true },
              { data: 'aksi',            orderable:false, searchable:false }
            ],
            createdRow: function (row, data) {
              if (data && (data.status_raw === 'in_survei' ||
                           data.status_raw === 'cancel_survei' ||
                           data.status_raw === 'denah_moodboard')) {
                row.classList.add('row-in-survey');
                var title =
                  data.status_raw === 'in_survei'        ? 'Nonaktif (sedang proses survei)' :
                  data.status_raw === 'cancel_survei'    ? 'Nonaktif (cancel di survei)' :
                                                            'Nonaktif (tahap Denah & Moodboard)';
                $(row).find('a.btn-warning, button.btn-danger').attr({
                  'disabled': true, 'aria-disabled': 'true', 'tabindex': '-1', 'title': title
                }).addClass('disabled');
              }
            },
            drawCallback: function () {
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });

          var $s1 = $('#table-klien_filter input');
          if ($s1.length) {
            $s1.off().on('input', debounce(function () {
              dtAktif.search(this.value).draw();
            }, 250));
          }
          $(document).on('change', '#status_filter,#tanggal_awal,#tanggal_akhir', function () {
            dtAktif.ajax.reload();
          });
          $('#reset-filter').on('click', function () {
            $('#tanggal_awal, #tanggal_akhir').val('');
            $('#status_filter').val('');
            dtAktif.ajax.reload();
          });

          $('#btnCompact').on('click', function(){ $tblAktif.removeClass('table-comfortable').addClass('table-compact'); });
          $('#btnComfort').on('click', function(){ $tblAktif.removeClass('table-compact').addClass('table-comfortable'); });

          function applyStatusChipAktif() {
            renderChip('chipContainer', 'Status', $('#status_filter').val() || '', function(){
              $('#status_filter').val('');
              dtAktif.ajax.reload();
            });
          }
          $('#status_filter').on('change', applyStatusChipAktif);
          applyStatusChipAktif();
        }

        // ===== 2) KLIEN CANCEL
        var $tblCancel = $('#table-klien-cancel');
        if ($tblCancel.length && !$.fn.dataTable.isDataTable($tblCancel)) {
          var dtCancel = $tblCancel.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
              url: $tblCancel.data('url'),
              data: function (d) {
                d.tanggal_awal_cancel  = $('#tanggal_awal_cancel').val();
                d.tanggal_akhir_cancel = $('#tanggal_akhir_cancel').val();
              }
            },
            order: [[2,'desc']],
            columns: [
              { data: 'DT_RowIndex',      orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',             name:'nama' },
              { data: 'tanggal_cancel',   name:'tanggal_cancel', orderable:true, searchable:false, className:'dt-date' },
              { data: 'lokasi_lahan',     name:'lokasi_lahan' },
              { data: 'budget_fmt',       name:'budget_fmt', searchable:false },
              { data: 'kelas',            name:'kelas' },
              { data: 'kode_proyek',      name:'kode_proyek' },
              { data: 'keterangan_badge', orderable:false, searchable:true },
              { data: 'aksi',             orderable:false, searchable:false, className:'text-center' }
            ],
            drawCallback: function () {
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });

          var $s2 = $('#table-klien-cancel_filter input');
          if ($s2.length) {
            $s2.off().on('input', debounce(function () {
              dtCancel.search(this.value).draw();
            }, 250));
          }
          $(document).on('change', '#tanggal_awal_cancel,#tanggal_akhir_cancel', function () {
            dtCancel.ajax.reload();
          });
          $('#reset-filter-cancel').on('click', function () {
            $('#tanggal_awal_cancel, #tanggal_akhir_cancel').val('');
            dtCancel.ajax.reload();
          });
        }

        // ===== 3) Survei Inbox
        var $inbox = $('#tbl-inbox');
        if ($inbox.length && !$.fn.dataTable.isDataTable($inbox)) {
          $inbox.DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: $inbox.data('url') },
            order: [[3, 'desc']],
            columns: [
              { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',        name:'nama' },
              { data: 'kode',        name:'kode', orderable:false, searchable:false },
              { data: 'tgl_masuk',   name:'tgl_masuk' },
              { data: 'status',      name:'status', orderable:false, searchable:false },
              { data: 'aksi',        name:'aksi', orderable:false, searchable:false }
            ],
            drawCallback: function () {
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });

          if (window.Swal) {
            $inbox.on('click', 'form[action*="/approve"] button', function (e) {
              if (this.disabled) return;
              e.preventDefault();
              var form = this.closest('form');
              Swal.fire({
                icon: 'question',
                title: 'Setujui permintaan survei?',
                text: 'Status akan berubah menjadi Accepted.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true
              }).then(function (res) { if (res.isConfirmed) form.submit(); });
            });

            $inbox.on('click', 'form[action*="/reject"] button', function (e) {
              if (this.disabled) return;
              e.preventDefault();
              var form = this.closest('form');
              Swal.fire({
                icon: 'warning',
                title: 'Tolak permintaan survei?',
                text: 'Status akan berubah menjadi Rejected.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                reverseButtons: true
              }).then(function (res) { if (res.isConfirmed) form.submit(); });
            });
          }
        }

        // ===== 4) Survei Scheduled
        var $sched = $('#tbl-scheduled');
        if ($sched.length && !$.fn.dataTable.isDataTable($sched)) {
          $sched.DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: $sched.data('url') },
            order: [[4, 'desc']],
            columns: [
              { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',    name:'nama' },
              { data: 'kode',    name:'kode' },
              { data: 'lokasi',  name:'lokasi' },
              { data: 'tgl_jadwal', name:'tgl_jadwal' },
              { data: 'status',      name:'status', orderable:false, searchable:false },
              { data: 'aksi', orderable:false, searchable:false, className:'text-center' }
            ],
            drawCallback: function () {
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });
        }

        // ===== 5) Denah & Cancel Denah
        var $tblDenah = $('#tbl-denah');
        if ($tblDenah.length && !$.fn.dataTable.isDataTable($tblDenah)) {
          $tblDenah.DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: $tblDenah.data('url') },
            order: [[4, 'desc']],
            columns: [
              { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',        name:'nama' },
              { data: 'kode',        name:'kode_proyek' },
              { data: 'lokasi',      name:'lokasi_lahan' },
              { data: 'created_fmt', name:'created_fmt', className:'dt-date' },
              { data: 'aksi',        orderable:false, searchable:false, className:'text-center' }
            ],
            drawCallback: function(){
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });
        }

        var $tblDenahCancel = $('#tbl-denah-cancel');
        if ($tblDenahCancel.length && !$.fn.dataTable.isDataTable($tblDenahCancel)) {
          $tblDenahCancel.DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: $tblDenahCancel.data('url') },
            order: [[5, 'desc']],
            columns: [
              { data: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
              { data: 'nama',        name:'nama' },
              { data: 'kode',        name:'kode_proyek' },
              { data: 'lokasi',      name:'lokasi_lahan' },
              { data: 'alasan',      name:'alasan_cancel' },
              { data: 'canceled_fmt',name:'canceled_fmt', className:'dt-date' },
              { data: 'aksi',        orderable:false, searchable:false, className:'text-center' }
            ],
            drawCallback: function(){
              var ctn = this.api().table().container();
              renderFeatherIcons(ctn);
              initTooltips(ctn);
            }
          });
        }
      });
    }

    // ====================
    // Show loading on non-AJAX submits & internal links
    // ====================
    document.querySelectorAll('a').forEach(function (a) {
      try {
        a.addEventListener('click', function (e) {
          var href = a.getAttribute('href') || '';
          var isInternal = a.href && a.href.indexOf(location.origin) === 0;

          var isToggle =
            a.hasAttribute('data-bs-toggle') ||
            a.hasAttribute('data-toggle') ||
            a.matches('.has-sub > a') ||
            href === '#' ||
            href.toLowerCase().startsWith('javascript');

          var isNewTab = a.target === '_blank' || e.button === 1 || e.ctrlKey || e.metaKey;

          var isFile =
            a.hasAttribute('download') ||
            /^\/?storage\//i.test(href) ||
            /\/storage\//i.test(a.pathname || '');

          var optedOut = a.hasAttribute('data-no-loading');

          if (isInternal && !isToggle && !optedOut && !isNewTab && !isFile) {
            showLoading();
          }
        });
      } catch (e) { /* ignore */ }
    });

    // ====================
    // Modal helper for reset password
    // ====================
    window.openResetModal = function (id, name) {
      var input = document.getElementById('resetUserId');
      var label = document.getElementById('resetUserName');
      if (input) input.value = id;
      if (label) label.textContent = '(' + (name || '') + ')';
      var modalEl = document.getElementById('resetPasswordModal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        var bs = bootstrap.Modal.getOrCreateInstance(modalEl);
        bs.show();
      }
    };

    // ====================
    // Feather icons (initial + observe)
    // ====================
    renderFeatherIcons(document);
    try {
      var mo = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
          if (m.addedNodes && m.addedNodes.length) {
            m.addedNodes.forEach(function (n) {
              if (n.nodeType === 1 && (n.hasAttribute('data-feather') || (n.querySelector && n.querySelector('[data-feather]')))) {
                renderFeatherIcons(n);
              }
            });
          }
        });
      });
      mo.observe(document.body, { childList: true, subtree: true });
    } catch (e) { /* ignore */ }

    // ===== Sidebar submenu toggle (robust) =====
    (function () {
      var toggles = document.querySelectorAll(
        '#sidebar .sidebar-item.has-sub > a, #sidebar .sidebar-item.has-sub > .sidebar-link'
      );

      function openItem(li) {
        li.classList.add('active');
        var sm = li.querySelector(':scope > .submenu');
        if (sm) {
          sm.style.display = 'block';
          sm.style.maxHeight = sm.scrollHeight + 'px';
        }
      }
      function closeItem(li) {
        li.classList.remove('active');
        var sm = li.querySelector(':scope > .submenu');
        if (sm) {
          sm.style.maxHeight = null;
          setTimeout(function () { sm.style.display = ''; }, 250);
        }
      }

      document.querySelectorAll('#sidebar .sidebar-item.has-sub.active').forEach(openItem);

      toggles.forEach(function (a) {
        a.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

          var li = this.closest('.sidebar-item.has-sub');
          if (!li) return;

          var isOpen = li.classList.contains('active');

          document.querySelectorAll('#sidebar .sidebar-item.has-sub.active').forEach(function (other) {
            if (other !== li) closeItem(other);
          });

          if (isOpen) closeItem(li); else openItem(li);
        });
      });
    })();

    // ===== Flatpickr init =====
    (function(){
      if (typeof flatpickr === 'undefined') return;

      const baseOpts = {
        dateFormat: "Y-m-d",
        allowInput: true,
        disableMobile: true,
        locale: flatpickr.l10ns.id,
      };

      if (document.getElementById('tanggal_awal'))  flatpickr('#tanggal_awal',  baseOpts);
      if (document.getElementById('tanggal_akhir')) flatpickr('#tanggal_akhir', baseOpts);
      if (document.getElementById('tanggal_awal_cancel'))  flatpickr('#tanggal_awal_cancel',  baseOpts);
      if (document.getElementById('tanggal_akhir_cancel')) flatpickr('#tanggal_akhir_cancel', baseOpts);

      if (document.getElementById('tgl_jadwal')) {
        flatpickr('#tgl_jadwal', {
          ...baseOpts,
          enableTime: true,
          time_24hr: true,
          dateFormat: "Y-m-d H:i",
        });
      }
    })();

/* ===== Mobile sidebar toggle (hamburger) — robust ===== */
(function () {
  function openSidebar()  {
    document.body.classList.add('sidebar-open');
    var sb = document.getElementById('sidebar');
    if (sb) sb.classList.add('active');            // sinkron dg CSS
    var btn = document.getElementById('mobileSidebarBtn') || document.querySelector('.burger-btn');
    if (btn) btn.setAttribute('aria-expanded', 'true');
  }
  function closeSidebar() {
    document.body.classList.remove('sidebar-open');
    var sb = document.getElementById('sidebar');
    if (sb) sb.classList.remove('active');
    var btn = document.getElementById('mobileSidebarBtn') || document.querySelector('.burger-btn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
  }
  function toggleSidebar(){
    if (document.body.classList.contains('sidebar-open')) closeSidebar();
    else openSidebar();
  }

  // buat backdrop kalau belum ada
  var backdrop = document.getElementById('sidebar-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.id = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  // Delegasi klik: tombol hamburger & backdrop
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#mobileSidebarBtn, .burger-btn');
    if (btn) { e.preventDefault(); toggleSidebar(); return; }

    if (e.target.closest('#sidebar-backdrop')) { closeSidebar(); return; }

    // Tutup saat klik link di dalam sidebar (khusus mobile)
    var link = e.target.closest('#sidebar a[href]');
    if (link && window.innerWidth < 1200) closeSidebar();
  });

  // ESC untuk tutup
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
  });

  // Jika resize ke desktop, pastikan tertutup
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 1200) closeSidebar();
  });
})();

let _ctrl;
async function reloadStats(q) {
  if (_ctrl) _ctrl.abort();
  _ctrl = new AbortController();

  const params = new URLSearchParams(q||{range:'30d'});
  const res = await fetch(`{{ route('project.dashboard.stats') }}?`+params, {
    headers:{'X-Requested-With':'XMLHttpRequest'},
    signal: _ctrl.signal
  });
  const js = await res.json();
  // ... lanjut update UI
}




    // ensure overlay hidden on load
    hideLoading();
  });
})();
