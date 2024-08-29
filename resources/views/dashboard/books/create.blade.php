@extends('dashboard.layout.layout')

@section('body')
    <div class="page-body">
        <!-- Container-fluid starts-->
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="page-header-left">
                            <h3>Books</h3>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb pull-right">
                            <li class="breadcrumb-item">
                                <a href="/dashboard/books">
                                    <i data-feather="home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Digital</li>
                            <li class="breadcrumb-item active">Add Book</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->

        <!-- Container-fluid starts-->
        <div class="container-fluid">
            <div class="row Book-adding">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Add Book</h5>
                        </div>
                        <div class="card-body">
                            <div class="digital-add needs-validation">
                                <form action="{{ route('dashboard.books.store') }}" method="post" enctype="multipart/form-data">
                                    @csrf

                                    <div class="col-12">

                                        @if ($errors->any())
                                            {!! implode('', $errors->all('<div>:message</div>')) !!}
                                        @endif

                                        <div class="form-group">
                                            <label for="validationCustomtitle" class="col-form-label pt-0">Category</label>
                                            <select name="category_id" id="" class="form-control" required>
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="validationCustomtitle" class="col-form-label pt-0">Author</label>
                                            <select name="author_id" id="" class="form-control" required>
                                                <option value="">Select Author</option>
                                                @foreach ($authors as $author)
                                                    <option value="{{ $author->id }}">{{ $author->first_name }} {{ $author->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label for="validationCustom05" class="col-form-label pt-0">
                                                 Book Image</label>
                                            <input class="form-control dropify" id="validationCustom05" type="file" name="book_image">
                                        </div>


                                        <div class="form-group">
                                            <label for="validationCustom01" class="col-form-label pt-0">
                                                Book Name</label>
                                            <input class="form-control" id="validationCustom01" type="text" name="title" required>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-form-label">Book Description</label>
                                            <textarea rows="5" cols="12" name="description"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="validationCustom02" class="col-form-label">
                                                Base Price </label>
                                            <input class="form-control" id="validationCustom02" type="text" name="price">
                                        </div>

                                        <div class="form-group">
                                            <label for="validationCustom02" class="col-form-label">
                                                Discount Price </label>
                                            <input class="form-control" id="validationCustom02" type="text" name="discount_price">
                                        </div>

                                        <div class="form-group">
                                            <label for="validationCustom02" class="col-form-label">
                                                Copies Owned </label>
                                            <input class="form-control" id="validationCustom02" type="text" name="copies_owned">
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Container-fluid Ends-->

    </div>
    </div>
@endsection


@push('javascripts')
    <script>
        $(".colors").select2({
            tags: true
        });
    </script>

@endpush
