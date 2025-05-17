@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/staff.js') }}"></script>
@endsection

@section('content')
    <style>
    </style>
    <div class="card mt-3">
        <div class="card-header">
            <h4>{{ __('Barber') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive col-12">
                <table class="table table-striped w-100" id="staffTable">
                    <thead>
                        <tr>
                            <th>{{ __('Photo') }}</th>
                            <th>{{ __('Details') }}</th>
                            <th>{{ __('Salon') }}</th>
                            <th>{{ __('Bookings') }}</th>
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
