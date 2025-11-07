{{-- ========= Header with Notifications + Chat ========= --}}
@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$user  = Auth::user();
$name  = $user->name  ?? 'User';
$email = $user->email ?? null;
$role  = strtolower($user->role ?? 'default');

/** ===== Avatar priority:
 *  1) Random file di /public/mazer/assets/images/faces/{role}
 *  2) Gravatar by email (kalau ada email)
 *  3) UI Avatars fallback
 */
$avatar = null;
$dir = public_path("mazer/assets/images/faces/{$role}");
if (File::exists($dir)) {
    $files = collect(File::files($dir))->filter(fn($f) => $f->isFile())->values();
    if ($files->count()) {
        $avatar = asset("mazer/assets/images/faces/{$role}/".$files->random()->getFilename());
    }
}
if (!$avatar && $email) {
    $hash   = md5(strtolower(trim($email)));
    $avatar = "https://www.gravatar.com/avatar/{$hash}?s=80&d=identicon";
}
if (!$avatar) {
    $avatar = 'https://ui-avatars.com/api/?name='.urlencode($name).'&size=80&background=0D8ABC&color=fff';
}

/** ===== Link "My Profile" (fallback aman) */
$profileRoute = Route::has('profile.edit') ? route('profile.edit') : url('/profile');

/** ===== Daftar target role utk chat (exclude role sendiri) */
$allRoles      = ['admin','marketing','studio','project','ceo'];
$otherRoles    = array_values(array_filter($allRoles, fn($r) => $r !== $role));
$selectTargets = array_merge(['all'], $otherRoles);

/** ===== Label ramah */
$roleLabels = [
    'all'       => 'All',
    'admin'     => 'Admin',
    'marketing' => 'Marketing',
    'studio'    => 'Studio',
    'project'   => 'Project',
    'ceo'       => 'CEO',
];
@endphp


