<!doctype html>
<html lang="no">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Venstrejustert skjema</title>
<style>
:root {
--bg: #0f172a; /* mørk bakgrunn */
--panel: #111827; /* litt lysere panel */
--text: #e5e7eb; /* lys tekst */
--muted: #9ca3af; /* dempet tekst */
--accent: #60a5fa; /* blå aksent */
--accent-strong: #3b82f6;
--danger: #ef4444; /* rød for Avbryt */
--radius: 16px;
--shadow: 0 10px 30px rgba(0,0,0,.35);
}

* { box-sizing: border-box; }
html, body { height: 100%; }
body {
margin: 0;
background: linear-gradient(135deg, #0b1226, #0a0f1e 60%, #0b1226);
color: var(--text);
font: 16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;
}

/* Venstreside-layout */
.page {
min-height: 100vh;
display: grid;
grid-template-columns: minmax(260px, 680px) 1fr; /* venstre kolonne + tom høyre */
gap: 0;
}

.left {
padding: 32px 28px 48px 28px;
}

.right { /* bevisst tom – alt er på venstre side */ }

.card {
background: radial-gradient(150% 120% at 0% 0%, #111827, #0f172a 70%);
border: 1px solid rgba(255,255,255,.06);
border-radius: var(--radius);
box-shadow: var(--shadow);
padding: 28px;
}

.heading {
font-size: clamp(18px, 2.2vw, 24px);
font-weight: 700;
letter-spacing: .2px;
margin: 0 0 18px 0;
}

.sub {
color: var(--muted);
font-size: 14px;
margin-bottom: 22px;
}

.field {
display: grid;
gap: 8px;
margin-bottom: 16px;
}

label {
font-size: 14px;
color: var(--muted);
}

.input {
width: 100%;
height: 48px;
border-radius: 12px;
background: #0b1020;
border: 1px solid rgba(255,255,255,.08);
color: var(--text);
padding: 12px 14px;
outline: none;
transition: border-color .2s, box-shadow .2s;
}

.input:focus {
border-color: var(--accent);
</html>
