{{-- Campana de notificaciones: botón + panel desplegable autónomo (no requiere JS de Bootstrap) --}}
<div class="position-relative" id="jsCampanaWrap">
    <button class="btn btn-outline-light btn-sm rounded-3 position-relative" id="jsCampanaBtn" type="button" aria-label="Notificaciones">
        <i class="bi bi-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="jsCampanaBadge" style="font-size: .6rem;">0</span>
    </button>
    <div class="position-absolute end-0 mt-2 bg-white rounded-4 shadow d-none" id="jsCampanaPanel" style="width: 340px; z-index: 1055;">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <span class="fw-bold small text-dark">Notificaciones</span>
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="jsCampanaLeerTodas">Marcar todas como leídas</button>
        </div>
        <div style="max-height: 340px; overflow-y: auto;" id="jsCampanaLista">
            <div class="px-3 py-4 text-center texto-slate small">No tienes notificaciones.</div>
        </div>
    </div>
</div>
<script>
    (function () {
        const urlLista      = "{{ route('notificaciones.index') }}";
        const urlLeerTodas  = "{{ route('notificaciones.leerTodas') }}";
        const urlLeerBase   = "{{ url('/notificaciones') }}";
        const csrf          = "{{ csrf_token() }}";

        const btn       = document.getElementById('jsCampanaBtn');
        const panel     = document.getElementById('jsCampanaPanel');
        const badge     = document.getElementById('jsCampanaBadge');
        const lista     = document.getElementById('jsCampanaLista');
        const btnTodas  = document.getElementById('jsCampanaLeerTodas');
        if (!btn || !panel || !badge || !lista || !btnTodas) return;

        function crearItem(n) {
            const a = document.createElement('a');
            a.className = 'dropdown-item js-notif py-2 border-bottom' + (n.leida ? ' opacity-50' : '');
            a.href = n.url;
            a.dataset.id = n.id;

            const fila = document.createElement('div');
            fila.className = 'd-flex gap-2 align-items-start';

            const ic = document.createElement('i');
            ic.className = 'bi ' + (n.icono || 'bi-bell') + ' mt-1';

            const col = document.createElement('div');
            const t = document.createElement('div');
            t.className = 'fw-semibold small text-dark';
            t.textContent = n.titulo;
            const x = document.createElement('div');
            x.className = 'small texto-slate';
            x.style.lineHeight = '1.3';
            x.textContent = n.texto;
            const f = document.createElement('div');
            f.className = 'texto-slate';
            f.style.fontSize = '.65rem';
            f.textContent = n.fecha;
            col.append(t, x, f);
            fila.append(ic, col);
            a.append(fila);
            return a;
        }

        function pintar(data) {
            if (data.unread > 0) {
                badge.textContent = data.unread > 99 ? '99+' : data.unread;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            lista.innerHTML = '';
            if (!data.items.length) {
                const v = document.createElement('div');
                v.className = 'px-3 py-4 text-center texto-slate small';
                v.textContent = 'No tienes notificaciones.';
                lista.append(v);
                return;
            }
            data.items.forEach(function (n) {
                const item = crearItem(n);
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    fetch(urlLeerBase + '/' + n.id + '/leer', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } })
                        .catch(function () {})
                        .finally(function () { window.location.href = n.url; });
                });
                lista.append(item);
            });
        }

        function refrescar() {
            fetch(urlLista, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) { if (d) pintar(d); })
                .catch(function () {});
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('d-none');
            if (!panel.classList.contains('d-none')) refrescar();
        });

        btnTodas.addEventListener('click', function () {
            fetch(urlLeerTodas, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } })
                .then(refrescar)
                .catch(function () {});
        });

        document.addEventListener('click', function (e) {
            if (!panel.classList.contains('d-none') && !panel.contains(e.target) && e.target !== btn) {
                panel.classList.add('d-none');
            }
        });

        refrescar();
        setInterval(refrescar, 30000);
    })();
</script>
