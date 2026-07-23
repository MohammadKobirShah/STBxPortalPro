<?php require_once 'kobir.php'; global $SCARLET_WITCH; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#060317">
<title>LOGIN | RKDY STALKER PRO | Kobir Shah</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHJ4PSIxNCIgZmlsbD0iIzVmNWFmMCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTQlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC13ZWlnaHQ9IjkwMCIgZm9udC1zaXplPSIzNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPks8L3RleHQ+PC9zdmc+">
<link rel="preload" href="assets/style.css" as="style">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="toast-container" class="toast-wrap"></div>

<div id="vaultModal" class="modal-bg">
  <div class="modal glass">
    <div class="row between" style="margin-bottom:18px">
      <div>
        <h2>Identity Vault</h2>
        <div class="micro" style="color:var(--muted-2);margin-top:4px">Saved portal sessions</div>
      </div>
      <button class="icon-btn" onclick="toggleVault(false)" aria-label="Close"><?= '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>' ?></button>
    </div>
    <div id="vaultList" class="stack" style="max-height:420px;overflow:auto;padding-right:4px"></div>
    <div id="vaultDetail" class="hidden stack" style="margin-top:10px"></div>
    <button class="btn ghost" style="margin-top:18px" onclick="toggleVault(false)">Close Vault</button>
  </div>
</div>

<main class="container">
  <section class="glass login-card">
    <div class="card">
      <div id="loginView">
        <div class="login-hero">
          <div class="logo-big">K</div>
          <h1>RKDY STALKER PRO</h1>
          <p>Kobir Shah • Handshake Engine</p>
        </div>

        <form id="handshakeForm" class="stack" style="margin-top:26px;gap:16px" autocomplete="off">
          <div class="field">
            <label class="label">Portal URL <span style="color:#f0abfc">*</span></label>
            <i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg></i>
            <input class="input" name="URL" type="url" required placeholder="http://portal.example.com/stalker_portal/c/">
          </div>

          <div class="grid-2">
            <div class="field">
              <label class="label">MAC Address <span style="color:#f0abfc">*</span></label>
              <i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></i>
              <input class="input" name="MAC" type="text" required placeholder="00:1A:79:XX:XX:XX" inputmode="text">
            </div>
            <div class="field">
              <label class="label">Serial Number</label>
              <i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/></svg></i>
              <input class="input" name="SN" type="text" placeholder="Auto-generated if blank">
            </div>
          </div>

          <div class="hw-grid">
            <div class="grid-3">
              <div class="stack" style="gap:6px">
                <label class="label">Device ID 1</label>
                <input class="input" name="D1" type="text" placeholder="Optional" style="padding:12px 12px;border-radius:12px">
              </div>
              <div class="stack" style="gap:6px">
                <label class="label">Device ID 2</label>
                <input class="input" name="D2" type="text" placeholder="Optional" style="padding:12px 12px;border-radius:12px">
              </div>
              <div class="stack" style="gap:6px">
                <label class="label">Signature</label>
                <input class="input" name="SG" type="text" placeholder="Optional" style="padding:12px 12px;border-radius:12px">
              </div>
            </div>
          </div>

          <div class="grid-4">
            <select class="input" name="Model">
              <option value="MAG250">MAG250</option>
              <option value="MAG254">MAG254</option>
              <option value="MAG270" selected>MAG270</option>
            </select>
            <select class="input" name="Proxy">
              <option value="DIRECT" selected>STREAM: DIRECT</option>
              <option value="PROXY">STREAM: WEB PROXY</option>
            </select>
            <select class="input" name="API">
              <option value="" selected>API: AUTO</option>
              <option value="328">API: 328</option>
              <option value="263">API: 263</option>
              <option value="262">API: 262</option>
            </select>
            <select class="input" name="Share">
              <option value="OFF" selected>SHARING: OFF</option>
              <option value="ON">SHARING: ON</option>
            </select>
          </div>

          <div class="field">
            <label class="label">SOCKS5 Proxy <span class="micro" style="color:var(--muted-2);text-transform:none;letter-spacing:0;font-weight:700">— optional IP bypass</span></label>
            <i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="16" y="16" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v4M5 16v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg></i>
            <input class="input" name="ProxyServer" type="text" placeholder="user:pass@host:port">
            <div class="micro" style="color:var(--muted-2);margin:8px 0 0 4px;font-weight:600;letter-spacing:0;text-transform:none">If proxy is blank, connection is direct.</div>
          </div>

          <button type="submit" class="btn" id="submitBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h7l-1 8 11-13h-7z"/></svg>
            <span>Initialize Handshake</span>
          </button>

          <div style="text-align:center">
            <button type="button" class="link" onclick="loadVault()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l10 6-10 6L2 8 12 2z"/><path d="M2 14l10 6 10-6"/></svg>
              Browse Saved Vault
            </button>
          </div>
        </form>
      </div>

      <div id="successView" class="hidden stack" style="gap:18px">
        <div class="row between" style="flex-wrap:wrap;gap:14px;padding-bottom:16px;border-bottom:1px solid var(--line-soft)">
          <div class="row" style="gap:14px">
            <div style="position:relative">
              <div class="stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(217,70,239,.15));border:1px solid rgba(168,85,247,.3);color:#e9d5ff">
                <span style="font-weight:900;font-size:20px">K</span>
              </div>
              <span style="position:absolute;inset:-3px auto auto -3px;width:14px;height:14px"><span style="position:absolute;inset:0;border-radius:50%;background:#d946ef;opacity:.65;animation:pulse 1.6s infinite"></span><span style="position:absolute;inset:2px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#d946ef);border:3px solid #060317"></span></span>
            </div>
            <div>
              <h2 style="margin:0;font-size:clamp(20px,3vw,28px);font-weight:900;line-height:1.1;background:linear-gradient(90deg,#ddd6fe,#f0abfc);-webkit-background-clip:text;background-clip:text;color:transparent">Handshake Success</h2>
              <div class="row" style="gap:8px;margin-top:6px">
                <span class="pill">Verified</span>
                <span id="systemIp" class="micro" style="color:var(--muted-2)"></span>
              </div>
            </div>
          </div>
          <div class="row" style="gap:8px;flex-wrap:wrap">
            <button class="icon-btn" title="Vault" onclick="loadVault()"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l10 6-10 6L2 8 12 2z"/><path d="M2 14l10 6 10-6"/></svg></button>
            <a class="icon-btn" href="index.php" style="background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(217,70,239,.12));border-color:rgba(168,85,247,.25);color:#e9d5ff" title="Open Dashboard"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></a>
          </div>
        </div>

        <div id="statsGrid" class="success-grid"></div>

        <div class="hw-grid" style="display:flex;gap:18px;align-items:center;justify-content:space-between;flex-wrap:wrap">
          <div class="row" style="gap:12px;flex-wrap:wrap">
            <div class="row" style="gap:8px;color:var(--muted-2)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
              <span class="micro" id="portalUrl"></span>
            </div>
            <div class="row" style="gap:8px;color:var(--muted-2)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"/></svg>
              <span class="micro" id="stbVersion"></span>
            </div>
            <div class="row" style="gap:8px;color:var(--muted-2)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span class="micro" id="lastActive"></span>
            </div>
          </div>
        </div>

        <div>
          <div class="label" style="color:#f0abfc;margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"/></svg>
            Hardware Fingerprint
          </div>
          <div id="metaGrid" class="meta-list"></div>
          <p style="text-align:center;margin:22px 0 0" class="micro" data-brand>Custom build by Kobir Shah • RKDY STALKER PRO</p>
        </div>

        <button type="button" class="link" style="margin:0 auto" onclick="showLogin()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
          Clear &amp; New Login
        </button>
      </div>
    </div>
  </section>
