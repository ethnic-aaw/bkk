const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const tgl = d => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const badge = m => m === 'Sudah MOU'
  ? '<span class="rounded-full bg-daun/10 text-daun px-2.5 py-0.5 text-xs font-semibold">&#10003; Sudah MOU</span>'
  : '<span class="rounded-full bg-bata/10 text-bata px-2.5 py-0.5 text-xs font-semibold">Belum MOU</span>';
const getJobs = async p => (await fetch('api.php?a=jobs&' + new URLSearchParams(p))).json();
const debounce = (fn, ms = 250) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

const animateCount = (el, to) => {
  const from = parseInt(el.textContent) || 0;
  const dur = 800; const start = performance.now();
  const step = now => {
    const p = Math.min((now - start) / dur, 1);
    const eased = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.round(from + (to - from) * eased) + ' posisi';
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
};

const staggerFade = (container, items) => {
  container.innerHTML = '';
  items.forEach((html, i) => {
    const div = document.createElement('a');
    div.className = 'flex gap-3 items-start p-3 hover:bg-kertas rounded-lg opacity-0 translate-y-2 transition-all duration-400';
    div.style.transitionDelay = (i * 60) + 'ms';
    div.innerHTML = html;
    container.appendChild(div);
    requestAnimationFrame(() => { div.classList.remove('opacity-0', 'translate-y-2'); });
  });
};

const toast = (msg, type = 'info') => {
  const t = document.createElement('div');
  t.className = `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl text-sm font-medium shadow-xl animate-slide-up ${
    type === 'success' ? 'bg-daun text-white' : type === 'error' ? 'bg-bata text-white' : 'bg-tinta text-white'
  }`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => { t.classList.add('opacity-0', 'translate-y-2'); setTimeout(() => t.remove(), 300); }, 3000);
};

// Geser daftar lowongan berjalan otomatis, berhenti saat kursor di atasnya
const autoScrollY = (el, speed = 24) => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let paused = false, pos = 0, last = performance.now();
  el.addEventListener('mouseenter', () => paused = true);
  el.addEventListener('mouseleave', () => paused = false);
  el.addEventListener('focusin', () => paused = true);
  el.addEventListener('focusout', () => paused = false);
  const tick = t => {
    const dt = Math.min(t - last, 64); last = t;
    const max = el.scrollHeight - el.clientHeight;
    if (paused || max <= 2) { pos = el.scrollTop; requestAnimationFrame(tick); return; }
    pos += speed * dt / 1000;
    if (pos >= max) pos = 0;
    el.scrollTop = pos;
    requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
};

const style = document.createElement('style');
style.textContent = '@keyframes slide-up{from{opacity:0;transform:translateY(1rem)}to{opacity:1;transform:translateY(0)}} .animate-slide-up{animation:slide-up .3s ease-out} .reveal{opacity:0;transform:translateY(18px);transition:opacity .5s ease,transform .5s ease}.reveal.reveal-in{opacity:1;transform:none}';
document.head.appendChild(style);

$('#menuBtn')?.addEventListener('click', () => $('#menu').classList.toggle('hidden'));

// Animasi ringan saat elemen masuk viewport
const io = 'IntersectionObserver' in window ? new IntersectionObserver(es => es.forEach(en => {
  if (en.isIntersecting) { en.target.classList.add('reveal-in'); io.unobserve(en.target); }
}), { threshold: 0.12 }) : null;
$$('.reveal').forEach((el, i) => { el.style.transitionDelay = (i % 2) * 70 + 'ms'; io ? io.observe(el) : el.classList.add('reveal-in'); });

// Filter mitra berdasarkan status MOU
$$('#mitraTabs button').forEach(b => b.addEventListener('click', () => {
  $$('#mitraTabs button').forEach(x => x.classList.toggle('bg-tinta', x === b)), $$('#mitraTabs button').forEach(x => x.classList.toggle('text-white', x === b));
  $$('#mitra article').forEach(a => a.classList.toggle('hidden', b.dataset.f && a.dataset.mou !== b.dataset.f));
}));

// Pencarian langsung di hero
if ($('#heroJobs')) {
  let lastIds = [];
  const run = async () => {
    const j = await getJobs({ q: $('#hq').value, bidang: $('#hb').value });
    $('#heroSkeleton')?.remove();
    animateCount($('#heroCount'), j.length);
    const items = j.length ? j.map(x => `<a href="lowongan.php?id=${x.id}" class="flex gap-3 items-start p-3 hover:bg-kertas rounded-lg">
      <span class="mt-1.5 h-2.5 w-2.5 rounded-full shrink-0 ${x.mou === 'Sudah MOU' ? 'bg-daun' : 'bg-bata'}"></span>
      <span class="min-w-0"><b class="block truncate">${esc(x.judul)}</b><span class="text-sm text-tinta/65">${esc(x.mitra)}, ${esc(x.lokasi)}</span></span>
      <span class="ml-auto text-xs bg-kunyit/30 rounded-full px-2 py-0.5 whitespace-nowrap">${esc(x.tipe)}</span></a>`)
      : ['<p class="p-4 text-sm text-tinta/60">Belum ada lowongan yang cocok. Coba kata kunci atau bidang lain.</p>'];
    staggerFade($('#heroJobs'), items);

    // Deteksi lowongan baru
    const currIds = j.map(x => x.id);
    if (lastIds.length && currIds.some(id => !lastIds.includes(id))) {
      toast('Lowongan baru tersedia!', 'success');
    }
    lastIds = currIds;
  };
  $('#hq').addEventListener('input', debounce(run)); $('#hb').addEventListener('change', run);
  $$('[data-chip]').forEach(c => c.addEventListener('click', () => { $('#hb').value = c.dataset.chip; run(); }));
  run();
  autoScrollY($('#heroJobs'));

  // Auto-refresh tiap 60 detik
  setInterval(() => { if (!document.hidden) run(); }, 60000);
}

