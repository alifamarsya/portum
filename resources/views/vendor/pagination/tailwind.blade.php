@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <p class="text-[12.5px] text-slate-500">
            {!! __('Menampilkan') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>
                {!! __('sampai') !!}
                <span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('dari') !!}
            <span class="font-medium text-ink">{{ $paginator->total() }}</span>
            {!! __('data') !!}
        </p>

        <div class="inline-flex items-center gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                      class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 cursor-not-allowed">
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-4 h-4 rotate-180'])
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 bg-white border border-slate-200 hover:border-brand hover:text-brand transition">
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-4 h-4 rotate-180'])
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="w-8 h-8 flex items-center justify-center text-slate-400 text-sm">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="w-8 h-8 flex items-center justify-center rounded-lg bg-brand text-white text-sm font-semibold shadow-card">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 text-sm bg-white border border-slate-200 hover:border-brand hover:text-brand transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 bg-white border border-slate-200 hover:border-brand hover:text-brand transition">
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-4 h-4'])
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                      class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 cursor-not-allowed">
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-4 h-4'])
                </span>
            @endif
        </div>
    </nav>
@endif