</main>

<script defer src="assets/app.js"></script>
<script>
const $ = (s, r=document)=>r.querySelector(s);
const $$ = (s, r=document)=>[...r.querySelectorAll(s)];
function toast(msg,type='ok'){
  const c=$('#toast-container');const el=document.createElement('div');
  el.className='toast '+type;
  el.innerHTML=(type==='ok'?'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>':'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>')+'<span>'+msg+'</span>';
  c.appendChild(el);setTimeout(()=>{el.style.opacity='0';el.style.transform='translateX(12px)';setTimeout(()=>el.remove(),400)},3500);
}
function toggleVault(show){$('#vaultModal').classList.toggle('open',!!show);if(!show){$('#vaultDetail').classList.add('hidden');$('#vaultList').classList.remove('hidden')}}
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
async function api(action, body){
  const res=await fetch('kobir.php?action='+action,{method:'POST',headers:{'Content-Type':'application/json','x-developed-by':'KOBIR_SHAH_DEV','X-POWERED-BY':'KOBIR-SHAH','X-GITHUB-USERNAME':'kobirshah','DEV-NAME':'KOBIR SHAH'},body:body?JSON.stringify(body):null});
  return res.json();
}
async function loadVault(){
  toggleVault(true);
  const list=$('#vaultList');
  list.innerHTML='<div class="spinner-ctr"><div class="loader"></div></div>';
  $('#vaultDetail').classList.add('hidden');$('#vaultList').classList.remove('hidden');
  try{
    const data=await api('all_portals');
    const portals=data.portals||[];
    if(!portals.length){list.innerHTML='<div class="empty">Vault is empty</div>';return}
    list.innerHTML=portals.map(p=>`
      <div class="vault-item">
        <div style="min-width:0;flex:1">
          <div class="url">${esc(p.URL.replace(/^https?:\/\//,'').split('/')[0])}</div>
          <div class="mac">${esc(p.MAC)} • ${esc(p.Model||'MAG254')}${p.ProxyServer?' • SOCKS5':''}</div>
        </div>
        <div class="row" style="gap:8px;flex:none">
          <button class="icon-btn" data-act="switch" data-id="${esc(p.id)}" title="Switch" style="width:40px;height:40px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 1l4 4-4 4M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 01-4 4H3"/></svg></button>
          <button class="vault-arrow" data-act="view" data-id="${esc(p.id)}" title="Inspect"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></button>
        </div>
      </div>`).join('');
    list.onclick=async e=>{
      const b=e.target.closest('button');if(!b)return;
      const id=b.dataset.id;
      if(b.dataset.act==='switch'){await switchPortal(id);return}
      const p=portals.find(x=>x.id===id);if(!p)return;
      $('#vaultList').classList.add('hidden');const d=$('#vaultDetail');d.classList.remove('hidden');
      const rows=[['Portal URL',p.URL],['MAC',p.MAC],['Serial',p.SN||'N/A'],['Model',p.Model],['Device ID 1',p.D1||'N/A'],['Device ID 2',p.D2||'N/A'],['Stream Mode',p.Proxy||'DIRECT'],['Proxy',p.ProxyServer||'DIRECT']];
      d.innerHTML=`
        <button class="link" onclick="document.getElementById('vaultList').classList.remove('hidden');this.parentElement.classList.add('hidden')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg> Back</button>
        <div class="detail-grid" style="margin-top:12px">
          ${rows.map(r=>`<div class="detail-row"><div class="k">${esc(r[0])}</div><div class="v">${esc(r[1])}</div></div>`).join('')}
        </div>
        <button class="btn" style="margin-top:16px" onclick="switchPortal('${esc(id)}')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg> Switch Identity Now</button>
      `;
    };
  }catch(e){list.innerHTML='<div class="empty">Vault load failed</div>'}
}
async function switchPortal(id){
  const res=await api('switch_portal',{id});
  if(res.statusCode===200){toast('Portal switched');setTimeout(()=>location.reload(),600)}
  else toast(res.message||'Switch failed','err');
}
function showLogin(){$('#loginView').classList.remove('hidden');$('#successView').classList.add('hidden')}
function showSuccess(d){
  $('#loginView').classList.add('hidden');$('#successView').classList.remove('hidden');
  const js=d.data?.js||{};
  $('#systemIp').textContent='Virtual IP: '+(js.ip||'Protected');
  $('#portalUrl').textContent=d.URL?'portal/'+(d.URL.replace(/^https?:\/\//,'').split('/')[0]):'N/A';
  $('#stbVersion').textContent=(js.stb_type||'MAG')+' | v'+(js.image_version||'218');
  $('#lastActive').textContent=d.Date||'—';
  const items=[
    ['Subscriber',d.Name,'user'],
    ['MAC',d.mac,'monitor'],
    ['Password',d.Password,'shield'],
    ['Parent PIN',d.parent_password||'0000','alert'],
    ['Country',js.country||'Global','globe'],
    ['Expiry',d.expirydate==='0000-00-00 00:00:00'?'Unlimited':d.expirydate,'clock'],
    ['Login',d.login,'at'],
    ['Hardware',js.hw_version||'N/A','cpu']
  ];
  const icons={user:'<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',shield:'<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>',alert:'<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',globe:'<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/>',clock:'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',monitor:'<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',cpu:'<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/>',at:'<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 006 0v-1a10 10 0 10-4 8"/>'};
  $('#statsGrid').innerHTML=items.map(it=>{
    const [l,v]=it;const ic=icons[it[2]]||icons.user;const col='linear-gradient(135deg,rgba(99,102,241,.18),rgba(217,70,239,.12))';
    return `<div class="stat">
      <div class="stat-icon" style="background:${col};border:1px solid rgba(168,85,247,.25);color:#e9d5ff">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${ic}</svg>
      </div>
      <div class="k">${esc(l)}</div><div class="v" title="${esc(v||'N/A')}">${esc(v||'N/A')}</div>
    </div>`;
  }).join('');
  const meta=[['Device ID 1',d.device_id||d.D1||'N/A'],['Device ID 2',d.device_id2||d.D2||'N/A'],['Signature',d.sig||d.SG||'N/A'],['SOCKS5',d.ProxyServer||'DIRECT']];
  $('#metaGrid').innerHTML=meta.map(m=>`<div class="meta-item"><div class="k">${esc(m[0])}</div><div class="v">${esc(m[1])}</div></div>`).join('');
}
async function restoreSession(){
  try{
    const data=await api('login_details');
    if(data.KOBIR&&data.KOBIR.statusCode===200){showSuccess(data.KOBIR);toast('Session restored');return}
  }catch(e){}
  showLogin();
}
document.addEventListener('DOMContentLoaded',()=>{
  restoreSession();
  $('#handshakeForm').addEventListener('submit',async e=>{
    e.preventDefault();
    const btn=$('#submitBtn');btn.disabled=true;btn.querySelector('span').textContent='Authenticating...';
    const obj=Object.fromEntries(new FormData(e.target));
    try{
      const res=await api('login',obj);
      if(res.KOBIR?.statusCode===200){showSuccess(res.KOBIR);toast('Portal access granted')}
      else toast(res.KOBIR?.message||'Handshake failed','err');
    }catch(err){toast('Server communication error','err')}
    finally{btn.disabled=false;btn.querySelector('span').textContent='Initialize Handshake'}
  });
  document.addEventListener('keydown',e=>{if(e.key==='Escape')toggleVault(false)});
});
</script>
<style>@keyframes pulse{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.2);opacity:0}}</style>
</body>
</html>
