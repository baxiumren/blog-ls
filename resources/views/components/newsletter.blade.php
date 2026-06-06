<div class="w-full sm:w-auto" x-data="{ email: '', done: false, loading: false, error: '',
    async submit() {
        if (!this.email) return;
        this.loading = true; this.error = '';
        try {
            const res = await fetch('/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content || '' },
                body: JSON.stringify({ email: this.email }),
            });
            if (res.ok) { this.done = true; }
            else { const d = await res.json(); this.error = (d.errors &amp;&amp; d.errors.email ? d.errors.email[0] : 'Something went wrong.'); }
        } catch (e) { this.error = 'Network error. Please try again.'; }
        this.loading = false;
    } }">
    <template x-if="!done">
        <form @submit.prevent="submit()" class="flex items-center gap-2">
            <input type="email" x-model="email" required placeholder="you@email.com" class="bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition w-full sm:w-56 placeholder-zinc-600">
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">Subscribe</button>
        </form>
    </template>
    <p x-show="done" x-cloak class="text-green-400 text-sm font-medium">✓ Thanks for subscribing!</p>
    <p x-show="error" x-cloak x-text="error" class="text-red-400 text-xs mt-1"></p>
</div>