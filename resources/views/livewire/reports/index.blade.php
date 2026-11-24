<div>
    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-200/80 mb-8">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daily Reports History</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $totalReports }} {{ $totalReports === 1 ? 'Report' : 'Reports' }} Total
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Search, filter, preview, and export operational daily reports across properties.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('reports.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Today's Report
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-2xs">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Metric Badges Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Filtered</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $reports->total() }}</p>
            </div>
            <div class="h-9 w-9 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center border border-slate-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Drafts In Progress</p>
                <p class="text-xl font-bold text-amber-600 mt-0.5">{{ $draftsCount }}</p>
            </div>
            <div class="h-9 w-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Completed Reports</p>
                <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ $completedCount }}</p>
            </div>
            <div class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            <!-- Search Keyword -->
            <div class="lg:col-span-4 relative">
                <label for="search" class="block text-xs font-medium text-slate-500 mb-1">Search Keywords</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           id="search"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search date, staff, tasks, notes..."
                           class="block w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                </div>
            </div>

            <!-- Date From -->
            <div class="lg:col-span-2">
                <label for="dateFrom" class="block text-xs font-medium text-slate-500 mb-1">Date From</label>
                <input type="date"
                       id="dateFrom"
                       wire:model.live="dateFrom"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
            </div>

            <!-- Date To -->
            <div class="lg:col-span-2">
                <label for="dateTo" class="block text-xs font-medium text-slate-500 mb-1">Date To</label>
                <input type="date"
                       id="dateTo"
                       wire:model.live="dateTo"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-2">
                <label for="statusFilter" class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select id="statusFilter"
                        wire:model.live="statusFilter"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    <option value="all">All Statuses</option>
                    <option value="draft">Draft only</option>
                    <option value="completed">Completed only</option>
                </select>
            </div>

            <!-- Hotel Filter (For Admin or Multi-Property) -->
            @if(auth()->user()->isAdmin())
                <div class="lg:col-span-2">
                    <label for="hotelFilter" class="block text-xs font-medium text-slate-500 mb-1">Hotel Property</label>
                    <select id="hotelFilter"
                            wire:model.live="hotelFilter"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        <option value="">All Properties</option>
                        @foreach($hotels as $h)
                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Reset Filters Button -->
            <div class="sm:col-span-2 lg:col-span-12 flex items-center justify-end pt-1">
                @if(!empty($search) || !empty($dateFrom) || !empty($dateTo) || $statusFilter !== 'all' || !empty($hotelFilter))
                    <button type="button"
                            wire:click="resetFilters"
                            class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset All Filters
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Reports Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        @if($reports->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/70">
                            <th class="px-4 py-3.5">Report Date</th>
                            <th class="px-4 py-3.5">Hotel Property</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Prepared By</th>
                            <th class="px-4 py-3.5">Completed Date</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($reports as $report)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Report Date -->
                                <td class="px-4 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $report->formatted_date }}
                                    <span class="block text-xs font-normal text-slate-400">{{ $report->day_name }}</span>
                                </td>

                                <!-- Hotel -->
                                <td class="px-4 py-3.5 text-slate-700 font-medium whitespace-nowrap">
                                    {{ $report->hotel->name }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $report->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $report->isCompleted() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>

                                <!-- Creator -->
                                <td class="px-4 py-3.5 text-slate-600 text-xs whitespace-nowrap">
                                    {{ $report->creator?->name ?? 'System' }}
                                </td>

                                <!-- Completed Date -->
                                <td class="px-4 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $report->completed_at ? $report->completed_at->format('d M Y, H:i') : '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <!-- Edit / View Wizard Link -->
                                        <a href="{{ route('reports.wizard', $report) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition-colors"
                                           title="{{ $report->isCompleted() ? 'View details in Wizard' : 'Resume editing draft' }}">
                                            {{ $report->isCompleted() ? 'View' : 'Resume' }}
                                        </a>

                                         <!-- Preview Link -->
                                        <a href="{{ route('reports.preview', $report) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors"
                                           title="Consolidated Document Preview">
                                            Preview
                                        </a>

                                        <!-- WhatsApp Markdown Button -->
                                        <button type="button"
                                                wire:click="openWhatsAppModal({{ $report->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors"
                                                title="WhatsApp Markdown Preview & Copy">
                                            <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 2C6.492 2 2 6.492 2 12.031c0 1.97.57 3.82 1.56 5.39l-1.04 3.81 3.91-1.02c1.51.92 3.28 1.45 5.17 1.45 5.54 0 10.031-4.492 10.031-10.031S17.57 2 12.031 2zm0 18.32c-1.67 0-3.23-.46-4.58-1.27l-.33-.2-2.31.61.62-2.25-.22-.35c-.9-1.42-1.42-3.1-1.42-4.86 0-4.6 3.75-8.35 8.35-8.35s8.35 3.75 8.35 8.35-3.75 8.35-8.35 8.35zm4.58-6.24c-.25-.13-1.48-.73-1.71-.81-.23-.08-.4-.13-.57.13-.17.25-.66.81-.81.98-.15.17-.3.19-.55.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.25-1.49-1.4-1.74-.15-.25-.02-.39.11-.51.11-.11.25-.3.38-.45.13-.15.17-.25.25-.42.08-.17.04-.32-.02-.45-.06-.13-.57-1.37-.78-1.87-.21-.5-.41-.43-.57-.44h-.49c-.17 0-.45.06-.68.32-.23.25-.89.87-.89 2.12s.91 2.46 1.04 2.63c.13.17 1.8 2.75 4.36 3.85.61.26 1.09.42 1.46.54.61.2 1.17.17 1.61.1.49-.07 1.48-.61 1.69-1.2.21-.59.21-1.1.15-1.2-.06-.1-.23-.17-.48-.3z"/>
                                            </svg>
                                            WhatsApp
                                        </button>

                                        <!-- Download DOCX (If completed) -->
                                        @if($report->isCompleted())
                                            <a href="{{ route('reports.export.docx', $report) }}"
                                               class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-200"
                                               title="Download Word Document (.docx)">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 9H15z" />
                                                </svg>
                                            </a>

                                            <!-- Download PDF (If completed) -->
                                            <a href="{{ route('reports.export.pdf', $report) }}"
                                               class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-200"
                                               title="Download PDF Document (.pdf)">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-2v1.5h2c.55 0 1-.45 1-1s-.45-.5-1-.5zm5.5 2h-1.5v-3H15c.55 0 1 .45 1 1v1c0 .55-.45 1-1 1zm-8-6h3c1.1 0 2 .9 2 2s-.9 2-2 2h-1v2H7v-6zm5.5 0h2.5c1.1 0 2 .9 2 2v2c0 1.1-.9 2-2 2h-2.5v-6zm6 0h3v1.5h-1.5v1h1.5v1.5h-1.5V13H18.5v-6z"/>
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Delete Draft (If draft and creator/admin) -->
                                        @if($report->isDraft() && auth()->user()->can('delete', $report))
                                            <button type="button"
                                                    wire:click="deleteReport({{ $report->id }})"
                                                    wire:confirm="Are you sure you want to delete this draft report? This cannot be undone."
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                    title="Delete draft">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="px-4 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $reports->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-800">No reports found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if(!empty($search) || !empty($dateFrom) || !empty($dateTo) || $statusFilter !== 'all' || !empty($hotelFilter))
                        No daily reports match the current filter criteria. Try clearing or relaxing your filters.
                    @else
                        No operational reports have been created yet. Start by generating today's report.
                    @endif
                </p>
                <div class="mt-5 flex items-center justify-center space-x-3">
                    @if(!empty($search) || !empty($dateFrom) || !empty($dateTo) || $statusFilter !== 'all' || !empty($hotelFilter))
                        <button type="button"
                                wire:click="resetFilters"
                                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                            Clear Filters
                        </button>
                    @endif
                    <a href="{{ route('reports.create') }}"
                       class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-teal-700 text-white hover:bg-teal-800 transition-colors">
                        Start Today's Report
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- WhatsApp Markdown Preview Modal -->
    @if($showWhatsAppModal)
        <div x-data="{
            copied: false,
            copySuccessMessage: '',
            copyWhatsApp() {
                const text = $refs.modalWhatsappText.value;
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
                    alert('Copying failed. Please manually select the text and copy.');
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
        }" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeWhatsAppModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 2C6.492 2 2 6.492 2 12.031c0 1.97.57 3.82 1.56 5.39l-1.04 3.81 3.91-1.02c1.51.92 3.28 1.45 5.17 1.45 5.54 0 10.031-4.492 10.031-10.031S17.57 2 12.031 2zm0 18.32c-1.67 0-3.23-.46-4.58-1.27l-.33-.2-2.31.61.62-2.25-.22-.35c-.9-1.42-1.42-3.1-1.42-4.86 0-4.6 3.75-8.35 8.35-8.35s8.35 3.75 8.35 8.35-3.75 8.35-8.35 8.35zm4.58-6.24c-.25-.13-1.48-.73-1.71-.81-.23-.08-.4-.13-.57.13-.17.25-.66.81-.81.98-.15.17-.3.19-.55.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.25-1.49-1.4-1.74-.15-.25-.02-.39.11-.51.11-.11.25-.3.38-.45.13-.15.17-.25.25-.42.08-.17.04-.32-.02-.45-.06-.13-.57-1.37-.78-1.87-.21-.5-.41-.43-.57-.44h-.49c-.17 0-.45.06-.68.32-.23.25-.89.87-.89 2.12s.91 2.46 1.04 2.63c.13.17 1.8 2.75 4.36 3.85.61.26 1.09.42 1.46.54.61.2 1.17.17 1.61.1.49-.07 1.48-.61 1.69-1.2.21-.59.21-1.1.15-1.2-.06-.1-.23-.17-.48-.3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900" id="modal-title">
                                        WhatsApp Report / Markdown Preview
                                    </h3>
                                    <p class="text-xs text-slate-500">
                                        Formatted for direct WhatsApp pasting without header/footer.
                                    </p>
                                </div>
                            </div>
                            <button wire:click="closeWhatsAppModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div x-show="copySuccessMessage" x-cloak class="mt-3 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="copySuccessMessage"></span>
                        </div>

                        <div class="mt-4">
                            <textarea x-ref="modalWhatsappText" readonly class="w-full h-72 font-mono text-xs text-slate-800 bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:ring-1 focus:ring-emerald-500 whitespace-pre leading-relaxed select-all">{{ $whatsAppMarkdown }}</textarea>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-3.5 flex flex-row-reverse gap-2 border-t border-slate-200">
                        <button type="button"
                                @click="copyWhatsApp"
                                :class="copied ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-emerald-700 hover:bg-emerald-800 text-white'"
                                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold shadow-2xs transition-colors">
                            <template x-if="!copied">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy Report
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center text-white">
                                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Copied!
                                </span>
                            </template>
                        </button>
                        <button type="button"
                                wire:click="closeWhatsAppModal"
                                class="inline-flex items-center justify-center rounded-lg bg-white border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
