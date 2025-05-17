@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/viewStaff.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/style/viewSalon.css') }}">
@endsection

<style>
    .bank-details span {
        display: block;
        line-height: 18px;
    }
</style>

@php
    use App\Models\GlobalFunction;
@endphp

@section('content')
    <input type="hidden" value="{{ $staff->id }}" id="staffId">

    <div class="card">
        <div class="card-header">
            <h4>
                {{ $staff->name }}
                <a data-toggle="modal" data-target="#editStaffModal" href=""
                class="ml-2 mb-1 btn btn-primary text-white">{{ __('Edit Barber') }}</a>
            </h4>

        </div>
        <div class="card-body">

            <div class="form-row">
                 {{-- Staff --}}
                 <div class="mt-3 col-3">
                    <label class="mb-1 text-grey d-block" for="">{{ __('Barber') }}</label>
                    <div class="d-flex align-items-center card-profile">
                        @if ($staff->photo != null)
                            <img class="rounded owner-img-border mr-2" width="80" height="80"
                                src="{{ env('FILES_BASE_URL') }}{{ $staff->photo }}" alt="">
                        @else
                            <img class="rounded owner-img-border mr-2" width="80" height="80"
                                src="http://placehold.jp/150x150.png" alt="">
                        @endif

                        <div>
                            <p class="mt-0 mb-0 p-data">{{ $staff->name }}</p>
                            <span
                                class="mt-0 mb-0">{{ $staff->phone }}</span><br>
                            <span class="mt-0 mb-0">{{ $staff->gender == 1 ? __('Male') : __('Female') }}</span>
                            <span  class="badge bg-warning text-white "><i class="fas fa-star"></i>{{GlobalFunction::formateLongFloatNumber($staff->rating)}}</span>
                        </div>
                    </div>
                </div>
                 {{-- Salon --}}
                 <div class="mt-3 col-3">
                    <label class="mb-1 text-grey d-block " for="">{{ __('Salon') }}</label>
                    <div class="d-flex align-items-center card-profile">
                        <img class="rounded owner-img-border mr-2" width="80" height="80"
                            src="{{ env('FILES_BASE_URL') }}{{ $staff->salon->images[0]->image }}" alt="">
                        <div>
                            <p class="mt-0 mb-0 p-data">{{ $staff->salon->salon_name }}</p>
                            <span class="mt-0 mb-0">{{ $staff->salon->salon_number }}</span>
                            <p class="mt-0 mb-0">{{ $staff->salon->salon_address }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>{{ __('Slots') }}</h4>
        </div>
        <div class="card-body">
            <div class="slote-table table-responsive col-12">
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Monday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['mondaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Tuesday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['tuesdaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Wednesday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['wednesdaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Thursday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['thursdaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Friday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['fridaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Saturday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['saturdaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 d-flex">
                    <label class="mb-0 text-grey" for="">{{ __('Sunday') }}</label>
                    <div class="slot-time-block">
                        @foreach ($slots['sundaySlots'] as $item)
                            <div class="slot-time-inner">
                                <span class="slot-time">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header">
            <h4>{{ __('Bookings') }}</h4>
        </div>
        <div class="card-body">
                <div class="table-responsive col-12">
                    <table class="table table-striped w-100 word-wrap" id="staffBookingsTable">
                        <thead>
                            <tr>
                                <th>{{ __('Booking Number') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Salon') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date & Time') }}</th>
                                <th>{{ __('Service Amount') }}</th>
                                <th>{{ __('Discount Amount') }}</th>
                                <th>{{ __('Subtotal') }}</th>
                                <th>{{ __('Total Tax Amount') }}</th>
                                <th>{{ __('Payable Amount') }}</th>
                                <th>{{ __('Order Date') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>

    {{-- Edit Staff Modal --}}
    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('Edit Barber') }}</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form action="" method="post" enctype="multipart/form-data" id="editStaffForm"
                        autocomplete="off">
                        @csrf

                        <input type="hidden" value="{{$staff->id}}" name="id">

                        <div class="form-group">
                            <label>{{ __('Photo : (Select only if you want to edit)') }} </label>
                            <input class="form-control" type="file" id="photo" name="photo">
                        </div>

                        <div class="form-group">
                            <label> {{ __('Name') }}</label>
                            <input type="text" value="{{$staff->name}}" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label> {{ __('Phone') }}</label>
                            <input type="text" value="{{$staff->phone}}" name="phone" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label> {{ __('Gender') }}</label>
                            <select name="gender" class="form-control">
                                <option {{$staff->gender == 0 ? 'selected' : '' }} value="0">{{ __('Female') }}</option>
                                <option {{$staff->gender == 1 ? 'selected' : '' }} value="1">{{ __('Male') }}</option>
                            </select>
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
