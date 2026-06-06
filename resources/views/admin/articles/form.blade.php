@extends('admin.layout')
@section('title', $article->exists ? 'Edit article' : 'New article')

@section('content')
    <form method="POST" action="{{ $article->exists ? '/admin/articles/' . $article->id : '/admin/articles' }}" enctype="multipart/form-data" x-data="articleForm()" @submit="loading = true">
        @csrf
        @if ($article->exists) @method('PUT') @endif

        @php
        $status = (! $article->exists || ! $article->published_at) ? 'Draft' : ($article->published_at->isFuture() ? 'Scheduled' : 'Published');
        $statusColor = $status === 'Published' ? 'bg-green-500/15 text-green-400' : ($status === 'Scheduled' ? 'bg-blue-500/15 text-blue-400' : 'bg-amber-500/15 text-amber-400');
    @endphp
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3 min-w-0">
            <a href="/admin/articles" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white transition shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></a>
            <div class="min-w-0">
                <h1 class="text-lg font-bold truncate leading-tight">{{ $article->exists ? 'Edit article' : 'New article' }}</h1>
                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded mt-0.5 {{ $statusColor }}">{{ $status }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if ($article->exists)<a href="/admin/articles/{{ $article->id }}/preview" target="_blank" class="text-sm text-zinc-400 hover:text-white px-3 py-2.5 transition">Preview ↗</a>@endif
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2 shadow-lg shadow-blue-600/20">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>{{ $article->exists ? 'Update' : 'Publish' }}</span>
            </button>
        </div>
    </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-4 items-start">
            {{-- Editor --}}
            <div class="lg:col-span-2 bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Title *</label>
                    <div>
                        <input type="text" name="title" value="{{ old('title', $article->title) }}" required placeholder="Add title…" @input="onTitle($event.target.value)" class="w-full bg-transparent border-0 border-b border-zinc-800 focus:border-blue-500 px-0 py-2 text-2xl font-bold focus:outline-none focus:ring-0 transition placeholder-zinc-700">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Permalink <span class="text-zinc-600">(slug — leave blank to auto-generate)</span></label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-500 shrink-0">/news/</span>
                        <input type="text" name="slug" value="{{ old('slug', $article->slug) }}" placeholder="auto-from-title" x-ref="slug" @input="slugTouched = true; dirty = true" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Excerpt <span class="text-zinc-600">(ringkasan)</span></label>
                    <input type="text" name="excerpt" value="{{ old('excerpt', $article->excerpt) }}" placeholder="Short summary shown in listings…" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs text-zinc-400">Body *</label>
                        <div class="flex items-center gap-3 text-[11px] text-zinc-500">
                            <span><span x-text="words"></span> words · <span x-text="readMins"></span> min read</span>
                            <button type="button" @click="togglePreview()" class="text-blue-400 hover:text-white transition font-medium" x-text="showPreview ? '✎ Edit' : '👁 Preview'"></button>
                        </div>
                    </div>
                    <div x-data="{ help: false }" class="mb-2">
                        <button type="button" @click="help = !help" class="text-[11px] text-blue-400 hover:text-white transition">📖 Formatting guide</button>
                        <div x-show="help" x-cloak x-collapse class="mt-2 bg-zinc-800/40 border border-zinc-700 rounded-lg p-3 text-[11px] text-zinc-400 grid sm:grid-cols-2 gap-x-6 gap-y-1.5">
                            <div><code class="text-zinc-300">**bold**</code> → <b>bold</b></div>
                            <div><code class="text-zinc-300">*italic*</code> → <i>italic</i></div>
                            <div><code class="text-zinc-300">## Heading</code> → H2</div>
                            <div><code class="text-zinc-300">### Sub</code> → H3</div>
                            <div><code class="text-zinc-300">- item</code> → bullet list</div>
                            <div><code class="text-zinc-300">&gt; quote</code> → quote block</div>
                            <div><code class="text-zinc-300">[text](https://url)</code> → link</div>
                            <div><code class="text-zinc-300">![alt](image-url)</code> → image</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1 mb-2" x-show="!showPreview">
                        <button type="button" @click="wrap('**','**','bold')" title="Bold" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-bold transition">B</button>
                        <button type="button" @click="wrap('*','*','italic')" title="Italic" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm italic transition">I</button>
                        <span class="w-px h-5 bg-zinc-700 mx-1"></span>
                        <button type="button" @click="line('## ','Heading')" title="Heading 2" class="px-2.5 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-semibold transition">H2</button>
                        <button type="button" @click="line('### ','Heading')" title="Heading 3" class="px-2.5 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-semibold transition">H3</button>
                        <span class="w-px h-5 bg-zinc-700 mx-1"></span>
                        <button type="button" @click="line('- ','List item')" title="Bullet list" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition">•</button>
                        <button type="button" @click="line('> ','Quote')" title="Quote" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition">❝</button>
                        <button type="button" @click="wrap('[','](https://)','link text')" title="Link" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition">🔗</button>
                        <button type="button" @click="wrap('![','](image-url)','alt')" title="Image" class="w-8 h-8 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition">🖼️</button>
                    </div>
                    <textarea x-ref="ta" name="body" rows="18" required x-show="!showPreview" @input="dirty = true; recount()" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono leading-relaxed focus:outline-none focus:border-blue-500 transition">{{ old('body', $article->body) }}</textarea>
                    <div x-show="showPreview" x-cloak x-html="previewHtml" class="article-body bg-zinc-800/40 border border-zinc-700 rounded-lg px-4 py-3 min-h-[300px]"></div>
                    <p class="text-[11px] text-zinc-500 mt-1.5">Select text + click a format button, or type <b>Markdown</b>. Toggle <b>Preview</b> to see the result.</p>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Publish --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-3">
                    <h2 class="text-sm font-semibold flex items-center gap-2"><svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>Publish</h2>
                    <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                        <input type="checkbox" name="published" value="1" {{ old('published', $article->published_at) ? 'checked' : '' }} class="rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0">
                        <span>Publish now <span class="text-zinc-500">(uncheck = draft)</span></span>
                    </label>
                    <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $article->is_featured) ? 'checked' : '' }} class="rounded bg-zinc-800 border-zinc-700 text-amber-500 focus:ring-0">
                        <span>⭐ Featured <span class="text-zinc-500">(headline)</span></span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Schedule <span class="text-zinc-600">(optional)</span></label>
                    <input type="datetime-local" name="publish_at" value="{{ old('publish_at') }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition [color-scheme:dark]">
                    <p class="text-[11px] text-zinc-600 mt-1">Future date = scheduled (auto-publishes then). Overrides "Publish now".</p>
                </div>

                {{-- Organize --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
                    <h2 class="text-sm font-semibold flex items-center gap-2"><svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>Organize</h2>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Category *</label>
                        <input type="text" name="category" list="cats" value="{{ old('category', $article->category ?? 'News') }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                        <datalist id="cats"><option>News</option><option>Transfers</option><option>Preview</option><option>Analysis</option><option>Match report</option><option>Interview</option><option>Injury</option><option>Opinion</option></datalist>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Tags <span class="text-zinc-600">(pisah koma)</span></label>
                        <input type="text" name="tags" value="{{ old('tags', $article->tags) }}" placeholder="Arsenal, Transfer, Premier League" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">League <span class="text-zinc-600">(optional)</span></label>
                        <select name="league_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="">— none —</option>
                            @foreach ($leagues as $l)<option value="{{ $l->id }}" {{ old('league_id', $article->league_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Team <span class="text-zinc-600">(optional)</span></label>
                        <select name="team_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="">— none —</option>
                            @foreach ($teams as $t)<option value="{{ $t->id }}" {{ old('team_id', $article->team_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
                    <h2 class="text-sm font-semibold flex items-center gap-2"><svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 6a5 5 0 100 10 5 5 0 000-10z" /></svg>SEO</h2>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Meta title <span class="text-zinc-600">(kosong = pakai judul)</span></label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" maxlength="255" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Meta description <span class="text-zinc-600">(kosong = pakai excerpt)</span></label>
                        <textarea name="meta_description" x-ref="meta" @input="metaLen = $event.target.value.length" rows="3" maxlength="255" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">{{ old('meta_description', $article->meta_description) }}</textarea>
                        <p class="text-[11px] mt-1" :class="metaLen > 160 ? 'text-red-400' : 'text-zinc-600'">Ideal ~155 chars · <span x-text="metaLen"></span>/160</p>
                    </div>
                    <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                        <input type="checkbox" name="noindex" value="1" {{ old('noindex', $article->noindex) ? 'checked' : '' }} class="rounded bg-zinc-800 border-zinc-700 text-red-500 focus:ring-0">
                        <span>🚫 No-index <span class="text-zinc-500">(sembunyiin dari Google)</span></span>
                    </label>
                </div>

                {{-- Image --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-3" x-data="{ preview: null }">
                    <h2 class="text-sm font-semibold flex items-center gap-2"><svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>Featured image</h2>
                    <template x-if="preview">
                        <img :src="preview" alt="" class="w-full aspect-video object-cover rounded-lg">
                    </template>
                    @if ($article->image)
                        <img src="{{ asset('storage/' . $article->image) }}" alt="" class="w-full aspect-video object-cover rounded-lg" x-show="!preview">
                        <label class="flex items-center gap-2 text-xs text-red-400 cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="rounded bg-zinc-800 border-zinc-700"> Remove current image
                        </label>
                    @endif
                    <input type="file" name="image" accept="image/*" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="w-full text-xs text-zinc-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-zinc-700 file:text-white file:text-xs file:cursor-pointer">
                    <p class="text-[11px] text-zinc-600 mt-1.5">Recommended: 1200×675 px (16:9). Displayed as a 16:9 cover across the site.</p>
                </div>
            </div>
        </div>
    </form>
@endsection