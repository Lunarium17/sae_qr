@php
    $ahoraSae = \Carbon\Carbon::now();
    $horaSae = (int) $ahoraSae->format('H');
    $saludoHorarioSae = $horaSae < 12 ? 'Buenos días' : ($horaSae < 19 ? 'Buenas tardes' : 'Buenas noches');

    $diasEspSae = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];
    $mesesEspSae = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];
    $fechaActualEspanolSae = $diasEspSae[(int) $ahoraSae->format('w')] . ', '
        . (int) $ahoraSae->format('d') . ' de '
        . $mesesEspSae[(int) $ahoraSae->format('n')] . ' de '
        . $ahoraSae->format('Y');
    $semestreNumSae = ((int) $ahoraSae->format('n')) <= 6 ? '1' : '2';
    $periodoEscolarActualSae = 'Periodo escolar ' . $ahoraSae->format('Y') . '-' . $semestreNumSae;
@endphp
<style>
    /* Color Institucional Azul Pantone 294 C (#002F6C) */
    :root {
        --pantone-294c: #002F6C;
        --pantone-294c-hover: #002250;
        --pantone-294c-rgb: 0, 47, 108;
    }

    /* Barra superior en todos los dashboards y vistas internas */
    nav.navbar {
        background-color: var(--pantone-294c) !important;
    }
    nav.navbar .btn-light {
        color: var(--pantone-294c) !important;
    }

    /* Logo oficial SAE_QR en navbar y formularios de acceso */
    .sae-navbar-logo {
        height: 36px;
        width: auto;
        object-fit: contain;
        background-color: #ffffff;
        padding: 3px 10px;
        border-radius: 8px;
        margin-right: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
        vertical-align: middle;
    }
    .sae-auth-logo {
        max-width: 240px;
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    /* Tarjeta de bienvenida institucional Azul Pantone 294 C */
    .card.sae-greeting-banner {
        background: linear-gradient(135deg, #002F6C 0%, #09356e 100%) !important;
        color: #ffffff !important;
    }
    .card.sae-greeting-banner h2,
    .card.sae-greeting-banner h3,
    .card.sae-greeting-banner .sae-greeting-title {
        color: #ffffff !important;
    }
    .card.sae-greeting-banner .sae-greeting-meta {
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 0.92rem;
    }
    .card.sae-greeting-banner .info-circle-help {
        background-color: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.7);
        color: #ffffff;
    }
    .card.sae-greeting-banner .info-circle-help:hover {
        background-color: #ffffff;
        color: var(--pantone-294c);
    }

    /* Tema Azul Pantone 294 C para Inicio de Sesión y Creación de Cuenta */
    .theme-pantone-294 {
        background: linear-gradient(135deg, #002F6C 0%, #001d45 100%) !important;
    }
    .theme-pantone-294 .card {
        border: none !important;
    }
    .theme-pantone-294 .text-primary {
        color: var(--pantone-294c) !important;
    }
    .theme-pantone-294 .border-primary {
        border-color: var(--pantone-294c) !important;
    }
    .theme-pantone-294 .btn-primary {
        background-color: var(--pantone-294c) !important;
        border-color: var(--pantone-294c) !important;
    }
    .theme-pantone-294 .btn-primary:hover,
    .theme-pantone-294 .btn-primary:focus {
        background-color: var(--pantone-294c-hover) !important;
        border-color: var(--pantone-294c-hover) !important;
    }
    .theme-pantone-294 a {
        color: var(--pantone-294c);
    }
    .theme-pantone-294 a:hover {
        color: var(--pantone-294c-hover);
    }
    .theme-pantone-294 .form-control:focus,
    .theme-pantone-294 .form-select:focus {
        border-color: var(--pantone-294c);
        box-shadow: 0 0 0 0.25rem rgba(0, 47, 108, 0.22);
    }

    /* Distinción visual de cada tarjeta / sección sin bordes y con fondo más oscuro para contraste */
    body:not(.theme-pantone-294) {
        background-color: #dce3ed !important;
    }
    body:not(.theme-pantone-294) .card {
        border: none !important;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04) !important;
        border-radius: 1rem !important;
        background-color: #ffffff;
    }

    /* Botones principales e iconos de sección homologados con Azul Pantone 294 C */
    body:not(.theme-pantone-294) .btn-primary {
        background-color: var(--pantone-294c) !important;
        border-color: var(--pantone-294c) !important;
    }
    body:not(.theme-pantone-294) .btn-primary:hover,
    body:not(.theme-pantone-294) .btn-primary:focus {
        background-color: var(--pantone-294c-hover) !important;
        border-color: var(--pantone-294c-hover) !important;
    }
    body:not(.theme-pantone-294) .card h4 i.text-primary,
    body:not(.theme-pantone-294) .card h5 i.text-primary,
    body:not(.theme-pantone-294) .card h6 i.text-primary,
    body:not(.theme-pantone-294) h3 i.text-primary {
        color: var(--pantone-294c) !important;
    }

    /* Marco animado anti-captura de pantalla para el QR del alumno */
    .qr-live-frame {
        position: relative;
        border: 3px solid var(--pantone-294c) !important;
        animation: qrLivePulse 2s infinite ease-in-out;
    }
    .qr-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #0f172a;
        color: #ffffff;
        font-family: monospace;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
        letter-spacing: 0.5px;
    }
    .qr-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #22c55e;
        box-shadow: 0 0 8px #22c55e;
        animation: qrLiveBlink 1s infinite alternate;
    }
    @keyframes qrLivePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0, 47, 108, 0.25); }
        50% { box-shadow: 0 0 0 8px rgba(0, 47, 108, 0.04); }
    }
    @keyframes qrLiveBlink {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0.35; transform: scale(0.8); }
    }

    /* Círculo de ayuda interactivo (i / !) estilo contorno limpio como en la imagen */
    .info-circle-help {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 21px;
        height: 21px;
        border-radius: 50%;
        background-color: #f0f6ff;
        color: #1d4ed8;
        border: 1.5px solid #3b82f6;
        font-size: 0.74rem;
        font-weight: 800;
        cursor: pointer;
        user-select: none;
        vertical-align: middle;
        margin-left: 7px;
        transition: transform 0.15s ease, background-color 0.15s ease, color 0.15s ease;
    }
    .info-circle-help:hover {
        background-color: var(--pantone-294c);
        border-color: var(--pantone-294c);
        color: #ffffff;
        transform: scale(1.1);
    }

    /* Notificaciones flotantes tipo Toast moderno */
    .sae-toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 420px;
        width: calc(100% - 40px);
        pointer-events: none;
    }
    .sae-toast {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #ffffff;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16), 0 4px 10px rgba(15, 23, 42, 0.08);
        border-left: 5px solid #198754;
        position: relative;
        overflow: hidden;
        animation: saeToastIn 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .sae-toast.sae-toast-danger {
        border-left-color: #dc3545;
    }
    .sae-toast.sae-toast-warning {
        border-left-color: #ffc107;
    }
    .sae-toast.sae-toast-hide {
        opacity: 0;
        transform: translateX(30px);
    }
    .sae-toast-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.05rem;
    }
    .sae-toast-success .sae-toast-icon {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }
    .sae-toast-danger .sae-toast-icon {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }
    .sae-toast-body {
        flex: 1;
        font-size: 0.9rem;
        color: #1e293b;
        line-height: 1.35;
    }
    .sae-toast-title {
        font-weight: 700;
        font-size: 0.88rem;
        margin-bottom: 2px;
        color: #0f172a;
    }
    .sae-toast-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.1rem;
        line-height: 1;
        padding: 2px 4px;
        cursor: pointer;
        border-radius: 6px;
    }
    .sae-toast-close:hover {
        color: #334155;
        background: #f1f5f9;
    }
    .sae-toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: #198754;
        width: 100%;
        animation: saeToastBar 4s linear forwards;
    }
    .sae-toast-danger .sae-toast-progress {
        background: #dc3545;
    }
    @keyframes saeToastIn {
        from { opacity: 0; transform: translateY(-12px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes saeToastBar {
        from { width: 100%; }
        to { width: 0%; }
    }

    /* Selector desplegable enriquecido tipo tarjeta (Custom Dropdown) */
    .sae-custom-select-wrapper {
        position: relative;
        width: 100%;
    }
    .sae-custom-select-trigger {
        width: 100%;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.52rem 2.25rem 0.52rem 0.85rem;
        text-align: left;
        font-size: 0.95rem;
        color: #212529;
        cursor: pointer;
        position: relative;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 40px;
    }
    .sae-custom-select-trigger:focus,
    .sae-custom-select-wrapper.open .sae-custom-select-trigger {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .sae-custom-select-trigger.disabled {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    .sae-custom-select-trigger::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid #495057;
        border-bottom: 2px solid #495057;
        transform: translateY(-65%) rotate(45deg);
        transition: transform 0.2s ease;
    }
    .sae-custom-select-wrapper.open .sae-custom-select-trigger::after {
        transform: translateY(-30%) rotate(-135deg);
    }
    .sae-custom-select-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.15);
        z-index: 1060;
        display: none;
        overflow: hidden;
    }
    .sae-custom-select-wrapper.open .sae-custom-select-menu {
        display: block;
    }
    .sae-custom-select-search {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .sae-custom-select-search input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 10px 6px 32px;
        font-size: 0.875rem;
        outline: none;
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center;
    }
    .sae-custom-select-search input:focus {
        border-color: #3b82f6;
    }
    .sae-custom-select-list {
        max-height: 250px;
        overflow-y: auto;
        padding: 6px;
    }
    .sae-custom-select-item {
        padding: 9px 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.12s ease;
        border-bottom: 1px solid #f8fafc;
    }
    .sae-custom-select-item:last-child {
        border-bottom: none;
    }
    .sae-custom-select-item:hover,
    .sae-custom-select-item.active {
        background-color: #eff6ff;
    }
    .sae-custom-select-item.disabled {
        opacity: 0.55;
        cursor: not-allowed;
        background-color: transparent;
    }
    .sae-custom-select-title {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .sae-custom-select-badge {
        background-color: #dbeafe;
        color: #1e40af;
        font-size: 0.73rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        font-family: monospace;
    }
    .sae-custom-select-sub {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 2px;
    }
</style>

<!-- Contenedor global de notificaciones sobrepuestas (4 segundos) -->
<div class="sae-toast-container" id="saeToastContainer">
    @if (session('success'))
        <div class="sae-toast sae-toast-success" data-sae-toast>
            <div class="sae-toast-icon"><i class="bi bi-check-lg"></i></div>
            <div class="sae-toast-body">
                <div class="sae-toast-title">Operación exitosa</div>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="sae-toast-close" onclick="cerrarSaeToast(this.parentElement)" aria-label="Cerrar">&times;</button>
            <div class="sae-toast-progress"></div>
        </div>
    @endif

    @if (session('error'))
        <div class="sae-toast sae-toast-danger" data-sae-toast>
            <div class="sae-toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="sae-toast-body">
                <div class="sae-toast-title">Atención</div>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="sae-toast-close" onclick="cerrarSaeToast(this.parentElement)" aria-label="Cerrar">&times;</button>
            <div class="sae-toast-progress"></div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="sae-toast sae-toast-danger" data-sae-toast>
            <div class="sae-toast-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="sae-toast-body">
                <div class="sae-toast-title">Revisa los campos</div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="sae-toast-close" onclick="cerrarSaeToast(this.parentElement)" aria-label="Cerrar">&times;</button>
            <div class="sae-toast-progress"></div>
        </div>
    @endif
</div>

<script>
    function cerrarSaeToast(toastEl) {
        if (!toastEl || toastEl.classList.contains('sae-toast-hide')) return;
        toastEl.classList.add('sae-toast-hide');
        setTimeout(function () {
            if (toastEl && toastEl.parentElement) {
                toastEl.remove();
            }
        }, 300);
    }

    window.mostrarNotificacionSae = function (tipo, mensaje, titulo) {
        const container = document.getElementById('saeToastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        const esError = tipo === 'danger' || tipo === 'error';
        toast.className = 'sae-toast ' + (esError ? 'sae-toast-danger' : 'sae-toast-success');
        toast.innerHTML =
            '<div class="sae-toast-icon"><i class="bi ' + (esError ? 'bi-exclamation-triangle-fill' : 'bi-check-lg') + '"></i></div>' +
            '<div class="sae-toast-body">' +
                '<div class="sae-toast-title">' + (titulo || (esError ? 'Atención' : 'Notificación')) + '</div>' +
                '<div></div>' +
            '</div>' +
            '<button type="button" class="sae-toast-close" aria-label="Cerrar">&times;</button>' +
            '<div class="sae-toast-progress"></div>';
        toast.querySelector('.sae-toast-body > div:last-child').textContent = mensaje;
        toast.querySelector('.sae-toast-close').addEventListener('click', function () {
            cerrarSaeToast(toast);
        });
        container.appendChild(toast);
        setTimeout(function () { cerrarSaeToast(toast); }, 4000);
    };

    // Inicializador de selectores desplegables enriquecidos (sae-enhance-select)
    window.inicializarSelectEnriquecido = function (selectEl) {
        if (!selectEl) return;

        let wrapper = selectEl._saeWrapper;
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'sae-custom-select-wrapper';
            selectEl.parentNode.insertBefore(wrapper, selectEl);
            wrapper.appendChild(selectEl);
            selectEl.classList.add('d-none');
            selectEl._saeWrapper = wrapper;

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'sae-custom-select-trigger';

            const menu = document.createElement('div');
            menu.className = 'sae-custom-select-menu';

            const searchBox = document.createElement('div');
            searchBox.className = 'sae-custom-select-search';
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = selectEl.getAttribute('data-search-placeholder') || 'Buscar opción...';
            searchBox.appendChild(searchInput);

            const list = document.createElement('div');
            list.className = 'sae-custom-select-list';

            menu.appendChild(searchBox);
            menu.appendChild(list);
            wrapper.appendChild(trigger);
            wrapper.appendChild(menu);

            wrapper._trigger = trigger;
            wrapper._menu = menu;
            wrapper._searchBox = searchBox;
            wrapper._searchInput = searchInput;
            wrapper._list = list;

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (selectEl.disabled) return;
                document.querySelectorAll('.sae-custom-select-wrapper.open').forEach(function (other) {
                    if (other !== wrapper) other.classList.remove('open');
                });
                wrapper.classList.toggle('open');
                if (wrapper.classList.contains('open') && !searchBox.classList.contains('d-none')) {
                    searchInput.value = '';
                    filtrarItems('');
                    setTimeout(function () { searchInput.focus(); }, 40);
                }
            });

            searchInput.addEventListener('input', function () {
                filtrarItems(this.value.trim().toLowerCase());
            });

            searchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        function filtrarItems(q) {
            const items = wrapper._list.querySelectorAll('.sae-custom-select-item');
            items.forEach(function (item) {
                const texto = (item.getAttribute('data-search') || '').toLowerCase();
                item.style.display = (!q || texto.indexOf(q) !== -1) ? '' : 'none';
            });
        }

        function renderOpciones() {
            const trigger = wrapper._trigger;
            const list = wrapper._list;
            const searchBox = wrapper._searchBox;

            trigger.classList.toggle('disabled', !!selectEl.disabled);
            list.innerHTML = '';

            const opciones = Array.from(selectEl.options);
            const habilitadas = opciones.filter(function (o) { return !o.disabled || o.value !== ''; });
            const mostrarBuscador = selectEl.getAttribute('data-searchable') === 'true' || habilitadas.length >= 4;
            searchBox.classList.toggle('d-none', !mostrarBuscador);

            const opcionActual = selectEl.options[selectEl.selectedIndex];
            trigger.textContent = opcionActual ? opcionActual.textContent.trim() : 'Seleccionar';

            opciones.forEach(function (opt) {
                if (opt.disabled && opt.value === '') {
                    return;
                }
                const item = document.createElement('div');
                item.className = 'sae-custom-select-item' +
                    (opt.selected ? ' active' : '') +
                    (opt.disabled ? ' disabled' : '');

                const titulo = opt.getAttribute('data-title') || opt.textContent.trim();
                const badge = opt.getAttribute('data-badge') || '';
                const sub = opt.getAttribute('data-sub') || '';

                item.setAttribute('data-search', titulo + ' ' + badge + ' ' + sub);

                const titleRow = document.createElement('div');
                titleRow.className = 'sae-custom-select-title';
                const spanTitle = document.createElement('span');
                spanTitle.textContent = titulo;
                titleRow.appendChild(spanTitle);

                if (badge) {
                    const spanBadge = document.createElement('span');
                    spanBadge.className = 'sae-custom-select-badge';
                    spanBadge.textContent = badge;
                    titleRow.appendChild(spanBadge);
                }

                item.appendChild(titleRow);

                if (sub) {
                    const subRow = document.createElement('div');
                    subRow.className = 'sae-custom-select-sub';
                    subRow.textContent = sub;
                    item.appendChild(subRow);
                }

                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (opt.disabled) return;
                    selectEl.value = opt.value;
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    wrapper.classList.remove('open');
                    renderOpciones();
                });

                list.appendChild(item);
            });
        }

        selectEl._saeRender = renderOpciones;
        renderOpciones();
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-cierre de notificaciones en 4 segundos
        document.querySelectorAll('[data-sae-toast]').forEach(function (toastEl) {
            setTimeout(function () {
                cerrarSaeToast(toastEl);
            }, 4000);
        });

        // Inicializar Tooltips de Bootstrap para los círculos (!) al pasar el cursor o dar clic
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el, {
                    trigger: 'hover focus click'
                });
            });
        }

        // Inicializar todos los selects con clase .js-sae-select
        document.querySelectorAll('select.js-sae-select').forEach(function (sel) {
            window.inicializarSelectEnriquecido(sel);
        });

        // Insertar el logo oficial de SAE_QR en la barra de navegación superior
        const logoUrl = "{{ asset('img/logo_sae_qr.jpg') }}";
        document.querySelectorAll('nav.navbar .navbar-brand').forEach(function (brand) {
            if (!brand.querySelector('.sae-navbar-logo')) {
                const iconoAnterior = brand.querySelector('i.bi');
                if (iconoAnterior) {
                    iconoAnterior.remove();
                }
                brand.innerHTML = brand.innerHTML.replace(/SAE\s*QR\s*\|\s*/i, '');
                const imgLogo = document.createElement('img');
                imgLogo.src = logoUrl;
                imgLogo.alt = 'SAE_QR';
                imgLogo.className = 'sae-navbar-logo';
                brand.insertBefore(imgLogo, brand.firstChild);
                brand.classList.add('d-inline-flex', 'align-items-center');
            }
        });

        // Actualizar saludo dinámico según la hora local del usuario (Buenos días / Buenas tardes / Buenas noches)
        const horaLocal = new Date().getHours();
        const saludoTexto = horaLocal < 12 ? 'Buenos días' : (horaLocal < 19 ? 'Buenas tardes' : 'Buenas noches');
        document.querySelectorAll('.js-saludo-horario').forEach(function (el) {
            el.textContent = saludoTexto;
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.sae-custom-select-wrapper.open').forEach(function (w) {
                w.classList.remove('open');
            });
        });
    });
</script>
