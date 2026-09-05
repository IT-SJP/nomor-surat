@props([
    'items',
    'options' => [10, 15, 25, 50, 100],
    'label' => 'data',
])

<div class="px-6 py-4 bg-slate-50/60 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4">
    <!-- Left: Rows per page selector + Showing items counter -->
    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-sm text-slate-500 dark:text-slate-400 font-medium order-2 sm:order-1">
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Item per halaman:</span>
            <div class="relative">
                <select 
                    wire:model.live="perPage"
                    class="select select-bordered select-sm rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:border-primary-500 focus:outline-none cursor-pointer">
                    @foreach ($options as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>
        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
            Menampilkan <span class="text-slate-900 dark:text-white font-bold">{{ $items->firstItem() ?? 0 }}</span> - <span class="text-slate-900 dark:text-white font-bold">{{ $items->lastItem() ?? 0 }}</span> dari <span class="text-slate-900 dark:text-white font-bold">{{ $items->total() }}</span> {{ $label }}
        </div>
    </div>

    <!-- Right: Pagination Links -->
    <div class="order-1 sm:order-2 flex justify-center">
        {{ $items->links('components.daisyui-pagination') }}
    </div>
</div>
