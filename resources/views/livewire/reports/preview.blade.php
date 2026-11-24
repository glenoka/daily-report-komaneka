<div class="max-w-5xl mx-auto pb-16">
    <!-- Notifications & Flash Messages -->
    <div class="print:hidden space-y-3 mb-6">
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        @if ($exportError)
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $exportError }}</span>
                </div>
                @if ($failedFormat)
                    <button wire:click="retryExport('{{ $failedFormat }}')"
                            class="px-3 py-1 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition-colors">
                        Retry {{ strtoupper($failedFormat) }} Export
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Top Bar: Navigation, Completion status, Downloads, Finalization -->
    <div class="print:hidden mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <a href="{{ route('reports.wizard', $report) }}" class="text-slate-400 hover:text-slate-600 transition-colors" title="Back to Wizard">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Consolidated Document Preview
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $report->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ ucfirst($previewData['status']) }}
                </span>
            </div>
            <p class="text-sm text-slate-500">
                Official document format for <span class="font-semibold text-slate-700">{{ $previewData['hotel_name'] }}</span>
                <span class="mx-2">•</span>
                <span>{{ $previewData['day_name'] }}, {{ $previewData['report_date'] }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $previewData['is_complete'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                <span class="w-2 h-2 rounded-full mr-1.5 {{ $previewData['is_complete'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                {{ $previewData['filled_sections'] }} / {{ $previewData['total_sections'] }} Sections Filled
            </span>

            <!-- Download DOCX Button -->
            <a href="{{ route('reports.export.docx', $report) }}"
               class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-2xs transition-colors"
               title="Export and download as Word (.docx)">
                <svg class="w-4 h-4 mr-1.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 9H15z" />
                </svg>
                Download DOCX
            </a>

            <!-- Download PDF Button -->
            <a href="{{ route('reports.export.pdf', $report) }}"
               class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-2xs transition-colors"
               title="Export and download as PDF (.pdf)">
                <svg class="w-4 h-4 mr-1.5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-2v1.5h2c.55 0 1-.45 1-1s-.45-.5-1-.5zm5.5 2h-1.5v-3H15c.55 0 1 .45 1 1v1c0 .55-.45 1-1 1zm-8-6h3c1.1 0 2 .9 2 2s-.9 2-2 2h-1v2H7v-6zm5.5 0h2.5c1.1 0 2 .9 2 2v2c0 1.1-.9 2-2 2h-2.5v-6zm6 0h3v1.5h-1.5v1h1.5v1.5h-1.5V13H18.5v-6z"/>
                </svg>
                Download PDF
            </a>

            <!-- Print / PDF Browser Button -->
            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-2xs transition-colors">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>

            @if ($report->isDraft())
                @can('finalize', $report)
                    <!-- Create Report (Finalize) Button -->
                    <button wire:click="finalizeReport"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-teal-700 hover:bg-teal-800 rounded-lg shadow-2xs transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="finalizeReport" class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Report
                        </span>
                        <span wire:loading wire:target="finalizeReport" class="inline-flex items-center">
                            <svg class="animate-spin -ml-0.5 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Finalizing...
                        </span>
                    </button>
                @endcan
            @else
                @can('reopen', $report)
                    <!-- Revise Report (Reopen draft) -->
                    <button wire:click="reopenReport"
                            wire:confirm="Reopening will set the report back to Draft so it can be edited. Continue?"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg shadow-2xs transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Revise Report
                    </button>
                @endcan
            @endif

            <a href="{{ route('reports.wizard', $report) }}"
               class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Back to Editing
            </a>
        </div>
    </div>

    <!-- Document-like Paper Sheet (Centered) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-md p-8 md:p-14 max-w-4xl mx-auto print:shadow-none print:border-none print:p-0 print:max-w-none font-serif-doc text-slate-900">
        
        <!-- Sections in Official Template Order (Report Body Begins Directly) -->
        <div class="space-y-8">
            @foreach($previewData['tabs'] as $tIndex => $tab)
                <section class="group relative pt-4 pb-2 border-t border-slate-100 first:border-t-0 first:pt-0">
                    <!-- Tab / Main Section Heading with Edit Link -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm md:text-base font-bold text-slate-950 tracking-wide uppercase flex items-center">
                            <span class="mr-2">{{ $tIndex + 1 }}.</span>
                            <span>{{ $tab['title'] }}</span>
                        </h3>

                        <a href="{{ $tab['edit_url'] }}"
                           class="print:hidden text-xs font-semibold text-teal-700 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 px-2.5 py-1 rounded transition-colors inline-flex items-center opacity-80 group-hover:opacity-100"
                           title="Edit this section in wizard">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Section
                        </a>
                    </div>

                    <!-- Individual Fields / Subsections -->
                    <div class="space-y-5 pl-4 md:pl-5">
                        @foreach($tab['sections'] as $sec)
                            <div class="space-y-1.5">
                                @if(count($tab['sections']) > 1 || $tab['key'] === 'departmental_activities')
                                    <!-- Field Title -->
                                    <h4 class="text-xs md:text-sm font-bold text-slate-900">
                                        {{ $sec['title'] }}
                                    </h4>
                                @endif

                                @if($sec['field_type'] === 'text')
                                    <!-- Paragraph Rendering -->
                                    @if(!$sec['is_empty'])
                                        <div class="text-xs md:text-sm text-slate-800 leading-relaxed whitespace-pre-line pl-1">
                                            {{ $sec['content'] }}
                                        </div>
                                    @else
                                        <p class="text-xs italic text-slate-400 pl-1">
                                            (No guest experience or concern reported)
                                        </p>
                                    @endif

                                @else
                                    <!-- Bullet List Rendering -->
                                    @if(!$sec['is_empty'])
                                        <ul class="space-y-1 text-xs md:text-sm text-slate-800 leading-relaxed list-none pl-1">
                                            @foreach($sec['items'] as $item)
                                                <li class="flex items-start">
                                                    <span class="inline-block w-4 text-slate-700 flex-shrink-0 font-bold">•</span>
                                                    <span class="flex-1">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs italic text-slate-400 pl-1">
                                            (No items reported)
                                        </p>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <!-- WhatsApp Report / Markdown Preview Section -->
    <div x-data="{
        copied: false,
        copySuccessMessage: '',
        expanded: true,
        copyWhatsApp() {
            const text = $refs.whatsappContent.value;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopied();
                }).catch(() => {
                    this.fallbackCopy(text);
                });
            } else {
                this.fallbackCopy(text);
            }
        },
        fallbackCopy(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                this.showCopied();
            } catch (e) {
                alert('Copying failed. Please manually select the text below and copy.');
            }
            document.body.removeChild(ta);
        },
        showCopied() {
            this.copied = true;
            this.copySuccessMessage = 'Report copied to clipboard!';
            setTimeout(() => {
                this.copied = false;
                this.copySuccessMessage = '';
            }, 3000);
        }
    }" class="mt-8 bg-white rounded-2xl border border-slate-200/90 shadow-md p-6 md:p-8 max-w-4xl mx-auto print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200 gap-3">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 2C6.492 2 2 6.492 2 12.031c0 1.97.57 3.82 1.56 5.39l-1.04 3.81 3.91-1.02c1.51.92 3.28 1.45 5.17 1.45 5.54 0 10.031-4.492 10.031-10.031S17.57 2 12.031 2zm0 18.32c-1.67 0-3.23-.46-4.58-1.27l-.33-.2-2.31.61.62-2.25-.22-.35c-.9-1.42-1.42-3.1-1.42-4.86 0-4.6 3.75-8.35 8.35-8.35s8.35 3.75 8.35 8.35-3.75 8.35-8.35 8.35zm4.58-6.24c-.25-.13-1.48-.73-1.71-.81-.23-.08-.4-.13-.57.13-.17.25-.66.81-.81.98-.15.17-.3.19-.55.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.25-1.49-1.4-1.74-.15-.25-.02-.39.11-.51.11-.11.25-.3.38-.45.13-.15.17-.25.25-.42.08-.17.04-.32-.02-.45-.06-.13-.57-1.37-.78-1.87-.21-.5-.41-.43-.57-.44h-.49c-.17 0-.45.06-.68.32-.23.25-.89.87-.89 2.12s.91 2.46 1.04 2.63c.13.17 1.8 2.75 4.36 3.85.61.26 1.09.42 1.46.54.61.2 1.17.17 1.61.1.49-.07 1.48-.61 1.69-1.2.21-.59.21-1.1.15-1.2-.06-.1-.23-.17-.48-.3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        WhatsApp Report / Markdown Preview
                    </h3>
                    <p class="text-xs text-slate-500">
                        Formatted with single-asterisk bold and clean list markers for direct WhatsApp pasting.
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button"
                        @click="copyWhatsApp"
                        :class="copied ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300'"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold shadow-2xs transition-all">
                    <template x-if="!copied">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy WhatsApp Report
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="inline-flex items-center text-white">
                            <svg class="w-4 h-4 mr-1.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Report Copied!
                        </span>
                    </template>
                </button>

                <button type="button"
                        @click="expanded = !expanded"
                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg border border-slate-200 hover:bg-slate-50"
                        :title="expanded ? 'Collapse preview' : 'Expand preview'">
                    <svg class="w-4 h-4 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="copySuccessMessage" x-cloak class="mt-3 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="copySuccessMessage"></span>
        </div>

        <div x-show="expanded" class="mt-4">
            <textarea x-ref="whatsappContent" readonly class="w-full h-80 font-mono text-xs text-slate-800 bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:ring-1 focus:ring-emerald-500 whitespace-pre leading-relaxed select-all">{{ $whatsAppMarkdown }}</textarea>
        </div>
    </div>
</div>
