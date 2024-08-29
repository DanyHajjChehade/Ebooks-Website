<div class="testimonials" id="testimonials">
    <h2 class="main-title">Testimonials</h2>
    <div class="container">
        @foreach ($comments as $comment)
        <x-Home.testimonialsItemSection :comment="$comment"/>
        @endforeach
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    @if ($comments->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $comments->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    @endif

                    @foreach ($comments->getUrlRange(1, $comments->lastPage()) as $page => $url)
                        <li class="page-item {{ ($comments->currentPage() == $page) ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($comments->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $comments->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">&raquo;</span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</div>

