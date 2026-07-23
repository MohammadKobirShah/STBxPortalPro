<?php require_once 'kobir.php';
if (!file_exists($DARK_SIDE . "/login.kobir")) { if (php_sapi_name() !== 'cli') { header("Location: login.php"); exit; } }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#060317">
<title>RKDY STALKER PRO | Kobir Shah</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHJ4PSIxNCIgZmlsbD0iIzVmNWFmMCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTQlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC13ZWlnaHQ9IjkwMCIgZm9udC1zaXplPSIzNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPks8L3RleHQ+PC9zdmc+">
<link rel="preload" href="assets/style.css" as="style">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div id="vaultModal" class="modal-bg">
  <div class="modal glass">
    <div class="row between" style="margin-bottom:18px">
      <div>
        <h2>Identity Vault</h2>
        <div class="micro" style="color:var(--muted-2);margin-top:4px">Switch saved portal identities</div>
      </div>
      <button class="icon-btn" onclick="closeVault()"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    </div>
    <div id="vaultList" class="stack" style="max-height:420px;overflow:auto"></div>
    <button class="btn ghost" style="margin-top:18px" onclick="closeVault()">Dismiss</button>
  </div>
</div>

<header class="topbar">
  <div class="container row between" style="gap:12px">
    <div class="row" style="gap:12px;min-width:0">
      <div class="logo">K</div>
      <div style="min-width:0">
        <div class="brand-title">RKDY STALKER</div>
        <div class="brand-sub">Kobir Shah • Premium Dashboard</div>
      </div>
    </div>
    <div class="search desktop-search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input id="searchInput" type="text" placeholder="Find channels fast…">
    </div>
    <div class="row" style="gap:8px;flex:none">
      <button class="icon-btn" onclick="openVault()" title="Vault"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l10 6-10 6L2 8 12 2z"/><path d="M2 14l10 6 10-6"/></svg></button>
      <a class="icon-btn" href="playlist.php" title="M3U Playlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg></a>
      <a class="icon-btn danger" href="login.php" title="Logout / New Login"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg></a>
    </div>
  </div>
  <div class="container mobile-search" style="margin-top:10px">
    <div class="search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input id="mobileSearch" type="text" placeholder="Search channels…">
    </div>
  </div>
</header>

<div class="container cat-scroll" style="margin-top:10px;padding-inline:16px" id="catsMobile"></div>

<main class="container" style="margin-top:18px;display:grid;grid-template-columns:210px minmax(0,1fr);gap:28px;align-items:flex-start">
  <aside class="side cat-col" id="catsDesktop"></aside>
  <section>
    <div class="row between" style="margin-bottom:18px;flex-wrap:wrap;gap:10px">
      <div>
        <h2 id="catTitle" class="title-xl">Discovery</h2>
        <p class="section-desc">Showing <b id="chCount">0</b> channels • fast render</p>
      </div>
    </div>
    <div id="grid" class="channel-grid"></div>
    <div class="spinner-ctr" id="loader"><div class="loader"></div></div>
  </section>
</main>

<footer class="footer">
  <span class="micro" style="letter-spacing:.42em">Custom build by Kobir Shah • RKDY STALKER PRO</span>
</footer>

