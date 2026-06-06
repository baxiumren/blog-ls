// Auto-refresh skor live + smooth minute ticker
(function () {
    var state = {}; // apiId -> { st, short, min, hs, as }

    function statusHTML(d) {
        if (d.st === 'live') return '<span class="text-xs font-bold text-green-500">' + (d.min != null ? d.min : '') + "'</span>";
        if (d.st === 'finished') return '<span class="text-[10px] font-medium text-zinc-500">FT</span>';
        return null;
    }
    function scoreHTML(d) {
        return '<span class="text-sm font-bold tabular-nums">' + (d.hs != null ? d.hs : 0) + ' - ' + (d.as != null ? d.as : 0) + '</span>';
    }
    function renderRow(row, d) {
        var st = row.querySelector('[data-fx-status]');
        var sc = row.querySelector('[data-fx-score]');
        var sh = statusHTML(d);
        if (st && sh) st.innerHTML = sh;
        if (sc && (d.st === 'live' || d.st === 'finished')) sc.innerHTML = scoreHTML(d);
    }
    function paintAll() {
        document.querySelectorAll('[data-fx]').forEach(function (row) {
            var d = state[row.getAttribute('data-fx')];
            if (d) renderRow(row, d);
        });
    }
    function poll() {
        fetch('/live').then(function (r) { return r.json(); }).then(function (map) {
            Object.keys(map).forEach(function (id) { state[id] = map[id]; });
            paintAll();
        }).catch(function () {});
    }
    // +1 menit tiap 60 detik, cuma pas babak jalan (resync dari server tiap poll)
    function minuteTick() {
        var ticking = ['1H', '2H', 'ET'];
        var changed = false;
        Object.keys(state).forEach(function (id) {
            var d = state[id];
            if (d && d.st === 'live' && ticking.indexOf(d.short) !== -1 && typeof d.min === 'number') {
                d.min += 1;
                changed = true;
            }
        });
        if (changed) paintAll();
    }

    if (document.querySelector('[data-fx]')) {
        poll();
        setInterval(poll, 45000);
        setInterval(minuteTick, 60000);
    }
})();