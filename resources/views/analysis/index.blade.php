<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>UAV Forest AI · Centro de análisis</title>
  <style>
:root {
  --forest:#123a2e; --forest-deep:#0b3026; --forest-mid:#22604b;
  --forest-bright:#14855c; --mint:#e9f5ef; --canvas:#f3f8f5;
  --surface:#fff; --surface-soft:#f8fbf9; --text:#172b25;
  --muted:#5d7067; --line:#dce8e1; --line-strong:#c9ddd1;
  --blue:#256eb9; --blue-soft:#ebf4ff; --purple:#754ab6;
  --purple-soft:#f3edfb; --amber:#a36b1d; --amber-soft:#fff7e8;
  --red:#b93838; --red-soft:#fff0ee; --success:#157347;
  --radius:16px; --shadow:0 9px 35px rgba(13,56,38,.055);
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--canvas);color:var(--text);font:14px/1.5 Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif}
button,select,input{font:inherit}
button,a,select,input{ -webkit-tap-highlight-color:transparent }
button{cursor:pointer}
a{color:inherit}
:focus-visible{outline:3px solid #55a689;outline-offset:3px}
[hidden]{display:none!important}
.sr-only{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
.ico{width:21px;height:21px;flex:none;fill:none;stroke:currentColor;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round}
.ico-sm{width:17px;height:17px}.ico-lg{width:27px;height:27px}
.topbar{position:sticky;top:0;z-index:40;color:#fff;background:linear-gradient(110deg,#0d352a,#0a2d25);box-shadow:0 5px 25px rgba(9,44,32,.16)}
.topbar-inner{max-width:1540px;min-height:76px;margin:auto;padding:10px 32px;display:flex;align-items:center;gap:28px}
.brand{display:flex;gap:13px;align-items:center;flex-shrink:0;min-width:245px}
.brand-logo{width:48px;height:48px;display:grid;place-items:center;border:1px solid #ffffff2b;border-radius:14px;background:#ffffff10}
.brand-logo .ico{width:29px;height:29px;stroke-width:1.55}
.brand-name{font-weight:800;font-size:21px;letter-spacing:-.025em;line-height:1.2}
.brand-subtitle{font-size:11px;color:#d1e1d9;margin-top:3px;white-space:nowrap}
.nav-main{display:flex;gap:5px;align-items:center;justify-content:center;flex:1}
.nav-item{display:flex;align-items:center;gap:8px;padding:12px 13px;border:1px solid transparent;border-radius:11px;background:none;color:#d3e4db;font-size:12px;font-weight:650;white-space:nowrap;transition:.18s}
.nav-item:hover{background:#ffffff12;color:#fff}.nav-item.is-active{background:#ffffff22;border-color:#ffffff11;color:#fff}
.system-status{display:flex;gap:13px;align-items:center;justify-content:flex-end;min-width:190px}
.system-status-copy{display:flex;flex-direction:column;line-height:1.25;text-align:right}
.system-status-label{font-size:11px;font-weight:750}.system-status-detail{font-size:10px;color:#c0d9cd;margin-top:3px}
.api-status{display:inline-flex;align-items:center;gap:7px;border:1px solid #ffffff33;border-radius:999px;padding:9px 12px;font-size:11px;font-weight:750;white-space:nowrap;background:#ffffff09}
.api-dot{width:8px;height:8px;border-radius:50%;display:inline-block;flex:none}.api-dot.online{background:#42cf8f}.api-dot.offline{background:#ff6473}
main{max-width:1540px;padding:28px 32px 60px;margin:0 auto}
.analysis-mode-panel{display:none}.analysis-mode-panel.is-active{display:block}
.page-hero{position:relative;min-height:172px;overflow:hidden;border-radius:18px 18px 0 0;background:linear-gradient(105deg,#fff 20%,#f9fcfa 67%,#d8e9dd);padding:27px 27px 30px}
.hero-media{position:absolute;right:0;top:0;bottom:0;width:54%;background-position:center;background-size:cover;opacity:.80;mask-image:linear-gradient(to right,transparent 0%,#000 70%);-webkit-mask-image:linear-gradient(to right,transparent 0%,#000 70%);pointer-events:none}
.hero-media:after{content:"";position:absolute;inset:0;background:linear-gradient(to bottom,rgba(255,255,255,.07),rgba(255,255,255,.44))}
.hero-copy{position:relative;z-index:1;max-width:900px}.hero-kicker{display:block;margin:0 0 6px;color:#11613f;letter-spacing:.08em;font-size:10px;font-weight:800;text-transform:uppercase}
.page-hero h1{font-size:clamp(27px,2.6vw,37px);font-weight:800;line-height:1.16;letter-spacing:-.035em;margin:0 0 9px;color:#10251e}
.page-hero p{max-width:690px;font-size:13px;line-height:1.5;color:#526960;margin:0}
.hero-api{position:absolute;right:20px;top:21px;z-index:2;display:inline-flex;align-items:center;gap:9px;padding:10px 14px;border-radius:99px;background:#fff;box-shadow:0 5px 17px rgba(11,48,38,.13);font-size:11px;font-weight:800}
.hero-api.is-offline{color:var(--red)}.hero-api.is-online{color:var(--success)}
.workspace-shell{padding:0 17px 18px;background:linear-gradient(#fff9,#fff);border-radius:0 0 18px 18px;box-shadow:var(--shadow)}
.card{background:var(--surface);border:1px solid var(--line);border-radius:15px;box-shadow:0 6px 23px rgba(14,61,41,.035)}
.section-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:12px}
.section-heading-left{display:flex;align-items:center;gap:12px;min-width:0}
.section-heading h2,.section-heading h3{font-size:16px;line-height:1.25;letter-spacing:-.016em;margin:0;font-weight:800}
.section-heading p{color:var(--muted);font-size:12px;margin:4px 0 0}
.icon-tile{width:37px;height:37px;display:grid;place-items:center;border-radius:11px;background:#e7f3ed;color:#0e6a46;flex-shrink:0}
.icon-tile.dark{background:linear-gradient(135deg,#31765e,#0d4438);color:white}
.icon-tile.blue{background:var(--blue-soft);color:var(--blue)}
.icon-tile.purple{background:var(--purple-soft);color:var(--purple)}
.icon-tile.amber{background:var(--amber-soft);color:#bd790e}
.mosaic-card{padding:17px 16px 14px;margin-bottom:16px}
.mosaic-selection{display:flex;gap:16px;align-items:center;padding:12px;border:1px solid #e0eae4;background:#fbfdfc;border-radius:13px}
.mosaic-thumbnail{width:100px;height:74px;flex:none;border:1px solid #dde8e0;border-radius:9px;background:#eaf2ec;display:grid;place-items:center;overflow:hidden;color:#1b7955}
.mosaic-thumbnail img{width:100%;height:100%;object-fit:cover}
.mosaic-details{flex:1;min-width:0}.mosaic-name{font-size:14px;font-weight:800;overflow-wrap:anywhere;line-height:1.4}
.mosaic-project,.mosaic-meta{color:var(--muted);font-size:11px;margin-top:4px;overflow-wrap:anywhere}
.mosaic-meta{display:flex;align-items:center;flex-wrap:wrap;gap:4px 10px;font-weight:600}
.mosaic-picker{margin:0;flex:none;max-width:100%}.select-control{position:relative;display:block;min-width:179px;max-width:100%;border:1px solid #d8e4dd;border-radius:10px;background:linear-gradient(#f7faf8,#eef4f0)}
.select-face{display:flex;align-items:center;justify-content:center;gap:9px;padding:11px 13px;color:var(--forest);font-size:11px;font-weight:800;pointer-events:none}
.select-control select{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.select-control:focus-within{outline:3px solid #55a689;outline-offset:2px}
.help-inline{display:flex;align-items:start;gap:7px;font-size:11px;color:var(--muted);margin-top:10px}.help-inline .ico{margin-top:1px}
.spatial-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:15px}
.spatial-item{min-width:0;display:flex;align-items:center;gap:12px;border:1px solid #e0e9e4;border-radius:12px;padding:10px 12px;background:#fff;min-height:72px}
.stat-orb{width:43px;height:43px;border-radius:50%;display:grid;place-items:center;flex:none;background:#e8f2ff;color:#2171d2}
.stat-orb.green{background:#e3f5ec;color:#07905c}.stat-orb.purple{background:#f2edff;color:#754ac4}.stat-orb.gray{background:#ecf5f2;color:#176a60}
.spatial-label{font-size:11px;color:#697e73}.spatial-value{font-size:14px;font-weight:800;line-height:1.4;overflow-wrap:anywhere}
.status-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:800;background:#e4f6ea;color:#136d41}
.status-tag.error{color:var(--red);background:var(--red-soft)}.status-tag.neutral{color:#537061;background:#eaf2ed}.status-tag.pending{background:#fff3dd;color:#8b5b11}
.protocol-card{margin-bottom:16px;overflow:hidden;padding:0}
.protocol-head{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;list-style:none;cursor:pointer}
.protocol-head::-webkit-details-marker{display:none}.protocol-head:focus-visible{outline-offset:-4px}
.protocol-summary{display:flex;align-items:center;gap:12px;min-width:0}.protocol-summary strong{font-size:15px;font-weight:800}
.protocol-summary>span:last-child>span{display:block;color:var(--muted);font-size:11px;margin-top:3px}
.lock-tag{display:inline-flex;align-items:center;gap:6px;border-radius:99px;background:#e8f5ee;color:#126741;font-size:10px;font-weight:800;padding:8px 11px;white-space:nowrap}
.protocol-chevron{display:none;color:var(--forest-mid)}.protocol-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));margin:0 7px 7px;border:1px solid #e2ebe5;border-radius:12px;overflow:hidden}
.protocol-item{padding:12px 14px;min-width:0;border-right:1px solid #e4ede8;border-bottom:1px solid #e4ede8;display:flex;align-items:center;gap:10px}
.protocol-item:nth-child(4n){border-right:0}.protocol-item:nth-child(n+5){border-bottom:0}
.protocol-ico{width:39px;height:39px;flex:none;border-radius:50%;display:grid;place-items:center;color:#236c6b;background:#edf6f4}
.protocol-name{font-size:10px;color:#5d7068;line-height:1.35}.protocol-value{font-size:15px;font-weight:800;margin-top:4px;overflow-wrap:anywhere;line-height:1.25}
.protocol-note{padding:0 17px 12px;color:#6c8075;font-size:10px}
.execution-card{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:16px}
.execution-description{display:flex;align-items:center;gap:12px;min-width:0}.execution-description strong{font-size:16px;font-weight:800}
.execution-description p{font-size:11px;color:var(--muted);margin:4px 0 0}
.execution-action{flex:0 0 300px;min-width:0}
.button{min-height:45px;border:0;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;gap:12px;padding:11px 18px;background:linear-gradient(115deg,#0e5140,#0c4838);color:#fff;font-size:13px;font-weight:800;transition:transform .18s,box-shadow .18s,background .18s;box-shadow:0 4px 15px #0b49331c}
.button:hover:not(:disabled){background:var(--forest-deep);transform:translateY(-1px);box-shadow:0 8px 20px #0b493329}
.button:disabled{background:#e8eeea;color:#6f8076;border:1px solid #cfded4;box-shadow:none;cursor:not-allowed}
.button.full{width:100%}.button-details{display:flex;flex-direction:column;gap:2px;text-align:left;line-height:1.3}.button-details small{font-size:10px;font-weight:500;opacity:.86}
.execution-hint{margin:8px 0 0;color:#a04a42;font-size:11px}
.result-card{padding:17px 16px;margin-top:16px}
.result-title-section{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.result-subline{font-size:11px;color:var(--muted);margin:5px 0 0}
.result-section-title{font-size:15px;font-weight:800;margin:22px 0 5px}.result-section-copy{font-size:11px;color:var(--muted);margin:0 0 13px}
.trace-flow{display:grid;grid-template-columns:minmax(0,1fr) 20px minmax(0,1fr) 20px minmax(0,1fr) 20px minmax(0,1fr);gap:7px;align-items:center}
.trace-node{min-width:0;min-height:122px;padding:13px 12px;border:1px solid var(--line);border-radius:12px;background:#fff}
.trace-node.final{background:#f0f8f3;border-color:#b9dccc}.trace-node .trace-value{font-size:27px;line-height:1.1;font-weight:850;letter-spacing:-.03em}.trace-node.final .trace-value{color:#0b754a}
.trace-label{font-size:11px;font-weight:800;margin-top:6px}.trace-desc{font-size:10px;color:var(--muted);margin-top:5px;line-height:1.4}.trace-arrow{color:#87a59a;text-align:center;font-size:22px}
.evidence-groups{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px}
.evidence-group{border:1px solid var(--line);border-radius:12px;padding:13px;background:#fff;min-width:0}.evidence-group h4{font-size:12px;color:#4b6659;margin:0 0 11px;letter-spacing:.02em}
.evidence-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.evidence-mini{min-width:0;padding:10px;border:1px solid #e4ede6;border-radius:9px;background:#fafcfb}
.evidence-mini strong{display:block;font-size:22px;line-height:1.2;font-weight:800}.evidence-mini span{display:block;font-size:10px;color:#5a7466;margin-top:4px;line-height:1.4}
.evidence-mini.blue strong{color:var(--blue)}.evidence-mini.purple strong{color:var(--purple)}.evidence-mini.amber strong{color:#a97116}.evidence-mini.green strong{color:#14734d}
.evidence-group .group-note{font-size:10px;color:var(--muted);margin:10px 1px 0}
.method-note{border:1px solid #dbe9e0;background:#f6faf7;border-radius:10px;padding:12px 14px;margin-top:15px;font-size:11px;line-height:1.6;color:#476b56}
.interpretation-note{display:flex;gap:10px;align-items:start;margin-top:10px;border-radius:10px;background:#fff7e9;border:1px solid #eadaaa;color:#7b571b;padding:12px 14px;font-size:11px;line-height:1.6}
.interpretation-note .ico{margin-top:1px}
.result-actions{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:15px}
.artifact-button{min-height:83px;min-width:0;display:flex;align-items:center;gap:11px;text-decoration:none;padding:12px;border:1px solid var(--line);border-radius:11px;background:#fff;transition:.18s}
.artifact-button:hover{transform:translateY(-1px);border-color:#adcabc;box-shadow:0 5px 15px #153c2720}
.artifact-button .action-symbol{flex:none;display:grid;place-items:center;width:37px;height:37px;background:#edf6f2;color:var(--forest-mid);border-radius:11px}
.action-copy{min-width:0;flex:1}.action-copy strong{display:block;font-size:11px;font-weight:800;line-height:1.35}.action-copy span{display:block;margin-top:5px;color:#647b6e;font-size:10px;line-height:1.4}.artifact-button .action-chevron{width:15px;height:15px}
.artifact-button.primary{background:linear-gradient(120deg,#0d4836,#0a332a);border-color:#0d4836;color:white}
.artifact-button.primary .action-symbol{background:#ffffff1b;color:white}.artifact-button.primary .action-copy span{color:#d5e9dd}
.result-meta{display:flex;gap:6px 9px;flex-wrap:wrap;align-items:center;margin-top:15px;color:#64766d;font-size:10px;border-top:1px solid #e4ece7;padding-top:13px;overflow-wrap:anywhere}
.result-meta strong{font-weight:750;color:#42574a}.uuid-value{word-break:break-all}.copy-uuid{border:1px solid #d6e5da;color:#326149;background:#f6faf7;border-radius:6px;display:inline-flex;align-items:center;gap:4px;padding:4px 7px;font-size:10px}
.empty-card{display:flex;gap:12px;align-items:flex-start;margin-bottom:16px;padding:19px;background:#fff;border:1px dashed #cbdcd1;border-radius:13px;color:var(--muted);font-size:12px}.empty-card strong{color:var(--text);font-size:14px}
.alert{margin:0 0 14px;border:1px solid;padding:12px 15px;border-radius:11px;font-size:12px;line-height:1.55}.alert-success{border-color:#b9e0c6;background:#ecf8ef;color:#186542}.alert-error{border-color:#ebc8c8;background:#fff3f3;color:#a32d2d}.alert-warning{border-color:#ecd9a6;background:#fff9e9;color:#885d15}
.alert strong{font-weight:800}
/* LAB: diseño consistente manteniendo todos los identificadores y formularios existentes */
.lab-hero{border-radius:18px;margin-bottom:16px;min-height:153px}
.scope-note{display:flex;align-items:start;gap:8px;padding:11px 13px;border:1px solid #ddeae2;border-radius:10px;background:#f8fbf9;color:#526a5b;font-size:11px;margin-top:16px;max-width:880px}
.analysis-grid{display:grid;grid-template-columns:330px minmax(0,1fr);gap:16px;align-items:start}
.settings{padding:20px;position:sticky;top:92px}.section-title{font-size:17px;margin:0;font-weight:800}.section-description{font-size:12px;color:var(--muted);margin:7px 0 20px;line-height:1.6}.section-eyebrow{font-size:10px;color:#167046;letter-spacing:.08em;font-weight:800;text-transform:uppercase;margin-bottom:6px}
.field{margin-bottom:17px}.field>label,.model-choice-label{font-size:12px;font-weight:750;display:block;margin-bottom:8px}
input,select{max-width:100%}.field input:not([type="file"]):not([type="checkbox"]),.field select{width:100%;background:#fff;color:var(--text);border:1px solid #cfdfd5;border-radius:9px;padding:11px 12px;font-size:13px}
.helper{font-size:11px;color:var(--muted);line-height:1.55;margin-top:6px}
.upload{display:block;cursor:pointer;text-align:center;padding:20px 15px;border:1px dashed #bacfc0;border-radius:11px;background:#f8fcf9}.upload:hover{border-color:#4b9d79}.upload input{display:none}.upload-symbol{display:grid;place-items:center;background:#e3f3e9;color:var(--forest-mid);border-radius:12px;width:43px;height:43px;margin:auto auto 9px}.upload-symbol svg{width:22px;height:22px}.upload-name{font-size:12px;font-weight:800;overflow-wrap:anywhere}.upload-small{font-size:10px;margin-top:5px;color:var(--muted)}
.model-select-native{position:absolute!important;opacity:0!important;width:1px!important;height:1px!important;pointer-events:none!important}.model-cards{display:grid;gap:8px}
.model-card{width:100%;text-align:left;border:1px solid #dce8df;border-radius:11px;padding:12px;background:#fff}.model-card:hover{border-color:#9ac4ab;background:#fcfefd}.model-card.is-active{border-color:#4e9775;box-shadow:0 0 0 2px #b6dac827;background:#f4faf6}
.model-card-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.model-card-name{font-size:12px;font-weight:800}.model-card-code{font-size:10px;color:#4c6d5b;background:#e9f2eb;border-radius:5px;padding:2px 5px}.model-card-desc{font-size:11px;color:var(--muted);line-height:1.45;margin-top:5px}.model-card-meta{display:flex;gap:5px;flex-wrap:wrap;margin-top:7px}.mini-chip{font-size:9px;background:#eaf3ed;border-radius:99px;padding:3px 7px;color:#426d56;font-weight:700}
.compare-options{display:none;padding:13px;border:1px solid #e0d3ed;background:#fbf8ff;border-radius:11px;margin-bottom:16px}.compare-options.visible{display:block}.compare-title{font-size:12px;font-weight:800;color:#754ab6;margin-bottom:12px}
.science-note{font-size:11px;line-height:1.6;color:#526e60;margin-top:16px;background:#f5faf7;border-radius:11px;border:1px solid #dae9de;padding:13px}.science-note-title{font-weight:800;margin-bottom:7px;color:var(--forest-mid)}
.workspace{overflow:hidden}.workspace-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--line);background:#fafdfa}.workspace-eyebrow{font-size:10px;color:#22805a;font-weight:800}.workspace-title{font-size:15px;font-weight:800;margin-top:3px}.workspace-description{font-size:11px;color:var(--muted);margin-top:3px}
.viewer{min-height:480px;display:flex;justify-content:center;align-items:center;overflow:hidden;background:#101d19}.viewer-placeholder{max-width:420px;text-align:center;color:#bed0c5;padding:35px 25px;font-size:12px}.viewer-empty-symbol{margin:0 auto 15px;width:60px;height:60px;display:grid;place-items:center;border:1px solid #ffffff35;border-radius:15px}.viewer-empty-symbol svg{width:30px;height:30px}.image-wrapper{display:inline-block;position:relative;max-width:100%;line-height:0}.image-wrapper img{display:block;width:auto;height:auto;max-width:100%;max-height:740px}.image-wrapper canvas{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none}
.viewer-controls{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.viewer-controls label{display:inline-flex;gap:5px;align-items:center;font-size:10px;color:#587061;background:#fff;border:1px solid var(--line);border-radius:8px;padding:5px 7px}.viewer-controls input{width:auto}
.legend{display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:11px 17px;background:#f9fcfa;border-top:1px solid var(--line);font-size:11px}.legend-item{display:inline-flex;align-items:center;gap:5px}.legend-title{font-size:10px;font-weight:800;color:var(--muted)}.legend-color{width:11px;height:11px;border-radius:3px}.legend-yolo{background:var(--blue)}.legend-mask{background:#19a66b}.legend-match{background:var(--purple)}
.metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;padding:14px;background:#f9fcfa;border-top:1px solid var(--line)}.metric{background:#fff;border:1px solid var(--line);border-radius:10px;padding:12px;min-width:0}.metric-name{font-size:10px;color:var(--muted)}.metric-value{font-size:20px;font-weight:800;overflow-wrap:anywhere}.metric-yolo{color:var(--blue)}.metric-mask{color:#16855a}.metric-match{color:var(--purple)}
.detail-card{margin-top:16px;padding:20px}.table-wrapper{overflow-x:auto;max-width:100%}table{width:100%;border-collapse:collapse;font-size:12px;text-align:left}th,td{padding:10px 12px;border-bottom:1px solid var(--line)}th{background:#f9fcfa;color:var(--muted);font-size:10px;font-weight:800;text-transform:uppercase;white-space:nowrap}.badge{display:inline-block;border-radius:7px;font-weight:800;padding:3px 7px;font-size:10px}.badge-match{color:var(--purple);background:var(--purple-soft)}.badge-yolo{color:var(--blue);background:var(--blue-soft)}.badge-mask{color:#118855;background:#e3f5eb}
.mobile-nav{display:none}
@media(max-width:1210px){.topbar-inner{gap:14px;padding:10px 20px}.brand{min-width:0}.brand-subtitle{display:none}.system-status-copy{display:none}.nav-item{padding:10px}.spatial-item{gap:8px}.stat-orb{width:38px;height:38px}}
@media(max-width:1050px){.topbar-inner{min-height:67px}.brand-name{font-size:18px}.hero-api{position:relative;display:table;right:auto;top:auto;margin-top:13px}.page-hero{min-height:0}.hero-media{width:60%}.spatial-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.protocol-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.protocol-item:nth-child(4n){border-right:1px solid #e4ede8}.protocol-item:nth-child(2n){border-right:0}.protocol-item:nth-child(n+5){border-bottom:1px solid #e4ede8}.protocol-item:nth-child(n+7){border-bottom:0}.evidence-groups{grid-template-columns:1fr 1fr}.evidence-group:last-child{grid-column:1/-1}.result-actions{grid-template-columns:repeat(2,minmax(0,1fr))}.analysis-grid{grid-template-columns:290px minmax(0,1fr)}.metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:780px){main{padding:16px 14px 100px}.topbar-inner{padding:10px 15px}.topbar .nav-main{display:none}.brand-logo{width:42px;height:42px}.system-status{margin-left:auto;min-width:0}.api-status{padding:8px 9px;font-size:10px}.page-hero{padding:23px 18px;border-radius:15px 15px 0 0}.page-hero h1{font-size:27px}.hero-media{opacity:.5}.workspace-shell{padding:0 11px 11px}.mosaic-selection{flex-wrap:wrap}.mosaic-picker{width:100%}.select-control{width:100%}.execution-card{align-items:stretch;flex-direction:column}.execution-action{flex:auto}.trace-flow{grid-template-columns:1fr;gap:7px}.trace-arrow{transform:rotate(90deg);height:16px;line-height:16px}.trace-node{min-height:0}.analysis-grid{grid-template-columns:1fr}.settings{position:static}.viewer{min-height:390px}.mobile-nav{position:fixed;bottom:0;left:0;right:0;z-index:42;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));background:#fff;border-top:1px solid #d7e6de;box-shadow:0 -4px 20px #102d2112;padding:6px 10px calc(6px + env(safe-area-inset-bottom))}.mobile-nav .nav-item{display:flex;flex-direction:column;justify-content:center;gap:2px;font-size:10px;color:#637b6b;background:none;padding:6px 8px}.mobile-nav .nav-item.is-active{color:#0d6b43;background:#eaf5ee;border-color:transparent}.mobile-nav .nav-item .ico{width:20px;height:20px}}
@media(max-width:560px){body{font-size:13px}.brand-name{font-size:17px}.topbar-inner{gap:7px}.api-status{font-size:0;padding:10px}.api-status .api-dot{width:10px;height:10px}.api-status:after{content:"Motor de IA";font-size:10px}.hero-kicker{font-size:9px}.page-hero h1{font-size:25px}.page-hero p{font-size:12px}.hero-media{display:none}.mosaic-card{padding:13px}.section-heading h2,.section-heading h3{font-size:14px}.mosaic-thumbnail{width:69px;height:69px}.mosaic-selection{gap:10px}.mosaic-details{flex-basis:calc(100% - 85px)}.mosaic-name{font-size:12px}.mosaic-project,.mosaic-meta{font-size:10px}.spatial-grid{gap:7px}.spatial-item{padding:9px;gap:6px;min-height:72px}.stat-orb{width:34px;height:34px}.stat-orb .ico{width:19px;height:19px}.spatial-label{font-size:10px}.spatial-value{font-size:12px}.protocol-head{padding:13px}.protocol-summary strong{font-size:13px}.protocol-summary span{font-size:10px}.lock-tag{display:none}.protocol-chevron{display:block}.protocol-card[open] .protocol-chevron{transform:rotate(180deg)}.protocol-grid{grid-template-columns:repeat(2,minmax(0,1fr));margin:0 6px 6px}.protocol-item{padding:10px 8px;gap:5px;min-height:62px}.protocol-ico{display:none}.protocol-value{font-size:13px}.protocol-name{font-size:9px}.execution-description strong{font-size:14px}.result-card{padding:14px 12px}.result-section-title{font-size:14px}.evidence-groups{grid-template-columns:1fr}.evidence-group:last-child{grid-column:auto}.result-actions{grid-template-columns:1fr}.artifact-button{min-height:68px}.viewer-controls{gap:4px}.viewer-controls label{font-size:9px}.metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.workspace-header{flex-wrap:wrap}.viewer{min-height:290px}.interpretation-note{font-size:10px}}
@media(max-width:365px){.brand-logo{width:38px;height:38px}.brand-name{font-size:15px}.api-status:after{content:"IA"}.spatial-item{display:block}.spatial-item .stat-orb{margin-bottom:5px}.spatial-value{font-size:11px}.protocol-head{gap:5px}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important;scroll-behavior:auto!important}}

  </style>
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
 <symbol id="i-tree" viewBox="0 0 24 24"><path d="m12 2-5 7h3l-5 7h5v5h4v-5h5l-5-7h3L12 2Z"/><path d="M3 21h18"/></symbol>
 <symbol id="i-image" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.4"/><path d="m3 17 5-5 4 4 3-3 6 6"/></symbol>
 <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></symbol>
 <symbol id="i-map" viewBox="0 0 24 24"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3zM9 3v15M15 6v15"/></symbol>
 <symbol id="i-folder" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10H3z"/></symbol>
 <symbol id="i-layers" viewBox="0 0 24 24"><path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/></symbol>
 <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c5 5 5 13 0 18M12 3c-5 5-5 13 0 18"/></symbol>
 <symbol id="i-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></symbol>
 <symbol id="i-lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></symbol>
 <symbol id="i-chev" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
 <symbol id="i-right" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
 <symbol id="i-play" viewBox="0 0 24 24"><path d="m8 4 12 8-12 8z"/></symbol>
 <symbol id="i-cpu" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M10 10h4v4h-4zM9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/></symbol>
 <symbol id="i-network" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="19" r="2.5"/><circle cx="19" cy="19" r="2.5"/><path d="m11 7-5 9m7-9 5 9M7.5 19h9"/></symbol>
 <symbol id="i-square" viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2"/><path d="M5 9V3M9 5H3M19 15v6M15 19h6"/></symbol>
 <symbol id="i-overlap" viewBox="0 0 24 24"><circle cx="9" cy="12" r="7"/><circle cx="15" cy="12" r="7"/></symbol>
 <symbol id="i-bars" viewBox="0 0 24 24"><path d="M5 20v-7h3v7M11 20V4h3v16M17 20v-11h3v11"/></symbol>
 <symbol id="i-database" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 4 18 4 18 0V5M3 12c0 4 18 4 18 0"/></symbol>
 <symbol id="i-file" viewBox="0 0 24 24"><path d="M6 2h8l5 5v15H6zM14 2v5h5M9 12h7M9 16h7"/></symbol>
 <symbol id="i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></symbol>
 <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 3 2 21h20zM12 9v5M12 18h.01"/></symbol>
 <symbol id="i-copy" viewBox="0 0 24 24"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h3"/></symbol>
 <symbol id="i-check" viewBox="0 0 24 24"><path d="m4 12 5 5 11-11"/></symbol>
 <symbol id="i-refresh" viewBox="0 0 24 24"><path d="M20 9a8 8 0 0 0-14-3L3 9m0-6v6h6M4 15a8 8 0 0 0 14 3l3-3m0 6v-6h-6"/></symbol>
 <symbol id="i-upload" viewBox="0 0 24 24"><path d="M12 16V3m0 0L7 8m5-5 5 5M4 14v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></symbol>
</svg>
@php
  $availableMosaics = $availableMosaics ?? collect();
  $selectedMosaicUuid = $selectedMosaicUuid ?? request()->query('mosaic');
  $selectedMosaic = $selectedMosaic ?? null;
  if (!$selectedMosaic && $selectedMosaicUuid) {
    $selectedMosaic = $availableMosaics->firstWhere('uuid', $selectedMosaicUuid);
  }
  if (!$selectedMosaic && $availableMosaics->isNotEmpty()) {
    $selectedMosaic = $availableMosaics->first();
    $selectedMosaicUuid = $selectedMosaic->uuid;
  }
  $isCompare = $analysisMode === 'both';
  $singleCrowns = !$isCompare ? ($result['crowns'] ?? []) : [];
  $yoloCrowns = $isCompare ? ($result['yolo']['crowns'] ?? []) : [];
  $maskCrowns = $isCompare ? ($result['maskrcnn']['crowns'] ?? []) : [];
  $matches = $isCompare ? ($result['matches'] ?? []) : [];
  $summary = $isCompare ? ($result['summary'] ?? []) : [];
  $initialWorkspaceTab = (!$result && $wallToWallAnalysis) || request()->has('mosaic') ? 'wall' : 'lab';
  $apiOnline = ($apiStatus['status'] ?? 'offline') === 'ok';
@endphp
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand" aria-label="UAV Forest AI">
      <div class="brand-logo"><svg class="ico"><use href="#i-tree"/></svg></div>
      <div><div class="brand-name">UAV Forest AI</div><div class="brand-subtitle">Inteligencia geoespacial para análisis forestal</div></div>
    </div>
    <nav class="nav-main" aria-label="Modos de trabajo">
      <button type="button" class="nav-item {{ $initialWorkspaceTab === 'lab' ? 'is-active' : '' }}" data-workspace-tab="lab" aria-controls="workspace-lab" aria-pressed="{{ $initialWorkspaceTab === 'lab' ? 'true' : 'false' }}"><svg class="ico ico-sm"><use href="#i-cpu"/></svg>Laboratorio</button>
      <button type="button" class="nav-item {{ $initialWorkspaceTab === 'wall' ? 'is-active' : '' }}" data-workspace-tab="wall" aria-controls="workspace-wall" aria-pressed="{{ $initialWorkspaceTab === 'wall' ? 'true' : 'false' }}"><svg class="ico ico-sm"><use href="#i-image"/></svg>Ortomosaico</button>
    </nav>
    <div class="system-status" aria-label="Estado del motor de inferencia">
      <div class="system-status-copy"><span class="system-status-label">Motor de inferencia</span><span class="system-status-detail">Servicio científico de IA</span></div>
      <div class="api-status" role="status"><span class="api-dot {{ $apiOnline ? 'online' : 'offline' }}"></span>{{ $apiOnline ? 'Sistema disponible' : 'Sistema no disponible' }}</div>
    </div>
  </div>
</header>
<main>
  @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-error" role="alert"><strong>Revisa los datos ingresados.</strong>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  @if(session('error'))<div class="alert alert-error" role="alert"><strong>Error durante el análisis.</strong> {{ session('error') }}</div>@endif
<div id="workspace-lab" class="analysis-mode-panel {{ $initialWorkspaceTab === 'lab' ? 'is-active' : '' }}">
  <div class="page-hero lab-hero">
    <div class="hero-media" style="background-image:url('{{ asset('images/uav-forest-hero.webp') }}')" aria-hidden="true"></div>
    <div class="hero-copy"><span class="hero-kicker">Experimento controlado</span>
      <h1>Laboratorio de inferencia</h1>
      <p>Inspecciona candidatos de copa y compara la evidencia espacial de YOLO-Seg y Mask R-CNN sobre imágenes UAV individuales.</p>
    </div>
  </div>
  <div class="scope-note"><svg class="ico ico-sm"><use href="#i-info"/></svg><div><strong>Alcance científico:</strong> el score del modelo no es una probabilidad calibrada de acierto y el acuerdo espacial entre modelos no sustituye la validación frente a verdad de campo.</div></div>
  <div style="height:16px"></div>


<div class="analysis-grid">


    {{-- ==========================================================
         CONFIGURACIÓN
         ========================================================== --}}

    <aside class="card settings">


        <div class="section-eyebrow">Experimento controlado</div>

        <h2 class="section-title">Configurar inferencia</h2>

        <p class="section-description">
            Selecciona una imagen UAV y el modelo que deseas inspeccionar.
            Los scores y parámetros mostrados deben interpretarse dentro del protocolo experimental.
        </p>


        <form
            id="analysisForm"
            method="POST"
            action="{{ route('analysis.analyze') }}"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- Imagen --}}

            <div class="field">

                <label>
                    Imagen UAV
                </label>


                <label class="upload">

                    <input
                        id="imageInput"
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >


                    <div class="upload-symbol" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 16V4M12 4L8 8M12 4L16 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 14V18C5 19.1046 5.89543 20 7 20H17C18.1046 20 19 19.1046 19 18V14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <div id="uploadName" class="upload-name">Seleccionar imagen UAV</div>


                    <div class="upload-small">

                        JPG · PNG · WEBP
                        <br>
                        máximo 10 MB

                    </div>

                </label>

            </div>


            {{-- Modelo --}}

            <div class="field">

                <label class="model-choice-label">Modelo / estrategia</label>

                <select id="modelSelect" name="model" required class="model-select-native">
                    <option value="yolo" {{ old('model', $analysisMode) === 'yolo' ? 'selected' : '' }}>YOLO-Seg E1.1</option>
                    <option value="maskrcnn" {{ old('model', $analysisMode) === 'maskrcnn' ? 'selected' : '' }}>Mask R-CNN M1.0</option>
                    <option value="both" {{ old('model', $analysisMode) === 'both' ? 'selected' : '' }}>Comparar ambos</option>
                </select>

                <div class="model-cards" id="modelCards">
                    <button type="button" class="model-card" data-model-value="yolo">
                        <div class="model-card-top">
                            <span class="model-card-name">YOLO-Seg</span>
                            <span class="model-card-code">E1.1</span>
                        </div>
                        <div class="model-card-desc">Segmentación rápida de candidatos de copa. Modelo corroborador y fallback dentro del pipeline wall-to-wall.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">Score base 0.25</span>
                            <span class="mini-chip">Segmentación</span>
                        </div>
                    </button>

                    <button type="button" class="model-card" data-model-value="maskrcnn">
                        <div class="model-card-top">
                            <span class="model-card-name">Mask R-CNN</span>
                            <span class="model-card-code">M1.0</span>
                        </div>
                        <div class="model-card-desc">Segmentación de instancias utilizada como geometría primaria provisional cuando existe candidato asociado.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">Score base 0.40</span>
                            <span class="mini-chip">Geometría primaria</span>
                        </div>
                    </button>

                    <button type="button" class="model-card" data-model-value="both">
                        <div class="model-card-top">
                            <span class="model-card-name">Comparación intermodelo</span>
                            <span class="model-card-code">YOLO + MRCNN</span>
                        </div>
                        <div class="model-card-desc">Ejecuta ambos modelos sobre la misma imagen para inspeccionar concordancia espacial y evidencia unilateral.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">IoU de máscaras</span>
                            <span class="mini-chip">QA experimental</span>
                        </div>
                    </button>
                </div>

                <div id="modelHelp" class="helper"></div>

            </div>


            {{-- Single threshold --}}

            <div
                id="singleThresholdGroup"
                class="field"
            >

                <label>
                    Score mínimo del modelo
                </label>


                <input
                    id="thresholdInput"
                    type="number"
                    name="threshold"
                    min="0.01"
                    max="0.99"
                    step="0.01"
                    value="{{ old('threshold', 0.25) }}"
                >


                <div
                    id="thresholdHelp"
                    class="helper"
                ></div>

            </div>


            {{-- Comparison settings --}}

            <div
                id="compareOptions"
                class="compare-options"
            >

                <div class="compare-title">
                    Parámetros de comparación
                </div>


                <div class="field">

                    <label>
                        Score mínimo YOLO
                    </label>

                    <input
                        type="number"
                        name="yolo_threshold"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'yolo_threshold',
                                0.25
                            )
                        }}"
                    >

                    <div class="helper">
                        Threshold seleccionado
                        mediante VALIDATION.
                    </div>

                </div>


                <div class="field">

                    <label>
                        Score mínimo Mask R-CNN
                    </label>

                    <input
                        type="number"
                        name="maskrcnn_threshold"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'maskrcnn_threshold',
                                0.40
                            )
                        }}"
                    >

                    <div class="helper">
                        Threshold seleccionado
                        mediante VALIDATION.
                    </div>

                </div>


                <div class="field">

                    <label>
                        IoU mínimo de coincidencia
                    </label>

                    <input
                        type="number"
                        name="matching_iou"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'matching_iou',
                                0.50
                            )
                        }}"
                    >

                    <div class="helper">

                        Define el acuerdo espacial mínimo
                        para emparejar una predicción YOLO
                        con una predicción Mask R-CNN.

                    </div>

                </div>

            </div>


            <button
                id="analyzeButton"
                class="button"
                type="submit"
            >

                <span id="buttonText">
                    Analizar imagen
                </span>

            </button>

        </form>


        <div class="science-note">
            <div class="science-note-title">
                <span>ⓘ</span>
                Interpretación metodológica
            </div>
            El <strong>score del modelo</strong> es una salida interna de confianza y no debe
            interpretarse como probabilidad de que una detección sea correcta.
            <br><br>
            En comparación intermodelo, el <strong>IoU describe acuerdo espacial entre máscaras</strong>;
            no mide exactitud frente a verdad de campo.
        </div>

    </aside>



    {{-- ==========================================================
         WORKSPACE
         ========================================================== --}}

    <section>


        <div class="card workspace">


            <div class="workspace-header">


                <div>

                    <div class="workspace-eyebrow">Inspección visual</div>
                    <div class="workspace-title">Visor de inferencia</div>
                    <div class="workspace-description">Imagen UAV + geometrías propuestas por los modelos</div>

                </div>


                @if($result)


                    <div class="viewer-controls">


                        @if($isCompare)

                            <label>

                                <input
                                    id="showYolo"
                                    type="checkbox"
                                    checked
                                >

                                YOLO

                            </label>


                            <label>

                                <input
                                    id="showMask"
                                    type="checkbox"
                                    checked
                                >

                                Mask R-CNN

                            </label>


                            <label>

                                <input
                                    id="showMatches"
                                    type="checkbox"
                                    checked
                                >

                                Coincidencias

                            </label>

                        @else

                            <label>

                                <input
                                    id="showFill"
                                    type="checkbox"
                                    checked
                                >

                                Máscara

                            </label>

                        @endif


                        <label>

                            <input
                                id="showLabels"
                                type="checkbox"
                                checked
                            >

                            Etiquetas

                        </label>


                    </div>


                @endif

            </div>



            <div class="viewer">


                @if(
                    $result
                    &&
                    $imageUrl
                )


                    <div class="image-wrapper">

                        <img
                            id="uavImage"
                            src="{{ $imageUrl }}"
                            alt="Imagen UAV analizada"
                        >

                        <canvas
                            id="maskCanvas"
                        ></canvas>

                    </div>


                @else


                    <div
                        id="viewerPlaceholder"
                        class="viewer-placeholder"
                    >

                        <div class="viewer-empty-symbol" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 17L8.5 12L11 14.5L15 9.5L20 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <strong style="display:block;color:#fff;margin-bottom:6px;">Área de inspección</strong>
                        Selecciona una imagen UAV para previsualizarla y ejecutar una inferencia.

                    </div>


                    <img
                        id="previewImage"
                        style="
                            display:none;
                            max-width:100%;
                            max-height:740px;
                        "
                        alt="Vista previa"
                    >


                @endif


            </div>



            @if($result)


                @if($isCompare)


                    <div class="legend">

                        <span class="legend-title">Leyenda</span>

                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-yolo
                                "
                            ></span>

                            YOLO-Seg

                        </div>


                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-mask
                                "
                            ></span>

                            Mask R-CNN

                        </div>


                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-match
                                "
                            ></span>

                            Coincidencia espacial

                        </div>

                    </div>


                    <div class="metrics">


                        <div class="metric">

                            <div class="metric-name">
                                YOLO
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-yolo
                                "
                            >
                                {{
                                    $summary[
                                        'yolo_detections'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Mask R-CNN
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-mask
                                "
                            >
                                {{
                                    $summary[
                                        'maskrcnn_detections'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Coincidencias
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-match
                                "
                            >
                                {{
                                    $summary[
                                        'matches'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Solo YOLO
                            </div>

                            <div class="metric-value">
                                {{
                                    $summary[
                                        'only_yolo'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Solo Mask R-CNN
                            </div>

                            <div class="metric-value">
                                {{
                                    $summary[
                                        'only_maskrcnn'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                    </div>


                @else


                    <div class="metrics">


                        <div class="metric">

                            <div class="metric-name">
                                Modelo
                            </div>

                            <div
                                class="metric-value"
                                style="font-size:14px;"
                            >
                                {{
                                    $result[
                                        'model'
                                    ] ?? '—'
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Threshold
                            </div>

                            <div class="metric-value">

                                {{
                                    number_format(
                                        $result[
                                            'threshold'
                                        ] ?? 0,
                                        2
                                    )
                                }}

                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Copas candidatas
                            </div>

                            <div class="metric-value">

                                {{
                                    $result[
                                        'detections'
                                    ] ?? 0
                                }}

                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Resolución
                            </div>

                            <div
                                class="metric-value"
                                style="font-size:14px;"
                            >

                                {{
                                    $result[
                                        'width'
                                    ] ?? '?'
                                }}

                                ×

                                {{
                                    $result[
                                        'height'
                                    ] ?? '?'
                                }}

                            </div>

                        </div>

                    </div>


                @endif


            @endif


        </div>



        {{-- ======================================================
             COMPARISON TABLE
             ====================================================== --}}

        @if(
            $result
            &&
            $isCompare
        )


            <div class="card detail-card">


                <h2 class="section-title">
                    Coincidencias entre modelos
                </h2>


                <p class="section-description">

                    Emparejamiento uno a uno realizado
                    mediante IoU de máscaras.

                </p>


                @if(count($matches) > 0)


                    <div class="table-wrapper">


                        <table>


                            <thead>

                                <tr>

                                    <th>
                                        Match
                                    </th>

                                    <th>
                                        YOLO
                                    </th>

                                    <th>
                                        Mask R-CNN
                                    </th>

                                    <th>
                                        IoU
                                    </th>

                                    <th>
                                        Score YOLO
                                    </th>

                                    <th>
                                        Score Mask
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                @foreach(
                                    $matches
                                    as $match
                                )


                                    <tr>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-match
                                                "
                                            >

                                                #{{ $match['id'] ?? '?' }}

                                            </span>

                                        </td>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-yolo
                                                "
                                            >

                                                #{{
                                                    $match[
                                                        'yolo_id'
                                                    ] ?? '?'
                                                }}

                                            </span>

                                        </td>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-mask
                                                "
                                            >

                                                #{{
                                                    $match[
                                                        'maskrcnn_id'
                                                    ] ?? '?'
                                                }}

                                            </span>

                                        </td>


                                        <td>

                                            <strong>

                                                {{
                                                    number_format(
                                                        $match[
                                                            'iou'
                                                        ] ?? 0,
                                                        3
                                                    )
                                                }}

                                            </strong>

                                        </td>


                                        <td>

                                            {{
                                                number_format(
                                                    $match[
                                                        'yolo_confidence'
                                                    ] ?? 0,
                                                    3
                                                )
                                            }}

                                        </td>


                                        <td>

                                            {{
                                                number_format(
                                                    $match[
                                                        'maskrcnn_confidence'
                                                    ] ?? 0,
                                                    3
                                                )
                                            }}

                                        </td>


                                    </tr>


                                @endforeach


                            </tbody>

                        </table>

                    </div>


                @else


                    <div class="alert alert-warning">

                        No se encontraron coincidencias
                        con el IoU mínimo seleccionado.

                    </div>


                @endif


            </div>


            <div class="card detail-card">


                <h2 class="section-title">
                    Configuración experimental
                </h2>


                <p class="section-description">
                    Parámetros utilizados en esta comparación.
                </p>


                <table>

                    <tbody>

                        <tr>

                            <td>
                                Score mínimo YOLO
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'yolo_threshold'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Score mínimo Mask R-CNN
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'maskrcnn_threshold'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Matching IoU
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'matching_iou'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Tiempo YOLO
                            </td>

                            <td>
                                {{
                                    $result[
                                        'yolo'
                                    ][
                                        'processing_seconds'
                                    ] ?? '—'
                                }}
                                s
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Tiempo Mask R-CNN
                            </td>

                            <td>
                                {{
                                    $result[
                                        'maskrcnn'
                                    ][
                                        'processing_seconds'
                                    ] ?? '—'
                                }}
                                s
                            </td>

                        </tr>

                    </tbody>

                </table>


            </div>


        @elseif(
            $result
            &&
            count($singleCrowns) > 0
        )


            <div class="card detail-card">


                <h2 class="section-title">
                    Copas candidatas
                </h2>


                <p class="section-description">

                    Instancias propuestas por
                    {{ $result['model'] ?? 'el modelo' }}.

                </p>


                <div class="table-wrapper">


                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Copa
                                </th>

                                <th>
                                    Score del modelo
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach(
                                $singleCrowns
                                as $crown
                            )

                                <tr>

                                    <td>
                                        #{{ $crown['id'] ?? '?' }}
                                    </td>

                                    <td>

                                        {{
                                            number_format(
                                                $crown[
                                                    'confidence'
                                                ] ?? 0,
                                                3
                                            )
                                        }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


            </div>


        @endif


    </section>


</div>


</div>


<section id="workspace-wall" class="analysis-mode-panel {{ $initialWorkspaceTab === 'wall' ? 'is-active' : '' }}" aria-label="Análisis de ortomosaico">
  <div class="page-hero">
    <div class="hero-media" style="background-image:url('{{ asset('images/uav-forest-hero.webp') }}')" aria-hidden="true"></div>
    <div class="hero-copy">
      <span class="hero-kicker">Procesamiento espacial integral</span>
      <h1>Análisis de ortomosaico</h1>
      <p>Procesa un ortomosaico completo mediante el pipeline wall-to-wall. La configuración científica permanece protegida y los resultados conservan su trazabilidad.</p>
    </div>
    <div class="hero-api {{ $apiOnline ? 'is-online' : 'is-offline' }}" role="status"><span class="api-dot {{ $apiOnline ? 'online' : 'offline' }}"></span>{{ $apiOnline ? 'Motor IA disponible' : 'Motor IA no disponible' }}</div>
  </div>
  <div class="workspace-shell">
    @if($availableMosaics->isNotEmpty() && $selectedMosaic)
      @php
        $mosaicStatus = strtolower((string) ($selectedMosaic->status ?? ''));
        $canRunWall = $apiOnline && $mosaicStatus === 'ready';
        $mosaicThumb = data_get($selectedMosaic, 'thumbnail_url') ?: data_get($selectedMosaic, 'preview_url');
        // Estos valores representan la referencia histórica de la vista V0.6G.
        // Si el controlador suministra scientificProtocol se usan sus valores reales.
        $activeProtocol = $scientificProtocol ?? [];
        $protocolFromBackend = is_array($activeProtocol) && !empty($activeProtocol);
        if (!$protocolFromBackend) $activeProtocol = [];
        $protocolEntries = [
          ['Score YOLO-Seg', data_get($activeProtocol, 'yolo_score', '0.25'), 'i-network'],
          ['Score Mask R-CNN', data_get($activeProtocol, 'maskrcnn_score', '0.40'), 'i-network'],
          ['Threshold máscara', data_get($activeProtocol, 'mask_threshold', '0.50'), 'i-folder'],
          ['Entrada modelo', data_get($activeProtocol, 'model_input_px', '1024 px'), 'i-square'],
          ['Normalización', data_get($activeProtocol, 'normalization', '2144 → 1024 px'), 'i-square'],
          ['Solape de salida', data_get($activeProtocol, 'output_overlap_px', '256 px'), 'i-layers'],
          ['IoU intramodelo', data_get($activeProtocol, 'intramodel_iou', '0.50'), 'i-overlap'],
          ['IoU intermodelo', data_get($activeProtocol, 'intermodel_iou', '0.50'), 'i-overlap'],
        ];
      @endphp
      <section class="card mosaic-card" aria-labelledby="mosaic-heading">
        <div class="section-heading">
          <div class="section-heading-left"><span class="icon-tile"><svg class="ico"><use href="#i-image"/></svg></span><h2 id="mosaic-heading">Ortomosaico listo para análisis</h2></div>
        </div>
        <div class="mosaic-selection">
          <div class="mosaic-thumbnail" aria-label="Vista previa del ortomosaico">
            @if($mosaicThumb)
              <img src="{{ $mosaicThumb }}" alt="Miniatura del ortomosaico seleccionado" loading="lazy">
            @else
              <svg class="ico ico-lg" aria-hidden="true"><use href="#i-image"/></svg>
            @endif
          </div>
          <div class="mosaic-details">
            <div class="mosaic-name">{{ $selectedMosaic->original_name }}</div>
            <div class="mosaic-project">{{ $selectedMosaic->project->name ?? 'Proyecto no especificado' }}</div>
            <div class="mosaic-meta"><span>{{ number_format($selectedMosaic->width) }} × {{ number_format($selectedMosaic->height) }} px</span><span>·</span><span>GSD {{ $selectedMosaic->gsd_cm ? number_format((float) $selectedMosaic->gsd_cm, 4) . ' cm/px' : 'no disponible' }}</span></div>
          </div>
          <form method="GET" action="{{ route('analysis.index') }}" class="mosaic-picker">
            <label class="sr-only" for="wallToWallMosaic">Cambiar ortomosaico</label>
            <div class="select-control">
              <span class="select-face" aria-hidden="true"><svg class="ico ico-sm"><use href="#i-folder"/></svg>Cambiar ortomosaico<svg class="ico ico-sm"><use href="#i-chev"/></svg></span>
              <select id="wallToWallMosaic" name="mosaic" aria-label="Cambiar ortomosaico" onchange="this.form.submit()">
                @foreach($availableMosaics as $mosaic)
                  <option value="{{ $mosaic->uuid }}" {{ $selectedMosaicUuid === $mosaic->uuid ? 'selected' : '' }}>{{ $mosaic->original_name }}@if($mosaic->project) · {{ $mosaic->project->name }}@endif · {{ number_format($mosaic->width) }} × {{ number_format($mosaic->height) }} px</option>
                @endforeach
              </select>
            </div>
          </form>
        </div>
        <div class="help-inline"><svg class="ico ico-sm"><use href="#i-info"/></svg><span>Al cambiar el ortomosaico también se actualiza el último análisis wall-to-wall que se muestra.</span></div>
        <div class="spatial-grid" aria-label="Propiedades espaciales del archivo">
          <div class="spatial-item"><span class="stat-orb"><svg class="ico"><use href="#i-image"/></svg></span><div><div class="spatial-label">Dimensiones</div><div class="spatial-value">{{ number_format($selectedMosaic->width) }} × {{ number_format($selectedMosaic->height) }} px</div></div></div>
          <div class="spatial-item"><span class="stat-orb green"><svg class="ico"><use href="#i-target"/></svg></span><div><div class="spatial-label">GSD nativo</div><div class="spatial-value">{{ $selectedMosaic->gsd_cm ? number_format((float) $selectedMosaic->gsd_cm, 4) . ' cm/px' : '—' }}</div></div></div>
          <div class="spatial-item"><span class="stat-orb"><svg class="ico"><use href="#i-globe"/></svg></span><div><div class="spatial-label">CRS</div><div class="spatial-value">{{ $selectedMosaic->crs ?? '—' }}</div></div></div>
          <div class="spatial-item"><span class="stat-orb purple"><svg class="ico"><use href="#i-layers"/></svg></span><div><div class="spatial-label">Estado del archivo</div><span class="status-tag {{ $mosaicStatus === 'ready' ? '' : 'pending' }}">{{ strtoupper($selectedMosaic->status ?? '—') }}</span></div></div>
        </div>
      </section>
      <details id="scientificProtocol" class="card protocol-card" open>
        <summary class="protocol-head">
          <span class="protocol-summary"><span class="icon-tile dark"><svg class="ico"><use href="#i-lock"/></svg></span><span><strong>Protocolo científico activo</strong><span>Parámetros de solo lectura; no se envían desde el navegador.</span></span></span>
          <span class="lock-tag"><svg class="ico ico-sm"><use href="#i-lock"/></svg>Configuración protegida</span>
          <svg class="ico ico-sm protocol-chevron"><use href="#i-chev"/></svg>
        </summary>
        <div class="protocol-grid">
          @foreach($protocolEntries as $entry)
            <div class="protocol-item"><span class="protocol-ico"><svg class="ico"><use href="#{{ $entry[2] }}"/></svg></span><div><div class="protocol-name">{{ $entry[0] }}</div><div class="protocol-value">{{ $entry[1] }}</div></div></div>
          @endforeach
        </div>
        @unless($protocolFromBackend)
          <div class="protocol-note">Valores de referencia de la interfaz V0.6G. Debe verificarse su correspondencia con la configuración efectiva de FastAPI antes de interpretarlos como parámetros ejecutados.</div>
        @endunless
      </details>
      <section class="card execution-card" aria-labelledby="execution-heading">
        <div class="execution-description"><span class="icon-tile dark"><svg class="ico"><use href="#i-cpu"/></svg></span><div><strong id="execution-heading">Ejecutar pipeline wall-to-wall</strong><p>Procesa el ortomosaico mediante ventanas de inferencia, deduplicación y consolidación intermodelo.</p></div></div>
        <div class="execution-action">
          <form method="POST" action="{{ route('analysis.wall-to-wall.run', ['mosaic' => $selectedMosaic->uuid]) }}" id="wallToWallForm">
            @csrf
            <button id="wallToWallSubmit" class="button full" type="submit" @unless($canRunWall) disabled @endunless>
              <svg class="ico"><use href="{{ $canRunWall ? '#i-play' : '#i-lock' }}"/></svg>
              <span class="button-details"><span id="wallToWallButtonText">{{ $canRunWall ? 'Ejecutar análisis' : ($apiOnline ? 'Archivo no preparado' : 'FastAPI no disponible') }}</span><small>{{ $canRunWall ? 'Iniciar procesamiento completo' : 'La ejecución está deshabilitada' }}</small></span>
            </button>
          </form>
          @unless($canRunWall)<p class="execution-hint">{{ $apiOnline ? 'Selecciona un ortomosaico en estado READY.' : 'Activa el servicio FastAPI antes de ejecutar el análisis.' }}</p>@endunless
        </div>
      </section>
    @else
      <section class="empty-card" aria-live="polite"><svg class="ico ico-lg"><use href="#i-info"/></svg><div><strong>No hay ortomosaicos disponibles</strong><div>No se encontraron ortomosaicos con estado READY para este análisis.</div></div></section>
    @endif

    @if($wallToWallAnalysis)
      @php
        $wallSummary = $wallToWallAnalysis->summary ?? [];
        $wallModels = $wallToWallAnalysis->models ?? [];
        $wallParameters = $wallToWallAnalysis->parameters ?? [];
        $gpkgArtifact = $wallToWallAnalysis->artifacts->firstWhere('type', 'geopackage');
        $manifestArtifact = $wallToWallAnalysis->artifacts->firstWhere('type', 'manifest');
        $geojsonArtifact = $wallToWallAnalysis->artifacts->firstWhere('type', 'geojson');
        $structuralConflictObjects = ($wallSummary['split_merge_objects'] ?? 0) + ($wallSummary['containment_conflict_objects'] ?? 0);
        $analysisStatus = strtolower((string) ($wallToWallAnalysis->status ?? ''));
      @endphp
      <section class="card result-card" aria-labelledby="results-heading">
        <div class="result-title-section">
          <div class="section-heading-left"><span class="icon-tile"><svg class="ico"><use href="#i-bars"/></svg></span><div><h2 id="results-heading" style="font-size:16px;line-height:1.2;margin:0">Resultados del procesamiento</h2><p class="result-subline">Pipeline {{ $wallParameters['pipeline_version'] ?? 'V0.7E' }} · Método {{ $wallParameters['scientific_method_version'] ?? 'V0.6G' }} · Análisis persistente</p></div></div>
          <span class="status-tag {{ in_array($analysisStatus, ['completed','complete','success','finished'], true) ? '' : (in_array($analysisStatus, ['failed','error'], true) ? 'error' : 'pending') }}">{{ strtoupper($wallToWallAnalysis->status) }}</span>
        </div>
        <h3 class="result-section-title">Resumen del procesamiento</h3>
        <p class="result-section-copy">Trazabilidad desde las predicciones iniciales hasta las geometrías operativas.</p>
        <div class="trace-flow">
          <div class="trace-node"><div class="trace-value">{{ number_format($wallSummary['raw_predictions'] ?? 0) }}</div><div class="trace-label">Predicciones RAW</div><div class="trace-desc">Salidas previas a la deduplicación.</div></div>
          <div class="trace-arrow" aria-hidden="true">→</div>
          <div class="trace-node"><div class="trace-value">{{ number_format($wallSummary['unique_predictions'] ?? 0) }}</div><div class="trace-label">Candidatos únicos</div><div class="trace-desc">YOLO {{ number_format($wallSummary['unique_yolo'] ?? 0) }} · Mask R-CNN {{ number_format($wallSummary['unique_maskrcnn'] ?? 0) }}</div></div>
          <div class="trace-arrow" aria-hidden="true">→</div>
          <div class="trace-node"><div class="trace-value">{{ number_format($wallSummary['catalog_groups'] ?? 0) }}</div><div class="trace-label">Grupos de evidencia</div><div class="trace-desc">Agrupaciones utilizadas para asociar predicciones.</div></div>
          <div class="trace-arrow" aria-hidden="true">→</div>
          <div class="trace-node final"><div class="trace-value">{{ number_format($wallSummary['primary_objects'] ?? 0) }}</div><div class="trace-label">Objetos operativos</div><div class="trace-desc">Geometrías finales para QA/QC, no árboles validados.</div></div>
        </div>
        <h3 class="result-section-title">Evidencia de modelos y revisión</h3>
        <p class="result-section-copy">Estas métricas describen propiedades que pueden superponerse. No deben sumarse indiscriminadamente.</p>
        <div class="evidence-groups">
          <div class="evidence-group"><h4>Procedencia de la geometría</h4><div class="evidence-metrics">
            <div class="evidence-mini green"><strong>{{ number_format($wallSummary['primary_maskrcnn'] ?? 0) }}</strong><span>Geometría primaria Mask R-CNN</span></div>
            <div class="evidence-mini blue"><strong>{{ number_format($wallSummary['primary_yolo_fallback'] ?? 0) }}</strong><span>Fallback YOLO-Seg</span></div>
          </div><p class="group-note">Identifica el modelo del que proviene la geometría operativa.</p></div>
          <div class="evidence-group"><h4>Asociación entre modelos</h4><div class="evidence-metrics">
            <div class="evidence-mini purple"><strong>{{ number_format($wallSummary['bilateral_clean'] ?? 0) }}</strong><span>Evidencia bilateral</span></div>
            <div class="evidence-mini"><strong>{{ number_format($wallSummary['mrcnn_only'] ?? 0) }}</strong><span>Mask R-CNN-only</span></div>
            <div class="evidence-mini"><strong>{{ number_format($wallSummary['yolo_only'] ?? 0) }}</strong><span>YOLO-only</span></div>
          </div></div>
          <div class="evidence-group"><h4>Control de calidad</h4><div class="evidence-metrics">
            <div class="evidence-mini amber"><strong>{{ number_format($wallSummary['requires_review'] ?? 0) }}</strong><span>Requieren revisión</span></div>
            <div class="evidence-mini amber"><strong>{{ number_format($structuralConflictObjects) }}</strong><span>Con conflicto estructural</span></div>
          </div><p class="group-note">Los conflictos estructurales están incluidos en los casos marcados para revisión.</p></div>
        </div>
        <div class="method-note"><strong>Regla de consolidación:</strong> Mask R-CNN se utiliza como fuente geométrica primaria cuando existe una detección asociada. YOLO-Seg se conserva como evidencia independiente y como fallback cuando Mask R-CNN no presenta candidato.</div>
        <div class="interpretation-note"><svg class="ico"><use href="#i-alert"/></svg><div><strong>Alcance científico:</strong> los <strong>{{ number_format($wallSummary['primary_objects'] ?? 0) }} objetos operativos no equivalen a árboles confirmados</strong>, y los <strong>{{ number_format($wallSummary['requires_review'] ?? 0) }} objetos marcados para revisión no son errores confirmados</strong>. La validez definitiva requiere comparación con verdad de campo.</div></div>
        <h3 class="result-section-title">Resultados y productos del análisis</h3>
        <p class="result-section-copy">Consulta el mapa, descarga artefactos y revisa los detalles del análisis registrado.</p>
        <div class="result-actions">
          @if($geojsonArtifact)
            <a class="artifact-button primary" href="{{ route('analysis.map', ['analysisUuid' => $wallToWallAnalysis->uuid]) }}"><span class="action-symbol"><svg class="ico"><use href="#i-map"/></svg></span><span class="action-copy"><strong>Abrir mapa de resultados</strong><span>Objetos operativos en el mapa interactivo</span></span><svg class="ico action-chevron"><use href="#i-right"/></svg></a>
          @endif
          @if($gpkgArtifact)
            <a class="artifact-button" href="{{ route('analysis.artifacts.download', ['analysisUuid' => $wallToWallAnalysis->uuid, 'artifactUuid' => $gpkgArtifact->uuid]) }}"><span class="action-symbol"><svg class="ico"><use href="#i-database"/></svg></span><span class="action-copy"><strong>Exportar GeoPackage</strong><span>Resultados geoespaciales .gpkg</span></span><svg class="ico action-chevron"><use href="#i-right"/></svg></a>
          @endif
          @if($manifestArtifact)
            <a class="artifact-button" href="{{ route('analysis.artifacts.download', ['analysisUuid' => $wallToWallAnalysis->uuid, 'artifactUuid' => $manifestArtifact->uuid]) }}"><span class="action-symbol"><svg class="ico"><use href="#i-file"/></svg></span><span class="action-copy"><strong>Descargar manifiesto técnico</strong><span>Metadatos y configuración registrada</span></span><svg class="ico action-chevron"><use href="#i-right"/></svg></a>
          @endif
          <a class="artifact-button" href="{{ route('analysis.jobs.show', ['analysisUuid' => $wallToWallAnalysis->uuid]) }}"><span class="action-symbol"><svg class="ico"><use href="#i-info"/></svg></span><span class="action-copy"><strong>Ver detalles técnicos</strong><span>Parámetros y estado de la ejecución</span></span><svg class="ico action-chevron"><use href="#i-right"/></svg></a>
        </div>
        <div class="result-meta">
          <span>UUID: <strong class="uuid-value">{{ $wallToWallAnalysis->uuid }}</strong></span>
          <button type="button" class="copy-uuid" data-copy-uuid="{{ $wallToWallAnalysis->uuid }}" aria-label="Copiar UUID del análisis"><svg class="ico ico-sm"><use href="#i-copy"/></svg><span>Copiar</span></button>
          @if($wallToWallAnalysis->completed_at)<span>· Completado: <strong>{{ $wallToWallAnalysis->completed_at->format('d/m/Y H:i') }}</strong></span>@endif
          <span>· Ventanas procesadas: <strong>{{ $wallToWallAnalysis->processed_tiles }} / {{ $wallToWallAnalysis->total_tiles }}</strong></span>
        </div>
      </section>
    @endif
  </div>
</section>

</main>
<nav class="mobile-nav" aria-label="Modos de análisis para móvil">
  <button type="button" class="nav-item {{ $initialWorkspaceTab === 'lab' ? 'is-active' : '' }}" data-workspace-tab="lab" aria-controls="workspace-lab" aria-pressed="{{ $initialWorkspaceTab === 'lab' ? 'true' : 'false' }}"><svg class="ico"><use href="#i-cpu"/></svg>Laboratorio</button>
  <button type="button" class="nav-item {{ $initialWorkspaceTab === 'wall' ? 'is-active' : '' }}" data-workspace-tab="wall" aria-controls="workspace-wall" aria-pressed="{{ $initialWorkspaceTab === 'wall' ? 'true' : 'false' }}"><svg class="ico"><use href="#i-image"/></svg>Ortomosaico</button>
</nav>
<script>
  // En teléfonos, el protocolo permanece consultable sin ocupar el primer pantallazo.
  const scientificProtocolDetails = document.getElementById('scientificProtocol');
  if (scientificProtocolDetails && window.matchMedia('(max-width:560px)').matches) {
    scientificProtocolDetails.removeAttribute('open');
  }
  // Copiar el identificador persistente sin alterar el resultado.
  document.querySelectorAll('[data-copy-uuid]').forEach(copyButton => {
    copyButton.addEventListener('click', async () => {
      const value = copyButton.getAttribute('data-copy-uuid');
      try {
        await navigator.clipboard.writeText(value);
        const label = copyButton.querySelector('span');
        if (label) label.textContent = 'Copiado';
      } catch (_) {
        // La lectura manual sigue disponible cuando el navegador restringe el portapapeles.
        const label = copyButton.querySelector('span');
        if (label) label.textContent = 'Selecciona el UUID';
      }
    });
  });


    /*
    |--------------------------------------------------------------------------
    | Workspace navigation
    |--------------------------------------------------------------------------
    */

    const workspaceTabs = document.querySelectorAll('[data-workspace-tab]');
    const workspacePanels = {
        lab: document.getElementById('workspace-lab'),
        wall: document.getElementById('workspace-wall'),
    };

    function activateWorkspaceTab(tabName) {
        workspaceTabs.forEach(tab => {
            const active = tab.dataset.workspaceTab === tabName;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        Object.entries(workspacePanels).forEach(([name, panel]) => {
            panel?.classList.toggle('is-active', name === tabName);
        });

        try {
            window.sessionStorage.setItem('uav-analysis-workspace', tabName);
        } catch (error) {
            // sessionStorage no es indispensable para el funcionamiento.
        }
    }

    workspaceTabs.forEach(tab => {
        tab.addEventListener('click', () => activateWorkspaceTab(tab.dataset.workspaceTab));
    });

    try {
        const storedWorkspace = window.sessionStorage.getItem('uav-analysis-workspace');
        const selectedMosaicInUrl = @json(request()->has('mosaic'));
        if (!selectedMosaicInUrl && storedWorkspace && workspacePanels[storedWorkspace]) {
            activateWorkspaceTab(storedWorkspace);
        }
    } catch (error) {
        // Mantener el estado inicial definido por Blade.
    }

    /*
    |--------------------------------------------------------------------------
    | Form configuration
    |--------------------------------------------------------------------------
    */

    const modelSelect =
        document.getElementById(
            'modelSelect'
        );

    const thresholdInput =
        document.getElementById(
            'thresholdInput'
        );

    const thresholdHelp =
        document.getElementById(
            'thresholdHelp'
        );

    const singleThresholdGroup =
        document.getElementById(
            'singleThresholdGroup'
        );

    const compareOptions =
        document.getElementById(
            'compareOptions'
        );

    const modelHelp =
        document.getElementById(
            'modelHelp'
        );


    const modelConfiguration = {

        yolo: {

            threshold: 0.25,

            help:
                'YOLO-Seg E1.1 · score mínimo de referencia = 0.25'
        },

        maskrcnn: {

            threshold: 0.40,

            help:
                'Mask R-CNN M1.0 · score mínimo de referencia = 0.40'
        },

        both: {

            help:
                'Ejecuta ambos modelos sobre la misma imagen para inspeccionar acuerdo espacial entre máscaras.'
        }

    };


    function updateModelInterface(
        changeThreshold = true
    ) {

        const model =
            modelSelect.value;


        if (model === 'both') {

            singleThresholdGroup
                .style.display = 'none';

            compareOptions
                .classList.add(
                    'visible'
                );

            modelHelp.textContent =
                modelConfiguration
                    .both
                    .help;

            return;
        }


        singleThresholdGroup
            .style.display = 'block';

        compareOptions
            .classList.remove(
                'visible'
            );


        const configuration =
            modelConfiguration[
                model
            ];


        modelHelp.textContent =
            configuration.help;


        thresholdHelp.textContent =
            configuration.help;


        if (changeThreshold) {

            thresholdInput.value =
                configuration.threshold;
        }
    }


    modelSelect
        ?.addEventListener(
            'change',
            function () {

                updateModelInterface(
                    true
                );
            }
        );


    updateModelInterface(
        false
    );


    const modelCards = document.querySelectorAll('[data-model-value]');

    function syncModelCards() {
        const current = modelSelect?.value;
        modelCards.forEach(card => {
            card.classList.toggle('is-active', card.dataset.modelValue === current);
            card.setAttribute('aria-pressed', card.dataset.modelValue === current ? 'true' : 'false');
        });
    }

    modelCards.forEach(card => {
        card.addEventListener('click', () => {
            if (!modelSelect) return;
            modelSelect.value = card.dataset.modelValue;
            modelSelect.dispatchEvent(new Event('change', { bubbles: true }));
            syncModelCards();
        });
    });

    modelSelect?.addEventListener('change', syncModelCards);
    syncModelCards();


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    const imageInput =
        document.getElementById(
            'imageInput'
        );

    const uploadName =
        document.getElementById(
            'uploadName'
        );


    imageInput
        ?.addEventListener(
            'change',
            function (event) {

                const file =
                    event.target.files[0];


                if (!file) {
                    return;
                }


                uploadName.textContent =
                    file.name;


                const preview =
                    document.getElementById(
                        'previewImage'
                    );


                const placeholder =
                    document.getElementById(
                        'viewerPlaceholder'
                    );


                if (preview) {

                    preview.src =
                        URL.createObjectURL(
                            file
                        );

                    preview.style.display =
                        'block';
                }


                if (placeholder) {

                    placeholder.style.display =
                        'none';
                }
            }
        );


    /*
    |--------------------------------------------------------------------------
    | Loading
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById(
            'analysisForm'
        );

    const button =
        document.getElementById(
            'analyzeButton'
        );

    const buttonText =
        document.getElementById(
            'buttonText'
        );


    form?.addEventListener(
        'submit',
        function () {

            button.disabled = true;

            buttonText.textContent =
                'Procesando modelos...';
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Loading wall-to-wall
    |--------------------------------------------------------------------------
    */

    const wallToWallForm =
        document.getElementById(
            'wallToWallForm'
        );

    const wallToWallSubmit =
        document.getElementById(
            'wallToWallSubmit'
        );

    const wallToWallButtonText =
        document.getElementById(
            'wallToWallButtonText'
        );


    wallToWallForm
        ?.addEventListener(
            'submit',
            function () {

                if (wallToWallSubmit) {

                    wallToWallSubmit.disabled =
                        true;
                }


                if (wallToWallButtonText) {

                    wallToWallButtonText.textContent =
                        'Procesando ortomosaico...';
                }
            }
        );


@if($result)


    /*
    |--------------------------------------------------------------------------
    | Canvas configuration
    |--------------------------------------------------------------------------
    */

    const isCompare =
        @json($isCompare);


    const image =
        document.getElementById(
            'uavImage'
        );


    const canvas =
        document.getElementById(
            'maskCanvas'
        );


    const ctx = canvas?.getContext('2d') ?? null;


    const originalWidth =
        {{
            (int)(
                $isCompare
                    ? (
                        $result[
                            'image'
                        ][
                            'width'
                        ] ?? 1024
                    )
                    : (
                        $result[
                            'width'
                        ] ?? 1024
                    )
            )
        }};


    const originalHeight =
        {{
            (int)(
                $isCompare
                    ? (
                        $result[
                            'image'
                        ][
                            'height'
                        ] ?? 1024
                    )
                    : (
                        $result[
                            'height'
                        ] ?? 1024
                    )
            )
        }};


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function drawPolygon(
        polygon,
        fill,
        stroke,
        label = null,
        dashed = false
    ) {

        if (
            !polygon
            ||
            polygon.length < 3
        ) {
            return;
        }


        ctx.beginPath();


        polygon.forEach(
            (point, index) => {

                if (index === 0) {

                    ctx.moveTo(
                        point[0],
                        point[1]
                    );

                } else {

                    ctx.lineTo(
                        point[0],
                        point[1]
                    );
                }
            }
        );


        ctx.closePath();


        if (fill) {

            ctx.fillStyle = fill;

            ctx.fill();
        }


        ctx.strokeStyle = stroke;

        ctx.lineWidth = 3;


        if (dashed) {

            ctx.setLineDash(
                [10, 7]
            );

        } else {

            ctx.setLineDash([]);
        }


        ctx.stroke();

        ctx.setLineDash([]);


        if (label) {

            const point =
                polygon[0];


            ctx.font =
                'bold 17px Arial';


            const width =
                ctx.measureText(
                    label
                ).width;


            const x =
                point[0];

            const y =
                Math.max(
                    25,
                    point[1]
                );


            ctx.fillStyle =
                'rgba(10,20,15,.82)';


            ctx.fillRect(
                x - 5,
                y - 21,
                width + 10,
                27
            );


            ctx.fillStyle =
                '#ffffff';


            ctx.fillText(
                label,
                x,
                y
            );
        }
    }


    function polygonCentroid(
        polygon
    ) {

        if (
            !polygon
            ||
            polygon.length === 0
        ) {

            return [0,0];
        }


        let x = 0;

        let y = 0;


        polygon.forEach(
            point => {

                x += point[0];

                y += point[1];
            }
        );


        return [
            x / polygon.length,
            y / polygon.length
        ];
    }


    function drawMatchMarker(
        polygon,
        match
    ) {

        const [
            x,
            y
        ] = polygonCentroid(
            polygon
        );


        ctx.beginPath();

        ctx.arc(
            x,
            y,
            18,
            0,
            Math.PI * 2
        );


        ctx.fillStyle =
            'rgba(138,70,199,.92)';

        ctx.fill();


        ctx.strokeStyle =
            '#ffffff';

        ctx.lineWidth = 2;

        ctx.stroke();


        ctx.fillStyle =
            '#ffffff';

        ctx.font =
            'bold 11px Arial';

        ctx.textAlign =
            'center';

        ctx.textBaseline =
            'middle';


        ctx.fillText(
            'M' + match.id,
            x,
            y
        );


        ctx.textAlign =
            'start';

        ctx.textBaseline =
            'alphabetic';
    }


    /*
    |--------------------------------------------------------------------------
    | Comparison mode
    |--------------------------------------------------------------------------
    */

@if($isCompare)


    const yoloCrowns =
        @json(
            $yoloCrowns
        );


    const maskCrowns =
        @json(
            $maskCrowns
        );


    const matches =
        @json(
            $matches
        );


    const matchedYoloIds =
        new Set(
            matches.map(
                item =>
                    Number(
                        item.yolo_id
                    )
            )
        );


    const matchedMaskIds =
        new Set(
            matches.map(
                item =>
                    Number(
                        item.maskrcnn_id
                    )
            )
        );


    function renderComparison() {
        if (!canvas || !ctx) return;


        canvas.width =
            originalWidth;

        canvas.height =
            originalHeight;


        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );


        const showYolo =
            document.getElementById(
                'showYolo'
            )?.checked;


        const showMask =
            document.getElementById(
                'showMask'
            )?.checked;


        const showMatches =
            document.getElementById(
                'showMatches'
            )?.checked;


        const showLabels =
            document.getElementById(
                'showLabels'
            )?.checked;


        /*
        |--------------------------------------------------------------------------
        | YOLO
        |--------------------------------------------------------------------------
        */

        if (showYolo) {

            yoloCrowns.forEach(
                crown => {

                    const matched =
                        matchedYoloIds.has(
                            Number(
                                crown.id
                            )
                        );


                    const label =
                        showLabels
                            ? (
                                'Y'
                                + crown.id
                                + ' · '
                                + Number(
                                    crown.confidence
                                ).toFixed(2)
                            )
                            : null;


                    drawPolygon(
                        crown.polygon,

                        'rgba(38,128,217,.18)',

                        matched
                            ? 'rgba(117,78,190,.95)'
                            : 'rgba(65,160,255,.95)',

                        label,

                        false
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mask R-CNN
        |--------------------------------------------------------------------------
        */

        if (showMask) {

            maskCrowns.forEach(
                crown => {

                    const matched =
                        matchedMaskIds.has(
                            Number(
                                crown.id
                            )
                        );


                    const label =
                        showLabels
                            ? (
                                'M'
                                + crown.id
                                + ' · '
                                + Number(
                                    crown.confidence
                                ).toFixed(2)
                            )
                            : null;


                    drawPolygon(
                        crown.polygon,

                        'rgba(27,166,107,.13)',

                        matched
                            ? 'rgba(177,95,220,.95)'
                            : 'rgba(53,226,143,.95)',

                        label,

                        true
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Match markers
        |--------------------------------------------------------------------------
        */

        if (showMatches) {

            matches.forEach(
                match => {

                    const crown =
                        yoloCrowns.find(
                            item =>
                                Number(
                                    item.id
                                )
                                ===
                                Number(
                                    match.yolo_id
                                )
                        );


                    if (
                        crown
                        &&
                        crown.polygon
                    ) {

                        drawMatchMarker(
                            crown.polygon,
                            match
                        );
                    }
                }
            );
        }
    }


    [
        'showYolo',
        'showMask',
        'showMatches',
        'showLabels'

    ].forEach(
        id => {

            document
                .getElementById(id)
                ?.addEventListener(
                    'change',
                    renderComparison
                );
        }
    );


@else


    /*
    |--------------------------------------------------------------------------
    | Individual mode
    |--------------------------------------------------------------------------
    */

    const crowns =
        @json(
            $singleCrowns
        );


    const resultMode =
        @json(
            $analysisMode
        );


    function renderSingle() {
        if (!canvas || !ctx) return;


        canvas.width =
            originalWidth;

        canvas.height =
            originalHeight;


        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );


        const showFill =
            document.getElementById(
                'showFill'
            )?.checked;


        const showLabels =
            document.getElementById(
                'showLabels'
            )?.checked;


        const isMask =
            resultMode ===
            'maskrcnn';


        crowns.forEach(
            crown => {


                const label =
                    showLabels
                        ? (
                            '#'
                            + crown.id
                            + ' · '
                            + Number(
                                crown.confidence
                            ).toFixed(2)
                        )
                        : null;


                drawPolygon(
                    crown.polygon,

                    showFill
                        ? (
                            isMask
                                ? 'rgba(27,166,107,.22)'
                                : 'rgba(38,128,217,.22)'
                        )
                        : null,

                    isMask
                        ? 'rgba(53,226,143,.95)'
                        : 'rgba(65,160,255,.95)',

                    label
                );

            }
        );

    }


    [
        'showFill',
        'showLabels'

    ].forEach(
        id => {

            document
                .getElementById(id)
                ?.addEventListener(
                    'change',
                    renderSingle
                );
        }
    );


@endif


    /*
    |--------------------------------------------------------------------------
    | Initial render
    |--------------------------------------------------------------------------
    */

    function renderCurrent() {
        if (!canvas || !ctx || !image) return;

        if (isCompare) {

            renderComparison();

        } else {

            renderSingle();
        }
    }


    image?.addEventListener(
        'load',
        renderCurrent
    );


    if (
        image
        &&
        image.complete
    ) {

        renderCurrent();
    }


@endif

</script>


</body>
</html>
