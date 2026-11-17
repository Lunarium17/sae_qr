<style>
    .card-hover { transition: transform .15s ease, box-shadow .15s ease; }
    .card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.12) !important; }
    .card-hover:active { transform: scale(.98); }
    a.card-hover:focus-visible { outline: 3px solid rgba(13,110,253,.35); outline-offset: 2px; }

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

    .kpi-azul { background: #eff6ff !important; }
    .kpi-esmeralda { background: #ecfdf5 !important; }
    .kpi-indigo { background: #eef2ff !important; }
    .kpi-ambar { background: #fefce8 !important; }
    .kpi-rojo { background: #fef2f2 !important; }

    .tile-card {
        border: 1px solid #eef2f7 !important;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .tile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .13) !important;
    }
    .tile-card .tile-flecha { display: inline-block; transition: transform .18s ease; }
    .tile-card:hover .tile-flecha { transform: translateX(6px); }
    .tile-primaria:hover { border-color: #0d6efd !important; }
    .tile-gris:hover { border-color: #334155 !important; }
    .tile-ambar:hover { border-color: #d97706 !important; }
    .tile-cian:hover { border-color: #0891b2 !important; }
    .tile-verde:hover { border-color: #059669 !important; }
    .tile-indigo:hover { border-color: #6366f1 !important; }
</style>
