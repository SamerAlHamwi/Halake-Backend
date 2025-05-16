@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/viewService.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/style/viewService.css') }}">
@endsection

@section('content')
    <input type="hidden" value="{{ $service->id }}" id="serviceId">

    <div class="card">
        <div class="card-header">
            <h4>
                {{ __('Service Details') }} :
            </h4>
            <span>{{ $service->service_number }}</span>
            <span class="mr-2 ml-2">:</span>
            <span> {{ $service->title }}</span>

            <label class="switch mb-0 ml-auto">
                <input id="serviceStatus" rel="{{ $service->id }}" type="checkbox" class="onoff"
                    {{ $service->status == 1 ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>

            <a href="" id="deleteService" class="ml-2 btn btn-danger text-white">{{ __('Delete Service') }}</a>

        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="d-block" for="">{{ __('Salon') }}</label>
                <a href="{{ route('viewSalonProfile', $service->salon->id) }}"><span class="badge bg-primary text-white">
                        {{ $service->salon->salon_name }}</span></a>
            </div>

            <div class="form-group mt-0">
                <label for="">{{ __('Images') }}</label>
                <div class="d-flex mb-2">
                    @foreach ($service->images as $image)
                        <div class="service_image">
                            <img width="100" class="rounded" height="100"
                                src="{{ env('FILES_BASE_URL') }}{{ $image->image }}" alt="">
                            <i rel="{{ $image->id }}" class="fas fa-trash img-delete"></i>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="" method="post" enctype="multipart/form-data" class="" id="serviceForm"
                autocomplete="off">
                @csrf

                <input type="hidden" name="id" value="{{ $service->id }}">

                <div class="form-row ">
                    <div class="form-group col-md-6">
                        <label for="">{{ __('Title') }}</label>
                        <input type="text" class="form-control" name="title" value="{{ $service->title }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">{{ __('Time (Minutes)') }}</label>
                        <input type="number" class="form-control" name="service_time"
                            value="{{ $service->service_time }}">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">{{ __('Category') }}</label>
                        <select name="category_id" id="category_id" class="form-control"
                            aria-label="Default select example">
                            @foreach ($categories as $cat)
                                <option {{ $cat->id == $service->category_id ? 'selected' : '' }}
                                    value="{{ $cat->id }}">{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label for="">{{ __('Gender') }}</label>
                        <select name="gender" id="gender" class="form-control" aria-label="Default select example">
                            <option {{ $service->gender == 0 ? 'selected' : '' }} value="0">
                                {{ __('Male') }}</option>
                            <option {{ $service->gender == 1 ? 'selected' : '' }} value="1">
                                {{ __('Female') }}</option>
                            <option {{ $service->gender == 2 ? 'selected' : '' }} value="2">
                                {{ __('Unisex') }}</option>
                        </select>
                    </div>

                </div>

                <div class="form-row ">
                    <div class="form-group col-md-6">
                        <label for="">{{ __('About') }}</label>
                        <textarea type="text" class="form-control" name="about">{{ $service->about }}</textarea>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="">{{ __('Price') }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    {{ $settings->currency }}
                                </div>
                            </div>
                            <input type="text" class="form-control" name="price" value="{{ $service->price }}">
                        </div>

                    </div>
                    <div class="form-group col-md-3">
                        <label for="">{{ __('Discount') }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    %
                                </div>
                            </div>
                            <input type="text" class="form-control" name="discount" value="{{ $service->discount }}">
                        </div>

                    </div>


                </div>

                <div class="form-group">
                    <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                </div>

            </form>
        </div>
    </div>
@endsection
