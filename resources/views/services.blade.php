@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/services.js') }}"></script>
@endsection

@section('content')
    <div class="card mt-3">
        <div class="card-header">
            <h4>{{ __('Services') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive col-12">
                <table class="table table-striped w-100 word-wrap" id="servicesTable">
                    <thead>
                        <tr>
                            <th>{{ __('Number') }}</th>
                            <th>{{ __('Image') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Time (Minutes)') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Gender') }}</th>
                            <th>{{ __('Salon') }}</th>
                            <th>{{ __('Status (On/Off)') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