// Halaman lowongan: daftar, detail, dan form lamaran
if ($('#jobList')) {
  let jobs = [];
  const card = j => `<article class="bg-white rounded-2xl p-5 border border-tinta/10 flex flex-col">
    <div class="flex justify-between gap-2"><span class="text-xs font-semibold bg-kunyit/30 rounded-full px-2.5 py-0.5">${esc(j.tipe)}</span>${badge(j.mou)}</div>
    <h3 class="font-judul font-bold text-xl mt-3">${esc(j.judul)}</h3>
    <p class="text-sm text-tinta/70">${esc(j.mitra)}, ${esc(j.lokasi)} (bidang ${esc(j.bidang)})</p>
    <p class="text-sm mt-2">${esc(j.gaji)}</p><p class="text-xs text-tinta/60">Tutup ${tgl(j.deadline)}</p>
    <button data-id="${j.id}" class="mt-4 self-start rounded-lg bg-tinta text-white px-4 py-2 text-sm font-semibold">Lihat detail dan lamar</button></article>`;
  const load = async () => {
    jobs = await getJobs(Object.fromEntries(new FormData($('#flt'))));
    $('#jobCount').textContent = jobs.length + ' lowongan ditemukan';
    $('#jobList').innerHTML = jobs.length ? jobs.map(card).join('') : '<p class="md:col-span-2 bg-white rounded-2xl p-8 text-center text-tinta/70">Tidak ada lowongan yang cocok. Longgarkan filter di atas.</p>';
  };
  const open = id => {
    const j = jobs.find(x => x.id == id); if (!j) return;
    $('#dlgBody').innerHTML = `<div class="flex justify-between items-start gap-3"><div><h2 class="font-judul font-bold text-2xl">${esc(j.judul)}</h2><p class="text-tinta/70">${esc(j.mitra)}, ${esc(j.lokasi)}</p></div><button id="x" class="text-2xl leading-none" aria-label="Tutup">&times;</button></div>
      <div class="mt-3 flex flex-wrap gap-2 text-xs">${badge(j.mou)}<span class="bg-kunyit/30 rounded-full px-2.5 py-0.5 font-semibold">${esc(j.tipe)}</span><span class="bg-tinta/10 rounded-full px-2.5 py-0.5">${esc(j.gaji)}</span><span class="bg-tinta/10 rounded-full px-2.5 py-0.5">Tutup ${tgl(j.deadline)}</span></div>
      <p class="mt-4">${esc(j.deskripsi)}</p>
      <h3 class="font-bold mt-4">Persyaratan</h3><ul class="list-disc pl-5 text-sm space-y-1 mt-1">${esc(j.syarat).split('\n').map(s => `<li>${s}</li>`).join('')}</ul>
      <h3 class="font-bold mt-4">Skema kontrak kerja</h3><p class="text-sm">${esc(j.kontrak)}</p>
      <form id="app" class="mt-6 grid gap-3 border-t border-tinta/10 pt-5"><h3 class="font-judul font-bold text-lg">Kirim lamaran</h3>
        <input type="hidden" name="lowongan_id" value="${j.id}"><input name="web" class="hidden" tabindex="-1" autocomplete="off">
        <input name="nama" required placeholder="Nama lengkap" class="rounded-lg border border-tinta/20 px-3 py-2">
        <div class="grid sm:grid-cols-2 gap-3"><input name="email" type="email" required placeholder="Email" class="rounded-lg border border-tinta/20 px-3 py-2"><input name="wa" required placeholder="Nomor WhatsApp" class="rounded-lg border border-tinta/20 px-3 py-2"></div>
        <div class="grid sm:grid-cols-2 gap-3"><input name="sekolah" placeholder="Asal sekolah" class="rounded-lg border border-tinta/20 px-3 py-2"><input name="jurusan" placeholder="Jurusan" class="rounded-lg border border-tinta/20 px-3 py-2"></div>
        <textarea name="pesan" rows="3" placeholder="Ceritakan singkat mengapa Anda cocok" class="rounded-lg border border-tinta/20 px-3 py-2"></textarea>
        <label class="text-sm">CV (PDF, maksimal 2 MB)<input type="file" name="cv" accept="application/pdf" class="block mt-1 text-sm"></label>
        <p id="msg" class="text-sm" role="status"></p>
        <button class="rounded-lg bg-daun text-white font-bold py-3">Kirim lamaran</button></form>`;
    $('#x').onclick = () => $('#dlg').close();
    $('#app').onsubmit = async ev => {
      ev.preventDefault(); const m = $('#msg'); m.textContent = 'Mengirim...';
      const r = await fetch('api.php?a=apply', { method: 'POST', body: new FormData(ev.target) }), d = await r.json();
      m.className = 'text-sm font-semibold ' + (d.ok ? 'text-daun' : 'text-bata'); m.textContent = d.msg;
      if (d.ok) ev.target.reset();
    };
    $('#dlg').showModal();
  };
  $('#jobList').addEventListener('click', e => { const b = e.target.closest('[data-id]'); if (b) open(b.dataset.id); });
  $('#flt').addEventListener('input', debounce(load));
  load().then(() => { const id = new URLSearchParams(location.search).get('id'); if (id) open(id); });
}