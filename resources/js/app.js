import './bootstrap';
import './live';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Komponen kalender (dipakai di date-bar)
Alpine.data('calendar', (selected) => ({
    open: false,
    selected: selected,                          // 'YYYY-MM-DD' yang lagi dipilih
    view: new Date(selected + 'T00:00:00'),      // bulan yang lagi ditampilin
    today: new Date(),

    // Label "June 2026"
    get monthLabel() {
        return this.view.toLocaleString('en-US', { month: 'long', year: 'numeric' });
    },

    // Susun tanggal jadi baris-baris minggu (7 kolom)
    get weeks() {
        const year = this.view.getFullYear();
        const month = this.view.getMonth();
        const startDay = new Date(year, month, 1).getDay();     // hari pertama jatuh di kolom mana
        const total = new Date(year, month + 1, 0).getDate();   // jumlah hari di bulan ini

        const cells = [];
        for (let i = 0; i < startDay; i++) cells.push(null);     // sel kosong sebelum tgl 1
        for (let d = 1; d <= total; d++) cells.push(new Date(year, month, d));
        while (cells.length % 7 !== 0) cells.push(null);         // pad biar genap 7

        const weeks = [];
        for (let i = 0; i < cells.length; i += 7) weeks.push(cells.slice(i, i + 7));
        return weeks;
    },

    // Ubah Date → 'YYYY-MM-DD' (pakai waktu lokal biar gak geser hari)
    iso(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    },

    isToday(date)    { return this.iso(date) === this.iso(this.today); },
    isSelected(date) { return this.iso(date) === this.selected; },
    prevMonth()      { this.view = new Date(this.view.getFullYear(), this.view.getMonth() - 1, 1); },
    nextMonth()      { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + 1, 1); },
    pick(date)       { window.location.href = '/?date=' + this.iso(date); },
}));

// Komponen search (header) — live suggestions
Alpine.data('searchBox', () => ({
    q: '',
    results: [],
    open: false,
    loading: false,
    async search() {
        const term = this.q.trim();
        if (term.length < 2) { this.results = []; this.open = false; return; }
        this.loading = true;
        this.open = true;
        try {
            const res = await fetch('/search?q=' + encodeURIComponent(term));
            const data = await res.json();
            const map = (arr, type, label) => (arr || []).map(x => ({ ...x, type, logo: x.logo ?? x.photo ?? null, label: label(x) }));
            this.results = [
                ...map(data.teams,   'team',   () => 'Team'),
                ...map(data.leagues, 'league', () => 'League'),
                ...map(data.players, 'player', x => 'Player' + (x.sub ? ' · ' + x.sub : '')),
                ...map(data.matches, 'match',  x => 'Match · ' + (x.sub || '')),
                ...map(data.articles, 'news', x => 'News' + (x.sub ? ' · ' + x.sub : '')),
            ];
        } catch (e) {
            this.results = [];
        }
        this.loading = false;
    },
}));

// Countdown ke match berikutnya (dipakai di header liga)
Alpine.data('countdown', (target) => ({
    days: '0', hours: '0', mins: '0', started: false, timer: null,
    init() {
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
    },
    tick() {
        const diff = new Date(target).getTime() - Date.now();
        if (diff <= 0) { this.started = true; clearInterval(this.timer); return; }
        const s = Math.floor(diff / 1000);
        this.days = String(Math.floor(s / 86400));
        this.hours = String(Math.floor((s % 86400) / 3600));
        this.mins = String(Math.floor((s % 3600) / 60));
    },
}));

// Poll "Who will win" — voting per match
Alpine.data('matchPoll', (fixtureId, initial) => ({
    counts: initial,
    choice: localStorage.getItem('vote-' + fixtureId) || null,
    get voted() { return this.choice !== null; },
    pct(k) { return this.counts.total ? Math.round((this.counts[k] / this.counts.total) * 100) : 0; },
    async vote(c) {
        this.choice = c;
        localStorage.setItem('vote-' + fixtureId, c);
        try {
            const res = await fetch('/match/' + fixtureId + '/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ choice: c }),
            });
            if (res.ok) this.counts = await res.json();
        } catch (e) {}
    },
}));

// Favorite teams (localStorage) — dipakai tombol Follow + widget "Your teams"
Alpine.store('favs', {
    items: JSON.parse(localStorage.getItem('favTeams') || '[]'),
    has(id) { return this.items.some(t => t.id == id); },
    toggle(team) {
        this.items = this.has(team.id)
            ? this.items.filter(t => t.id != team.id)
            : [...this.items, team];
        localStorage.setItem('favTeams', JSON.stringify(this.items));
    },
});

