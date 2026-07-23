<?php require_once "kobir.php";
global $SCARLET_WITCH, $MJ;
$id=trim((string)($_GET['id']??''));
$name=trim((string)($_GET['name']??'Channel'));
function e($s){return htmlspecialchars((string)($s??''),ENT_QUOTES,'UTF-8');}
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#060317">
<title><?=e($name)?> | RKDY STALKER PRO | Kobir Shah</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHJ4PSIxNCIgZmlsbD0iIzVmNWFmMCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTQlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC13ZWlnaHQ9IjkwMCIgZm9udC1zaXplPSIzNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPks8L3RleHQ+PC9zdmc+">
<style>
*{box-sizing:border-box;margin:0;padding:0}html,body{width:100%;height:100dvh;background:radial-gradient(circle at center,#120a35,#060413);color:#fff;font-family:Inter,system-ui,sans-serif;overflow:hidden}video,.wrap{position:fixed;inset:0;width:100%;height:100%}.wrap{display:flex;align-items:center;justify-content:center}.loading{position:fixed;inset:0;background:radial-gradient(circle,#120a35,#050312);display:grid;place-items:center;z-index:20}.brand{position:fixed;top:14px;left:14px;z-index:10;display:flex;gap:10px;align-items:center;padding:10px 12px;border-radius:14px;background:rgba(10,6,31,.6);backdrop-filter:blur(12px);border:1px solid rgba(168,85,247,.22)}.logo-sm{width:34px;height:34px;border-radius:11px;display:grid;place-items:center;background:linear-gradient(135deg,#6366f1,#8b5cf6,#d946ef);font-weight:900}.title{font-size:12px;font-weight:800}.sub{font-size:10px;color:#c4b5fd;letter-spacing:.12em;text-transform:uppercase}.bar{position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:10;font-size:11px;padding:8px 12px;border-radius:999px;background:rgba(10,6,31,.6);backdrop-filter:blur(12px);border:1px solid rgba(168,85,247,.22);color:#e9d5ff}.back{position:fixed;top:14px;right:14px;z-index:10;padding:10px 14px;border-radius:12px;border:1px solid rgba(168,85,247,.22);background:rgba(10,6,31,.6);color:#e9d5ff;cursor:pointer;backdrop-filter:blur(12px);text-decoration:none;font-weight:800;font-size:12px}.back:hover{background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(217,70,239,.15))}.spinner{width:42px;height:42px;border-radius:50%;border:3px solid rgba(168,85,247,.18);border-top-color:#d946ef;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<a class="back" href="index.php">← Dashboard</a>
<div class="brand"><div class="logo-sm">K</div><div><div class="title"><?=e($name)?></div><div class="sub">RKDY STALKER PRO</div></div></div>
<div class="bar">Stream starting…</div>
<div class="loading"><div class="spinner"></div></div>
<video id="p" autoplay controls playsinline></video>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js"></script>
<script>
const video=document.getElementById('p');const load=document.querySelector('.loading');const bar=document.querySelector('.bar');
const src='live.php?id=<?=rawurlencode($id)?>';
if(Hls.isSupported()){const hls=new Hls({maxBufferLength:20,maxMaxBufferLength:40,lowLatencyMode:true});hls.loadSource(src);hls.attachMedia(video);hls.on(Hls.Events.MANIFEST_PARSED,()=>{load.style.display='none';bar.textContent='Live';video.play().catch(()=>{bar.textContent='Tap to play'})});hls.on(Hls.Events.ERROR,(e,d)=>{if(d&&d.fatal){bar.textContent='Stream error'}})}
else{video.src=src;video.addEventListener('loadeddata',()=>{load.style.display='none';bar.textContent='Live'})}
video.addEventListener('playing',()=>{load.style.display='none';bar.textContent='● Live'});
</script>
</body>
</html>
