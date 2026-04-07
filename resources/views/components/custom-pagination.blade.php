@props(['paginator', 'label' => 'results'])
 
 <style>
     .page-link.arrow-link:hover {
         background-color: transparent !important;
         border-color: #dee2e6 !important;
         color: inherit !important;
     }
     .page-item.disabled .page-link.arrow-link {
         background-color: transparent !important;
         border-color: #dee2e6 !important;
     }
 </style>

<div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} {{ $label }}
        </div>
        <div>
            <nav>
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled"><span class="page-link arrow-link">‹</span></li>
                    @else
                        <li class="page-item"><a class="page-link arrow-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a></li>
                    @endif

                    {{-- Current Page Indicator --}}
                    <li class="page-item active"><span class="page-link">{{ $paginator->currentPage() }}</span></li>

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item"><a class="page-link arrow-link" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link arrow-link">›</span></li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
