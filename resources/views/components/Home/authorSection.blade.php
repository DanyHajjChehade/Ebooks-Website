<!-- Start Articles -->
<div class="articles" id="Author">
    <h2 class="main-title">Authors</h2>
    <div class="container d-flex justify-content-center mb-5">
        <div class="w-30 position-relative" style="width: 300px;">
            <input id="search" type="text" name="query" placeholder="Search by author name"
                   class="bg-white shadow-sm p-2 form-control border-0 ps-3 pe-5"
                   style="outline: none; box-shadow: none; width: 100%; height: 3rem; border-radius: 999px; text-align: left; padding-left: 1rem;">
        </div>
    </div>
    <div class="container" id="author-list">
        @foreach ($authors as $author)
            <x-Home.authorItemSection :author="$author"/>
        @endforeach

    </div>
    <div id="not-found">

    </div>
    <br>
    <!-- Bootstrap pagination links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    @if ($authors->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $authors->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    @endif

                    @foreach ($authors->getUrlRange(1, $authors->lastPage()) as $page => $url)
                        <li class="page-item {{ ($authors->currentPage() == $page) ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($authors->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $authors->nextPageUrl() }}" aria-label="Next">
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
<script type="text/javascript">
    $(document).ready(function() {
    $('#search').on('keyup', function() {
        var query = $(this).val();

        $.ajax({
            url: "{{ route('author.ajax') }}",
            type: "GET",
            dataType: "json",
            data: {
                'query': query,
            },
            success: function(response) {
                $('#author-list').empty(); // Clear the existing list
                if (response.data.length === 0) {
                    $('#author-list').html('');
                    $('#not-found').html('<center><h3><p>No authors found.</p></h3></center>');
                } else {
                    $('#not-found').html('');
                    $.each(response.data, function(key, item) {
                        var authorItem = `
                            <div class="box" id="authors">
                                <img src="${item.author_image}" alt="" />
                                <div class="content">
                                    <h3>${item.first_name} ${item.last_name}</h3>
                                </div>
                                <div class="info">
                                    <a href="/author/${item.first_name}/books">Author Profile</a>
                                    <i class="fas fa-long-arrow-alt-right"></i>
                                </div>
                            </div>
                        `;
                        $('#author-list').append(authorItem);
                    });
                }

                // Update pagination links
                $('#pagination-links').html(response.links);

                // If searching moved to a different page, navigate to that page
                if (response.current_page !== undefined && response.current_page !== null) {
                    $('.pagination').find('.page-item').removeClass('active');
                    $('.pagination').find('li').eq(response.current_page - 1).addClass('active');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});

</script>
<x-Home.spikes/>
<!-- End Articles -->

