<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Coupons;
use App\Models\GlobalFunction;
use App\Models\Salons;
use App\Models\Users;
use App\Models\UserWalletStatements;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Constants;
use App\Models\GlobalSettings;
use App\Models\PlatformData;
use App\Models\PlatformEarningHistory;
use App\Models\SalonEarningHistory;
use App\Models\SalonPayoutHistory;
use App\Models\SalonReviews;
use App\Models\SalonWalletStatements;
use App\Models\Staff;
use App\Models\UserAddress;
use Carbon\Carbon;
use Mockery\Generator\StringManipulation\Pass\ConstantsPass;
use PHPUnit\TextUI\XmlConfiguration\Constant;
use Symfony\Component\VarDumper\Caster\ConstStub;

class BookingsController extends Controller
{
    //
    public function deleteUserAddress(Request $request){
        $rules = [
            'user_id' => 'required',
            'address_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $address = UserAddress::find($request->address_id);
        if($address == null){
            return GlobalFunction::sendSimpleResponse(false, 'address does not exists');
        }
        if($address->user_id != $user->id){
            return GlobalFunction::sendSimpleResponse(false, 'address is not owned by this user');
        }
        $address->delete();

        return GlobalFunction::sendSimpleResponse(true, 'address deleted successfully');

    }
    public function editUserAddress(Request $request){
        $rules = [
            'user_id' => 'required',
            'address_id' => 'required',
            'name' => 'required',
            'mobile' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'pin' => 'required',
            'state' => 'required',
            'country' => 'required',
            'type' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $address = UserAddress::find($request->address_id);
        if($address == null){
            return GlobalFunction::sendSimpleResponse(false, 'address does not exists');
        }
        if($address->user_id != $user->id){
            return GlobalFunction::sendSimpleResponse(false, 'address is not owned by this user');
        }

        $address->name = $request->name;
        $address->mobile = $request->mobile;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->city = $request->city;
        $address->pin = $request->pin;
        $address->state = $request->state;
        $address->country = $request->country;
        $address->type = $request->type;
        if($request->has('latitude')){
            $address->latitude = $request->latitude;
        }
        if($request->has('longitude')){
            $address->longitude = $request->longitude;
        }
        $address->save();

        return GlobalFunction::sendSimpleResponse(true, 'address edited successfully');

    }


    public function addUserAddress(Request $request){
        $rules = [
            'user_id' => 'required',
            'name' => 'required',
            'mobile' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'pin' => 'required',
            'state' => 'required',
            'country' => 'required',
            'type' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $address = new UserAddress();
        $address->user_id = $user->id;
        $address->name = $request->name;
        $address->mobile = $request->mobile;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->city = $request->city;
        $address->pin = $request->pin;
        $address->state = $request->state;
        $address->country = $request->country;
        $address->type = $request->type;
        if($request->has('latitude')){
            $address->latitude = $request->latitude;
        }
        if($request->has('longitude')){
            $address->longitude = $request->longitude;
        }
        $address->save();

        return GlobalFunction::sendSimpleResponse(true, 'address saved successfully');

    }

    function fetchMyAddress(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }
        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Statement Data fetched successfully!', $addresses);
    }

    function fetchAcceptedPendingBookingsOfSalonByDate(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $bookings = Bookings::where('salon_id', $request->salon_id)
            ->where('date', $request->date)
            ->whereIn('status', [Constants::orderPlacedPending, Constants::orderAccepted])
            ->with(['user'])
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Bookings fetched successfully', $bookings);
    }
    function viewBookingDetails($id)
    {
        $booking = Bookings::find($id);
        $settings = GlobalSettings::first();

        // Generating Rating Bar
        $starDisabled = '<i class="fas fa-star starDisabled"></i>';
        $starActive = '<i class="fas fa-star starActive"></i>';

        $ratingBar = '';
        if ($booking->review != null) {
            for ($i = 0; $i < 5; $i++) {
                if ($booking->review->rating > $i) {
                    $ratingBar = $ratingBar . $starActive;
                } else {
                    $ratingBar = $ratingBar . $starDisabled;
                }
            }
        }
        // Having json object of booking summary
        $bookingSummary = json_decode($booking->services, true);
        // Staff
        $staff = null;
        if($booking->staff_id != null){
            $staff = Staff::find($booking->staff_id);
        }
        // Address
        $address = null;
        if($booking->service_location == 1 && $booking->address_id != null){
            $address = UserAddress::find($booking->address_id);
        }

        return view('viewBookingDetails', [
            'booking' => $booking,
            'staff' => $staff,
            'address' => $address,
            'ratingBar' => $ratingBar,
            'settings' => $settings,
            'bookingSummary' => $bookingSummary,
        ]);
    }
    function fetchDeclinedBookingsList(Request $request)
    {
        $totalData =  Bookings::where('status', Constants::orderDeclined)->count();
        $rows = Bookings::where('status', Constants::orderDeclined)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('status', Constants::orderDeclined)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('status', Constants::orderDeclined)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where(
                'status',
                Constants::orderDeclined
            )->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);

            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchCancelledBookingsList(Request $request)
    {
        $totalData =  Bookings::where('status', Constants::orderCancelled)->count();
        $rows = Bookings::where('status', Constants::orderCancelled)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('status', Constants::orderCancelled)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('status', Constants::orderCancelled)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where(
                'status',
                Constants::orderCancelled
            )->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);

            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchCompletedBookingsList(Request $request)
    {
        $totalData =  Bookings::where('status', Constants::orderCompleted)->count();
        $rows = Bookings::where('status', Constants::orderCompleted)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('status', Constants::orderCompleted)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('status', Constants::orderCompleted)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where(
                'status',
                Constants::orderCompleted
            )->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);

            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchAcceptedBookingsList(Request $request)
    {
        $totalData =  Bookings::where('status', Constants::orderAccepted)->count();
        $rows = Bookings::where('status', Constants::orderAccepted)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('status', Constants::orderAccepted)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('status', Constants::orderAccepted)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where(
                'status',
                Constants::orderAccepted
            )->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);

            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                 GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchPendingBookingsList(Request $request)
    {
        $totalData =  Bookings::where('status', Constants::orderPlacedPending)->count();
        $rows = Bookings::where('status', Constants::orderPlacedPending)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('status', Constants::orderPlacedPending)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('status', Constants::orderPlacedPending)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where(
                'status',
                Constants::orderPlacedPending
            )->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);

            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchAllBookingsList(Request $request)
    {
        $totalData =  Bookings::count();
        $rows = Bookings::with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);
            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $user,
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchSalonWalletStatementList(Request $request)
    {
        $totalData =  SalonWalletStatements::where('salon_id', $request->salonId)->count();
        $rows = SalonWalletStatements::where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = SalonWalletStatements::where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonWalletStatements::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonWalletStatements::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $cr_dr = $item->cr_or_dr;
            $icon = '';
            $textClass = '';
            $crDrBadge = '';

            if ($cr_dr == Constants::credit) {
                $icon =  '<i class="fas fa-plus-circle m-1 ic-credit"></i>';
                $textClass = 'text-credit';
                $crDrBadge = '<span  class="badge bg-success text-white ">' . __("Credit") . '</span>';
            } else {
                $icon =  '<i class="fas fa-minus-circle m-1 ic-debit"></i>';
                $textClass = 'text-debit';
                $crDrBadge = '<span  class="badge bg-danger text-white ">' . __("Debit") . '</span>';
            }
            $transaction = $icon . '<span class=' . $textClass . '>' . $item->transaction_id . '</span>';




            $data[] = array(
                $transaction,
                $item->summary,
                $settings->currency . $item->amount,
                $crDrBadge,
                GlobalFunction::formateDatabaseTime($item->created_at),
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchStaffBookingList(Request $request)
    {
        $totalData =  Bookings::where('staff_id', $request->staffId)->count();
        $rows = Bookings::where('staff_id', $request->staffId)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('staff_id', $request->staffId)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('staff_id', $request->staffId)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where('staff_id', $request->staffId)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">
                        ' . $item->salon->salon_name . '</span></a>';

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);
            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $item->user != null ? $item->user->fullname : '',
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateTimeString($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function fetchSalonBookingsList(Request $request)
    {
        $totalData =  Bookings::where('salon_id', $request->salonId)->count();
        $rows = Bookings::where('salon_id', $request->salonId)
            ->with(['user', 'salon'])->orderBy('id', 'DESC')->get();
        $settings = GlobalSettings::first();

        $result = $rows;

        $columns = array(
            0 => 'id',
            1 => 'fullname',
            2 => 'identity',
            3 => 'username',
        );

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Bookings::where('salon_id', $request->salonId)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('salon_id', $request->salonId)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where('salon_id', $request->salonId)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewBookingDetails', $item->id) . '" class="mr-2 btn btn-primary text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $action = $view;

            $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">
                        ' . $item->salon->salon_name . '</span></a>';

            $status = "";
            if ($item->status == Constants::orderPlacedPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            } else if ($item->status == Constants::orderAccepted) {
                $status = '<span class="badge bg-primary text-white" rel="' . $item->id . '">' . __('Accepted') . '</span>';
            } else if ($item->status == Constants::orderCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            } else if ($item->status == Constants::orderDeclined) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Declined') . '</span>';
            } else if ($item->status == Constants::orderCancelled) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Cancelled') . '</span>';
            }

            $dateTime =  $item->date . '<br>' . GlobalFunction::formateTimeString($item->time);
            $payableAmount = $settings->currency . $item->payable_amount;

            $data[] = array(
                $item->booking_id,
                $item->user != null ? $item->user->fullname : '',
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
                GlobalFunction::formateTimeString($item->created_at),
                $action,
            );
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        );
        echo json_encode($json_data);
        exit();
    }
    function bookings()
    {
        return view('bookings');
    }
    function fetchSalonPayoutHistory(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::find($request->salon_id);
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }

        $history = SalonPayoutHistory::where('salon_id', $salon->id)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Payout history Data fetched successfully!', $history);
    }
    function fetchSalonEarningHistory(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'month' => 'required',
            'year' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::find($request->salon_id);
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }

        $statement = SalonEarningHistory::where('salon_id', $salon->id)
            ->whereMonth('created_at', $request->month)
            ->whereYear('created_at', $request->year)
            ->orderBy('id', 'DESC')
            ->get();


        return GlobalFunction::sendDataResponse(true, 'Earning history Data fetched successfully!', $statement);
    }
    //
    function fetchSalonWalletStatement(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::find($request->salon_id);
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $statement = SalonWalletStatements::where('salon_id', $salon->id)
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Statement Data fetched successfully!', $statement);
    }
    function fetchBookingsByStaff(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'salon_id' => 'required',
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "Staff doesn't exists!"]);
        }
        if ($salon->id != $staff->salon_id) {
            return response()->json(['status' => false, 'message' => "This staff is not allowed with this salon!"]);
        }

        $bookings = Bookings::where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->with(['user','service_address'])
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Data fetched successfully', $bookings);
    }
    function fetchSalonBookingHistory(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $bookings = Bookings::where('salon_id', $salon->id)
            ->with(['user','service_address'])
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Data fetched successfully', $bookings);
    }

    function fetchBookingsByDate(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $bookings = Bookings::where('status', Constants::orderAccepted)
            ->where('date', $request->date)
            ->where('salon_id', $request->salon_id)
            ->with(['user','service_address'])
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Data fetched successfully', $bookings);
    }
    function completeBooking(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'booking_id' => 'required',
            'completion_otp' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user', 'review'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "Booking is not owned by this salon!"]);
        }
        if ($booking->completion_otp != $request->completion_otp) {
            return response()->json(['status' => false, 'message' => "Completion OTP is incorrect!"]);
        }
        if ($booking->status == Constants::orderAccepted) {
            $booking->status = Constants::orderCompleted;
            $booking->save();


            // Commission calculation
            $earning = $booking->subtotal;
            $settings = GlobalSettings::first();
            $commissionAmount = ($settings->comission / 100) * $earning;

            $earningAfterCommission = $earning - $commissionAmount;

            if($booking->payment_type == Constants::paymentTypePrepaid){

                // Adding Earning statement
                $earningSummary = "Earning from booking: " . $booking->booking_id;
                GlobalFunction::addSalonStatementEntry($salon->id, $booking->booking_id, $earning, Constants::credit, Constants::salonWalletEarning, $earningSummary);

                  // Adding earning to salon wallet
                    $salon->wallet = $salon->wallet + $earningAfterCommission;
                    $salon->save();

            }else{
                $salon->wallet = $salon->wallet - $commissionAmount;
                $salon->save();
            }

            // Adding Commission deduct statement
            $commissionSummary = "Commission of booking: " . $booking->booking_id . " : (" . $settings->comission . "%)";
            GlobalFunction::addSalonStatementEntry($salon->id, $booking->booking_id, $commissionAmount, Constants::debit, Constants::salonWalletCommission, $commissionSummary);

            // count increase + lifetime earning increase
            $salon->total_completed_bookings = $salon->total_completed_bookings + 1;
            $salon->lifetime_earnings = $salon->lifetime_earnings + $earningAfterCommission;
            $salon->save();

            // Adding Earning Logs Of Salon
            $salonEarningHistory = new SalonEarningHistory();
            $salonEarningHistory->salon_id = $salon->id;
            $salonEarningHistory->booking_id = $booking->id;
            $salonEarningHistory->earning_number = GlobalFunction::generateSalonEarningHistoryNumber();
            $salonEarningHistory->amount = $earningAfterCommission;
            $salonEarningHistory->save();

            // Adding Earning Logs of Platform
            $platformEarningHistory = new PlatformEarningHistory();
            $platformEarningHistory->earning_number = GlobalFunction::generatePlatformEarningHistoryNumber();
            $platformEarningHistory->amount = $commissionAmount;
            $platformEarningHistory->commission_percentage = $settings->comission;
            $platformEarningHistory->booking_id = $booking->id;
            $platformEarningHistory->salon_id = $salon->id;
            $platformEarningHistory->save();
            // Increasing total platform earning data
            $platformData = PlatformData::first();
            $platformData->lifetime_earnings = $platformData->lifetime_earnings + $commissionAmount;
            $platformData->save();

            // Sending push to user
            $title = "Booking : " . $booking->booking_id;
            $message = "Booking has been completed successfully!";
            GlobalFunction::sendPushToUser($title, $message, $booking->user);

            return GlobalFunction::sendSimpleResponse(true, 'Booking completed successfully');
        } else {
            return response()->json(['status' => false, 'message' => "This booking can't be completed!"]);
        }
    }
    function rejectBooking(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'booking_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user', 'review'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "Booking is not owned by this salon!"]);
        }
        if ($booking->status == Constants::orderPlacedPending) {
            $booking->status = Constants::orderDeclined;
            $booking->save();
            $booking->salon->total_rejected_bookings  = $booking->salon->total_rejected_bookings + 1;
            $booking->salon->save();

            if($booking->payment_type == Constants::paymentTypePrepaid){
                // Refunding to user
                $user = $booking->user;
                $user->wallet = $user->wallet + $booking->payable_amount;
                $user->save();
                // Adding statement entry
                $summary = 'Booking Declined By Salon : ' . $booking->booking_id . ' Refund';
                GlobalFunction::addUserStatementEntry($user->id, $booking->booking_id, $booking->payable_amount, Constants::credit, Constants::refund, $summary);
            }

            return GlobalFunction::sendSimpleResponse(true, 'Booking rejected successfully');

            // Sending push to user
            $title = "Booking :" . $booking->booking_id;
            $message = "Booking has been rejected by salon!";
            GlobalFunction::sendPushToUser($title, $message, $booking->user);
        } else {
            return response()->json(['status' => false, 'message' => "This booking can't be rejected!"]);
        }
    }
    function acceptBooking(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'booking_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user', 'review'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "Booking is not owned by this salon!"]);
        }
        if ($booking->status == Constants::orderPlacedPending) {
            $booking->status = Constants::orderAccepted;
            $booking->save();
            // Sending push to user
            $title = "Booking : " . $booking->booking_id;
            $message = "Booking has been accepted!";
            GlobalFunction::sendPushToUser($title, $message, $booking->user);
            return GlobalFunction::sendSimpleResponse(true, 'Booking accepted successfully');
        } else {
            return response()->json(['status' => false, 'message' => "This booking can't be accepted!"]);
        }
    }

    function Salon_fetchBookingDetails(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'booking_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user', 'review', 'salon.images','staff','service_address'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "Booking is not owned by this salon!"]);
        }

        return GlobalFunction::sendDataResponse(true, 'booking data fetched successfully', $booking);
    }
    function fetchSalonBookingRequests(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $bookings = Bookings::where('salon_id', $request->salon_id)
            ->where('status', Constants::orderPlacedPending)
            ->with(['user','service_address'])
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Bookings Request fetched successfully', $bookings);
    }
    function addRating(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'user_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user','service_address'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->user_id != $request->user_id) {
            return response()->json(['status' => false, 'message' => "This booking doesn't belong to this user"]);
        }
        if ($booking->status != Constants::orderCompleted) {
            return response()->json(['status' => false, 'message' => "This booking is not yet completed to rate!"]);
        }
        if ($booking->is_rated == 1) {
            return response()->json(['status' => false, 'message' => "This booking has been rated already!"]);
        }
        $booking->is_rated = 1;
        $booking->save();
        // Add rating
        $review = new SalonReviews();
        $review->user_id = $booking->user_id;
        $review->salon_id = $booking->salon_id;
        $review->booking_id = $booking->id;
        $review->rating = $request->rating;
        $review->comment = GlobalFunction::cleanString($request->comment);
        $review->save();

        $salon = $review->salon;
        $salon->rating = $salon->avgRating();
        $salon->save();

        // Staff Rating Calculatess
        if($booking->staff != null){
            $staff = $booking->staff;

            $staffBookings = Bookings::where('staff_id', $staff->id)->pluck('id');
            $totalRatings = SalonReviews::whereIn('booking_id', $staffBookings)->count();
            $ratingSum = SalonReviews::whereIn('booking_id', $staffBookings)->sum('rating');
            $avgRating = $ratingSum/$totalRatings;
            $staff->rating = $avgRating;
            $staff->save();
        }


        return GlobalFunction::sendDataResponse(true, 'Booking rated successfully!', $booking);
    }
    //
    function cancelBooking(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user','service_address'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }
        if ($booking->user_id != $request->user_id) {
            return response()->json(['status' => false, 'message' => "This booking doesn't belong to this user"]);
        }
        if ($booking->status == Constants::orderCancelled || $booking->status == Constants::orderDeclined || $booking->status == Constants::orderCompleted) {
            return response()->json(['status' => false, 'message' => "This booking is not eligible to be cancelled!"]);
        }
        $booking->status = Constants::orderCancelled;
        $booking->save();

        if($booking->payment_type == Constants::paymentTypePrepaid){
            // Refunding to user
            $user->wallet = $user->wallet + $booking->payable_amount;
            $user->save();
            // Adding statement entry
            $summary = 'Booking Cancelled By User: ' . $booking->booking_id . ' Refund';
            GlobalFunction::addUserStatementEntry($user->id, $booking->booking_id, $booking->payable_amount, Constants::credit, Constants::refund, $summary);
        }
        // Sending push to user
        $title = "Booking :" . $booking->booking_id;
        $message = "Booking has been cancelled successfully!";
        GlobalFunction::sendPushToUser($title, $message, $user);

        // Sending push to user
        $title = "Booking :" . $booking->booking_id;
        $message = "Booking has been cancelled!";
        GlobalFunction::sendPushToSalon($title, $message, $booking->salon);

        // Sending push to staff
        GlobalFunction::sendPushToStaff($title, $message, $booking->staff);

        return GlobalFunction::sendDataResponse(true, 'Booking cancelled successfully!', $booking);
    }
    function rescheduleBooking(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'user_id' => 'required',
            'date' => 'required',
            'time' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user','service_address'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }
        if ($booking->user_id != $request->user_id) {
            return response()->json(['status' => false, 'message' => "This booking doesn't belong to this user"]);
        }

        $booking->date = $request->date;
        $booking->time = $request->time;
        $booking->staff_id = $request->staff_id;
        $booking->status = Constants::orderPlacedPending;
        $booking->save();

        // Sending push to user
        $title = "Booking :" . $booking->booking_id;
        $message = "Booking has been rescheduled successfully!";
        GlobalFunction::sendPushToUser($title, $message, $user);

        // Sending push to salon
        $title = "Booking :" . $booking->booking_id .' has been rescheduled!';
        $message = "Review the details and Accept or Reject";
        GlobalFunction::sendPushToSalon($title, $message, $booking->salon);

        // Sending push to staff
        GlobalFunction::sendPushToStaff($title, $message, $booking->staff);



        return GlobalFunction::sendDataResponse(true, 'Booking rescheduled successfully!', $booking);
    }
    function fetchBookingDetails(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $booking = Bookings::where('booking_id', $request->booking_id)->with(['salon', 'user', 'review', 'salon.images', 'salon.slots','staff','service_address'])->first();
        if ($booking == null) {
            return response()->json(['status' => false, 'message' => "Booking doesn't exists!"]);
        }
        if ($booking->user_id != $request->user_id) {
            return response()->json(['status' => false, 'message' => "This booking doesn't belong to this user"]);
        }
        return GlobalFunction::sendDataResponse(true, 'details fetched successfully', $booking);
    }
    function fetchUserUpcomingBookings(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $bookings = Bookings::with(['salon', 'salon.images', 'user','service_address'])
            ->whereIn('status', [Constants::orderPlacedPending, Constants::orderAccepted])
            ->where('user_id', $user->id)
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'upcoming bookings fetched successfully', $bookings);
    }

    function fetchUserBookings(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $bookings = Bookings::with(['salon', 'salon.images', 'user','service_address','staff'])
            ->where('user_id', $user->id)
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'bookings fetched successfully', $bookings);
    }
    function placeBooking(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'salon_id' => 'required',
            'service_location' => 'required',
            'date' => 'required',
            'time' => 'required',
            'duration' => 'required',
            'services' => 'required',
            'is_coupon_applied' => [Rule::in(1, 0)],
            'service_amount' => 'required',
            'discount_amount' => 'required',
            'subtotal' => 'required',
            'total_tax_amount' => 'required',
            'payable_amount' => 'required',
            'payment_type' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $settings = GlobalSettings::first();

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }

        $bookingsCount = Bookings::where('user_id', $user->id)
            ->whereIn('status', [Constants::orderPlacedPending, Constants::orderAccepted])
            ->count();
        if ($bookingsCount >= $settings->max_order_at_once) {
            return response()->json(['status' => false, 'message' => "Maximum at a time order limit reached!"]);
        }

        $salon = Salons::find($request->salon_id);
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($salon->on_vacation == 1) {
            return response()->json(['status' => false, 'message' => "this salon is on vacation!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "this salon is not active!"]);
        }


        if($request->payment_type == Constants::paymentTypePrepaid){
            if ($user->wallet < $request->payable_amount) {
                return GlobalFunction::sendSimpleResponse(false, 'Insufficient balance in wallet');
            }
        }

        $booking = new Bookings();
        $booking->booking_id = GlobalFunction::generateBookingId();
        $booking->completion_otp = rand(1000, 9999);
        $booking->user_id = $request->user_id;
        $booking->salon_id = $request->salon_id;
        if($request->has('staff_id')){
            $staff = Staff::where('id', $request->staff_id)->first();
            if ($staff == null) {
                return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
            }

            if ($salon->id != $staff->salon_id) {
                return response()->json(['status' => false, 'message' => "This staff is not attached with this salon!"]);
            }
            $booking->staff_id = $request->staff_id;
        }
        $booking->service_location = $request->service_location;
        $booking->date = $request->date;
        $booking->time = $request->time;
        $booking->duration = $request->duration;
        $booking->services = $request->services;
        $booking->is_coupon_applied = $request->is_coupon_applied;

        $booking->service_amount = $request->service_amount;
        $booking->discount_amount = $request->discount_amount;
        $booking->subtotal = $request->subtotal;
        $booking->total_tax_amount = $request->total_tax_amount;
        $booking->payable_amount = $request->payable_amount;
        $booking->payment_type = $request->payment_type;

        if ($request->is_coupon_applied == 1) {
            $booking->coupon_title = $request->coupon_title;
            // add coupon to used coupon
            $discounts = explode(',', $user->coupons_used);
            array_push($discounts, $request->coupon_id);
            $user->coupons_used = implode(',', $discounts);
        }

        if($request->has('address_id')){
            $address = UserAddress::find($request->address_id);
            if($address == null){
                return GlobalFunction::sendSimpleResponse(false, 'address does not exists');
            }
            if($address->user_id != $user->id){
                return GlobalFunction::sendSimpleResponse(false, 'address is not owned by this user');
            }

            $booking->address_id = $address->id;
        }

        $booking->save();

        if($booking->payment_type == Constants::paymentTypePrepaid){

            // Deducting Money From Wallet
            $user->wallet = $user->wallet - $request->payable_amount;
            $user->save();

            // Add statement entry
            GlobalFunction::addUserStatementEntry(
                $user->id,
                $booking->booking_id,
                $booking->payable_amount,
                Constants::debit,
                Constants::purchase,
                null,
            );
        }

        $booking = Bookings::where('id', $booking->id)->first();

        // Sending push to user
        $title = "Booking :" . $booking->booking_id;
        $message = "Booking has been placed successfully!";
        GlobalFunction::sendPushToUser($title, $message, $user);

        // Send push to salon
        $title = "New Booking Request Received";
        $message = "Review the details and Accept or Reject";
        GlobalFunction::sendPushToSalon($title, $message, $salon);
        // Send push to staff
        GlobalFunction::sendPushToStaff($title, $message, $staff);

        return GlobalFunction::sendDataResponse(true, 'Booking placed successfully', $booking);
    }

    function fetchCoupons(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }
        $data = Coupons::whereNotIn('id', explode(',', $user->coupons_used))->orderBy('id', 'DESC')->get();
        return GlobalFunction::sendDataResponse(true, 'coupons fetched successfully', $data);
    }
}
