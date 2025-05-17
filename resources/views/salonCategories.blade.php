@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/salonCategories.js') }}"></script>
@endsection

@section('content')
    <div class="card mt-3">
        <div class="card-header">
            <h4>{{ __('Salon Categories') }}</h4>
            <a data-toggle="modal" data-target="#addSalonCatModal" href=""
                class="ml-auto btn btn-primary text-white">{{ __('Add Category') }}</a>
        </div>
        <div class="card-body">
            <div class="table-responsive col-12">
                <table class="table table-striped w-100 word-wrap" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>{{ __('Image') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div class="modal fade" id="editSalonCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('Add Category') }}</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form action="" method="post" enctype="multipart/form-data" id="editSalonCatForm"
                        autocomplete="off">
                        @csrf

                        <input type="hidden" name="id" id="editSalonCatId">

                        <div class="form-group">
                            <img id="imgSalonCat" src="" alt="" class="rounded" width="50"
                                height="50">
                        </div>

                        <div class="form-group">
                            <label>{{ __('Icon') }}</label>
                            <input class="form-control" type="file" id="icon" name="icon">
                        </div>

                        <div class="form-group">
                            <label> {{ __('Category') }}</label>
                            <input id="editSalonCatTitle" type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    {{-- Add Category Modal --}}
    <div class="modal fade" id="addSalonCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('Add Category') }}</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form action="" method="post" enctype="multipart/form-data" id="addSalonCatForm"
                        autocomplete="off">
                        @csrf

                        <div class="form-group">
                            <label>{{ __('Icon') }}</label>
                            <input class="form-control" type="file" id="icon" name="icon" required>
                        </div>

                        <div class="form-group">
                            <label> {{ __('Category') }}</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
