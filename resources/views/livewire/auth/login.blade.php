<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="h-12 w-12 rounded-xl bg-teal-700 flex items-center justify-center text-white shadow-md">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900">
            Daily Reporting System
        </h2>
        <p class="mt-1 text-center text-sm text-slate-500">
            Hospitality Operations Management Portal
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200/80 rounded-2xl sm:px-10">
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <div class="mt-1">
                        <input wire:model="email" id="email" type="email" autocomplete="email" required
                               class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 placeholder-slate-400 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 sm:text-sm">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div class="mt-1">
                        <input wire:model="password" id="password" type="password" autocomplete="current-password" required
                               class="block w-full rounded-lg border border-slate-300 px-3.5 py-2 text-slate-900 placeholder-slate-400 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input wire:model="remember" id="remember" type="checkbox"
                               class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <label for="remember" class="ml-2 block text-sm text-slate-600">Remember me</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 transition-colors">
                        <span wire:loading.remove wire:target="login">Sign In</span>
                        <span wire:loading wire:target="login">Signing in...</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-500 text-center">
                <span class="font-semibold text-slate-600">Demo Accounts:</span><br>
                Admin: <code class="text-teal-700">admin@hotel.com</code> / <code class="text-slate-700">password</code><br>
                Staff: <code class="text-teal-700">staff@hotel.com</code> / <code class="text-slate-700">password</code>
            </div>
        </div>
    </div>
</div>
