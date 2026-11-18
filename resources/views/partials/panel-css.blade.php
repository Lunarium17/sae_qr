<style>
    :root {
        --panel-bg: #E2E8F0;
        --panel-border: rgba(203, 213, 225, .9);
        --panel-txt: #0F172A;
        --panel-txt-sec: #334155;
        --panel-shadow: 0 4px 6px -2px rgba(15, 23, 42, .07), 0 12px 24px -10px rgba(15, 23, 42, .12);
        --panel-shadow-lg: 0 6px 10px -4px rgba(15, 23, 42, .10), 0 20px 32px -12px rgba(15, 23, 42, .18);
    }

    body { background: var(--panel-bg); color: var(--panel-txt-sec); }

    .card {
        border: 1px solid var(--panel-border);
        box-shadow: var(--panel-shadow);
    }
    .card.rounded-4 { border-radius: 1.25rem; }

    .titulo-panel { color: var(--panel-txt); font-weight: 700; }
    .texto-slate { color: var(--panel-txt-sec); }
    .hr-divisor { border-top: 1px solid #CBD5E1; opacity: 1; margin: 0; }
    .punto-estado { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

    .navbar-editorial {
        background: #FFFFFF;
        border-bottom: 1px solid #E2E8F0;
    }
    #jsCampanaBtn { color: #334155; border-color: #CBD5E1; }
    #jsCampanaBtn:hover, #jsCampanaBtn:focus { background: #F1F5F9; color: #0F172A; }

    .bento {
        background: #FFFFFF;
        border: 1px solid var(--panel-border);
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: var(--panel-shadow);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .bento:hover {
        transform: translateY(-3px);
        box-shadow: var(--panel-shadow-lg);
    }
    .metrica-prota { background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); border: none; color: #FFFFFF; }
    .metrica-ambar { background: linear-gradient(180deg, #FFFBEB 0%, #FFFFFF 55%); border-top: 3px solid #D97706; }
    .metrica-coral { background: linear-gradient(180deg, #FFF1F2 0%, #FFFFFF 55%); border-top: 3px solid #E11D48; }
    .metrica-cielo { background: linear-gradient(180deg, #EFF6FF 0%, #FFFFFF 55%); border-top: 3px solid #2563EB; }
    .metrica-esmeralda { background: linear-gradient(180deg, #ECFDF5 0%, #FFFFFF 55%); border-top: 3px solid #059669; }
    .metrica-indigo { background: linear-gradient(180deg, #EEF2FF 0%, #FFFFFF 55%); border-top: 3px solid #6366F1; }
    .numero-hero { font-size: 2.75rem; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
    .numero-bento { font-size: 3rem; font-weight: 800; color: #0F172A; line-height: 1; letter-spacing: -.02em; }

    .kpi-prota { background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); border: none; color: #FFFFFF; }
    .kpi-prota .progress { background: rgba(255,255,255,.18) !important; }
    .kpi-prota .progress-bar { background: #34D399 !important; }

    .bento-azul { background: #EFF6FF; border-color: #BFDBFE; }
    .bento-indigo { background: #EEF2FF; border-color: #C7D2FE; }
    .bento-esmeralda { background: #ECFDF5; border-color: #A7F3D0; }
    .bento-ambar { background: #FEF3C7; border-color: #FCD34D; }
    .bento-marcada { background: #FFFFFF; border-color: #94A3B8; }

    .pildora-materia { font-size: .92rem; }
    .pildora-azul { background: #EFF6FF; border: 1px solid #BFDBFE; }
    .pildora-esmeralda { background: #ECFDF5; border: 1px solid #A7F3D0; }
    .pildora-ambar { background: #FEF3C7; border: 1px solid #FCD34D; }
    .pildora-coral { background: #FFF1F2; border: 1px solid #FECDD3; }

    .badge-suave-verde { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .badge-suave-carmesi { background: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3; }

    .table-moderna thead th {
        background-color: #F1F5F9;
        color: #0F172A;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
        padding: .75rem .85rem;
    }
    .table-moderna tbody td {
        font-size: .85rem;
        color: #334155;
        border-bottom: 1px solid #E2E8F0;
        padding: .7rem .85rem;
        vertical-align: middle;
    }
    .table-moderna tbody tr:hover { background-color: #F8FAFC; }
    .badge-estado { border-radius: 50rem; font-weight: 600; text-transform: uppercase; font-size: .68rem; padding: .35rem .65rem; }
    .badge-asistencia { background: #dcfce7; color: #14532d; }
    .badge-retardo { background: #fef9c3; color: #713f12; }
    .badge-permiso { background: #dbeafe; color: #1e3a8a; }
    .badge-falta { background: #fee2e2; color: #7f1d1d; }

    .banner-institucional {
        background: linear-gradient(135deg, #0b2447 0%, #19376d 55%, #2f5bd7 100%);
    }
    .btn-banner-translucido {
        background: rgba(255,255,255,.16);
        color: #fff;
        transition: background .2s ease;
    }
    .btn-banner-translucido:hover,
    .btn-banner-translucido:focus { background: rgba(255,255,255,.30); color: #fff; }

    .kpi-azul { background: #eff6ff !important; border-top: 3px solid #2563eb; }
    .kpi-esmeralda { background: #ecfdf5 !important; border-top: 3px solid #059669; }
    .kpi-indigo { background: #eef2ff !important; border-top: 3px solid #6366f1; }
    .kpi-ambar { background: #fefce8 !important; border-top: 3px solid #d97706; }
    .kpi-rojo { background: #fef2f2 !important; border-top: 3px solid #e11d48; }

    .tile-card {
        border: 1px solid var(--panel-border) !important;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .tile-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--panel-shadow-lg) !important;
    }
    .tile-card .tile-flecha { display: inline-block; transition: transform .18s ease; }
    .tile-card:hover .tile-flecha { transform: translateX(6px); }
    .tile-primaria:hover { border-color: #0d6efd !important; }
    .tile-gris:hover { border-color: #334155 !important; }
    .tile-ambar:hover { border-color: #d97706 !important; }
    .tile-cian:hover { border-color: #0891b2 !important; }
    .tile-verde:hover { border-color: #059669 !important; }
    .tile-indigo:hover { border-color: #6366f1 !important; }

    .card-hover { transition: transform .15s ease, box-shadow .15s ease; }
    .card-hover:hover { transform: translateY(-2px); box-shadow: var(--panel-shadow-lg) !important; }
    .card-hover:active { transform: scale(.98); }
    a.card-hover:focus-visible { outline: 3px solid rgba(13,110,253,.35); outline-offset: 2px; }

    .info-icon { position: relative; display: inline-flex; align-items: center; color: #0d6efd; cursor: help; vertical-align: middle; font-size: .8em; margin-left: .15rem; padding: .1rem; }
    .info-icon .info-icon-text { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); min-width: 240px; max-width: 320px; background: #212529; color: #fff; font-size: .75rem; font-weight: 400; line-height: 1.35; padding: .5rem .65rem; border-radius: .4rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .2); opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 10; text-align: left; }
    .info-icon:hover .info-icon-text, .info-icon:focus .info-icon-text, .info-icon:focus-within .info-icon-text { opacity: 1; visibility: visible; }

    /* Pildoras semanticas limpias (semáforo solo en derecho a examen / estados criticos) */
    .verde-limpio { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .ambar-limpio { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
    .rojo-limpio  { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
    .azul-suave   { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .neutra-slate { background: #F1F5F9; color: #334155; border: 1px solid #E2E8F0; }

    /* Bloque de incidencias A/R/P/F: contenedor neutro con puntos indicadores (0 en gris suave) */
    .incidencias-box { display: inline-flex; flex-wrap: wrap; justify-content: center; gap: .5rem; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: .875rem; padding: .45rem .75rem; }
    .inc-item { display: inline-flex; align-items: center; gap: .3rem; color: #94A3B8; font-size: .78rem; font-weight: 600; line-height: 1.4; white-space: nowrap; }
    .inc-item .dot { width: 7px; height: 7px; border-radius: 50%; background: #CBD5E1; opacity: .4; }
    .inc-item.inc-on { color: #334155; }
    .inc-item.inc-on .dot { opacity: 1; }
    .inc-item .val { font-weight: 700; }
    .inc-a .dot { background: #059669; }
    .inc-r .dot { background: #D97706; }
    .inc-p .dot { background: #2563EB; }
    .inc-f .dot { background: #DC2626; }
    .inc-a.inc-on .val { color: #047857; }
    .inc-r.inc-on .val { color: #B45309; }
    .inc-p.inc-on .val { color: #1D4ED8; }
    .inc-f.inc-on .val { color: #B91C1C; }

    /* Franja institucional azul para headers de tarjetas y modales */
    .franja-institucional { background: linear-gradient(135deg, #0B2447 0%, #19376D 60%, #2F5BD7 100%); }

    /* Boton de accion principal con el azul institucional del sistema */
    .btn-institucional { background: #2563EB; border-color: #2563EB; color: #FFFFFF; }
    .btn-institucional:hover, .btn-institucional:focus, .btn-institucional:active { background: #1D4ED8; border-color: #1D4ED8; color: #FFFFFF; }

    /* Aire extra en filas de tablas de padron */
    .tabla-padron tbody td { padding-top: 1.05rem; padding-bottom: 1.05rem; }

    /* Separacion reforzada de paneles destacados */
    .panel-definido { box-shadow: 0 4px 12px -6px rgba(15,23,42,.12); }

    /* Mini KPI compacto con numero claro */
    .kpi-mini { background: #FFFFFF; border: 1px solid rgba(203, 213, 225, .9); border-radius: 1rem; padding: .6rem 1.05rem; min-width: 104px; text-align: center; box-shadow: 0 2px 6px -2px rgba(15,23,42,.08); }
    .kpi-mini .num { font-size: 1.35rem; font-weight: 800; line-height: 1.1; color: #0F172A; }
    .kpi-mini .lbl { font-size: .72rem; color: #64748B; font-weight: 600; }
</style>
