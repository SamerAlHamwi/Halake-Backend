@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/viewSalon.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/style/viewSalon.css') }}">
@endsection

<style>
    .bank-details span {
        display: block;
        line-height: 18px;
    }
</style>

@section('content')
    <input type="hidden" value="{{ $salon->id }}" id="salonId">

    <div class="card">
        <div class="card-header">
            <h4>
                {{ $salon->salon_name }} / {{ $salon->salon_number }}
            </h4>
            {{-- Salon Status --}}
            @if ($salon->status == $salonStatus['statusSalonPending'])
                <span class="badge bg-warning text-white ">{{ __('Pending Review') }} </span>
            @elseif($salon->status == $salonStatus['statusSalonActive'])
                <span class="badge bg-success text-white ">{{ __('Active') }} </span>
            @elseif($salon->status == $salonStatus['statusSalonBanned'])
                <span class="badge bg-danger text-white ">{{ __('Banned') }} </span>
            @endif

            {{-- Top Rated Badge --}}
            @if ($salon->top_rated == 1)
                <span class="ml-2 badge bg-topRated bg-primary text-white ">{{ __('Top Rated') }} </span>
            @endif

            {{-- Action Buttons --}}
            @if ($salon->status == $salonStatus['statusSalonPending'])
                <a href="" id="approveSalon"
                    class="ml-auto btn btn-success activateSalon">{{ __('Approve Salon') }}</a>
            @elseif($salon->status == $salonStatus['statusSalonActive'])
                <a href="" id="banSalon" class="ml-auto btn btn-danger text-white">{{ __('Ban Salon') }}</a>
            @elseif($salon->status == $salonStatus['statusSalonBanned'])
                <a href="" id="activateSalon"
                    class="ml-auto btn btn-success activateSalon">{{ __('Activate Salon') }}</a>
            @endif

        </div>
        <div class="card-body">

            <div class="form-row">
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Lifetime Earnings') }}</label>
                    <p class="mt-0 p-data">{{ $settings->currency }}{{ $salon->lifetime_earnings }}</p>
                </div>
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Completed Bookings') }}</label>
                    <p class="mt-0 p-data">{{ $salon->total_completed_bookings }}</p>
                </div>
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Rejected Bookings') }}</label>
                    <p class="mt-0 p-data">{{ $salon->total_rejected_bookings }}</p>
                </div>
                <div class="col-md-2">
                    <label class="mb-0 text-grey d-block" for="">{{ __('Gender Served') }}</label>
                    @if ($salon->gender_served == 0)
                        <span class="badge bg-primary text-white ">{{ __('Male') }} </span>
                    @elseif($salon->gender_served == 1)
                        <span class="badge bg-primary text-white ">{{ __('Female') }} </span>
                    @else
                        <span class="badge bg-primary text-white ">{{ __('Unisex') }} </span>
                    @endif
                </div>
            </div>

            <div class="form-row mt-3">
                {{-- Salon Location --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Overall Rating') }}</label>
                    <p class="mt-0 p-data">{{ $salon->rating }}</p>
                </div>

                {{-- Mon-Fri Time --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Mon-Fri Time') }}</label>
                    <p class="mt-0 p-data">{{ $salon->mon_fri_from }} - {{ $salon->mon_fri_to }}</p>
                </div>
                {{-- Sat-Sun Time --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Sat-Sun Time') }}</label>
                    <p class="mt-0 p-data">{{ $salon->sat_sun_from }} - {{ $salon->sat_sun_to }}</p>
                </div>
                {{-- On Vacation Status --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('On Vacation') }}</label>
                    @if ($salon->on_vacation == 0)
                        <p class="mt-0 p-data">{{ __('No') }}</p>
                    @else
                        <p class="mt-0 p-data">{{ __('Yes') }}</p>
                    @endif
                </div>
                {{-- Rating --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey d-block" for="">{{ __('Salon Location') }}</label>
                    <a target="_blank" class="badge bg-primary text-white mt-1"
                        href="https://www.google.com/maps/?q={{ $salon->salon_lat }},{{ $salon->salon_long }}">{{ __('Click To Locate') }}</a>
                </div>
                {{-- Owner --}}
                <div class="col-md-2">
                    <label class="mb-0 text-grey d-block" for="">{{ __('Owner') }}</label>
                    <div class="d-flex mt-1 align-items-center">
                        <img class="rounded-circle owner-img-border" width="40" height="40"
                            src="{{ env('FILES_BASE_URL') }}{{ $salon->owner_photo }}" alt="">
                        <p class="mt-0 p-data mb-0 ml-2">{{ $salon->owner_name }}</p>
                    </div>
                </div>

            </div>


            <div class="form-row">
                <div class="col-md-12">
                    <label class="mb-0 text-grey d-block" for="">{{ __('Salon Categories') }}</label>
                    @foreach ($salonCats as $cat)
                        <span class="badge bg-cat-tag mt-1">{{ $cat->title }} </span>
                    @endforeach

                </div>
            </div>

            <div class="form-row  mt-3">
                <div class="col-md-2">
                    <div class="bank-details">
                        <span>{{ __('Bank Details :') }}</span>
                        @if ($salon->bankAccount != null)
                            <span class="text-dark font-14">{{ __('Holder : ') }}{{ $salon->bankAccount->holder }}</span>
                            <span
                                class="text-dark font-14">{{ __('Bank : ') }}{{ $salon->bankAccount->bank_title }}</span>
                            <span
                                class="text-dark font-14">{{ __('Account : ') }}{{ $salon->bankAccount->account_number }}</span>
                            <span
                                class="text-dark font-14">{{ __('Swift Code : ') }}{{ $salon->bankAccount->swift_code }}</span>
                            <a data-toggle="modal" data-target="#chequePhotoModal" id="chequePhoto" data-cheque="{{ env('FILES_BASE_URL') . $salon->bankAccount->cheque_photo }}" target="_blank" class="badge bg-primary text-white mt-1"
                                    href="">{{ __('Cheque Photo') }}</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="mb-0 text-grey" for="">{{ __('Wallet') }}</label>
                    <p class="mt-0 p-data">{{ $settings->currency }}{{ $salon->wallet }}</p>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-pills border-b  ml-0">

                <li role="presentation" class="nav-item"><a class="nav-link pointer active" href="#tabDetails"
                        aria-controls="home" role="tab" data-toggle="tab">{{ __('Details') }}<span
                            class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabServices" role="tab"
                        data-toggle="tab">{{ __('Services') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabBookings" role="tab"
                        aria-controls="tabBookings" data-toggle="tab">{{ __('Bookings') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabGallery" role="tab"
                        aria-controls="tabGallery" data-toggle="tab">{{ __('Gallery') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabStaff" role="tab"
                        aria-controls="tabStaff" data-toggle="tab">{{ __('Staff') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabReviews" role="tab"
                        aria-controls="tabReviews" data-toggle="tab">{{ __('Reviews') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabAwards" role="tab"
                        aria-controls="tabAwards" data-toggle="tab">{{ __('Awards') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabWalletMoney"
                        role="tab" data-toggle="tab">{{ __('Wallet') }}
                        <span class="badge badge-transparent "></span></a>
                </li>
                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabPayOuts" role="tab"
                        data-toggle="tab">{{ __('Payouts') }}
                        <span class="badge badge-transparent "></span></a>
                </li>
                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#tabEarningHistory"
                        role="tab" data-toggle="tab">{{ __('Earnings') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

            </ul>

            <a data-toggle="modal" data-target="#addSalonImagesModal" href="" id="addSalonImages"
                class="ml-auto btn btn-primary text-white">{{ __('Add Images') }}</a>
        </div>
        <div class="card-body">
            <div class="tab-content tabs" id="home">

                {{-- Details --}}
                <div role="tabpanel" class="tab-pane active" id="tabDetails">

                    <div class="form-group mt-0">
                        <label for="">{{ __('Images') }}</label>
                        <div class="d-flex mb-2">
                            @foreach ($salon->images as $image)
                                <div class="salon_image">
                                    <img width="100" class="rounded" height="100"
                                        src="{{ env('FILES_BASE_URL') }}{{ $image->image }}" alt="">
                                    <i rel="{{ $image->id }}" class="fas fa-trash img-delete"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>


                    <form action="" method="post" enctype="multipart/form-data" class=""
                        id="salonDetailsForm" autocomplete="off">
                        @csrf

                        <input type="hidden" name="id" value="{{ $salon->id }}">

                        <div class="form-row ">
                            <div class="form-group col-md-3">
                                <label for="">{{ __('Email') }}</label>
                                <input type="text" class="form-control" name="email" disabled
                                    value="{{ $salon->email }}">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="">{{ __('Salon Name') }}</label>
                                <input type="text" class="form-control" name="salon_name"
                                    value="{{ $salon->salon_name }}">
                            </div>


                            <div class="form-group col-md-3">
                                <label for="">{{ __('Salon Phone') }}</label>
                                <input type="text" class="form-control" name="salon_phone"
                                    value="{{ $salon->salon_phone }}">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="">{{ __('Gender Served') }}</label>
                                <select name="gender_served" id="gender" class="form-control"
                                    aria-label="Default select example">
                                    <option {{ $salon->gender_served == 0 ? 'selected' : '' }} value="0">
                                        {{ __('Male') }}</option>
                                    <option {{ $salon->gender_served == 1 ? 'selected' : '' }} value="1">
                                        {{ __('Female') }}</option>
                                    <option {{ $salon->gender_served == 2 ? 'selected' : '' }} value="2">
                                        {{ __('Unisex') }}</option>
                                </select>
                            </div>

                        </div>

                        <div class="form-row ">
                            <div class="form-group col-md-4">
                                <label for="">{{ __('Salon About') }}</label>
                                <textarea type="text" class="form-control" name="salon_about">{{ $salon->salon_about }}</textarea>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="">{{ __('Salon Address') }}</label>
                                <textarea type="text" class="form-control" name="salon_address">{{ $salon->salon_address }}</textarea>
                            </div>

                        </div>

                        <div class="form-group">
                            <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                        </div>

                    </form>


                </div>
                {{-- Bookings --}}
                <div role="tabpanel" class="tab-pane" id="tabBookings">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="bookingsTable">
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
                                    <th>{{ __('Placed On') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Services --}}
                <div role="tabpanel" class="tab-pane" id="tabServices">
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
                                    <th>{{ __('Status (On/Off)') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Staff --}}
                <div role="tabpanel" class="tab-pane" id="tabStaff">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="staffTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Photo') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th>{{ __('Bookings') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Gallery --}}
                <div role="tabpanel" class="tab-pane" id="tabGallery">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="galleryTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Image') }}</th>
                                    <th class="w-70">{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Reviews --}}
                <div role="tabpanel" class="tab-pane" id="tabReviews">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="reviewsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Rating') }}</th>
                                    <th class="w-30">{{ __('Comment') }}</th>
                                    <th>{{ __('Booking') }}</th>
                                    <th>{{ __('Date&Time') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Awards --}}
                <div role="tabpanel" class="tab-pane" id="tabAwards">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="awardsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Award') }}</th>
                                    <th>{{ __('By') }}</th>
                                    <th class="w-70">{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Wallet & Money --}}
                <div role="tabpanel" class="tab-pane" id="tabWalletMoney">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="walletStatementTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction ID') }}</th>
                                    <th>{{ __('Summary') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Credit/Debit') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Payouts --}}
                <div role="tabpanel" class="tab-pane" id="tabPayOuts">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="salonPayOutsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Request Number') }}</th>
                                    <th>{{ __('Bank Details') }}</th>
                                    <th>{{ __('Amount & Status') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Salon') }}</th>
                                    <th>{{ __('Summary') }}</th>
                                    <th>{{ __('Placed On') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Earnings List --}}
                <div role="tabpanel" class="tab-pane" id="tabEarningHistory">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100 word-wrap" id="earningsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Earning Number') }}</th>
                                    <th>{{ __('Booking Number') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>

            {{-- Add Salon Images Modal --}}
            <div class="modal fade" id="addSalonImagesModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>{{ __('Add Salon Images') }}</h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data" id="addSalonImagesForm"
                                autocomplete="off">
                                @csrf
                                <input type="hidden" name="id" value="{{ $salon->id }}">

                                {{-- Images --}}
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">{{ __('Select Images') }}</label>
                                        <input class="form-control" type="file" name="images[]"
                                            accept="image/png, image/gif, image/jpeg" required multiple>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>
            {{-- Preview Gallery Modal --}}
            <div class="modal fade" id="previewGalleryModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>{{ __('Preview Gallery Post') }}</h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p id="descGalleryPreview"></p>
                            <img class="rounded" width="100%" id="imggalleryPreview" src="" alt="">
                        </div>

                    </div>
                </div>
            </div>

            {{-- Reject Modal --}}
            <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>{{ __('Reject Withdrawal') }}</h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data" id="rejectForm"
                                autocomplete="off">
                                @csrf
                                <input type="hidden" id="rejectId" name="id">
                                <div class="form-group">
                                    <label> {{ __('Summary') }}</label>
                                    <textarea rows="10" style="height:200px !important;" type="text" name="summary" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>
            {{-- Complete Modal --}}
            <div class="modal fade" id="completeModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>{{ __('Complete Withdrawal') }}</h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data" id="completeForm"
                                autocomplete="off">
                                @csrf
                                <input type="hidden" id="completeId" name="id">
                                <div class="form-group">
                                    <label> {{ __('Summary') }}</label>
                                    <textarea rows="10" style="height:200px !important;" type="text" name="summary" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <input class="btn btn-primary mr-1" type="submit" value=" {{ __('Submit') }}">
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>

                 {{-- View cheque photo Modal --}}
      <div class="modal fade" id="chequePhotoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5>{{ __('Cheque Photo') }}</h5>

                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <img id="chequePhotoImg" src="{{ env('FILES_BASE_URL') . $salon->bankAccount->cheque_photo }}" width="100%" src="" alt="">
              </div>

          </div>
      </div>
  </div>
        @endsection
