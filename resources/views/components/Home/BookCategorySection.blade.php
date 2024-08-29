<div id="books" class="container mt-5">
    <div class="container d-flex justify-content-center mb-5">
        <div class="w-30 position-relative" style="width: 300px;">
            <input id="searchbook" type="text" name="query" placeholder="Search by book name"
                   class="bg-white shadow-sm p-2 form-control border-0 ps-3 pe-5"
                   style="outline: none; box-shadow: none; width: 100%; height: 3rem; border-radius: 999px; text-align: left; padding-left: 1rem;">
        </div>
    </div>
    <div class="row" id="book-list">
        @foreach ($books as $book)
        <x-Home.BookCategorySectionItem :cart="$cart" :book="$book"/>
        @endforeach
    </div>
    <div id="not-found">

    </div>
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    @if ($books->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $books->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    @endif

                    @foreach ($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                        <li class="page-item {{ ($books->currentPage() == $page) ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($books->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $books->nextPageUrl() }}" aria-label="Next">
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
@if ($books->isEmpty())
<br><br><br><br>
   <center><b><h1>There is no books right now.Please come back later.</h1></b></center>
   <br><br><br><br>
@else
    <div class="container mt-4 mb-5">

    </div>
@endif
<script type="text/javascript">
    $(document).ready(function() {
    $('#searchbook').on('keyup', function() {
        var query = $(this).val();
        var categoryId = {{ $category->id }};
        $.ajax({
            url: "{{ route('category.books.ajax') }}",
            type: "GET",
            dataType: "json",
            data: {
                'query': query,
                'category_id':categoryId
            },
            success: function(response) {

                $('#book-list').empty(); // Clear the existing list
                if (response.data.length === 0) {
                    $('#book-list').html('');
                    $('#not-found').html('<center><h3><p>No books found.</p></h3></center>')
                } else {
                    $('#not-found').html('');
                    $.each(response.data, function(index, book) {
                        var html = `
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="/${book.book_image}" class="card-img-top" alt="">
                        <div class="card-body">
                            <h4 class="card-title">${book.title}</h4>
                            <p class="card-text" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical;">${book.description}</p>
                            <ul>
                                <li class="card-text"><b>Category:</b> ${book.category ? book.category.name : 'Unknown Category'}</li>
                                <li class="card-text"><b>Author:</b> ${book.author ? `${book.author.first_name} ${book.author.last_name}` : 'Unknown Author'}</li>
                                <li class="card-text"><b>Price:</b>
                                    ${book.discount_price ?
                                        `<span style="text-decoration: line-through; color: red;">${book.price}$</span>` :
                                        `<span style="color: blue;">${book.price}$</span>`}
                                </li>
                                ${book.discount_price ?
                                    `<li class="card-text"><b>Discount Price:</b> <span style="color: blue;">${book.discount_price}$</span></li>` :
                                    ''}
                                ${book.copies_owned === 0 ?
                                    `<li class="card-text" style="color:red;"><b>Out of Stock</b></li>` :
                                    `<li class="card-text"><b>Owned Copies:</b> ${book.copies_owned}</li>`}
                            </ul>
                            ${book.copies_owned === 0 ? '' : `
                                <form method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="book_id" value="${book.id}">
                                    <input type="number" min="1" max="${book.copies_owned}" value="1" name="quantity" class="form-control form-control-sm">
                                    <br>
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            `}
                        </div>
                    </div>
                </div>
            `;
                        $('#book-list').append(html);
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
