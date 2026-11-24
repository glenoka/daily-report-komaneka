<div x-data="{
    isDirty: false,
    init() {
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    this.isDirty = false;
                });
            });
        });
    }
}"
@input.window="isDirty = true"
class="max-w-5xl mx-auto pb-12">
    <!-- Top Header: Hotel, Date, Status, Back -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600 transition-colors" title="Back to Dashboard">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Daily Report Wizard
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $report->isCompleted() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ ucfirst($report->status) }}
                </span>
            </div>
            <p class="text-sm text-slate-500">
                Property: <span class="font-semibold text-slate-700">{{ $report->hotel->name }}</span>
                <span class="mx-2">•</span>
                Date: <span class="font-semibold text-slate-700">{{ $report->day_name }}, {{ $report->formatted_date }}</span>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if($saveStatus === 'saving')
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                    <svg class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Auto-saving...
                </span>
            @elseif($saveStatus === 'error')
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Save failed</span>
                    <button type="button" wire:click="retrySave" class="underline text-rose-800 hover:text-rose-950 font-bold ml-1">
                        Retry
                    </button>
                </div>
            @elseif($lastSavedAt)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 text-emerald-500 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Auto-saved at {{ $lastSavedAt }}
                </span>
            @endif

            <a href="{{ route('reports.preview', $report) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Review Report
            </a>

            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Alert / Feedback Messages -->
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-teal-50 border border-teal-200 p-4 text-sm text-teal-800 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-teal-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-center space-x-2">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($report->isCompleted() && !auth()->user()->isAdmin())
        <div class="mb-6 rounded-lg bg-slate-100 border border-slate-300 p-4 text-sm text-slate-700 flex items-center space-x-2">
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>This report is finalized and completed. You are viewing it in read-only mode.</span>
        </div>
    @endif

    <!-- 5-Tab Stepper Navigation -->
    <div class="mb-8 bg-white rounded-xl border border-slate-200 p-2 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-1">
            @foreach($tabs as $index => $tab)
                @php
                    $tabKey = $tab['key'];
                    $isActive = ($currentTab === $tabKey);
                    $status = $this->getTabStatus($tabKey);
                @endphp
                <button type="button"
                        wire:click="setTab('{{ $tabKey }}')"
                        class="flex flex-col items-start p-3 rounded-lg text-left transition-all relative border {{ $isActive ? 'bg-teal-50/70 border-teal-600 shadow-xs' : 'border-transparent hover:bg-slate-50' }}">
                    <div class="flex items-center justify-between w-full mb-1">
                        <span class="text-xs font-bold {{ $isActive ? 'text-teal-700' : 'text-slate-400' }}">
                            STEP {{ $index + 1 }}
                        </span>
                        
                        <!-- Status indicator dot/pill -->
                        @if($status === 'saved')
                            <span class="inline-flex items-center text-[11px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200" title="Saved">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                Saved
                            </span>
                        @elseif($status === 'in_progress')
                            <span class="inline-flex items-center text-[11px] font-medium text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200" title="In Progress">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span>
                                In Progress
                            </span>
                        @else
                            <span class="inline-flex items-center text-[11px] font-medium text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded" title="Not Started">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1"></span>
                                Not Started
                            </span>
                        @endif
                    </div>

                    <span class="text-xs font-semibold {{ $isActive ? 'text-teal-950 font-bold' : 'text-slate-700' }} line-clamp-1">
                        {{ $tab['title'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Active Tab Content Area -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8">
        
        <!-- Tab 1: General Information -->
        @if($currentTab === 'general_information')
            <div class="space-y-8">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">1. General Information</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Structured metrics and operational forecast. The system automatically formats your data into standard hotel report bullet points.
                    </p>
                </div>

                <!-- Group 1: Today's Overview -->
                <div class="bg-slate-50/60 rounded-xl p-6 border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-5">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">1.1</span>
                            <h3 class="text-base font-bold text-slate-900">Today's Overview</h3>
                        </div>
                        <span class="text-xs text-slate-400">Structured Data Entry</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <!-- Occupancy (%) -->
                        <div>
                            <label for="overview_occupancy" class="block text-xs font-semibold text-slate-700 mb-1">
                                Occupancy (%)
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       id="overview_occupancy"
                                       wire:model.live.debounce.300ms="overviewData.occupancy"
                                       placeholder="e.g. 20.00"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-10 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rooms Occupied -->
                        <div>
                            <label for="overview_rooms_occupied" class="block text-xs font-semibold text-slate-700 mb-1">
                                Rooms Occupied
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       min="0"
                                       id="overview_rooms_occupied"
                                       wire:model.live.debounce.300ms="overviewData.rooms_occupied"
                                       placeholder="e.g. 15"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">Rooms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Check-ins -->
                        <div>
                            <label for="overview_check_ins" class="block text-xs font-semibold text-slate-700 mb-1">
                                Check-ins
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       min="0"
                                       id="overview_check_ins"
                                       wire:model.live.debounce.300ms="overviewData.check_ins"
                                       placeholder="e.g. 7"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">Rooms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Check-outs -->
                        <div>
                            <label for="overview_check_outs" class="block text-xs font-semibold text-slate-700 mb-1">
                                Check-outs
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       min="0"
                                       id="overview_check_outs"
                                       wire:model.live.debounce.300ms="overviewData.check_outs"
                                       placeholder="e.g. 7"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">Rooms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Number of Guests (Adults + Children) -->
                        <div class="lg:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Number of Guests
                                </label>
                                <span class="text-xs font-semibold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                                    Total: {{ $this->guestsTotal }} Pax
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative rounded-lg shadow-2xs">
                                    <input type="number"
                                           min="0"
                                           id="overview_adults"
                                           wire:model.live.debounce.300ms="overviewData.guests_adults"
                                           placeholder="e.g. 31"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-xs font-medium text-slate-400">Adults</span>
                                    </div>
                                </div>
                                <div class="relative rounded-lg shadow-2xs">
                                    <input type="number"
                                           min="0"
                                           id="overview_children"
                                           wire:model.live.debounce.300ms="overviewData.guests_children"
                                           placeholder="e.g. 2"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-xs font-medium text-slate-400">Children</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Out of Order (OOO) Section -->
                        <div class="md:col-span-2 lg:col-span-3 pt-3 border-t border-slate-200/60">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Out of Order (OOO)
                                </label>
                                @if($this->oooCount > 0)
                                    <span class="text-[11px] font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        {{ $this->oooCount }} {{ $this->oooCount === 1 ? 'Room' : 'Rooms' }} OOO
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Optional (leave empty if none)</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="overviewData.ooo_rooms"
                                           placeholder="Room numbers, e.g. 2310, 2321, 3311"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <p class="text-[11px] text-slate-400 mt-1">Separate room numbers with commas. '#' will be formatted automatically.</p>
                                </div>
                                <div>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="overviewData.ooo_reason"
                                           placeholder="Reason, e.g. Preventive Maintenance Program"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <p class="text-[11px] text-slate-400 mt-1">Brief explanation of out-of-order status.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Property Tours & Manager on Duty -->
                        <div class="md:col-span-2 lg:col-span-3 pt-3 border-t border-slate-200/60 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="overview_property_tours" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Property Tours (Room #)
                                </label>
                                <input type="text"
                                       id="overview_property_tours"
                                       wire:model.live.debounce.300ms="overviewData.property_tours"
                                       placeholder="e.g. 2311, 4103"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <p class="text-[11px] text-slate-400 mt-1">Inspected or show rooms. Omit if no tours took place.</p>
                            </div>

                            <div>
                                <label for="overview_mod" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Manager on Duty (MOD)
                                </label>
                                <input type="text"
                                       id="overview_mod"
                                       wire:model.live.debounce.300ms="overviewData.manager_on_duty"
                                       placeholder="e.g. Ariawan, Agung Janardana, Laksmi"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <p class="text-[11px] text-slate-400 mt-1">Names separated by commas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Forecast for Tomorrow -->
                <div class="bg-slate-50/60 rounded-xl p-6 border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-5">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">1.2</span>
                            <h3 class="text-base font-bold text-slate-900">Forecast for Tomorrow</h3>
                        </div>
                        <span class="text-xs text-slate-400">Structured Data Entry</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <!-- Projected Occupancy (%) -->
                        <div>
                            <label for="forecast_occupancy" class="block text-xs font-semibold text-slate-700 mb-1">
                                Projected Occupancy (%)
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       id="forecast_occupancy"
                                       wire:model.live.debounce.300ms="forecastData.projected_occupancy"
                                       placeholder="e.g. 16.00"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-10 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rooms Projected -->
                        <div>
                            <label for="forecast_rooms_projected" class="block text-xs font-semibold text-slate-700 mb-1">
                                Rooms Projected
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       min="0"
                                       id="forecast_rooms_projected"
                                       wire:model.live.debounce.300ms="forecastData.rooms_projected"
                                       placeholder="e.g. 12"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">Rooms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Expected Check-ins (Optional) -->
                        <div>
                            <label for="forecast_check_ins" class="block text-xs font-semibold text-slate-700 mb-1">
                                Expected Check-ins <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <div class="relative rounded-lg shadow-2xs">
                                <input type="number"
                                       min="0"
                                       id="forecast_check_ins"
                                       wire:model.live.debounce.300ms="forecastData.expected_check_ins"
                                       placeholder="e.g. 5"
                                       class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 pr-16 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-xs font-medium text-slate-400">Rooms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Out of Order (OOO) for Tomorrow -->
                        <div class="md:col-span-2 lg:col-span-3 pt-3 border-t border-slate-200/60">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Out of Order (OOO) for Tomorrow
                                </label>
                                @if($this->forecastOooCount > 0)
                                    <span class="text-[11px] font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        {{ $this->forecastOooCount }} {{ $this->forecastOooCount === 1 ? 'Room' : 'Rooms' }} OOO
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Optional (leave empty if none)</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="forecastData.ooo_rooms"
                                           placeholder="Room numbers, e.g. 2310, 2321, 3311"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <p class="text-[11px] text-slate-400 mt-1">Separate room numbers with commas.</p>
                                </div>
                                <div>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="forecastData.ooo_reason"
                                           placeholder="Reason, e.g. Preventive Maintenance Program"
                                           class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 text-sm focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700" />
                                    <p class="text-[11px] text-slate-400 mt-1">Brief reason for out-of-order status.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Generated Report Output Preview -->
                <div class="bg-white rounded-xl p-6 border border-teal-200 shadow-2xs">
                    <div class="flex items-center space-x-2 pb-3 border-b border-slate-100 mb-4">
                        <svg class="w-4 h-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900">
                            Live Report Output Preview
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <!-- Today's Overview Preview -->
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200/80">
                            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-2.5 pb-1 border-b border-slate-200">
                                Today's Overview
                            </h5>
                            @if(!empty($this->liveOverviewLines))
                                <ul class="space-y-1.5 text-xs text-slate-800">
                                    @foreach($this->liveOverviewLines as $line)
                                        <li class="flex items-start">
                                            <span class="text-teal-600 font-bold mr-2">•</span>
                                            <span>{{ $line }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs italic text-slate-400">Enter metrics above to generate bullet points...</p>
                            @endif
                        </div>

                        <!-- Forecast for Tomorrow Preview -->
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200/80">
                            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-2.5 pb-1 border-b border-slate-200">
                                Forecast for Tomorrow
                            </h5>
                            @if(!empty($this->liveForecastLines))
                                <ul class="space-y-1.5 text-xs text-slate-800">
                                    @foreach($this->liveForecastLines as $line)
                                        <li class="flex items-start">
                                            <span class="text-teal-600 font-bold mr-2">•</span>
                                            <span>{{ $line }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs italic text-slate-400">Enter forecast metrics above to generate bullet points...</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        <!-- Tab 2: Guest Experience & Concern -->
        @elseif($currentTab === 'guest_experience')
            <div class="space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">2. Guest Experience & Concern</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Free-form paragraph text describing guest feedback, issues, and guest satisfaction remarks. Preserves line breaks and formatting as entered.
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="guest_experience" class="block text-sm font-semibold text-slate-800">
                        Guest Experience & Concern
                    </label>
                    <p class="text-xs text-slate-500">
                        Note: This is a paragraph narrative field. Content is stored as paragraph text with line breaks preserved (list normalization is not applied).
                    </p>
                    <textarea
                        id="guest_experience"
                        wire:model.live.debounce.1500ms="formData.guest_experience"
                        rows="8"
                        class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-slate-900 text-sm leading-relaxed placeholder-slate-400 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 font-normal"
                        placeholder="Enter guest feedback, compliments, complaints, and resolution details as paragraphs..."
                    >{{ $formData['guest_experience'] ?? '' }}</textarea>
                </div>
            </div>

        <!-- Tab 3: Departmental Activities -->
        @elseif($currentTab === 'departmental_activities')
            <div class="space-y-6" data-departmental-activities="true">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">3. Departmental Activities</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Detailed operational tasks and activities by department. Each department is stored independently.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Engineering -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">1</span>
                            <h3 class="text-sm font-bold text-slate-900">Engineering</h3>
                        </div>
                        <x-list-input
                            name="engineering"
                            label="Engineering Activities"
                            wire:model.live.debounce.1500ms="formData.engineering"
                            :value="$formData['engineering'] ?? ''"
                            placeholder="Engineering tasks, repairs, HVAC, generator logs..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 2. Food & Beverage — Production -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">2</span>
                            <h3 class="text-sm font-bold text-slate-900">Food & Beverage — Production</h3>
                        </div>
                        <x-list-input
                            name="fb_production"
                            label="F&B Production (Kitchen / Bakery)"
                            wire:model.live.debounce.1500ms="formData.fb_production"
                            :value="$formData['fb_production'] ?? ''"
                            placeholder="Kitchen prep, breakfast/dinner banquet production..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 3. Food & Beverage — Service -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">3</span>
                            <h3 class="text-sm font-bold text-slate-900">Food & Beverage — Service</h3>
                        </div>
                        <x-list-input
                            name="fb_service"
                            label="F&B Service (Restaurant / Bar / Room Service)"
                            wire:model.live.debounce.1500ms="formData.fb_service"
                            :value="$formData['fb_service'] ?? ''"
                            placeholder="Restaurant seating, events, bar service updates..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 4. Housekeeping -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">4</span>
                            <h3 class="text-sm font-bold text-slate-900">Housekeeping</h3>
                        </div>
                        <x-list-input
                            name="housekeeping"
                            label="Housekeeping & Rooms"
                            wire:model.live.debounce.1500ms="formData.housekeeping"
                            :value="$formData['housekeeping'] ?? ''"
                            placeholder="Room turnover, linen inventory, public area cleaning..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 5. SPA -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">5</span>
                            <h3 class="text-sm font-bold text-slate-900">SPA</h3>
                        </div>
                        <x-list-input
                            name="spa"
                            label="SPA Treatments & Operations"
                            wire:model.live.debounce.1500ms="formData.spa"
                            :value="$formData['spa'] ?? ''"
                            placeholder="Spa appointments, product inventories, guest wellness..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 6. Security -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">6</span>
                            <h3 class="text-sm font-bold text-slate-900">Security</h3>
                        </div>
                        <x-list-input
                            name="security"
                            label="Security & Safety Reports"
                            wire:model.live.debounce.1500ms="formData.security"
                            :value="$formData['security'] ?? ''"
                            placeholder="Patrol rounds, access checks, perimeter monitoring..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>

                    <!-- 7. IT -->
                    <div class="bg-slate-50/70 rounded-xl p-5 border border-slate-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-teal-100 text-teal-800 font-bold text-xs">7</span>
                            <h3 class="text-sm font-bold text-slate-900">IT</h3>
                        </div>
                        <x-list-input
                            name="it"
                            label="IT Systems & Network"
                            wire:model.live.debounce.1500ms="formData.it"
                            :value="$formData['it'] ?? ''"
                            placeholder="Network status, PMS / POS systems, hardware maintenance..."
                            rows="5"
                            data-department-input="true"
                        />
                    </div>
                </div>
            </div>

        <!-- Tab 4: Operational Updates -->
        @elseif($currentTab === 'operational_updates')
            <div class="space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">4. Operational Updates</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        General property operational updates, housekeeping status, or facilities announcements.
                    </p>
                </div>

                <div class="space-y-6">
                    <x-list-input
                        name="operational_updates"
                        label="Operational Updates"
                        wire:model.live.debounce.1500ms="formData.operational_updates"
                        :value="$formData['operational_updates'] ?? ''"
                        placeholder="Paste or type general operational updates..."
                        helper="Inter-department notices, ongoing room deep-cleaning, pool chemical treatments, etc."
                        rows="7"
                    />
                </div>
            </div>

        <!-- Tab 5: Activities and Works Plan -->
        @elseif($currentTab === 'activities_works_plan')
            <div class="space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">5. Activities and Works Plan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Upcoming activities, preventative maintenance works, and scheduled operational plans.
                    </p>
                </div>

                <div class="space-y-6">
                    <x-list-input
                        name="activities_works_plan"
                        label="Activities and Works Plan"
                        wire:model.live.debounce.1500ms="formData.activities_works_plan"
                        :value="$formData['activities_works_plan'] ?? ''"
                        placeholder="Paste or type planned activities and works..."
                        helper="Upcoming projects, vendor visits, planned inspections, or scheduled system upgrades."
                        rows="7"
                    />
                </div>
            </div>
        @endif

        <!-- Action / Navigation Footer -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Left: Previous button -->
            <div>
                @if($this->hasPreviousTab)
                    <button type="button"
                            wire:click="previous"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                @endif
            </div>

            <!-- Right: Save & Save & Continue buttons -->
            <div class="flex items-center space-x-3">
                @if($report->isDraft() || auth()->user()->isAdmin())
                    <!-- Save only current tab -->
                    <button type="button"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs transition-colors disabled:opacity-50">
                        <span wire:loading wire:target="save" class="mr-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-slate-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        Save
                    </button>

                    <!-- Save & Continue to next tab -->
                    <button type="button"
                            wire:click="saveAndContinue"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-teal-700 rounded-lg hover:bg-teal-800 shadow-xs transition-colors focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading wire:target="saveAndContinue" class="mr-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        @if($this->hasNextTab)
                            Save & Continue
                            <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        @else
                            Save & Finish
                        @endif
                    </button>

                    @if(!$this->hasNextTab)
                        <a href="{{ route('reports.preview', $report) }}"
                           class="inline-flex items-center px-5 py-2 text-sm font-semibold text-teal-800 bg-teal-50 border border-teal-300 rounded-lg hover:bg-teal-100 shadow-xs transition-colors">
                            Review Report
                            <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
