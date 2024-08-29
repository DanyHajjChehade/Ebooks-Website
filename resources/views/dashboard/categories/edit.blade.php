@extends('dashboard.layout.layout')

@section('body')
    <div class="page-body">
        <!-- Container-fluid starts-->
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="page-header-left">
                            {{-- <h3>{{/*$category->name*/}} --}}
                            </h3>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <ol class="breadcrumb pull-right">
                            <li class="breadcrumb-item">
                                <a href="/dashboard/categories">
                                    <i data-feather="home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Digital</li>
                            <li class="breadcrumb-item active">Sub Category</li>
                            {{-- <li class="breadcrumb-item active">{{/*$category->name*/}}</li> --}}
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



                        </div>

                        <div class="card-body">

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
                                <form class="needs-validation" action="{{route('dashboard.categories.update',$category->id)}}" method="POST" enctype="multipart/form-data">
                                    <div class="form">
                                        @csrf
                                        @method('put')
                                        <div class="form-group">
                                            <label for="validationCustom01" class="mb-1">Name:</label>
                                            <input class="form-control" id="validationCustom01" type="text"
                                                name="name" value="{{$category->name}}">
                                        </div>

                                        <div class="form-group">
                                            <label for="validationCustom01" class="mb-1">Description</label>
                                            <textarea rows="5" cols="12" class="form-control" id="validationCustom01" name="description">{{ $category->description }}</textarea>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="validationCustom02" class="mb-1">Image:</label>
                                            <input class="form-control dropify" id="validationCustom02" type="file"
                                                name="image" data-default-file="{{asset($category->image)}}">
                                        </div>



                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->

    </div>
@endsection
