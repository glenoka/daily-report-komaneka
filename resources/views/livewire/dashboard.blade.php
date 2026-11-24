<div>
    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-200/80 mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operations Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">
                Overview of daily reports for <span class="font-medium text-slate-700">{{ auth()->user()->hotel?->name ?? 'Hotel' }}</span>
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <button wire:click="createTodayReport"
                    class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Today's Report
            </button>
        </div>
    </div>

    @if (session()->has('info'))
        <div class="mb-6 rounded-lg bg-teal-50 border border-teal-200 p-4 text-sm text-teal-800">
            {{ session('info') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Reports This Month -->
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 mr-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Reports This Month</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalThisMonth }}</p>
            </div>
        </div>

        <!-- In-Progress Drafts -->
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 mr-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Drafts In Progress</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $draftsCount }}</p>
            </div>
        </div>

        <!-- Completed Reports -->
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mr-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Completed Reports</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $completedCount }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Reports Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Recent Reports</h2>
                <p class="text-xs text-slate-400">Latest activity across properties</p>
            </div>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center text-xs font-semibold text-teal-700 hover:text-teal-900 transition-colors">
                View All Reports & History
                <svg class="w-3.5 h-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        @if(isset($recentReports) && $recentReports->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50">
                            <th class="px-4 py-3">Report Date</th>
                            <th class="px-4 py-3">Hotel</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($recentReports as $rep)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $rep->formatted_date }}
                                    <span class="block text-xs font-normal text-slate-400">{{ $rep->day_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $rep->hotel->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $rep->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ ucfirst($rep->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $rep->creator?->name ?? 'System' }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <a href="{{ route('reports.wizard', $rep) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition-colors">
                                            {{ $rep->isCompleted() ? 'View' : 'Resume' }}
                                        </a>

                                        <a href="{{ route('reports.preview', $rep) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors"
                                           title="Consolidated Document Preview">
                                            Preview
                                        </a>

                                        @if($rep->isCompleted())
                                            <a href="{{ route('reports.export.docx', $rep) }}"
                                               class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                               title="Download Word (.docx)">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 9H15z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('reports.export.pdf', $rep) }}"
                                               class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                               title="Download PDF (.pdf)">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-2v1.5h2c.55 0 1-.45 1-1s-.45-.5-1-.5zm5.5 2h-1.5v-3H15c.55 0 1 .45 1 1v1c0 .55-.45 1-1 1zm-8-6h3c1.1 0 2 .9 2 2s-.9 2-2 2h-1v2H7v-6zm5.5 0h2.5c1.1 0 2 .9 2 2v2c0 1.1-.9 2-2 2h-2.5v-6zm6 0h3v1.5h-1.5v1h1.5v1.5h-1.5V13H18.5v-6z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">No reports generated yet</p>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Begin by creating today's report. The multi-tab wizard will guide you through all operational sections.
                </p>
                <div class="mt-5">
                    <button wire:click="createTodayReport"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 transition-colors">
                        Start Today's Report
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