<script defer src="assets/app.js"></script>
<script>
const grid=document.getElementById('grid');
const chCount=document.getElementById('chCount');
const loader=document.getElementById('loader');
const catsD=document.getElementById('catsDesktop');
const catsM=document.getElementById('catsMobile');
const catTitle=document.getElementById('catTitle');
let all=[],current='all',page=0,pageSize=30;
const fallback=name=>{
  const ch=name.charAt(0).toUpperCase();
  const colors=['#6366f1','#8b5cf6','#d946ef','#22c55e','#f59e0b','#06b6d4'];
  const c=colors[(name.length)%colors.length];
  return `data:image/svg+xml;base64,${btoa(`<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180"><rect width="180" height="180" fill="${c}" opacity="0.12"/><text x="50%" y="55%" font-family="Arial" font-weight="900" font-size="78" fill="${c}" text-anchor="middle" dominant-baseline="middle">${ch}</text></svg>`)}`;
};
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function icon(name){return AppIcons(name)}
function openVault(){document.getElementById('vaultModal').classList.add('open');loadVault()}
function closeVault(){document.getElementById('vaultModal').classList.remove('open')}
async function api(a,b){const r=await fetch('kobir.php?action='+a,{method:'POST',headers:{'Content-Type':'application/json','x-developed-by':'KOBIR_SHAH_DEV','X-POWERED-BY':'KOBIR-SHAH','X-GITHUB-USERNAME':'kobirshah','DEV-NAME':'KOBIR SHAH'},body:b?JSON.stringify(b):null});return r.json()}
async function loadVault(){
  const list=document.getElementById('vaultList');list.innerHTML='<div class="spinner-ctr"><div class="loader"></div></div>';
  try{const d=await api('all_portals');const ps=d.portals||[];if(!ps.length){list.innerHTML='<div class="empty">No saved portals yet</div>';return}
  list.innerHTML=ps.map(p=>`<div class="vault-item"><div style="min-width:0;flex:1"><div class="url">${esc(p.URL.replace(/^https?:\/\//,''))}</div><div class="mac">${esc(p.MAC)} • ${esc(p.Model)}${p.ProxyServer?' • SOCKS5':''}</div></div><button class="icon-btn" onclick="switchPortal('${esc(p.id)}')" title="Switch"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>`).join('')}
  catch(e){list.innerHTML='<div class="empty">Vault load failed</div>'}
}
async function switchPortal(id){const r=await api('switch_portal',{id});if(r.statusCode===200){location.reload()}}
function renderCats(){
  const genres=['all',...[...new Set(all.map(c=>c.genre).filter(Boolean))]];
  const html=genres.map(g=>`<button class="${catsD?'cat-btn':'cat-pill'} ${g===current?'active':''}" data-g="${esc(g)}">${g==='all'?'Discovery':esc(g)}</button>`).join('');
  if(catsD)catsD.innerHTML=html;catsM.innerHTML=html;
  [...document.querySelectorAll('[data-g]')].forEach(b=>b.onclick=()=>{current=b.dataset.g;catTitle.textContent=current==='all'?'Discovery':current;renderCats();reset()});
}
function getFiltered(){
  const q=((document.getElementById('searchInput')?.value||document.getElementById('mobileSearch')?.value)||'').toLowerCase().trim();
  return all.filter(c=>(current==='all'||c.genre===current)&&c.Name.toLowerCase().includes(q));
}
function cardHtml(c,i){
  const delay=Math.min(i*25,600);
  const num=String(c.number||'—').padStart(3,'0');
  const logo=c.logo||fallback(c.Name);
  return `<a class="ch" href="play.php?id=${encodeURIComponent(c.playback_url)}&name=${encodeURIComponent(c.Name)}" style="animation:cardin .45s ease ${delay}ms forwards">
    <div class="ch-logo">
      <img loading="lazy" decoding="async" src="${esc(logo)}" alt="${esc(c.Name)}" onerror="this.src='${esc(fallback(c.Name))}'">
      <div class="play-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><polygon points="6 3 20 12 6 21 6 3"/></svg></div>
    </div>
    <div class="ch-info">
      <div class="row between"><span class="ch-num">#${esc(num)}</span>${c.Name.toUpperCase().includes('4K')?'<span class="tag-4k">4K</span>':''}</div>
      <div class="ch-name" title="${esc(c.Name)}">${esc(c.Name)}</div>
    </div>
  </a>`;
}
function skeletonHtml(n=18){return Array.from({length:n},()=>`<div class="ch sk-pad" style="opacity:1;transform:none"><div class="skeleton sk-logo"></div><div class="sk-line short"></div><div class="sk-line"></div></div>`).join('')}
function loadMore(){
  const f=getFiltered();chCount.textContent=f.length;
  const slice=f.slice(page,page+pageSize);
  if(!slice.length){loader.style.display='none';return}
  slice.forEach((c,i)=>grid.insertAdjacentHTML('beforeend',cardHtml(c,page+i)));
  page+=pageSize;
  loader.style.display=page>=f.length?'none':'grid';
}
function reset(){grid.innerHTML='';page=0;loadMore()}
async function init(){
  grid.innerHTML=skeletonHtml(18);
  try{
    const d=await api('livechannels');
    all=Array.isArray(d)?d:[];if(!all.length&&d&&!Array.isArray(d)){grid.innerHTML=`<div class="empty channel-grid" style="grid-column:1/-1">${esc(d.KOBIR?.message||d.message||'No channels loaded')}</div>`;loader.style.display='none';return}
    grid.innerHTML='';renderCats();reset();
    document.getElementById('searchInput').addEventListener('input',reset);
    document.getElementById('mobileSearch').addEventListener('input',reset);
    const io=new IntersectionObserver(es=>{if(es[0].isIntersecting)loadMore()},{rootMargin:'200px'});io.observe(loader);
  }catch(e){grid.innerHTML='<div class="empty channel-grid" style="grid-column:1/-1">Failed to load channels</div>';loader.style.display='none'}
}
document.addEventListener('DOMContentLoaded',init);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeVault()});
</script>
<style>@keyframes cardin{to{opacity:1;transform:translateY(0) scale(1)}}@keyframes pulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(2.2);opacity:0}}.ch.show{opacity:1}</style>
</body>
</html>
