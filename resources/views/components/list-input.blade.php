@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => "Paste or type list items here (bullets and numbers are cleaned automatically)...",
    'rows' => 5,
    'required' => false,
    'helper' => null,
])

<div x-data="{
    rawUndo: '',
    showCleanedNotice: false,
    timer: null,
    itemCount: 0,
    updateCount() {
        const val = this.$refs.textarea.value.trim();
        this.itemCount = val ? val.split('\n').filter(l => l.trim().length > 0).length : 0;
    },
    undoPaste() {
        if (this.rawUndo) {
            this.$refs.textarea.value = this.rawUndo;
            this.$refs.textarea.dispatchEvent(new Event('input', { bubbles: true }));
            this.rawUndo = '';
            this.showCleanedNotice = false;
        }
    }
}" 
x-init="
    updateCount();
    $refs.textarea.addEventListener('input', () => updateCount());
    window.addEventListener('list-cleaned', (e) => {
        if (e.detail.targetId === $refs.textarea.id || !e.detail.targetId) {
            rawUndo = $refs.textarea.dataset.rawPaste || '';
            showCleanedNotice = true;
            clearTimeout(timer);
            timer = setTimeout(() => { showCleanedNotice = false; }, 6000);
            updateCount();
        }
    });
" 
class="space-y-1.5">
    <div class="flex items-center justify-between">
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-800">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
        
        <div class="flex items-center space-x-2 text-xs">
            <!-- Item count pill -->
            <span class="text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-medium" x-text="`${itemCount} ${itemCount === 1 ? 'item' : 'items'}`"></span>
        </div>
    </div>

    @if($helper)
        <p class="text-xs text-slate-500">{{ $helper }}</p>
    @endif

    <div class="relative">
        <textarea
            x-ref="textarea"
            id="{{ $name }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            data-list-input="true"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'list-input-paste block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-slate-900 text-sm leading-relaxed placeholder-slate-400 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 font-normal']) }}
        >{{ $slot->isNotEmpty() ? $slot : $value }}</textarea>

        <!-- Cleaned notice with Undo button -->
        <div x-show="showCleanedNotice" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute bottom-3 right-3 flex items-center bg-slate-900/90 text-white text-xs px-2.5 py-1.5 rounded-md shadow-md backdrop-blur-sm space-x-2">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Bullet markers cleaned</span>
            <button type="button" @click="undoPaste" class="text-teal-300 hover:text-white underline font-semibold ml-1">
                Undo
            </button>
        </div>
    </div>
</div>
