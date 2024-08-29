@extends('dashboard.layout.layout')

@section('body')
<div class="page-body">
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <div class="page-header-left">
                        <h3>Book Categories</h3>
                    </div>
                </div>
                <div class="col-lg-6">
                    <ol class="breadcrumb pull-right">
                        <li class="breadcrumb-item">
                            <a href="">
                                <i data-feather="home"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item">Digital</li>
                        <li class="breadcrumb-item active">Category</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->

    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <form class="form-inline search-form search-box">

                        </form>

                        <button type="button" class="btn btn-primary mt-md-0 mt-2" data-bs-toggle="modal" data-original-title="test" data-bs-target="#exampleModal">Add New Category</button>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="table-responsive table-desi">
                            <table class="table all-package table-category" id="editableTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th></th>
                                        <th>Image</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title f-w-600" id="exampleModalLabel">Add New Category</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form">
                            <div class="form-group">
                                <label for="validationCustom01" class="mb-1">Name:</label>
                                <input class="form-control" id="validationCustom01" type="text" name="name">
                            </div>
                            <div class="form-group">
                                <label for="validationCustom01" class="mb-1">Description</label>
                                <textarea rows="5" cols="12" class="form-control" id="validationCustom01" name="description"></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label for="validationCustom02" class="mb-1">Image:</label>
                                <input class="form-control dropify" id="validationCustom02" type="file" name="image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('dashboard.categories.delete') }}" method="POST">
                <div class="modal-content">
                    <div class="modal-body">
                        @csrf
                        @method('DELETE')
                        <div class="form-group">
                            <p>Are you sure you want to delete?</p>
                            <input type="hidden" name="id" id="id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('javascripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#editableTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.categories.getall') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'empty_column', name: 'empty_column'},
                    { data: 'image', name: 'image' },
                    { data: 'action', name: 'action' }
                ]
            });
        });

        $('#editableTable tbody').on('click', '#deleteBtn', function() {
            var id = $(this).attr("data-id");
            $('#deletemodal #id').val(id);
        });
    </script>
@endpush
@endsection