<style>
  .custom-header{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;background:transparent}
  .custom-header .burger-btn{font-size:1.6rem;color:var(--bs-body-color)}

  /* ========== User pill ========== */
  .user-pill{display:flex;align-items:center;gap:.75rem;padding:.35rem .6rem .35rem .75rem;border-radius:9999px;border:1px solid var(--bs-border-color,#2f3246);background:transparent}
  .user-pill:hover{background:transparent!important;box-shadow:none!important}
  .user-meta{display:flex;flex-direction:column;line-height:1.05;min-width:0}
  .user-meta .name{font-weight:700;color:var(--bs-body-color)}
  .user-meta .email{font-size:.8rem;color:var(--bs-secondary-color,#6b7280)}
  .user-meta .name,.user-meta .email{max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .user-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(79,175,255,.35)}

  /* ========== Dropdowns ========== */
  .dropdown-menu.custom-user-dropdown,
  .dropdown-menu.notif-menu,
  .dropdown-menu.chat-menu{
    margin-top:.5rem;min-width:260px;border-radius:16px;padding:.75rem;
    border:1px solid var(--bs-border-color,#2f3246);
    box-shadow:0 12px 30px -12px rgba(0,0,0,.45);
    background-color:var(--bs-dropdown-bg);color:var(--bs-body-color);
    z-index:1085;
  }
  .menu-avatar{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(79,175,255,.35)}
  .header-actions a{color:var(--bs-secondary-color,#6b7280);text-decoration:none}
  .header-actions a:hover{color:var(--bs-body-color)}

  /* ========== Notif list ========== */
  #notifList .notif-item{padding:.65rem .9rem;border-bottom:1px solid var(--bs-border-color,#2f3246)}
  #notifList .notif-item:last-child{border-bottom:none}
  #notifList .notif-title{font-size:.915rem}
  #notifList .notif-time{font-size:.78rem;color:var(--bs-secondary-color,#6b7280)}
  /* Notif baru */
  #notifList .notif-item.is-new{border-left:3px solid var(--bs-primary,#6366f1);border-top-left-radius:10px;border-bottom-left-radius:10px}
  html[data-bs-theme="light"] #notifList .notif-item.is-new{background:rgba(0,0,0,.04)}
  html[data-bs-theme="dark"]  #notifList .notif-item.is-new{background:rgba(255,255,255,.06)}

  /* ========== Badges kecil ========== */
  #notifBadge{position:absolute;top:-6px;right:-10px;font-size:.65rem;line-height:1;padding:.15rem .35rem;display:none}
  .chat-badge{position:absolute;top:-6px;right:-10px;background:#dc3545;color:#fff;font-size:.65rem;line-height:1;border-radius:999px;padding:.2rem .38rem;display:none}

  /* ========== Chat dropdown ========== */
  .chat-menu{min-width:360px;border-radius:16px}
  #chatList{max-height:360px;overflow:auto}
  .msg{padding:.5rem .75rem;margin:.25rem .5rem;border-radius:12px;max-width:80%}
  .msg.me{margin-left:auto;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.35)}
  .msg.other{background:rgba(0,0,0,.04)}
  html[data-bs-theme="dark"] .msg.other{background:rgba(255,255,255,.06)}
  .msg .meta{font-size:.75rem;opacity:.7}

  /* ========== Chat modal (pop-up tengah) ========== */
  .modal-chat .modal-dialog{max-width:820px}
  .modal-chat .modal-body{height:60vh;display:flex;flex-direction:column}
  #chatModalList{flex:1;overflow:auto;padding:.25rem}
  .chat-msg{max-width:72%;margin:.25rem 0;padding:.5rem .7rem;border-radius:12px}
  .chat-me{align-self:flex-end;background:#2759ff1a}
  .chat-other{align-self:flex-start;background:#ffffff10}
  html[data-bs-theme="light"] .chat-other{background:rgba(0,0,0,.05)}

  /* Samakan ukuran & posisi badge chat dengan notifikasi */
#notifBadge,
#chatBadge{
  position: absolute;
  top: -6px;
  right: -10px;
  transform: none !important;       /* override aturan lama */
  font-size: .65rem;                 /* sama persis */
  line-height: 1;
  padding: .15rem .35rem;
  min-width: auto;
  border-radius: 9999px;
  display: none;                     /* ditampilkan via JS */
}

/* (opsional) kalau kamu masih punya .chat-badge lama, biar pasti ketimpa: */
.chat-badge{
  top: -6px !important;
  right: -10px !important;
  transform: none !important;
  font-size: .65rem !important;
  padding: .15rem .35rem !important;
}
  /* Pastikan menu benar-benar di atas segalanya */
  .dropdown-menu.custom-user-dropdown,
  .dropdown-menu.notif-menu,
  .dropdown-menu.chat-menu{
    background-color: var(--bs-dropdown-bg, #fff) !important;
    z-index: 2050;
    will-change: top,left;
  }
  html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown,
  html[data-bs-theme="dark"] .dropdown-menu.notif-menu,
  html[data-bs-theme="dark"] .dropdown-menu.chat-menu{
    background-color: #2c2e3e !important;
  }

  /* Backdrop transparan untuk menutup dropdown saat klik di luar */
  .portal-backdrop{
    position: fixed; inset: 0; background: transparent;
    z-index: 2147482000; /* di bawah menu, di atas konten lain */
  }
  /* ==== pastikan header & dropdown user di atas semua konten ==== */
.header-layer{ position:relative; z-index: 60000; }            /* header keseluruhan */
.userbox{ position:relative; z-index: 70000; }                 /* <li> trigger user */
.dropdown-menu.custom-user-dropdown{
  position: absolute;                                          /* tetap di bawah trigger */
  z-index: 80000 !important;                                   /* lebih tinggi dari filter */
}
/* ===== Avatar & header di atas bubble ===== */
.msg .head{
  display:flex; align-items:center; gap:.4rem;
  font-size:.75rem; opacity:.8;
  margin: .25rem .5rem .25rem .5rem;
}
.msg .head .who{ font-weight:600 }
.msg .head .avatar{
  width:22px; height:22px; border-radius:50%; object-fit:cover;
  border:1px solid rgba(255,255,255,.25);
}
.msg.me   .head{ justify-content:flex-end }
.msg.other .head{ justify-content:flex-start }

/* (opsional) pastikan tombol filter tidak menaikkan z-index */
.page-content .dropdown .dropdown-toggle{ position:relative; z-index: 1; }
</style>

@php
  // fallback kalau $selectTargets tidak dikirim dari controller
  $selectTargets = $selectTargets ?? ['all','admin','marketing','studio','project','ceo'];
@endphp

<header class="custom-header mb-3">
  {{-- Burger (hanya < xl) --}}
  <button id="mobileSidebarBtn" class="btn btn-link d-xl-none p-0 burger-btn" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <div></div> {{-- kiri sengaja kosong --}}

  <div class="header-actions d-flex align-items-center gap-3 ms-3">
    {{-- ========= CHAT: icon + dropdown ========= --}}
    <div class="dropdown">
      <a id="chatDropdown" class="position-relative" href="#" role="button"
         data-bs-toggle="dropdown" data-bs-offset="0,8"
         data-bs-auto-close="outside">
        <i class="bi bi-chat-dots" style="font-size:1.22rem"></i>
        <span id="chatBadge" class="badge chat-badge" style="display:none">0</span>
      </a>

      <div class="dropdown-menu dropdown-menu-end p-0 chat-menu" aria-labelledby="chatDropdown" style="width:360px;max-height:70vh;overflow:auto">
        <div class="p-3 border-bottom d-flex align-items-center gap-2">
          <div class="fw-semibold">Chat</div>
          <div class="ms-auto d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Ke:</label>
            <select id="chatRole" class="form-select form-select-sm" style="width:160px">
            @foreach($selectTargets as $r)
            <option value="{{ $r }}">{{ $roleLabels[$r] ?? ucfirst($r) }}</option>
            @endforeach
            </select>
            <button type="button" id="chatPopBtn" class="btn btn-sm btn-outline-secondary" title="Buka jendela">
              <i class="bi bi-arrows-fullscreen"></i>
            </button>
          </div>
        </div>

        <div id="chatList" class="p-2">
          <div class="p-3 text-muted">Memuat…</div>
        </div>

        <form id="chatForm" class="p-2 border-top d-flex align-items-center gap-2">
          @csrf
          <input id="chatInput" class="form-control" autocomplete="off" placeholder="Tulis pesan…">
          <button class="btn btn-primary" id="chatSend" type="submit">
            <i class="bi bi-send"></i>
          </button>
        </form>
      </div>
    </div>

    {{-- Ikon opsional --}}


{{-- ========= NOTIF: icon + dropdown (FEED ONLY) ========= --}}
<div class="dropdown">
  <a id="notifDropdown" class="position-relative" href="#" role="button"
     data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,8">
    <i class="bi bi-bell" style="font-size:1.25rem"></i>
    <span id="notifBadge" class="badge bg-danger rounded-pill d-none"
          style="position:absolute; top:-6px; right:-6px;">0</span>
  </a>

  <div class="dropdown-menu dropdown-menu-end notif-menu p-0" style="width:360px;">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Notifikasi</div>
      <button id="markReadBtn" class="btn btn-sm btn-outline-secondary">Tandai dibaca</button>
    </div>
    <div id="notifList" style="max-height:400px; overflow:auto">
      <div class="p-3 text-muted">Memuat…</div>
    </div>
  </div>
</div>


    {{-- ========= User pill ========= --}}

    <li class="nav-item dropdown list-unstyled">
      <a href="#" id="userDropdown" class="nav-link p-0" data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
        <div class="user-pill d-flex align-items-center gap-2">
          <div class="user-meta text-end">
            <span class="name d-block fw-semibold text-truncate" style="max-width:180px">{{ $user->name }}</span>
            <span class="email small text-muted d-block text-truncate" style="max-width:180px">{{ $user->email }}</span>
          </div>
          <img src="{{ $avatar }}" alt="Avatar" class="user-avatar rounded-circle" style="width:36px;height:36px;object-fit:cover">
        </div>
      </a>

      <ul class="dropdown-menu dropdown-menu-end custom-user-dropdown">
        <li class="d-flex align-items-center gap-3 px-3 pt-2 pb-2">
          <img src="{{ $avatar }}" class="rounded-circle" alt="Avatar" style="width:40px;height:40px;object-fit:cover">
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $user->name }}</div>
            <div class="text-muted small text-truncate">{{ $user->email }}</div>
          </div>
        </li>
        <li><hr class="dropdown-divider my-2"></li>
        <li><a class="dropdown-item py-2" href="{{ $profileRoute }}">My Profile</a></li>
        <li>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="dropdown-item py-2 text-danger bg-transparent border-0 w-100" type="submit">Logout</button>
          </form>
        </li>
      </ul>
    </li>
  </div>
</header>

{{-- ========= Chat Modal (popup penuh) ========= --}}
<div class="modal fade modal-chat" id="chatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="fw-semibold">Chat Antar Role</div>
        <div class="ms-auto d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Ke:</label>
          <select id="chatModalRole" class="form-select form-select-sm" style="width:160px">
            @foreach($selectTargets as $r)
            <option value="{{ $r }}">{{ $roleLabels[$r] ?? ucfirst($r) }}</option>
            @endforeach
          </select>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="chatModalList" class="mb-2"><div class="p-3 text-muted">Memuat…</div></div>
        <form id="chatModalForm" class="d-flex align-items-center gap-2">
          @csrf
          <input id="chatModalInput" class="form-control" autocomplete="off" placeholder="Tulis pesan…">
          <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  /* =========================
     Helper umum
  ========================= */
  const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const USER_ID = document.body?.dataset?.userId || @json(auth()->id());                     // angka atau null
  const MY_ROLE = @json(strtolower(optional(auth()->user())->role));                          // string atau null

  // Mapping label role (hard-coded supaya Blade tidak bingung)
  const ROLE_LABELS = {
    "all":"All","admin":"Admin","marketing":"Marketing","studio":"Studio","project":"Project","ceo":"CEO"
  };

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const cap = s => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');

  /* =========================
     NOTIFIKASI (Feed Only)
  ========================= */
  const FEED_URL = @json(route('notifications.feed'));
  const SEEN_URL = @json(route('notifications.seen'));

  const notifBadge    = document.getElementById('notifBadge');
  const notifList     = document.getElementById('notifList');
  const markBtn       = document.getElementById('markReadBtn');
  const notifDropdown = document.getElementById('notifDropdown');

  function setNotifBadge(n){
    n = +n || 0;
    if (!notifBadge) return;
    notifBadge.textContent = n;
    notifBadge.classList.toggle('d-none', n === 0);
    notifBadge.style.display = n > 0 ? 'inline-block' : 'none';
  }

  let feedAborter = null;
  async function loadFeed(){
    if (!notifList) return;
    try{
      feedAborter?.abort(); feedAborter = new AbortController();
      const res = await fetch(FEED_URL + '?ts=' + Date.now(), {
        method:'GET', credentials:'same-origin',
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        cache:'no-store', signal: feedAborter.signal
      });
      if (!res.ok){
        notifList.innerHTML = `<div class="p-3 text-danger">Gagal memuat (status ${res.status}).</div>`;
        return;
      }
      const data = await res.json();
      setNotifBadge(data.unread || 0);

      const items = data.items || [];
      notifList.innerHTML = items.length
        ? items.map(it => `
            <div class="notif-item ${it.is_new ? 'is-new' : ''} p-3">
              <div class="notif-title">${String(it.message ?? '')}</div>
              <div class="notif-time text-muted small">${esc(it.time_human ?? '')}</div>
            </div>`).join('')
        : `<div class="p-3 text-muted">Belum ada notifikasi.</div>`;
    }catch(e){
      if (e.name !== 'AbortError'){
        console.error(e);
        notifList.innerHTML = `<div class="p-3 text-danger">Kesalahan jaringan.</div>`;
      }
    }
  }

  async function markSeen(){
    try{
      await fetch(SEEN_URL, {
        method:'POST', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}
      });
      setNotifBadge(0);
      await loadFeed();
    }catch(e){ console.error(e); }
  }

  // Init & polling
  loadFeed();
  setInterval(loadFeed, 10000);
  notifDropdown?.addEventListener('shown.bs.dropdown', loadFeed);
  window.addEventListener('focus', loadFeed);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) loadFeed(); });
  markBtn?.addEventListener('click', markSeen);

  /* =========================
     CHAT antar role (opsional)
  ========================= */
  const CHAT_FETCH_BASE = @json(route('chat.fetch'));
  const CHAT_SEND_URL   = @json(route('chat.send'));
  const CHAT_UNREAD_URL = @json(route('chat.unread'));
  const CHAT_MARK_URL   = @json(route('chat.markSeen'));

  const chatBadge      = document.getElementById('chatBadge');
  const chatRoleSel    = document.getElementById('chatRole');
  const chatList       = document.getElementById('chatList');
  const chatForm       = document.getElementById('chatForm');
  const chatInput      = document.getElementById('chatInput');
  const chatPopBtn     = document.getElementById('chatPopBtn');
  const chatDropdown   = document.getElementById('chatDropdown');

  // Modal
  const chatModalEl    = document.getElementById('chatModal');
  const chatModalList  = document.getElementById('chatModalList');
  const chatModalRole  = document.getElementById('chatModalRole');
  const chatModalForm  = document.getElementById('chatModalForm');
  const chatModalInput = document.getElementById('chatModalInput');
  let chatModal = null;
  try { if (chatModalEl && window.bootstrap?.Modal) chatModal = new bootstrap.Modal(chatModalEl, {backdrop:'static', keyboard:false}); } catch{}

  const setChatBadge = (n) => {
    if (!chatBadge) return;
    n = +n || 0;
    chatBadge.textContent = n;
    chatBadge.style.display = n > 0 ? 'inline-block' : 'none';
  };

  const avatarFor = (role) => `https://ui-avatars.com/api/?name=${encodeURIComponent(cap(role||'?'))}&background=4F46E5&color=fff&bold=true&size=64`;

  function applyRoleDots(selectEl, byRole = {}, broadcast = 0){
    if (!selectEl) return;
    [...selectEl.options].forEach(opt => {
      const r = String(opt.value || '');
      const base = ROLE_LABELS[r] || cap(r);
      const on  = (r === 'all') ? (broadcast > 0) : ((byRole[r] || 0) > 0);
      const desired = (on ? '🟢 ' : '') + base;
      if (opt.textContent !== desired) opt.textContent = desired;
    });
  }

  const hostFor = (target) => target === 'modal' ? chatModalList : chatList;
  const msgHTML = (m, targetRole) => {
    const role = m.me ? (MY_ROLE||'me') : (String(m.role || targetRole || '').toLowerCase() || 'other');
    const who  = role === 'all' ? 'All' : cap(role);
    const ava  = avatarFor(role);
    const body = esc(m.body ?? '');
    const time = esc(m.time ?? 'baru saja');
    return `
      <div class="msg ${m.me ? 'me' : 'other'}">
        <div class="head" style="display:flex;align-items:center;gap:.4rem;font-size:.75rem;opacity:.8;margin:.25rem .5rem;">
          <img class="avatar" src="${ava}" alt="${who}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.25)">
          <span class="who" style="font-weight:600">${who}</span>
        </div>
        <div class="body">${body}</div>
        <div class="meta" style="font-size:.75rem;opacity:.7">${time}</div>
      </div>`;
  };

  function renderChat(items, target='dropdown'){
    const host = hostFor(target);
    if (!host) return;
    if (!items.length){
      host.innerHTML = `<div class="p-3 text-muted">Belum ada pesan.</div>`;
      return;
    }
    const targetRole = (target === 'modal') ? (chatModalRole?.value || 'all') : (chatRoleSel?.value || 'all');
    host.innerHTML = items.map(m => msgHTML(m, targetRole)).join('');
    host.scrollTop = host.scrollHeight;
  }

  async function loadChat(role, target='dropdown'){
    const host = hostFor(target); if (!host) return;
    host.innerHTML = `<div class="p-3 text-muted">Memuat…</div>`;
    try{
      const res = await fetch(CHAT_FETCH_BASE + '?role=' + encodeURIComponent(role) + '&ts=' + Date.now(), {
        method:'GET', credentials:'same-origin',
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      if (!res.ok){
        host.innerHTML = `<div class="p-3 text-danger">Gagal memuat (status ${res.status}).</div>`;
        return;
      }
      const data = await res.json();
      renderChat(data.items || [], target);
      if ('unread_total' in data) setChatBadge(data.unread_total || 0);
    }catch(e){
      console.error(e);
      host.innerHTML = `<div class="p-3 text-danger">Kesalahan jaringan.</div>`;
    }
  }

  async function sendChat(role, text, target='dropdown'){
    const host = hostFor(target); if (!host) return;
    text = (text || '').trim(); if (!text) return;
    try{
      const res = await fetch(CHAT_SEND_URL, {
        method:'POST', credentials:'same-origin',
        headers:{
          'Accept':'application/json','Content-Type':'application/json',
          'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'
        },
        body: JSON.stringify({role, body: text})
      });
      if (!res.ok){
        host.insertAdjacentHTML('beforeend', `<div class="p-2 text-danger small">Gagal mengirim.</div>`);
        return;
      }
      host.insertAdjacentHTML('beforeend', msgHTML({me:true, body:text, time:'baru saja'}, role));
      host.scrollTop = host.scrollHeight;
      (target==='modal' ? chatModalInput : chatInput).value = '';
    }catch(e){ console.error(e); }
  }

  async function refreshUnreadChat(){
    try{
      const r = await fetch(CHAT_UNREAD_URL, {
        credentials:'same-origin',
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      if (!r.ok) return;
      const d = await r.json();
      setChatBadge(d.count || 0);
      applyRoleDots(chatRoleSel,   d.by_role || {}, d.broadcast || 0);
      applyRoleDots(chatModalRole, d.by_role || {}, d.broadcast || 0);
    }catch{}
  }

  async function markChatSeen(role){
    try{
      await fetch(CHAT_MARK_URL, {
        method:'POST', credentials:'same-origin',
        headers:{
          'Accept':'application/json','Content-Type':'application/json',
          'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'
        },
        body: JSON.stringify({role})
      });
    }catch{}
  }

  // Event chat
  chatDropdown?.addEventListener('shown.bs.dropdown', () => {
    const r = chatRoleSel?.value || 'all';
    loadChat(r,'dropdown'); markChatSeen(r).then(refreshUnreadChat);
  });
  chatRoleSel?.addEventListener('change', () => {
    const r = chatRoleSel.value || 'all';
    loadChat(r,'dropdown'); markChatSeen(r).then(refreshUnreadChat);
  });
  chatInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatForm?.requestSubmit(); }
  });
  chatForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    sendChat(chatRoleSel.value || 'all', chatInput.value, 'dropdown');
  });

  // Modal chat
  chatPopBtn?.addEventListener('click', () => {
    if (!chatModal) return;
    if (chatModalRole) chatModalRole.value = chatRoleSel?.value || 'all';
    chatModal.show();
  });
  chatModalEl?.addEventListener('shown.bs.modal', () => {
    const r = chatModalRole?.value || 'all';
    loadChat(r,'modal'); markChatSeen(r).then(refreshUnreadChat); chatModalInput?.focus();
  });
  chatModalRole?.addEventListener('change', () => {
    const r = chatModalRole.value || 'all';
    loadChat(r,'modal'); markChatSeen(r).then(refreshUnreadChat);
  });
  chatModalInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatModalForm?.requestSubmit(); }
  });
  chatModalForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    sendChat(chatModalRole.value || 'all', chatModalInput.value, 'modal');
  });

  refreshUnreadChat();
  setInterval(refreshUnreadChat, 10000);

  // Realtime (opsional)
  if (window.Echo && MY_ROLE) {
    try {
      window.Echo.private(`roles.${MY_ROLE}`).listen('.chat.incoming', (e) => {
        refreshUnreadChat();
        const from = String(e?.from_role ?? '');
        const body = String(e?.body ?? '');
        const ddOpen = chatDropdown?.parentElement?.classList.contains('show');

        if (ddOpen && chatRoleSel?.value === from) {
          chatList?.insertAdjacentHTML('beforeend', msgHTML({me:false, role:from, body, time:'baru saja'}, from));
          chatList.scrollTop = chatList.scrollHeight;
        }
        if (chatModalEl?.classList.contains('show') && chatModalRole?.value === from) {
          chatModalList?.insertAdjacentHTML('beforeend', msgHTML({me:false, role:from, body, time:'baru saja'}, from));
          chatModalList.scrollTop = chatModalList.scrollHeight;
        }
      });
    } catch (err) { console.warn('Echo chat tidak aktif:', err); }
  }

  /* =========================
     Dropdown Profil di atas elemen lain
  ========================= */
  const userTrigger = document.getElementById('userDropdown');
  const userMenu    = document.querySelector('#userDropdown + ul.custom-user-dropdown');

  if (userTrigger && userMenu && window.bootstrap?.Dropdown) {
    let ph = null, backdrop = null;

    const place = () => {
      const r = userTrigger.getBoundingClientRect();
      userMenu.style.position = 'fixed';
      userMenu.style.top  = (r.bottom + 8) + 'px';
      userMenu.style.left = (r.right - userMenu.offsetWidth) + 'px';
      userMenu.style.zIndex = '2147483000';
    };

    userTrigger.addEventListener('show.bs.dropdown', () => {
      ph = document.createElement('span');
      userMenu.parentNode.insertBefore(ph, userMenu);
      document.body.appendChild(userMenu);

      backdrop = document.createElement('div');
      backdrop.style.cssText = 'position:fixed;inset:0;background:transparent;z-index:2147482999';
      backdrop.addEventListener('click', () => bootstrap.Dropdown.getOrCreateInstance(userTrigger).hide());
      document.body.appendChild(backdrop);

      requestAnimationFrame(place);
      window.addEventListener('scroll', place, true);
      window.addEventListener('resize', place, true);
    });

    userTrigger.addEventListener('hide.bs.dropdown', () => {
      window.removeEventListener('scroll', place, true);
      window.removeEventListener('resize', place, true);
      backdrop?.remove(); backdrop = null;

      if (ph) { ph.parentNode.insertBefore(userMenu, ph); ph.remove(); ph = null; }
      Object.assign(userMenu.style, {position:'', top:'', left:'', zIndex:''});
    });
  }
})();
</script>
