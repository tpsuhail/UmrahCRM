<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Umrah CRM</title>
<meta name="crm-api" content="{{ route('api.dispatch') }}"/>
<meta name="crm-login" content="{{ route('api.login') }}"/>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<style>
/* ── TOKENS (matching existing dashboard identity) ── */
:root{
  --green:#22c55e;--blue:#3b82f6;--amber:#f59e0b;--red:#ef4444;
  --purple:#a855f7;--teal:#14b8a6;--orange:#f97316;--cyan:#06b6d4;
}
[data-theme="dark"]{
  --bg:#080e1a;--bg2:#0e1726;--card:#111f33;--card2:#162540;
  --border:#1e3050;--text:#e2eaf8;--muted:#6a849e;--input-bg:#0c1828;
  --shadow:0 8px 32px rgba(0,0,0,.55);--glow:0 0 0 1px rgba(59,130,246,.18);
  --grad-card:linear-gradient(135deg,#111f33 0%,#0e1c30 100%);
}
[data-theme="light"]{
  --bg:#f0f5ff;--bg2:#e4edfb;--card:#ffffff;--card2:#f5f8ff;
  --border:#c8d8f0;--text:#0f1e35;--muted:#5a6f88;--input-bg:#e8f0fb;
  --shadow:0 4px 20px rgba(20,50,100,.10);--glow:0 0 0 1px rgba(59,130,246,.12);
  --grad-card:linear-gradient(135deg,#ffffff 0%,#f5f8ff 100%);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Cairo','Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background .3s,color .3s;font-size:.9rem}
[data-lang="en"] body,[data-lang="en"]{font-family:'Inter','Cairo',sans-serif}
button{font-family:inherit}
input,select,textarea{font-family:inherit;color:var(--text)}

/* ── SHARED CONTROLS ── */
.btn{border:none;border-radius:10px;padding:9px 18px;cursor:pointer;font-size:.83rem;font-weight:700;transition:all .2s;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:linear-gradient(135deg,var(--blue),#6366f1);color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.35)}
.btn-primary:hover{transform:translateY(-1px)}
.btn-ghost{background:var(--input-bg);color:var(--text);border:1px solid var(--border)}
.btn-ghost:hover{border-color:var(--blue);color:var(--blue)}
.btn-danger{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3)}
.btn-sm{padding:5px 12px;font-size:.75rem}
.btn:disabled{opacity:.5;cursor:not-allowed}
.field{margin-bottom:14px}
.field label{display:block;font-size:.76rem;font-weight:700;color:var(--muted);margin-bottom:5px}
.field input,.field select,.field textarea{width:100%;padding:9px 12px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.85rem;outline:none;transition:border-color .2s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--blue);box-shadow:var(--glow)}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:.68rem;font-weight:700}
.b-green{background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.25)}
.b-red{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25)}
.b-blue{background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.25)}
.b-amber{background:rgba(245,158,11,.15);color:#fcd34d;border:1px solid rgba(245,158,11,.25)}
.b-teal{background:rgba(20,184,166,.15);color:#5eead4;border:1px solid rgba(20,184,166,.25)}
.b-purple{background:rgba(168,85,247,.15);color:#d8b4fe;border:1px solid rgba(168,85,247,.25)}
.b-gray{background:rgba(106,132,158,.15);color:var(--muted);border:1px solid var(--border)}
.nav-badge{background:var(--red);color:#fff;border-radius:99px;font-size:.62rem;padding:2px 8px;margin-inline-start:auto;font-weight:800}
.detail-list{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;font-size:.8rem}
.detail-list .dl-item{background:var(--input-bg);border:1px solid var(--border);border-radius:8px;padding:8px 10px}
.detail-list .dl-lbl{font-size:.65rem;color:var(--muted);font-weight:700;margin-bottom:2px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
@media(max-width:520px){.form-grid,.detail-list{grid-template-columns:1fr}}
/* wizard */
.wz-steps{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap}
.wz-step{flex:1;min-width:90px;display:flex;flex-direction:column;align-items:center;gap:5px;padding:8px 4px;border-radius:10px;border:1px solid var(--border);background:var(--input-bg);font-size:.68rem;font-weight:700;color:var(--muted);position:relative}
.wz-step.done{color:var(--green);border-color:rgba(34,197,94,.3)}
.wz-step.active{color:#fff;background:linear-gradient(135deg,var(--blue),#6366f1);border-color:transparent;box-shadow:0 4px 12px rgba(59,130,246,.35)}
.wz-num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--card);border:1px solid var(--border);font-size:.72rem}
.wz-step.active .wz-num{background:rgba(255,255,255,.2);border-color:transparent;color:#fff}
.wz-step.done .wz-num{background:var(--green);color:#fff;border-color:transparent}
.wz-nav{display:flex;justify-content:space-between;gap:10px;margin-top:18px}
.sub-row{display:flex;align-items:center;gap:8px;background:var(--input-bg);border:1px solid var(--border);border-radius:10px;padding:9px 12px;margin-bottom:8px;font-size:.8rem}
.sub-row .sub-main{flex:1;min-width:0}
.sub-row .sub-x{color:var(--red);cursor:pointer;font-weight:800;flex-shrink:0}
.success-box{text-align:center;padding:40px 20px}
.success-box .big{font-size:3rem;margin-bottom:10px}
.req-id{font-size:1.5rem;font-weight:900;letter-spacing:1px;background:var(--input-bg);border:1px dashed var(--blue);border-radius:12px;padding:14px 20px;margin:14px auto;display:inline-block;color:var(--blue)}
.wz-card{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px;margin-bottom:12px}
.wz-card-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:.82rem}
.wz-card-hd .sub-x{color:var(--red);cursor:pointer;font-weight:700;font-size:.72rem}
.wz-toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:var(--input-bg);border:1px solid var(--border);border-radius:10px;padding:10px 14px;margin-bottom:14px}
.wz-summary{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.wz-chip{background:var(--card);border:1px solid var(--border);border-radius:99px;padding:6px 14px;font-size:.72rem;font-weight:700;color:var(--muted);box-shadow:var(--shadow)}
.fu-board{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;align-items:start}
@media(max-width:1000px){.fu-board{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.fu-board{grid-template-columns:1fr}}
.fu-col{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:10px;min-height:120px}
.fu-col.mine{box-shadow:0 0 0 2px var(--blue)}
.fu-col-hd{display:flex;align-items:center;justify-content:space-between;font-size:.8rem;font-weight:800;padding:4px 6px 10px}
.fu-count{background:var(--card);border:1px solid var(--border);border-radius:99px;padding:2px 10px;font-size:.7rem}
.fu-card{background:var(--card);border:1px solid var(--border);border-radius:11px;padding:10px 12px;margin-bottom:8px;font-size:.75rem;box-shadow:var(--shadow)}
.fu-card .fu-title{font-weight:800;font-size:.8rem;margin-bottom:4px}
.stage-age{display:inline-flex;align-items:center;gap:3px;font-size:.62rem;font-weight:700;border-radius:99px;padding:2px 8px;border:1px solid transparent}
.stage-age.fresh{background:rgba(34,197,94,.12);color:#4ade80;border-color:rgba(34,197,94,.25)}
.stage-age.warn{background:rgba(245,158,11,.14);color:#f59e0b;border-color:rgba(245,158,11,.3)}
.stage-age.stale{background:rgba(239,68,68,.14);color:#f87171;border-color:rgba(239,68,68,.3)}
.fu-meta{color:var(--muted);font-size:.68rem;margin-bottom:6px}
.fu-prog{height:6px;background:var(--input-bg);border-radius:99px;overflow:hidden;margin:6px 0}
.fu-prog>div{height:100%;background:linear-gradient(90deg,var(--blue),var(--green));border-radius:99px}
.fu-actions{display:flex;justify-content:space-between;gap:6px;margin-top:6px}
.fu-btn{border:1px solid var(--border);background:var(--input-bg);color:var(--text);border-radius:8px;padding:4px 10px;font-size:.68rem;font-weight:700;cursor:pointer}
.fu-btn:hover{border-color:var(--blue);color:var(--blue)}
.fu-btn.primary{background:linear-gradient(135deg,var(--blue),#6366f1);color:#fff;border:none}
.stage-bar{display:flex;gap:0;margin:10px 0 4px;align-items:center}
.stage-dot{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;position:relative;font-size:.62rem;font-weight:700;color:var(--muted)}
.stage-dot .dot{width:22px;height:22px;border-radius:50%;background:var(--input-bg);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.68rem;z-index:1}
.stage-dot.done .dot{background:var(--green);border-color:var(--green);color:#fff}
.stage-dot.current .dot{background:linear-gradient(135deg,var(--blue),#6366f1);border-color:transparent;color:#fff;box-shadow:0 3px 10px rgba(59,130,246,.4)}
.stage-dot.current{color:var(--blue)}
.stage-dot:not(:first-child)::before{content:'';position:absolute;top:11px;inset-inline-start:-50%;width:100%;height:2px;background:var(--border)}
.stage-dot.done:not(:first-child)::before,.stage-dot.current:not(:first-child)::before{background:var(--green)}
.wz-body{animation:wzIn .3s ease}
@keyframes wzIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
/* wizard trips table */
.wz-trip-tbl{width:100%;border-collapse:separate;border-spacing:0;min-width:760px}
.wz-trip-tbl thead th{background:var(--bg2);color:var(--muted);font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:8px 8px;text-align:start;white-space:nowrap;border-bottom:1px solid var(--border);position:sticky;top:0}
.wz-trip-tbl tbody td{padding:5px 6px;border-bottom:1px solid var(--border);vertical-align:middle}
.wz-trip-tbl tbody tr:hover td{background:var(--card2)}
.wz-trip-tbl tbody tr:last-child td{border-bottom:none}
.wz-trip-tbl td:first-child,.wz-trip-tbl th:first-child{text-align:center;width:34px;color:var(--muted)}
.wz-trip-tbl td:last-child,.wz-trip-tbl th:last-child{text-align:center;width:36px}
.wz-trip-tbl input,.wz-trip-tbl select{width:100%;padding:6px 8px;background:var(--input-bg);border:1px solid var(--border);border-radius:7px;font-size:.78rem;outline:none;transition:border-color .15s}
.wz-trip-tbl input:focus,.wz-trip-tbl select:focus{border-color:var(--blue);box-shadow:var(--glow)}
.wz-trip-tbl input[type=date],.wz-trip-tbl input[type=time],.wz-trip-tbl input[type=number]{min-width:0}
.wz-trip-tbl .sub-x{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;color:var(--red);cursor:pointer;font-weight:700;transition:background .15s}
.wz-trip-tbl .sub-x:hover{background:rgba(239,68,68,.15)}
.wz-trip-tbl .no-data td{text-align:center;color:var(--muted);font-style:italic;padding:22px}

/* ── LOGIN ── */
#loginScreen{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at top,rgba(59,130,246,.08),transparent 60%),var(--bg)}
.login-card{width:100%;max-width:400px;background:var(--grad-card);border:1px solid var(--border);border-radius:20px;padding:36px 32px;box-shadow:var(--shadow)}
.login-brand{display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:28px;text-align:center}
.brand-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#1d4ed8,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(59,130,246,.45)}
.login-brand h1{font-size:1.25rem;font-weight:900}
.login-brand h1 span{background:linear-gradient(90deg,var(--blue),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.login-brand .sub{font-size:.75rem;color:var(--muted)}
.login-error{display:none;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:10px;padding:10px 14px;font-size:.78rem;margin-bottom:14px}
.login-toggles{display:flex;justify-content:center;gap:8px;margin-top:20px}
.toggle-btn{background:var(--input-bg);border:1px solid var(--border);border-radius:10px;padding:7px 12px;cursor:pointer;font-size:.8rem;color:var(--text);transition:all .2s}
.toggle-btn:hover{border-color:var(--blue)}

/* ── APP SHELL ── */
#appShell{display:none;min-height:100vh}
.topbar{background:linear-gradient(90deg,var(--card) 0%,var(--bg2) 100%);border-bottom:2px solid var(--border);padding:10px 20px;display:flex;align-items:center;justify-content:space-between;gap:10px;position:sticky;top:0;z-index:90}
.topbar-left{display:flex;align-items:center;gap:12px}
.menu-btn{display:none;background:none;border:none;font-size:1.3rem;color:var(--text);cursor:pointer}
.topbar h1{font-size:.95rem;font-weight:900}
.topbar h1 span{background:linear-gradient(90deg,var(--blue),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.topbar-user{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:10px}
.topbar-user b{color:var(--text)}
.bell-wrap{position:relative}
.bell-badge{position:absolute;top:-4px;inset-inline-end:-4px;background:var(--red);color:#fff;font-size:.58rem;font-weight:800;min-width:15px;height:15px;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 3px;border:1px solid var(--card)}
.bell-menu{position:absolute;inset-inline-end:0;top:calc(100% + 8px);width:290px;max-width:82vw;background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.28);z-index:120;overflow:hidden;animation:wzIn .18s ease}
.bell-menu-hd{font-size:.8rem;font-weight:800;padding:11px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.bell-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
.bell-item:last-child{border-bottom:none}
.bell-item:hover{background:var(--bg2)}
.bell-item .bi-txt{flex:1;font-size:.79rem}
.bell-empty{padding:22px 14px;text-align:center;color:var(--muted);font-size:.82rem}
/* ops board */
.ops-summary{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
.ops-chip{background:var(--card);border:1px solid var(--border);border-radius:99px;padding:6px 14px;font-size:.74rem;font-weight:800;box-shadow:var(--shadow)}
.ops-chip.ok{color:#22c55e;border-color:rgba(34,197,94,.35)}
.ops-chip.warn{color:#f59e0b;border-color:rgba(245,158,11,.35)}
.ops-date-hd td{background:var(--bg2)!important;font-size:.78rem;padding:8px 10px!important;border-top:2px solid var(--border)}
.layout{display:flex;min-height:calc(100vh - 55px)}
.sidebar{width:220px;background:var(--card);border-inline-end:1px solid var(--border);padding:16px 10px;flex-shrink:0;transition:transform .25s}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;cursor:pointer;font-size:.83rem;font-weight:600;color:var(--muted);margin-bottom:4px;transition:all .15s;border:1px solid transparent}
.nav-item:hover{background:var(--card2);color:var(--text)}
.nav-item.active{background:linear-gradient(135deg,rgba(59,130,246,.18),rgba(99,102,241,.12));color:var(--blue);border-color:rgba(59,130,246,.25)}
.nav-item .soon{margin-inline-start:auto;font-size:.6rem;background:var(--input-bg);border:1px solid var(--border);border-radius:99px;padding:2px 7px;color:var(--muted)}
.content{flex:1;padding:22px;max-width:1300px;min-width:0}

/* ── CARDS/KPI ── */
.page-title{font-size:1.05rem;font-weight:900;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--grad-card);border:1px solid var(--border);border-radius:16px;padding:16px 14px;position:relative;overflow:hidden;box-shadow:var(--shadow)}
.kpi-accent{position:absolute;top:0;inset-inline-start:0;width:4px;height:100%}
.kpi-val{font-size:1.9rem;font-weight:900;line-height:1.1}
.kpi-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-top:4px}
.card{background:var(--grad-card);border:1px solid var(--border);border-radius:16px;padding:20px;margin-bottom:18px;box-shadow:var(--shadow)}
.card-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:8px;flex-wrap:wrap}
.card-title{font-size:.92rem;font-weight:800}

/* ── TABLES ── */
.tbl-wrap{overflow-x:auto;border-radius:10px;border:1px solid var(--border)}
table{width:100%;border-collapse:collapse;font-size:.78rem;min-width:560px}
th{background:var(--bg2);padding:9px 11px;text-align:start;color:var(--muted);font-weight:700;border-bottom:1px solid var(--border);white-space:nowrap;font-size:.71rem;text-transform:uppercase;letter-spacing:.3px}
td{padding:8px 11px;border-bottom:1px solid var(--border);white-space:nowrap}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--card2)}
.no-data td{text-align:center;color:var(--muted);padding:22px;font-style:italic}
.tbl-search{position:relative;max-width:260px;flex:1}
.tbl-search input{width:100%;padding:7px 12px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.8rem;outline:none}
.tbl-search input:focus{border-color:var(--blue)}
.link{color:var(--blue);cursor:pointer;font-weight:700}

/* ── MODAL ── */
#modalOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:center;justify-content:center;padding:16px}
.modal{background:var(--card);border:1px solid var(--border);border-radius:18px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;box-shadow:var(--shadow)}
.modal.wide{max-width:920px}
.pager{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 4px 2px;font-size:.74rem;font-weight:700;color:var(--muted);flex-wrap:wrap}
.pager-per{display:flex;align-items:center;gap:6px}
.pager-nav{display:flex;align-items:center;gap:10px}
.pager-tot{opacity:.8}
.pager-size{background:var(--input-bg);border:1px solid var(--border);border-radius:7px;padding:3px 6px;font-size:.74rem;font-weight:700;color:var(--text);cursor:pointer}
.card-badge{background:var(--input-bg);border:1px solid var(--border);border-radius:20px;padding:3px 11px;font-size:.7rem;color:var(--muted);font-weight:800}
.drv b{color:var(--text);font-weight:800}
.drv-mob{font-weight:800;color:var(--blue);font-size:.72rem;letter-spacing:.3px}
.plate{display:inline-block;background:var(--input-bg);border:1px solid var(--border);border-radius:5px;padding:2px 7px;font-weight:800;font-size:.74rem;letter-spacing:1px;white-space:nowrap}
.field-err{border-color:var(--red)!important;box-shadow:0 0 0 2px rgba(239,68,68,.15)!important}
.wz-errs{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.35);border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:.78rem;color:var(--text)}
.wz-errs ul{margin:6px 0 0;padding-inline-start:18px}
.wz-errs li{margin:2px 0;color:var(--red)}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:9px;font-size:.76rem}
.bar-lbl{width:110px;flex-shrink:0;font-weight:700;color:var(--muted)}
.bar-track{flex:1;height:12px;background:var(--bg2);border-radius:99px;overflow:hidden}
.bar-fill{height:100%;border-radius:99px;transition:width .5s ease}
.bar-val{width:34px;text-align:end;font-weight:800;flex-shrink:0}
.dash-3col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:900px){.dash-3col{grid-template-columns:1fr}}
.pager .fu-btn[disabled]{opacity:.4;cursor:default}
.mini-tbl{width:100%;border-collapse:collapse;font-size:.74rem}
.mini-tbl th{color:var(--muted);font-size:.64rem;text-transform:uppercase;text-align:start;padding:5px 6px;border-bottom:1px solid var(--border)}
.mini-tbl td{padding:6px 6px;border-bottom:1px solid var(--border)}
.mini-tbl tr:last-child td{border-bottom:none}
.mini-tbl tr:hover td{background:var(--bg2);cursor:pointer}
.dash-2col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:900px){.dash-2col{grid-template-columns:1fr}}
.diff-box{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:.76rem}
.diff-box .di{padding:3px 0;border-bottom:1px dashed var(--border)}
.diff-box .di:last-child{border-bottom:none}
.modal-hd{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-hd h3{font-size:.95rem;font-weight:800}
.modal-close{background:none;border:none;font-size:1.1rem;color:var(--muted);cursor:pointer}
.modal-body{padding:20px}
.modal-ft{padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}

/* ── TOAST ── */
#toast{position:fixed;top:64px;inset-inline-start:50%;transform:translateX(-50%);background:var(--card);border:1px solid var(--border);border-radius:12px;padding:12px 20px;font-size:.82rem;font-weight:700;box-shadow:0 8px 26px rgba(0,0,0,.3);z-index:300;display:none;max-width:90vw}
[data-lang="ar"] #toast{transform:translateX(50%)}
#toast.ok{border-color:rgba(34,197,94,.4);color:#4ade80}
#toast.err{border-color:rgba(239,68,68,.4);color:#f87171}

/* ── LOADING ── */
.spinner{width:34px;height:34px;border:3px solid var(--border);border-top-color:var(--blue);border-radius:50%;animation:spin .75s linear infinite;margin:30px auto}
@keyframes spin{to{transform:rotate(360deg)}}
.btn .mini-spin{width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}

/* ── RESPONSIVE ── */
@media(max-width:820px){
  .menu-btn{display:block}
  .sidebar{position:fixed;top:55px;bottom:0;inset-inline-start:0;z-index:80;transform:translateX(-105%);box-shadow:var(--shadow)}
  [data-lang="ar"] .sidebar{transform:translateX(105%)}
  .sidebar.open{transform:translateX(0)!important}
  .content{padding:14px}
}
@media(prefers-reduced-motion:reduce){*{animation-duration:.01ms!important;transition-duration:.01ms!important}}
</style>
</head>
<body data-theme="light" data-lang="en">

<!-- ═══════════ LOGIN ═══════════ -->
<div id="loginScreen">
  <div class="login-card">
    <div class="login-brand">
      <div class="brand-icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="4" y="8" width="16" height="13" rx="1" fill="white" opacity="0.95"/>
          <polygon points="4,8 12,3 20,8" fill="white" opacity="0.7"/>
          <rect x="4" y="13" width="16" height="2.5" fill="#f59e0b" opacity="0.9"/>
          <rect x="10" y="14.5" width="4" height="6.5" rx=".5" fill="#1d4ed8" opacity="0.8"/>
        </svg>
      </div>
      <h1>UMRAH <span>CRM</span></h1>
      <div class="sub" data-i18n="loginSub">Operator & Agents Portal</div>
    </div>
    <div class="login-error" id="loginError"></div>
    <div class="field"><label data-i18n="username">Username</label><input id="loginUser" autocomplete="username"/></div>
    <div class="field"><label data-i18n="password">Password</label><input id="loginPass" type="password" autocomplete="current-password"/></div>
    <button class="btn btn-primary" style="width:100%;justify-content:center" id="loginBtn" onclick="doLogin()"><span data-i18n="signIn">Sign in</span></button>
    <div class="login-toggles">
      <button class="toggle-btn" onclick="toggleTheme()">🌓</button>
      <button class="toggle-btn" onclick="toggleLang()" id="langBtnLogin">عربي</button>
    </div>
  </div>
</div>

<!-- ═══════════ APP SHELL ═══════════ -->
<div id="appShell">
  <div class="topbar">
    <div class="topbar-left">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <h1>UMRAH <span>CRM</span></h1>
    </div>
    <div class="topbar-user">
      <span>👤 <b id="userName"></b> · <span id="userRoleLbl"></span></span>
      <div class="bell-wrap">
        <button class="toggle-btn" onclick="toggleBell(event)" id="bellBtn" title="Notifications">🔔<span class="bell-badge" id="bellBadge" style="display:none">0</span></button>
        <div class="bell-menu" id="bellMenu" style="display:none"></div>
      </div>
      <button class="toggle-btn" onclick="toggleTheme()">🌓</button>
      <button class="toggle-btn" onclick="toggleLang()" id="langBtnApp">عربي</button>
      <button class="toggle-btn" onclick="openChangePassword()" data-i18n-title="changePass" title="Change password">🔑</button>
      <button class="toggle-btn" onclick="doLogout()" data-i18n-title="logout" title="Logout">🚪</button>
    </div>
  </div>
  <div class="layout">
    <nav class="sidebar" id="sidebar"></nav>
    <main class="content" id="content"></main>
  </div>
</div>

<!-- ═══════════ MODAL + TOAST ═══════════ -->
<div id="modalOverlay"><div class="modal">
  <div class="modal-hd"><h3 id="modalTitle"></h3><button class="modal-close" onclick="closeModal()">✕</button></div>
  <div class="modal-body" id="modalBody"></div>
  <div class="modal-ft" id="modalFooter"></div>
</div></div>
<div id="toast"></div>
<div id="busy" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:500;align-items:center;justify-content:center">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px 26px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow)">
    <div class="spinner" style="margin:0;width:24px;height:24px"></div>
    <span id="busyTxt" style="font-size:.85rem;font-weight:700"></span>
  </div>
</div>

<!-- ═══════════ PRINT PREVIEW ═══════════ -->
<div id="printOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:400;flex-direction:column">
  <div style="display:flex;gap:8px;justify-content:center;padding:10px;background:var(--card);border-bottom:1px solid var(--border)">
    <button class="btn btn-primary btn-sm" onclick="printPreviewDoc()">🖨️ <span id="ppPrint"></span></button>
    <button class="btn btn-ghost btn-sm" onclick="downloadPreviewPdf()">📥 <span id="ppPdf"></span></button>
    <button class="btn btn-ghost btn-sm" onclick="closePrintPreview()">✕ <span id="ppClose"></span></button>
  </div>
  <iframe id="printFrame" style="flex:1;border:none;background:#fff;width:100%"></iframe>
</div>

<script>
/* Endpoints are injected by the server so the app works under any base path. */
var API_URL   = document.querySelector('meta[name="crm-api"]').content;
var LOGIN_URL = document.querySelector('meta[name="crm-login"]').content;
/* ════════════════ STATE + I18N ════════════════ */
var state = { token:null, user:null, lang:'en', theme:'light', view:'dashboard', cache:{} };

var T = {
  loginSub:{en:'Operator & Agents Portal',ar:'بوابة المشغّل والوكلاء'},
  username:{en:'Username',ar:'اسم المستخدم'}, password:{en:'Password',ar:'كلمة المرور'},
  signIn:{en:'Sign in',ar:'تسجيل الدخول'},
  errInvalid:{en:'Invalid username or password',ar:'اسم المستخدم أو كلمة المرور غير صحيحة'},
  errDisabled:{en:'This account is disabled',ar:'هذا الحساب موقوف'},
  errServer:{en:'Server error, try again',ar:'خطأ في الخادم، حاول مرة أخرى'},
  errAuth:{en:'Session expired, sign in again',ar:'انتهت الجلسة، سجّل الدخول مجدداً'},
  logout:{en:'Logout',ar:'خروج'}, changePass:{en:'Change password',ar:'تغيير كلمة المرور'},
  operator:{en:'Operator',ar:'مشغّل'}, agent:{en:'Agent',ar:'وكيل'},
  dashboard:{en:'Dashboard',ar:'لوحة التحكم'}, agents:{en:'Agents',ar:'الوكلاء'},
  users:{en:'Users',ar:'المستخدمون'}, masters:{en:'Settings & Lists',ar:'الإعدادات والقوائم'},
  groups:{en:'Groups',ar:'المجموعات'}, requests:{en:'Requests',ar:'الطلبات'},
  logsNav:{en:'Activity Log',ar:'سجل النشاط'}, soon:{en:'Phase 2',ar:'المرحلة 2'},
  kAgents:{en:'Active Agents',ar:'وكلاء نشطون'}, kUsers:{en:'Active Users',ar:'مستخدمون نشطون'},
  kGroups:{en:'Groups',ar:'المجموعات'}, kPax:{en:'Total Pax',ar:'إجمالي المعتمرين'},
  kInKingdom:{en:'Groups in Kingdom',ar:'مجموعات داخل المملكة'}, kPending:{en:'Pending Requests',ar:'طلبات معلّقة'},
  recentActivity:{en:'Recent Activity',ar:'آخر النشاطات'},
  addAgent:{en:'+ Add Agent',ar:'+ إضافة وكيل'}, addUser:{en:'+ Add User',ar:'+ إضافة مستخدم'},
  addItem:{en:'+ Add Item',ar:'+ إضافة عنصر'},
  search:{en:'Search…',ar:'بحث…'},
  agentName:{en:'Agent Name',ar:'اسم الوكيل'}, country:{en:'Country',ar:'الدولة'},
  contactName:{en:'Contact Person',ar:'اسم المسؤول'}, mobile:{en:'Mobile',ar:'الجوال'},
  email:{en:'Email',ar:'البريد الإلكتروني'}, notes:{en:'Notes',ar:'ملاحظات'},
  status:{en:'Status',ar:'الحالة'}, active:{en:'Active',ar:'نشط'}, inactive:{en:'Inactive',ar:'موقوف'},
  actions:{en:'Actions',ar:'إجراءات'}, edit:{en:'Edit',ar:'تعديل'}, save:{en:'Save',ar:'حفظ'},
  cancel:{en:'Cancel',ar:'إلغاء'}, code:{en:'Code',ar:'الكود'},
  role:{en:'Role',ar:'الدور'}, displayName:{en:'Display Name',ar:'الاسم الظاهر'},
  linkedAgent:{en:'Linked Agent',ar:'الوكيل المرتبط'}, lastLogin:{en:'Last Login',ar:'آخر دخول'},
  resetPass:{en:'Reset password',ar:'إعادة تعيين كلمة المرور'},
  tempPassIs:{en:'Temporary password:',ar:'كلمة المرور المؤقتة:'},
  newAgentModal:{en:'Agent Details',ar:'بيانات الوكيل'}, newUserModal:{en:'User Account',ar:'حساب مستخدم'},
  masterModal:{en:'List Item',ar:'عنصر قائمة'},
  type:{en:'Type',ar:'النوع'}, valueAr:{en:'Arabic Value',ar:'القيمة بالعربية'}, valueEn:{en:'English Value',ar:'القيمة بالإنجليزية'},
  hotel:{en:'Hotels',ar:'الفنادق'}, port:{en:'Ports',ar:'المنافذ'}, city:{en:'Cities',ar:'المدن'},
  tripType:{en:'Trip Types',ar:'أنواع الرحلات'}, transportCompany:{en:'Transport Companies',ar:'شركات النقل'},
  oldPass:{en:'Current password',ar:'كلمة المرور الحالية'}, newPass:{en:'New password (min 6)',ar:'كلمة المرور الجديدة (6 أحرف على الأقل)'},
  saved:{en:'Saved ✓',ar:'تم الحفظ ✓'}, errWrongOld:{en:'Current password is wrong',ar:'كلمة المرور الحالية غير صحيحة'},
  errWeak:{en:'Password too short (min 6)',ar:'كلمة المرور قصيرة (6 أحرف على الأقل)'},
  errDuplicate:{en:'Username already exists',ar:'اسم المستخدم موجود مسبقاً'},
  errNoAgent:{en:'Select the linked agent',ar:'اختر الوكيل المرتبط'},
  errMissing:{en:'Fill the required fields',ar:'أكمل الحقول المطلوبة'},
  comingSoon:{en:'This module arrives in Phase 2 — Groups, booking requests and approvals.',ar:'هذه الوحدة قادمة في المرحلة الثانية — المجموعات وطلبات الحجز والاعتماد.'},
  welcome:{en:'Welcome',ar:'مرحباً'},
  time:{en:'Time',ar:'الوقت'}, user:{en:'User',ar:'المستخدم'}, action:{en:'Action',ar:'الإجراء'},
  details:{en:'Details',ar:'التفاصيل'},
  /* ── Phase 2 ── */
  kArriving7:{en:'Arrivals (7 days)',ar:'وصول خلال 7 أيام'}, kDeparting7:{en:'Departures (7 days)',ar:'مغادرة خلال 7 أيام'},
  addGroup:{en:'+ Add Group',ar:'+ إضافة مجموعة'}, newRequest:{en:'+ New Booking Request',ar:'+ طلب حجز جديد'},
  fileNo:{en:'File No',ar:'رقم الملف'}, groupCode:{en:'Group Code',ar:'رقم المجموعة في النظام'},
  groupName:{en:'Group Name',ar:'اسم المجموعة'}, leaderName:{en:'Leader Name',ar:'اسم المشرف'},
  leaderMobile:{en:'Leader Mobile',ar:'جوال المشرف'}, totalPax:{en:'Total Pax',ar:'إجمالي المعتمرين'},
  paxBreakdown:{en:'Pax Breakdown (e.g. 40+3)',ar:'تفصيل العدد (مثال 40+3)'},
  arrivedPax:{en:'Arrived Pax',ar:'عدد الواصلين'}, departedPax:{en:'Departed Pax',ar:'عدد المغادرين'},
  arrival:{en:'Arrival',ar:'الوصول'}, departure:{en:'Departure',ar:'المغادرة'},
  arrivalDate:{en:'Arrival Date',ar:'تاريخ الوصول'}, arrivalFlight:{en:'Arrival Flight',ar:'رحلة الوصول'},
  arrivalPort:{en:'Arrival Port',ar:'منفذ الوصول'},
  departureDate:{en:'Departure Date',ar:'تاريخ المغادرة'}, departureFlight:{en:'Departure Flight',ar:'رحلة المغادرة'},
  departurePort:{en:'Departure Port',ar:'منفذ المغادرة'},
  pax:{en:'Pax',ar:'العدد'}, groupModal:{en:'Group File',ar:'ملف المجموعة'},
  requestModal:{en:'Booking Request',ar:'طلب حجز'}, reviewModal:{en:'Review Request',ar:'مراجعة الطلب'},
  view:{en:'View',ar:'عرض'}, changeStatus:{en:'Status',ar:'الحالة'},
  submit:{en:'Submit Request',ar:'إرسال الطلب'}, approve:{en:'Approve',ar:'اعتماد'},
  reject:{en:'Reject',ar:'رفض'}, cancelReq:{en:'Cancel Request',ar:'إلغاء الطلب'},
  reviewNote:{en:'Review note (optional)',ar:'ملاحظة المراجعة (اختياري)'},
  submittedAt:{en:'Submitted',ar:'تاريخ الإرسال'}, reviewedBy:{en:'Reviewed By',ar:'تمت المراجعة بواسطة'},
  requestType:{en:'Type',ar:'النوع'}, newGroupReq:{en:'New Group',ar:'مجموعة جديدة'},
  all:{en:'All',ar:'الكل'},
  stNew:{en:'New',ar:'جديدة'}, stConfirmed:{en:'Confirmed',ar:'مؤكدة'},
  stInKingdom:{en:'In Kingdom',ar:'داخل المملكة'}, stDeparted:{en:'Departed',ar:'غادرت'},
  stClosed:{en:'Closed',ar:'مغلقة'}, stCancelled:{en:'Cancelled',ar:'ملغاة'},
  stPending:{en:'Pending',ar:'معلّق'}, stApproved:{en:'Approved',ar:'معتمد'}, stRejected:{en:'Rejected',ar:'مرفوض'},
  approvedMsg:{en:'Approved — group created ✓',ar:'تم الاعتماد — أنشئت المجموعة ✓'},
  rejectedMsg:{en:'Request rejected',ar:'تم رفض الطلب'},
  submittedMsg:{en:'Request submitted — awaiting approval',ar:'تم إرسال الطلب — بانتظار الاعتماد'},
  confirmCancel:{en:'Cancel this pending request?',ar:'إلغاء هذا الطلب المعلّق؟'},
  agentCol:{en:'Agent',ar:'الوكيل'}, noGroups:{en:'No groups yet',ar:'لا توجد مجموعات بعد'},
  noRequests:{en:'No requests',ar:'لا توجد طلبات'},
  /* ── Phase 3 ── */
  opsBoard:{en:'Operations Board',ar:'لوحة التشغيل'},
  back:{en:'← Back',ar:'← رجوع'},
  hotelsSec:{en:'Hotels',ar:'الفنادق'}, brnSec:{en:'Hotel BRN Agreements',ar:'اتفاقيات الفنادق BRN'},
  cateringSec:{en:'Catering BRN Agreements',ar:'اتفاقيات الإعاشة BRN'},
  ordersSec:{en:'Transport Orders',ar:'أوامر التشغيل'}, tripsSec:{en:'Trips',ar:'الرحلات'},
  infoSec:{en:'Group Info',ar:'بيانات المجموعة'},
  addHotel:{en:'+ Hotel',ar:'+ فندق'}, addBrn:{en:'+ BRN',ar:'+ اتفاقية'}, addCatering:{en:'+ Catering BRN',ar:'+ اتفاقية إعاشة'},
  addOrder:{en:'+ Order',ar:'+ أمر تشغيل'}, addTrip:{en:'+ Trip',ar:'+ رحلة'},
  cityLbl:{en:'City',ar:'المدينة'}, hotelLbl:{en:'Hotel',ar:'الفندق'},
  rooms:{en:'Rooms',ar:'عدد الغرف'}, checkIn:{en:'Check-in',ar:'تاريخ الدخول'},
  checkOut:{en:'Check-out',ar:'تاريخ الخروج'}, nights:{en:'Nights',ar:'عدد الليالي'},
  brnNumber:{en:'BRN Number',ar:'رقم الاتفاقية'}, roomsCount:{en:'Rooms Count',ar:'عدد الغرف'},
  company:{en:'Transport Company',ar:'شركة النقل'}, orderNo:{en:'Order No',ar:'رقم التشغيل'},
  brnLbl:{en:'BRN',ar:'حجز المنصة'},
  tripDate:{en:'Date',ar:'التاريخ'}, dayLbl:{en:'Day',ar:'اليوم'}, tripTypeLbl:{en:'Movement Type',ar:'نوع التحرك'},
  modeLbl:{en:'Trip Type',ar:'نوع الرحلة'},
  modeAir:{en:'Air',ar:'جواً'}, modeLand:{en:'Land',ar:'براً'}, modeSea:{en:'Sea',ar:'بحراً'},
  adults:{en:'Adults',ar:'الكبار'}, children:{en:'Children',ar:'الأطفال'}, infants:{en:'Infants',ar:'الرضع'},
  fromLbl:{en:'From',ar:'من'}, toLbl:{en:'To',ar:'إلى'},
  fromDetail:{en:'From Detail (port/hotel)',ar:'تفصيل الانطلاق (منفذ/فندق)'},
  toDetail:{en:'To Detail (port/hotel)',ar:'تفصيل الوصول (منفذ/فندق)'},
  timeLbl:{en:'Time',ar:'الوقت'}, flightNo:{en:'Flight No',ar:'رقم الرحلة'},
  buses:{en:'Buses',ar:'عدد الحافلات'}, driverName:{en:'Driver Name',ar:'اسم السائق'},
  driverMobile:{en:'Driver Mobile',ar:'جوال السائق'},
  bookingStatus:{en:'Booking',ar:'الحجز'}, tripStatus:{en:'Trip Status',ar:'حالة الرحلة'},
  hotelModal:{en:'Hotel Booking',ar:'حجز فندق'}, brnModal:{en:'BRN Agreement',ar:'اتفاقية فندق'},
  orderModal:{en:'Transport Order',ar:'أمر تشغيل'}, tripModal:{en:'Trip',ar:'رحلة'},
  linkedBooking:{en:'Linked Hotel Booking',ar:'حجز الفندق المرتبط'},
  linkedOrder:{en:'Linked Order',ar:'أمر التشغيل المرتبط'},
  deleteLbl:{en:'Delete',ar:'حذف'}, confirmDelete:{en:'Delete this item?',ar:'حذف هذا العنصر؟'},
  today:{en:'Today',ar:'اليوم'}, tomorrow:{en:'Tomorrow',ar:'غداً'}, next7:{en:'Next 7 days',ar:'٧ أيام القادمة'},
  noTrips:{en:'No trips',ar:'لا توجد رحلات'}, noHotels:{en:'No hotel bookings',ar:'لا توجد حجوزات فنادق'},
  noBrn:{en:'No BRN records',ar:'لا توجد اتفاقيات'}, noOrders:{en:'No transport orders',ar:'لا توجد أوامر تشغيل'},
  stTentative:{en:'Tentative',ar:'مبدئي'}, stBooked:{en:'Booked',ar:'محجوز'},
  stRequested:{en:'Requested',ar:'مطلوب'}, stDone:{en:'Done',ar:'منفّذة'},
  route:{en:'Route',ar:'المسار'}, driver:{en:'Driver',ar:'السائق'},
  /* ── Phase 4 ── */
  voucherLbl:{en:'Voucher',ar:'القسيمة'}, printLbl:{en:'Print',ar:'طباعة'},
  downloadPdf:{en:'Download PDF',ar:'تحميل PDF'}, closeLbl:{en:'Close',ar:'إغلاق'},
  generating:{en:'Generating PDF…',ar:'جاري إنشاء PDF…'},
  printOrder:{en:'Print Order',ar:'طباعة الأمر'},
  companyName:{en:'Company Name (Voucher Header)',ar:'اسم الشركة (ترويسة القسيمة)'},
  pdfFail:{en:'PDF generation failed',ar:'فشل إنشاء ملف PDF'},
  /* ── Wizard ── */
  wzGroup:{en:'Group & Travel',ar:'المجموعة والرحلة'}, wzHotels:{en:'Hotels',ar:'الفنادق'},
  wzBrn:{en:'Hotel BRN',ar:'اتفاقيات الفنادق BRN'}, wzCatering:{en:'Catering BRN',ar:'اتفاقيات الإعاشة BRN'},
  wzTrips:{en:'Trips',ar:'الرحلات'}, wzReview:{en:'Review & Submit',ar:'المراجعة والإرسال'},
  next:{en:'Next →',ar:'التالي ←'}, prev:{en:'← Back',ar:'→ رجوع'},
  addLine:{en:'+ Add',ar:'+ إضافة'}, removeLine:{en:'Remove',ar:'حذف'},
  wizardTitle:{en:'New Booking Request',ar:'طلب حجز جديد'},
  reqCreated:{en:'Request Submitted Successfully',ar:'تم إرسال الطلب بنجاح'},
  yourReqId:{en:'Your Request ID',ar:'رقم طلبك'},
  reqPendingNote:{en:'Your request is now pending operator approval. You can track its status under Requests.',ar:'طلبك الآن بانتظار اعتماد المشغّل. يمكنك متابعة حالته من صفحة الطلبات.'},
  goToRequests:{en:'Go to Requests',ar:'الذهاب إلى الطلبات'},
  newAnother:{en:'+ New Request',ar:'+ طلب جديد'},
  reviewGroupSec:{en:'Group Information',ar:'بيانات المجموعة'},
  emptyStep:{en:'None added — you can skip this step.',ar:'لا يوجد — يمكنك تخطي هذه الخطوة.'},
  stepXofY:{en:'Step',ar:'خطوة'},
  addGroupPair:{en:'+ Add another group',ar:'+ إضافة مجموعة أخرى'},
  travelType:{en:'Travel Type',ar:'نوع الرحلة'},
  arrivalSec:{en:'Arrival',ar:'الوصول'}, departureSec:{en:'Departure',ar:'المغادرة'},
  ticketCopy:{en:'Ticket Copy (PDF/Image)',ar:'صورة التذكرة (PDF/صورة)'},
  ticketUploaded:{en:'Ticket attached ✓',ar:'تم إرفاق التذكرة ✓'},
  rsvNo:{en:'RSV No.',ar:'رقم الحجز'}, viewTicket:{en:'View Ticket',ar:'عرض التذكرة'},
  atLeastOneGroup:{en:'Add at least one group code or name',ar:'أضف رقم أو اسم مجموعة واحدة على الأقل'},
  /* hotels/brn/trips steps */
  addHotelRow:{en:'+ Add Hotel',ar:'+ إضافة فندق'}, addTripRow:{en:'+ Add Trip',ar:'+ إضافة رحلة'},
  atLeastOneHotel:{en:'Add at least one hotel to continue',ar:'أضف فندقاً واحداً على الأقل للمتابعة'},
  hotelChainWarn:{en:'Note: last hotel check-out is not within ±1 day of departure — continuing anyway.',ar:'تنبيه: تاريخ خروج آخر فندق ليس ضمن ±يوم من المغادرة — سيتم المتابعة.'},
  brnSkip:{en:'Hotel BRN will be provided by the operator (skip)',ar:'اتفاقيات الفنادق BRN سيوفرها المشغّل (تخطي)'},
  cateringSkip:{en:'Catering BRN will be provided by the operator (skip)',ar:'اتفاقيات الإعاشة BRN سيوفرها المشغّل (تخطي)'},
  hotelsCopyBrn:{en:'↺ Same as Hotel BRN — dates',ar:'↺ نفس اتفاقيات الفنادق — التواريخ'},
  cateringCopyBrn:{en:'↺ Same as Hotel BRN — dates',ar:'↺ نفس اتفاقيات الفنادق — التواريخ'},
  cateringLbl:{en:'Catering Name',ar:'اسم الإعاشة'},
  cateringModal:{en:'Catering BRN Agreement',ar:'اتفاقية إعاشة'},
  noCatering:{en:'No catering agreements',ar:'لا توجد اتفاقيات إعاشة'},
  autoGen:{en:'Auto-generated from travel & hotels — edit as needed',ar:'مُولّدة تلقائياً من الرحلة والفنادق — عدّل حسب الحاجة'},
  regenTrips:{en:'↻ Regenerate',ar:'↻ إعادة توليد'},
  mazarat:{en:'Mazarat',ar:'مزارات'},
  arrivalTrip:{en:'Arrival',ar:'الوصول'}, departureTrip:{en:'Departure',ar:'المغادرة'},
  betweenCities:{en:'Between Cities',ar:'بين المدن'},
  checkInRule:{en:'(arrival day ±1)',ar:'(يوم الوصول ±1)'},
  checkOutRuleLast:{en:'(departure day ±1)',ar:'(يوم المغادرة ±1)'},
  lockedPrev:{en:'(= previous check-out)',ar:'(= خروج الفندق السابق)'},
  removeRow:{en:'Remove',ar:'حذف'}, dropHere:{en:'No rows yet',ar:'لا توجد صفوف'},
  /* batch: individuals, vehicles, return, ops */
  stReturned:{en:'Returned',ar:'معاد للوكيل'},
  returnToAgent:{en:'Back to Agent',ar:'إعادة للوكيل'},
  editResubmit:{en:'Edit & Resubmit',ar:'تعديل وإعادة إرسال'},
  bookingKind:{en:'Booking Type',ar:'نوع الحجز'},
  kindGroup:{en:'Group',ar:'مجموعة'}, kindIndividual:{en:'Individuals',ar:'أفراد'},
  wzHosting:{en:'Hosting Details',ar:'بيانات المستضيف'},
  hostName:{en:'Hoster Name',ar:'اسم المستضيف'},
  hostId:{en:'ID / Iqama Number',ar:'رقم الهوية / الإقامة'},
  dob:{en:'Date of Birth',ar:'تاريخ الميلاد'},
  nationality:{en:'Nationality',ar:'الجنسية'},
  residenceCity:{en:'Residence City',ar:'مدينة الإقامة'},
  address:{en:'Address',ar:'العنوان'},
  idCopy:{en:'ID Copy (PDF/Image)',ar:'صورة الهوية (PDF/صورة)'},
  idUploaded:{en:'ID attached ✓',ar:'تم إرفاق الهوية ✓'},
  viewId:{en:'View ID',ar:'عرض الهوية'},
  vehicleType:{en:'Vehicle Type',ar:'نوع المركبة'},
  vehicles:{en:'No. Vehicle',ar:'عدد المركبات'},
  capacity:{en:'Capacity',ar:'السعة'},
  detailCol:{en:'Detail',ar:'تفصيل'},
  portMode:{en:'Port Type',ar:'نوع المنفذ'},
  transportBy:{en:'Transportation provided by',ar:'النقل مقدم من'},
  byAgent:{en:'Agent',ar:'الوكيل'}, byOperator:{en:'Umrah Operator',ar:'مشغّل العمرة'},
  brnSkipped:{en:'Skipped — provided by operator',ar:'تم التخطي — يوفرها المشغّل'},
  cateringSkipped:{en:'Skipped — provided by operator',ar:'تم التخطي — يوفرها المشغّل'},
  summaryLbl:{en:'Summary',ar:'الملخص'},
  hostingSec:{en:'Hosting',ar:'الاستضافة'},
  returnedNote:{en:'This request was returned for edits — see the note, fix, and resubmit.',ar:'أُعيد هذا الطلب للتعديل — راجع الملاحظة ثم عدّل وأعد الإرسال.'},
  /* follow-up workflow */
  followup:{en:'Follow-up',ar:'المتابعة'},
  stageVisa:{en:'Visa',ar:'التأشيرات'}, stageTransport:{en:'Transport Confirm',ar:'تأكيد النقل'},
  stageOperation:{en:'In Operation',ar:'قيد التشغيل'}, stageCompleted:{en:'Completed',ar:'مكتملة'},
  moveNext:{en:'Next →',ar:'التالي ←'}, moveBack:{en:'← Back',ar:'→ رجوع'},
  stageNote:{en:'Note (optional)',ar:'ملاحظة (اختياري)'},
  moveToStage:{en:'Move to',ar:'نقل إلى'},
  tripsDoneLbl:{en:'trips done',ar:'رحلات منفذة'},
  noneInStage:{en:'Nothing here',ar:'لا يوجد'},
  department:{en:'Department',ar:'القسم'},
  deptNone:{en:'– (All)',ar:'– (الكل)'}, deptReviewer:{en:'Reviewer',ar:'المراجعة'},
  deptVisa:{en:'Visa',ar:'التأشيرات'}, deptOperations:{en:'Operations',ar:'العمليات'},
  yourQueue:{en:'Your queue',ar:'قائمتك'},
  stageProgress:{en:'Workflow Stage',ar:'مرحلة المتابعة'},
  loading:{en:'Loading…',ar:'جاري التحميل…'},
  fuWaiting:{en:'⏳ Waiting — assign Transport Order',ar:'⏳ بانتظار إسناد أمر تشغيل'},
  fuConfirmed:{en:'✅ Transport Confirmed',ar:'✅ تم تأكيد النقل'},
  quickTrips:{en:'Trips — Quick Actions',ar:'الرحلات — إجراءات سريعة'},
  assignDriver:{en:'Assign Driver',ar:'تعيين سائق'},
  saveDriver:{en:'Save',ar:'حفظ'},
  markDone:{en:'Done ✓',ar:'تمت ✓'},
  undoDone:{en:'Undo',ar:'تراجع'},
  openTrips:{en:'🚌 Trips',ar:'🚌 الرحلات'},
  today0:{en:'today',ar:'اليوم'}, day1:{en:'1 day',ar:'يوم'}, daysN:{en:'days',ar:'أيام'},
  inStage:{en:'in stage',ar:'في المرحلة'},
  /* batch */
  confirmTitle:{en:'Confirm',ar:'تأكيد'}, confirmYes:{en:'Yes, continue',ar:'نعم، متابعة'},
  agentRef:{en:'Agent Reference No.',ar:'الرقم المرجعي للوكيل'},
  confirmationNo:{en:'Confirmation No.',ar:'رقم التأكيد'},
  autoLbl:{en:'Auto',ar:'تلقائي'},
  reqNo:{en:'Req #',ar:'رقم الطلب'},
  editReqBadge:{en:'Edit',ar:'تعديل'},
  requestEdit:{en:'✏️ Request Changes',ar:'✏️ طلب تعديل'},
  editReqNote:{en:'Change request for group',ar:'طلب تعديل للمجموعة'},
  waitingVisa:{en:'⏳ Waiting Visa',ar:'⏳ بانتظار التأشيرة'},
  visaDone:{en:'✅ Visa Done',ar:'✅ التأشيرة جاهزة'},
  markVisaDone:{en:'Visa Done ✓',ar:'التأشيرة جاهزة ✓'},
  undoVisa:{en:'Undo Visa',ar:'تراجع'},
  gateVisa:{en:'Visa must be Done first',ar:'يجب إنهاء التأشيرة أولاً'},
  gateOrder:{en:'Transport must be Confirmed (order status = Booked)',ar:'يجب تأكيد النقل (حالة الأمر = مؤكد)'},
  gateTrips:{en:'All trips must be Done first',ar:'يجب إتمام جميع الرحلات أولاً'},
  mazaratOrder:{en:'Mazarat trip date must be before the next trip',ar:'تاريخ المزارات يجب أن يكون قبل الرحلة التالية'},
  notifications:{en:'Notifications',ar:'التنبيهات'},
  notifPending:{en:'Pending requests to review',ar:'طلبات بانتظار المراجعة'},
  notifVisa:{en:'Groups waiting visa',ar:'مجموعات بانتظار التأشيرة'},
  notifTransport:{en:'Groups waiting transport order',ar:'مجموعات بانتظار أمر تشغيل'},
  notifStale:{en:'Files stuck 5+ days in stage',ar:'ملفات متوقفة 5+ أيام'},
  notifTripsToday:{en:'Today trips not done yet',ar:'رحلات اليوم غير منفذة'},
  allClear:{en:'All clear ✓',ar:'لا توجد تنبيهات ✓'},
  returnedAlert:{en:'request(s) returned to you — tap to fix & resubmit',ar:'طلب/طلبات معادة إليك — اضغط للتعديل وإعادة الإرسال'},
  statusFilter:{en:'Status',ar:'الحالة'}, agentFilter:{en:'Agent',ar:'الوكيل'},
  allLbl:{en:'All',ar:'الكل'},
  exportPdf:{en:'🖨️ Export PDF',ar:'🖨️ تصدير PDF'},
  sumTrips:{en:'Trips',ar:'الرحلات'}, sumDone:{en:'Done',ar:'منفذة'},
  sumRemaining:{en:'Remaining',ar:'متبقية'}, sumVehicles:{en:'Vehicles',ar:'مركبات'},
  movementSchedule:{en:'Movement Schedule',ar:'جدول التحركات'},
  generatedAt:{en:'Generated',ar:'تاريخ الإنشاء'},
  dashPending:{en:'Pending Requests',ar:'طلبات معلقة'},
  dashMyReqs:{en:'My Open Requests',ar:'طلباتي المفتوحة'},
  viewAll:{en:'View all →',ar:'عرض الكل ←'},
  noData:{en:'Nothing here',ar:'لا يوجد'},
  changesSummary:{en:'Changes Summary',ar:'ملخص التغييرات'},
  noChanges:{en:'No field changes detected',ar:'لا توجد تغييرات في البيانات'},
  addedLbl:{en:'added',ar:'إضافة'}, removedLbl:{en:'removed',ar:'حذف'}, changedLbl:{en:'changed',ar:'تعديل'},
  openFull:{en:'Open Full Page',ar:'فتح الصفحة كاملة'},
  hotelsSec2:{en:'Hotels',ar:'الفنادق'}, tripsSec2:{en:'Trips',ar:'الرحلات'},
  /* batch: log, visa note, archive, logo, transport confirm */
  updatesLog:{en:'Updates Log',ar:'سجل التحديثات'},
  logTime:{en:'Date & Time',ar:'التاريخ والوقت'}, logUser:{en:'User',ar:'المستخدم'},
  logAction:{en:'Action',ar:'الإجراء'}, logDetails:{en:'Notes / Details',ar:'ملاحظات / تفاصيل'},
  noLogs:{en:'No activity yet',ar:'لا يوجد نشاط'},
  visaNoteTitle:{en:'Mark Visa as Done',ar:'تأكيد إصدار التأشيرة'},
  visaNoteLbl:{en:'Note (optional)',ar:'ملاحظة (اختياري)'},
  waitingConfirm:{en:'⏳ Waiting — Transport Confirmation',ar:'⏳ بانتظار تأكيد النقل'},
  transportConfirmed:{en:'✅ Transport Confirmed',ar:'✅ تم تأكيد النقل'},
  noOrderYet:{en:'⏳ No order yet',ar:'⏳ لا يوجد أمر تشغيل'},
  addOrderBtn:{en:'+ Order',ar:'+ أمر تشغيل'},
  archiveLbl:{en:'📦 Archive',ar:'📦 أرشفة'}, unarchiveLbl:{en:'📤 Unarchive',ar:'📤 إلغاء الأرشفة'},
  archivedLbl:{en:'Archived',ar:'مؤرشف'},
  showArchived:{en:'Show archived',ar:'عرض المؤرشف'},
  notArchivable:{en:'Only Departed / Closed / Cancelled groups can be archived',ar:'يمكن أرشفة المجموعات المغادرة/المغلقة/الملغاة فقط'},
  companyLogo:{en:'Company Logo',ar:'شعار الشركة'},
  uploadLogo:{en:'Upload logo',ar:'رفع الشعار'},
  currentLogo:{en:'Current logo',ar:'الشعار الحالي'},
  oldValue:{en:'Before',ar:'قبل'}, newValue:{en:'After',ar:'بعد'},
  fieldLbl:{en:'Field',ar:'الحقل'},
  autoStatusMsg:{en:'Group status updated automatically',ar:'تم تحديث حالة المجموعة تلقائياً'},
  /* transportation page */
  transportNav:{en:'Transportation',ar:'النقل'},
  tpOrders:{en:'Orders',ar:'أوامر التشغيل'},
  tpAwaiting:{en:'Awaiting Transport',ar:'بانتظار النقل'},
  tpPending:{en:'Pending Requests',ar:'طلبات معلقة'},
  kpiOrders:{en:'Total Orders',ar:'إجمالي الأوامر'},
  kpiConfirmed:{en:'Confirmed',ar:'مؤكدة'},
  kpiRequested:{en:'Awaiting Confirmation',ar:'بانتظار التأكيد'},
  kpiAwaitOrder:{en:'Groups Without Order',ar:'مجموعات بلا أمر'},
  kpiTrips:{en:'Total Trips',ar:'إجمالي الرحلات'},
  kpiNoDriver:{en:'Trips Without Driver',ar:'رحلات بلا سائق'},
  kpiVehicles:{en:'Vehicles',ar:'المركبات'},
  kpiPendingReq:{en:'Pending Requests',ar:'طلبات معلقة'},
  tripsCount:{en:'Trips',ar:'الرحلات'},
  noOrdersYet:{en:'No orders',ar:'لا توجد أوامر'},
  noAwaiting:{en:'All groups have confirmed transport ✓',ar:'جميع المجموعات لديها نقل مؤكد ✓'},
  noPendingReqs:{en:'No pending requests ✓',ar:'لا توجد طلبات معلقة ✓'},
  createOrder:{en:'Create Order',ar:'إنشاء أمر'},
  reqEditTrip:{en:'Trip Change',ar:'تعديل رحلة'},
  reqEditOrder:{en:'Order Change',ar:'تعديل أمر'},
  requestChange:{en:'Request Change',ar:'طلب تعديل'},
  changeNote:{en:'Reason / Note',ar:'السبب / ملاحظة'},
  submitRequest:{en:'Submit Request',ar:'إرسال الطلب'},
  reqSent:{en:'Request sent for approval',ar:'تم إرسال الطلب للموافقة'},
  pendingApproval:{en:'Pending operator approval',ar:'بانتظار موافقة المشغل'},
  reviewChange:{en:'Review Change Request',ar:'مراجعة طلب التعديل'},
  targetLbl:{en:'Target',ar:'الهدف'},
  notifTransportReqs:{en:'Transport change requests',ar:'طلبات تعديل النقل'},
  /* unified edit engine */
  cascadeTitle:{en:'This change affects other records',ar:'هذا التعديل يؤثر على سجلات أخرى'},
  cascadeIntro:{en:'Saving will automatically update the linked trips and hotels below to keep everything consistent:',ar:'سيتم تحديث الرحلات والفنادق المرتبطة تلقائياً للحفاظ على الاتساق:'},
  applyAll:{en:'Save & Update All',ar:'حفظ وتحديث الكل'},
  cascadedMsg:{en:'linked record(s) updated',ar:'سجل مرتبط تم تحديثه'},
  linkedFlight:{en:'⚠️ This trip shares its flight details with the group — changes sync both ways',ar:'⚠️ هذه الرحلة ترتبط ببيانات رحلة الطيران للمجموعة — التعديل يتزامن في الاتجاهين'},
  /* reports */
  reportsNav:{en:'Reports',ar:'التقارير'},
  rpModule:{en:'Report',ar:'التقرير'},
  rpGroups:{en:'Groups',ar:'المجموعات'}, rpTrips:{en:'Trips',ar:'الرحلات'},
  rpOrders:{en:'Transport Orders',ar:'أوامر التشغيل'}, rpHotels:{en:'Hotels',ar:'الفنادق'},
  rpBrn:{en:'BRN Agreements',ar:'اتفاقيات الفنادق'}, rpRequests:{en:'Requests',ar:'الطلبات'},
  rpSearch:{en:'Search (multiple values allowed)',ar:'بحث (يمكن إدخال عدة قيم)'},
  rpSearchHint:{en:'Separate multiple values with commas — e.g. 7, 12, RIYA',ar:'افصل بين القيم بفاصلة — مثال: 7، 12، ريا'},
  rpDateFrom:{en:'From',ar:'من تاريخ'}, rpDateTo:{en:'To',ar:'إلى تاريخ'},
  rpRun:{en:'🔍 Run Report',ar:'🔍 عرض التقرير'}, rpReset:{en:'Reset',ar:'إعادة تعيين'},
  rpExportPdf:{en:'📄 PDF',ar:'📄 PDF'}, rpExportXls:{en:'📊 Excel',ar:'📊 إكسل'},
  rpRows:{en:'rows',ar:'سجل'}, rpNoRows:{en:'No matching records',ar:'لا توجد سجلات مطابقة'},
  rpStage:{en:'Stage',ar:'المرحلة'},
  generating:{en:'Generating…',ar:'جارٍ الإنشاء…'},
  rpFilters:{en:'Filters',ar:'عوامل التصفية'},
  rowsPerPage:{en:'Rows:',ar:'صفوف:'},
  actionCentre:{en:'Action Centre',ar:'مركز الإجراءات'},
  typeCol:{en:'Type',ar:'النوع'},
  vFixBelow:{en:'Please complete the required fields:',ar:'يرجى استكمال الحقول المطلوبة:'},
  vPax:{en:'Total pax must be greater than 0',ar:'عدد المعتمرين يجب أن يكون أكبر من صفر'},
  vArrivalDate:{en:'Arrival date is required',ar:'تاريخ الوصول مطلوب'},
  vDepartureDate:{en:'Departure date is required',ar:'تاريخ المغادرة مطلوب'},
  vDepAfterArr:{en:'Departure must be after arrival',ar:'المغادرة يجب أن تكون بعد الوصول'},
  vArrivalPort:{en:'Arrival port is required',ar:'منفذ الوصول مطلوب'},
  vDeparturePort:{en:'Departure port is required',ar:'منفذ المغادرة مطلوب'},
  vLeader:{en:'Group leader name is required',ar:'اسم المشرف مطلوب'},
  vLeaderMobile:{en:'Leader mobile is required',ar:'جوال المشرف مطلوب'},
  vHotelCity:{en:'Hotel city required',ar:'مدينة الفندق مطلوبة'},
  vHotelName:{en:'Hotel name required',ar:'اسم الفندق مطلوب'},
  vCheckIn:{en:'Check-in required',ar:'تاريخ الدخول مطلوب'},
  vCheckOut:{en:'Check-out required',ar:'تاريخ الخروج مطلوب'},
  vCheckOutAfter:{en:'Check-out must be after check-in',ar:'الخروج يجب أن يكون بعد الدخول'},
  vBrnNumber:{en:'BRN number required',ar:'رقم الاتفاقية مطلوب'},
  vHostName:{en:'Host name required',ar:'اسم المضيف مطلوب'},
  vHostId:{en:'Host ID / Iqama required',ar:'هوية المضيف مطلوبة'},
  vHostCity:{en:'Residence city required',ar:'مدينة الإقامة مطلوبة'},
  vHostMobile:{en:'Host mobile required',ar:'جوال المضيف مطلوب'},
  vNoTrips:{en:'At least one trip is required',ar:'يجب إضافة رحلة واحدة على الأقل'},
  vTripDate:{en:'Trip date required',ar:'تاريخ الرحلة مطلوب'},
  vTripType:{en:'Movement type required',ar:'نوع التحرك مطلوب'},
  vVehicleType:{en:'Vehicle type required',ar:'نوع المركبة مطلوب'},
  vVehicleQty:{en:'Vehicle count required',ar:'عدد المركبات مطلوب'},
  vNoPastDate:{en:'Date',ar:'التاريخ'},
  vNoPastDate2:{en:'past dates are not allowed',ar:'لا يُسمح بتواريخ سابقة'},
  stCancelledTrip:{en:'Cancelled',ar:'ملغاة'},
  tripCancel:{en:'Cancel Trip',ar:'إلغاء الرحلة'},
  tripRestore:{en:'Restore',ar:'استعادة'},
  confirmCancelTrip:{en:'Cancel this trip? It will no longer count as outstanding work.',ar:'إلغاء هذه الرحلة؟ لن تُحتسب ضمن الأعمال المعلقة.'},
  vTripFuture:{en:"Can't mark as Done — this trip hasn't happened yet",ar:'لا يمكن اعتبارها منفذة — لم تحدث الرحلة بعد'},
  opsMissed:{en:'⚠️ Missed',ar:'⚠️ متأخرة'},
  copyTrip:{en:'Copy trip details',ar:'نسخ تفاصيل الرحلة'},
  plateNo:{en:'Plate No.',ar:'رقم اللوحة'},
  copied:{en:'Copied — ready to paste',ar:'تم النسخ — جاهز للّصق'},
  hotelNextHint:{en:'Need another city? Click "+ Add Hotel" — the next hotel\'s check-in follows this check-out automatically.',ar:'تحتاج مدينة أخرى؟ اضغط "+ إضافة فندق" — تاريخ دخول الفندق التالي يتبع تاريخ الخروج هذا تلقائياً.'},
  saveDraft:{en:'💾 Save Draft',ar:'💾 حفظ كمسودة'},
  draftSaved:{en:'Draft saved',ar:'تم حفظ المسودة'},
  stDraft:{en:'Draft',ar:'مسودة'},
  draftResume:{en:'Continue Draft',ar:'متابعة المسودة'},
  draftHint:{en:'Your progress is saved automatically as a draft',ar:'يتم حفظ تقدمك تلقائياً كمسودة'},
  deleteDraft:{en:'Delete draft',ar:'حذف المسودة'},
  /* dashboard analytics */
  kTotalGroups:{en:'Active Groups',ar:'المجموعات النشطة'},
  kTotalPax:{en:'Total Pax',ar:'إجمالي المعتمرين'},
  kArrToday:{en:'Arriving Today',ar:'قادمون اليوم'},
  kArrTomorrow:{en:'Arriving Tomorrow',ar:'قادمون غداً'},
  kArrNext7:{en:'Arriving in 7 Days',ar:'قادمون خلال ٧ أيام'},
  kInKingdom:{en:'In Kingdom',ar:'داخل المملكة'},
  kDepToday:{en:'Departing Today',ar:'مغادرون اليوم'},
  kDepNext7:{en:'Departing in 7 Days',ar:'مغادرون خلال ٧ أيام'},
  kDeparted:{en:'Departed',ar:'غادروا'},
  kOverdue:{en:'Overdue Departure',ar:'متأخرون عن المغادرة'},
  kAwaitTransport:{en:'Awaiting Transport',ar:'بانتظار النقل'},
  kNoArrDate:{en:'No Arrival Date',ar:'بدون تاريخ وصول'},
  stageDist:{en:'Stage Distribution',ar:'توزيع المراحل'},
  statusDist:{en:'Status Distribution',ar:'توزيع الحالات'},
  todayMovements:{en:"Today's Movements",ar:'تحركات اليوم'},
  overdueGroups:{en:'Overdue Departures',ar:'متأخرون عن المغادرة'},
  agentStats:{en:'Agent Statistics',ar:'إحصائيات الوكلاء'},
  daysLate:{en:'days late',ar:'أيام تأخير'},
  colGroups:{en:'Groups',ar:'المجموعات'}, colArr7:{en:'Arr. 7d',ar:'وصول ٧ي'},
  colDep7:{en:'Dep. 7d',ar:'مغادرة ٧ي'}, colNoOrder:{en:'No Order',ar:'بلا أمر'},
  noMovements:{en:'No movements scheduled today',ar:'لا توجد تحركات اليوم'},
  allOnTime:{en:'No overdue departures ✓',ar:'لا يوجد تأخير ✓'}
};
function t(k){ return (T[k]&&T[k][state.lang])||k; }

function applyLang(){
  document.documentElement.lang = state.lang;
  document.documentElement.dir = state.lang==='ar'?'rtl':'ltr';
  document.body.setAttribute('data-lang', state.lang);
  document.querySelectorAll('[data-i18n]').forEach(function(el){ el.textContent = t(el.getAttribute('data-i18n')); });
  document.querySelectorAll('[data-i18n-title]').forEach(function(el){ el.title = t(el.getAttribute('data-i18n-title')); });
  var lb = state.lang==='ar'?'EN':'عربي';
  document.getElementById('langBtnLogin').textContent = lb;
  document.getElementById('langBtnApp').textContent = lb;
  if (state.user) renderShell();
}
function toggleLang(){ state.lang = state.lang==='ar'?'en':'ar'; persistPrefs(); applyLang(); }
function toggleTheme(){ state.theme = state.theme==='dark'?'light':'dark'; document.body.setAttribute('data-theme',state.theme); persistPrefs(); }

/* prefs + token persistence (falls back to memory if storage blocked) */
function persistPrefs(){ try{ localStorage.setItem('crm_prefs', JSON.stringify({lang:state.lang,theme:state.theme})); }catch(e){} }
function persistToken(){ try{ if(state.token) localStorage.setItem('crm_token', state.token); else localStorage.removeItem('crm_token'); }catch(e){} }
(function restore(){
  try{
    var p = JSON.parse(localStorage.getItem('crm_prefs')||'{}');
    if(p.lang) state.lang=p.lang; if(p.theme) state.theme=p.theme;
    var tk = localStorage.getItem('crm_token'); if(tk) state.token=tk;
  }catch(e){}
  document.body.setAttribute('data-theme', state.theme);
})();

/* ════════════════ API WRAPPER ════════════════ */
var busyCount=0, busyTimer=null;
function busyShow(){
  busyCount++;
  if(busyCount===1){
    busyTimer=setTimeout(function(){
      if(busyCount>0){
        var el=document.getElementById('busy');
        document.getElementById('busyTxt').textContent=t('loading');
        el.style.display='flex';
      }
    },300); // only show if the call takes noticeable time
  }
}
function busyHide(){
  busyCount=Math.max(0,busyCount-1);
  if(busyCount===0){
    clearTimeout(busyTimer);
    var el=document.getElementById('busy'); if(el) el.style.display='none';
  }
}
/* Every authenticated call goes through the one dispatcher endpoint.
   Role and agent-code filtering happen server-side; this is only transport. */
function apiPost(url, body){
  return fetch(url, {
    method:'POST',
    headers:{ 'Content-Type':'application/json', 'Accept':'application/json' },
    body: JSON.stringify(body)
  }).then(function(r){
    return r.json().catch(function(){ return { ok:false, error:'SERVER' }; });
  });
}
function apiCall(action, payload){
  busyShow();
  return apiPost(API_URL, { token: state.token, action: action, payload: payload||{} })
    .then(function(res){
      busyHide();
      if(res && res.ok===false && res.error==='AUTH'){ forceLogin(t('errAuth')); return Promise.reject(res); }
      return res;
    }, function(e){
      busyHide(); toast(t('errServer'),'err'); return Promise.reject(e);
    });
}
function toast(msg, cls){
  var el=document.getElementById('toast');
  el.textContent=msg; el.className=cls||''; el.style.display='block';
  clearTimeout(el._t); el._t=setTimeout(function(){ el.style.display='none'; },3200);
}

/* ════════════════ AUTH FLOW ════════════════ */
function doLogin(){
  var u=document.getElementById('loginUser').value, p=document.getElementById('loginPass').value;
  var btn=document.getElementById('loginBtn'); btn.disabled=true; btn.innerHTML='<span class="mini-spin"></span>';
  apiPost(LOGIN_URL, { username:u, password:p }).then(function(res){
    btn.disabled=false; btn.innerHTML='<span>'+t('signIn')+'</span>';
    if(!res.ok){
      var err=document.getElementById('loginError');
      err.textContent = res.error==='DISABLED'?t('errDisabled'):res.error==='SERVER'?t('errServer'):t('errInvalid');
      err.style.display='block'; return;
    }
    state.token=res.token; state.user=res.user; persistToken();
    enterApp();
  }, function(){
    btn.disabled=false; btn.innerHTML='<span>'+t('signIn')+'</span>'; toast(t('errServer'),'err');
  });
}
function doLogout(){
  apiCall('auth.logout').finally(function(){ forceLogin(); });
}
function forceLogin(msg){
  state.token=null; state.user=null; persistToken();
  try{ localStorage.removeItem('crm_user'); }catch(e){}
  document.getElementById('appShell').style.display='none';
  document.getElementById('loginScreen').style.display='flex';
  if(msg){ var err=document.getElementById('loginError'); err.textContent=msg; err.style.display='block'; }
}
function enterApp(){
  document.getElementById('loginScreen').style.display='none';
  document.getElementById('appShell').style.display='block';
  state.view='dashboard';
  renderShell();
}
/* try resuming an existing session on load (token + user survive a refresh;
   if the token expired server-side, the first api call bounces back to login) */
window.addEventListener('load', function(){
  applyLang();
  document.getElementById('loginPass').addEventListener('keydown',function(e){ if(e.key==='Enter') doLogin(); });
  if(state.token){
    try{
      var u = JSON.parse(localStorage.getItem('crm_user')||'null');
      if(u){ state.user=u; enterApp(); }
    }catch(e){}
  }
});
/* keep user object for refresh-resume */
function persistUser(){ try{ localStorage.setItem('crm_user', JSON.stringify(state.user)); }catch(e){} }

/* ════════════════ SHELL + ROUTER ════════════════ */
var NAV = [
  { id:'dashboard', icon:'📊', label:'dashboard', roles:['operator','agent'] },
  { id:'agents',    icon:'🤝', label:'agents',    roles:['operator'] },
  { id:'users',     icon:'👥', label:'users',     roles:['operator'] },
  { id:'groups',    icon:'🕋', label:'groups',    roles:['operator','agent'] },
  { id:'requests',  icon:'📨', label:'requests',  roles:['operator','agent'], badge:true },
  { id:'followup',  icon:'🧭', label:'followup',  roles:['operator','agent'] },
  { id:'ops',       icon:'🚌', label:'opsBoard',  roles:['operator','agent'] },
  { id:'transport', icon:'🚐', label:'transportNav', roles:['operator','agent'] },
  { id:'reports',   icon:'📊', label:'reportsNav',   roles:['operator','agent'] },
  { id:'masters',   icon:'⚙️', label:'masters',   roles:['operator'] },
  { id:'logs',      icon:'📜', label:'logsNav',   roles:['operator'] }
];

function renderShell(){
  if(!state.user){ var bw=document.querySelector('.bell-wrap'); if(bw) bw.style.display='none'; return; }
  persistUser();
  document.getElementById('userName').textContent = state.user.displayName;
  document.getElementById('userRoleLbl').textContent = t(state.user.role);
  var bw2=document.querySelector('.bell-wrap'); if(bw2) bw2.style.display='';
  if(!state.cache.notifKpis) refreshBell(); else updateBell();
  renderSidebar();
  renderView();
}
function renderSidebar(){
  var sb='';
  NAV.forEach(function(n){
    if(n.roles.indexOf(state.user.role)===-1) return;
    var badge='';
    if(n.badge && state.user.role==='operator' && state.cache.pendingCount>0) badge='<span class="nav-badge">'+state.cache.pendingCount+'</span>';
    else if(n.soon) badge='<span class="soon">'+t('soon')+'</span>';
    sb+='<div class="nav-item'+(state.view===n.id?' active':'')+'" onclick="go(\''+n.id+'\')">'+
        '<span>'+n.icon+'</span><span>'+t(n.label)+'</span>'+badge+'</div>';
  });
  document.getElementById('sidebar').innerHTML=sb;
}
/* ─── table pagination ─── */
var PAGERS={};
var PAGE_SIZES=[10,20,40,50,100];
var PAGER_RENDER={
  req:function(){renderRequestRows();}, grp:function(){renderGroupRows();}, ops:function(){renderOpsRows();},
  ag:function(){renderAgentRows();}, us:function(){renderUserRows();}, lg:function(){renderLogRows();},
  ms:function(){ renderMasterRows(['port','vehicleType','companyName'].indexOf(currentMasterType)!==-1); },
  tp:function(){ renderTpRows(); },
  rp:function(){ rpRenderRows(); }
};
function pagerState(key){
  if(!PAGERS[key]) PAGERS[key]={page:1, per:10};
  if(!PAGERS[key].per) PAGERS[key].per=10;
  return PAGERS[key];
}
function pageSlice(key, rows, per){
  var pg=pagerState(key);
  if(per && !pg.perSet) pg.per=per;          /* caller default until the user picks a size */
  var size=pg.per;
  var pages=Math.max(1, Math.ceil(rows.length/size));
  if(pg.page>pages) pg.page=pages;
  if(pg.page<1) pg.page=1;
  var start=(pg.page-1)*size;
  return { rows: rows.slice(start,start+size), pagerHtml: pagerHtml(key,pg.page,pages,rows.length,size) };
}
function pagerHtml(key,page,pages,total,size){
  var sel='<select class="pager-size" onchange="pagerSize(\''+key+'\',this.value)">'+
    PAGE_SIZES.map(function(n){
      return '<option value="'+n+'"'+(n===size?' selected':'')+'>'+n+'</option>';
    }).join('')+'</select>';
  return '<div class="pager">'+
    '<span class="pager-per">'+t('rowsPerPage')+' '+sel+'</span>'+
    '<span class="pager-nav">'+
      '<button class="fu-btn" '+(page<=1?'disabled':'')+' onclick="pagerGo(\''+key+'\',-1)">‹</button>'+
      '<span>'+page+' / '+pages+'</span>'+
      '<button class="fu-btn" '+(page>=pages?'disabled':'')+' onclick="pagerGo(\''+key+'\',1)">›</button>'+
    '</span>'+
    '<span class="pager-tot">'+total+' '+t('rpRows')+'</span></div>';
}
function pagerSize(key, v){
  var pg=pagerState(key);
  pg.per=parseInt(v)||10; pg.perSet=true; pg.page=1;
  if(PAGER_RENDER[key]) PAGER_RENDER[key]();
}
function pagerGo(key,d){ pagerState(key).page+=d; if(PAGER_RENDER[key]) PAGER_RENDER[key](); }
function pagerReset(key){ if(PAGERS[key]) PAGERS[key].page=1; }

function go(v){ state.view=v; document.getElementById('sidebar').classList.remove('open'); closeBell(); renderShell(); }

/* ─── notification bell ─── */
function notifList(k){
  return [
    {n:k.pendingRequests, icon:'📨', txt:t('notifPending'), view:'requests'},
    {n:k.waitingVisa, icon:'🛂', txt:t('notifVisa'), view:'followup'},
    {n:k.transportWaiting, icon:'📋', txt:t('notifTransport'), view:'followup'},
    {n:k.staleStages, icon:'⏱', txt:t('notifStale'), view:'followup'},
    {n:k.tripsToday, icon:'🚌', txt:t('notifTripsToday'), view:'ops'},
    {n:k.transportReqs, icon:'🚐', txt:t('notifTransportReqs'), view:'transport'}
  ].filter(function(x){return x.n>0;});
}
function bellItems(){
  var k=state.cache.notifKpis||{}, role=state.cache.notifRole||(state.user&&state.user.role);
  if(role==='agent'){
    if(k.returnedRequests>0) return [{n:k.returnedRequests, icon:'↩️', txt:t('returnedAlert'), view:'requests'}];
    return [];
  }
  return notifList(k);
}
function updateBell(){
  var badge=document.getElementById('bellBadge'); if(!badge) return;
  var items=bellItems();
  var total=items.reduce(function(s,x){return s+(x.n||0);},0);
  if(total>0){ badge.textContent=total>99?'99+':total; badge.style.display='flex'; }
  else badge.style.display='none';
}
function toggleBell(ev){
  if(ev) ev.stopPropagation();
  var m=document.getElementById('bellMenu');
  if(m.style.display==='none'){ renderBellMenu(); m.style.display='block'; }
  else m.style.display='none';
}
function closeBell(){ var m=document.getElementById('bellMenu'); if(m) m.style.display='none'; }
function renderBellMenu(){
  var m=document.getElementById('bellMenu');
  var items=bellItems();
  var body = items.length
    ? items.map(function(x){
        return '<div class="bell-item" onclick="go(\''+x.view+'\')"><span style="font-size:1.15rem">'+x.icon+'</span>'+
          '<span class="bi-txt">'+x.txt+'</span><span class="nav-badge">'+x.n+'</span></div>';
      }).join('')
    : '<div class="bell-empty">'+t('allClear')+'</div>';
  m.innerHTML='<div class="bell-menu-hd">🔔 '+t('notifications')+'</div>'+body;
}
/* refresh bell counts without needing to open the dashboard */
function refreshBell(){
  apiCall('dashboard.get').then(function(res){
    if(!res.ok) return;
    state.cache.notifKpis=res.kpis; state.cache.notifRole=res.role;
    updateBell();
    var m=document.getElementById('bellMenu');
    if(m && m.style.display==='block') renderBellMenu();
  });
}
document.addEventListener('click', function(e){
  var w=document.querySelector('.bell-wrap');
  if(w && !w.contains(e.target)) closeBell();
});

function renderView(){
  var c=document.getElementById('content');
  if(state.view==='dashboard') return viewDashboard(c);
  if(state.view==='agents')    return viewAgents(c);
  if(state.view==='users')     return viewUsers(c);
  if(state.view==='masters')   return viewMasters(c);
  if(state.view==='logs')      return viewLogs(c);
  if(state.view==='groups')    return viewGroups(c);
  if(state.view==='requests')  return viewRequests(c);
  if(state.view==='groupDetail') return viewGroupDetail(c);
  if(state.view==='reqWizard')   return viewReqWizard(c);
  if(state.view==='ops')       return viewOps(c);
  if(state.view==='transport') return viewTransport(c);
  if(state.view==='reports')   return viewReports(c);
  if(state.view==='followup')  return viewFollowup(c);
  /* fallback */
  c.innerHTML='<div class="card" style="text-align:center;padding:50px 20px"><div style="font-size:2.4rem;margin-bottom:12px">🚧</div>'+
    '<div style="font-weight:800;margin-bottom:6px">'+t(state.view)+'</div>'+
    '<div style="color:var(--muted);font-size:.82rem">'+t('comingSoon')+'</div></div>';
}

/* ════════════════ VIEW: DASHBOARD ════════════════ */
function dashMiniCard(title, view, headers, rows){
  var body=rows.length
    ? '<div class="tbl-wrap"><table class="mini-tbl"><thead><tr>'+headers.map(function(h){return '<th>'+h+'</th>';}).join('')+'</tr></thead><tbody>'+
      rows.map(function(r){ return '<tr onclick="'+r.click+'">'+r.cells.map(function(cl){return '<td>'+cl+'</td>';}).join('')+'</tr>'; }).join('')+
      '</tbody></table></div>'
    : '<div style="color:var(--muted);font-size:.8rem;text-align:center;padding:14px">'+t('noData')+'</div>';
  return '<div class="card"><div class="card-hd"><div class="card-title">'+title+'</div>'+
    '<span class="link" style="font-size:.72rem" onclick="go(\''+view+'\')">'+t('viewAll')+'</span></div>'+body+'</div>';
}
function viewDashboard(c){
  c.innerHTML='<div class="page-title">'+t('welcome')+'، '+state.user.displayName+' 👋</div><div class="spinner"></div>';
  // one render, two sources: .get carries notifications + request lists, .stats carries analytics
  Promise.all([apiCall('dashboard.get'), apiCall('dashboard.stats')]).then(function(rs){
    var res=rs[0], d=rs[1];
    if(!res.ok || !d.ok) return;
    var isOp = res.role==='operator';
    var k=d.kpis, nk=res.kpis;
    state.cache.pendingCount = isOp ? nk.pendingRequests : 0;
    state.cache.notifKpis=nk; state.cache.notifRole=res.role;
    state.cache.dstats=d;
    renderSidebar();

    var html='<div class="page-title">'+t('welcome')+'، '+state.user.displayName+' 👋</div>';

    /* ── 1. KPI GRID (merged — single source of counters) ── */
    var kpi=function(val,label,color,icon,view){
      return '<div class="kpi"'+(view?' style="cursor:pointer" onclick="go(\''+view+'\')"':'')+'>'+
        '<div class="kpi-accent" style="background:var(--'+color+')"></div>'+
        '<div style="font-size:1.05rem;margin-bottom:2px">'+icon+'</div>'+
        '<div class="kpi-val" style="color:var(--'+color+')">'+val+'</div>'+
        '<div class="kpi-lbl">'+label+'</div></div>';
    };
    html+='<div class="kpi-grid">'+
      kpi(k.totalGroups,t('kTotalGroups'),'purple','🕋','groups')+
      kpi(k.totalPax,t('kTotalPax'),'blue','👥','groups')+
      kpi(k.arrivingToday,t('kArrToday'),'green','🛬','ops')+
      kpi(k.arrivingTomorrow,t('kArrTomorrow'),'teal','📅','ops')+
      kpi(k.arrivingNext7,t('kArrNext7'),'cyan','⏳','ops')+
      kpi(k.inKingdom,t('kInKingdom'),'green','🏠','groups')+
      kpi(k.departingToday,t('kDepToday'),'orange','🛫','ops')+
      kpi(k.departingNext7,t('kDepNext7'),'purple','📆','ops')+
      kpi(k.departed,t('kDeparted'),'muted','✈️','groups')+
      kpi(k.overdue,t('kOverdue'),'red','⚠️','groups')+
      kpi(k.awaitingTransport,t('kAwaitTransport'),'amber','🚐','transport')+
      kpi(k.noArrivalDate,t('kNoArrDate'),'red','❓','groups')+
      (isOp?kpi(k.agents,t('kAgents'),'blue','🤝','agents')+kpi(k.users,t('kUsers'),'teal','👤','users'):'')+
      '</div>';

    /* ── 2. ACTION CENTRE — what needs doing ── */
    if(isOp){
      var notifs=notifList(nk);
      html+='<div class="card"><div class="card-hd"><div class="card-title">🔔 '+t('actionCentre')+'</div>'+
        '<span class="card-badge">'+notifs.reduce(function(a,x){return a+x.n;},0)+'</span></div>';
      if(!notifs.length) html+='<div style="color:var(--muted);font-size:.82rem;text-align:center;padding:12px">'+t('allClear')+'</div>';
      else html+=notifs.map(function(x){
        return '<div class="bell-item" onclick="go(\''+x.view+'\')"><span style="font-size:1.1rem">'+x.icon+'</span>'+
          '<span class="bi-txt">'+x.txt+'</span><span class="nav-badge">'+x.n+'</span></div>';
      }).join('');
      html+='</div>';
    } else if(nk.returnedRequests>0){
      html+='<div class="card" style="border-color:rgba(245,158,11,.45);cursor:pointer" onclick="go(\'requests\')">'+
        '<div style="display:flex;align-items:center;gap:10px"><span style="font-size:1.4rem">↩️</span>'+
        '<div><b>'+nk.returnedRequests+'</b> '+t('returnedAlert')+'</div></div></div>';
    }

    /* ── 3. DISTRIBUTIONS ── */
    var bars=function(dist,colorOf,labelOf){
      var max=Math.max.apply(null,[1].concat(Object.keys(dist).map(function(x){return dist[x];})));
      return Object.keys(dist).map(function(key){
        var v=dist[key], pct=Math.round(v*100/max);
        return '<div class="bar-row"><span class="bar-lbl">'+labelOf(key)+'</span>'+
          '<span class="bar-track"><span class="bar-fill" style="width:'+pct+'%;background:var(--'+colorOf(key)+')"></span></span>'+
          '<span class="bar-val">'+v+'</span></div>';
      }).join('');
    };
    var stageColor={Visa:'amber',Transport:'blue',Operation:'purple',Completed:'green'};
    var statusColor={New:'muted',Confirmed:'blue',InKingdom:'green',Departed:'purple',Closed:'muted',Cancelled:'red'};
    html+='<div class="dash-3col">'+
      '<div class="card"><div class="card-hd"><div class="card-title">📊 '+t('stageDist')+'</div></div>'+
        bars(d.stageDist,function(x){return stageColor[x]||'blue';},function(x){return stageLabel(x);})+'</div>'+
      '<div class="card"><div class="card-hd"><div class="card-title">📈 '+t('statusDist')+'</div></div>'+
        bars(d.statusDist,function(x){return statusColor[x]||'blue';},function(x){return t('st'+x);})+'</div>'+
      '</div>';

    /* ── 4. TODAY'S MOVEMENTS (single table — the old duplicate is gone) ── */
    html+='<div class="card"><div class="card-hd"><div class="card-title">⏰ '+t('todayMovements')+'</div>'+
      '<span class="card-badge">'+d.todayTrips.length+'</span>'+
      '<span class="link" style="font-size:.72rem" onclick="go(\'ops\')">'+t('viewAll')+'</span></div>';
    if(d.todayTrips.length){
      html+='<div class="tbl-wrap"><table class="mini-tbl"><thead><tr>'+
        '<th>'+t('timeLbl')+'</th><th>'+t('tripTypeLbl')+'</th><th>'+t('fileNo')+'</th>'+
        (isOp?'<th>'+t('agentCol')+'</th>':'')+
        '<th>'+t('route')+'</th><th>'+t('driver')+'</th><th>'+t('tripStatus')+'</th></tr></thead><tbody>'+
        d.todayTrips.map(function(x){
          return '<tr onclick="openGroupPopup(\''+x.groupId+'\')">'+
            '<td dir="ltr"><b>'+esc(x.time)+'</b></td><td>'+esc(x.tripType)+'</td>'+
            '<td><b>'+esc(x.fileNo)+'</b> <span style="color:var(--muted);font-size:.66rem">'+esc(x.groupName)+'</span></td>'+
            (isOp?'<td>'+esc(x.agentName)+'</td>':'')+
            '<td>'+esc(x.from)+' → '+esc(x.to)+'</td><td>'+esc(x.driverName)+'</td>'+
            '<td>'+stBadge(x.tripStatus)+'</td></tr>';
        }).join('')+'</tbody></table></div>';
    } else html+='<div style="color:var(--muted);font-size:.82rem;text-align:center;padding:14px">'+t('noMovements')+'</div>';
    html+='</div>';

    /* ── 5. OVERDUE + REQUESTS, side by side ── */
    var overdueCard='<div class="card"'+(d.overdueList.length?' style="border-color:rgba(239,68,68,.4)"':'')+'>'+
      '<div class="card-hd"><div class="card-title">⚠️ '+t('overdueGroups')+'</div>'+
      '<span class="card-badge">'+d.overdueList.length+'</span></div>';
    if(d.overdueList.length){
      overdueCard+='<div class="tbl-wrap"><table class="mini-tbl"><thead><tr>'+
        '<th>'+t('fileNo')+'</th><th>'+t('groupName')+'</th>'+
        '<th>'+t('departureDate')+'</th><th></th></tr></thead><tbody>'+
        d.overdueList.map(function(x){
          return '<tr onclick="openGroupPopup(\''+x.groupId+'\')">'+
            '<td><b>'+esc(x.fileNo)+'</b></td><td>'+esc(x.groupName)+'</td>'+
            '<td dir="ltr">'+esc(x.departureDate)+'</td>'+
            '<td><span class="badge b-red">'+x.daysLate+' '+t('daysLate')+'</span></td></tr>';
        }).join('')+'</tbody></table></div>';
    } else overdueCard+='<div style="color:var(--muted);font-size:.82rem;text-align:center;padding:14px">'+t('allOnTime')+'</div>';
    overdueCard+='</div>';

    var reqCard = isOp
      ? dashMiniCard('📨 '+t('dashPending'),'requests',
          ['#',t('agentCol'),t('groupName'),t('pax')],
          (res.pendingRequests||[]).map(function(r){
            return {cells:['<span dir="ltr" style="font-weight:700">'+esc(r.requestId)+'</span>',
              esc(agentNameOf(r.agentCode)),esc(r.groupName),esc(r.totalPax)], click:"go('requests')"};
          }))
      : dashMiniCard('📨 '+t('dashMyReqs'),'requests',
          ['#',t('groupName'),t('status')],
          (res.openRequests||[]).map(function(r){
            return {cells:['<span dir="ltr" style="font-weight:700">'+esc(r.requestId)+'</span>',
              esc(r.groupName),stBadge(r.status)], click:"go('requests')"};
          }));
    html+='<div class="dash-3col">'+overdueCard+reqCard+'</div>';

    /* ── 6. AGENT STATS (operator) ── */
    if(isOp && d.agentStats.length){
      html+='<div class="card"><div class="card-hd"><div class="card-title">📋 '+t('agentStats')+'</div>'+
        '<span class="card-badge">'+d.agentStats.length+'</span></div>'+
        '<div class="tbl-wrap"><table class="mini-tbl"><thead><tr>'+
        '<th>'+t('agentCol')+'</th><th>'+t('colGroups')+'</th><th>'+t('pax')+'</th>'+
        '<th>'+t('colArr7')+'</th><th>'+t('colDep7')+'</th><th>'+t('kInKingdom')+'</th>'+
        '<th>'+t('colNoOrder')+'</th><th>'+t('kOverdue')+'</th></tr></thead><tbody>'+
        d.agentStats.map(function(a){
          return '<tr><td><b>'+esc(a.agentName)+'</b></td><td>'+a.groups+'</td><td>'+a.pax+'</td>'+
            '<td>'+a.arriving7+'</td><td>'+a.departing7+'</td><td>'+a.inKingdom+'</td>'+
            '<td>'+(a.noOrder?'<span style="color:var(--amber);font-weight:800">'+a.noOrder+'</span>':'0')+'</td>'+
            '<td>'+(a.overdue?'<span style="color:var(--red);font-weight:800">'+a.overdue+'</span>':'0')+'</td></tr>';
        }).join('')+'</tbody></table></div></div>';
    }

    /* ── 7. RECENT ACTIVITY (operator) ── */
    if(isOp){
      html+='<div class="card"><div class="card-hd"><div class="card-title">📜 '+t('recentActivity')+'</div></div>'+
        '<div id="dashLogs"><div class="spinner"></div></div></div>';
    }

    c.innerHTML=html;
    updateBell();

    if(isOp){
      apiCall('logs.list',{limit:8}).then(function(lr){
        if(!lr.ok) return;
        var rows=lr.rows.map(function(l){
          return '<tr><td dir="ltr">'+l.timestamp+'</td><td>'+l.username+'</td><td><span class="badge b-blue">'+l.action+'</span></td><td>'+(l.details||'–')+'</td></tr>';
        }).join('');
        var box=document.getElementById('dashLogs'); if(!box) return;
        box.innerHTML='<div class="tbl-wrap"><table class="mini-tbl"><thead><tr><th>'+t('time')+'</th><th>'+t('user')+'</th><th>'+t('action')+'</th><th>'+t('details')+'</th></tr></thead><tbody>'+
          (rows||'<tr class="no-data"><td colspan="4">—</td></tr>')+'</tbody></table></div>';
      });
    }
  });
}

/* ════════════════ VIEW: AGENTS ════════════════ */
function viewAgents(c){
  c.innerHTML='<div class="page-title">🤝 '+t('agents')+
    '<button class="btn btn-primary btn-sm" onclick="openAgentModal()">'+t('addAgent')+'</button></div>'+
    '<div class="card"><div class="card-hd"><div class="tbl-search"><input id="agSearch" placeholder="'+t('search')+'" oninput="pagerReset(&quot;ag&quot;);renderAgentRows()"/></div></div>'+
    '<div class="tbl-wrap"><table><thead><tr><th>'+t('code')+'</th><th>'+t('agentName')+'</th><th>'+t('country')+'</th><th>'+t('contactName')+'</th><th>'+t('mobile')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead>'+
    '<tbody id="agBody"><tr class="no-data"><td colspan="7"><div class="spinner"></div></td></tr></tbody></table></div><div id="agPager"></div></div>';
  apiCall('agents.list').then(function(res){
    if(!res.ok) return; state.cache.agents=res.rows; renderAgentRows();
  });
}
function renderAgentRows(){
  var q=(document.getElementById('agSearch')||{}).value||''; q=q.toLowerCase();
  var rows=(state.cache.agents||[]).filter(function(a){
    return !q || (a.agentName+a.country+a.contactName+a.mobile+a.agentCode).toLowerCase().indexOf(q)!==-1;
  });
  var ps=pageSlice('ag', rows);
  var pgEl=document.getElementById('agPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  rows=ps.rows;
  var html=rows.map(function(a){
    return '<tr><td dir="ltr">'+a.agentCode+'</td><td>'+a.agentName+'</td><td>'+(a.country||'–')+'</td><td>'+(a.contactName||'–')+'</td>'+
      '<td dir="ltr">'+(a.mobile||'–')+'</td><td><span class="badge '+(a.status==='active'?'b-green':'b-red')+'">'+t(a.status==='active'?'active':'inactive')+'</span></td>'+
      '<td><span class="link" onclick="openAgentModal(\''+a.agentCode+'\')">'+t('edit')+'</span></td></tr>';
  }).join('');
  document.getElementById('agBody').innerHTML=html||'<tr class="no-data"><td colspan="7">—</td></tr>';
}
function openAgentModal(code){
  var a=(state.cache.agents||[]).find(function(x){return x.agentCode===code;})||{};
  openModal(t('newAgentModal'),
    field('mAgName',t('agentName'),a.agentName)+
    field('mAgCountry',t('country'),a.country)+
    field('mAgContact',t('contactName'),a.contactName)+
    field('mAgMobile',t('mobile'),a.mobile)+
    field('mAgEmail',t('email'),a.email)+
    (code?selectField('mAgStatus',t('status'),[['active',t('active')],['inactive',t('inactive')]],a.status):'')+
    field('mAgNotes',t('notes'),a.notes),
    function(){
      var p={ agentCode:code||'', agentName:val('mAgName'), country:val('mAgCountry'),
        contactName:val('mAgContact'), mobile:val('mAgMobile'), email:val('mAgEmail'),
        status:code?val('mAgStatus'):'active', notes:val('mAgNotes') };
      if(!p.agentName){ toast(t('errMissing'),'err'); return; }
      apiCall('agents.save',p).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); viewAgents(document.getElementById('content')); }
        else toast(t('errMissing'),'err');
      });
    });
}

/* ════════════════ VIEW: USERS ════════════════ */
function viewUsers(c){
  c.innerHTML='<div class="page-title">👥 '+t('users')+
    '<button class="btn btn-primary btn-sm" onclick="openUserModal()">'+t('addUser')+'</button></div>'+
    '<div class="card"><div class="tbl-wrap"><table><thead><tr><th>'+t('username')+'</th><th>'+t('displayName')+'</th><th>'+t('role')+'</th><th>'+t('linkedAgent')+'</th><th>'+t('lastLogin')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead>'+
    '<tbody id="usBody"><tr class="no-data"><td colspan="7"><div class="spinner"></div></td></tr></tbody></table></div><div id="usPager"></div></div>';
  Promise.all([apiCall('users.list'), apiCall('agents.list')]).then(function(rs){
    if(!rs[0].ok) return;
    state.cache.users=rs[0].rows; state.cache.agents=rs[1].ok?rs[1].rows:[];
    renderUserRows();
  });
}
function renderUserRows(){
  var rows=(state.cache.users||[]);
  var ps=pageSlice('us', rows);
  var pgEl=document.getElementById('usPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  var html=ps.rows.map(function(u){
    var ag=(state.cache.agents||[]).find(function(a){return a.agentCode===u.agentCode;});
    return '<tr><td dir="ltr">'+u.username+'</td><td>'+u.displayName+'</td><td><span class="badge '+(u.role==='operator'?'b-blue':'b-amber')+'">'+t(u.role)+'</span>'+(u.department?' <span style="font-size:.62rem;color:var(--muted)">'+t('dept'+u.department.charAt(0).toUpperCase()+u.department.slice(1))+'</span>':'')+'</td>'+
      '<td>'+(ag?ag.agentName:'–')+'</td><td>'+(u.lastLogin||'–')+'</td>'+
      '<td><span class="badge '+(u.status==='active'?'b-green':'b-red')+'">'+t(u.status==='active'?'active':'inactive')+'</span></td>'+
      '<td><span class="link" onclick="openUserModal(\''+u.userId+'\')">'+t('edit')+'</span> · <span class="link" onclick="resetUserPass(\''+u.userId+'\')">🔑</span></td></tr>';
  }).join('');
  document.getElementById('usBody').innerHTML=html||'<tr class="no-data"><td colspan="7">—</td></tr>';
}
function agentOptions(sel){
  return (state.cache.agents||[]).filter(function(a){return a.status==='active';})
    .map(function(a){ return [a.agentCode, a.agentName]; });
}
function openUserModal(userId){
  var u=(state.cache.users||[]).find(function(x){return x.userId===userId;})||{};
  var isNew=!userId;
  openModal(t('newUserModal'),
    (isNew?field('mUsName',t('username'),''):'')+
    (isNew?field('mUsPass',t('password'),'','password'):'')+
    field('mUsDisplay',t('displayName'),u.displayName)+
    selectField('mUsRole',t('role'),[['operator',t('operator')],['agent',t('agent')]],u.role||'agent')+
    selectField('mUsDept',t('department'),[['',t('deptNone')],['reviewer',t('deptReviewer')],['visa',t('deptVisa')],['operations',t('deptOperations')]],u.department||'')+
    selectField('mUsAgent',t('linkedAgent'),[['','–']].concat(agentOptions()),u.agentCode)+
    (!isNew?selectField('mUsStatus',t('status'),[['active',t('active')],['inactive',t('inactive')]],u.status):''),
    function(){
      var p={ userId:userId||'', username:isNew?val('mUsName'):u.username, password:isNew?val('mUsPass'):'',
        displayName:val('mUsDisplay'), role:val('mUsRole'), agentCode:val('mUsAgent'),
        department:val('mUsDept'),
        status:isNew?'active':val('mUsStatus') };
      apiCall('users.save',p).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); viewUsers(document.getElementById('content')); }
        else toast(t(res.error==='DUPLICATE'?'errDuplicate':res.error==='WEAK'?'errWeak':res.error==='NO_AGENT'?'errNoAgent':'errMissing'),'err');
      });
    });
}
function resetUserPass(userId){
  apiCall('auth.resetPassword',{userId:userId}).then(function(res){
    if(res.ok) openModal(t('resetPass'),
      '<div style="text-align:center;padding:10px"><div style="color:var(--muted);font-size:.8rem;margin-bottom:8px">'+t('tempPassIs')+'</div>'+
      '<div dir="ltr" style="font-size:1.3rem;font-weight:900;letter-spacing:1px;background:var(--input-bg);border:1px solid var(--border);border-radius:10px;padding:12px">'+res.tempPassword+'</div></div>', null);
  });
}

/* ════════════════ VIEW: MASTERS ════════════════ */
var MASTER_TYPES=['hotel','transportCompany','port','city','tripType','vehicleType','companyName'];
var currentMasterType='hotel';
function viewMasters(c){
  var tabs=MASTER_TYPES.map(function(mt){
    return '<button class="btn btn-sm '+(currentMasterType===mt?'btn-primary':'btn-ghost')+'" onclick="pagerReset(&quot;ms&quot;);currentMasterType=\''+mt+'\';viewMasters(document.getElementById(\'content\'))">'+t(mt)+'</button>';
  }).join(' ');
  var hasMeta = currentMasterType==='port'||currentMasterType==='vehicleType'||currentMasterType==='companyName';
  var metaHd = currentMasterType==='port'?t('portMode'):currentMasterType==='vehicleType'?t('capacity'):currentMasterType==='companyName'?t('companyLogo'):t('detailCol');
  c.innerHTML='<div class="page-title">⚙️ '+t('masters')+
    '<button class="btn btn-primary btn-sm" onclick="openMasterModal()">'+t('addItem')+'</button></div>'+
    '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">'+tabs+'</div>'+
    '<div class="card"><div class="tbl-wrap"><table><thead><tr><th>'+t('valueAr')+'</th><th>'+t('valueEn')+'</th>'+
    (hasMeta?'<th>'+metaHd+'</th>':'')+
    '<th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead>'+
    '<tbody id="msBody"><tr class="no-data"><td colspan="5"><div class="spinner"></div></td></tr></tbody></table></div><div id="msPager"></div></div>';
  apiCall('masters.list',{type:currentMasterType}).then(function(res){
    if(!res.ok) return; state.cache.masters=res.rows;
    renderMasterRows(hasMeta);
  });
}
function renderMasterRows(hasMeta){
  var rows=(state.cache.masters||[]);
  var ps=pageSlice('ms', rows);
  var pgEl=document.getElementById('msPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  var html=ps.rows.map(function(m){
    var metaCell='';
    if(hasMeta){
      var mv=m.meta||'–';
      if(currentMasterType==='port'&&m.meta) mv=t('mode'+m.meta);
      if(currentMasterType==='companyName'&&m.meta) mv='<img src="'+escapeAttr(m.meta)+'" alt="logo" style="max-height:28px;max-width:80px;vertical-align:middle;background:#fff;border-radius:4px;padding:2px"/>';
      metaCell='<td>'+mv+'</td>';
    }
    return '<tr><td>'+(m.value_ar||'–')+'</td><td>'+(m.value_en||'–')+'</td>'+metaCell+
      '<td><span class="badge '+(m.active==='yes'?'b-green':'b-red')+'">'+t(m.active==='yes'?'active':'inactive')+'</span></td>'+
      '<td><span class="link" onclick="openMasterModal(\''+m.masterId+'\')">'+t('edit')+'</span></td></tr>';
  }).join('');
  document.getElementById('msBody').innerHTML=html||'<tr class="no-data"><td colspan="5">—</td></tr>';
}
function masterMetaField(type, meta){
  if(type==='port'){
    return '<div class="field" id="mMsMetaWrap"><label>'+t('portMode')+'</label><select id="mMsMeta">'+
      selectFieldOpts([['','–'],['Air',t('modeAir')],['Land',t('modeLand')],['Sea',t('modeSea')]],meta||'')+'</select></div>';
  }
  if(type==='vehicleType'){
    return '<div class="field" id="mMsMetaWrap"><label>'+t('capacity')+' *</label><input id="mMsMeta" type="number" min="1" value="'+escapeAttr(meta)+'"/></div>';
  }
  if(type==='companyName'){
    return '<div class="field" id="mMsMetaWrap"><label>'+t('companyLogo')+'</label>'+
      (meta?'<div style="margin-bottom:6px"><img src="'+escapeAttr(meta)+'" alt="logo" style="max-height:54px;max-width:150px;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff"/><div style="font-size:.64rem;color:var(--muted)">'+t('currentLogo')+'</div></div>':'')+
      '<input type="file" id="mMsLogo" accept="image/*" style="font-size:.74rem"/>'+
      '<input type="hidden" id="mMsMeta" value="'+escapeAttr(meta)+'"/>'+
      '<div style="font-size:.63rem;color:var(--muted)">'+t('uploadLogo')+'</div></div>';
  }
  return '<div id="mMsMetaWrap"></div>';
}
function onMasterTypeChange(){
  var type=val('mMsType');
  var wrap=document.getElementById('mMsMetaWrap');
  if(wrap) wrap.outerHTML=masterMetaField(type,'');
}
function openMasterModal(id){
  var m=(state.cache.masters||[]).find(function(x){return x.masterId===id;})||{};
  var typeSel='<div class="field"><label>'+t('type')+'</label><select id="mMsType" onchange="onMasterTypeChange()">'+
    selectFieldOpts(MASTER_TYPES.map(function(mt){return [mt,t(mt)];}),m.type||currentMasterType)+'</select></div>';
  openModal(t('masterModal'),
    typeSel+
    field('mMsAr',t('valueAr'),m.value_ar)+
    field('mMsEn',t('valueEn'),m.value_en)+
    masterMetaField(m.type||currentMasterType, m.meta)+
    (id?selectField('mMsActive',t('status'),[['yes',t('active')],['no',t('inactive')]],m.active):''),
    function(){
      var type=val('mMsType');
      var p={ masterId:id||'', type:type, value_ar:val('mMsAr'), value_en:val('mMsEn'),
        meta:val('mMsMeta'), active:id?val('mMsActive'):'yes' };
      if(type==='vehicleType' && !(parseInt(p.meta)>0)){ toast(t('errMissing'),'err'); return; }
      var send=function(){
        apiCall('masters.save',p).then(function(res){
          if(res.ok){ toast(t('saved'),'ok'); closeModal(); state.cache.mtypes={}; state.cache.ports=null; state.cache.docCompany=null; viewMasters(document.getElementById('content')); }
          else toast(t('errMissing'),'err');
        });
      };
      var lf=document.getElementById('mMsLogo');
      if(type==='companyName' && lf && lf.files && lf.files[0]){
        var f=lf.files[0], rd=new FileReader();
        rd.onload=function(){ p.logoBase64=rd.result; p.logoName=f.name; send(); };
        rd.onerror=function(){ toast(t('errServer'),'err'); };
        rd.readAsDataURL(f);
      } else send();
    });
}

/* ════════════════ VIEW: LOGS ════════════════ */
function viewLogs(c){
  c.innerHTML='<div class="page-title">📜 '+t('logsNav')+'</div>'+
    '<div class="card"><div class="tbl-wrap"><table><thead><tr><th>'+t('time')+'</th><th>'+t('user')+'</th><th>'+t('action')+'</th><th>'+t('details')+'</th></tr></thead>'+
    '<tbody id="lgBody"><tr class="no-data"><td colspan="4"><div class="spinner"></div></td></tr></tbody></table></div><div id="lgPager"></div></div>';
  apiCall('logs.list',{limit:500}).then(function(res){
    if(!res.ok) return;
    state.cache.logs=res.rows;
    renderLogRows();
  });
}
function renderLogRows(){
  var rows=(state.cache.logs||[]);
  var ps=pageSlice('lg', rows);
  var pgEl=document.getElementById('lgPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  var html=ps.rows.map(function(l){
    return '<tr><td dir="ltr">'+l.timestamp+'</td><td>'+l.username+'</td><td><span class="badge b-blue">'+l.action+'</span></td><td>'+(l.details||'–')+'</td></tr>';
  }).join('');
  document.getElementById('lgBody').innerHTML=html||'<tr class="no-data"><td colspan="4">—</td></tr>';
}

/* ════════════════ PHASE 2: SHARED HELPERS ════════════════ */
var GROUP_STATUSES=['New','Confirmed','InKingdom','Departed','Closed','Cancelled'];
var TRIP_MODES=['Air','Land','Sea'];
function modeOptions(sel){ return selectFieldOpts([['','–']].concat(TRIP_MODES.map(function(m){return [m,t('mode'+m)];})), sel); }
function modeLabel(m){ return m?t('mode'+m):''; }
var STATUS_BADGE={New:'b-blue',Confirmed:'b-teal',InKingdom:'b-green',Departed:'b-purple',Closed:'b-gray',Cancelled:'b-red',
                  Pending:'b-amber',Approved:'b-green',Rejected:'b-red',Returned:'b-amber',Draft:'b-gray'};
var TRIP_STATUSES=['Pending','Done','Cancelled'];
function stBadge(s){ return '<span class="badge '+(STATUS_BADGE[s]||'b-gray')+'">'+t('st'+s)+'</span>'; }
function esc(v){ return (v===undefined||v===null||v==='')?'–':v.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;'); }

/* Pax split: adults + children + infants with live auto-total */
function paxFieldsHtml(g){
  g=g||{};
  return '<div class="form-grid">'+
    '<div class="field"><label>'+t('adults')+'</label><input id="gAdults" type="number" min="0" value="'+escapeAttr(g.adults)+'" oninput="onPaxInput()"/></div>'+
    '<div class="field"><label>'+t('children')+'</label><input id="gChildren" type="number" min="0" value="'+escapeAttr(g.children)+'" oninput="onPaxInput()"/></div>'+
    '<div class="field"><label>'+t('infants')+'</label><input id="gInfants" type="number" min="0" value="'+escapeAttr(g.infants)+'" oninput="onPaxInput()"/></div>'+
    '<div class="field"><label>'+t('totalPax')+'</label><input id="gPax" type="number" readonly value="'+escapeAttr(g.totalPax)+'" style="background:var(--bg2);font-weight:800"/></div>'+
    '</div>'+
    '<div class="field"><label>'+t('paxBreakdown')+'</label><input id="gPaxBd" value="'+escapeAttr(g.paxBreakdown)+'"/></div>';
}
function onPaxInput(){
  var a=parseInt(val('gAdults'))||0, c=parseInt(val('gChildren'))||0, i=parseInt(val('gInfants'))||0;
  var el=document.getElementById('gPax'); if(el) el.value=(a+c+i)||'';
}
function paxValues(){
  return { adults:val('gAdults'), children:val('gChildren'), infants:val('gInfants'),
           totalPax:val('gPax'), paxBreakdown:val('gPaxBd') };
}

function loadPorts(){
  if(state.cache.ports) return Promise.resolve(state.cache.ports);
  return apiCall('masters.list',{type:'port'}).then(function(res){
    state.cache.ports = res.ok ? res.rows.filter(function(m){return m.active==='yes';}) : [];
    return state.cache.ports;
  });
}
function portOptions(selected, mode){
  var opts=[['','–']];
  (state.cache.ports||[]).forEach(function(p){
    if(mode && p.meta && p.meta!==mode) return; // filter by port type; legacy (no meta) always shown
    opts.push([p.value_en, state.lang==='ar'?(p.value_ar||p.value_en):(p.value_en||p.value_ar)]);
  });
  // preserve a selected value not in the filtered list
  if(selected && !opts.some(function(o){return o[0]===selected;})) opts.push([selected,selected]);
  return selectFieldOpts(opts, selected);
}
function selectFieldOpts(options, selected){
  return options.map(function(o){
    return '<option value="'+escapeAttr(o[0])+'"'+(o[0]===selected?' selected':'')+'>'+o[1]+'</option>';
  }).join('');
}

/* Group form fields (shared by operator group modal + agent request form) */
function groupFormHtml(g, isOperator){
  g=g||{};
  var agentSel='';
  if(isOperator){
    agentSel='<div class="field"><label>'+t('agentCol')+' *</label><select id="gAgent">'+
      selectFieldOpts([['','–']].concat(agentOptions()), g.agentCode)+'</select></div>';
  }
  return agentSel+
    '<div class="form-grid">'+
    (isOperator?field('gFileNo',t('fileNo'),g.fileNo):'')+
    field('gCode',t('groupCode'),g.groupCode)+
    '</div>'+
    field('gName',t('groupName')+' *',g.groupName)+
    '<div class="form-grid">'+
    field('gLeader',t('leaderName')+' *',g.leaderName)+
    field('gLeaderMob',t('leaderMobile')+' *',g.leaderMobile)+
    field('gAgentRef',t('agentRef'),g.agentRef)+
    '</div>'+
    paxFieldsHtml(g)+
    '<div class="form-grid">'+
    (isOperator?field('gArrPax',t('arrivedPax'),g.arrivedPax,'number'):'')+
    (isOperator?field('gDepPax',t('departedPax'),g.departedPax,'number'):'')+
    field('gArrDate',t('arrivalDate')+' *',g.arrivalDate,'date')+
    field('gArrFlight',t('arrivalFlight'),g.arrivalFlight)+
    '<div class="field"><label>'+t('arrivalPort')+' *</label><select id="gArrPort">'+portOptions(g.arrivalPort)+'</select></div>'+
    field('gDepDate',t('departureDate')+' *',g.departureDate,'date')+
    field('gDepFlight',t('departureFlight'),g.departureFlight)+
    '<div class="field"><label>'+t('departurePort')+' *</label><select id="gDepPort">'+portOptions(g.departurePort)+'</select></div>'+
    '</div>'+
    field('gNotes',t('notes'),g.notes);
}
function groupFormValues(isOperator){
  var pax=paxValues();
  return {
    fileNo:isOperator?val('gFileNo'):'', groupCode:val('gCode'), groupName:val('gName'),
    leaderName:val('gLeader'), leaderMobile:val('gLeaderMob'),
    adults:pax.adults, children:pax.children, infants:pax.infants,
    totalPax:pax.totalPax, paxBreakdown:pax.paxBreakdown,
    arrivedPax:isOperator?val('gArrPax'):'', departedPax:isOperator?val('gDepPax'):'',
    arrivalDate:val('gArrDate'), arrivalFlight:val('gArrFlight'), arrivalPort:val('gArrPort'),
    departureDate:val('gDepDate'), departureFlight:val('gDepFlight'), departurePort:val('gDepPort'),
    notes:val('gNotes')
  };
}
function groupDetailHtml(g){
  var items=[
    ['fileNo',g.fileNo],['groupCode',g.groupCode],['agentRef',g.agentRef],['groupName',g.groupName],['status',g.status?t('st'+g.status):''],
    ['leaderName',g.leaderName],['leaderMobile',g.leaderMobile],
    ['totalPax',g.totalPax],['adults',g.adults],['children',g.children],['infants',g.infants],['paxBreakdown',g.paxBreakdown],
    ['arrivalDate',g.arrivalDate],['arrivalTime',g.arrivalTime],['arrivalFlight',g.arrivalFlight],['arrivalPort',g.arrivalPort],['travelType',modeLabel(g.arrivalMode)],
    ['departureDate',g.departureDate],['departureTime',g.departureTime],['departureFlight',g.departureFlight],['departurePort',g.departurePort],
    ['notes',g.notes]
  ];
  var out='<div class="detail-list">'+items.map(function(it){
    return '<div class="dl-item"><div class="dl-lbl">'+t(it[0])+'</div><div>'+esc(it[1])+'</div></div>';
  }).join('')+'</div>';
  if(g.ticketUrl) out+='<div style="margin-top:10px"><a class="link" href="'+esc(g.ticketUrl)+'" target="_blank">📎 '+t('viewTicket')+'</a></div>';
  return out;
}

/* ════════════════ VIEW: GROUPS ════════════════ */
function viewGroups(c){
  var isOp = state.user.role==='operator';
  var actionBtn = isOp
    ? '<button class="btn btn-primary btn-sm" onclick="openRequestForm(\'operator\')">'+t('addGroup')+'</button>'
    : '<button class="btn btn-primary btn-sm" onclick="openRequestForm()">'+t('newRequest')+'</button>';
  c.innerHTML='<div class="page-title">🕋 '+t('groups')+actionBtn+'</div>'+
    '<div class="card"><div class="card-hd" style="gap:8px;flex-wrap:wrap">'+
    '<div class="tbl-search"><input id="grSearch" data-pg="grp" placeholder="'+t('search')+'" oninput="pagerReset(&quot;grp&quot;);renderGroupRows()"/></div>'+
    '<select id="grStatusF" onchange="pagerReset(&quot;grp&quot;);renderGroupRows()" style="padding:7px 10px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.78rem">'+
      selectFieldOpts([['',t('statusFilter')+': '+t('allLbl')]].concat(GROUP_STATUSES.map(function(st){return [st,t('st'+st)];})), '')+'</select>'+
    (isOp?'<select id="grAgentF" onchange="pagerReset(&quot;grp&quot;);renderGroupRows()" style="padding:7px 10px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.78rem">'+
      selectFieldOpts([['',t('agentFilter')+': '+t('allLbl')]].concat(agentOptions()), '')+'</select>':'')+
    '<label style="display:flex;align-items:center;gap:5px;font-size:.74rem;color:var(--muted);cursor:pointer">'+
    '<input type="checkbox" id="grArchF" onchange="pagerReset(&quot;grp&quot;);renderGroupRows()"/> '+t('showArchived')+'</label>'+
    '</div>'+
    '<div class="tbl-wrap"><table><thead><tr><th>'+t('fileNo')+'</th><th>'+t('groupCode')+'</th><th>'+t('groupName')+'</th>'+
    '<th>'+t('typeCol')+'</th>'+
    (isOp?'<th>'+t('agentCol')+'</th>':'')+
    '<th>'+t('pax')+'</th><th>'+t('arrival')+'</th><th>'+t('departure')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead>'+
    '<tbody id="grBody"><tr class="no-data"><td colspan="9"><div class="spinner"></div></td></tr></tbody></table></div><div id="grpPager"></div></div>';
  var calls=[apiCall('groups.list'), loadPorts()];
  if(isOp) calls.push(apiCall('agents.list'));
  Promise.all(calls).then(function(rs){
    if(!rs[0].ok) return;
    state.cache.groups=rs[0].rows;
    if(isOp && rs[2] && rs[2].ok) state.cache.agents=rs[2].rows;
    renderGroupRows();
  });
}
function agentNameOf(code){
  var a=(state.cache.agents||[]).find(function(x){return x.agentCode===code;});
  return a?a.agentName:code;
}
function renderGroupRows(){
  var isOp = state.user.role==='operator';
  var q=(document.getElementById('grSearch')||{}).value||''; q=q.toLowerCase();
  var stF=(document.getElementById('grStatusF')||{}).value||'';
  var agF=(document.getElementById('grAgentF')||{}).value||'';
  var showArch=(document.getElementById('grArchF')||{}).checked;
  var rows=(state.cache.groups||[]).filter(function(g){
    if(!showArch && g.archived==='yes') return false;
    if(stF && g.status!==stF) return false;
    if(agF && g.agentCode!==agF) return false;
    return !q || (g.fileNo+g.groupCode+g.groupName+g.leaderName+(isOp?agentNameOf(g.agentCode):'')).toLowerCase().indexOf(q)!==-1;
  });
  var ps=pageSlice('grp', rows);
  var pgEl=document.getElementById('grpPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  rows=ps.rows;
  var html=rows.map(function(g){
    var actions = '<span class="link" onclick="openGroup(\''+g.groupId+'\')">'+t('view')+'</span>'+
      ' · <span class="link" title="'+t('voucherLbl')+'" onclick="showVoucherFor(\''+g.groupId+'\')">🖨️</span>'+
      ' · <span class="link" title="'+t('printOrder')+'" onclick="showOrderDocFor(\''+g.groupId+'\')">📋</span>';
    if(isOp) actions += ' · <span class="link" onclick="openGroupModal(\''+g.groupId+'\')">'+t('edit')+'</span> · <span class="link" onclick="openStatusModal(\''+g.groupId+'\')">'+t('changeStatus')+'</span>';
    var isInd=g.bookingType==='Individual';
    return '<tr><td>'+esc(g.fileNo)+'</td><td dir="ltr" style="font-size:.7rem">'+esc(g.groupCode)+'</td><td><span class="link" onclick="openGroup(\''+g.groupId+'\')">'+esc(g.groupName)+'</span></td>'+
      '<td><span class="badge '+(isInd?'b-purple':'b-blue')+'">'+t(isInd?'kindIndividual':'kindGroup')+'</span></td>'+
      (isOp?'<td>'+esc(agentNameOf(g.agentCode))+'</td>':'')+
      '<td>'+esc(g.totalPax)+(g.paxBreakdown?' <span style="color:var(--muted);font-size:.68rem">('+esc(g.paxBreakdown)+')</span>':'')+'</td>'+
      '<td dir="ltr">'+esc(g.arrivalDate)+(g.arrivalFlight?' · '+esc(g.arrivalFlight):'')+'</td>'+
      '<td dir="ltr">'+esc(g.departureDate)+(g.departureFlight?' · '+esc(g.departureFlight):'')+'</td>'+
      '<td>'+stBadge(g.status)+(isOp?' <span class="badge '+(STAGE_BADGE[g.stage]||'b-amber')+'" style="font-size:.58rem">'+stageLabel(STAGE_BADGE[g.stage]?g.stage:'Visa')+'</span>':'')+'</td><td>'+actions+'</td></tr>';
  }).join('');
  document.getElementById('grBody').innerHTML=html||'<tr class="no-data"><td colspan="10">'+t('noGroups')+'</td></tr>';
}
/* ════════════════ UNIFIED EDIT ROUTER ════════════════
   Every Edit button in the CRM goes through here. It decides the right path:
   • operator → edits directly (with a dependency-impact preview where it matters)
   • agent    → submits a change request for operator approval
   This keeps one consistent behaviour instead of scattered, divergent edit buttons. */
function editEntity(kind, id, groupId){
  var isOp = state.user.role==='operator';
  if(isOp){
    if(kind==='group') return openGroupModal(groupId||id);
    if(kind==='trip')  return openTripModal(id);
    if(kind==='order') return openOrderModal(id);
    if(kind==='hotel') return openHotelModal(id);
    if(kind==='brn')   return openBrnModal(id);
    return;
  }
  // ─ agent paths: everything becomes a request ─
  if(kind==='trip')  return requestTripChange(groupId||state.currentGroupId, id);
  if(kind==='order') return tpRequestOrderChange(groupId||state.currentGroupId, id);
  // group / hotel / brn edits all reshape the booking → go through the wizard change-request
  return requestGroupEdit();
}

function openGroupModal(groupId){
  var g=(state.cache.groups||[]).find(function(x){return x.groupId===groupId;})||{};
  openModal(t('groupModal'), groupFormHtml(g,true), function(){
    var p=groupFormValues(true);
    p.groupId=groupId||''; p.agentCode=val('gAgent');
    if(!mValidate([
      {id:'gAgent',   ok:!!(groupId||p.agentCode),      msg:t('errNoAgent')},
      {id:'',         ok:!!p.groupName,                 msg:t('atLeastOneGroup')},
      {id:'gAdults',  ok:parseInt(p.totalPax)>0,        msg:t('vPax')},
      {id:'gArrDate', ok:!!p.arrivalDate,               msg:t('vArrivalDate')},
      {id:'gDepDate', ok:!!p.departureDate,             msg:t('vDepartureDate')},
      {id:'gDepDate', ok:!(p.arrivalDate&&p.departureDate&&p.departureDate<p.arrivalDate), msg:t('vDepAfterArr')},
      pastDateErr('gArrDate', p.arrivalDate, t('arrivalDate')),
      pastDateErr('gDepDate', p.departureDate, t('departureDate')),
      {id:'gArrPort', ok:!!p.arrivalPort,               msg:t('vArrivalPort')},
      {id:'gDepPort', ok:!!p.departurePort,             msg:t('vDeparturePort')},
      {id:'gLeader',  ok:!!p.leaderName,                msg:t('vLeader')}
    ])) return;
    if(!groupId){ groupSaveCommit(p); return; }   // new group: nothing to cascade
    // existing group: show what this edit will change downstream, then confirm
    apiCall('groups.editImpact',p).then(function(res){
      if(res.ok && res.changes && res.changes.length) showCascadePreview(res.changes, function(){ groupSaveCommit(p); });
      else groupSaveCommit(p);
    });
  });
}
function groupSaveCommit(p){
  apiCall('groups.save',p).then(function(res){
    if(res.ok){
      toast(t('saved')+(res.cascaded?' · '+res.cascaded+' '+t('cascadedMsg'):''),'ok');
      closeModal(); state.cache.groups=null;
      if(state.view==='groupDetail') viewGroupDetail(document.getElementById('content'));
      else viewGroups(document.getElementById('content'));
    }
    else toast(t(res.error==='NO_AGENT'?'errNoAgent':'errMissing'),'err');
  });
}
/* shared dependency-impact preview — used before any edit that ripples */
function showCascadePreview(changes, onConfirm){
  var byLabel={};
  changes.forEach(function(c){
    var key=(c.kind==='trip'?'🚌 ':'🏨 ')+c.label;
    (byLabel[key]=byLabel[key]||[]).push(c);
  });
  var body='<div style="font-size:.82rem;margin-bottom:10px">'+t('cascadeIntro')+'</div>'+
    Object.keys(byLabel).map(function(k){
      return '<div class="diff-box" style="margin-bottom:8px"><div style="font-weight:800;margin-bottom:6px">'+esc(k)+'</div>'+
        '<div class="tbl-wrap"><table class="mini-tbl"><thead><tr><th>'+t('fieldLbl')+'</th><th>'+t('oldValue')+'</th><th>'+t('newValue')+'</th></tr></thead><tbody>'+
        byLabel[k].map(function(c){
          return '<tr><td><b>'+esc(c.fieldLabel)+'</b></td>'+
            '<td style="color:var(--red);text-decoration:line-through;opacity:.75">'+esc(c.before)+'</td>'+
            '<td style="color:var(--green);font-weight:700">'+esc(c.after)+'</td></tr>';
        }).join('')+'</tbody></table></div></div>';
    }).join('');
  openModal('⚠️ '+t('cascadeTitle'), body, null);
  modalWide(true);
  document.getElementById('modalFooter').innerHTML=
    '<button class="btn btn-ghost" onclick="closeModal()">'+t('cancel')+'</button>'+
    '<button class="btn btn-primary" id="cascadeYes">'+t('applyAll')+'</button>';
  document.getElementById('cascadeYes').onclick=function(){ onConfirm(); };
}
function openGroupDetail(groupId){
  var g=(state.cache.groups||[]).find(function(x){return x.groupId===groupId;});
  if(g) openModal(t('groupModal'), groupDetailHtml(g), null);
}
function openStatusModal(groupId){
  var g=(state.cache.groups||[]).find(function(x){return x.groupId===groupId;});
  if(!g) return;
  var opts=GROUP_STATUSES.map(function(s){ return [s, t('st'+s)]; });
  openModal(t('changeStatus')+' — '+esc(g.groupName),
    '<div class="field"><label>'+t('status')+'</label><select id="mStStatus">'+selectFieldOpts(opts,g.status)+'</select></div>',
    function(){
      apiCall('groups.setStatus',{groupId:groupId,status:val('mStStatus')}).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); viewGroups(document.getElementById('content')); }
      });
    });
}

/* ════════════════ VIEW: REQUESTS ════════════════ */
var reqFilter='Pending';
function viewRequests(c){
  var isOp = state.user.role==='operator';
  var filters=isOp?['Pending','Returned','Draft','Approved','Rejected','Cancelled','All']:['Draft','Pending','Approved','Rejected','Cancelled','All'];
  var tabs=filters.map(function(f){
    return '<button class="btn btn-sm '+(reqFilter===f?'btn-primary':'btn-ghost')+'" onclick="pagerReset(&quot;req&quot;);reqFilter=\''+f+'\';viewRequests(document.getElementById(\'content\'))">'+(f==='All'?t('all'):t('st'+f))+'</button>';
  }).join(' ');
  var newBtn = isOp?'':'<button class="btn btn-primary btn-sm" onclick="openRequestForm()">'+t('newRequest')+'</button>';
  var agentOpts='';
  if(isOp){
    agentOpts='<select id="rqAgentF" onchange="state.cache.reqAgentF=this.value;pagerReset(&quot;req&quot;);renderRequestRows()" style="padding:7px 10px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.78rem">'+
      selectFieldOpts([['',t('agentFilter')+': '+t('allLbl')]].concat(agentOptions()), state.cache.reqAgentF||'')+'</select>';
  }
  c.innerHTML='<div class="page-title">📨 '+t('requests')+newBtn+'</div>'+
    '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;align-items:center">'+tabs+
    '<div class="tbl-search"><input value="'+escapeAttr(state.cache.reqSearch||'')+'" placeholder="'+t('search')+'" oninput="state.cache.reqSearch=this.value;pagerReset(&quot;req&quot;);renderRequestRows()"/></div>'+agentOpts+'</div>'+
    '<div class="card"><div class="tbl-wrap"><table><thead><tr>'+
    '<th>'+t('reqNo')+'</th><th>'+t('requestType')+'</th>'+(isOp?'<th>'+t('agentCol')+'</th>':'')+
    '<th>'+t('groupName')+'</th><th>'+t('pax')+'</th><th>'+t('submittedAt')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead>'+
    '<tbody id="rqBody"><tr class="no-data"><td colspan="7"><div class="spinner"></div></td></tr></tbody></table></div><div id="reqPager"></div></div>';
  var calls=[apiCall('requests.list'), loadPorts()];
  if(isOp) calls.push(apiCall('agents.list'));
  Promise.all(calls).then(function(rs){
    if(!rs[0].ok) return;
    state.cache.requests=rs[0].rows;
    if(isOp && rs[2] && rs[2].ok) state.cache.agents=rs[2].rows;
    if(isOp) {
      state.cache.pendingCount = rs[0].rows.filter(function(r){return r.status==='Pending';}).length;
      renderSidebar();
    }
    renderRequestRows();
  });
}
function reqPayload(r){
  try{ return JSON.parse(r.payloadJSON||'{}'); }catch(e){ return {}; }
}
function reqGroup(r){ var p=reqPayload(r); return p.group||p; }
function renderRequestRows(){
  var isOp = state.user.role==='operator';
  var q=(state.cache.reqSearch||'').toLowerCase(), af=state.cache.reqAgentF||'';
  var rows=(state.cache.requests||[]).filter(function(r){
    var stOk = reqFilter==='All' || r.status===reqFilter ||
               (!isOp && reqFilter==='Pending' && r.status==='Returned'); // agents: merged tab
    if(!stOk) return false;
    if(af && r.agentCode!==af) return false;
    if(q){
      var g=reqGroup(r);
      if((r.requestId+' '+(g.groupName||'')+' '+(g.groupCode||'')+' '+agentNameOf(r.agentCode)).toLowerCase().indexOf(q)===-1) return false;
    }
    return true;
  });
  var ps=pageSlice('req', rows); 
  var pgEl=document.getElementById('reqPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  rows=ps.rows;
  var html=rows.map(function(r){
    var g=reqGroup(r), p=reqPayload(r);
    var counts=(p.hotels?p.hotels.length:0)+'H/'+(p.brn?p.brn.length:0)+'B/'+(p.trips?p.trips.length:0)+'T';
    var typeBadge = r.type==='EditGroup'
      ? '<span class="badge b-amber">✏️ '+t('editReqBadge')+'</span>'
      : '<span class="badge b-blue">'+t(r.type==='Individual'?'kindIndividual':'newGroupReq')+'</span>';
    var actions;
    if(r.status==='Draft'){
      actions='<span class="link" onclick="resumeDraft(\''+r.requestId+'\')">✏️ '+t('draftResume')+'</span>'+
        ' · <span class="link" style="color:var(--red)" onclick="deleteDraft(\''+r.requestId+'\')">'+t('deleteLbl')+'</span>';
      return '<tr><td dir="ltr" style="font-size:.72rem;font-weight:700">'+esc(r.requestId)+'</td>'+
        '<td><span class="badge b-gray">📝 '+t('stDraft')+'</span></td>'+
        (isOp?'<td>'+esc(agentNameOf(r.agentCode))+'</td>':'')+
        '<td>'+esc(g.groupName)+'</td><td>'+esc(g.totalPax)+'</td>'+
        '<td dir="ltr">'+esc(r.submittedAt)+'</td><td>'+stBadge(r.status)+'</td><td>'+actions+'</td></tr>';
    }
    actions='<span class="link" onclick="openReviewModal(\''+r.requestId+'\')">'+(isOp&&r.status==='Pending'?t('reviewModal'):t('view'))+'</span>';
    if(!isOp && r.status==='Pending') actions+=' · <span class="link" style="color:var(--red)" onclick="cancelRequest(\''+r.requestId+'\')">'+t('cancelReq')+'</span>';
    if(!isOp && r.status==='Returned') actions+=' · <span class="link" onclick="resubmitRequest(\''+r.requestId+'\')">✏️ '+t('editResubmit')+'</span>';
    return '<tr><td dir="ltr" style="font-size:.72rem;font-weight:700">'+esc(r.requestId)+'</td>'+
      '<td>'+typeBadge+' <span style="color:var(--muted);font-size:.66rem">'+counts+'</span></td>'+
      (isOp?'<td>'+esc(agentNameOf(r.agentCode))+'</td>':'')+
      '<td>'+esc(g.groupName)+'</td><td>'+esc(g.totalPax)+'</td>'+
      '<td dir="ltr">'+esc(r.submittedAt)+'</td><td>'+stBadge(r.status)+'</td><td>'+actions+'</td></tr>';
  }).join('');
  document.getElementById('rqBody').innerHTML=html||'<tr class="no-data"><td colspan="8">'+t('noRequests')+'</td></tr>';
}
function wzStepsFor(kind){
  return kind==='Individual' ? ['wzGroup','wzBrn','wzHosting','wzCatering','wzTrips','wzReview']
                             : ['wzGroup','wzBrn','wzHotels','wzCatering','wzTrips','wzReview'];
}
/* split a mixed BRN array (both types share one table) into hotel vs catering.
   Rows with no brnType are treated as Hotel for backward compatibility. */
function wzSplitBrn(arr){
  var hotel=[], catering=[];
  (arr||[]).forEach(function(b){ if(b.brnType==='Catering') catering.push(b); else hotel.push(b); });
  return { hotel:hotel, catering:catering };
}
function openRequestForm(mode, resubmitReq){
  state.wizard={ mode:(mode==='operator'?'operator':'agent'), step:1, kind:'Group',
    steps:wzStepsFor('Group'),
    group:{ groupPairs:[{code:'',name:''}] }, hotels:[], brn:[], catering:[], trips:[],
    host:{}, hostIdFile:null, ticket:null, agentCode:'', brnSkip:false, cateringSkip:false,
    tripsGenerated:false, done:false, reqId:'', createdFileNo:'', resubmitId:'' };
  // prefill from a Returned request payload
  if(resubmitReq){
    var w=state.wizard, p=reqPayload(resubmitReq), g=p.group||p;
    try{ g.groupPairs = typeof g.groupPairs==='string' ? JSON.parse(g.groupPairs||'[]') : (g.groupPairs||[]); }catch(e){ g.groupPairs=[]; }
    if(!g.groupPairs.length) g.groupPairs=[{code:g.groupCode||'',name:g.groupName||''}];
    w.kind = g.bookingType==='Individual' ? 'Individual' : 'Group';
    w.steps = wzStepsFor(w.kind);
    w.group = g;
    try{ w.host = JSON.parse(g.hostJSON||'{}'); }catch(e){ w.host={}; }
    w.hotels = p.hotels||[];
    var sb = wzSplitBrn(p.brn||[]); w.brn = sb.hotel; w.catering = sb.catering;
    w.trips = (p.trips||[]).map(function(tr){ return Object.assign({}, tr, { tripType: tr.tripType||tr.movementType||'' }); });
    w.brnSkip = !(w.brn&&w.brn.length);
    w.cateringSkip = !(w.catering&&w.catering.length);
    w.tripsGenerated = !!(w.trips&&w.trips.length);
    w.resubmitId = resubmitReq.requestId;
  }
  state.view='reqWizard';
  var pre=[loadPorts(), loadMasterType('hotel'), loadMasterType('city'), loadMasterType('tripType'), loadMasterType('vehicleType')];
  if(mode==='operator' && !state.cache.agents) pre.push(apiCall('agents.list').then(function(r){ if(r.ok) state.cache.agents=r.rows; }));
  Promise.all(pre).then(function(){ renderShell(); });
}

/* ════════════════ BOOKING REQUEST WIZARD ════════════════ */
function wzStepName(){ var w=state.wizard; return w.steps[w.step-1]; }
function viewReqWizard(c){
  var w=state.wizard;
  if(!w){ openRequestForm(); return; }
  if(w.done){ renderWizardSuccess(c); return; }

  var steps=w.steps.map(function(s,i){
    var n=i+1, cls=w.step===n?'active':(w.step>n?'done':'');
    return '<div class="wz-step '+cls+'"><span class="wz-num">'+(w.step>n?'✓':n)+'</span><span>'+t(s)+'</span></div>';
  }).join('');

  var name=wzStepName(), body='';
  if(name==='wzGroup') body=wzStepGroup();
  else if(name==='wzHotels') body=wzStepHotels();
  else if(name==='wzBrn') body=wzStepBrn();
  else if(name==='wzCatering') body=wzStepCatering();
  else if(name==='wzHosting') body=wzStepHosting();
  else if(name==='wzTrips') body=wzStepTrips();
  else if(name==='wzReview') body=wzStepReview();

  var last=w.step===w.steps.length;
  var nav='<div class="wz-nav">'+
    (w.step>1?'<button class="btn btn-ghost" onclick="wzBack()">'+t('prev')+'</button>':'<span></span>')+
    '<span style="display:flex;gap:8px;align-items:center">'+
    '<button class="btn btn-ghost btn-sm" onclick="wzSaveDraft(false)">'+t('saveDraft')+'</button>'+
    (!last?'<button class="btn btn-primary" onclick="wzNext()">'+t('next')+'</button>':
      '<button class="btn btn-primary" id="wzSubmitBtn" onclick="wzSubmit()">'+t('submit')+'</button>')+
    '</span></div>';

  c.innerHTML='<div class="page-title"><span><span class="link" onclick="wzCancel()">'+t('back')+'</span> &nbsp; 📨 '+t('wizardTitle')+'</span></div>'+
    '<div class="wz-steps">'+steps+'</div>'+
    wzSummaryBar()+
    '<div class="card wz-body"><div id="wzErrBox"></div>'+body+nav+
    '<div style="font-size:.66rem;color:var(--muted);text-align:center;margin-top:8px">'+t('draftHint')+'</div></div>';

  if(name==='wzGroup'){ setTimeout(function(){ onPaxInput(); wzModeChange(); },0); }
}
function wzCancel(){ state.wizard=null; state.view='requests'; renderShell(); }

/* ── drafts: nothing typed into the wizard should ever be lost ── */
function wzDraftPayload(){
  var w=state.wizard;
  wzHotelCapture(); if(!w.brnSkip) wzBrnCapture(); if(!w.cateringSkip) wzCateringCapture(); wzTripCapture(); wzHostCapture();
  var g=Object.assign({}, w.group||{});
  if(w.kind==='Individual') g.hostJSON=JSON.stringify(w.host||{});
  g.bookingType=w.kind;
  // drafts keep BOTH kinds in one array so resume can re-split them by brnType
  var hotelBrn=(w.brn||[]).map(function(b){ return Object.assign({},b,{brnType:'Hotel'}); });
  var cateringBrn=(w.catering||[]).map(function(b){ return Object.assign({},b,{brnType:'Catering'}); });
  return {
    requestId: w.draftId||'', group:g, hotels:w.hotels||[], brn:hotelBrn.concat(cateringBrn), trips:w.trips||[],
    step:w.step, kind:w.kind, brnSkip:!!w.brnSkip, cateringSkip:!!w.cateringSkip,
    agentCode: w.agentCode||'', editGroupId: w.editGroupId||''
  };
}
function wzSaveDraft(silent){
  var w=state.wizard; if(!w) return;
  if(w.resubmitId || w.editGroupId) return;   // those already have a record of their own
  apiCall('requests.saveDraft', wzDraftPayload()).then(function(res){
    if(res.ok){
      w.draftId=res.requestId;
      if(!silent) toast(t('draftSaved'),'ok');
    } else if(!silent) toast(t('errServer'),'err');
  });
}
function resumeDraft(requestId){
  apiCall('requests.list').then(function(res){
    if(!res.ok) return;
    var r=(res.rows||[]).find(function(x){return x.requestId===requestId;});
    if(!r) return;
    var p={}; try{ p=JSON.parse(r.payloadJSON||'{}'); }catch(e){}
    var wz=p.wizard||{};
    openRequestForm(state.user.role==='operator'?'operator':'agent', null);
    var w=state.wizard;
    w.kind = wz.kind || (p.group && p.group.bookingType==='Individual' ? 'Individual':'Group');
    w.steps = wzStepsFor(w.kind);
    w.group = p.group||{};
    if(w.group.groupPairs && typeof w.group.groupPairs==='string'){
      try{ w.group.groupPairs=JSON.parse(w.group.groupPairs); }catch(e){ w.group.groupPairs=[{code:'',name:''}]; }
    }
    w.hotels=p.hotels||[];
    var sbD=wzSplitBrn(p.brn||[]); w.brn=sbD.hotel; w.catering=sbD.catering;
    w.trips=(p.trips||[]).map(function(tr){ return Object.assign({},tr,{tripType:tr.tripType||''}); });
    try{ w.host=JSON.parse((p.group||{}).hostJSON||'{}'); }catch(e){ w.host={}; }
    w.brnSkip=!!wz.brnSkip;
    w.cateringSkip = (typeof wz.cateringSkip!=='undefined') ? !!wz.cateringSkip : !(w.catering&&w.catering.length);
    w.agentCode=r.agentCode||'';
    w.draftId=requestId;
    w.tripsGenerated=(w.trips.length>0);
    w.step=Math.min(wz.step||1, w.steps.length);
    renderShell();
  });
}
function deleteDraft(requestId){
  uiConfirm(t('deleteDraft')+'?', function(){
    apiCall('requests.deleteDraft',{requestId:requestId}).then(function(res){
      if(res.ok){ toast(t('saved'),'ok'); viewRequests(document.getElementById('content')); }
    });
  });
}

/* compact summary of completed steps */
function wzSummaryBar(){
  var w=state.wizard, chips=[];
  var done=function(name){ return w.steps.indexOf(name)>-1 && w.step>w.steps.indexOf(name)+1; };
  if(done('wzGroup')){
    var g=w.group;
    chips.push('🕋 '+esc(g.groupName||g.groupCode)+' • '+esc(g.totalPax)+' pax • '+esc(g.arrivalDate)+' → '+esc(g.departureDate));
  }
  if(done('wzHotels')){
    var hs=w.hotels.filter(function(h){return h.hotelName||h.city;});
    chips.push('🏨 '+hs.length+' — '+hs.map(function(h){return esc(h.city);}).join(' → '));
  }
  if(done('wzBrn')) chips.push('📄 '+(w.brnSkip?t('brnSkipped'):(w.brn.filter(function(b){return b.brnNumber;}).length+' '+t('wzBrn'))));
  if(done('wzCatering')) chips.push('🍽️ '+(w.cateringSkip?t('cateringSkipped'):(w.catering.filter(function(b){return b.brnNumber;}).length+' '+t('wzCatering'))));
  if(done('wzHosting')) chips.push('🏠 '+esc(w.host.name||'–')+' • '+esc(w.host.city||'–'));
  if(done('wzTrips')) chips.push('🚌 '+w.trips.length+' '+t('wzTrips'));
  if(!chips.length) return '';
  return '<div class="wz-summary">'+chips.map(function(ch){ return '<span class="wz-chip">'+ch+'</span>'; }).join('')+'</div>';
}

/* ── step 1: group + travel ── */
function radioGroup(name, opts, sel, onchange){
  return '<div style="display:flex;gap:8px;flex-wrap:wrap">'+opts.map(function(o){
    var id=name+'_'+o[0];
    return '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;background:var(--input-bg);border:1px solid '+(o[0]===sel?'var(--blue)':'var(--border)')+';border-radius:9px;padding:7px 12px;font-size:.8rem;font-weight:700">'+
      '<input type="radio" name="'+name+'" value="'+o[0]+'"'+(o[0]===sel?' checked':'')+' onchange="'+onchange+'"/> '+o[1]+'</label>';
  }).join('')+'</div>';
}
function modeRadioOpts(){ return TRIP_MODES.map(function(m){ return [m, t('mode'+m)]; }); }

function wzStepGroup(){
  var w=state.wizard, g=w.group;
  var agentSel='';
  if(w.mode==='operator'){
    agentSel='<div class="field"><label>'+t('agentCol')+' *</label><select id="wzAgent">'+
      selectFieldOpts([['','–']].concat(agentOptions()), w.agentCode)+'</select></div>';
  }
  var kindSel='<div class="field"><label>'+t('bookingKind')+'</label>'+
    radioGroup('wzKind',[['Group',t('kindGroup')],['Individual',t('kindIndividual')]],w.kind,'wzKindChange()')+'</div>';
  // group code/name pairs
  var pairs=(g.groupPairs&&g.groupPairs.length)?g.groupPairs:[{code:'',name:''}];
  var pairsHtml=pairs.map(function(pr,i){
    return '<div class="form-grid" style="align-items:end;position:relative">'+
      '<div class="field"><label>'+t('groupCode')+(i===0?' *':'')+'</label><input id="gCode_'+i+'" value="'+escapeAttr(pr.code)+'"/></div>'+
      '<div class="field" style="display:flex;gap:6px;align-items:end">'+
        '<div style="flex:1"><label style="display:block;font-size:.76rem;font-weight:700;color:var(--muted);margin-bottom:5px">'+t('groupName')+(i===0?' *':'')+'</label><input id="gName_'+i+'" value="'+escapeAttr(pr.name)+'"/></div>'+
        (pairs.length>1?'<button class="btn btn-danger btn-sm" style="margin-bottom:0" onclick="wzRemovePair('+i+')">✕</button>':'')+
      '</div></div>';
  }).join('');

  var arrMode=g.arrivalMode||'Air', depMode=g.departureMode||'Air';

  return '<div class="card-title" style="margin-bottom:14px">🕋 '+t('wzGroup')+'</div>'+
    agentSel+kindSel+
    '<div id="wzPairs">'+pairsHtml+'</div>'+
    '<button class="btn btn-ghost btn-sm" style="margin-bottom:16px" onclick="wzAddPair()">'+t('addGroupPair')+'</button>'+
    '<div class="form-grid">'+
    field('gLeader',t('leaderName')+' *',g.leaderName)+
    field('gLeaderMob',t('leaderMobile')+' *',g.leaderMobile)+
    field('gAgentRef',t('agentRef'),g.agentRef)+
    '</div>'+
    paxFieldsHtml(g)+
    // arrival
    '<div class="card-title" style="font-size:.85rem;margin:16px 0 8px">🛬 '+t('arrivalSec')+'</div>'+
    '<div class="field"><label>'+t('travelType')+'</label>'+radioGroup('arrMode',modeRadioOpts(),arrMode,'wzModeChange()')+'</div>'+
    '<div class="form-grid">'+
    field('gArrDate',t('arrivalDate')+' *',g.arrivalDate,'date')+
    field('gArrTime',t('timeLbl'),g.arrivalTime,'time')+
    '<div class="field"><label>'+t('arrivalPort')+' *</label><select id="gArrPort">'+portOptions(g.arrivalPort,arrMode)+'</select></div>'+
    '<div class="field" id="arrFlightWrap"'+(arrMode==='Air'?'':' style="display:none"')+'><label>'+t('arrivalFlight')+'</label><input id="gArrFlight" value="'+escapeAttr(g.arrivalFlight)+'"/></div>'+
    '</div>'+
    // departure
    '<div class="card-title" style="font-size:.85rem;margin:16px 0 8px">🛫 '+t('departureSec')+'</div>'+
    '<div class="field"><label>'+t('travelType')+'</label>'+radioGroup('depMode',modeRadioOpts(),depMode,'wzModeChange()')+'</div>'+
    '<div class="form-grid">'+
    field('gDepDate',t('departureDate')+' *',g.departureDate,'date')+
    field('gDepTime',t('timeLbl'),g.departureTime,'time')+
    '<div class="field"><label>'+t('departurePort')+' *</label><select id="gDepPort">'+portOptions(g.departurePort,depMode)+'</select></div>'+
    '<div class="field" id="depFlightWrap"'+(depMode==='Air'?'':' style="display:none"')+'><label>'+t('departureFlight')+'</label><input id="gDepFlight" value="'+escapeAttr(g.departureFlight)+'"/></div>'+
    '</div>'+
    // ticket
    '<div class="field" style="margin-top:14px"><label>'+t('ticketCopy')+'</label>'+
    '<input type="file" id="gTicket" accept=".pdf,.jpg,.jpeg,.png" onchange="wzTicketPick(this)"/>'+
    '<div id="ticketMsg" style="font-size:.75rem;color:var(--green);margin-top:5px">'+(w.ticket?t('ticketUploaded')+' ('+esc(w.ticket.name)+')':'')+'</div></div>'+
    field('gNotes',t('notes'),g.notes);
}
function wzKindChange(){
  var kind=(document.querySelector('input[name=wzKind]:checked')||{}).value||'Group';
  var w=state.wizard;
  if(kind===w.kind) return;
  captureGroupStep();
  w.kind=kind; w.steps=wzStepsFor(kind);
  w.tripsGenerated=false; w.trips=[];
  renderShell();
}
function wzModeChange(){
  var arr=(document.querySelector('input[name=arrMode]:checked')||{}).value;
  var dep=(document.querySelector('input[name=depMode]:checked')||{}).value;
  var aw=document.getElementById('arrFlightWrap'), dw=document.getElementById('depFlightWrap');
  if(aw) aw.style.display = arr==='Air'?'':'none';
  if(dw) dw.style.display = dep==='Air'?'':'none';
  // re-filter port dropdowns by selected mode (keep current selection if still valid)
  var ap=document.getElementById('gArrPort'), dp=document.getElementById('gDepPort');
  if(ap){ var cur=ap.value; ap.innerHTML=portOptions(cur,arr); }
  if(dp){ var cur2=dp.value; dp.innerHTML=portOptions(cur2,dep); }
  // refresh radio borders
  document.querySelectorAll('input[name=arrMode],input[name=depMode],input[name=wzKind]').forEach(function(r){
    r.closest('label').style.borderColor = r.checked?'var(--blue)':'var(--border)';
  });
}
function wzAddPair(){ captureGroupStep(); state.wizard.group.groupPairs.push({code:'',name:''}); renderShell(); }
function wzRemovePair(i){ captureGroupStep(); state.wizard.group.groupPairs.splice(i,1); if(!state.wizard.group.groupPairs.length) state.wizard.group.groupPairs=[{code:'',name:''}]; renderShell(); }
function wzTicketPick(input){
  var f=input.files&&input.files[0]; if(!f) return;
  if(f.size>8*1024*1024){ toast('Max 8MB','err'); input.value=''; return; }
  var reader=new FileReader();
  reader.onload=function(){ state.wizard.ticket={name:f.name, base64:reader.result};
    var m=document.getElementById('ticketMsg'); if(m) m.textContent=t('ticketUploaded')+' ('+f.name+')'; };
  reader.readAsDataURL(f);
}
function readGroupPairs(){
  var pairs=[], i=0;
  while(document.getElementById('gCode_'+i)||document.getElementById('gName_'+i)){
    var c=document.getElementById('gCode_'+i), n=document.getElementById('gName_'+i);
    pairs.push({code:c?c.value.trim():'', name:n?n.value.trim():''});
    i++;
  }
  return pairs;
}
function captureGroupStep(){
  // guard: on later steps these inputs don't exist, and reading them would
  // overwrite the captured group with empty strings (same pattern as the other captures)
  if(!document.getElementById('gArrDate')) return;
  var w=state.wizard, pax=paxValues();
  var pairs=readGroupPairs();
  if(!pairs.length) pairs=[{code:'',name:''}];
  var arrMode=(document.querySelector('input[name=arrMode]:checked')||{}).value||'Air';
  var depMode=(document.querySelector('input[name=depMode]:checked')||{}).value||'Air';
  w.group={
    groupPairs:pairs,
    groupCode:pairs.map(function(p){return p.code;}).filter(String).join(', '),
    groupName:pairs.map(function(p){return p.name;}).filter(String).join(' / '),
    leaderName:val('gLeader'), leaderMobile:val('gLeaderMob'),
    adults:pax.adults, children:pax.children, infants:pax.infants,
    totalPax:pax.totalPax, paxBreakdown:pax.paxBreakdown,
    arrivalMode:arrMode, arrivalDate:val('gArrDate'), arrivalTime:val('gArrTime'),
    arrivalPort:val('gArrPort'), arrivalFlight:arrMode==='Air'?val('gArrFlight'):'',
    departureMode:depMode, departureDate:val('gDepDate'), departureTime:val('gDepTime'),
    departurePort:val('gDepPort'), departureFlight:depMode==='Air'?val('gDepFlight'):'',
    bookingType:w.kind,
    agentRef:val('gAgentRef'),
    notes:val('gNotes')
  };
  if(w.mode==='operator') w.agentCode=val('wzAgent')||w.agentCode;
  if(w.ticket) w.group.ticketBase64=w.ticket.base64, w.group.ticketName=w.ticket.name;
}

/* ═══════ shared date helpers for the wizard ═══════ */
function ymd(v){ var m=(v||'').match(/^(\d{4})-(\d{2})-(\d{2})/); return m?m[0]:''; }
function shiftDate(dateStr, days){
  var m=(dateStr||'').match(/^(\d{4})-(\d{2})-(\d{2})/); if(!m) return '';
  var d=new Date(Date.UTC(+m[1],+m[2]-1,+m[3])); d.setUTCDate(d.getUTCDate()+days);
  return d.toISOString().slice(0,10);
}
var DAYS_EN=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
var DAYS_AR=['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
function clientDayName(dateStr){
  var m=(dateStr||'').match(/^(\d{4})-(\d{2})-(\d{2})/); if(!m) return '';
  var d=new Date(Date.UTC(+m[1],+m[2]-1,+m[3]));
  return (state.lang==='ar'?DAYS_AR:DAYS_EN)[d.getUTCDay()];
}
function dateSelectOpts(base, sel){
  var o=dateOptions(base,[-1,0,1],sel);
  if(sel && o.indexOf('value="'+sel+'"')===-1) o+='<option value="'+escapeAttr(sel)+'" selected>'+esc(sel)+'</option>';
  return '<option value="">–</option>'+o;
}
function dateOptions(base, offsets, sel){
  return offsets.map(function(o){ var d=shiftDate(base,o); return d?('<option value="'+d+'"'+(d===sel?' selected':'')+'>'+d+(o===0?'':(o>0?' (+'+o+')':' ('+o+')'))+'</option>'):''; }).join('');
}
function cityLabelOf(hotelCity){ // normalized display label for a city value
  return hotelCity||'';
}

/* ═══════ STEP: HOTELS (inline editable, chained dates) ═══════ */
function wzStepHotels(){
  var w=state.wizard, g=w.group;
  if(!w.hotels.length) w.hotels=[{city:'',hotelName:'',rooms:'',checkIn:'',checkOut:'',rsvNo:'',notes:''}];
  var rows=w.hotels.map(function(h,i){ return hotelRow(h,i,g); }).join('');
  // only offer "copy from Hotel BRN" when there is Hotel BRN data to copy from
  var hasBrn = !w.brnSkip && (w.brn||[]).some(function(b){ return b.checkIn||b.checkOut||b.hotelName; });
  return '<div class="card-hd"><div class="card-title">🏨 '+t('wzHotels')+'</div>'+
    '<span style="display:flex;gap:8px">'+
    (hasBrn?'<button class="btn btn-ghost btn-sm" onclick="wzHotelCopyBrn()">'+t('hotelsCopyBrn')+'</button>':'')+
    '<button class="btn btn-primary btn-sm" onclick="wzAddHotel()">'+t('addHotelRow')+'</button></span></div>'+
    '<div id="wzHotelRows">'+rows+'</div>';
}
/* copy dates (and name as a starting point) from Hotel BRN into Hotels */
function wzHotelCopyBrn(){
  wzHotelCapture();
  var src=(state.wizard.brn||[]).filter(function(b){ return b.checkIn||b.checkOut||b.hotelName; });
  if(!src.length) return;
  state.wizard.hotels=src.map(function(b){ return {city:'',hotelName:b.hotelName||'',rooms:b.roomsCount||'',checkIn:b.checkIn||'',checkOut:b.checkOut||'',rsvNo:'',notes:''}; });
  renderShell();
}
function hotelRow(h,i,g){
  var w=state.wizard;
  var isFirst=(i===0), isLast=(i===w.hotels.length-1);
  // check-in control
  var ci;
  if(isFirst){
    ci='<select id="wh_in_'+i+'" onchange="wzHotelSync()">'+dateSelectOpts(g.arrivalDate,h.checkIn)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkInRule')+'</div>';
  } else {
    ci='<input id="wh_in_'+i+'" value="'+escapeAttr(h.checkIn)+'" readonly style="background:var(--bg2)"/>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('lockedPrev')+'</div>';
  }
  // check-out control
  var co;
  // the calendar must never open before this hotel's own check-in
  var coMin = h.checkIn ? ' min="'+escapeAttr(h.checkIn)+'"' : dateMinAttr('wh_out_'+i);
  var coHint = '<div style="font-size:.63rem;color:var(--muted);margin-top:3px">'+t('hotelNextHint')+'</div>';
  if(isLast){
    co='<select id="wh_out_'+i+'" onchange="wzHotelSync()">'+dateSelectOpts(g.departureDate,h.checkOut)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkOutRuleLast')+'</div>'+coHint;
  } else {
    co='<input id="wh_out_'+i+'" type="date"'+coMin+' value="'+escapeAttr(h.checkOut)+'" onchange="wzHotelSync()"/>'+coHint;
  }
  return '<div class="wz-card" data-i="'+i+'">'+
    '<div class="wz-card-hd"><b>#'+(i+1)+'</b>'+(w.hotels.length>1?'<span class="sub-x" onclick="wzRemoveHotel('+i+')">✕ '+t('removeRow')+'</span>':'')+'</div>'+
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('cityLbl')+' *</label><select id="wh_city_'+i+'" onchange="wzHotelCapture()">'+masterOptions('city',h.city)+'</select></div>'+
    '<div class="field"><label>'+t('hotelLbl')+' *</label><input id="wh_name_'+i+'" value="'+escapeAttr(h.hotelName)+'" oninput="wzHotelCapture()"/></div>'+
    '<div class="field"><label>'+t('checkIn')+'</label>'+ci+'</div>'+
    '<div class="field"><label>'+t('checkOut')+'</label>'+co+'</div>'+
    '<div class="field"><label>'+t('rooms')+'</label><input id="wh_rooms_'+i+'" type="number" value="'+escapeAttr(h.rooms)+'" oninput="wzHotelCapture()"/></div>'+
    '<div class="field"><label>'+t('rsvNo')+'</label><input id="wh_rsv_'+i+'" value="'+escapeAttr(h.rsvNo)+'" oninput="wzHotelCapture()"/></div>'+
    '</div>'+
    '<div class="field"><label>'+t('notes')+'</label><input id="wh_notes_'+i+'" value="'+escapeAttr(h.notes)+'" oninput="wzHotelCapture()"/></div>'+
    '</div>';
}
function wzHotelCapture(){ // read all hotel inputs into state (no re-render)
  var w=state.wizard;
  if(!document.getElementById('wh_city_0')) return;
  w.hotels.forEach(function(h,i){
    var gv=function(p){ var el=document.getElementById('wh_'+p+'_'+i); return el?el.value:h[({city:'city',name:'hotelName',in:'checkIn',out:'checkOut',rooms:'rooms',rsv:'rsvNo',notes:'notes'})[p]]; };
    h.city=gv('city'); h.hotelName=gv('name'); h.checkIn=gv('in'); h.checkOut=gv('out');
    h.rooms=gv('rooms'); h.rsvNo=gv('rsv'); h.notes=gv('notes');
  });
}
function wzHotelSync(){ // chain: each hotel check-in = previous check-out; nights auto
  wzHotelCapture();
  var w=state.wizard;
  for(var i=1;i<w.hotels.length;i++){ w.hotels[i].checkIn = w.hotels[i-1].checkOut || ''; }
  renderShell();
}
function wzAddHotel(){ wzHotelCapture(); state.wizard.hotels.push({city:'',hotelName:'',rooms:'',checkIn:'',checkOut:'',rsvNo:'',notes:''}); renderShell(); }
function wzRemoveHotel(i){ wzHotelCapture(); state.wizard.hotels.splice(i,1); if(!state.wizard.hotels.length) state.wizard.hotels=[{city:'',hotelName:'',rooms:'',checkIn:'',checkOut:'',rsvNo:'',notes:''}]; renderShell(); }

/* ═══════ STEP: HOTEL BRN (skip / manual — comes before Hotels now) ═══════ */
function wzStepBrn(){
  var w=state.wizard;
  var skipChecked=w.brnSkip?'checked':'';
  var top='<div class="wz-toolbar">'+
    '<label style="display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;cursor:pointer"><input type="checkbox" '+skipChecked+' onchange="wzBrnToggleSkip(this.checked)"/> '+t('brnSkip')+'</label>'+
    '</div>';
  if(w.brnSkip) return '<div class="card-hd"><div class="card-title">📄 '+t('wzBrn')+'</div></div>'+top+
    '<div style="color:var(--muted);font-size:.82rem;padding:16px 4px;text-align:center">'+t('brnSkip')+'</div>';
  if(!w.brn.length) w.brn=[{hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}];
  var rows=w.brn.map(function(b,i){ return brnRow(b,i); }).join('');
  return '<div class="card-hd"><div class="card-title">📄 '+t('wzBrn')+'</div>'+
    '<button class="btn btn-primary btn-sm" onclick="wzAddBrn()">'+t('addBrn')+'</button></div>'+top+
    '<div id="wzBrnRows">'+rows+'</div>';
}
function brnRow(b,i){
  var w=state.wizard, g=w.group;
  var isFirst=(i===0), isLast=(i===w.brn.length-1);
  // check-in: first = arrival ±1; others locked to previous check-out
  var ci;
  if(isFirst){
    ci='<select id="wb_in_'+i+'" onchange="wzBrnSync()">'+dateSelectOpts(g.arrivalDate,b.checkIn)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkInRule')+'</div>';
  } else {
    ci='<input id="wb_in_'+i+'" value="'+escapeAttr(b.checkIn)+'" readonly style="background:var(--bg2)"/>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('lockedPrev')+'</div>';
  }
  // check-out: last = departure ±1; others free date (chained forward)
  var co;
  if(isLast){
    co='<select id="wb_out_'+i+'" onchange="wzBrnSync()">'+dateSelectOpts(g.departureDate,b.checkOut)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkOutRuleLast')+'</div>';
  } else {
    co='<input id="wb_out_'+i+'" type="date"'+(b.checkIn?' min="'+escapeAttr(b.checkIn)+'"':dateMinAttr('wb_out_'+i))+' value="'+escapeAttr(b.checkOut)+'" onchange="wzBrnSync()"/>';
  }
  return '<div class="wz-card">'+
    '<div class="wz-card-hd"><b>#'+(i+1)+'</b>'+(w.brn.length>1?'<span class="sub-x" onclick="wzRemoveBrn('+i+')">✕ '+t('removeRow')+'</span>':'')+'</div>'+
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('hotelLbl')+'</label><input id="wb_name_'+i+'" value="'+escapeAttr(b.hotelName)+'" oninput="wzBrnCapture()"/></div>'+
    '<div class="field"><label>'+t('brnNumber')+' *</label><input id="wb_num_'+i+'" value="'+escapeAttr(b.brnNumber)+'" oninput="wzBrnCapture()"/></div>'+
    '<div class="field"><label>'+t('checkIn')+'</label>'+ci+'</div>'+
    '<div class="field"><label>'+t('checkOut')+'</label>'+co+'</div>'+
    '<div class="field"><label>'+t('roomsCount')+'</label><input id="wb_rooms_'+i+'" type="number" value="'+escapeAttr(b.roomsCount)+'" oninput="wzBrnCapture()"/></div>'+
    '</div></div>';
}
function wzBrnSync(){ // chain: each BRN check-in = previous check-out
  wzBrnCapture();
  var w=state.wizard;
  for(var i=1;i<w.brn.length;i++){ w.brn[i].checkIn = w.brn[i-1].checkOut || ''; }
  renderShell();
}
function wzBrnCapture(){
  var w=state.wizard;
  // guard: only capture when the BRN inputs are actually in the DOM,
  // otherwise we'd wipe previously captured data (e.g. capturing at submit from the review step)
  if(!document.getElementById('wb_num_0')) return;
  w.brn.forEach(function(b,i){
    var gv=function(p,cur){ var el=document.getElementById('wb_'+p+'_'+i); return el?el.value:cur; };
    b.hotelName=gv('name',b.hotelName); b.brnNumber=gv('num',b.brnNumber);
    b.checkIn=gv('in',b.checkIn); b.checkOut=gv('out',b.checkOut); b.roomsCount=gv('rooms',b.roomsCount);
  });
}
function wzBrnToggleSkip(on){ if(!on) wzBrnCapture(); state.wizard.brnSkip=on; if(on) state.wizard.brn=[]; renderShell(); }
function wzAddBrn(){ wzBrnCapture(); state.wizard.brn.push({hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}); renderShell(); }
function wzRemoveBrn(i){ wzBrnCapture(); state.wizard.brn.splice(i,1); if(!state.wizard.brn.length) state.wizard.brn=[{hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}]; renderShell(); }

/* ═══════ STEP: CATERING BRN (skip / copy-dates-from-Hotel-BRN / manual) ═══════ */
function wzStepCatering(){
  var w=state.wizard;
  var skipChecked=w.cateringSkip?'checked':'';
  // offer "copy from Hotel BRN" only when Hotel BRN has data to copy
  var hasBrn = !w.brnSkip && (w.brn||[]).some(function(b){ return b.checkIn||b.checkOut||b.hotelName; });
  var top='<div class="wz-toolbar">'+
    '<label style="display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;cursor:pointer"><input type="checkbox" '+skipChecked+' onchange="wzCateringToggleSkip(this.checked)"/> '+t('cateringSkip')+'</label>'+
    (w.cateringSkip||!hasBrn?'':'<button class="btn btn-ghost btn-sm" onclick="wzCateringCopyBrn()">'+t('cateringCopyBrn')+'</button>')+
    '</div>';
  if(w.cateringSkip) return '<div class="card-hd"><div class="card-title">🍽️ '+t('wzCatering')+'</div></div>'+top+
    '<div style="color:var(--muted);font-size:.82rem;padding:16px 4px;text-align:center">'+t('cateringSkip')+'</div>';
  if(!w.catering.length) w.catering=[{hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}];
  var rows=w.catering.map(function(b,i){ return cateringRow(b,i); }).join('');
  return '<div class="card-hd"><div class="card-title">🍽️ '+t('wzCatering')+'</div>'+
    '<button class="btn btn-primary btn-sm" onclick="wzAddCatering()">'+t('addCatering')+'</button></div>'+top+
    '<div id="wzCateringRows">'+rows+'</div>';
}
function cateringRow(b,i){
  var w=state.wizard, g=w.group;
  var isFirst=(i===0), isLast=(i===w.catering.length-1);
  var ci;
  if(isFirst){
    ci='<select id="wcx_in_'+i+'" onchange="wzCateringSync()">'+dateSelectOpts(g.arrivalDate,b.checkIn)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkInRule')+'</div>';
  } else {
    ci='<input id="wcx_in_'+i+'" value="'+escapeAttr(b.checkIn)+'" readonly style="background:var(--bg2)"/>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('lockedPrev')+'</div>';
  }
  var co;
  if(isLast){
    co='<select id="wcx_out_'+i+'" onchange="wzCateringSync()">'+dateSelectOpts(g.departureDate,b.checkOut)+'</select>'+
       '<div style="font-size:.63rem;color:var(--muted)">'+t('checkOutRuleLast')+'</div>';
  } else {
    co='<input id="wcx_out_'+i+'" type="date"'+(b.checkIn?' min="'+escapeAttr(b.checkIn)+'"':dateMinAttr('wcx_out_'+i))+' value="'+escapeAttr(b.checkOut)+'" onchange="wzCateringSync()"/>';
  }
  return '<div class="wz-card">'+
    '<div class="wz-card-hd"><b>#'+(i+1)+'</b>'+(w.catering.length>1?'<span class="sub-x" onclick="wzRemoveCatering('+i+')">✕ '+t('removeRow')+'</span>':'')+'</div>'+
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('cateringLbl')+'</label><input id="wcx_name_'+i+'" value="'+escapeAttr(b.hotelName)+'" oninput="wzCateringCapture()"/></div>'+
    '<div class="field"><label>'+t('brnNumber')+' *</label><input id="wcx_num_'+i+'" value="'+escapeAttr(b.brnNumber)+'" oninput="wzCateringCapture()"/></div>'+
    '<div class="field"><label>'+t('checkIn')+'</label>'+ci+'</div>'+
    '<div class="field"><label>'+t('checkOut')+'</label>'+co+'</div>'+
    '<div class="field"><label>'+t('roomsCount')+'</label><input id="wcx_rooms_'+i+'" type="number" value="'+escapeAttr(b.roomsCount)+'" oninput="wzCateringCapture()"/></div>'+
    '</div></div>';
}
function wzCateringSync(){
  wzCateringCapture();
  var w=state.wizard;
  for(var i=1;i<w.catering.length;i++){ w.catering[i].checkIn = w.catering[i-1].checkOut || ''; }
  renderShell();
}
function wzCateringCapture(){
  var w=state.wizard;
  if(!document.getElementById('wcx_num_0')) return;
  w.catering.forEach(function(b,i){
    var gv=function(p,cur){ var el=document.getElementById('wcx_'+p+'_'+i); return el?el.value:cur; };
    b.hotelName=gv('name',b.hotelName); b.brnNumber=gv('num',b.brnNumber);
    b.checkIn=gv('in',b.checkIn); b.checkOut=gv('out',b.checkOut); b.roomsCount=gv('rooms',b.roomsCount);
  });
}
function wzCateringToggleSkip(on){ if(!on) wzCateringCapture(); state.wizard.cateringSkip=on; if(on) state.wizard.catering=[]; renderShell(); }
/* copy the SAME dates (and rooms) from Hotel BRN; user fills catering name + BRN # */
function wzCateringCopyBrn(){
  wzBrnCapture();
  var src=(state.wizard.brn||[]).filter(function(b){ return b.checkIn||b.checkOut||b.hotelName; });
  if(!src.length) return;
  state.wizard.catering=src.map(function(b){ return {hotelName:'',brnNumber:'',checkIn:b.checkIn||'',checkOut:b.checkOut||'',roomsCount:b.roomsCount||''}; });
  renderShell();
}
function wzAddCatering(){ wzCateringCapture(); state.wizard.catering.push({hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}); renderShell(); }
function wzRemoveCatering(i){ wzCateringCapture(); state.wizard.catering.splice(i,1); if(!state.wizard.catering.length) state.wizard.catering=[{hotelName:'',brnNumber:'',checkIn:'',checkOut:'',roomsCount:''}]; renderShell(); }

/* ═══════ STEP (individual): HOSTING DETAILS ═══════ */
function wzStepHosting(){
  var w=state.wizard, h=w.host||{};
  return '<div class="card-title" style="margin-bottom:14px">🏠 '+t('wzHosting')+'</div>'+
    '<div class="wz-card"><div class="form-grid">'+
    field('wh_name',t('hostName')+' *',h.name)+
    field('wh_id',t('hostId')+' *',h.id)+
    field('wh_dob',t('dob'),h.dob,'date')+
    field('wh_nat',t('nationality'),h.nationality)+
    field('wh_mob',t('mobile'),h.mobile)+
    '<div class="field"><label>'+t('residenceCity')+' *</label><input id="wh_city" value="'+escapeAttr(h.city)+'"/></div>'+
    '</div>'+
    field('wh_addr',t('address'),h.address)+
    '<div class="field"><label>'+t('idCopy')+'</label>'+
    '<input type="file" id="wh_idfile" accept=".pdf,.jpg,.jpeg,.png" onchange="wzHostIdPick(this)"/>'+
    '<div id="hostIdMsg" style="font-size:.75rem;color:var(--green);margin-top:5px">'+(w.hostIdFile?t('idUploaded')+' ('+esc(w.hostIdFile.name)+')':'')+'</div></div>'+
    '</div>';
}
function wzHostCapture(){
  if(!document.getElementById('wh_name')) return;
  state.wizard.host={
    name:val('wh_name'), id:val('wh_id'), dob:val('wh_dob'), nationality:val('wh_nat'),
    mobile:val('wh_mob'), city:val('wh_city'), address:val('wh_addr')
  };
}
function wzHostIdPick(input){
  var f=input.files&&input.files[0]; if(!f) return;
  if(f.size>8*1024*1024){ toast('Max 8MB','err'); input.value=''; return; }
  var reader=new FileReader();
  reader.onload=function(){ state.wizard.hostIdFile={name:f.name, base64:reader.result};
    var m=document.getElementById('hostIdMsg'); if(m) m.textContent=t('idUploaded')+' ('+f.name+')'; };
  reader.readAsDataURL(f);
}

/* ═══════ STEP 4: TRIPS (auto-generated + editable inline) ═══════ */
// airport departure lead-time rules (hours before flight), Air only
function departureLeadHours(fromCity, port){
  var c=(fromCity||'').toUpperCase(), p=(port||'').toUpperCase();
  var isJED=p.indexOf('JED')!==-1, isMED=p.indexOf('MED')!==-1;
  if(c.indexOf('MAK')!==-1 && isJED) return 5;
  if(c.indexOf('MADI')!==-1 && isJED) return 12;
  if(c.indexOf('MADI')!==-1 && isMED) return 4;
  return null;
}
function computeDepartureBus(g, fromCity){
  // returns {date,time} for the departure bus given flight date/time & rules (Air only)
  if(g.departureMode!=='Air') return {date:g.departureDate,time:''};
  var lead=departureLeadHours(fromCity, g.departurePort);
  if(lead===null || !g.departureDate || !g.departureTime) return {date:g.departureDate,time:''};
  var tm=g.departureTime.match(/^(\d{1,2}):(\d{2})/); if(!tm) return {date:g.departureDate,time:''};
  var dt=new Date(Date.UTC(+ymd(g.departureDate).slice(0,4), +ymd(g.departureDate).slice(5,7)-1, +ymd(g.departureDate).slice(8,10), +tm[1], +tm[2]));
  dt.setUTCHours(dt.getUTCHours()-lead);
  return { date: dt.toISOString().slice(0,10), time: dt.toISOString().slice(11,16) };
}
/* trip-type select that always includes the current value even if not in masters */
function tripTypeOptions(sel){
  var masters=((state.cache.mtypes||{}).tripType)||[];
  var opts=[['','–']];
  var found=false;
  masters.forEach(function(m){
    opts.push([m.value_en, state.lang==='ar'?(m.value_ar||m.value_en):(m.value_en||m.value_ar)]);
    if(m.value_en===sel) found=true;
  });
  if(sel && !found) opts.push([sel, sel]); // preserve generated label
  return selectFieldOpts(opts, sel);
}
function tripTypeValue(kind, city){
  // match a master tripType value_en; fall back to a sensible label
  var masters=(state.cache.mtypes&&state.cache.mtypes.tripType)||[];
  function find(pred){ var m=masters.filter(pred)[0]; return m?m.value_en:null; }
  var c=(city||'').toLowerCase();
  if(kind==='arrival')   return find(function(m){return /arriv/i.test(m.value_en);}) || 'Arrival';
  if(kind==='departure') return find(function(m){return /depart/i.test(m.value_en);}) || 'Departure';
  if(kind==='between')   return find(function(m){return /between|cities/i.test(m.value_en);}) || 'Between Cities';
  if(kind==='mazarat'){
    // prefer a city-specific mazarat entry (e.g. "Makkah Mazarat"), else generic
    var byCity=find(function(m){ return /mazarat|ziyarat/i.test(m.value_en) && c && m.value_en.toLowerCase().indexOf(c)!==-1; });
    return byCity || find(function(m){return /mazarat|ziyarat/i.test(m.value_en);}) || ('Mazarat '+(city||''));
  }
  return '';
}
function generateTrips(){
  var w=state.wizard, g=w.group;
  var trips=[];
  var mode=g.arrivalMode||'Air';
  if(w.kind==='Individual'){
    wzHostCapture();
    trips.push({tripType:t('arrivalTrip'),transportMode:mode,date:g.arrivalDate,time:g.arrivalTime,
      from:g.arrivalPort,fromDetail:'',to:(w.host&&w.host.city)||'',toDetail:'',flightNo:g.arrivalFlight,
      vehicleType:'',buses:'',pax:g.totalPax});
    return trips;
  }
  wzHotelCapture();
  var hotels=w.hotels.filter(function(h){return h.city||h.hotelName;});
  var cities=[]; hotels.forEach(function(h){ if(h.city && cities.indexOf(h.city)===-1) cities.push(h.city); });
  if(cities.length){
    trips.push({tripType:tripTypeValue('arrival'),transportMode:mode,date:g.arrivalDate,time:g.arrivalTime,
      from:g.arrivalPort,fromDetail:'',to:cities[0],toDetail:'',flightNo:g.arrivalFlight,buses:'',pax:g.totalPax});
    trips.push({tripType:tripTypeValue('mazarat',cities[0]),transportMode:'Land',date:shiftDate(g.arrivalDate,2),time:'07:00',
      from:cities[0],fromDetail:'',to:'',toDetail:'',flightNo:'',buses:'',pax:g.totalPax});
    if(cities.length>=2){
      var h2=hotels.filter(function(h){return h.city===cities[1];})[0]||{};
      trips.push({tripType:tripTypeValue('between'),transportMode:'Land',date:h2.checkIn||'',time:'',
        from:cities[0],fromDetail:'',to:cities[1],toDetail:'',flightNo:'',buses:'',pax:g.totalPax});
      trips.push({tripType:tripTypeValue('mazarat',cities[1]),transportMode:'Land',date:shiftDate(h2.checkIn||'',2),time:'07:00',
        from:cities[1],fromDetail:'',to:'',toDetail:'',flightNo:'',buses:'',pax:g.totalPax});
    }
    var lastCity=cities[cities.length-1];
    var dep=computeDepartureBus(g,lastCity);
    trips.push({tripType:tripTypeValue('departure'),transportMode:g.departureMode||'Air',date:dep.date,time:dep.time,
      from:lastCity,fromDetail:'',to:g.departurePort,toDetail:'',flightNo:g.departureFlight,buses:'',pax:g.totalPax});
  }
  return trips;
}
function wzStepTrips(){
  var w=state.wizard;
  if(!w.tripsGenerated){ w.trips=generateTrips(); w.tripsGenerated=true; }
  if(!w.group.transportBy) w.group.transportBy='Operator';
  var rows=w.trips.map(function(tr,i){ return tripRow(tr,i); }).join('');
  var tbRadio='<div class="wz-toolbar"><span style="font-size:.8rem;font-weight:700">'+t('transportBy')+':</span>'+
    radioGroup('wzTransBy',[['Agent',t('byAgent')],['Operator',t('byOperator')]],w.group.transportBy,'wzTransByChange()')+'</div>';
  return '<div class="card-hd"><div class="card-title">🚌 '+t('wzTrips')+'</div>'+
    '<div style="display:flex;gap:6px"><button class="btn btn-ghost btn-sm" onclick="wzRegenTrips()">'+t('regenTrips')+'</button>'+
    '<button class="btn btn-primary btn-sm" onclick="wzAddTrip()">'+t('addTripRow')+'</button></div></div>'+
    tbRadio+
    '<div style="font-size:.72rem;color:var(--muted);margin-bottom:10px">'+t('autoGen')+'</div>'+
    '<div class="tbl-wrap"><table class="wz-trip-tbl"><thead><tr>'+
    '<th>#</th><th>'+t('tripTypeLbl')+'</th><th>'+t('tripDate')+' *</th><th>'+t('dayLbl')+'</th><th>'+t('timeLbl')+'</th>'+
    '<th>'+t('fromLbl')+'</th><th>'+t('toLbl')+' *</th><th>'+t('vehicleType')+'</th><th>'+t('vehicles')+'</th><th></th></tr></thead>'+
    '<tbody id="wzTripRows">'+(rows||'<tr class="no-data"><td colspan="10">'+t('dropHere')+'</td></tr>')+'</tbody></table></div>'+
    tripMoveDatalist();
}
function wzTransByChange(){
  var v=(document.querySelector('input[name=wzTransBy]:checked')||{}).value||'Operator';
  state.wizard.group.transportBy=v;
  document.querySelectorAll('input[name=wzTransBy]').forEach(function(r){
    r.closest('label').style.borderColor = r.checked?'var(--blue)':'var(--border)';
  });
}
function vehicleOptions(sel){
  var vs=((state.cache.mtypes||{}).vehicleType)||[];
  var opts=[['','–']];
  vs.forEach(function(v){
    var lbl=(state.lang==='ar'?(v.value_ar||v.value_en):(v.value_en||v.value_ar));
    opts.push([v.value_en, lbl+(v.meta?' ('+v.meta+')':'')]);
  });
  if(sel && !opts.some(function(o){return o[0]===sel;})) opts.push([sel,sel]);
  return selectFieldOpts(opts, sel);
}
function vehicleCapacity(name){
  var vs=((state.cache.mtypes||{}).vehicleType)||[];
  var v=vs.find(function(x){return x.value_en===name;});
  return v?(parseInt(v.meta)||0):0;
}
function tripMoveDatalist(dlId){
  var masters=((state.cache.mtypes||{}).tripType)||[];
  return '<datalist id="'+(dlId||'wzMoveTypes')+'">'+masters.map(function(m){
    var lbl=state.lang==='ar'?(m.value_ar||m.value_en):(m.value_en||m.value_ar);
    return '<option value="'+escapeAttr(lbl)+'"></option>';
  }).join('')+'</datalist>';
}
function tripRow(tr,i){
  return '<tr>'+
    '<td>'+(i+1)+'</td>'+
    '<td><input list="wzMoveTypes" id="wt_type_'+i+'" value="'+escapeAttr(tr.tripType)+'" oninput="wzTripCapture()" style="min-width:140px"/></td>'+
    '<td><input id="wt_date_'+i+'" type="date"'+dateMinAttr('wt_date_'+i)+' value="'+escapeAttr(tr.date)+'" onchange="wzTripDateChange('+i+')" style="min-width:130px"/></td>'+
    '<td id="wt_day_'+i+'" style="font-size:.72rem;color:var(--muted);white-space:nowrap">'+clientDayName(tr.date)+'</td>'+
    '<td><input id="wt_time_'+i+'" type="time" value="'+escapeAttr(tr.time)+'" onchange="wzTripCapture()" style="min-width:100px"/></td>'+
    '<td><input id="wt_from_'+i+'" value="'+escapeAttr(tr.from)+'" oninput="wzTripCapture()" style="min-width:100px"/></td>'+
    '<td><input id="wt_to_'+i+'" value="'+escapeAttr(tr.to)+'" oninput="wzTripCapture()" style="min-width:100px"/></td>'+
    '<td><select id="wt_veh_'+i+'" onchange="wzVehChange('+i+')" style="min-width:120px">'+vehicleOptions(tr.vehicleType)+'</select></td>'+
    '<td><input id="wt_buses_'+i+'" type="number" value="'+escapeAttr(tr.buses)+'" oninput="wzTripCapture()" style="min-width:70px"/></td>'+
    '<td><span class="sub-x" onclick="wzRemoveTrip('+i+')" title="'+t('removeRow')+'">✕</span></td></tr>';
}
function wzTripDateChange(i){
  wzTripCapture();
  var el=document.getElementById('wt_day_'+i);
  if(el) el.textContent=clientDayName((document.getElementById('wt_date_'+i)||{}).value||'');
}
function wzVehChange(i){
  var sel=document.getElementById('wt_veh_'+i);
  var veh=sel?sel.value:'';
  var w=state.wizard;
  var cap=vehicleCapacity(veh);
  var pax=parseInt(w.group.totalPax)||0;
  var qty=(cap>0 && pax>0) ? Math.ceil(pax/cap) : '';
  if(qty){ var el=document.getElementById('wt_buses_'+i); if(el) el.value=qty; }

  /* the fleet is almost always uniform, so the first choice fills the blanks
     on every other row — rows already set by hand are left alone */
  if(veh){
    for(var j=0;j<w.trips.length;j++){
      if(j===i) continue;
      var vs=document.getElementById('wt_veh_'+j);
      if(vs && !vs.value){
        vs.value=veh;
        var bs=document.getElementById('wt_buses_'+j);
        if(bs && !bs.value && qty) bs.value=qty;
      }
    }
  }
  wzTripCapture();
}
function wzTripCapture(){
  var w=state.wizard;
  if(!document.getElementById('wt_type_0')) return;
  w.trips.forEach(function(tr,i){
    var gv=function(p,cur){ var el=document.getElementById('wt_'+p+'_'+i); return el?el.value:cur; };
    tr.tripType=gv('type',tr.tripType); tr.date=gv('date',tr.date); tr.time=gv('time',tr.time);
    tr.from=gv('from',tr.from); tr.to=gv('to',tr.to);
    tr.vehicleType=gv('veh',tr.vehicleType); tr.buses=gv('buses',tr.buses);
  });
}
function wzSortTrips(){
  wzTripCapture();
  state.wizard.trips.sort(function(a,b){ return (a.date||'').localeCompare(b.date||'')|| (a.time||'').localeCompare(b.time||''); });
}
function wzAddTrip(){ wzTripCapture(); state.wizard.trips.push({tripType:'',transportMode:'Land',date:'',time:'',from:'',to:'',flightNo:'',buses:'',pax:state.wizard.group.totalPax}); renderShell(); }
function wzRemoveTrip(i){ wzTripCapture(); state.wizard.trips.splice(i,1); renderShell(); }
function wzRegenTrips(){ uiConfirm(t('regenTrips')+'؟', function(){ state.wizard.tripsGenerated=false; renderShell(); }); }

/* ── step: review ── */
function hostDetailHtml(h){
  h=h||{};
  var items=[['hostName',h.name],['hostId',h.id],['dob',h.dob],['nationality',h.nationality],
    ['mobile',h.mobile],['residenceCity',h.city],['address',h.address]];
  return '<div class="detail-list">'+items.map(function(it){
    return '<div class="dl-item"><div class="dl-lbl">'+t(it[0])+'</div><div>'+esc(it[1])+'</div></div>';
  }).join('')+'</div>';
}
function wzStepReview(){
  var w=state.wizard, g=w.group, isInd=w.kind==='Individual';
  var h='<div class="card-title" style="margin-bottom:12px">✅ '+t('wzReview')+'</div>';
  h+='<div class="card-title" style="font-size:.82rem;margin:8px 0">'+t('reviewGroupSec')+' — '+t(isInd?'kindIndividual':'kindGroup')+'</div>'+groupDetailHtml(Object.assign({status:''},g));
  h+='<div style="font-size:.78rem;margin:8px 0;color:var(--muted)"><b>'+t('transportBy')+':</b> '+t(g.transportBy==='Agent'?'byAgent':'byOperator')+'</div>';
  if(isInd){
    h+='<div class="card-title" style="font-size:.82rem;margin:16px 0 8px">🏠 '+t('wzHosting')+'</div>'+hostDetailHtml(w.host)+
      (w.hostIdFile?'<div style="font-size:.75rem;color:var(--green);margin-top:6px">'+t('idUploaded')+' ('+esc(w.hostIdFile.name)+')</div>':'');
  } else {
    h+=reviewTable('🏨 '+t('wzHotels'), w.hotels.filter(function(x){return x.hotelName||x.city;}), function(x){ return [x.city,x.hotelName,x.rooms,x.checkIn,x.checkOut,x.rsvNo]; }, [t('cityLbl'),t('hotelLbl'),t('rooms'),t('checkIn'),t('checkOut'),t('rsvNo')]);
  }
  // Hotel BRN (both kinds of booking now carry BRN)
  if(w.brnSkip) h+='<div class="card-title" style="font-size:.82rem;margin:16px 0 4px">📄 '+t('wzBrn')+'</div><div style="font-size:.78rem;color:var(--muted)">'+t('brnSkipped')+'</div>';
  else h+=reviewTable('📄 '+t('wzBrn'), w.brn.filter(function(x){return x.brnNumber;}), function(x){ return [x.hotelName,x.brnNumber,x.checkIn,x.checkOut,x.roomsCount]; }, [t('hotelLbl'),t('brnNumber'),t('checkIn'),t('checkOut'),t('roomsCount')]);
  // Catering BRN
  if(w.cateringSkip) h+='<div class="card-title" style="font-size:.82rem;margin:16px 0 4px">🍽️ '+t('wzCatering')+'</div><div style="font-size:.78rem;color:var(--muted)">'+t('cateringSkipped')+'</div>';
  else h+=reviewTable('🍽️ '+t('wzCatering'), w.catering.filter(function(x){return x.brnNumber;}), function(x){ return [x.hotelName,x.brnNumber,x.checkIn,x.checkOut,x.roomsCount]; }, [t('cateringLbl'),t('brnNumber'),t('checkIn'),t('checkOut'),t('roomsCount')]);
  h+=reviewTable('🚌 '+t('wzTrips'), w.trips.filter(function(x){return x.date||x.tripType;}), function(x){ return [x.date,clientDayName(x.date),x.tripType,(x.from||'')+'→'+(x.to||''),x.time,x.vehicleType,x.buses]; }, [t('tripDate'),t('dayLbl'),t('tripTypeLbl'),t('route'),t('timeLbl'),t('vehicleType'),t('vehicles')]);
  return h;
}
function reviewTable(title, arr, mapFn, headers){
  var rows=(arr||[]).map(function(x){ return '<tr>'+mapFn(x).map(function(v){return '<td>'+esc(v)+'</td>';}).join('')+'</tr>'; }).join('');
  return '<div class="card-title" style="font-size:.82rem;margin:16px 0 8px">'+title+' ('+(arr?arr.length:0)+')</div>'+
    '<div class="tbl-wrap"><table><thead><tr>'+headers.map(function(hd){return '<th>'+hd+'</th>';}).join('')+'</tr></thead><tbody>'+
    (rows||'<tr class="no-data"><td colspan="'+headers.length+'">'+t('emptyStep')+'</td></tr>')+'</tbody></table></div>';
}

/* ── nav + submit ── */
/* ── step validation ──
   Returns a list of {id,msg}. Field ids get highlighted so the user can see
   exactly what's missing instead of guessing from a generic toast. */
function wzValidate(name){
  var w=state.wizard, errs=[];
  var need=function(id,cond,msg){ if(!cond) errs.push({id:id,msg:msg}); };

  if(name==='wzGroup'){
    captureGroupStep();
    var g=w.group||{};   // read AFTER the capture — it assigns a fresh object
    if(w.mode==='operator') need('wzAgent', w.agentCode, t('errNoAgent'));
    need('', (g.groupName||g.groupCode), t('atLeastOneGroup'));
    need('gAdults', parseInt(g.totalPax)>0, t('vPax'));
    need('gArrDate', g.arrivalDate, t('vArrivalDate'));
    need('gDepDate', g.departureDate, t('vDepartureDate'));
    if(g.arrivalDate && g.departureDate && g.departureDate < g.arrivalDate)
      errs.push({id:'gDepDate', msg:t('vDepAfterArr')});
    if(isPastDate(g.arrivalDate))   errs.push({id:'gArrDate', msg:t('arrivalDate')+': '+t('vNoPastDate2')});
    if(isPastDate(g.departureDate)) errs.push({id:'gDepDate', msg:t('departureDate')+': '+t('vNoPastDate2')});
    need('gArrPort', g.arrivalPort, t('vArrivalPort'));
    need('gDepPort', g.departurePort, t('vDeparturePort'));
    need('gLeader', g.leaderName, t('vLeader'));
    need('gLeaderMob', g.leaderMobile, t('vLeaderMobile'));
  }
  else if(name==='wzHotels'){
    wzHotelCapture();
    var valid=w.hotels.filter(function(h){ return h.city && h.hotelName; });
    if(!valid.length) errs.push({id:'wh_name_0', msg:t('atLeastOneHotel')});
    w.hotels.forEach(function(h,i){
      if(!h.city && !h.hotelName && w.hotels.length>1) return;   // blank spare row
      if(!h.city)      errs.push({id:'wh_city_'+i, msg:t('vHotelCity')+' #'+(i+1)});
      if(!h.hotelName) errs.push({id:'wh_name_'+i, msg:t('vHotelName')+' #'+(i+1)});
      if(!h.checkIn)   errs.push({id:'wh_in_'+i,   msg:t('vCheckIn')+' #'+(i+1)});
      if(!h.checkOut)  errs.push({id:'wh_out_'+i,  msg:t('vCheckOut')+' #'+(i+1)});
      if(h.checkIn && h.checkOut && h.checkOut <= h.checkIn)
        errs.push({id:'wh_out_'+i, msg:t('vCheckOutAfter')+' #'+(i+1)});
      if(isPastDate(h.checkIn))  errs.push({id:'wh_in_'+i,  msg:t('checkIn')+' #'+(i+1)+': '+t('vNoPastDate2')});
      if(isPastDate(h.checkOut)) errs.push({id:'wh_out_'+i, msg:t('checkOut')+' #'+(i+1)+': '+t('vNoPastDate2')});
    });
  }
  else if(name==='wzBrn'){
    if(!w.brnSkip){
      wzBrnCapture();
      w.brn.forEach(function(b,i){
        if(!b.brnNumber) errs.push({id:'wb_num_'+i, msg:t('vBrnNumber')+' #'+(i+1)});
      });
    }
  }
  else if(name==='wzCatering'){
    if(!w.cateringSkip){
      wzCateringCapture();
      w.catering.forEach(function(b,i){
        if(!b.brnNumber) errs.push({id:'wcx_num_'+i, msg:t('vBrnNumber')+' #'+(i+1)});
      });
    }
  }
  else if(name==='wzHosting'){
    wzHostCapture();
    need('wh_name', w.host.name,   t('vHostName'));
    need('wh_id',   w.host.id,     t('vHostId'));
    need('wh_city', w.host.city,   t('vHostCity'));
    need('wh_mob',  w.host.mobile, t('vHostMobile'));
  }
  else if(name==='wzTrips'){
    wzTripCapture();
    if(!w.trips.length) errs.push({id:'', msg:t('vNoTrips')});
    w.trips.forEach(function(tr,i){
      if(!tr.date)        errs.push({id:'wt_date_'+i,  msg:t('vTripDate')+' #'+(i+1)});
      if(!tr.tripType)    errs.push({id:'wt_type_'+i,  msg:t('vTripType')+' #'+(i+1)});
      if(!tr.vehicleType) errs.push({id:'wt_veh_'+i,   msg:t('vVehicleType')+' #'+(i+1)});
      if(!(parseInt(tr.buses)>0)) errs.push({id:'wt_buses_'+i, msg:t('vVehicleQty')+' #'+(i+1)});
      if(isPastDate(tr.date))     errs.push({id:'wt_date_'+i, msg:t('tripDate')+' #'+(i+1)+': '+t('vNoPastDate2')});
    });
    // mazarat legs must fall before the trip that follows them
    for(var i=0;i<w.trips.length-1;i++){
      var a=w.trips[i], b=w.trips[i+1];
      if(/mazarat|ziyarat|مزارات/i.test(a.tripType||'') && a.date && b.date && a.date>=b.date)
        errs.push({id:'wt_date_'+i, msg:t('mazaratOrder')+' (#'+(i+1)+')'});
    }
  }
  return errs;
}
/* A `min` attribute only constrains the date PICKER — a value that is typed or
   pasted straight into the box sails past it. So every date is checked here too. */
function isPastDate(v){
  if(!v) return false;
  if(state.user && state.user.username==='admin') return false;   // admin may backdate
  return v < todayStrLocal();
}
function pastDateErr(id, v, label){
  return { id:id, ok:!isPastDate(v), msg:(label||t('vNoPastDate'))+': '+t('vNoPastDate2') };
}

/* modal-level validation: same highlight + toast behaviour as the wizard */
/* keep the check-out calendar's floor pinned to whatever check-in currently says */
function hModalDates(){
  var ci=document.getElementById('hIn'), co=document.getElementById('hOut');
  if(!ci||!co) return;
  if(ci.value){ co.min=ci.value; if(co.value && co.value<=ci.value) co.value=''; }
}
function mValidate(rules){
  document.querySelectorAll('.field-err').forEach(function(el){ el.classList.remove('field-err'); });
  var bad=rules.filter(function(r){ return !r.ok; });
  bad.forEach(function(r){ var el=document.getElementById(r.id); if(el) el.classList.add('field-err'); });
  if(bad.length){ toast(bad[0].msg,'err'); return false; }
  return true;
}
function wzShowErrors(errs){
  document.querySelectorAll('.field-err').forEach(function(el){ el.classList.remove('field-err'); });
  errs.forEach(function(e){
    if(!e.id) return;
    var el=document.getElementById(e.id);
    if(el) el.classList.add('field-err');
  });
  var box=document.getElementById('wzErrBox');
  if(box){
    box.innerHTML = errs.length
      ? '<div class="wz-errs"><b>⚠️ '+t('vFixBelow')+'</b><ul>'+
        errs.slice(0,8).map(function(e){ return '<li>'+esc(e.msg)+'</li>'; }).join('')+
        (errs.length>8?'<li>… +'+(errs.length-8)+'</li>':'')+'</ul></div>'
      : '';
  }
  if(errs.length){
    var first=errs.filter(function(e){return e.id;})[0];
    if(first){ var fe=document.getElementById(first.id); if(fe && fe.scrollIntoView) fe.scrollIntoView({block:'center',behavior:'smooth'}); }
    toast(errs[0].msg,'err');
  }
}
function wzNext(){
  var w=state.wizard, name=wzStepName();
  var errs=wzValidate(name);
  if(errs.length){ wzShowErrors(errs); return; }
  wzShowErrors([]);

  if(name==='wzHotels'){
    w.hotels=w.hotels.filter(function(h){ return h.city && h.hotelName; });
    var last=w.hotels[w.hotels.length-1];
    var okDates=[shiftDate(w.group.departureDate,-1),shiftDate(w.group.departureDate,0),shiftDate(w.group.departureDate,1)];
    if(last && last.checkOut && w.group.departureDate && okDates.indexOf(last.checkOut)===-1) toast(t('hotelChainWarn'),'');
  }
  else if(name==='wzTrips'){ wzSortTrips(); }

  w.step++; wzSaveDraft(true); renderShell();
}
function wzBack(){
  var w=state.wizard, name=wzStepName();
  if(name==='wzHotels') wzHotelCapture();
  else if(name==='wzBrn' && !w.brnSkip) wzBrnCapture();
  else if(name==='wzCatering' && !w.cateringSkip) wzCateringCapture();
  else if(name==='wzHosting') wzHostCapture();
  else if(name==='wzTrips') wzTripCapture();
  w.step--; renderShell();
}
function wzSubmit(){
  var w=state.wizard;
  // captures are DOM-guarded, so calling them from the review step is safe
  wzHotelCapture(); if(!w.brnSkip) wzBrnCapture(); if(!w.cateringSkip) wzCateringCapture(); wzTripCapture(); wzHostCapture();

  /* final gate: re-validate EVERY step, not just the last one — a user can reach
     Review, go back, blank a field, and jump forward again via the step bar */
  var bad=[];
  w.steps.forEach(function(st){
    if(st==='wzReview') return;
    wzValidate(st).forEach(function(e){ bad.push({step:st, msg:e.msg}); });
  });
  if(bad.length){
    var idx=w.steps.indexOf(bad[0].step);
    toast(bad[0].msg,'err');
    if(idx>=0){ w.step=idx+1; renderShell(); setTimeout(function(){ wzShowErrors(wzValidate(bad[0].step)); },30); }
    return;
  }
  var isInd=w.kind==='Individual';
  var hotels=isInd?[]:w.hotels.filter(function(h){ return h.city||h.hotelName; });
  // both BRN kinds live in one array, distinguished by brnType
  var hotelBrn=w.brnSkip?[]:w.brn.filter(function(b){ return b.brnNumber; }).map(function(b){ return Object.assign({},b,{brnType:'Hotel'}); });
  var cateringBrn=w.cateringSkip?[]:w.catering.filter(function(b){ return b.brnNumber; }).map(function(b){ return Object.assign({},b,{brnType:'Catering'}); });
  var brn=hotelBrn.concat(cateringBrn);
  var trips=w.trips.filter(function(tr){ return tr.date||tr.tripType; });
  if(isInd){
    w.group.hostJSON=JSON.stringify(w.host||{});
    if(w.hostIdFile){ w.group.hostIdBase64=w.hostIdFile.base64; w.group.hostIdName=w.hostIdFile.name; }
  }
  var btn=document.getElementById('wzSubmitBtn'); if(btn){ btn.disabled=true; btn.innerHTML='<span class="mini-spin"></span>'; }
  var payload={group:w.group,hotels:hotels,brn:brn,trips:trips};
  if(w.draftId) payload.draftId=w.draftId;   // promote the draft instead of orphaning it
  function fail(err){ toast(t(err==='NO_AGENT'?'errNoAgent':'errMissing'),'err'); if(btn){ btn.disabled=false; btn.textContent=t('submit'); } }
  if(w.mode==='operator'){
    payload.agentCode=w.agentCode;
    apiCall('groups.createFull',payload).then(function(res){
      if(res.ok){ w.done=true; w.reqId=res.groupId; w.createdFileNo=res.fileNo; renderShell(); }
      else fail(res.error);
    });
  } else if(w.editGroupId){
    payload.editGroupId=w.editGroupId;
    apiCall('requests.submit',payload).then(function(res){
      if(res.ok){ w.done=true; w.reqId=res.requestId; renderShell(); }
      else fail(res.error);
    });
  } else if(w.resubmitId){
    payload.requestId=w.resubmitId;
    apiCall('requests.resubmit',payload).then(function(res){
      if(res.ok){ w.done=true; w.reqId=res.requestId; renderShell(); }
      else fail(res.error);
    });
  } else {
    apiCall('requests.submit',payload).then(function(res){
      if(res.ok){ w.done=true; w.reqId=res.requestId; renderShell(); }
      else fail(res.error);
    });
  }
}
function renderWizardSuccess(c){
  var w=state.wizard, isOp=w.mode==='operator';
  c.innerHTML='<div class="card success-box"><div class="big">🎉</div>'+
    '<div style="font-size:1.1rem;font-weight:900;margin-bottom:6px">'+(isOp?t('saved'):t('reqCreated'))+'</div>'+
    '<div style="color:var(--muted);font-size:.82rem">'+(isOp?t('fileNo'):t('yourReqId'))+'</div>'+
    '<div class="req-id" dir="ltr">'+esc(isOp?w.createdFileNo:w.reqId)+'</div>'+
    (isOp?'':'<div style="color:var(--muted);font-size:.82rem;max-width:420px;margin:0 auto 20px">'+t('reqPendingNote')+'</div>')+
    '<div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:14px">'+
    (isOp?'<button class="btn btn-primary" onclick="state.wizard=null;openGroup(\''+w.reqId+'\')">'+t('view')+'</button>'+
          '<button class="btn btn-ghost" onclick="state.wizard=null;state.view=\'groups\';renderShell()">'+t('groups')+'</button>':
          '<button class="btn btn-primary" onclick="state.wizard=null;state.view=\'requests\';renderShell()">'+t('goToRequests')+'</button>')+
    '<button class="btn btn-ghost" onclick="openRequestForm(\''+w.mode+'\')">'+t('newAnother')+'</button></div></div>';
}
function openReviewModal(requestId){
  var r=(state.cache.requests||[]).find(function(x){return x.requestId===requestId;});
  if(!r) return;
  var isOp = state.user.role==='operator';
  var p=reqPayload(r), g=reqGroup(r);
  var isInd = g.bookingType==='Individual';
  var body='';
  if(r.type==='EditGroup' && r.groupId){
    body+='<div style="background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.35);border-radius:9px;padding:8px 12px;font-size:.78rem;margin-bottom:10px">✏️ '+t('editReqNote')+' <span class="link" onclick="closeModal();openGroup(\''+r.groupId+'\')">'+esc(r.groupId)+'</span></div>'+
    '<div id="editDiffBox"><div class="spinner" style="width:20px;height:20px;margin:8px auto"></div></div>';
  }
  body+='<div style="margin-bottom:8px"><span class="badge '+(isInd?'b-purple':'b-blue')+'">'+t(isInd?'kindIndividual':'kindGroup')+'</span> '+
    '<span style="font-size:.75rem;color:var(--muted)"><b>'+t('transportBy')+':</b> '+t(g.transportBy==='Agent'?'byAgent':'byOperator')+'</span></div>';
  body+=groupDetailHtml(Object.assign({status:r.status},g));
  if(isInd){
    var host={}; try{ host=JSON.parse(g.hostJSON||'{}'); }catch(e){}
    body+='<div class="card-title" style="font-size:.82rem;margin:14px 0 8px">🏠 '+t('wzHosting')+'</div>'+hostDetailHtml(host)+
      (g.hostIdUrl?'<div style="margin-top:8px"><a class="link" href="'+esc(g.hostIdUrl)+'" target="_blank">📎 '+t('viewId')+'</a></div>':'');
  } else {
    body+=reviewTable('🏨 '+t('wzHotels'), p.hotels, function(x){ return [x.city,x.hotelName,x.rooms,x.checkIn,x.checkOut,x.rsvNo]; }, [t('cityLbl'),t('hotelLbl'),t('rooms'),t('checkIn'),t('checkOut'),t('rsvNo')]);
  }
  var rmSplit=wzSplitBrn(p.brn||[]);
  if(!rmSplit.hotel.length) body+='<div class="card-title" style="font-size:.82rem;margin:16px 0 4px">📄 '+t('wzBrn')+'</div><div style="font-size:.78rem;color:var(--muted)">'+t('brnSkipped')+'</div>';
  else body+=reviewTable('📄 '+t('wzBrn'), rmSplit.hotel, function(x){ return [x.hotelName,x.brnNumber,x.checkIn,x.checkOut,x.roomsCount]; }, [t('hotelLbl'),t('brnNumber'),t('checkIn'),t('checkOut'),t('roomsCount')]);
  if(!rmSplit.catering.length) body+='<div class="card-title" style="font-size:.82rem;margin:16px 0 4px">🍽️ '+t('wzCatering')+'</div><div style="font-size:.78rem;color:var(--muted)">'+t('cateringSkipped')+'</div>';
  else body+=reviewTable('🍽️ '+t('wzCatering'), rmSplit.catering, function(x){ return [x.hotelName,x.brnNumber,x.checkIn,x.checkOut,x.roomsCount]; }, [t('cateringLbl'),t('brnNumber'),t('checkIn'),t('checkOut'),t('roomsCount')]);
  body+=reviewTable('🚌 '+t('wzTrips'), p.trips, function(x){ return [x.date,clientDayName(x.date),x.tripType,(x.from||'')+'→'+(x.to||''),x.time,x.vehicleType,x.buses]; }, [t('tripDate'),t('dayLbl'),t('tripTypeLbl'),t('route'),t('timeLbl'),t('vehicleType'),t('vehicles')]);
  if(r.reviewNote) body+='<div class="field" style="margin-top:12px"><label>'+t('reviewNote')+'</label><div style="background:var(--input-bg);border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:.8rem">'+esc(r.reviewNote)+'</div></div>';
  if(isOp && r.status==='Pending'){
    body+='<div class="field" style="margin-top:12px"><label>'+t('reviewNote')+'</label><textarea id="mRvNote" rows="2"></textarea></div>';
  }
  openModal(t(isOp&&r.status==='Pending'?'reviewModal':'requestModal'), body, null);
  modalWide(true);
  if(isOp && r.status==='Pending'){
    document.getElementById('modalFooter').innerHTML=
      '<button class="btn btn-danger" onclick="reviewRequest(\''+requestId+'\',\'reject\')">✕ '+t('reject')+'</button>'+
      '<button class="btn btn-ghost" onclick="reviewRequest(\''+requestId+'\',\'return\')">↩ '+t('returnToAgent')+'</button>'+
      '<button class="btn btn-primary" onclick="reviewRequest(\''+requestId+'\',\'approve\')">✓ '+t('approve')+'</button>';
  }
  if(r.type==='EditGroup' && r.groupId) buildEditDiff(r, p);
}

/* ── auto-detected changes summary for edit requests ── */
function buildEditDiff(r, payload){
  Promise.all([
    apiCall('hotels.list',{groupId:r.groupId}),
    apiCall('transport.list',{groupId:r.groupId}),
    state.cache.groups?Promise.resolve({ok:true,rows:state.cache.groups}):apiCall('groups.list')
  ]).then(function(rs){
    var box=document.getElementById('editDiffBox'); if(!box) return;
    if(!rs[0].ok||!rs[1].ok||!rs[2].ok){ box.innerHTML=''; return; }
    if(!state.cache.groups) state.cache.groups=rs[2].rows;
    var cur=(rs[2].rows||state.cache.groups).find(function(x){return x.groupId===r.groupId;})||{};
    var ng=payload.group||{};
    var items=[];
    // group field diffs
    var fields=[['groupName',t('groupName')],['groupCode',t('groupCode')],['leaderName',t('leaderName')],['leaderMobile',t('leaderMobile')],
      ['adults',t('adults')],['children',t('children')],['infants',t('infants')],['totalPax',t('pax')],
      ['arrivalDate',t('arrivalDate')],['arrivalTime',t('arrivalSec')+' '+t('timeLbl')],['arrivalPort',t('arrivalPort')],['arrivalFlight',t('arrivalFlight')],
      ['departureDate',t('departureDate')],['departureTime',t('departureSec')+' '+t('timeLbl')],['departurePort',t('departurePort')],['departureFlight',t('departureFlight')],
      ['agentRef',t('agentRef')],['transportBy',t('transportBy')],['notes',t('notes')]];
    fields.forEach(function(f){
      var a=(cur[f[0]]||'').toString().trim(), b=(ng[f[0]]||'').toString().trim();
      if(a!==b) items.push('<tr><td><b>'+f[1]+'</b></td>'+
        '<td style="color:var(--red);text-decoration:line-through;opacity:.75">'+esc(a)+'</td>'+
        '<td style="color:var(--green);font-weight:700">'+esc(b)+'</td></tr>');
    });
    // children diffs by signature
    function sect(icon,label,oldArr,newArr,sig){
      var os=(oldArr||[]).map(sig), ns=(newArr||[]).map(sig);
      var added=ns.filter(function(x){return os.indexOf(x)===-1;}).length;
      var removed=os.filter(function(x){return ns.indexOf(x)===-1;}).length;
      if(added||removed) items.push('<tr><td>'+icon+' <b>'+label+'</b></td>'+
        '<td>'+os.length+' '+(removed?'<span style="color:var(--red)">(−'+removed+' '+t('removedLbl')+')</span>':'')+'</td>'+
        '<td>'+ns.length+' '+(added?'<span style="color:var(--green)">(+'+added+' '+t('addedLbl')+')</span>':'')+'</td></tr>');
    }
    sect('🏨',t('hotelsSec2'), rs[0].hotels, payload.hotels, function(h){return [h.city,h.hotelName,h.checkIn,h.checkOut,h.rooms,h.rsvNo].join('|');});
    sect('📄','BRN', rs[0].brn, payload.brn, function(b){return [b.hotelName,b.brnNumber,b.checkIn,b.checkOut,b.roomsCount].join('|');});
    sect('🚌',t('tripsSec2'), rs[1].trips, payload.trips, function(x){return [x.date,x.tripType,x.time,x.from,x.to,x.vehicleType,x.buses].join('|');});
    // host diff for individuals
    try{
      var oh=JSON.parse(cur.hostJSON||'{}'), nh=JSON.parse(ng.hostJSON||'{}');
      [['name',t('hostName')],['id',t('hostId')],['mobile',t('mobile')],['city',t('residenceCity')],['address',t('address')]].forEach(function(f){
        var a=(oh[f[0]]||'').toString(), b=(nh[f[0]]||'').toString();
        if(a!==b) items.push('<tr><td>🏠 <b>'+f[1]+'</b></td>'+
          '<td style="color:var(--red);text-decoration:line-through;opacity:.75">'+esc(a)+'</td>'+
          '<td style="color:var(--green);font-weight:700">'+esc(b)+'</td></tr>');
      });
    }catch(e){}
    box.innerHTML='<div class="diff-box"><div style="font-weight:800;margin-bottom:8px">🔍 '+t('changesSummary')+'</div>'+
      (items.length
        ? '<div class="tbl-wrap"><table class="mini-tbl"><thead><tr><th>'+t('fieldLbl')+'</th><th>'+t('oldValue')+'</th><th>'+t('newValue')+'</th></tr></thead><tbody>'+
          items.join('')+'</tbody></table></div>'
        : '<div style="color:var(--muted)">'+t('noChanges')+'</div>')+'</div>';
  });
}
function reviewRequest(requestId, decision){
  var note=val('mRvNote');
  apiCall('requests.review',{requestId:requestId,decision:decision,note:note}).then(function(res){
    if(res.ok){
      toast(decision==='approve'?t('approvedMsg'):t('rejectedMsg'), decision==='approve'?'ok':'');
      closeModal(); refreshBell(); viewRequests(document.getElementById('content'));
    } else toast(t('errServer'),'err');
  });
}
function cancelRequest(requestId){
  uiConfirm(t('confirmCancel'), function(){
    apiCall('requests.cancel',{requestId:requestId}).then(function(res){
      if(res.ok){ toast(t('saved'),'ok'); viewRequests(document.getElementById('content')); }
    });
  });
}
function resubmitRequest(requestId){
  var r=(state.cache.requests||[]).find(function(x){return x.requestId===requestId;});
  if(!r) return;
  if(r.reviewNote) toast(t('returnedNote'),'');
  openRequestForm('agent', r);
}

/* ════════════════ PHASE 3: GROUP DETAIL ════════════════ */
function loadMasterType(type){
  state.cache.mtypes = state.cache.mtypes||{};
  if(state.cache.mtypes[type]) return Promise.resolve(state.cache.mtypes[type]);
  return apiCall('masters.list',{type:type}).then(function(res){
    state.cache.mtypes[type] = res.ok ? res.rows.filter(function(m){return m.active==='yes';}) : [];
    return state.cache.mtypes[type];
  });
}
function masterOptions(type, selected, withEmpty){
  var opts = withEmpty!==false ? [['','–']] : [];
  ((state.cache.mtypes||{})[type]||[]).forEach(function(m){
    opts.push([m.value_en, state.lang==='ar'?(m.value_ar||m.value_en):(m.value_en||m.value_ar)]);
  });
  return selectFieldOpts(opts, selected);
}
function openGroup(groupId){
  state.currentGroupId=groupId;
  state.view='groupDetail';
  renderShell();
}
function currentGroup(){
  return (state.cache.groups||[]).find(function(x){return x.groupId===state.currentGroupId;});
}
function viewGroupDetail(c){
  var isOp = state.user.role==='operator';
  c.innerHTML='<div class="spinner"></div>';
  var calls=[
    apiCall('hotels.list',{groupId:state.currentGroupId}),
    apiCall('transport.list',{groupId:state.currentGroupId}),
    loadMasterType('hotel'), loadMasterType('city'),
    loadMasterType('transportCompany'), loadMasterType('tripType'), loadMasterType('vehicleType'),
    loadPorts()
  ];
  if(!currentGroup()) calls.push(apiCall('groups.list').then(function(res){ if(res.ok) state.cache.groups=res.rows; }));
  if(isOp && !state.cache.agents) calls.push(apiCall('agents.list').then(function(res){ if(res.ok) state.cache.agents=res.rows; }));
  Promise.all(calls).then(function(rs){
    var g=currentGroup();
    if(!g || !rs[0].ok || !rs[1].ok){ c.innerHTML='<div class="card">–</div>'; return; }
    state.cache.hotels=rs[0].bookings; state.cache.brn=rs[0].brn;
    state.cache.orders=rs[1].orders; state.cache.trips=rs[1].trips;
    renderGroupDetail(c, g, isOp);
  });
}
function secHd(title, btnHtml){
  return '<div class="card-hd"><div class="card-title">'+title+'</div>'+(btnHtml||'')+'</div>';
}
function opBtn(label, onclick){
  return '<button class="btn btn-primary btn-sm" onclick="'+onclick+'">'+label+'</button>';
}
/* Build all group-detail section cards. readOnly=true hides action buttons (used by the popup). */
function groupSectionsHtml(g, isOp, readOnly){
  var isInd = g.bookingType==='Individual';
  var canEdit = isOp && !readOnly;
  var html='';

  /* info */
  var tbVal=g.transportBy==='Agent'?'Agent':'Operator';
  var tbHtml='<div style="margin-top:10px;font-size:.78rem"><b>'+t('transportBy')+':</b> ';
  if(canEdit){
    tbHtml+=['Agent','Operator'].map(function(v){
      return '<button class="fu-btn '+(tbVal===v?'primary':'')+'" style="margin-inline-start:6px" onclick="setTransportBy(\''+g.groupId+'\',\''+v+'\')">'+t(v==='Agent'?'byAgent':'byOperator')+'</button>';
    }).join('');
  } else {
    tbHtml+=t(tbVal==='Agent'?'byAgent':'byOperator');
  }
  tbHtml+='</div>';
  html+='<div class="card">'+secHd('ℹ️ '+t('infoSec'))+
    '<div style="font-size:.72rem;font-weight:700;color:var(--muted)">'+t('stageProgress')+'</div>'+
    stageBarHtml(g.stage||'Visa')+
    groupDetailHtml(g)+tbHtml+
    (isOp?'<div style="margin-top:10px;font-size:.72rem;color:var(--muted)"><b>'+t('agentCol')+':</b> '+esc(agentNameOf(g.agentCode))+'</div>':'')+'</div>';

  /* hosting (individuals) */
  if(isInd){
    var host={}; try{ host=JSON.parse(g.hostJSON||'{}'); }catch(e){}
    html+='<div class="card">'+secHd('🏠 '+t('hostingSec'))+hostDetailHtml(host)+
      (g.hostIdUrl?'<div style="margin-top:10px"><a class="link" href="'+esc(g.hostIdUrl)+'" target="_blank">📎 '+t('viewId')+'</a></div>':'')+'</div>';
  }

  /* hotels + brn (groups only) */
  if(!isInd){
    var hRows=(state.cache.hotels||[]).map(function(h){
      var act;
      if(canEdit) act='<span class="link" onclick="editEntity(\'hotel\',\''+h.bookingId+'\',\''+g.groupId+'\')">'+t('edit')+'</span> · <span class="link" style="color:var(--red)" onclick="delItem(\'hotels.delete\',{bookingId:\''+h.bookingId+'\'})">'+t('deleteLbl')+'</span>';
      else if(!isOp) act='<span class="link" onclick="editEntity(\'hotel\',\''+h.bookingId+'\',\''+g.groupId+'\')">✏️ '+t('requestChange')+'</span>';
      else act='–';
      return '<tr><td>'+esc(h.city)+'</td><td>'+esc(h.hotelName)+'</td><td>'+esc(h.rooms)+'</td>'+
        '<td dir="ltr">'+esc(h.checkIn)+'</td><td dir="ltr">'+esc(h.checkOut)+'</td><td>'+esc(h.nights)+'</td>'+
        '<td>'+stBadge(h.status)+'</td><td style="white-space:nowrap">'+act+'</td></tr>';
    }).join('');
    html+='<div class="card">'+secHd('🏨 '+t('hotelsSec'), canEdit?opBtn(t('addHotel'),'openHotelModal()'):'')+
      '<div class="tbl-wrap"><table><thead><tr><th>'+t('cityLbl')+'</th><th>'+t('hotelLbl')+'</th><th>'+t('rooms')+'</th><th>'+t('checkIn')+'</th><th>'+t('checkOut')+'</th><th>'+t('nights')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead><tbody>'+
      (hRows||'<tr class="no-data"><td colspan="8">'+t('noHotels')+'</td></tr>')+'</tbody></table></div></div>';
  }

  /* BRN agreements — Hotel + Catering (both booking kinds) */
  function brnCardHtml(type){
    var isCatering=(type==='Catering');
    var rows=(state.cache.brn||[]).filter(function(b){ return isCatering ? b.brnType==='Catering' : b.brnType!=='Catering'; }).map(function(b){
      var act;
      if(canEdit) act='<span class="link" onclick="editEntity(\'brn\',\''+b.brnId+'\',\''+g.groupId+'\')">'+t('edit')+'</span> · <span class="link" style="color:var(--red)" onclick="delItem(\'brn.delete\',{brnId:\''+b.brnId+'\'})">'+t('deleteLbl')+'</span>';
      else if(!isOp) act='<span class="link" onclick="editEntity(\'brn\',\''+b.brnId+'\',\''+g.groupId+'\')">✏️ '+t('requestChange')+'</span>';
      else act='–';
      return '<tr><td>'+esc(b.hotelName)+'</td><td dir="ltr">'+esc(b.checkIn)+'</td><td dir="ltr">'+esc(b.checkOut)+'</td>'+
        '<td dir="ltr">'+esc(b.brnNumber)+'</td><td>'+esc(b.roomsCount)+'</td><td style="white-space:nowrap">'+act+'</td></tr>';
    }).join('');
    var icon=isCatering?'🍽️ ':'📄 ', sec=isCatering?t('cateringSec'):t('brnSec'),
        addBtn=isCatering?opBtn(t('addCatering'),"openBrnModal('','Catering')"):opBtn(t('addBrn'),"openBrnModal('','Hotel')"),
        nameHd=isCatering?t('cateringLbl'):t('hotelLbl'), empty=isCatering?t('noCatering'):t('noBrn');
    return '<div class="card">'+secHd(icon+sec, canEdit?addBtn:'')+
      '<div class="tbl-wrap"><table><thead><tr><th>'+nameHd+'</th><th>'+t('checkIn')+'</th><th>'+t('checkOut')+'</th><th>'+t('brnNumber')+'</th><th>'+t('roomsCount')+'</th><th>'+t('actions')+'</th></tr></thead><tbody>'+
      (rows||'<tr class="no-data"><td colspan="6">'+empty+'</td></tr>')+'</tbody></table></div></div>';
  }
  html+=brnCardHtml('Hotel')+brnCardHtml('Catering');

  /* orders */
  var oRows=(state.cache.orders||[]).map(function(o){
    var act='<span class="link" onclick="showOrderDoc(\''+o.orderId+'\')">🖨️ '+t('printOrder')+'</span>';
    if(isOp) act+=' · <span class="link" onclick="editEntity(\'order\',\''+o.orderId+'\',\''+g.groupId+'\')">'+t('edit')+'</span>';
    else act+=' · <span class="link" onclick="editEntity(\'order\',\''+o.orderId+'\',\''+g.groupId+'\')">✏️ '+t('requestChange')+'</span>';
    return '<tr><td>'+esc(o.transportCompany)+'</td><td dir="ltr">'+esc(o.orderNumber)+'</td><td dir="ltr">'+esc(o.confirmationNo)+'</td><td dir="ltr">'+esc(o.brn)+'</td>'+
      '<td>'+stBadge(o.status)+'</td><td>'+act+'</td></tr>';
  }).join('');
  // transport confirmation state: only a "Booked" order means Transport Confirmed
  var ordersAll=(state.cache.orders||[]);
  var confirmed=ordersAll.some(function(o){ return o.status==='Booked'; });
  var tState = !ordersAll.length
    ? '<span class="badge b-gray">'+t('noOrderYet')+'</span>'
    : (confirmed?'<span class="badge b-green">'+t('transportConfirmed')+'</span>'
                :'<span class="badge b-amber">'+t('waitingConfirm')+'</span>');
  html+='<div class="card">'+secHd('📋 '+t('ordersSec'), isOp?opBtn(t('addOrderBtn'),'openOrderModal()'):'')+
    '<div style="margin-bottom:8px">'+tState+'</div>'+
    '<div class="tbl-wrap"><table><thead><tr><th>'+t('company')+'</th><th>'+t('orderNo')+'</th><th>'+t('confirmationNo')+'</th><th>'+t('brnLbl')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr></thead><tbody>'+
    (oRows||'<tr class="no-data"><td colspan="6">'+t('noOrders')+'</td></tr>')+'</tbody></table></div></div>';

  /* trips */
  var trRows=(state.cache.trips||[]).slice().sort(function(a,b){ return (a.date||'').localeCompare(b.date||''); }).map(function(tr){
    var act;
    var copyBtn='<span class="link" title="'+t('copyTrip')+'" onclick="copyTripMsg(\''+tr.tripId+'\')">📋</span>';
    if(canEdit){
      act=copyBtn+' · <span class="link" onclick="editEntity(\'trip\',\''+tr.tripId+'\',\''+g.groupId+'\')">'+t('edit')+'</span> · <span class="link" style="color:var(--red)" onclick="delItem(\'trips.delete\',{tripId:\''+tr.tripId+'\'})">'+t('deleteLbl')+'</span>';
    } else if(!isOp){
      act=copyBtn+' · <span class="link" onclick="editEntity(\'trip\',\''+tr.tripId+'\',\''+g.groupId+'\')">✏️ '+t('requestChange')+'</span>';
    } else { act=copyBtn; }
    return '<tr'+(tr.tripStatus==='Done'?' style="background:rgba(34,197,94,.07)"':'')+'><td dir="ltr">'+esc(tr.date)+'</td><td>'+esc(clientDayName(tr.date)||tr.day)+'</td><td>'+esc(tr.tripType)+'</td>'+
      '<td>'+esc(tr.from)+(tr.fromDetail?' <span style="color:var(--muted);font-size:.68rem">'+esc(tr.fromDetail)+'</span>':'')+' → '+esc(tr.to)+(tr.toDetail?' <span style="color:var(--muted);font-size:.68rem">'+esc(tr.toDetail)+'</span>':'')+'</td>'+
      '<td dir="ltr">'+esc(tr.time)+'</td><td>'+esc(tr.vehicleType)+'</td><td>'+esc(tr.buses)+'</td><td>'+esc(tr.pax)+'</td>'+
      '<td class="drv">'+(tr.driverName?'<b>'+esc(tr.driverName)+'</b>':'–')+
        (tr.driverMobile?'<br/><span dir="ltr" class="drv-mob">'+esc(tr.driverMobile)+'</span>':'')+
        (tr.plateNo?'<br/><span class="plate">'+esc(tr.plateNo)+'</span>':'')+'</td>'+
      '<td>'+stBadge(tr.bookingStatus)+'</td><td>'+stBadge(tr.tripStatus)+'</td><td style="white-space:nowrap">'+act+'</td></tr>';
  }).join('');
  html+='<div class="card">'+secHd('🚌 '+t('tripsSec'), canEdit?opBtn(t('addTrip'),'openTripModal()'):'')+
    '<div class="tbl-wrap"><table><thead><tr><th>'+t('tripDate')+'</th><th>'+t('dayLbl')+'</th><th>'+t('tripTypeLbl')+'</th><th>'+t('route')+'</th><th>'+t('timeLbl')+'</th><th>'+t('vehicleType')+'</th><th>'+t('vehicles')+'</th><th>'+t('pax')+'</th><th>'+t('driver')+'</th><th>'+t('bookingStatus')+'</th><th>'+t('tripStatus')+'</th><th>'+t('actions')+'</th></tr></thead><tbody>'+
    (trRows||'<tr class="no-data"><td colspan="12">'+t('noTrips')+'</td></tr>')+'</tbody></table></div></div>';

  /* updates log — filled asynchronously */
  html+='<div class="card">'+secHd('🕓 '+t('updatesLog'))+
    '<div id="groupLogBox"><div class="spinner" style="width:22px;height:22px;margin:10px auto"></div></div></div>';

  return html;
}

/* fetch + render the group activity log into #groupLogBox */
function loadGroupLog(groupId){
  apiCall('groups.log',{groupId:groupId}).then(function(res){
    var box=document.getElementById('groupLogBox'); if(!box) return;
    if(!res.ok || !res.rows.length){
      box.innerHTML='<div style="color:var(--muted);font-size:.8rem;text-align:center;padding:12px">'+t('noLogs')+'</div>';
      return;
    }
    box.innerHTML='<div class="tbl-wrap"><table class="mini-tbl"><thead><tr>'+
      '<th>'+t('logTime')+'</th><th>'+t('logUser')+'</th><th>'+t('logAction')+'</th><th>'+t('logDetails')+'</th></tr></thead><tbody>'+
      res.rows.map(function(l){
        return '<tr><td dir="ltr" style="white-space:nowrap">'+esc(l.timestamp)+'</td>'+
          '<td>'+esc(l.username)+'</td>'+
          '<td><span class="badge b-gray" style="font-size:.6rem">'+esc(l.action)+'</span></td>'+
          '<td>'+esc(l.details)+'</td></tr>';
      }).join('')+'</tbody></table></div>';
  });
}

function renderGroupDetail(c, g, isOp){
  var isInd = g.bookingType==='Individual';
  var html='<div class="page-title"><span><span class="link" onclick="go(\'groups\')">'+t('back')+'</span> &nbsp; 🕋 '+
    t('fileNo')+' '+esc(g.fileNo)+' — '+esc(g.groupName)+' '+stBadge(g.status)+
    ' <span class="badge '+(isInd?'b-purple':'b-blue')+'">'+t(isInd?'kindIndividual':'kindGroup')+'</span></span>'+
    '<span>'+((!isOp && (g.stage||'Visa')!=='Completed' && g.status!=='Cancelled')?'<button class="btn btn-ghost btn-sm" onclick="requestGroupEdit()">'+t('requestEdit')+'</button> ':'')+
    '<button class="btn btn-primary btn-sm" onclick="showVoucher()">🖨️ '+t('voucherLbl')+'</button> '+
    (isOp?'<button class="btn btn-ghost btn-sm" onclick="editEntity(\'group\',\''+g.groupId+'\',\''+g.groupId+'\')">'+t('edit')+'</button> '+
    '<button class="btn btn-ghost btn-sm" onclick="openStatusModal(\''+g.groupId+'\')">'+t('changeStatus')+'</button> '+
    (['Departed','Closed','Cancelled'].indexOf(g.status)!==-1
      ? '<button class="btn btn-ghost btn-sm" onclick="toggleArchive(\''+g.groupId+'\','+(g.archived==='yes'?'false':'true')+')">'+t(g.archived==='yes'?'unarchiveLbl':'archiveLbl')+'</button>'
      : '')
    :'')+'</span>'+
    '</div>';

  html+=groupSectionsHtml(g, isOp, false);
  c.innerHTML=html;
  loadGroupLog(g.groupId);
}
function delItem(action, payload){
  uiConfirm(t('confirmDelete'), function(){
    apiCall(action, payload).then(function(res){
      if(res.ok){ toast(t('saved'),'ok'); viewGroupDetail(document.getElementById('content')); }
    });
  });
}

/* ── hotel modal ── */
function openHotelModal(bookingId){
  Promise.all([loadMasterType('city'), loadMasterType('hotel')]).then(function(){ openHotelModal_(bookingId); });
}
function openHotelModal_(bookingId){
  var h=(state.cache.hotels||[]).find(function(x){return x.bookingId===bookingId;})||{};
  openModal(t('hotelModal'),
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('cityLbl')+' *</label><select id="hCity">'+masterOptions('city',h.city)+'</select></div>'+
    '<div class="field"><label>'+t('hotelLbl')+' *</label><select id="hName">'+masterOptions('hotel',h.hotelName)+'</select></div>'+
    field('hRooms',t('rooms'),h.rooms,'number')+
    '<div class="field"><label>'+t('status')+'</label><select id="hStatus">'+
      selectFieldOpts([['Tentative',t('stTentative')],['Confirmed',t('stConfirmed')],['Cancelled',t('stCancelled')]],h.status||'Confirmed')+'</select></div>'+
    '<div class="field"><label>'+t('checkIn')+' *</label><input id="hIn" type="date"'+dateMinAttr('hIn')+' value="'+escapeAttr(h.checkIn)+'" onchange="hModalDates()"/></div>'+
    '<div class="field"><label>'+t('checkOut')+' *</label><input id="hOut" type="date"'+(h.checkIn?' min="'+escapeAttr(h.checkIn)+'"':dateMinAttr('hOut'))+' value="'+escapeAttr(h.checkOut)+'"/></div>'+
    '</div>'+field('hNotes',t('notes'),h.notes),
    function(){
      var p={ bookingId:bookingId||'', groupId:state.currentGroupId, city:val('hCity'), hotelName:val('hName'),
        rooms:val('hRooms'), checkIn:val('hIn'), checkOut:val('hOut'), status:val('hStatus'), notes:val('hNotes') };
      if(!mValidate([
        {id:'hCity', ok:!!p.city,     msg:t('vHotelCity')},
        {id:'hName', ok:!!p.hotelName,msg:t('vHotelName')},
        {id:'hIn',   ok:!!p.checkIn,  msg:t('vCheckIn')},
        {id:'hOut',  ok:!!p.checkOut, msg:t('vCheckOut')},
        {id:'hOut',  ok:!(p.checkIn&&p.checkOut&&p.checkOut<=p.checkIn), msg:t('vCheckOutAfter')},
        pastDateErr('hIn',  p.checkIn,  t('checkIn')),
        pastDateErr('hOut', p.checkOut, t('checkOut'))
      ])) return;
      apiCall('hotels.save',p).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); viewGroupDetail(document.getElementById('content')); }
        else toast(t('errMissing'),'err');
      });
    });
}
/* ── brn modal (type = 'Hotel' | 'Catering') ── */
function openBrnModal(brnId, type){
  loadMasterType('hotel').then(function(){ openBrnModal_(brnId, type); });
}
function openBrnModal_(brnId, type){
  var b=(state.cache.brn||[]).find(function(x){return x.brnId===brnId;})||{};
  // editing keeps the row's own type; adding uses the passed type (default Hotel)
  var brnType = brnId ? (b.brnType==='Catering'?'Catering':'Hotel') : (type==='Catering'?'Catering':'Hotel');
  var isCatering = brnType==='Catering';
  var nameField = isCatering
    ? '<div class="field"><label>'+t('cateringLbl')+'</label><input id="bHotel" value="'+escapeAttr(b.hotelName||'')+'"/></div>'
    : '<div class="field"><label>'+t('hotelLbl')+'</label><select id="bHotel">'+masterOptions('hotel',b.hotelName)+'</select></div>';
  openModal(isCatering?t('cateringModal'):t('brnModal'),
    '<div class="form-grid">'+
    nameField+
    field('bNumber',t('brnNumber')+' *',b.brnNumber)+
    field('bIn',t('checkIn'),b.checkIn,'date')+
    field('bOut',t('checkOut'),b.checkOut,'date')+
    field('bRooms',t('roomsCount'),b.roomsCount,'number')+
    '</div>',
    function(){
      var p={ brnId:brnId||'', groupId:state.currentGroupId, brnType:brnType, hotelName:val('bHotel'),
        brnNumber:val('bNumber'), checkIn:val('bIn'), checkOut:val('bOut'), roomsCount:val('bRooms') };
      if(!mValidate([{id:'bNumber', ok:!!p.brnNumber, msg:t('vBrnNumber')}])) return;
      apiCall('brn.save',p).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); viewGroupDetail(document.getElementById('content')); }
        else toast(t('errMissing'),'err');
      });
    });
}
/* ── order modal ── */
function openOrderModal(orderId){
  // the company dropdown reads from the masters cache — make sure it's loaded first,
  // otherwise opening this from the follow-up popup renders an empty select
  loadMasterType('transportCompany').then(function(){ openOrderModal_(orderId); });
}
function openOrderModal_(orderId){
  var o=(state.cache.orders||[]).find(function(x){return x.orderId===orderId;})||{};
  var numField = orderId
    ? field('oNumber',t('orderNo'),o.orderNumber)
    : '<div class="field"><label>'+t('orderNo')+'</label><input id="oNumber" value="" placeholder="'+t('autoLbl')+'" readonly style="background:var(--bg2)"/></div>';
  openModal(t('orderModal'),
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('company')+' *</label><select id="oCompany">'+masterOptions('transportCompany',o.transportCompany)+'</select></div>'+
    numField+
    field('oConf',t('confirmationNo'),o.confirmationNo)+
    field('oBrn',t('brnLbl'),o.brn)+
    '<div class="field"><label>'+t('status')+'</label><select id="oStatus">'+
      selectFieldOpts([['Requested',t('stRequested')],['Booked',t('stBooked')]],o.status||'Requested')+'</select></div>'+
    '</div>',
    function(){
      var p={ orderId:orderId||'', groupId:state.currentGroupId, transportCompany:val('oCompany'),
        orderNumber:val('oNumber'), confirmationNo:val('oConf'), brn:val('oBrn'), status:val('oStatus') };
      if(!p.transportCompany){ toast(t('errMissing'),'err'); return; }
      apiCall('transport.saveOrder',p).then(function(res){
        if(res.ok){
          toast(t('saved'),'ok'); closeModal(); refreshBell();
          if(state.view==='followup'){ openGroupPopup(state.currentGroupId); viewFollowup(document.getElementById('content')); }
          else viewGroupDetail(document.getElementById('content'));
        }
        else toast(t('errMissing'),'err');
      });
    });
}
/* ── trip modal (shared: group detail + ops board) ── */
function tripModalVehChange(){
  var cap=vehicleCapacity(val('tVeh'));
  var pax=parseInt(val('tPax'))||0;
  if(cap>0 && pax>0){ var el=document.getElementById('tBuses'); if(el) el.value=Math.ceil(pax/cap); }
}
function openTripModal(tripId, fromOps){
  // masters must be loaded for the dropdowns (cached; instant after first call)
  Promise.all([loadMasterType('tripType'), loadMasterType('vehicleType')]).then(function(){
    openTripModal_(tripId, fromOps);
  });
}
function openTripModal_(tripId, fromOps){
  var list = fromOps ? (state.cache.opsRows||[]) : (state.cache.trips||[]);
  var tr = list.find(function(x){return x.tripId===tripId;})||{};
  var groupId = tr.groupId || state.currentGroupId;
  var orderOpts=[['','–']];
  (state.cache.orders||[]).forEach(function(o){
    if(o.groupId===groupId) orderOpts.push([o.orderId, o.transportCompany+' '+(o.orderNumber||'')]);
  });
  openModal(t('tripModal'),
    '<div class="form-grid">'+
    field('tDate',t('tripDate')+' *',tr.date,'date')+
    '<div class="field"><label>'+t('tripTypeLbl')+' *</label><input id="tType" list="tmMoveTypes" value="'+escapeAttr(tr.tripType)+'"/></div>'+
    '<div class="field"><label>'+t('modeLbl')+'</label><select id="tMode">'+modeOptions(tr.transportMode)+'</select></div>'+
    field('tTime',t('timeLbl'),tr.time,'time')+
    field('tFlight',t('flightNo'),tr.flightNo)+
    '<div class="field"><label>'+t('vehicleType')+'</label><select id="tVeh" onchange="tripModalVehChange()">'+vehicleOptions(tr.vehicleType)+'</select></div>'+
    field('tFrom',t('fromLbl'),tr.from)+
    field('tFromD',t('fromDetail'),tr.fromDetail)+
    field('tTo',t('toLbl'),tr.to)+
    field('tToD',t('toDetail'),tr.toDetail)+
    field('tBuses',t('vehicles'),tr.buses,'number')+
    field('tPax',t('pax'),tr.pax,'number')+
    field('tDriver',t('driverName'),tr.driverName)+
    field('tDriverMob',t('driverMobile'),tr.driverMobile)+
    field('tPlate',t('plateNo'),tr.plateNo)+
    '<div class="field"><label>'+t('bookingStatus')+'</label><select id="tBook">'+
      selectFieldOpts([['Pending',t('stPending')],['Booked',t('stBooked')]],tr.bookingStatus||'Pending')+'</select></div>'+
    '<div class="field"><label>'+t('tripStatus')+'</label><select id="tStatus">'+
      selectFieldOpts([['Pending',t('stPending')],['Done',t('stDone')],['Cancelled',t('stCancelledTrip')]],tr.tripStatus||'Pending')+'</select></div>'+
    (fromOps?'':'<div class="field"><label>'+t('linkedOrder')+'</label><select id="tOrder">'+selectFieldOpts(orderOpts,tr.orderId)+'</select></div>')+
    '</div>'+field('tNotes',t('notes'),tr.notes)+tripMoveDatalist('tmMoveTypes')+
    (/arrival|departure|قدوم|وصول|مغادرة/i.test(tr.tripType||'')
      ? '<div class="diff-box" style="margin-top:10px">'+t('linkedFlight')+'</div>' : ''),
    function(){
      var p={ tripId:tripId||'', groupId:groupId, orderId:fromOps?(tr.orderId||''):val('tOrder'),
        date:val('tDate'), tripType:val('tType'), transportMode:val('tMode'), vehicleType:val('tVeh'),
        time:val('tTime'), flightNo:val('tFlight'),
        from:val('tFrom'), fromDetail:val('tFromD'), to:val('tTo'), toDetail:val('tToD'),
        buses:val('tBuses'), pax:val('tPax'), driverName:val('tDriver'), driverMobile:val('tDriverMob'), plateNo:val('tPlate'),
        bookingStatus:val('tBook'), tripStatus:val('tStatus'), notes:val('tNotes') };
      if(!mValidate([
        {id:'tDate',  ok:!!p.date,                  msg:t('vTripDate')},
        {id:'tType',  ok:!!p.tripType,              msg:t('vTripType')},
        {id:'tVeh',   ok:!!p.vehicleType,           msg:t('vVehicleType')},
        {id:'tBuses', ok:parseInt(p.buses)>0,       msg:t('vVehicleQty')},
        pastDateErr('tDate', p.date, t('tripDate'))
      ])) return;
      apiCall('trips.save',p).then(function(res){
        if(res.ok){
          state.cache.groups=null;   // trip may have back-synced the group's flight details
          toast(t('saved'),'ok'); closeModal();
          if(fromOps) viewOps(document.getElementById('content'));
          else viewGroupDetail(document.getElementById('content'));
        } else toast(t('errMissing'),'err');
      });
    });
}

/* ════════════════ PHASE 3: OPERATIONS BOARD ════════════════ */
var opsFilter='today';
function viewOps(c){
  c.innerHTML='<div class="page-title">🚌 '+t('opsBoard')+'</div><div class="spinner"></div>';
  Promise.all([apiCall('ops.board'), loadMasterType('tripType'), loadMasterType('vehicleType'), loadCompany()]).then(function(rs){
    var res=rs[0];
    if(!res.ok) return;
    state.cache.opsRows=res.rows; state.cache.opsToday=res.todayUTC;
    renderOps(c);
  });
}
/* company name + logo for document headers (cached) */
function loadCompany(){
  if(state.cache.docCompany) return Promise.resolve(state.cache.docCompany);
  return apiCall('company.get').then(function(res){
    state.cache.docCompany = res.ok ? res.company : {en:'',ar:'',logo:''};
    return state.cache.docCompany;
  });
}
function opsDiff(dateStr){
  var m=(dateStr||'').match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
  if(!m) return null;
  return Math.round((Date.UTC(+m[1],+m[2]-1,+m[3]) - state.cache.opsToday)/86400000);
}
/* ════════════════ REPORTS ════════════════ */
var RP_MODULES=[['groups','rpGroups','🕋'],['trips','rpTrips','🚌'],['orders','rpOrders','📋'],
                ['hotels','rpHotels','🏨'],['brn','rpBrn','📄'],['requests','rpRequests','📨']];
var rpState={ module:'groups', search:'', agentCode:'', status:'', stage:'', dateFrom:'', dateTo:'' };

function viewReports(c){
  var isOp=state.user.role==='operator';
  c.innerHTML='<div class="page-title">📊 '+t('reportsNav')+'</div><div class="spinner"></div>';
  apiCall('reports.meta').then(function(meta){
    if(!meta.ok) return;
    state.cache.rpMeta=meta;

    var tabs=RP_MODULES.map(function(m){
      return '<button class="btn btn-sm '+(rpState.module===m[0]?'btn-primary':'btn-ghost')+'" '+
        'onclick="rpSetModule(\''+m[0]+'\')">'+m[2]+' '+t(m[1])+'</button>';
    }).join(' ');

    var agentSel = isOp
      ? '<div class="field"><label>'+t('agentFilter')+'</label><select id="rpAgent">'+
        selectFieldOpts([['',t('allLbl')]].concat(meta.agents.map(function(a){return [a.agentCode,a.agentName];})), rpState.agentCode)+
        '</select></div>'
      : '';

    c.innerHTML='<div class="page-title">📊 '+t('reportsNav')+
      '<span><button class="btn btn-ghost btn-sm" onclick="rpExportPdf()">'+t('rpExportPdf')+'</button> '+
      '<button class="btn btn-ghost btn-sm" onclick="rpExportExcel()">'+t('rpExportXls')+'</button></span></div>'+
      '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">'+tabs+'</div>'+
      '<div class="card"><div class="card-hd"><div class="card-title">⚙️ '+t('rpFilters')+'</div>'+
        '<span class="link" style="font-size:.72rem" onclick="rpReset()">'+t('rpReset')+'</span></div>'+
        '<div class="field"><label>'+t('rpSearch')+'</label>'+
          '<textarea id="rpSearch" rows="2" placeholder="'+t('rpSearchHint')+'">'+esc2(rpState.search)+'</textarea>'+
          '<div style="font-size:.64rem;color:var(--muted);margin-top:3px">'+t('rpSearchHint')+'</div></div>'+
        '<div class="form-grid" style="margin-top:10px">'+
          agentSel+
          '<div class="field"><label>'+t('statusFilter')+'</label><select id="rpStatus">'+
            selectFieldOpts([['',t('allLbl')]].concat(rpStatusOpts()), rpState.status)+'</select></div>'+
          (rpState.module==='groups'?'<div class="field"><label>'+t('rpStage')+'</label><select id="rpStage">'+
            selectFieldOpts([['',t('allLbl')]].concat(meta.stages.map(function(s){return [s,stageLabel(s)];})), rpState.stage)+'</select></div>':'')+
          field('rpFrom',t('rpDateFrom'),rpState.dateFrom,'date')+
          field('rpTo',t('rpDateTo'),rpState.dateTo,'date')+
        '</div>'+
        '<div style="margin-top:12px"><button class="btn btn-primary btn-sm" onclick="rpRun()">'+t('rpRun')+'</button></div>'+
      '</div>'+
      '<div id="rpResults"></div>';
    rpRun();
  });
}
function esc2(v){ return (v===undefined||v===null)?'':v.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;'); }
function rpStatusOpts(){
  var m=rpState.module;
  if(m==='trips')    return [['Pending',t('stPending')],['Done',t('stDone')]];
  if(m==='orders')   return [['Requested',t('stRequested')],['Booked',t('stBooked')]];
  if(m==='requests') return [['Pending',t('stPending')],['Approved',t('stApproved')],['Returned',t('stReturned')],['Rejected',t('stRejected')],['Cancelled',t('stCancelled')]];
  return GROUP_STATUSES.map(function(s){ return [s,t('st'+s)]; });
}
function rpSetModule(m){
  rpState.module=m; rpState.status=''; rpState.stage='';
  pagerReset('rp');
  viewReports(document.getElementById('content'));
}
function rpReset(){
  rpState={ module:rpState.module, search:'', agentCode:'', status:'', stage:'', dateFrom:'', dateTo:'' };
  pagerReset('rp');
  viewReports(document.getElementById('content'));
}
function rpCapture(){
  rpState.search   = (document.getElementById('rpSearch')||{}).value||'';
  rpState.agentCode= (document.getElementById('rpAgent')||{}).value||'';
  rpState.status   = (document.getElementById('rpStatus')||{}).value||'';
  rpState.stage    = (document.getElementById('rpStage')||{}).value||'';
  rpState.dateFrom = (document.getElementById('rpFrom')||{}).value||'';
  rpState.dateTo   = (document.getElementById('rpTo')||{}).value||'';
}
function rpRun(){
  rpCapture(); pagerReset('rp');
  var box=document.getElementById('rpResults');
  box.innerHTML='<div class="card"><div class="spinner"></div></div>';
  apiCall('reports.run', rpState).then(function(res){
    if(!res.ok){ box.innerHTML='<div class="card">'+t('errServer')+'</div>'; return; }
    state.cache.rp=res;
    rpRenderTable();
  });
}
function rpRenderTable(){
  var res=state.cache.rp; if(!res) return;
  var lang=state.lang;
  var box=document.getElementById('rpResults');
  box.innerHTML='<div class="card">'+
    '<div class="card-hd"><div class="card-title">'+t(('rp'+res.module.charAt(0).toUpperCase()+res.module.slice(1)))+
      ' <span style="color:var(--muted);font-weight:400;font-size:.78rem">— '+res.total+' '+t('rpRows')+'</span></div></div>'+
    '<div class="tbl-wrap"><table><thead><tr>'+
      res.columns.map(function(c){ return '<th>'+esc(c[lang]||c.en)+'</th>'; }).join('')+
    '</tr></thead><tbody id="rpBody"></tbody></table></div><div id="rpPager"></div></div>';
  rpRenderRows();
}
function rpRenderRows(){
  var res=state.cache.rp; if(!res) return;
  var ps=pageSlice('rp', res.rows);
  var pg=document.getElementById('rpPager'); if(pg) pg.innerHTML=ps.pagerHtml;
  var html=ps.rows.map(function(r){
    return '<tr'+(r.groupId?' style="cursor:pointer" onclick="openGroupPopup(\''+r.groupId+'\')"':'')+'>'+
      res.columns.map(function(c){
        var v=r[c.key];
        if(/status/i.test(c.key) && v) return '<td>'+stBadge(v)+'</td>';
        return '<td>'+esc(v)+'</td>';
      }).join('')+'</tr>';
  }).join('');
  document.getElementById('rpBody').innerHTML=html||
    '<tr class="no-data"><td colspan="'+res.columns.length+'">'+t('rpNoRows')+'</td></tr>';
}
/* export the CURRENT result set (all rows, not just the visible page) */
function rpExportPdf(){
  var res=state.cache.rp;
  if(!res || !res.rows.length){ toast(t('rpNoRows'),'err'); return; }
  loadCompany().then(function(company){
    var lang=state.lang;
    var title=t('rp'+res.module.charAt(0).toUpperCase()+res.module.slice(1));
    var h=docHeader(company, title+' Report', 'تقرير '+title, false, '');

    /* what the reader needs to know about how this was filtered */
    var f=[];
    if(rpState.search)    f.push([ 'Search','بحث', rpState.search.replace(/[\n;]+/g,', ') ]);
    if(rpState.agentCode) f.push([ 'Agent','الوكيل', rpAgentName(rpState.agentCode) ]);
    if(rpState.status)    f.push([ 'Status','الحالة', rpState.status ]);
    if(rpState.stage)     f.push([ 'Stage','المرحلة', rpState.stage ]);
    if(rpState.dateFrom||rpState.dateTo) f.push([ 'Date Range','الفترة', (rpState.dateFrom||'…')+' → '+(rpState.dateTo||'…') ]);
    h+='<table class="dt"><tr>'+k2('Records','عدد السجلات')+'<td class="num">'+res.total+'</td>'+
      f.map(function(x){ return k2(x[0],x[1])+'<td>'+dv(x[2])+'</td>'; }).join('')+'</tr></table>';

    h+=sec2(title,'')+
      '<table class="dt"><tr>'+res.columns.map(function(c){ return th2(c.en,c.ar); }).join('')+'</tr>';
    res.rows.forEach(function(r){
      h+='<tr>'+res.columns.map(function(c){ return '<td>'+dv(r[c.key]||'—')+'</td>'; }).join('')+'</tr>';
    });
    h+='</table>';
    h+='<div class="ft"><b>'+dv(company.en)+'</b> — '+dv(title)+' Report — '+res.total+' records<br/>'+
      '<span dir="ltr">'+new Date().toLocaleString()+'</span></div>';
    openPrintPreview(docShell(title+' Report', h, false), 'report-'+res.module);
  });
}
function rpAgentName(code){
  var m=(state.cache.rpMeta||{}).agents||[];
  var a=m.find(function(x){return x.agentCode===code;});
  return a?a.agentName:code;
}
function rpExportExcel(){
  var res=state.cache.rp;
  if(!res || !res.rows.length){ toast(t('rpNoRows'),'err'); return; }
  toast(t('generating'));
  apiCall('reports.excel', Object.assign({}, rpState, { lang: state.lang })).then(function(r){
    if(!r.ok){ toast(t('errServer')+(r.message?': '+r.message:''),'err'); return; }
    var bytes=atob(r.base64), arr=new Uint8Array(bytes.length);
    for(var i=0;i<bytes.length;i++) arr[i]=bytes.charCodeAt(i);
    var blob=new Blob([arr],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
    var url=URL.createObjectURL(blob), a=document.createElement('a');
    a.href=url; a.download=r.filename; a.click();
    URL.revokeObjectURL(url);
    toast(t('saved'),'ok');
  });
}

/* ════════════════ TRANSPORTATION PAGE ════════════════ */
var tpTab='orders';
function viewTransport(c){
  c.innerHTML='<div class="spinner"></div>';
  var calls=[apiCall('transport.board'), loadMasterType('transportCompany'), loadMasterType('vehicleType')];
  Promise.all(calls).then(function(rs){
    var res=rs[0];
    if(!res.ok){ c.innerHTML='<div class="card">–</div>'; return; }
    state.cache.tp=res;
    renderTransport(c);
  });
}
function renderTransport(c){
  var isOp=state.user.role==='operator';
  var d=state.cache.tp, k=d.kpis;
  var kpi=function(val,label,color){
    return '<div class="kpi"><div class="kpi-accent" style="background:var(--'+color+')"></div>'+
      '<div class="kpi-val" style="color:var(--'+color+')">'+val+'</div>'+
      '<div class="kpi-lbl">'+label+'</div></div>';
  };
  var tabs=[['orders','📋 '+t('tpOrders')+' ('+d.orders.length+')'],
            ['awaiting','⏳ '+t('tpAwaiting')+' ('+d.awaiting.length+')'],
            ['pending','📨 '+t('tpPending')+' ('+d.pending.length+')']]
    .map(function(x){
      return '<button class="btn btn-sm '+(tpTab===x[0]?'btn-primary':'btn-ghost')+'" onclick="pagerReset(&quot;tp&quot;);tpTab=\''+x[0]+'\';renderTransport(document.getElementById(\'content\'))">'+x[1]+'</button>';
    }).join(' ');

  c.innerHTML='<div class="page-title">🚐 '+t('transportNav')+'</div>'+
    '<div class="kpi-grid">'+
      kpi(k.ordersTotal,'📋 '+t('kpiOrders'),'blue')+
      kpi(k.ordersConfirmed,'✅ '+t('kpiConfirmed'),'green')+
      kpi(k.ordersRequested,'⏳ '+t('kpiRequested'),'amber')+
      kpi(k.awaitingOrder,'🕋 '+t('kpiAwaitOrder'),'red')+
      kpi(k.tripsDone+' / '+k.tripsTotal,'🚌 '+t('kpiTrips'),'purple')+
      kpi(k.tripsNoDriver,'👤 '+t('kpiNoDriver'),'amber')+
      kpi(k.vehicles,'🚍 '+t('kpiVehicles'),'blue')+
      kpi(k.pendingReqs,'📨 '+t('kpiPendingReq'),'red')+
    '</div>'+
    '<div style="display:flex;gap:6px;flex-wrap:wrap;margin:14px 0 10px;align-items:center">'+tabs+
    '<div class="tbl-search" style="margin-inline-start:auto"><input id="tpSearch" placeholder="'+t('search')+'" oninput="pagerReset(&quot;tp&quot;);renderTpRows()"/></div></div>'+
    '<div class="card"><div class="tbl-wrap"><table id="tpTable"><thead id="tpHead"></thead><tbody id="tpBody"></tbody></table></div><div id="tpPager"></div></div>';
  renderTpRows();
}
function renderTpRows(){
  var isOp=state.user.role==='operator';
  var d=state.cache.tp;
  var q=((document.getElementById('tpSearch')||{}).value||'').toLowerCase();
  var head='', rows=[], cols=1, empty='';

  if(tpTab==='orders'){
    cols=isOp?9:8;
    head='<tr><th>'+t('company')+'</th><th>'+t('orderNo')+'</th><th>'+t('confirmationNo')+'</th><th>'+t('fileNo')+'</th>'+
      (isOp?'<th>'+t('agentCol')+'</th>':'')+
      '<th>'+t('brnLbl')+'</th><th>'+t('tripsCount')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr>';
    rows=d.orders.filter(function(o){
      return !q || (o.transportCompany+' '+o.orderNumber+' '+o.confirmationNo+' '+o.fileNo+' '+o.groupName+' '+o.agentName).toLowerCase().indexOf(q)!==-1;
    });
    empty=t('noOrdersYet');
  } else if(tpTab==='awaiting'){
    cols=isOp?7:6;
    head='<tr><th>'+t('fileNo')+'</th><th>'+t('groupName')+'</th>'+
      (isOp?'<th>'+t('agentCol')+'</th>':'')+
      '<th>'+t('arrivalDate')+'</th><th>'+t('pax')+'</th><th>'+t('status')+'</th><th>'+t('actions')+'</th></tr>';
    rows=d.awaiting.filter(function(g){
      return !q || (g.fileNo+' '+g.groupName+' '+g.agentName).toLowerCase().indexOf(q)!==-1;
    });
    empty=t('noAwaiting');
  } else {
    cols=isOp?7:6;
    head='<tr><th>'+t('reqNo')+'</th><th>'+t('requestType')+'</th><th>'+t('fileNo')+'</th>'+
      (isOp?'<th>'+t('agentCol')+'</th>':'')+
      '<th>'+t('changeNote')+'</th><th>'+t('submittedAt')+'</th><th>'+t('actions')+'</th></tr>';
    rows=d.pending.filter(function(r){
      return !q || (r.requestId+' '+r.fileNo+' '+r.groupName+' '+r.agentName+' '+r.note).toLowerCase().indexOf(q)!==-1;
    });
    empty=t('noPendingReqs');
  }

  var ps=pageSlice('tp', rows);
  var pg=document.getElementById('tpPager'); if(pg) pg.innerHTML=ps.pagerHtml;
  document.getElementById('tpHead').innerHTML=head;

  var html='';
  if(tpTab==='orders'){
    html=ps.rows.map(function(o){
      var act='<span class="link" onclick="tpPrintOrder(\''+o.groupId+'\',\''+o.orderId+'\')">🖨️</span>'+
        (isOp?' · <span class="link" onclick="tpEditOrder(\''+o.groupId+'\',\''+o.orderId+'\')">'+t('edit')+'</span>'
             :' · <span class="link" onclick="tpRequestOrderChange(\''+o.groupId+'\',\''+o.orderId+'\')">✏️</span>');
      return '<tr><td>'+esc(o.transportCompany)+'</td><td dir="ltr"><b>'+esc(o.orderNumber)+'</b></td>'+
        '<td dir="ltr">'+esc(o.confirmationNo)+'</td>'+
        '<td><span class="link" onclick="openGroupPopup(\''+o.groupId+'\')">'+esc(o.fileNo)+'</span> <span style="color:var(--muted);font-size:.68rem">'+esc(o.groupName)+'</span></td>'+
        (isOp?'<td>'+esc(o.agentName)+'</td>':'')+
        '<td dir="ltr">'+esc(o.brn)+'</td><td>'+esc(o.tripsCount)+'</td>'+
        '<td>'+stBadge(o.status)+'</td><td style="white-space:nowrap">'+act+'</td></tr>';
    }).join('');
  } else if(tpTab==='awaiting'){
    html=ps.rows.map(function(g){
      var badge = g.state==='Requested'
        ? '<span class="badge b-amber">'+t('waitingConfirm')+'</span>'
        : '<span class="badge b-gray">'+t('noOrderYet')+'</span>';
      var act=isOp?'<span class="link" onclick="tpEditOrder(\''+g.groupId+'\',\'\')">'+t('createOrder')+'</span>'
                  :'<span class="link" onclick="openGroupPopup(\''+g.groupId+'\')">'+t('view')+'</span>';
      return '<tr><td><span class="link" onclick="openGroupPopup(\''+g.groupId+'\')">'+esc(g.fileNo)+'</span></td>'+
        '<td>'+esc(g.groupName)+'</td>'+
        (isOp?'<td>'+esc(g.agentName)+'</td>':'')+
        '<td dir="ltr">'+esc(g.arrivalDate)+'</td><td>'+esc(g.totalPax)+'</td>'+
        '<td>'+badge+'</td><td>'+act+'</td></tr>';
    }).join('');
  } else {
    html=ps.rows.map(function(r){
      var typeB='<span class="badge b-amber">'+t(r.type==='EditTrip'?'reqEditTrip':'reqEditOrder')+'</span>';
      var act=isOp?'<span class="link" onclick="tpReviewRequest(\''+r.requestId+'\')">'+t('reviewModal')+'</span>'
                  :'<span style="color:var(--muted);font-size:.7rem">'+t('pendingApproval')+'</span>';
      return '<tr><td dir="ltr" style="font-weight:700;font-size:.72rem">'+esc(r.requestId)+'</td>'+
        '<td>'+typeB+'</td>'+
        '<td><span class="link" onclick="openGroupPopup(\''+r.groupId+'\')">'+esc(r.fileNo)+'</span> <span style="color:var(--muted);font-size:.68rem">'+esc(r.groupName)+'</span></td>'+
        (isOp?'<td>'+esc(r.agentName)+'</td>':'')+
        '<td>'+esc(r.note)+'</td><td dir="ltr">'+esc(r.submittedAt)+'</td><td>'+act+'</td></tr>';
    }).join('');
  }
  document.getElementById('tpBody').innerHTML=html||'<tr class="no-data"><td colspan="'+cols+'">'+empty+'</td></tr>';
}
/* operator: create/edit an order straight from the transport page */
function tpEditOrder(groupId, orderId){
  state.currentGroupId=groupId;
  // orders cache must hold this group's orders for the modal to prefill
  apiCall('transport.list',{groupId:groupId}).then(function(res){
    if(!res.ok) return;
    state.cache.orders=res.orders||[]; state.cache.trips=res.trips||[];
    openOrderModal(orderId||undefined);
  });
}
function tpPrintOrder(groupId, orderId){
  state.currentGroupId=groupId;
  apiCall('transport.list',{groupId:groupId}).then(function(res){
    if(!res.ok) return;
    state.cache.orders=res.orders||[];
    showOrderDoc(orderId);
  });
}
/* operator reviews a trip/order change request */
function tpReviewRequest(requestId){
  var r=(state.cache.tp.pending||[]).find(function(x){return x.requestId===requestId;});
  if(!r) return;
  apiCall('requests.list').then(function(res){
    if(!res.ok) return;
    var full=(res.rows||[]).find(function(x){return x.requestId===requestId;});
    if(!full) return;
    var p={}; try{ p=JSON.parse(full.payloadJSON||'{}'); }catch(e){}
    var before=p.before||{}, fields=p.fields||{};
    var labels=tpFieldLabels();
    var diff=Object.keys(fields).filter(function(kk){
      return (before[kk]||'').toString().trim()!==(fields[kk]||'').toString().trim();
    }).map(function(kk){
      return '<tr><td><b>'+(labels[kk]||kk)+'</b></td>'+
        '<td style="color:var(--red);text-decoration:line-through;opacity:.75">'+esc(before[kk])+'</td>'+
        '<td style="color:var(--green);font-weight:700">'+esc(fields[kk])+'</td></tr>';
    }).join('');
    var body='<div style="margin-bottom:10px"><span class="badge b-amber">'+t(r.type==='EditTrip'?'reqEditTrip':'reqEditOrder')+'</span> '+
      '<span style="font-size:.78rem"><b>'+t('fileNo')+':</b> '+esc(r.fileNo)+' — '+esc(r.groupName)+' · <b>'+t('agentCol')+':</b> '+esc(r.agentName)+'</span></div>'+
      (p.note?'<div class="diff-box" style="margin-bottom:10px"><b>'+t('changeNote')+':</b> '+esc(p.note)+'</div>':'')+
      '<div class="diff-box"><div style="font-weight:800;margin-bottom:8px">🔍 '+t('changesSummary')+'</div>'+
      (diff?'<div class="tbl-wrap"><table class="mini-tbl"><thead><tr><th>'+t('fieldLbl')+'</th><th>'+t('oldValue')+'</th><th>'+t('newValue')+'</th></tr></thead><tbody>'+diff+'</tbody></table></div>'
           :'<div style="color:var(--muted)">'+t('noChanges')+'</div>')+'</div>'+
      '<div class="field" style="margin-top:10px"><label>'+t('reviewNote')+'</label><textarea id="mRvNote" rows="2"></textarea></div>';
    openModal(t('reviewChange'), body, null);
    modalWide(true);
    document.getElementById('modalFooter').innerHTML=
      '<button class="btn btn-danger" onclick="tpDecide(\''+requestId+'\',\'reject\')">✕ '+t('reject')+'</button>'+
      '<button class="btn btn-ghost" onclick="tpDecide(\''+requestId+'\',\'return\')">↩ '+t('returnToAgent')+'</button>'+
      '<button class="btn btn-primary" onclick="tpDecide(\''+requestId+'\',\'approve\')">✓ '+t('approve')+'</button>';
  });
}
function tpDecide(requestId, decision){
  apiCall('requests.review',{requestId:requestId,decision:decision,note:val('mRvNote')}).then(function(res){
    if(res.ok){
      toast(decision==='approve'?t('approvedMsg'):t('rejectedMsg'), decision==='approve'?'ok':'');
      closeModal(); refreshBell(); viewTransport(document.getElementById('content'));
    } else toast(t('errServer')+(res.message?': '+res.message:''),'err');
  });
}
function tpFieldLabels(){
  return { date:t('tripDate'), tripType:t('tripTypeLbl'), time:t('timeLbl'), from:t('fromLbl'), to:t('toLbl'),
    fromDetail:t('fromDetail'), toDetail:t('toDetail'), vehicleType:t('vehicleType'), buses:t('vehicles'),
    pax:t('pax'), driverName:t('driverName'), driverMobile:t('driverMobile'), flightNo:t('flightNo'),
    tripStatus:t('tripStatus'), bookingStatus:t('bookingStatus'), notes:t('notes'),
    transportCompany:t('company'), orderNumber:t('orderNo'), confirmationNo:t('confirmationNo'),
    brn:t('brnLbl'), status:t('status') };
}
/* agent: request a change to an order */
function tpRequestOrderChange(groupId, orderId){
  loadMasterType('transportCompany').then(function(){ tpRequestOrderChange_(groupId, orderId); });
}
function tpRequestOrderChange_(groupId, orderId){
  var o=(state.cache.tp.orders||[]).find(function(x){return x.orderId===orderId;})||{};
  openModal(t('requestChange')+' — '+t('reqEditOrder'),
    '<div class="form-grid">'+
    '<div class="field"><label>'+t('company')+'</label><select id="rqoCompany">'+masterOptions('transportCompany',o.transportCompany)+'</select></div>'+
    field('rqoConf',t('confirmationNo'),o.confirmationNo)+
    field('rqoBrn',t('brnLbl'),o.brn)+
    '</div>'+
    '<div class="field"><label>'+t('changeNote')+' *</label><textarea id="rqoNote" rows="2"></textarea></div>',
    function(){
      if(!val('rqoNote')){ toast(t('errMissing'),'err'); return; }
      apiCall('transport.request',{
        type:'EditOrder', groupId:groupId, orderId:orderId, note:val('rqoNote'),
        fields:{ transportCompany:val('rqoCompany'), confirmationNo:val('rqoConf'), brn:val('rqoBrn') }
      }).then(function(res){
        if(res.ok){ toast(t('reqSent'),'ok'); closeModal(); viewTransport(document.getElementById('content')); }
        else toast(t('errServer'),'err');
      });
    });
}
/* agent: request a change to a trip (used from group popup / ops) */
function requestTripChange(groupId, tripId){
  var tr=(state.cache.trips||[]).find(function(x){return x.tripId===tripId;})||{};
  Promise.all([loadMasterType('tripType'), loadMasterType('vehicleType')]).then(function(){
    openModal(t('requestChange')+' — '+t('reqEditTrip'),
      '<div class="form-grid">'+
      field('rqtDate',t('tripDate'),tr.date,'date')+
      '<div class="field"><label>'+t('tripTypeLbl')+'</label><input id="rqtType" list="rqtMoves" value="'+escapeAttr(tr.tripType)+'"/></div>'+
      field('rqtTime',t('timeLbl'),tr.time,'time')+
      field('rqtFrom',t('fromLbl'),tr.from)+
      field('rqtTo',t('toLbl'),tr.to)+
      '<div class="field"><label>'+t('vehicleType')+'</label><select id="rqtVeh">'+vehicleOptions(tr.vehicleType)+'</select></div>'+
      field('rqtBuses',t('vehicles'),tr.buses,'number')+
      field('rqtFlight',t('flightNo'),tr.flightNo)+
      '</div>'+tripMoveDatalist('rqtMoves')+
      '<div class="field"><label>'+t('changeNote')+' *</label><textarea id="rqtNote" rows="2"></textarea></div>',
      function(){
        if(!val('rqtNote')){ toast(t('errMissing'),'err'); return; }
        apiCall('transport.request',{
          type:'EditTrip', groupId:groupId, tripId:tripId, note:val('rqtNote'),
          fields:{ date:val('rqtDate'), tripType:val('rqtType'), time:val('rqtTime'),
                   from:val('rqtFrom'), to:val('rqtTo'), vehicleType:val('rqtVeh'),
                   buses:val('rqtBuses'), flightNo:val('rqtFlight') }
        }).then(function(res){
          if(res.ok){ toast(t('reqSent'),'ok'); closeModal(); }
          else toast(t('errServer'),'err');
        });
      });
  });
}

/* ════════════════ OPERATIONS BOARD ════════════════ */
function renderOps(c){
  var isOp = state.user.role==='operator';
  var filters=[['today',t('today')],['tomorrow',t('tomorrow')],['next7',t('next7')],['missed',t('opsMissed')],['all',t('all')]];
  var tabs=filters.map(function(f){
    return '<button class="btn btn-sm '+(opsFilter===f[0]?'btn-primary':'btn-ghost')+'" onclick="opsFilter=\''+f[0]+'\';renderOps(document.getElementById(\'content\'))">'+f[1]+'</button>';
  }).join(' ');
  var agNames={}; (state.cache.opsRows||[]).forEach(function(r){ if(r.agentName) agNames[r.agentName]=1; });
  var opsAgSel = isOp
    ? '<select id="opsAgentF" onchange="pagerReset(&quot;ops&quot;);renderOpsRows()" style="padding:7px 10px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.78rem">'+
      selectFieldOpts([['',t('agentFilter')+': '+t('allLbl')]].concat(Object.keys(agNames).map(function(n){return [n,n];})), '')+'</select>'
    : '';
  c.innerHTML='<div class="page-title">🚌 '+t('opsBoard')+
    '<button class="btn btn-ghost btn-sm" onclick="exportOpsPdf()">'+t('exportPdf')+'</button></div>'+
    '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;align-items:center">'+
    '<div class="tbl-search"><input id="opsSearch" placeholder="'+t('search')+'" oninput="pagerReset(&quot;ops&quot;);renderOpsRows()"/></div>'+
    tabs+opsAgSel+'</div>'+
    '<div id="opsSummary" class="ops-summary"></div>'+
    '<div class="card"><div class="tbl-wrap"><table><thead><tr>'+
    '<th>'+t('timeLbl')+'</th><th>'+t('tripTypeLbl')+'</th><th>'+t('fileNo')+'</th>'+
    (isOp?'<th>'+t('agentCol')+'</th>':'')+
    '<th>'+t('route')+'</th><th>'+t('vehicleType')+'</th><th>'+t('vehicles')+'</th><th>'+t('pax')+'</th>'+
    '<th>'+t('company')+'</th><th>'+t('orderNo')+'</th>'+
    '<th>'+t('driver')+'</th><th>'+t('plateNo')+'</th><th>'+t('bookingStatus')+'</th><th>'+t('tripStatus')+'</th><th></th>'+
    (isOp?'<th>'+t('actions')+'</th>':'')+
    '</tr></thead><tbody id="opsBody"></tbody></table></div><div id="opsPager"></div></div>';
  renderOpsRows();
}
function opsFilteredRows(){
  var q=((document.getElementById('opsSearch')||{}).value||'').toLowerCase();
  return (state.cache.opsRows||[]).filter(function(r){
    var d=opsDiff(r.date);
    if(opsFilter==='today' && d!==0) return false;
    if(opsFilter==='tomorrow' && d!==1) return false;
    if(opsFilter==='next7' && (d===null||d<0||d>7)) return false;
    if(opsFilter==='missed' && !(d!==null && d<0 && r.tripStatus==='Pending')) return false;
    var agF=(document.getElementById('opsAgentF')||{}).value||'';
    if(agF && r.agentName!==agF) return false;
    if(q && (r.fileNo+r.groupName+r.agentName+r.tripType+r.from+r.to+r.driverName+(r.flightNo||'')+(r.vehicleType||'')).toLowerCase().indexOf(q)===-1) return false;
    return true;
  }).sort(function(a,b){ return (a.date||'').localeCompare(b.date||'')||(a.time||'').localeCompare(b.time||''); });
}
/* row actions depend on where the trip is in its life: pending / done / cancelled */
function opsRowActions(r){
  if(r.tripStatus==='Cancelled'){
    return '<button class="fu-btn" onclick="opsSetStatus(\''+r.tripId+'\',\'Pending\')">'+t('tripRestore')+'</button>'+
      ' <span class="link" onclick="openTripModal(\''+r.tripId+'\',true)">'+t('edit')+'</span>';
  }
  var future=isFutureDate(r.date);
  var doneBtn = r.tripStatus==='Done'
    ? '<button class="fu-btn" onclick="opsSetStatus(\''+r.tripId+'\',\'Pending\')">'+t('undoDone')+'</button>'
    : (future
        ? '<button class="fu-btn" style="opacity:.45" title="'+t('vTripFuture')+'" onclick="toast(t(\'vTripFuture\'),\'err\')">'+t('markDone')+'</button>'
        : '<button class="fu-btn primary" onclick="opsSetStatus(\''+r.tripId+'\',\'Done\')">'+t('markDone')+'</button>');
  return doneBtn+
    ' <span class="link" style="color:var(--red)" title="'+t('tripCancel')+'" onclick="opsCancelTrip(\''+r.tripId+'\')">✕</span>'+
    ' <span class="link" onclick="openTripModal(\''+r.tripId+'\',true)">'+t('edit')+'</span>';
}
function isFutureDate(v){
  if(!v) return false;
  return v > todayStrLocal();
}
function opsSetStatus(tripId, next){
  var r=(state.cache.opsRows||[]).find(function(x){return x.tripId===tripId;});
  if(!r) return;
  if(next==='Done' && isFutureDate(r.date)){ toast(t('vTripFuture'),'err'); return; }
  apiCall('trips.save', { tripId:String(tripId), groupId:String(r.groupId||''), tripStatus:next }).then(function(res){
    if(!res.ok){
      toast(res.error==='TRIP_FUTURE' ? t('vTripFuture')
            : t('errServer')+(res.message?': '+res.message:(res.error?' ('+res.error+')':'')),'err');
      return;
    }
    r.tripStatus=next; state.cache.groups=null; toast(t('saved'),'ok'); refreshBell(); renderOpsRows();
  });
}
function opsCancelTrip(tripId){
  uiConfirm(t('confirmCancelTrip'), function(){ opsSetStatus(tripId,'Cancelled'); });
}
/* kept for older call sites */
function opsToggleDone(tripId){
  var r=(state.cache.opsRows||[]).find(function(x){return x.tripId===tripId;});
  if(!r) return;
  opsSetStatus(tripId, r.tripStatus==='Done'?'Pending':'Done');
}

/* ── build a message ready to paste straight into WhatsApp / any chat app ── */
function tripMsgText(r){
  var day=clientDayName(r.date)||r.day||'';
  var L=[];
  L.push('🚌 *'+(state.lang==='ar'?'تفاصيل الرحلة':'TRIP DETAILS')+'*');
  L.push('━━━━━━━━━━━━━━');
  if(r.fileNo)    L.push('📁 '+t('fileNo')+': *'+r.fileNo+'*'+(r.groupName?' — '+r.groupName:''));
  if(r.tripType)  L.push('🧭 '+t('tripTypeLbl')+': '+r.tripType);
  L.push('📅 '+t('tripDate')+': '+(r.date||'—')+(day?' ('+day+')':''));
  L.push('🕐 '+t('timeLbl')+': *'+(r.time||'—')+'*');
  L.push('📍 '+t('fromLbl')+': '+(r.from||'—')+(r.fromDetail?' — '+r.fromDetail:''));
  L.push('🏁 '+t('toLbl')+': '+(r.to||'—')+(r.toDetail?' — '+r.toDetail:''));
  if(r.vehicleType||r.buses) L.push('🚐 '+t('vehicleType')+': '+(r.vehicleType||'—')+(r.buses?' × '+r.buses:''));
  if(r.pax)       L.push('👥 '+t('pax')+': '+r.pax);
  if(r.transportCompany) L.push('🏢 '+t('company')+': '+r.transportCompany+(r.orderNumber?' (#'+r.orderNumber+')':''));
  L.push('━━━━━━━━━━━━━━');
  L.push('👤 '+t('driverName')+': *'+(r.driverName||'—')+'*');
  L.push('📱 '+t('driverMobile')+': *'+(r.driverMobile||'—')+'*');
  L.push('🔢 '+t('plateNo')+': *'+(r.plateNo||'—')+'*');
  if(r.notes) L.push('📝 '+r.notes);
  return L.join('\n');
}
function copyTripMsg(tripId){
  var r=(state.cache.opsRows||[]).find(function(x){return x.tripId===tripId;})
      ||(state.cache.trips||[]).find(function(x){return x.tripId===tripId;});
  if(!r) return;
  copyText(tripMsgText(r));
}
function copyText(txt){
  // clipboard API is blocked inside the Apps Script iframe, so fall back to a temp textarea
  var done=function(){ toast(t('copied'),'ok'); };
  try{
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(txt).then(done, function(){ copyFallback(txt, done); });
      return;
    }
  }catch(e){}
  copyFallback(txt, done);
}
function copyFallback(txt, done){
  var ta=document.createElement('textarea');
  ta.value=txt;
  ta.style.position='fixed'; ta.style.opacity='0';
  document.body.appendChild(ta);
  ta.focus(); ta.select();
  try{ document.execCommand('copy'); done(); }
  catch(e){ toast(t('errServer'),'err'); }
  document.body.removeChild(ta);
}
function renderOpsRows(){
  var isOp = state.user.role==='operator';
  var allRows=opsFilteredRows();
  var ps=pageSlice('ops', allRows);
  var pgEl=document.getElementById('opsPager'); if(pgEl) pgEl.innerHTML=ps.pagerHtml;
  var rows=ps.rows;
  var cols = isOp ? 16 : 14;

  // summary strip (whole filtered set, not just this page)
  var done=allRows.filter(function(r){return r.tripStatus==='Done';}).length;
  var cancelled=allRows.filter(function(r){return r.tripStatus==='Cancelled';}).length;
  var remaining=allRows.length-done-cancelled;   // cancelled work is settled, not outstanding
  var veh=allRows.filter(function(r){return r.tripStatus!=='Cancelled';})
                 .reduce(function(s,r){ return s+(parseInt(r.buses)||0); },0);
  var sum=document.getElementById('opsSummary');
  if(sum) sum.innerHTML=
    '<span class="ops-chip">🚌 '+allRows.length+' '+t('sumTrips')+'</span>'+
    '<span class="ops-chip ok">✅ '+done+' '+t('sumDone')+'</span>'+
    '<span class="ops-chip warn">⏳ '+remaining+' '+t('sumRemaining')+'</span>'+
    (cancelled?'<span class="ops-chip" style="color:var(--red)">✕ '+cancelled+' '+t('stCancelledTrip')+'</span>':'')+
    '<span class="ops-chip">🚍 '+veh+' '+t('sumVehicles')+'</span>';

  // rows grouped under date headers
  var html='', lastDate=null;
  rows.forEach(function(r){
    if(r.date!==lastDate){
      lastDate=r.date;
      var dayN=clientDayName(r.date)||r.day||'';
      var cnt=allRows.filter(function(x){return x.date===r.date;}).length;
      html+='<tr class="ops-date-hd"><td colspan="'+cols+'">📅 <b dir="ltr">'+esc(r.date)+'</b> — '+esc(dayN)+
        ' <span style="color:var(--muted);font-weight:400">('+cnt+' '+t('sumTrips')+')</span></td></tr>';
    }
    html+='<tr'+(r.tripStatus==='Done'?' style="background:rgba(34,197,94,.07)"'
                :r.tripStatus==='Cancelled'?' style="opacity:.55"':'')+'>'+
      '<td dir="ltr" style="font-weight:700'+(r.tripStatus==='Cancelled'?';text-decoration:line-through':'')+'">'+esc(r.time)+'</td>'+
      '<td>'+esc(r.tripType)+'</td>'+
      '<td><span class="link" onclick="openGroup(\''+r.groupId+'\')">'+esc(r.fileNo)+'</span> <span class="link" title="'+t('quickTrips')+'" onclick="openGroupTripsModal(\''+r.groupId+'\')">🚌</span> <span style="color:var(--muted);font-size:.68rem">'+esc(r.groupName)+'</span></td>'+
      (isOp?'<td>'+esc(r.agentName)+'</td>':'')+
      '<td>'+esc(r.from)+(r.fromDetail?' <span style="color:var(--muted);font-size:.68rem">'+esc(r.fromDetail)+'</span>':'')+' → '+esc(r.to)+(r.toDetail?' <span style="color:var(--muted);font-size:.68rem">'+esc(r.toDetail)+'</span>':'')+'</td>'+
      '<td>'+esc(r.vehicleType)+'</td><td>'+esc(r.buses)+'</td><td>'+esc(r.pax)+'</td>'+
      '<td>'+esc(r.transportCompany)+'</td><td dir="ltr">'+esc(r.orderNumber)+(r.confirmationNo?' <span style="color:var(--muted);font-size:.66rem">✔'+esc(r.confirmationNo)+'</span>':'')+'</td>'+
      '<td class="drv">'+(r.driverName?'<b>'+esc(r.driverName)+'</b>':'<span style="color:var(--muted)">–</span>')+
        (r.driverMobile?'<br/><span dir="ltr" class="drv-mob">'+esc(r.driverMobile)+'</span>':'')+'</td>'+
      '<td>'+(r.plateNo?'<span class="plate">'+esc(r.plateNo)+'</span>':'<span style="color:var(--muted)">–</span>')+'</td>'+
      '<td>'+stBadge(r.bookingStatus)+'</td><td>'+stBadge(r.tripStatus)+'</td>'+
      '<td><span class="link" title="'+t('copyTrip')+'" onclick="copyTripMsg(\''+r.tripId+'\')">📋</span></td>'+
      (isOp?'<td style="white-space:nowrap">'+opsRowActions(r)+'</td>':'')+'</tr>';
  });
  document.getElementById('opsBody').innerHTML=html||'<tr class="no-data"><td colspan="'+cols+'">'+t('noTrips')+'</td></tr>';
}
/* export the currently filtered board as a printable / PDF document */
function exportOpsPdf(){
  var isOp = state.user.role==='operator';
  var rows=opsFilteredRows();
  var filters={today:t('today'),tomorrow:t('tomorrow'),next7:t('next7'),all:t('all')};
  var done=rows.filter(function(r){return r.tripStatus==='Done';}).length;
  var cancelled=rows.filter(function(r){return r.tripStatus==='Cancelled';}).length;
  var veh=rows.filter(function(r){return r.tripStatus!=='Cancelled';})
              .reduce(function(s,r){ return s+(parseInt(r.buses)||0); },0);
  var pax=rows.reduce(function(s,r){ return s+(parseInt(r.pax)||0); },0);
  var company=(state.cache.docCompany)||{en:'',ar:'',logo:''};

  var h=docHeader(company, 'Movement Schedule', 'جدول التحركات', false, '');

  h+='<table class="dt"><tr>'+
    k2('Scope','النطاق')+'<td>'+esc(filters[opsFilter]||'')+'</td>'+
    k2('Trips','الرحلات')+'<td class="num">'+rows.length+'</td>'+
    k2('Done','منفذة')+'<td class="num">'+done+'</td>'+
    k2('Remaining','متبقية')+'<td class="num">'+(rows.length-done-cancelled)+'</td>'+
    k2('Vehicles','المركبات')+'<td class="num">'+veh+'</td>'+
    '</tr></table>';

  h+=sec2('Daily Movements','التحركات اليومية')+
    '<table class="dt"><tr>'+
    th2('Time','الوقت')+th2('Movement','نوع التحرك')+th2('File','الملف')+
    (isOp?th2('Agent','الوكيل'):'')+
    th2('From → To','المسار')+th2('Vehicle','المركبة')+th2('Qty','العدد')+th2('Pax','الأفراد')+
    th2('Company','شركة النقل')+th2('Order No.','رقم التشغيل')+
    th2('Driver','السائق')+th2('Status','الحالة')+'</tr>';

  var colspan=isOp?12:11, lastDate=null;
  if(!rows.length) h+='<tr><td colspan="'+colspan+'" class="mut">—</td></tr>';
  rows.forEach(function(r){
    if(r.date!==lastDate){
      lastDate=r.date;
      var cnt=rows.filter(function(x){return x.date===r.date;}).length;
      h+='<tr class="daybar"><td colspan="'+colspan+'"><span dir="ltr">'+esc(r.date)+'</span> — '+
        esc(clientDayName(r.date)||r.day||'')+' &nbsp;· '+cnt+' trips / رحلات</td></tr>';
    }
    var pill = r.tripStatus==='Done'      ? '<span class="pill p-green">Done منفذة</span>'
             : r.tripStatus==='Cancelled' ? '<span class="pill p-gray">Cancelled ملغاة</span>'
             : '<span class="pill p-amber">Pending معلقة</span>';
    h+='<tr'+(r.tripStatus==='Done'?' class="done"':'')+'>'+
      '<td class="num" dir="ltr">'+esc(r.time)+'</td>'+
      '<td class="mv">'+moveTypeBoth(r.tripType)+'</td>'+
      '<td><b dir="ltr">'+esc(r.fileNo)+'</b><br/><span class="mut">'+esc(r.groupName)+'</span></td>'+
      (isOp?'<td class="mut">'+esc(r.agentName)+'</td>':'')+
      '<td>'+esc(r.from)+' → '+esc(r.to)+'</td>'+
      '<td>'+esc(r.vehicleType)+'</td><td class="num">'+esc(r.buses)+'</td><td>'+esc(r.pax)+'</td>'+
      '<td>'+esc(r.transportCompany)+'</td><td dir="ltr">'+esc(r.orderNumber)+'</td>'+
      '<td class="drv">'+(r.driverName?esc(r.driverName):'—')+
        (r.driverMobile?'<span class="mob">'+esc(r.driverMobile)+'</span>':'')+
        (r.plateNo?'<span class="plate">'+esc(r.plateNo)+'</span>':'')+'</td>'+
      '<td>'+pill+'</td></tr>';
  });
  h+='</table>';

  h+=docSign('Operations Manager','مدير العمليات','Transport Company','شركة النقل');
  h+='<div class="ft"><b>'+rows.length+' trips</b> · '+done+' done · '+(rows.length-done-cancelled)+' remaining'+
    (cancelled?' · '+cancelled+' cancelled':'')+' · '+veh+' vehicles · '+pax+' pax<br/>'+
    (company.en?'<b>'+dv(company.en)+'</b> — Movement Schedule — ':'')+
    '<span dir="ltr">'+new Date().toLocaleString()+'</span></div>';

  openPrintPreview(docShell('Movement Schedule', h, false, true), 'movement-schedule');   // landscape
}
function dv(v){ return (v===undefined||v===null)?'':v.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;'); }

/* ══════ DOCUMENT DESIGN SYSTEM ══════
   Table-based + solid colours: Google's HTML→PDF converter ignores gradients,
   flexbox and shadows, so everything here is built from things it renders faithfully. */
var DOC_BRAND = {
  navy:'#17263f', gold:'#8a6d2f', goldLt:'#d8c48a', cream:'#f4efe2',
  paper:'#fbfaf6', paper2:'#faf8f3', line:'#ddd3ba', line2:'#c9bfa5',
  ink:'#2b2620', body:'#5c5344', muted:'#8a7f6a'
};
var DOC_CSS =
  "@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');"+
  '*{box-sizing:border-box}'+
  'body{font-family:Tajawal,"Segoe UI",Arial,Tahoma,sans-serif;color:'+DOC_BRAND.body+';background:#fff;margin:0;padding:24px 22px;font-size:13px;-webkit-print-color-adjust:exact;print-color-adjust:exact}'+
  /* ── masthead ── */
  '.mast{width:100%;border-collapse:collapse;margin-bottom:10px}'+
  '.mast td{border:none;padding:0;vertical-align:middle}'+
  '.mast .co{font-size:21px;font-weight:700;color:'+DOC_BRAND.navy+';letter-spacing:1.4px;line-height:1.3}'+
  '.mast .co-ar{font-size:13px;color:'+DOC_BRAND.gold+';font-weight:700;margin-top:1px}'+
  '.mast img{max-height:56px;max-width:140px;display:block}'+
  '.mast .fileno{border:1px solid '+DOC_BRAND.line+';background:'+DOC_BRAND.paper+';padding:6px 14px;text-align:center}'+
  '.mast .fileno .l{font-size:8px;letter-spacing:1px;color:'+DOC_BRAND.muted+';font-weight:700}'+
  '.mast .fileno .v{font-size:21px;font-weight:700;color:'+DOC_BRAND.navy+';line-height:1.2}'+
  '.rule{height:2px;background:'+DOC_BRAND.navy+';margin:0 0 2px}'+
  '.rule-thin{height:1px;background:'+DOC_BRAND.goldLt+';margin-bottom:14px}'+
  /* ── document title ── */
  '.dtitle{text-align:center;margin:14px 0 16px}'+
  '.dtitle .en{font-size:16px;font-weight:700;color:'+DOC_BRAND.navy+';letter-spacing:5px;text-transform:uppercase}'+
  '.dtitle .ar{font-size:13px;color:'+DOC_BRAND.gold+';font-weight:700;margin-top:3px;letter-spacing:1px}'+
  /* ── section header ── */
  '.sec{background:'+DOC_BRAND.navy+';color:'+DOC_BRAND.cream+';font-weight:700;font-size:10.5px;letter-spacing:2.2px;padding:5px 11px;margin:15px 0 0;text-transform:uppercase}'+
  '.sec .ar{color:'+DOC_BRAND.goldLt+';letter-spacing:0;font-size:11px}'+
  /* ── tables ── */
  'table.dt{width:100%;border-collapse:collapse;margin:0}'+
  'table.dt th{background:'+DOC_BRAND.cream+';color:'+DOC_BRAND.navy+';font-size:9px;font-weight:700;letter-spacing:.6px;padding:7px 5px;text-align:center;border:1px solid '+DOC_BRAND.line+';text-transform:uppercase;line-height:1.5}'+
  'table.dt th .ar{display:block;color:'+DOC_BRAND.gold+';font-size:9.5px;letter-spacing:0;font-weight:400}'+
  'table.dt td{padding:8px 5px;text-align:center;border:1px solid '+DOC_BRAND.line+';font-size:12px;color:'+DOC_BRAND.ink+';background:#fff}'+
  'table.dt tr:nth-child(even) td{background:'+DOC_BRAND.paper2+'}'+
  'table.dt td.k{background:'+DOC_BRAND.paper+';color:'+DOC_BRAND.muted+';font-size:8.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;line-height:1.6}'+
  'table.dt td.k .ar{display:block;color:'+DOC_BRAND.gold+';font-size:9.5px;letter-spacing:0}'+
  'table.dt td.num{font-weight:700;color:'+DOC_BRAND.navy+';font-size:14px}'+
  'table.dt td.mut{color:'+DOC_BRAND.muted+';font-size:10.5px}'+
  'table.dt td.mv{font-weight:700;color:'+DOC_BRAND.navy+';font-size:12px;line-height:1.5}'+
  'table.dt td.mv .ar{display:block;color:'+DOC_BRAND.gold+';font-weight:400;font-size:10.5px}'+
  'table.dt td.tot{background:'+DOC_BRAND.cream+';font-weight:700;color:'+DOC_BRAND.navy+'}'+
  '.daybar td{background:'+DOC_BRAND.cream+'!important;color:'+DOC_BRAND.navy+';font-weight:700;text-align:start;font-size:11px;letter-spacing:1.4px}'+
  '.done td{background:'+DOC_BRAND.paper+'!important}'+
  /* ── pills ── */
  '.pill{display:inline-block;padding:3px 9px;border:1px solid '+DOC_BRAND.line2+';border-radius:2px;font-size:8.5px;font-weight:700;letter-spacing:1.1px;text-transform:uppercase}'+
  '.p-green{background:'+DOC_BRAND.paper+';color:'+DOC_BRAND.gold+';border-color:'+DOC_BRAND.goldLt+'}'+
  '.p-amber{background:#fff;color:'+DOC_BRAND.muted+'}'+
  '.p-gray{background:#fff;color:'+DOC_BRAND.muted+'}'+
  /* ── notes / signature / footer ── */
  '.note{background:'+DOC_BRAND.paper+';border:1px solid '+DOC_BRAND.line+';border-inline-start:3px solid '+DOC_BRAND.gold+';padding:9px 13px;font-size:12px;margin:12px 0;color:'+DOC_BRAND.ink+'}'+
  '.sign{width:100%;border-collapse:collapse;margin-top:26px}'+
  '.sign td{border:none;padding:0;width:50%;vertical-align:bottom}'+
  '.sign .box{border-top:1px solid '+DOC_BRAND.line2+';margin-top:44px;padding-top:6px;text-align:center;font-size:9.5px;font-weight:700;letter-spacing:1.2px;color:'+DOC_BRAND.navy+';text-transform:uppercase}'+
  '.sign .box .ar{display:block;color:'+DOC_BRAND.gold+';letter-spacing:0;font-size:10.5px;font-weight:400;margin-top:1px}'+
  '.ft{margin-top:20px;border-top:1px solid '+DOC_BRAND.line+';padding-top:8px;font-size:9.5px;color:'+DOC_BRAND.muted+';text-align:center;letter-spacing:.5px;line-height:1.7}'+
  '.ft b{color:'+DOC_BRAND.navy+'}'+
  'table.dt td.drv{font-weight:700;color:'+DOC_BRAND.navy+';font-size:12.5px;background:'+DOC_BRAND.goldSoft+'}'+
  'table.dt td.drv .mob{display:block;font-size:12.5px;font-weight:700;color:'+DOC_BRAND.gold+';letter-spacing:.4px;direction:ltr}'+
  'table.dt td.drv .plate{display:inline-block;margin-top:3px;border:1px solid '+DOC_BRAND.navy+';border-radius:3px;padding:1px 7px;font-size:12px;font-weight:700;color:'+DOC_BRAND.navy+';background:#fff;letter-spacing:1.2px}'+
  '@page{size:A4;margin:11mm}';
/* landscape variant — the movement schedule is far too wide for portrait */
var DOC_CSS_LANDSCAPE = DOC_CSS.replace('@page{size:A4;margin:11mm}','@page{size:A4 landscape;margin:9mm}');

/* bilingual table heading */
function th2(en, ar){ return '<th>'+dv(en)+'<span class="ar">'+dv(ar)+'</span></th>'; }
/* bilingual key cell */
function k2(en, ar){ return '<td class="k">'+dv(en)+'<span class="ar">'+dv(ar)+'</span></td>'; }
/* bilingual section bar */
function sec2(en, ar){
  return '<div class="sec">'+dv(en)+' &nbsp;<span class="ar">'+dv(ar)+'</span></div>';
}
/* masthead: logo · company · file-no block, then the ruled title */
function docHeader(company, titleEn, titleAr, rtl, fileNo){
  var logo = company.logo ? '<td style="width:1%;padding-inline-end:14px"><img src="'+escapeAttr(company.logo)+'" alt=""/></td>' : '';
  var file = fileNo ? '<td style="width:1%"><div class="fileno"><div class="l">FILE NO.</div>'+
      '<div class="v">'+dv(fileNo)+'</div><div class="l" style="color:'+DOC_BRAND.gold+'">رقم الملف</div></div></td>' : '';
  return '<table class="mast"><tr>'+logo+
      '<td><div class="co">'+dv(company.en)+'</div><div class="co-ar">'+dv(company.ar)+'</div></td>'+
      file+
    '</tr></table>'+
    '<div class="rule"></div><div class="rule-thin"></div>'+
    '<div class="dtitle"><div class="en">'+dv(titleEn)+'</div><div class="ar">'+dv(titleAr)+'</div></div>';
}
/* signature block */
function docSign(leftEn, leftAr, rightEn, rightAr){
  return '<table class="sign"><tr>'+
    '<td><div class="box">'+dv(leftEn)+'<span class="ar">'+dv(leftAr)+'</span></div></td>'+
    '<td style="width:8%"></td>'+
    '<td><div class="box">'+dv(rightEn)+'<span class="ar">'+dv(rightAr)+'</span></div></td>'+
    '</tr></table>';
}
function docShell(title, bodyHtml, rtl, landscape){
  return '<!DOCTYPE html><html'+(rtl?' dir="rtl"':'')+'><head><meta charset="UTF-8"/><title>'+dv(title)+'</title>'+
    '<style>'+(landscape?DOC_CSS_LANDSCAPE:DOC_CSS)+'</style></head><body>'+bodyHtml+'</body></html>';
}

/* Movement type shown in both languages: matches the value against the
   tripType masters and renders "English / العربية" when a pair exists. */
function moveTypeBoth(v){
  if(!v) return '';
  var list=((state.cache.mtypes||{}).tripType)||[];
  var m=list.filter(function(x){ return x.value_en===v || x.value_ar===v; })[0];
  if(!m) return dv(v);   // free-typed value: show as entered
  var en=m.value_en||'', ar=m.value_ar||'';
  if(en && ar && en!==ar) return dv(en)+'<br/><span class="mut">'+dv(ar)+'</span>';
  return dv(en||ar);
}

/* shared header: logo + company + coloured title ribbon */
function docHeader(company, titleEn, titleAr, rtl){
  var name = rtl ? dv(company.ar) : dv(company.en);
  var sub  = rtl ? dv(company.en) : dv(company.ar);
  var logo = company.logo
    ? '<td class="logo"><img src="'+escapeAttr(company.logo)+'" alt=""/></td>'
    : '';
  return '<table class="hdr"><tr>'+logo+
      '<td><div class="co">'+name+'</div><div class="co-sub">'+sub+'</div></td>'+
    '</tr></table>'+
    '<table class="ribbon"><tr><td>'+dv(titleAr)+' &nbsp;•&nbsp; '+dv(titleEn)+'</td></tr></table>';
}
function docShell(title, bodyHtml, rtl, landscape){
  return '<!DOCTYPE html><html'+(rtl?' dir="rtl"':'')+'><head><meta charset="UTF-8"/><title>'+dv(title)+'</title>'+
    '<style>'+(landscape?DOC_CSS_LANDSCAPE:DOC_CSS)+'</style></head><body>'+bodyHtml+'</body></html>';
}

/* ── VOUCHER (matches the agent voucher sample layout) ── */
function buildVoucherHtml(d){
  var g=d.group, a=d.agent;
  var h=docHeader(d.company, 'Service Voucher', 'قسيمة الخدمات', false, g.fileNo);

  /* key facts */
  h+='<table class="dt"><tr>'+
    k2('File No.','رقم الملف')+'<td class="num" dir="ltr">'+dv(g.fileNo)+'</td>'+
    k2('Group Code','رقم المجموعة')+'<td dir="ltr">'+dv(g.groupCode||'—')+'</td>'+
    k2('Total Pax','عدد المعتمرين')+'<td class="num">'+dv(g.totalPax)+'</td>'+
    k2('Agent Ref.','مرجع الوكيل')+'<td dir="ltr">'+dv(g.agentRef||'—')+'</td>'+
    '</tr></table>';

  h+=sec2('Group &amp; Agent','الوكيل والمجموعة')+
    '<table class="dt"><tr>'+
    th2('Agent Name','اسم الوكيل')+th2('Country','الدولة')+
    th2('Group Leader','اسم المشرف')+th2('Contact No.','رقم الجوال')+
    th2('Adults / Children / Infants','كبار / أطفال / رضع')+'</tr>'+
    '<tr><td>'+dv(a.agentName)+'</td><td>'+dv(a.country)+'</td>'+
    '<td>'+dv(g.leaderName)+'</td><td dir="ltr">'+dv(g.leaderMobile)+'</td>'+
    '<td>'+dv(g.adults||0)+' / '+dv(g.children||0)+' / '+dv(g.infants||0)+'</td></tr></table>';

  h+=sec2('Flight Details','معلومات الرحلة')+
    '<table class="dt"><tr>'+th2('Movement','نوع التحرك')+th2('Date','التاريخ')+th2('Day','اليوم')+
    th2('Time','الوقت')+th2('Flight No.','رقم الرحلة')+th2('Port','المنفذ')+'</tr>'+
    '<tr><td class="mv">Arrival<span class="ar">الوصول</span></td>'+
    '<td dir="ltr">'+dv(g.arrivalDate)+'</td><td class="mut">'+dv(clientDayName(g.arrivalDate))+'</td>'+
    '<td dir="ltr">'+dv(g.arrivalTime)+'</td><td dir="ltr">'+dv(g.arrivalFlight)+'</td><td>'+dv(g.arrivalPort)+'</td></tr>'+
    '<tr><td class="mv">Departure<span class="ar">المغادرة</span></td>'+
    '<td dir="ltr">'+dv(g.departureDate)+'</td><td class="mut">'+dv(clientDayName(g.departureDate))+'</td>'+
    '<td dir="ltr">'+dv(g.departureTime)+'</td><td dir="ltr">'+dv(g.departureFlight)+'</td><td>'+dv(g.departurePort)+'</td></tr></table>';

  if(g.bookingType==='Individual'){
    var host={}; try{ host=JSON.parse(g.hostJSON||'{}'); }catch(e){}
    h+=sec2('Hosting Details','بيانات الاستضافة')+
      '<table class="dt"><tr>'+th2('Host Name','اسم المضيف')+th2('ID / Iqama','الهوية')+
      th2('Mobile','الجوال')+th2('City','المدينة')+th2('Address','العنوان')+'</tr>'+
      '<tr><td>'+dv(host.name)+'</td><td dir="ltr">'+dv(host.id)+'</td><td dir="ltr">'+dv(host.mobile)+'</td>'+
      '<td>'+dv(host.city)+'</td><td>'+dv(host.address)+'</td></tr></table>';
  } else {
    h+=sec2('Accommodation','معلومات الفندق')+
      '<table class="dt"><tr>'+th2('City','المدينة')+th2('Hotel Name','اسم الفندق')+th2('Rooms','الغرف')+
      th2('Check-in','الدخول')+th2('Check-out','الخروج')+th2('Nights','الليالي')+'</tr>';
    if(d.hotels.length){
      d.hotels.forEach(function(x){
        h+='<tr><td>'+dv(x.city)+'</td><td>'+dv(x.hotelName)+'</td><td>'+dv(x.rooms)+'</td>'+
          '<td dir="ltr">'+dv(x.checkIn)+'</td><td dir="ltr">'+dv(x.checkOut)+'</td><td class="num">'+dv(x.nights)+'</td></tr>';
      });
    } else h+='<tr><td colspan="6" class="mut">—</td></tr>';
    h+='</table>';
  }

  // BRN agreements — Hotel + Catering (both booking kinds)
  var hotelBrnV=(d.brn||[]).filter(function(x){ return x.brnType!=='Catering'; });
  var cateringBrnV=(d.brn||[]).filter(function(x){ return x.brnType==='Catering'; });
  if(hotelBrnV.length){
    h+=sec2('Hotel Agreements (BRN)','اتفاقيات الفنادق')+
      '<table class="dt"><tr>'+th2('Hotel','الفندق')+th2('Check-in','الدخول')+th2('Check-out','الخروج')+
      th2('BRN No.','رقم الاتفاقية')+th2('Rooms','الغرف')+'</tr>';
    hotelBrnV.forEach(function(x){
      h+='<tr><td>'+dv(x.hotelName)+'</td><td dir="ltr">'+dv(x.checkIn)+'</td><td dir="ltr">'+dv(x.checkOut)+'</td>'+
        '<td dir="ltr">'+dv(x.brnNumber)+'</td><td>'+dv(x.roomsCount)+'</td></tr>';
    });
    h+='</table>';
  }
  if(cateringBrnV.length){
    h+=sec2('Catering Agreements (BRN)','اتفاقيات الإعاشة')+
      '<table class="dt"><tr>'+th2('Catering','الإعاشة')+th2('Check-in','الدخول')+th2('Check-out','الخروج')+
      th2('BRN No.','رقم الاتفاقية')+th2('Rooms','الغرف')+'</tr>';
    cateringBrnV.forEach(function(x){
      h+='<tr><td>'+dv(x.hotelName)+'</td><td dir="ltr">'+dv(x.checkIn)+'</td><td dir="ltr">'+dv(x.checkOut)+'</td>'+
        '<td dir="ltr">'+dv(x.brnNumber)+'</td><td>'+dv(x.roomsCount)+'</td></tr>';
    });
    h+='</table>';
  }

  h+=sec2('Transportation','معلومات المواصلات');
  if(d.orders.length){
    h+='<div class="note"><b>Transport Company · شركة النقل:</b> '+
      d.orders.map(function(o){
        return dv(o.transportCompany)+(o.orderNumber?' · #'+dv(o.orderNumber):'')+
          (o.status==='Booked'?' <span class="pill p-green">Confirmed مؤكد</span>':' <span class="pill p-amber">Pending بانتظار التأكيد</span>');
      }).join(' &nbsp;·&nbsp; ')+'</div>';
  }
  h+='<table class="dt"><tr><th>#</th>'+th2('Movement','نوع التحرك')+
    th2('Date','التاريخ')+th2('Day','اليوم')+th2('Time','الوقت')+
    th2('From','من')+th2('To','إلى')+th2('Vehicle','نوع المركبة')+th2('Qty','العدد')+'</tr>';
  if(d.trips.length){
    d.trips.forEach(function(x,i){
      h+='<tr><td class="mut">'+(i+1)+'</td>'+
        '<td class="mv">'+moveTypeBoth(x.tripType)+'</td>'+
        '<td dir="ltr">'+dv(x.date)+'</td><td class="mut">'+dv(clientDayName(x.date)||x.day)+'</td>'+
        '<td dir="ltr">'+dv(x.time)+'</td>'+
        '<td>'+dv(x.from)+(x.fromDetail?' <span class="mut">'+dv(x.fromDetail)+'</span>':'')+'</td>'+
        '<td>'+dv(x.to)+(x.toDetail?' <span class="mut">'+dv(x.toDetail)+'</span>':'')+'</td>'+
        '<td>'+dv(x.vehicleType)+'</td><td class="num">'+dv(x.buses)+'</td></tr>';
    });
  } else h+='<tr><td colspan="9" class="mut">—</td></tr>';
  h+='</table>';

  h+=docSign('Authorized Signature','التوقيع المعتمد','Agent Signature','توقيع الوكيل');
  h+='<div class="ft"><b>'+dv(d.company.en)+'</b> — Service Voucher — File No. '+dv(g.fileNo)+'<br/>'+
    '<span dir="ltr">'+dv(d.generatedAt)+'</span> — تم الإنشاء</div>';
  return docShell('Voucher '+g.fileNo, h, false);
}

function buildOrderHtml(d, orderId){
  var g=d.group, a=d.agent;
  var order=d.orders.find(function(o){return o.orderId===orderId;})||d.orders[0]||{};
  var trips=d.trips.filter(function(x){return x.orderId===orderId;});
  if(!trips.length) trips=d.trips;

  var h=docHeader(d.company, 'Bus Operation Order', 'أمر تشغيل باصات', true, g.fileNo);

  h+='<div class="note">السادة / <b>'+dv(order.transportCompany||'—')+'</b> المحترمين &nbsp;—&nbsp; تحية طيبة وبعد،'+
    '<br/>نأمل منكم تنفيذ التحركات الموضحة أدناه وفق الجدول التالي.</div>';

  h+='<table class="dt"><tr>'+
    k2('Order No.','رقم التشغيل')+'<td class="num" dir="ltr">'+dv(order.orderNumber)+'</td>'+
    k2('Confirmation','رقم التأكيد')+'<td class="num" dir="ltr">'+dv(order.confirmationNo||'—')+'</td>'+
    k2('Platform / BRN','حجز المنصة')+'<td dir="ltr">'+dv(order.brn||'—')+'</td>'+
    '</tr></table>';

  h+=sec2('Group Details','بيانات المجموعة')+
    '<table class="dt"><tr>'+
    th2('Group Code','رقم المجموعة')+th2('Agent','الوكيل')+th2('Agent Ref.','مرجع الوكيل')+
    th2('Group Leader','اسم المشرف')+th2('Contact','الجوال')+th2('Pax','العدد')+'</tr>'+
    '<tr><td dir="ltr">'+dv(g.groupCode||'—')+'</td><td>'+dv(a.agentName)+'</td>'+
    '<td dir="ltr">'+dv(g.agentRef||'—')+'</td>'+
    '<td>'+dv(g.leaderName)+'</td><td dir="ltr">'+dv(g.leaderMobile)+'</td>'+
    '<td class="num">'+dv(g.totalPax)+'</td></tr></table>';

  /* flight details — the bus times key off these, so the driver needs them on the sheet */
  h+=sec2('Flight Details','معلومات الرحلة')+
    '<table class="dt"><tr>'+th2('Movement','نوع التحرك')+th2('Date','التاريخ')+th2('Day','اليوم')+
    th2('Time','الوقت')+th2('Flight No.','رقم الرحلة')+th2('Port','المنفذ')+'</tr>'+
    '<tr><td class="mv">Arrival<span class="ar">الوصول</span></td>'+
    '<td dir="ltr">'+dv(g.arrivalDate)+'</td><td class="mut">'+dv(clientDayName(g.arrivalDate))+'</td>'+
    '<td dir="ltr" class="num">'+dv(g.arrivalTime)+'</td><td dir="ltr">'+dv(g.arrivalFlight)+'</td>'+
    '<td>'+dv(g.arrivalPort)+'</td></tr>'+
    '<tr><td class="mv">Departure<span class="ar">المغادرة</span></td>'+
    '<td dir="ltr">'+dv(g.departureDate)+'</td><td class="mut">'+dv(clientDayName(g.departureDate))+'</td>'+
    '<td dir="ltr" class="num">'+dv(g.departureTime)+'</td><td dir="ltr">'+dv(g.departureFlight)+'</td>'+
    '<td>'+dv(g.departurePort)+'</td></tr></table>';

  h+=sec2('Movement Schedule','جدول التحركات')+
    '<table class="dt"><tr><th>#</th>'+
    th2('Movement','نوع التحرك')+th2('From','محطة الانطلاق')+th2('To','محطة الوصول')+
    th2('Vehicle','نوع الحافلة')+th2('Qty','العدد')+
    th2('Time','الساعة')+th2('Date','التاريخ')+th2('Day','اليوم')+
    th2('Driver','السائق')+'</tr>';
  var totalBuses=0;
  trips.forEach(function(x,i){
    totalBuses += parseInt(x.buses)||0;
    h+='<tr><td class="mut">'+(i+1)+'</td>'+
      '<td class="mv">'+moveTypeBoth(x.tripType)+'</td>'+
      '<td>'+dv(x.from)+(x.fromDetail?' <span class="mut">'+dv(x.fromDetail)+'</span>':'')+'</td>'+
      '<td>'+dv(x.to)+(x.toDetail?' <span class="mut">'+dv(x.toDetail)+'</span>':'')+'</td>'+
      '<td>'+dv(x.vehicleType)+'</td><td class="num">'+dv(x.buses)+'</td>'+
      '<td dir="ltr">'+dv(x.time)+'</td><td dir="ltr">'+dv(x.date)+'</td>'+
      '<td class="mut">'+dv(clientDayName(x.date)||x.day)+'</td>'+
      '<td class="drv">'+(x.driverName?dv(x.driverName):'—')+
        (x.driverMobile?'<span class="mob">'+dv(x.driverMobile)+'</span>':'')+
        (x.plateNo?'<span class="plate">'+dv(x.plateNo)+'</span>':'')+'</td></tr>';
  });
  h+='<tr><td class="tot" colspan="5">TOTAL &nbsp;·&nbsp; الإجمالي</td><td class="tot">'+totalBuses+'</td>'+
    '<td class="tot" colspan="4">PAX · عدد المعتمرين: '+dv(g.totalPax)+'</td></tr></table>';

  var mak='', mad='';
  d.hotels.forEach(function(x){
    var c=(x.city||'').toLowerCase();
    if(!mak && c.indexOf('mak')!==-1) mak=x.hotelName;
    if(!mad && c.indexOf('madi')!==-1) mad=x.hotelName;
  });
  if(mak||mad){
    h+=sec2('Hotels','الفنادق')+
      '<table class="dt"><tr>'+th2('Hotel Makkah','الفندق بمكة المكرمة')+th2('Hotel Madinah','الفندق بالمدينة المنورة')+'</tr>'+
      '<tr><td>'+dv(mak||'—')+'</td><td>'+dv(mad||'—')+'</td></tr></table>';
  }

  h+=docSign('Transport Company','توقيع شركة النقل','Operator','توقيع المشغّل');
  h+='<div class="ft">شاكرين لكم حسن تعاونكم، وتفضلوا بقبول وافر التقدير والاحترام<br/>'+
    '<b>'+dv(d.company.ar)+'</b> — أمر تشغيل — File No. '+dv(g.fileNo)+' · <span dir="ltr">'+dv(d.generatedAt)+'</span></div>';
  return docShell('Order '+g.fileNo, h, true);
}

/* ── PREVIEW + PRINT + PDF ── */
function openPrintPreview(html, filename){
  currentPrintDoc={ html:html, filename:filename };
  document.getElementById('ppPrint').textContent=t('printLbl');
  document.getElementById('ppPdf').textContent=t('downloadPdf');
  document.getElementById('ppClose').textContent=t('closeLbl');
  document.getElementById('printFrame').srcdoc=html;
  document.getElementById('printOverlay').style.display='flex';
}
function closePrintPreview(){ document.getElementById('printOverlay').style.display='none'; }
function printPreviewDoc(){
  var f=document.getElementById('printFrame');
  try{ f.contentWindow.focus(); f.contentWindow.print(); }
  catch(e){ toast(t('errServer'),'err'); }
}
function downloadPreviewPdf(){
  toast(t('generating'));
  apiCall('pdf.fromHtml',{html:currentPrintDoc.html, filename:currentPrintDoc.filename}).then(function(res){
    if(!res.ok){ toast(t('pdfFail'),'err'); return; }
    var bytes=atob(res.base64), arr=new Uint8Array(bytes.length);
    for(var i=0;i<bytes.length;i++) arr[i]=bytes.charCodeAt(i);
    var blob=new Blob([arr],{type:'application/pdf'});
    var url=URL.createObjectURL(blob), a=document.createElement('a');
    a.href=url; a.download=res.filename; a.click();
    URL.revokeObjectURL(url);
    toast(t('saved'),'ok');
  });
}
function showVoucher(){
  Promise.all([apiCall('voucher.get',{groupId:state.currentGroupId}), loadMasterType('tripType')]).then(function(rs){
    var res=rs[0];
    if(!res.ok) return;
    openPrintPreview(buildVoucherHtml(res), 'voucher-'+res.group.fileNo);
  });
}
function showVoucherFor(groupId){ state.currentGroupId=groupId; showVoucher(); }
function showOrderDocFor(groupId){ state.currentGroupId=groupId; showOrderDoc(); }
function showOrderDoc(orderId){
  Promise.all([apiCall('voucher.get',{groupId:state.currentGroupId}), loadMasterType('tripType')]).then(function(rs){
    var res=rs[0];
    if(!res.ok) return;
    openPrintPreview(buildOrderHtml(res, orderId), 'transport-order-'+res.group.fileNo);
  });
}

/* ════════════════ FOLLOW-UP WORKFLOW BOARD ════════════════ */
var FU_STAGES=['Visa','Transport','Operation','Completed'];
var STAGE_BADGE={Visa:'b-amber',Transport:'b-blue',Operation:'b-purple',Completed:'b-green'};
function stageLabel(st){ return t('stage'+st); }
function deptStageMap(dept){ // which columns belong to a department
  if(dept==='visa') return ['Visa'];
  if(dept==='operations') return ['Transport','Operation'];
  return [];
}
function viewFollowup(c){
  c.innerHTML='<div class="page-title">🧭 '+t('followup')+'</div><div class="spinner"></div>';
  Promise.all([apiCall('followup.get')]).then(function(rs){
    if(!rs[0].ok) return;
    state.cache.fuRows=rs[0].rows;
    renderFollowup(c);
  });
}
function renderFollowup(c){
  var rows=state.cache.fuRows||[];
  var q=state.cache.fuSearch||'';
  var mine=deptStageMap(state.user.department||'');
  var cols=FU_STAGES.map(function(st){
    var items=rows.filter(function(r){
      if(r.stage!==st) return false;
      if(state.cache.fuAgentF && r.agentCode!==state.cache.fuAgentF) return false;
      return !q || (r.fileNo+' '+(r.groupCode||'')+' '+r.groupName+' '+r.agentName).toLowerCase().indexOf(q.toLowerCase())!==-1;
    });
    var cards=items.map(function(r){ return fuCard(r); }).join('');
    return '<div class="fu-col'+(mine.indexOf(st)!==-1?' mine':'')+'">'+
      '<div class="fu-col-hd"><span>'+stageLabel(st)+(mine.indexOf(st)!==-1?' ⭐':'')+'</span><span class="fu-count">'+items.length+'</span></div>'+
      (cards||'<div style="color:var(--muted);font-size:.72rem;text-align:center;padding:14px">'+t('noneInStage')+'</div>')+'</div>';
  }).join('');
  var agSet={}; rows.forEach(function(r){ if(r.agentCode) agSet[r.agentCode]=r.agentName; });
  var agOpts=Object.keys(agSet).map(function(code){ return [code, agSet[code]]; });
  var agSel = state.user.role==='operator'
    ? '<select onchange="state.cache.fuAgentF=this.value;renderFollowupBoardOnly()" style="padding:7px 10px;background:var(--input-bg);border:1px solid var(--border);border-radius:9px;font-size:.78rem">'+
      selectFieldOpts([['',t('agentFilter')+': '+t('allLbl')]].concat(agOpts), state.cache.fuAgentF||'')+'</select>'
    : '';
  c.innerHTML='<div class="page-title">🧭 '+t('followup')+
    '<span style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">'+agSel+
    '<div class="tbl-search"><input value="'+escapeAttr(q)+'" placeholder="'+t('search')+'" oninput="state.cache.fuSearch=this.value;renderFollowupBoardOnly()"/></div></span></div>'+
    '<div class="fu-board" id="fuBoard">'+cols+'</div>';
}
function renderFollowupBoardOnly(){
  // re-render columns only, keep search input focus
  var rows=state.cache.fuRows||[], q=state.cache.fuSearch||'';
  var mine=deptStageMap(state.user.department||'');
  var cols=FU_STAGES.map(function(st){
    var items=rows.filter(function(r){
      if(r.stage!==st) return false;
      if(state.cache.fuAgentF && r.agentCode!==state.cache.fuAgentF) return false;
      return !q || (r.fileNo+' '+(r.groupCode||'')+' '+r.groupName+' '+r.agentName).toLowerCase().indexOf(q.toLowerCase())!==-1;
    });
    var cards=items.map(function(r){ return fuCard(r); }).join('');
    return '<div class="fu-col'+(mine.indexOf(st)!==-1?' mine':'')+'">'+
      '<div class="fu-col-hd"><span>'+stageLabel(st)+(mine.indexOf(st)!==-1?' ⭐':'')+'</span><span class="fu-count">'+items.length+'</span></div>'+
      (cards||'<div style="color:var(--muted);font-size:.72rem;text-align:center;padding:14px">'+t('noneInStage')+'</div>')+'</div>';
  }).join('');
  var b=document.getElementById('fuBoard'); if(b) b.innerHTML=cols;
}
function stageAgeBadge(days, stage){
  if(days===''||days===undefined||days===null) return '';
  if(stage==='Completed') return ''; // no ageing once done
  var n=parseInt(days)||0;
  var cls=n>=5?'stale':(n>=3?'warn':'fresh');
  var label = n===0 ? t('today0') : (n===1 ? t('day1') : (n+' '+t('daysN')));
  return '<span class="stage-age '+cls+'" title="'+t('inStage')+'">⏱ '+label+'</span>';
}
function fuCard(r){
  var isOp=state.user.role==='operator';
  var idx=FU_STAGES.indexOf(r.stage);
  var prevSt=idx>0?FU_STAGES[idx-1]:null;
  var nextSt=idx<FU_STAGES.length-1?FU_STAGES[idx+1]:null;
  var isInd=r.bookingType==='Individual';
  var extra='';
  if(r.stage==='Visa'){
    extra='<div style="margin:4px 0;display:flex;align-items:center;gap:6px;flex-wrap:wrap">'+
      '<span class="badge '+(r.visaStatus==='Done'?'b-green':'b-amber')+'" style="font-size:.62rem">'+t(r.visaStatus==='Done'?'visaDone':'waitingVisa')+'</span>'+
      (isOp?'<button class="fu-btn '+(r.visaStatus==='Done'?'':'primary')+'" onclick="toggleVisa(\''+r.groupId+'\','+(r.visaStatus==='Done'?'false':'true')+')">'+t(r.visaStatus==='Done'?'undoVisa':'markVisaDone')+'</button>':'')+
      '</div>';
  }
  if(r.stage==='Transport'){
    var lbl, cls;
    if(r.ordersConfirmed>0){ lbl='transportConfirmed'; cls='b-green'; }
    else if(r.ordersCount>0){ lbl='waitingConfirm'; cls='b-amber'; }   // order exists but still "Requested"
    else { lbl='noOrderYet'; cls='b-gray'; }
    extra='<div style="margin:4px 0"><span class="badge '+cls+'" style="font-size:.62rem">'+t(lbl)+'</span></div>';
  }
  if(r.stage==='Operation'){
    var pct=r.tripsTotal?Math.round(r.tripsDone*100/r.tripsTotal):0;
    var allDone=r.tripsTotal>0 && r.tripsDone===r.tripsTotal;
    extra='<div class="fu-meta">'+r.tripsDone+'/'+r.tripsTotal+' '+t('tripsDoneLbl')+(allDone?' ✅':'')+'</div>'+
      '<div class="fu-prog" style="cursor:pointer" onclick="openGroupTripsModal(\''+r.groupId+'\')"><div style="width:'+pct+'%"></div></div>'+
      '<button class="fu-btn" style="width:100%;margin-bottom:4px" onclick="openGroupTripsModal(\''+r.groupId+'\')">'+t('openTrips')+'</button>';
  }
  var nextBtn='';
  if(isOp && nextSt){
    var gate='';
    if(nextSt==='Transport' && r.visaStatus!=='Done') gate='gateVisa';
    if(nextSt==='Operation' && !(r.ordersConfirmed>0)) gate='gateOrder';
    if(nextSt==='Completed' && !(r.tripsTotal>0 && r.tripsDone===r.tripsTotal)) gate='gateTrips';
    if(gate){
      nextBtn='<button class="fu-btn" style="opacity:.55" onclick="toast(t(\''+gate+'\'),\'err\')">'+stageLabel(nextSt)+' →</button>';
    } else {
      nextBtn='<button class="fu-btn primary" onclick="openStageMove(\''+r.groupId+'\',\''+nextSt+'\')">'+stageLabel(nextSt)+' →</button>';
    }
  }
  return '<div class="fu-card">'+
    '<div class="fu-title" style="display:flex;align-items:center;justify-content:space-between;gap:6px"><span><span class="link" onclick="openGroupPopup(\''+r.groupId+'\')">#'+esc(r.fileNo)+' '+esc(r.groupName)+'</span> '+
    (isInd?'<span class="badge b-purple" style="font-size:.58rem">'+t('kindIndividual')+'</span>':'')+'</span>'+
    stageAgeBadge(r.daysInStage, r.stage)+'</div>'+
    (r.groupCode?'<div class="fu-meta" dir="ltr">'+esc(r.groupCode)+'</div>':'')+
    '<div class="fu-meta">'+esc(r.agentName)+' • '+esc(r.totalPax)+' pax • 🛬 '+esc(r.arrivalDate)+'</div>'+
    extra+
    (isOp?'<div class="fu-actions">'+
    (prevSt?'<button class="fu-btn" onclick="openStageMove(\''+r.groupId+'\',\''+prevSt+'\')">← '+stageLabel(prevSt)+'</button>':'<span></span>')+
    nextBtn+'</div>':'')+'</div>';
}
function toggleVisa(groupId, done){
  if(done){
    // marking Done → ask for a note first
    openModal(t('visaNoteTitle'),
      '<div class="field"><label>'+t('visaNoteLbl')+'</label><textarea id="mVisaNote" rows="3" placeholder="'+t('visaNoteLbl')+'"></textarea></div>',
      function(){ visaSend(groupId, true, val('mVisaNote')); });
    return;
  }
  visaSend(groupId, false, '');
}
function visaSend(groupId, done, note){
  apiCall('groups.setVisa',{groupId:groupId,done:done,note:note||''}).then(function(res){
    if(res.ok){
      var r=(state.cache.fuRows||[]).find(function(x){return x.groupId===groupId;});
      if(r) r.visaStatus=done?'Done':'';
      closeModal(); toast(t('saved'),'ok'); refreshBell(); renderFollowupBoardOnly();
    }
  });
}
function openStageMove(groupId, stage){
  var r=(state.cache.fuRows||[]).find(function(x){return x.groupId===groupId;});
  openModal(t('moveToStage')+': '+stageLabel(stage)+(r?' — #'+esc(r.fileNo):''),
    '<div class="field"><label>'+t('stageNote')+'</label><textarea id="mStgNote" rows="2"></textarea></div>',
    function(){
      apiCall('groups.setStage',{groupId:groupId,stage:stage,note:val('mStgNote')}).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); refreshBell(); viewFollowup(document.getElementById('content')); }
        else if(res.error==='NEED_VISA') toast(t('gateVisa'),'err');
        else if(res.error==='NEED_ORDER') toast(t('gateOrder'),'err');
        else if(res.error==='NEED_TRIPS') toast(t('gateTrips'),'err');
      });
    });
}
/* stage progress bar for group detail */
function stageBarHtml(stage){
  var cur=FU_STAGES.indexOf(stage)!==-1?FU_STAGES.indexOf(stage):0;
  return '<div class="stage-bar">'+FU_STAGES.map(function(st,i){
    var cls=i<cur?'done':(i===cur?'current':'');
    return '<div class="stage-dot '+cls+'"><span class="dot">'+(i<cur?'✓':(i+1))+'</span><span>'+stageLabel(st)+'</span></div>';
  }).join('')+'</div>';
}

/* ── quick trips panel (from follow-up cards) ── */
function openGroupTripsModal(groupId){
  apiCall('transport.list',{groupId:groupId}).then(function(res){
    if(!res.ok) return;
    state.cache.qtTrips=res.trips.slice().sort(function(a,b){ return (a.date||'').localeCompare(b.date||'')||(a.time||'').localeCompare(b.time||''); });
    state.cache.qtGroupId=groupId;
    renderQuickTrips();
  });
}
function renderQuickTrips(){
  var isOp=state.user.role==='operator';
  var trips=state.cache.qtTrips||[];
  var rows=trips.map(function(tr,i){
    var done=tr.tripStatus==='Done';
    var actions='';
    if(isOp){
      actions='<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">'+
        '<input id="qtd_name_'+i+'" placeholder="'+t('driverName')+'" value="'+escapeAttr(tr.driverName)+'" style="flex:1;min-width:110px;padding:5px 8px;background:var(--input-bg);border:1px solid var(--border);border-radius:7px;font-size:.72rem"/>'+
        '<input id="qtd_mob_'+i+'" dir="ltr" placeholder="'+t('driverMobile')+'" value="'+escapeAttr(tr.driverMobile)+'" style="flex:1;min-width:100px;padding:5px 8px;background:var(--input-bg);border:1px solid var(--border);border-radius:7px;font-size:.72rem"/>'+
        '<input id="qtd_plate_'+i+'" placeholder="'+t('plateNo')+'" value="'+escapeAttr(tr.plateNo)+'" style="flex:1;min-width:95px;padding:5px 8px;background:var(--input-bg);border:1px solid var(--border);border-radius:7px;font-size:.72rem;font-weight:700;letter-spacing:1px"/>'+
        '<button class="fu-btn" onclick="qtSaveDriver('+i+')">💾 '+t('saveDriver')+'</button>'+
        '<button class="fu-btn '+(done?'':'primary')+'" onclick="qtToggleDone('+i+')">'+(done?t('undoDone'):t('markDone'))+'</button>'+
        '</div>';
    } else if(tr.driverName){
      actions='<div class="fu-meta">👤 '+esc(tr.driverName)+(tr.driverMobile?' <span dir="ltr">'+esc(tr.driverMobile)+'</span>':'')+(tr.plateNo?' <span class="plate">'+esc(tr.plateNo)+'</span>':'')+'</div>';
    }
    return '<div class="wz-card" style="'+(done?'border-color:rgba(34,197,94,.5)':'')+'">'+
      '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:.78rem">'+
      '<b>'+(i+1)+'. '+esc(tr.tripType)+'</b><span>'+stBadge(tr.bookingStatus)+' '+stBadge(tr.tripStatus)+'</span></div>'+
      '<div class="fu-meta" style="margin-top:4px" dir="ltr">'+esc(tr.date)+(clientDayName(tr.date)?' ('+esc(clientDayName(tr.date))+')':'')+' '+esc(tr.time)+' — '+esc(tr.from)+' → '+esc(tr.to)+
      (tr.vehicleType?' • '+esc(tr.vehicleType)+' ×'+esc(tr.buses):'')+'</div>'+
      actions+'</div>';
  }).join('');
  openModal(t('quickTrips'), rows||'<div style="color:var(--muted);text-align:center;padding:14px">'+t('noTrips')+'</div>', null);
}
function tripSavePayload(tr){
  // whitelist + stringify exactly what trips.save needs (avoids serialization surprises)
  return {
    tripId:String(tr.tripId||''), groupId:String(tr.groupId||''), orderId:String(tr.orderId||''),
    date:String(tr.date||''), tripType:String(tr.tripType||''), transportMode:String(tr.transportMode||''),
    vehicleType:String(tr.vehicleType||''), from:String(tr.from||''), fromDetail:String(tr.fromDetail||''),
    to:String(tr.to||''), toDetail:String(tr.toDetail||''), time:String(tr.time||''),
    flightNo:String(tr.flightNo||''), buses:String(tr.buses||''), pax:String(tr.pax||''),
    driverName:String(tr.driverName||''), driverMobile:String(tr.driverMobile||''),
    bookingStatus:String(tr.bookingStatus||''), tripStatus:String(tr.tripStatus||''), notes:String(tr.notes||'')
  };
}
function qtSend(i, patch){
  var tr=state.cache.qtTrips[i];
  if(patch.tripStatus==='Done' && isFutureDate(tr.date)){ toast(t('vTripFuture'),'err'); return; }
  // send ONLY the identity + changed fields; server patches just these
  var p={ tripId:String(tr.tripId||''), groupId:String(state.cache.qtGroupId||tr.groupId||'') };
  Object.keys(patch).forEach(function(k){ p[k]=String(patch[k]==null?'':patch[k]); });
  apiCall('trips.save', p).then(function(res){
    if(!res.ok){ toast(t('errServer')+(res.message?': '+res.message:(res.error?' ('+res.error+')':'')),'err'); return; }
    Object.assign(tr, patch);
    var op=(state.cache.opsRows||[]).find(function(x){return x.tripId===tr.tripId;});
    if(op) Object.assign(op, patch);
    if(patch.tripStatus!==undefined) state.cache.groups=null;  // auto status may have changed
    toast(t('saved'),'ok');
    renderQuickTrips();
    qtRefreshFollowup();
    refreshBell();
    if(state.view==='ops') renderOpsRows();
  });
}
function qtSaveDriver(i){
  qtSend(i, { driverName: val('qtd_name_'+i), driverMobile: val('qtd_mob_'+i), plateNo: val('qtd_plate_'+i) });
}
function qtToggleDone(i){
  var tr=state.cache.qtTrips[i];
  qtSend(i, { tripStatus: tr.tripStatus==='Done'?'Pending':'Done' });
}
function qtRefreshFollowup(){
  // update the cached follow-up row's progress so the board reflects changes live
  var rows=state.cache.fuRows||[], gid=state.cache.qtGroupId;
  var fu=rows.find(function(r){ return r.groupId===gid; });
  if(fu){
    var trips=state.cache.qtTrips||[];
    fu.tripsTotal=trips.length;
    fu.tripsDone=trips.filter(function(tr){ return tr.tripStatus==='Done'; }).length;
    if(state.view==='followup') renderFollowupBoardOnly();
  }
}

function toggleArchive(groupId, on){
  apiCall('groups.archive',{groupId:groupId,archived:on}).then(function(res){
    if(res.ok){
      var g=currentGroup(); if(g) g.archived=on?'yes':'';
      toast(t('saved'),'ok');
      if(on) go('groups'); else viewGroupDetail(document.getElementById('content'));
    } else if(res.error==='NOT_ARCHIVABLE') toast(t('notArchivable'),'err');
  });
}
function setTransportBy(groupId, v){
  apiCall('groups.setTransportBy',{groupId:groupId,transportBy:v}).then(function(res){
    if(res.ok){
      var g=currentGroup(); if(g) g.transportBy=v;
      toast(t('saved'),'ok'); viewGroupDetail(document.getElementById('content'));
    }
  });
}

/* agent requests changes on an approved group (reuses the wizard, prefilled) */
function requestGroupEdit(){
  var g=currentGroup(); if(!g) return;
  var pseudo={ requestId:'', payloadJSON: JSON.stringify({
    group:g, hotels:state.cache.hotels||[], brn:state.cache.brn||[], trips:state.cache.trips||[]
  })};
  openRequestForm('agent', pseudo);
  state.wizard.resubmitId='';
  state.wizard.editGroupId=g.groupId;
}

/* ── group quick-view popup (from follow-up) — mirrors the full Group Info page ── */
function openGroupPopup(groupId){
  Promise.all([
    apiCall('hotels.list',{groupId:groupId}),
    apiCall('transport.list',{groupId:groupId}),
    state.cache.groups?Promise.resolve({ok:true,rows:state.cache.groups}):apiCall('groups.list'),
    loadMasterType('tripType'), loadMasterType('vehicleType')
  ]).then(function(rs){
    if(!rs[0].ok||!rs[1].ok||!rs[2].ok) return;
    if(!state.cache.groups) state.cache.groups=rs[2].rows;
    var g=(rs[2].rows||state.cache.groups).find(function(x){return x.groupId===groupId;});
    if(!g){ openGroup(groupId); return; }
    // populate caches so the shared section builder can read them
    state.cache.hotels=rs[0].bookings||[];
    state.cache.brn=rs[0].brn||[];
    state.cache.trips=rs[1].trips||[];
    state.cache.orders=rs[1].orders||[];
    state.currentGroupId=groupId;   // so +Order / print target this group
    var isOp=state.user.role==='operator';
    var body='<div style="margin-bottom:10px"><span class="badge '+(g.bookingType==='Individual'?'b-purple':'b-blue')+'">'+
      t(g.bookingType==='Individual'?'kindIndividual':'kindGroup')+'</span> '+stBadge(g.status)+'</div>'+
      groupSectionsHtml(g, isOp, true);   // readOnly = true (orders stay actionable)
    openModal('#'+esc(g.fileNo)+' — '+esc(g.groupName), body, null);
    modalWide(true);
    loadGroupLog(groupId);
    document.getElementById('modalFooter').innerHTML=
      '<button class="btn btn-ghost" onclick="closeModal()">'+t('closeLbl')+'</button>'+
      '<button class="btn btn-primary" onclick="closeModal();openGroup(\''+groupId+'\')">'+t('openFull')+'</button>';
  });
}

/* ════════════════ CHANGE PASSWORD ════════════════ */
function openChangePassword(){
  openModal(t('changePass'),
    field('mCpOld',t('oldPass'),'','password')+field('mCpNew',t('newPass'),'','password'),
    function(){
      apiCall('auth.changePassword',{oldPassword:val('mCpOld'),newPassword:val('mCpNew')}).then(function(res){
        if(res.ok){ toast(t('saved'),'ok'); closeModal(); }
        else toast(t(res.error==='WRONG_OLD'?'errWrongOld':'errWeak'),'err');
      });
    });
}

/* themed confirmation dialog (replaces native confirm) */
function uiConfirm(msg, onYes){
  openModal(t('confirmTitle'), '<div style="font-size:.86rem;padding:4px 2px;line-height:1.6">'+msg+'</div>', null);
  document.getElementById('modalFooter').innerHTML=
    '<button class="btn btn-ghost" onclick="closeModal()">'+t('cancel')+'</button>'+
    '<button class="btn btn-primary" id="uiConfirmYes">'+t('confirmYes')+'</button>';
  document.getElementById('uiConfirmYes').onclick=function(){ closeModal(); onYes(); };
}

/* ════════════════ MODAL HELPERS ════════════════ */
function todayStrLocal(){
  var d=new Date();
  return d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);
}
/* only the admin user may pick past dates; everyone else (operators + agents) is blocked. DOB exempt. */
function dateMinAttr(id){
  var isAdmin = state.user && state.user.username==='admin';
  if(!isAdmin && id!=='wh_dob') return ' min="'+todayStrLocal()+'"';
  return '';
}
function field(id,label,value,type){
  var extra = type==='date' ? dateMinAttr(id) : '';
  return '<div class="field"><label>'+label+'</label><input id="'+id+'" type="'+(type||'text')+'"'+extra+' value="'+escapeAttr(value)+'"/></div>';
}
function selectField(id,label,options,selected){
  var opts=options.map(function(o){
    return '<option value="'+escapeAttr(o[0])+'"'+(o[0]===selected?' selected':'')+'>'+o[1]+'</option>';
  }).join('');
  return '<div class="field"><label>'+label+'</label><select id="'+id+'">'+opts+'</select></div>';
}
function val(id){ var el=document.getElementById(id); return el?el.value.trim():''; }
function escapeAttr(v){ return (v===undefined||v===null)?'':v.toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
function modalWide(on){ var m=document.querySelector('.modal'); if(m) m.classList[on?'add':'remove']('wide'); }
function openModal(title, bodyHtml, onSave){
  modalWide(false);
  document.getElementById('modalTitle').textContent=title;
  document.getElementById('modalBody').innerHTML=bodyHtml;
  var ft=document.getElementById('modalFooter');
  ft.innerHTML='<button class="btn btn-ghost" onclick="closeModal()">'+t('cancel')+'</button>'+
    (onSave?'<button class="btn btn-primary" id="modalSaveBtn">'+t('save')+'</button>':'');
  if(onSave) document.getElementById('modalSaveBtn').onclick=onSave;
  document.getElementById('modalOverlay').style.display='flex';
}
function closeModal(){ document.getElementById('modalOverlay').style.display='none'; }
document.getElementById('modalOverlay').addEventListener('click',function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>