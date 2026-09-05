@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        {{-- Versi desktop/wide --}}
        <div class="join hidden sm:inline-flex border border-slate-200 dark:border-slate-700 shadow-2xs rounded-lg overflow-hidden bg-white dark:bg-slate-800">
            {{-- Tombol Sebelumnya («) --}}
            @if ($paginator->onFirstPage())
                <button type="button" aria-disabled="true" aria-label="Sebelumnya" class="join-item btn btn-sm btn-disabled bg-slate-50 dark:bg-slate-800/60 text-slate-300 dark:text-slate-600 border-none" disabled>
                    «
                </button>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="join-item btn btn-sm bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-none hover:text-primary-600 dark:hover:text-primary-400 font-medium" aria-label="Sebelumnya">
                    «
                </button>
            @endif

            {{-- Elemen Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator "..." --}}
                @if (is_string($element))
                    <button type="button" aria-disabled="true" class="join-item btn btn-sm btn-disabled bg-slate-50 dark:bg-slate-800/60 text-slate-400 dark:text-slate-500 border-none" disabled>
                        {{ $element }}
                    </button>
                @endif

                {{-- Array Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-desktop-{{ $paginator->getPageName() }}-page{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <button type="button" class="join-item btn btn-sm btn-active bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold border-none shadow-xs" aria-current="page">
                                    {{ $page }}
                                </button>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="join-item btn btn-sm bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-none hover:text-primary-600 dark:hover:text-primary-400 font-medium" aria-label="Halaman {{ $page }}">
                                    {{ $page }}
                                </button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya (») --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="join-item btn btn-sm bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-none hover:text-primary-600 dark:hover:text-primary-400 font-medium" aria-label="Selanjutnya">
                    »
                </button>
            @else
                <button type="button" aria-disabled="true" aria-label="Selanjutnya" class="join-item btn btn-sm btn-disabled bg-slate-50 dark:bg-slate-800/60 text-slate-300 dark:text-slate-600 border-none" disabled>
                    »
                </button>
            @endif
        </div>

        {{-- Versi mobile --}}
        <div class="join inline-flex sm:hidden border border-slate-200 dark:border-slate-700 shadow-2xs rounded-lg overflow-hidden bg-white dark:bg-slate-800">
            {{-- Tombol Sebelumnya («) --}}
            @if ($paginator->onFirstPage())
                <button type="button" aria-disabled="true" aria-label="Sebelumnya" class="join-item btn btn-sm btn-disabled bg-slate-50 dark:bg-slate-800/60 text-slate-300 dark:text-slate-600 border-none" disabled>
                    «
                </button>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="join-item btn btn-sm bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-none hover:text-primary-600 dark:hover:text-primary-400 font-medium" aria-label="Sebelumnya">
                    «
                </button>
            @endif

            {{-- Info Halaman (Page X) --}}
            <button type="button" class="join-item btn btn-sm bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-none font-semibold pointer-events-none cursor-default" aria-current="page">
                Page {{ $paginator->currentPage() }}
            </button>

            {{-- Tombol Selanjutnya (») --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="join-item btn btn-sm bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-none hover:text-primary-600 dark:hover:text-primary-400 font-medium" aria-label="Selanjutnya">
                    »
                </button>
            @else
                <button type="button" aria-disabled="true" aria-label="Selanjutnya" class="join-item btn btn-sm btn-disabled bg-slate-50 dark:bg-slate-800/60 text-slate-300 dark:text-slate-600 border-none" disabled>
                    »
                </button>
            @endif
        </div>
    @endif
</div>
