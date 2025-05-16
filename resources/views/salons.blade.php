@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/salons.js') }}"></script>
@endsection

@section('content')
    <div class="card mt-3">
        <div class="card-header">
            <h4>{{ __('Salons') }}</h4>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills border-b mb-3  ml-0">

                <li role="presentation" class="nav-item"><a class="nav-link pointer active" href="#Section1" aria-controls="home"
                        role="tab" data-toggle="tab">{{ __('Active Salons') }}<span
                            class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#Section3" role="tab"
                        data-toggle="tab">{{ __('Banned Salons') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#Section2" role="tab"
                        data-toggle="tab">{{ __('Pending Review') }}
                        <span class="badge badge-transparent "></span></a>
                </li>

                <li role="presentation" class="nav-item"><a class="nav-link pointer" href="#Section4" role="tab"
                        data-toggle="tab">{{ __('SignUp Only') }}
                        <span class="badge badge-transparent "></span></a>
                </li>
            </ul>

            <div class="tab-content tabs" id="home">
                {{-- Section 1 --}}
                <div role="tabpanel" class="row tab-pane active" id="Section1">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100" id="activeSalonTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Number') }}</th>
                                    <th>{{ __('Salon Name') }}</th>
                                    <th>{{ __('Gender Served') }}</th>
                                    <th>{{ __('Lifetime Earnings') }}</th>
                                    <th>{{ __('Top Rated') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Section 2 --}}
                <div role="tabpanel" class="row tab-pane" id="Section2">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100" id="pendingSalonTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Number') }}</th>
                                    <th>{{ __('Salon Name') }}</th>
                                    <th>{{ __('Gender Served') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Section 3 --}}
                <div role="tabpanel" class="row tab-pane" id="Section3">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100" id="bannedSalonTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Number') }}</th>
                                    <th>{{ __('Salon Name') }}</th>
                                    <th>{{ __('Gender Served') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Section 3 --}}
                <div role="tabpanel" class="row tab-pane" id="Section4">
                    <div class="table-responsive col-12">
                        <table class="table table-striped w-100" id="signUpOnlySalonTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Number') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Action') }}</th>
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
@endsection
