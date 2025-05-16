@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/viewBookingDetails.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/style/viewBookingDetails.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection
@php
    use App\Models\Constants as Constants;
    use App\Models\GlobalFunction as GlobalFunction;
@endphp
@section('content')
    <style>
        .coupon-text {
            padding: 0px 5px;
            border-radius: 5px;
        }

        .starDisabled {
            color: rgb(184, 184, 184) !important;
        }

        .starActive {
            color: orangered !important;
        }


    </style>

    <input type="hidden" value="{{ $booking->id }}" id="bookingId">
    <input type="hidden" value="{{ $booking->booking_id }}" id="bookingIdBig">

    <div class="row flex-column flex-xl-row mt-2">

        <div class="card col-4 mr-2">
            <div class="card-header">
                <h4 class="d-inline">
                    {{ $booking->booking_id }}
                </h4>

                {{--  Status --}}
                @if ($booking->status == Constants::orderPlacedPending)
                    <span class="badge bg-warning text-white ">{{ __('Waiting For Confirmation') }} </span>
                @elseif($booking->status == Constants::orderAccepted)
                    <span class="badge bg-info text-white ">{{ __('Accepted') }} </span>
                @elseif($booking->status == Constants::orderCompleted)
                    <span class="badge bg-success text-white ">{{ __('Completed') }} </span>
                @elseif($booking->status == Constants::orderDeclined)
                    <span class="badge bg-danger text-white ">{{ __('Declined') }} </span>
                @elseif($booking->status == Constants::orderCancelled)
                    <span class="badge bg-danger text-white ">{{ __('Cancelled') }} </span>
                @endif

            </div>
            <div class="card-body">
                <div class="">
                    {{-- Salon --}}
                    <div class="mt-3">
                        <label class="mb-1 text-grey d-block " for="">{{ __('Salon') }}</label>
                        <div class="d-flex align-items-center card-profile">
                            <img class="rounded owner-img-border mr-2" width="80" height="80"
                                src="{{ env('FILES_BASE_URL') }}{{ $booking->salon->images[0]->image }}" alt="">
                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $booking->salon->salon_name }}</p>
                                <span class="mt-0 mb-0">{{ $booking->salon->salon_number }}</span>
                                <p class="mt-0 mb-0">{{ $booking->salon->salon_address }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- User --}}
                    <div class="mt-3">
                        <label class="mb-1 text-grey d-block" for="">{{ __('User') }}</label>
                        <div class="d-flex align-items-center card-profile">
                            @if ($booking->user->profile_image != null)
                                <img class="rounded owner-img-border mr-2" width="80" height="80"
                                    src="{{ env('FILES_BASE_URL') }}{{ $booking->user->profile_image }}" alt="">
                            @else
                                <img class="rounded owner-img-border mr-2" width="80" height="80"
                                    src="http://placehold.jp/150x150.png" alt="">
                            @endif

                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $booking->user->fullname }}</p>
                                <span
                                    class="mt-0 mb-0">{{ $booking->user->email != null ? $booking->user->email : '' }}</span>
                                {{-- <span class="mt-0 mb-0">{{ $booking->user->gender == 1 ? __('Male') : __('Female') }} :
                                {{ $booking->user->age() }}{{ __(' Years') }}</span> --}}
                            </div>
                        </div>
                    </div>
                    {{-- Staff --}}
                   @if ($staff != null)
                   <div class="mt-3">
                    <label class="mb-1 text-grey d-block" for="">{{ __('Staff') }}</label>
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
                                class="mt-0 mb-0">{{ $staff->phone }}</span>
                            {{-- <span class="mt-0 mb-0">{{ $booking->user->gender == 1 ? __('Male') : __('Female') }} :
                            {{ $booking->user->age() }}{{ __(' Years') }}</span> --}}
                        </div>
                    </div>
                </div>
                   @endif
                    {{-- Feedback --}}
                    <div class="mt-3">
                        <label class="mb-1 text-grey d-block" for="">{{ __('Feedback') }}</label>
                        <div class="card-profile align-items-center">
                            <div>
                                @if ($booking->review != null)
                                    {!! $ratingBar !!}
                                    <br>
                                    <span class="mt-0 mb-0">{{ $booking->review->comment }}</span><br>
                                @else
                                    <p class="mt-0 mb-0 p-data">{{ __('No Feedback') }}</p><br>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details and Invoice --}}
        <div class="card col ml-2">
            <div class="card-header">
                <h4 class="d-inline">
                    {{ __('Details') }}
                </h4>

                <a class="ml-auto" id="print-payment" href="">
                    <span class="badge bg-warning text-white ">{{ __('Print') }} </span>
                </a>
                <a class="ml-2" id="download-pdf" href="">
                    <span class="badge bg-warning text-white ">{{ __('Save PDF') }} </span>
                </a>
            </div>
            <div class="card-body" id="details-body">

                <div class="d-flex">
                    <div class="col p-0">
                        <label class="text-grey d-block mb-0" for="">{{ __('Booking Number') }}</label>
                        <div class="card-profile align-items-center">
                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $booking->booking_id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col p-0 text-right">
                        <label class="text-grey d-block mb-0" for="">{{ __('Booking Date') }}</label>
                        <div class="card-profile align-items-center">
                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $booking->created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Time/Date --}}
                <div class="mt-3">
                    <label class="text-grey d-block mb-0" for="">{{ __('Appointment Schedule') }}</label>
                    <div class="card-profile align-items-center">
                        <div>
                            <p class="p-data"><span class="mt-0 mb-0">{{ __('Date') }}: {{ $booking->date }}</span> |
                                <span class="mt-0 mb-0">{{ __('Time') }}:
                                    {{ GlobalFunction::formateTimeString($booking->time) }}</span> |
                                <span class="mt-0 mb-0">{{ __('Duration') }}:
                                    {{ $booking->duration }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex">
                    {{-- Salon --}}
                    <div class="col-md-6 p-0">
                        <label class="text-grey d-block mb-0" for="">{{ __('Salon') }}</label>
                        <div class="card-profile align-items-center">
                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $booking->salon->salon_name }}</p>
                                <p class="mt-0 mb-0">{{ $booking->salon->salon_address }}</p>
                                <span class="mt-0 mb-0">{{ __('Salon Number: ') }}{{ $booking->salon->salon_number }}</span>
                            </div>
                        </div>
                    </div>
                    {{-- Service Location --}}
                    <div class="col-md-6 p-0 text-right">
                        <label class="text-grey d-block mb-0" for="">{{ __('Service Location') }}</label>
                        <div class="card-profile align-items-center">
                            @if ($booking->service_location == 0)
                            {{-- At Salon --}}
                            <div>
                                <p class="mt-0 mb-0 p-data">{{__('At Salon')}}</p>
                            </div>
                            @else
                            {{-- At Other Location --}}
                            <div>
                                <p class="mt-0 mb-0 p-data">{{ $address->name }}</p>
                                <p class="mt-0 mb-0">{{ $address->mobile }}</p>
                                    <span  class="badge bg-secondary text-dark ">{{$address->type == 1 ? 'Home' : 'Office'}}</span>
                                    @if ($address->latitude != null)
                                    <a target="_blank" class="badge bg-secondary text-dark"
                                    href="https://www.google.com/maps/?q={{ $address->latitude }},{{ $address->longitude }}">{{ __('Locate') }}</a>
                                    @endif
                                    <br>
                                <span class="mt-0 mb-0">{{ $address->address }} <br> {{$address->locality}}, {{$address->city}}, {{$address->state}}, {{$address->country}},{{$address->pin}} </span>
                            </div>
                            @endif

                        </div>
                    </div>

                </div>



                 {{-- Payment Type --}}
                 <div class="mt-3">
                    <label class="text-grey d-block mb-0" for="">{{ __('Payment Type') }}</label>
                    <div class="card-profile align-items-center">
                        <div>
                            <p class="p-data">
                                @if ($booking->payment_type == Constants::paymentTypePrepaid)
                                <span class="mt-0 mb-0">{{ __('Pre-Paid') }}</span>
                                @else
                                <span class="mt-0 mb-0">{{ __('Pay After Service') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- User --}}
                <div class="col-md-6 p-0 mt-3">
                    <label class="text-grey d-block mb-0" for="">{{ __('Customer') }}</label>
                    <div class="card-profile align-items-center">
                        <div>
                            <p class="mt-0 mb-0 p-data">{{ $booking->user->fullname }}</p>
                            <span class="mt-0 mb-0">{{ $booking->user->email != null ? $booking->user->email : '' }}</span>
                        </div>
                    </div>
                </div>

                <div id="payment-details-body " class="mt-3">

                    @foreach ($bookingSummary['services'] as $item)
                        <div class="invoice-item">
                            <div class="d-flex">
                                <p>{{ $item['title'] }}</p>
                            </div>
                            <p>{{ $settings->currency }}{{ $item['price'] }}</p>
                        </div>
                    @endforeach

                    @if ($bookingSummary['coupon_apply'] == 1)
                        <div class="invoice-item ">
                            <div class="d-flex">
                                <p>{{ __('Discount Amount') }}</p>
                                <p class="ml-2 bg-dark text-white coupon-text">{{ $bookingSummary['coupon']['coupon'] }}
                                </p>
                            </div>
                            <p>{{ $settings->currency }}{{ $bookingSummary['discount_amount'] }}</p>
                        </div>
                    @endif
                    <div class="invoice-item ">
                        <div class="d-flex">
                            <p>{{ __('Subtotal') }}</p>
                        </div>
                        <p>{{ $settings->currency }}{{ $booking->subtotal }}</p>
                    </div>

                    @foreach ($bookingSummary['taxes'] as $item)
                        <div class="invoice-item">
                            <div class="d-flex">
                                @if ($item['type'] == Constants::taxPercent)
                                    <p>{{ $item['tax_title'] }} : {{ $item['value'] }}%</p>
                                @else
                                    <p>{{ $item['tax_title'] }}</p>
                                @endif
                            </div>
                            <p>{{ $settings->currency }}{{ $item['tax_amount'] }}</p>
                        </div>
                    @endforeach

                    <div class="invoice-item ">
                        <div class="d-flex">
                            <p class="text-white">{{ __('Payable Amount') }}</p>
                        </div>
                        <p class="text-white">{{ $settings->currency }}{{ $booking->payable_amount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
