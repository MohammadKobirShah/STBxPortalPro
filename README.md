# RKDY STALKER PRO
Owner: **Kobir Shah**
Timezone: Asia/Dhaka

## Performance-first rebuild
- Tailwind/Lucide/Google Fonts CDN removed — all UI CSS is local in `assets/style.css`
- Icon system: inline SVG only, no external font/JS icon payload
- Scripts deferred; channel cards use staggered CSS animation instead of heavy JS per-frame
- Images: native `loading="lazy"` + generated SVG fallback to avoid broken-logo white screens
- Responsive breakpoints: desktop (6 col) → small laptop (5) → tablet (4) → mobile (3/2)
- HLS player only on play page, using one minified CDN script (hls.js)

## Files
- `kobir.php` main engine (Asia/Dhaka timezone, branded session names)
- `login.php` premium login / success page
- `index.php` fast dashboard with virtualized pagination
- `play.php` clean fullscreen player
- `playlist.php` branded m3u export
- `assets/style.css` full custom theme
- `assets/app.js` tiny icon helper

## Login
Open `login.php`, enter portal URL + MAC, optional serial/device IDs/signature, optional SOCKS5 (`user:pass@host:port`).