// Searchable match picker (highlight & prediction forms)
Alpine.data('matchPicker', (options, selectedId, selectedLabel) => ({
    options,
    open: false,
    query: selectedLabel || '',
    selectedId: selectedId,
    get filtered() {
        const q = this.query.trim().toLowerCase();
        const base = q ? this.options.filter((o) => o.label.toLowerCase().includes(q)) : this.options;
        return base.slice(0, 60);
    },
    select(opt) {
        this.selectedId = opt.id;
        this.query = opt.label;
        this.open = false;
    },
}));

// Reactions emoji di artikel
Alpine.data('reactions', (articleId, initial, slug) => ({
    emojis: ['👍', '❤️', '🔥', '😮', '😂'],
    counts: initial || {},
    reacted: localStorage.getItem('reacted-' + articleId),
    async react(e) {
        if (this.reacted) return;
        this.reacted = e;
        localStorage.setItem('reacted-' + articleId, e);
        this.counts[e] = (this.counts[e] || 0) + 1;
        try {
            const res = await fetch('/news/' + slug + '/react', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ emoji: e }),
            });
            if (res.ok) this.counts = await res.json();
        } catch (err) {}
    },
}));

// Markdown toolbar editor (article body)
Alpine.data('mdEditor', () => ({
    ins(before, after, placeholder, lineMode) {
        const ta = this.$refs.ta;
        const s = ta.selectionStart, e = ta.selectionEnd;
        const sel = ta.value.substring(s, e) || placeholder;
        const text = lineMode ? (before + sel) : (before + sel + after);
        ta.setRangeText(text, s, e, 'end');
        ta.focus();
        ta.dispatchEvent(new Event('input'));
    },
    wrap(b, a, p) { this.ins(b, a, p, false); },
    line(b, p) { this.ins(b, '', p, true); },
}));

// Article editor — toolbar + word count + slug + preview + meta counter + unsaved warning
Alpine.data('articleForm', () => ({
    loading: false,
    dirty: false,
    words: 0,
    metaLen: 0,
    slugTouched: false,
    showPreview: false,
    previewHtml: '',
    help: false,

    init() {
        this.recount();
        if (this.$refs.meta) this.metaLen = this.$refs.meta.value.length;
        this.slugTouched = !!(this.$refs.slug && this.$refs.slug.value);
        window.addEventListener('beforeunload', (e) => {
            if (this.dirty && !this.loading) { e.preventDefault(); e.returnValue = ''; }
        });
    },

    // toolbar
    ins(before, after, placeholder, lineMode) {
        const ta = this.$refs.ta;
        const s = ta.selectionStart, e = ta.selectionEnd;
        const sel = ta.value.substring(s, e) || placeholder;
        ta.setRangeText(lineMode ? (before + sel) : (before + sel + after), s, e, 'end');
        ta.focus();
        this.dirty = true;
        this.recount();
    },
    wrap(b, a, p) { this.ins(b, a, p, false); },
    line(b, p) { this.ins(b, '', p, true); },

    // word count + reading time
    recount() {
        const v = (this.$refs.ta ? this.$refs.ta.value : '').trim();
        this.words = v ? v.split(/\s+/).length : 0;
    },
    get readMins() { return Math.max(1, Math.ceil(this.words / 200)); },

    // slug from title (until user edits slug manually)
    slugify(s) {
        return s.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    },
    onTitle(v) {
        this.dirty = true;
        if (!this.slugTouched && this.$refs.slug) this.$refs.slug.value = this.slugify(v);
    },

    // live preview (basic markdown → html)
    togglePreview() {
        this.showPreview = !this.showPreview;
        if (this.showPreview) this.previewHtml = this.md(this.$refs.ta ? this.$refs.ta.value : '');
    },
    md(src) {
        let h = src.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/^### (.*)$/gm, '<h3>$1</h3>')
            .replace(/^## (.*)$/gm, '<h2>$1</h2>')
            .replace(/^&gt; (.*)$/gm, '<blockquote>$1</blockquote>')
            .replace(/^\- (.*)$/gm, '<li>$1</li>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1">')
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank">$1</a>');
        h = h.split(/\n{2,}/).map((p) => /^<(h2|h3|blockquote|li|img)/.test(p.trim()) ? p : '<p>' + p.replace(/\n/g, '<br>') + '</p>').join('');
        return h.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
    },
}));

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

// Loading bar saat pindah halaman
(function () {
    const start = () => {
        const bar = document.getElementById('loadbar');
        if (!bar) return;
        bar.style.opacity = '1';
        requestAnimationFrame(() => { bar.style.width = '92%'; });
    };
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href') || '';
        if (href === '' || href[0] === '#' || a.target === '_blank' || href.startsWith('http') || e.ctrlKey || e.metaKey) return;
        start();
    });
    document.querySelectorAll('form').forEach((f) => f.addEventListener('submit', start));
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            const bar = document.getElementById('loadbar');
            if (bar) { bar.style.transition = 'none'; bar.style.width = '0'; bar.style.opacity = '0'; }
        }
    });
})();

// PWA service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}