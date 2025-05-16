<?php

namespace App\Http\Controllers;

use App\Models\Banners;
use App\Models\Bookings;
use App\Models\Constants;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use App\Models\SalonAvailability;
use App\Models\SalonAwards;
use App\Models\SalonBankAccounts;
use App\Models\SalonBookingSlots;
use App\Models\SalonCategories;
use App\Models\SalonEarningHistory;
use App\Models\SalonGallery;
use App\Models\SalonImages;
use App\Models\SalonNotifications;
use App\Models\SalonPayoutHistory;
use App\Models\SalonReviews;
use App\Models\Salons;
use App\Models\ServiceImages;
use App\Models\Services;
use App\Models\Staff;
use App\Models\StaffSlots;
use App\Models\Taxes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SalonController extends Controller
{
    function staff(){
        return view('staff');
    }
    function viewStaff($id){

        $staff = Staff::find($id);

        $slots = StaffSlots::where('staff_id', $id)->get();
        foreach ($slots as $slot) {
            $slot->time = GlobalFunction::formateTimeString($slot->time);
        }

        $mondaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 1;
        });
        $mondaySlots = GlobalFunction::sortSlotsByTime($mondaySlots);

        $tuesdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 2;
        });
        $tuesdaySlots = GlobalFunction::sortSlotsByTime($tuesdaySlots);

        $wednesdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 3;
        });
        $wednesdaySlots = GlobalFunction::sortSlotsByTime($wednesdaySlots);

        $thursdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 4;
        });
        $thursdaySlots = GlobalFunction::sortSlotsByTime($thursdaySlots);

        $fridaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 5;
        });
        $fridaySlots = GlobalFunction::sortSlotsByTime($fridaySlots);

        $saturdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 6;
        });
        $saturdaySlots = GlobalFunction::sortSlotsByTime($saturdaySlots);

        $sundaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 7;
        });
        $sundaySlots = GlobalFunction::sortSlotsByTime($sundaySlots);

        return view('viewStaff',[
            'staff' => $staff,
            'slots' => array(
                'mondaySlots' => $mondaySlots,
                'tuesdaySlots' => $tuesdaySlots,
                'wednesdaySlots' => $wednesdaySlots,
                'thursdaySlots' => $thursdaySlots,
                'fridaySlots' => $fridaySlots,
                'saturdaySlots' => $saturdaySlots,
                'sundaySlots' => $sundaySlots,
            )
        ]);
    }


    function fetchAllStaffOfSalon(Request $request){
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

        $staff = Staff::where('salon_id', $salon->id)
        ->withCount(['bookings'])
        ->where('is_deleted', 0)
        ->orderBy('id', 'DESC')->get();

        return GlobalFunction::sendDataResponse(true, 'Staff fetched successfully!', $staff);
    }
    function deleteStaff(Request $request){
        $rules = [
            'salon_id' => 'required',
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::with(['images', 'slots'])->where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "Staff doesn't exists!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        if ($salon->id != $staff->salon_id) {
            return response()->json(['status' => false, 'message' => "This staff is not allowed with this salon!"]);
        }
        $staff->is_deleted = 1;
        $staff->save();

        return GlobalFunction::sendSimpleResponse(true,'staff deleted successfully!');
    }
    function editStaff_Admin(Request $request){

        $staff =  Staff::find($request->id);

        $staff->name = $request->name;
        $staff->phone = $request->phone;

        $staff->gender = $request->gender;

        if($request->has('photo')){
            $staff->photo = GlobalFunction::saveFileAndGivePath($request->photo);
        }

        $staff->save();

        return GlobalFunction::sendSimpleResponse(true,'staff edited successfully!');
    }
    function editStaff(Request $request){
        $rules = [
            'salon_id' => 'required',
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::with(['images', 'slots'])->where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "Staff doesn't exists!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        if ($salon->id != $staff->salon_id) {
            return response()->json(['status' => false, 'message' => "This staff is not allowed with this salon!"]);
        }

        if($request->has('photo')){
            $staff->photo = GlobalFunction::saveFileAndGivePath($request->photo);
        }
        if($request->has('device_token')){
            $staff->device_token = $request->device_token;
        }
        if($request->has('name')){
            $staff->name = $request->name;
        }
        if($request->has('phone')){
            $staff->phone = $request->phone;
        }
        if($request->has('gender')){
            $staff->gender = $request->gender;
        }
        if($request->has('password')){
            $staff->password = $request->password;
        }

        $staff->save();

        $staff = Staff::where('id', $staff->id)->withCount(['bookings'])->first();

        return GlobalFunction::sendDataResponse(true,'staff edited successfully!', $staff);
    }
    function changeStaffStatus(Request $request){
        $rules = [
            'salon_id' => 'required',
            'staff_id' => 'required',
            'status' => Rule::in([1,0]),
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::with(['images', 'slots'])->where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        if ($salon->id != $staff->salon_id) {
            return response()->json(['status' => false, 'message' => "This staff is not allowed with this salon!"]);
        }

        $staff->status = $request->status;
        $staff->save();

        return GlobalFunction::sendSimpleResponse(true,'staff status changed successfully!');
    }
    function addStaffToSalon(Request $request){
        $rules = [
            'salon_id' => 'required',
            'photo' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'gender' => Rule::in([1,0]),
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::with(['images', 'slots'])->where('id', $request->salon_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }

        $staff = Staff::where([
            'phone'=> $request->phone,
            'salon_id'=> $request->salon_id,
        ])->first();

        if($staff != null){
            return GlobalFunction::sendSimpleResponse(false,'barber with this phone number exists already');
        }

        $staff = new Staff();
        $staff->name = $request->name;
        $staff->phone = $request->phone;
        $staff->salon_id = $request->salon_id;
        $staff->gender = $request->gender;
        $staff->password = $request->password;
        $staff->photo = GlobalFunction::saveFileAndGivePath($request->photo);
        $staff->save();

        return GlobalFunction::sendSimpleResponse(true,'staff added successfully!');
    }
    function deleteBookingSlots(Request $request)
    {
        $rules = [
            'slot_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $slot = SalonBookingSlots::find($request->slot_id);
        if ($slot == null) {
            return GlobalFunction::sendSimpleResponse(false, 'Slot does not Exists');
        }
        $slot->delete();

        return GlobalFunction::sendSimpleResponse(true, 'This Slot deleted successfully!');
    }
    function addBookingSlots(Request $request)
    {
        $rules = [
            'time' => 'required',
            'weekday' => 'required',
            'salon_id' => 'required',
            'booking_limit' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        if ($salon == null) {
            return GlobalFunction::sendSimpleResponse(false, 'Salon does not exists!');
        }

        $slot = SalonBookingSlots::where('time', $request->time)
            ->where('weekday', $request->weekday)
            ->where('salon_id', $salon->id)
            ->first();

        if ($slot == null) {
            $slot = new SalonBookingSlots();
            $slot->time = $request->time;
            $slot->weekday = $request->weekday;
            $slot->salon_id = $request->salon_id;
            $slot->booking_limit = $request->booking_limit;
            $slot->save();

            $salon = Salons::with(['images', 'bankAccount', 'slots'])->find($request->salon_id);
            return GlobalFunction::sendDataResponse(true, 'Slot added successfully', $salon);
        } else {
            return GlobalFunction::sendSimpleResponse(false, 'This Slot is available already!');
        }
    }

    function fetchSalonEarningsList(Request $request)
    {
        $totalData =  SalonEarningHistory::where('salon_id', $request->salonId)->with('booking')->count();
        $rows = SalonEarningHistory::where('salon_id', $request->salonId)->with('booking')->orderBy('id', 'DESC')->get();
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
            $result = SalonEarningHistory::where('salon_id', $request->salonId)->with('booking')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonEarningHistory::where('salon_id', $request->salonId)
                ->with('booking')
                ->where(function ($query) use ($search) {
                    $query->Where('earning_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonEarningHistory::where('salon_id', $request->salonId)
                ->with('booking')
                ->where(function ($query) use ($search) {
                    $query->Where('earning_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $data[] = array(
                $item->earning_number,
                $item->booking->booking_id,
                $settings->currency . $item->amount,
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

    function SubmitSalonWithdrawRequest(Request $request)
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
        $settings = GlobalSettings::first();
        if ($salon->wallet < $settings->min_amount_payout_salon) {
            return response()->json(['status' => false, 'message' => "Insufficient amount to withdraw!"]);
        }

        $item = new SalonPayoutHistory();
        $item->request_number = GlobalFunction::generateSalonWithdrawRequestNumber();
        $item->amount = $salon->wallet;
        $item->salon_id = $salon->id;
        $item->save();

        $summary = 'Withdraw request :' . $item->request_number;
        // Adding wallet statement
        GlobalFunction::addSalonStatementEntry(
            $salon->id,
            null,
            $salon->wallet,
            Constants::debit,
            Constants::salonWalletWithdraw,
            $summary
        );

        //resetting users wallet
        $salon->wallet = 0;
        $salon->save();

        return GlobalFunction::sendSimpleResponse(true, 'Salon withdraw request submitted successfully!');
    }

    function rejectSalonWithdrawal(Request $request)
    {
        $item = SalonPayoutHistory::find($request->id);
        if ($request->has('summary')) {
            $item->summary = $request->summary;
        }
        $item->status = Constants::statusWithdrawalRejected;
        $item->save();

        $summary = '(Rejected) Withdraw request :' . $item->request_number;
        // Adding wallet statement
        GlobalFunction::addSalonStatementEntry(
            $item->salon->id,
            null,
            $item->amount,
            Constants::credit,
            Constants::salonWalletPayoutReject,
            $summary
        );

        //adding money to user wallet
        $item->salon->wallet = $item->salon->wallet + $item->amount;
        $item->salon->save();

        return GlobalFunction::sendSimpleResponse(true, 'request rejected successfully');
    }

    function completeSalonWithdrawal(Request $request)
    {
        $item = SalonPayoutHistory::find($request->id);
        if ($request->has('summary')) {
            $item->summary = $request->summary;
        }
        $item->status = Constants::statusWithdrawalCompleted;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'request completed successfully');
    }
    function fetchSalonRejectedWithdrawalsList(Request $request)
    {
        $totalData =  SalonPayoutHistory::where('status', Constants::statusWithdrawalRejected)->with('salon')->count();
        $rows = SalonPayoutHistory::where('status', Constants::statusWithdrawalRejected)->with('salon')->orderBy('id', 'DESC')->get();
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
            $result = SalonPayoutHistory::where('status', Constants::statusWithdrawalRejected)
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonPayoutHistory::where('status', Constants::statusWithdrawalRejected)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonPayoutHistory::where('status', Constants::statusWithdrawalRejected)
                ->with('salon')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $bankAccount = $item->salon->bankAccount;

            $holder = '<span class="text-dark font-weight-bold font-14">' . $bankAccount->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $bankAccount->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $bankAccount->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $bankAccount->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Rejected') . '</span>';
            $amountData = $amount . $status;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }



            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $salon,
                $item->summary
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
    function fetchSalonCompletedWithdrawalsList(Request $request)
    {
        $totalData =  SalonPayoutHistory::where('status', Constants::statusWithdrawalCompleted)->with('salon')->count();
        $rows = SalonPayoutHistory::where('status', Constants::statusWithdrawalCompleted)->with('salon')->orderBy('id', 'DESC')->get();
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
            $result = SalonPayoutHistory::where('status', Constants::statusWithdrawalCompleted)
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonPayoutHistory::where('status', Constants::statusWithdrawalCompleted)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonPayoutHistory::where('status', Constants::statusWithdrawalCompleted)
                ->with('salon')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $bankAccount = $item->salon->bankAccount;

            $holder = '<span class="text-dark font-weight-bold font-14">' . $bankAccount->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $bankAccount->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $bankAccount->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $bankAccount->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            $amountData = $amount . $status;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $salon,
                $item->summary
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
    function fetchSalonPendingWithdrawalsList(Request $request)
    {
        $totalData =  SalonPayoutHistory::where('status', Constants::statusWithdrawalPending)->with('salon')->count();
        $rows = SalonPayoutHistory::where('status', Constants::statusWithdrawalPending)->with('salon')->orderBy('id', 'DESC')->get();
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
            $result = SalonPayoutHistory::where('status', Constants::statusWithdrawalPending)
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonPayoutHistory::where('status', Constants::statusWithdrawalPending)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->with('salon')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonPayoutHistory::where('status', Constants::statusWithdrawalPending)
                ->with('salon')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $bankAccount = $item->salon->bankAccount;

            $holder = '<span class="text-dark font-weight-bold font-14">' . $bankAccount->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $bankAccount->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $bankAccount->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $bankAccount->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            $amountData = $amount . $status;

            $complete = '<a href="" class="mr-2 btn btn-success text-white complete" rel=' . $item->id . ' >' . __("Complete") . '</a>';
            $reject = '<a href="" class="mr-2 btn btn-danger text-white reject" rel=' . $item->id . ' >' . __("Reject") . '</a>';
            // $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $complete . $reject;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }




            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $salon,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action
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
    function fetchSalonPayoutRequestsList(Request $request)
    {
        $salonId = $request->salonId;
        $totalData =  SalonPayoutHistory::where('salon_id', $salonId)->with(['salon', 'salon.bankAccount'])->count();
        $rows = SalonPayoutHistory::where('salon_id', $salonId)->with(['salon', 'salon.bankAccount'])->orderBy('id', 'DESC')->get();
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
            $result = SalonPayoutHistory::where('salon_id', $salonId)->with(['salon', 'salon.bankAccount'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result = SalonPayoutHistory::where('salon_id', $salonId)->with(['salon', 'salon.bankAccount'])
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonPayoutHistory::where('salon_id', $salonId)->with(['salon', 'salon.bankAccount'])
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('salon', function ($query) use ($search) {
                            $query->Where('salon_name', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $bankAccount = $item->salon->bankAccount;

            $holder = '<span class="text-dark font-weight-bold font-14">' . $bankAccount->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $bankAccount->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $bankAccount->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $bankAccount->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            $complete = '<a href="" class="mr-2 btn btn-success text-white complete" rel=' . $item->id . ' >' . __("Complete") . '</a>';
            $reject = '<a href="" class="mr-2 btn btn-danger text-white reject" rel=' . $item->id . ' >' . __("Reject") . '</a>';
            // $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = '';

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = "";
            if ($item->status == Constants::statusWithdrawalPending) {
                $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
                $action =  $complete . $reject;
            }
            if ($item->status == Constants::statusWithdrawalCompleted) {
                $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            }
            if ($item->status == Constants::statusWithdrawalRejected) {
                $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Rejected') . '</span>';
            }
            $amountData = $amount . $status;

            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $item->created_at,
                $item->salon->salon_name,
                $item->summary,
                GlobalFunction::formateDatabaseTime($item->created_at),
                $action
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
    function salonWithdraws(Request $request)
    {
        return view('salonWithdraws');
    }
    function deleteReview($id)
    {
        $review = SalonReviews::find($id);
        $salon = $review->salon;
        $review->delete();

        $salon->rating = $salon->avgRating();
        $salon->save();

        return Globalfunction::sendSimpleResponse(true, 'rating deleted successfully !');
    }
    function deleteAward($id)
    {
        $award = SalonAwards::find($id);
        $award->delete();
        return Globalfunction::sendSimpleResponse(true, 'Award deleted successfully !');
    }

    function fetchSalonReviewsList(Request $request)
    {
        $totalData =  SalonReviews::with('booking')->where('salon_id', $request->salonId)->count();
        $rows = SalonReviews::with('booking')->where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();

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
            $result = SalonReviews::with('booking')
                ->where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonReviews::with('booking')->where('salon_id', $request->salonId)
                ->whereHas('booking', function ($q) use ($search) {
                    $q->where('booking_id', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonReviews::with('booking')->where('salon_id', $request->salonId)
                ->whereHas('booking', function ($q) use ($search) {
                    $q->where('booking_id', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $starDisabled = '<i class="fas fa-star starDisabled"></i>';
            $starActive = '<i class="fas fa-star starActive"></i>';

            $ratingBar = '';
            for ($i = 0; $i < 5; $i++) {
                if ($item->rating > $i) {
                    $ratingBar = $ratingBar . $starActive;
                } else {
                    $ratingBar = $ratingBar . $starDisabled;
                }
            }

            $action = $delete;
            $data[] = array(
                $ratingBar,
                $item->comment,
                $item->booking != null ? $item->booking->booking_id : '',
                $item->created_at->diffForHumans(),
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
    function fetchSalonAwardsList(Request $request)
    {
        $totalData =  SalonAwards::where('salon_id', $request->salonId)->count();
        $rows = SalonAwards::where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();

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
            $result = SalonAwards::where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonAwards::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('description', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('award_by', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonAwards::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('description', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('award_by', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $action =  $delete;
            $data[] = array(
                $item->title,
                $item->award_by,
                $item->description,
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
    function fetchStaffList(Request $request)
    {
        $totalData =  Staff::count();
        $rows = Staff::orderBy('id', 'DESC')->get();

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
            $result = Staff::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Staff::where(function ($query) use ($search) {
                    $query->Where('name', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Staff::where(function ($query) use ($search) {
                    $query->Where('name', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            if ($item->photo == null) {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($item->photo);
                $image = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $view = '<a href='.route('viewStaff', $item->id).' class="mr-2 btn btn-primary text-white view" rel=' . $item->id . ' >' . __("View") . '</a>';

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $name = '<p class="mb-0" >'.$item->name.'</p>';
            $phone = '<span>'.$item->phone.'</span><br>';
            $rating = '<span  class="badge bg-warning text-white "><i class="fas fa-star"></i> ' .GlobalFunction::formateLongFloatNumber($item->rating) . '</span>';

            $isDeleted = '<span  class="badge bg-success text-white d-inline-block ml-2">Active</span>';
            if($item->is_deleted ==1){
                $isDeleted = '<span  class="badge bg-danger text-white d-inline-block ml-2">Deleted</span>';
            }


            // status
            $onOff = "";
            if ($item->status == 1) {
                $onOff = '<label class="switch m-0 mr-2">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff" checked>
                                <span class="slider round"></span>
                            </label>';
            } else {
                $onOff = '<label class="switch m-0 mr-2">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff">
                                <span class="slider round"></span>
                            </label>';
            }

            $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">
            ' . $item->salon->salon_name . '</span></a>';

            $details = $name.$phone.$rating.$isDeleted;

            $bookingsCount = Bookings::where('staff_id', $item->id)->count();

            $action = '<div class="d-flex align-items-center ">'. $onOff.$view. '</div>';

            $data[] = array(
                $image,
                $details,
                $salon,
                $bookingsCount,
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
    function fetchSalonStaffList(Request $request)
    {
        $totalData =  Staff::where('is_deleted', 0)->where('salon_id', $request->salonId)->count();
        $rows = Staff::where('is_deleted', 0)->where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();

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
            $result = Staff::where('is_deleted', 0)
                ->where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Staff::where('is_deleted', 0)
                ->where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('name', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Staff::where('is_deleted', 0)
                ->where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('name', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            if ($item->photo == null) {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($item->photo);
                $image = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $view = '<a href='.route('viewStaff', $item->id).' class="mr-2 btn btn-primary text-white view" rel=' . $item->id . ' >' . __("View") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $name = '<p class="mb-0" >'.$item->name.'</p>';
            $phone = '<span>'.$item->phone.'</span><br>';
            $rating = '<span  class="badge bg-warning text-white "><i class="fas fa-star"></i> ' .GlobalFunction::formateLongFloatNumber($item->rating) . '</span>';

            // status
            $onOff = "";
            if ($item->status == 1) {
                $onOff = '<label class="switch m-0 mr-2">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff" checked>
                                <span class="slider round"></span>
                            </label>';
            } else {
                $onOff = '<label class="switch m-0 mr-2">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff">
                                <span class="slider round"></span>
                            </label>';
            }

            $details = $name.$phone.$rating;

            $bookingsCount = Bookings::where('staff_id', $item->id)->count();

            $action = '<div class="d-flex align-items-center ">'. $onOff.$view . $delete . '</div>';

            $data[] = array(
                $image,
                $details,
                $bookingsCount,
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
    function fetchSalonGalleryList(Request $request)
    {
        $totalData =  SalonGallery::where('salon_id', $request->salonId)->count();
        $rows = SalonGallery::where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();

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
            $result = SalonGallery::where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonGallery::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('description', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonGallery::where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('description', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            if ($item->image == null) {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($item->image);
                $image = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $view = '<a href="" data-desc="' . $item->description . '" data-image=' . $item->image . ' class="mr-2 btn btn-primary text-white view" rel=' . $item->id . ' >' . __("View") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $action = $view . $delete;
            $data[] = array(
                $image,
                $item->description,
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

    function addImagesToSalon(Request $request)
    {
        foreach ($request->images as $img) {
            $salonImg = new SalonImages();
            $salonImg->salon_id = $request->id;
            $salonImg->image = GlobalFunction::saveFileAndGivePath($img);
            $salonImg->save();
        }

        return Globalfunction::sendSimpleResponse(true, 'Images saved successfully!');
    }
    function deleteSalonImage($id)
    {
        $salonImg = SalonImages::find($id);
        GlobalFunction::deleteFile($salonImg->image);
        $salonImg->delete();

        return Globalfunction::sendSimpleResponse(true, 'image deleted successfully');
    }
    function deleteStaffItem($id)
    {
        $item = Staff::find($id);
        $item->is_deleted = 1;
        $item->save();

        return Globalfunction::sendSimpleResponse(true, 'Staff item deleted successfully');
    }
    function deleteGalleryItem($id)
    {
        $item = SalonGallery::find($id);
        GlobalFunction::deleteFile($item->image);
        $item->delete();

        return Globalfunction::sendSimpleResponse(true, 'gallery item deleted successfully');
    }
    function updateSalonDetails_Admin(Request $request)
    {
        $salon = Salons::find($request->id);
        $salon->salon_name = $request->salon_name;
        $salon->salon_phone = $request->salon_phone;
        $salon->gender_served = $request->gender_served;
        $salon->salon_about = $request->salon_about;
        $salon->salon_address = $request->salon_address;
        $salon->save();
        return Globalfunction::sendSimpleResponse(true, 'Details Updated successfully');
    }
    function viewSalonProfile($salonId)
    {
        $salon = Salons::with(['images', 'bankAccount'])->find($salonId);
        $settings = GlobalSettings::first();
        $salonCats = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
        $salon->mon_fri_from = GlobalFunction::formateTimeString($salon->mon_fri_from);
        $salon->mon_fri_to = GlobalFunction::formateTimeString($salon->mon_fri_to);
        $salon->sat_sun_from = GlobalFunction::formateTimeString($salon->sat_sun_from);
        $salon->sat_sun_to = GlobalFunction::formateTimeString($salon->sat_sun_to);

        $slots = SalonBookingSlots::where('salon_id', $salonId)->get();
        foreach ($slots as $slot) {
            $slot->time = GlobalFunction::formateTimeString($slot->time);
        }

        $mondaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 1;
        });
        $tuesdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 2;
        });
        $wednesdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 3;
        });
        $thursdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 4;
        });
        $fridaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 5;
        });
        $saturdaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 6;
        });
        $sundaySlots = array_filter($slots->toArray(), function ($slot) {
            return $slot['weekday'] === 7;
        });


        return view('viewSalon', [
            'salon' => $salon,
            'salonCats' => $salonCats,
            'settings' => $settings,
            'salonStatus' => array(
                'statusSalonSignUpOnly' => Constants::statusSalonSignUpOnly,
                'statusSalonPending' => Constants::statusSalonPending,
                'statusSalonActive' => Constants::statusSalonActive,
                'statusSalonBanned' => Constants::statusSalonBanned,
            ),
            'slots' => array(
                'mondaySlots' => $mondaySlots,
                'tuesdaySlots' => $tuesdaySlots,
                'wednesdaySlots' => $wednesdaySlots,
                'thursdaySlots' => $thursdaySlots,
                'fridaySlots' => $fridaySlots,
                'saturdaySlots' => $saturdaySlots,
                'sundaySlots' => $sundaySlots,
            )
        ]);
    }
    function banSalon($id)
    {
        $salon = Salons::find($id);
        $salon->status = Constants::statusSalonBanned;
        $salon->save();
        return GlobalFunction::sendSimpleResponse(true, 'Salon banned successfully!');
    }
    function activateSalon($id)
    {
        $salon = Salons::find($id);
        $salon->status = Constants::statusSalonActive;
        $salon->save();
        return GlobalFunction::sendSimpleResponse(true, 'Salon activated successfully!');
    }
    function fetchPendingSalonList(Request $request)
    {
        $totalData =  Salons::where('status', Constants::statusSalonPending)->count();
        $rows = Salons::with(['images', 'bankAccount'])->orderBy('id', 'DESC')->where('status', Constants::statusSalonPending)->get();

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
            $result = Salons::with(['images', 'bankAccount'])
                ->offset($start)
                ->limit($limit)
                ->where('status', Constants::statusSalonPending)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonPending)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonPending)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {


            $imgUrl = GlobalFunction::createMediaUrl($item->owner_photo);

            if ($item->owner_photo != null) {
                $ownerImage = '<img src="' . $imgUrl . '" width="50" height="50">';
            } else {
                $ownerImage = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            }

            $view = '<a href="' . route('viewSalonProfile', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            // $delete = '<a href="" class="mr-2 btn btn-danger text-white " rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $ban = '<a href="" class="mr-2 btn btn-danger text-white ban" rel=' . $item->id . ' >' . __("Ban") . '</a>';

            $action = $view  . $ban;

            $gender = "";
            if ($item->gender_served == Constants::salonGenderMale) {
                $gender = '<span  class="badge bg-info text-white ">' . __("Male") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderFemale) {
                $gender = '<span  class="badge bg-danger text-white ">' . __("Female") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderUnisex) {
                $gender = '<span  class="badge bg-primary text-white ">' . __("Unisex") . '</span>';
            }

            $salonContact = "";
            $salonContact = '<p>' . $item->email . '<br>' . $item->salon_phone . '</p>';

            $data[] = array(
                $item->salon_number,
                $item->salon_name,
                $gender,
                $salonContact,
                $item->owner_name,
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
    function fetchSignUpOnlySalonList(Request $request)
    {
        $totalData =  Salons::where('status', Constants::statusSalonSignUpOnly)->count();
        $rows = Salons::with(['images', 'bankAccount'])->orderBy('id', 'DESC')->where('status', Constants::statusSalonSignUpOnly)->get();

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
            $result = Salons::with(['images', 'bankAccount'])
                ->offset($start)
                ->limit($limit)
                ->where('status', Constants::statusSalonSignUpOnly)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonSignUpOnly)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonSignUpOnly)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $view = '<a href="' . route('viewSalonProfile', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';


            $action = $view;

            $salonContact = "";
            $salonContact = '<p>' . $item->email . '<br>' . $item->salon_phone . '</p>';

            $data[] = array(
                $item->salon_number,
                $salonContact,
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
    function fetchBannedSalonList(Request $request)
    {
        $totalData =  Salons::where('status', Constants::statusSalonBanned)->count();
        $rows = Salons::with(['images', 'bankAccount'])->orderBy('id', 'DESC')->where('status', Constants::statusSalonBanned)->get();

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
            $result = Salons::with(['images', 'bankAccount'])
                ->offset($start)
                ->limit($limit)
                ->where('status', Constants::statusSalonBanned)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonBanned)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonBanned)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {


            $imgUrl = GlobalFunction::createMediaUrl($item->owner_photo);

            if ($item->owner_photo != null) {
                $ownerImage = '<img src="' . $imgUrl . '" width="50" height="50">';
            } else {
                $ownerImage = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            }


            $view = '<a href="' . route('viewSalonProfile', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            // $delete = '<a href="" class="mr-2 btn btn-danger text-white " rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $ban = '<a href="" class="mr-2 btn btn-success text-white activate" rel=' . $item->id . ' >' . __("Activate") . '</a>';

            $gender = "";
            if ($item->gender_served == Constants::salonGenderMale) {
                $gender = '<span  class="badge bg-info text-white ">' . __("Male") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderFemale) {
                $gender = '<span  class="badge bg-danger text-white ">' . __("Female") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderUnisex) {
                $gender = '<span  class="badge bg-primary text-white ">' . __("Unisex") . '</span>';
            }

            $salonContact = "";
            $salonContact = '<p>' . $item->email . '<br>' . $item->salon_phone . '</p>';


            $action = $view  . $ban;

            $data[] = array(
                $item->salon_number,
                $item->salon_name,
                $gender,
                $salonContact,
                $item->owner_name,
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
    function fetchActiveSalonList(Request $request)
    {
        $totalData =  Salons::where('status', Constants::statusSalonActive)->count();
        $rows = Salons::with(['images', 'bankAccount'])->orderBy('id', 'DESC')->where('status', Constants::statusSalonActive)->get();

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
            $result = Salons::with(['images', 'bankAccount'])
                ->offset($start)
                ->limit($limit)
                ->where('status', Constants::statusSalonActive)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonActive)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Salons::with(['images', 'bankAccount'])
                ->where('status', Constants::statusSalonActive)
                ->where(function ($query) use ($search) {
                    $query->Where('salon_number', 'LIKE', "%{$search}%")
                        ->orWhere('salon_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();

        $settings = GlobalSettings::first();

        foreach ($result as $item) {


            $imgUrl = GlobalFunction::createMediaUrl($item->owner_photo);

            if ($item->owner_photo != null) {
                $ownerImage = '<img src="' . $imgUrl . '" width="50" height="50">';
            } else {
                $ownerImage = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            }


            $view = '<a href="' . route('viewSalonProfile', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            // $delete = '<a href="" class="mr-2 btn btn-danger text-white " rel=' . $item->id . ' >' . __("Delete") . '</a>';

            $ban = '<a href="" class="mr-2 btn btn-danger text-white ban" rel=' . $item->id . ' >' . __("Ban") . '</a>';

            $gender = "";
            if ($item->gender_served == Constants::salonGenderMale) {
                $gender = '<span  class="badge bg-info text-white ">' . __("Male") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderFemale) {
                $gender = '<span  class="badge bg-danger text-white ">' . __("Female") . '</span>';
            } else if ($item->gender_served == Constants::salonGenderUnisex) {
                $gender = '<span  class="badge bg-primary text-white ">' . __("Unisex") . '</span>';
            }

            $topRated = "";
            if ($item->top_rated == 1) {
                $topRated = '<label class="switch ">
                                <input rel=' . $item->id . ' type="checkbox" class="topRated" checked>
                                <span class="slider round"></span>
                            </label>';
            } else {
                $topRated = '<label class="switch ">
                                <input rel=' . $item->id . ' type="checkbox" class="topRated">
                                <span class="slider round"></span>
                            </label>';
            }

            $salonContact = "";
            $salonContact = '<p>' . $item->email . '<br>' . $item->salon_phone . '</p>';
            $lifetimeEarning = $settings->currency . $item->lifetime_earnings;

            $action = $view  . $ban;

            $data[] = array(
                $item->salon_number,
                $item->salon_name,
                $gender,
                $lifetimeEarning,
                $topRated,
                $salonContact,
                $item->owner_name,
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
    function changeSalonTopRatedStatus($id, $status)
    {
        $item = Salons::find($id);
        $item->top_rated = $status;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'Status changed successfully');
    }
    function salons(Request $request)
    {
        return view('salons');
    }
    function fetchSalonByCoordinates(Request $request)
    {
        $rules = [
            'lat' => 'required',
            'long' => 'required',
            'km' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salons = Salons::with(['images', 'slots'])
            ->where('on_vacation', 0)
            ->where('status', Constants::statusSalonActive)
            ->get();

        if ($request->has('category_id')) {
            $salons = Salons::with(['images', 'slots'])
                ->whereRaw("find_in_set($request->category_id , salon_categories)")
                ->where('on_vacation', 0)
                ->where('status', Constants::statusSalonActive)
                ->get();
        }

        $salonData = [];
        foreach ($salons as $salon) {
            $distance = GlobalFunction::point2point_distance($request->lat, $request->long, $salon->salon_lat, $salon->salon_long, "K", $request->km);

            $categories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
            $salon->salonCats = $categories;

            if ($distance) {
                array_push($salonData, $salon);
            }
        }

        return GlobalFunction::sendDataResponse(true, 'Data fetched successfully', $salonData);
    }
    function deleteMySalonAccount(Request $request)
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

        if (in_array($salon->email, ["jeelkhokhariya59@gmail.com"])) {
            return GlobalFunction::sendSimpleResponse(false, 'This account can not be deleted! Please log out.');
        }

        SalonImages::where('salon_id', $salon->id)->delete();

        $services = Services::where('salon_id', $salon->id)->get();
        foreach ($services as $service) {
            ServiceImages::where('service_id', $service->id)->delete();
        }
        Services::where('salon_id', $salon->id)->delete();

        SalonAwards::where('salon_id', $salon->id)->delete();
        SalonGallery::where('salon_id', $salon->id)->delete();
        SalonBankAccounts::where('salon_id', $salon->id)->delete();
        SalonBookingSlots::where('salon_id', $salon->id)->delete();
        Staff::where('salon_id', $salon->id)->delete();
        $salon->delete();

        return GlobalFunction::sendSimpleResponse(true, "Salon & Data deleted successfully");
    }
    function fetchSalonReviews(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
            'salon_id' => 'required',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $result =  SalonReviews::with(['user', 'salon'])
            ->Where('salon_id', $request->salon_id)
            ->whereHas('user')
            ->whereHas('salon')
            ->orderBy('id', 'DESC')
            ->offset($request->start)
            ->limit($request->count)
            ->get();

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $result);
    }
    function fetchSalonDetails(Request $request)
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

        $salon = Salons::with(['gallery', 'awards', 'images', 'slots'])
            ->withCount('reviews')
            ->where('id', $request->salon_id)
            ->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $categories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
        foreach ($categories as $category) {
            $services = Services::where('category_id', $category->id)
                ->where('salon_id', $salon->id)
                ->where('status', Constants::statusServiceOn)
                ->with(['images', 'category'])
                ->orderBy('id', 'DESC')
                ->get();
            $category->services = $services;
        }
        $reviews = SalonReviews::where('salon_id', $salon->id)->orderBy('id', 'DESC')->with('user')->limit(5)->get();
        $salon->categories = $categories;
        $salon->reviews = $reviews;

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $salon);
    }
    function searchSalon(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $result =  Salons::with(['images', 'slots'])
            ->Where('salon_name', 'LIKE', "%{$request->keyword}%")
            ->where('on_vacation', 0)
            ->where('status', Constants::statusSalonActive)
            ->offset($request->start)
            ->limit($request->count)
            ->get();

        if ($request->has('category_id')) {
            $result =  Salons::with(['images', 'slots'])
                ->Where('salon_name', 'LIKE', "%{$request->keyword}%")
                ->where('on_vacation', 0)
                ->where('status', Constants::statusSalonActive)
                ->whereRaw("find_in_set($request->category_id , salon_categories)")
                ->offset($request->start)
                ->limit($request->count)
                ->get();
        }

        foreach ($result as $salon) {
            $categories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
            $salon->salonCats = $categories;
        }

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $result);
    }
    function searchServices(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $result =  Services::with(['images', 'salon'])
            ->Where('title', 'LIKE', "%{$request->keyword}%")
            ->where('status', Constants::statusServiceOn)
            ->whereHas('salon', function ($query) {
                $query->where('on_vacation', 0)
                    ->where('status', Constants::statusSalonActive);
            })
            ->offset($request->start)
            ->limit($request->count)
            ->get();

        if ($request->has('category_id')) {
            $result =  Services::with(['images', 'salon'])
                ->Where('title', 'LIKE', "%{$request->keyword}%")
                ->where('category_id', $request->category_id)
                ->where('status', Constants::statusServiceOn)
                ->whereHas('salon', function ($query) {
                    $query->where('on_vacation', 0)
                        ->where('status', Constants::statusSalonActive);
                })
                ->offset($request->start)
                ->limit($request->count)
                ->get();
        }

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $result);
    }

    function searchTopRatedSalonsOfCategory(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
            'category_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $topRatedSalons = Salons::where('top_rated', 1)
            ->where('on_vacation', 0)
            ->where('status', Constants::statusSalonActive)
            ->Where('salon_name', 'LIKE', "%{$request->keyword}%")
            ->with(['images', 'slots'])
            ->whereRaw("find_in_set($request->category_id , salon_categories)")
            ->offset($request->start)
            ->limit($request->count)
            ->get();

        foreach ($topRatedSalons as $salon) {
            $categories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
            $salon->salonCats = $categories;
        }

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $topRatedSalons);
    }
    function searchServicesOfCategory(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
            'category_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $result =  Services::with(['images', 'salon'])
            ->Where('title', 'LIKE', "%{$request->keyword}%")
            ->where('category_id', $request->category_id)
            ->where('status', Constants::statusServiceOn)
            ->whereHas('salon', function ($query) {
                $query->where('on_vacation', 0)
                    ->where('status', Constants::statusSalonActive);
            })
            ->offset($request->start)
            ->limit($request->count)
            ->get();

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $result);
    }

    function salonAndServiceByCategory(Request $request)
    {
        $rules = [
            'category_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $cat = SalonCategories::find($request->category_id);
        if ($cat == null) {
            return response()->json(['status' => false, 'message' => "Category doesn't exists!"]);
        }
        $topRatedSalons = Salons::whereRaw("find_in_set($request->category_id , salon_categories)")
            ->where('top_rated', 1)
            ->where('on_vacation', 0)
            ->where('status', Constants::statusSalonActive)
            ->with(['images', 'slots'])
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $services = Services::where('category_id', $request->category_id)
            ->inRandomOrder()
            ->whereHas('salon', function ($query) {
                $query->where('on_vacation', 0)
                    ->where('status', Constants::statusSalonActive);
            })
            ->where('status', Constants::statusServiceOn)
            ->with(['images', 'salon'])
            ->limit(20)
            ->get();

        $data = array(
            "topRatedSalons" => $topRatedSalons,
            "services" => $services,
        );
        return GlobalFunction::sendDataResponse(true, 'Data fetch successfully', $data);
    }

    function fetchHomePageData(Request $request)
    {

        $banners = Banners::orderBy('id', 'DESC')->get();
        $categories = SalonCategories::orderBy('id', 'DESC')->where('is_deleted', 0)->get();
        $salonsTopRated = Salons::with(['images', 'slots'])
            ->where('top_rated', 1)
            ->where('on_vacation', 0)
            ->where('status', Constants::statusSalonActive)
            ->inRandomOrder()
            ->get();

        foreach ($salonsTopRated as $salon) {
            $saloncategories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
            $salon->salonCats = $saloncategories;
        }

        $categoriesWithServices = [];
        foreach ($categories as $cat) {
            $services = Services::where('category_id', $cat->id)
                ->where('status', Constants::statusServiceOn)
                ->whereHas('salon', function ($query) {
                    $query->where('on_vacation', 0)
                        ->where('status', Constants::statusSalonActive);
                })
                ->with(['images'])
                ->get();
            if ($services->count() > 1) {
                $cat->services = $services;
                array_push($categoriesWithServices, $cat);
            }
        }
        $data = array(
            "banners" => $banners,
            "categories" => $categories,
            "topRatedSalons" => $salonsTopRated,
            "categoriesWithService" => $categoriesWithServices,
        );

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $data);
    }

    function fetchGlobalSettings()
    {
        $settings = GlobalSettings::first();
        $categories = SalonCategories::where('is_deleted', 0)->get();
        $taxes = Taxes::where('status', 1)->get();
        $settings->taxes = $taxes;
        $settings->categories = $categories;
        return GlobalFunction::sendDataResponse(true, 'fetched successfully', $settings);
    }
    function deleteSalonGalleryImage(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'gallery_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        $gallery = SalonGallery::where('id', $request->gallery_id)->first();

        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($gallery == null) {
            return response()->json(['status' => false, 'message' => "Gallery Image doesn't exists!"]);
        }
        if ($gallery->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "This salon doesn't own this gallery image!"]);
        }
        GlobalFunction::deleteFile($gallery->image);
        $gallery->delete();
        return GlobalFunction::sendSimpleResponse(true, 'Gallery image deleted successfully!');
    }
    function addSalonGalleryImage(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'image' => 'required',
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
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        $gallery = new SalonGallery();
        $gallery->salon_id = $salon->id;
        if ($request->has('description')) {
            $gallery->description = GlobalFunction::cleanString($request->description);
        }
        $gallery->image = GlobalFunction::saveFileAndGivePath($request->image);
        $gallery->save();
        return GlobalFunction::sendSimpleResponse(true, 'Gallery Image added successfully');
    }
    function deleteSalonAward(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'award_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        $award = SalonAwards::where('id', $request->award_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($award == null) {
            return response()->json(['status' => false, 'message' => "Award doesn't exists!"]);
        }
        if ($award->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "This salon doesn't own this award!"]);
        }
        $award->delete();

        return GlobalFunction::sendSimpleResponse(true, 'Award deleted successfully');
    }
    function editSalonAward(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'award_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::where('id', $request->salon_id)->first();
        $award = SalonAwards::where('id', $request->award_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($award == null) {
            return response()->json(['status' => false, 'message' => "Award doesn't exists!"]);
        }
        if ($award->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "This salon doesn't own this award!"]);
        }
        if ($request->has('title')) {
            $award->title = GlobalFunction::cleanString($request->title);
        }
        if ($request->has('award_by')) {
            $award->award_by = GlobalFunction::cleanString($request->award_by);
        }
        if ($request->has('description')) {
            $award->description = GlobalFunction::cleanString($request->description);
        }
        $award->save();
        return GlobalFunction::sendSimpleResponse(true, 'award updated successfully');
    }

    function addSalonAward(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'title' => 'required',
            'award_by' => 'required',
            'description' => 'required',
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
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        $award = new SalonAwards();
        $award->salon_id = $salon->id;
        $award->title = GlobalFunction::cleanString($request->title);
        $award->award_by = GlobalFunction::cleanString($request->award_by);
        $award->description = GlobalFunction::cleanString($request->description);
        $award->save();

        return GlobalFunction::sendSimpleResponse(true, 'Award added successfully');
    }
    function fetchMySalonDetails(Request $request)
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
        $salon = Salons::with(['gallery', 'awards', 'images', 'bankAccount', 'slots'])
            ->withCount('reviews')
            ->where('id', $request->salon_id)
            ->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        $categories = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
        foreach ($categories as $category) {
            $services = Services::where('category_id', $category->id)
                ->where('salon_id', $salon->id)
                ->with('images')
                ->orderBy('id', 'DESC')
                ->get();
            $category->services = $services;
        }
        $salon->categories = $categories;


        return response()->json(['status' => true, 'message' => 'data fetched successfully!', 'data' => $salon]);
    }
    function fetchMySalonReviews(Request $request)
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

        $reviews = SalonReviews::where('salon_id', $request->salon_id)
            ->with(['user'])
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return Globalfunction::sendDataResponse(true, 'reviews fetched successfully', $reviews);
    }
    function fetchSalonNotifications(Request $request)
    {
        $rules = [
            'start' => 'required',
            'count' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salonNotifications = SalonNotifications::offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['status' => true, 'message' => 'Data fetched successfully !', 'data' => $salonNotifications]);
    }
    //
    function fetchSalonCategories(Request $request)
    {
        $salonCats = SalonCategories::where('is_deleted', 0)->get();
        return response()->json(['status' => true, 'message' => 'Data fetched successfully!', 'data' => $salonCats]);
    }
    function updateSalonBankAccount(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'bank_title' => 'required',
            'account_number' => 'required',
            'holder' => 'required',
            'swift_code' => 'required',
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

        $hasChequePhoto = $request->has('cheque_photo');

        $account = SalonBankAccounts::where('salon_id', $salon->id)->first();
        if ($account != null) {
            $account->bank_title = GlobalFunction::cleanString($request->bank_title);
            $account->account_number = GlobalFunction::cleanString($request->account_number);
            $account->holder = GlobalFunction::cleanString($request->holder);
            $account->swift_code = GlobalFunction::cleanString($request->swift_code);
            if ($hasChequePhoto) {
                $account->cheque_photo = GlobalFunction::saveFileAndGivePath($request->cheque_photo);
            }
            $account->save();
            return response()->json(['status' => true, 'message' => "Bank Details updated successfully!"]);
        } else {
            if (!$hasChequePhoto) {
                return response()->json(['status' => false, 'message' => 'Cheque photo is required for new bank accounts!']);
            }
            $account = new SalonBankAccounts();
            $account->salon_id = $salon->id;
            $account->bank_title = $request->bank_title;
            $account->account_number = $request->account_number;
            $account->holder = $request->holder;
            $account->swift_code = $request->swift_code;
            $account->cheque_photo = GlobalFunction::saveFileAndGivePath($request->cheque_photo);
            $account->save();

            // Making salon status pending for verification
            $salon->status = Constants::statusSalonPending;
            $salon->save();
            return response()->json(['status' => true, 'message' => "Bank Details updated successfully!"]);
        }
    }

    function salonRegistration(Request $request)
    {
        $rules = [
            'type' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        // 0=register
        if ($request->type == 0) {
            $rules = [
                'owner_name' => 'required',
                'owner_photo' => 'required',
                'salon_name' => 'required',
                'email' => 'required',
                'device_type' => [Rule::in(Constants::deviceAndroid, Constants::deviceIOS)],
                'device_token' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json(['status' => false, 'message' => $msg]);
            }

            $salon = Salons::where('email', $request->email)->first();
            if ($salon != null) {
                return response()->json(['status' => false, 'message' => 'Salon with this email exists already!']);
            }

            $salon = new Salons();
            $salon->salon_number = GlobalFunction::generateSalonNumber();
            $salon->owner_name = GlobalFunction::cleanString($request->owner_name);
            $salon->salon_name = GlobalFunction::cleanString($request->salon_name);
            $salon->email = $request->email;
            $salon->owner_photo = GlobalFunction::saveFileAndGivePath($request->owner_photo);
            $salon->device_type = $request->device_type;
            $salon->device_token = $request->device_token;
            $salon->owner_photo = GlobalFunction::saveFileAndGivePath($request->owner_photo);
            $salon->save();

            $data = Salons::with(['bankAccount', 'slots'])->find($salon->id);

            return response()->json(['status' => true, 'message' => 'Registration successful!', 'data' => $data]);
            // 1 = login
        } else if ($request->type == 1) {
            $rules = [
                'email' => 'required',
                'device_type' => [Rule::in(Constants::deviceAndroid, Constants::deviceIOS)],
                'device_token' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json(['status' => false, 'message' => $msg]);
            }

            $salon = Salons::where('email', $request->email)->with(['bankAccount', 'images', 'slots'])->first();
            if ($salon == null) {
                return response()->json(['status' => false, 'message' => 'Salon is not available!']);
            }
            $salon->device_type = $request->device_type;
            $salon->device_token = $request->device_token;
            $salon->save();
            $salon = Salons::where('email', $request->email)->with(['bankAccount', 'images', 'slots'])->first();

            return response()->json(['status' => true, 'message' => 'Salon Log in successful!', 'data' => $salon]);
        }
    }

    function updateSalonDetails(Request $request)
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

        $salon = Salons::with(['images', 'slots'])->find($request->salon_id);
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($request->has('salon_name')) {
            $salon->salon_name = GlobalFunction::cleanString($request->salon_name);
        }
        if ($request->has('salon_about')) {
            $salon->salon_about = GlobalFunction::cleanString($request->salon_about);
        }
        if ($request->has('salon_address')) {
            $salon->salon_address = GlobalFunction::cleanString($request->salon_address);
        }
        if ($request->has('salon_phone')) {
            $salon->salon_phone = GlobalFunction::cleanString($request->salon_phone);
        }
        if ($request->has('salon_lat')) {
            $salon->salon_lat = $request->salon_lat;
        }
        if ($request->has('salon_long')) {
            $salon->salon_long = $request->salon_long;
        }
        if ($request->has('mon_fri_from')) {
            $salon->mon_fri_from = $request->mon_fri_from;
        }
        if ($request->has('mon_fri_to')) {
            $salon->mon_fri_to = $request->mon_fri_to;
        }
        if ($request->has('sat_sun_from')) {
            $salon->sat_sun_from = $request->sat_sun_from;
        }
        if ($request->has('sat_sun_to')) {
            $salon->sat_sun_to = $request->sat_sun_to;
        }
        if ($request->has('salon_categories')) {
            $salon->salon_categories = $request->salon_categories;
        }
        if ($request->has('gender_served')) {
            $salon->gender_served = $request->gender_served;
        }
        if ($request->has('is_notification')) {
            $salon->is_notification = $request->is_notification;
        }
        if ($request->has('on_vacation')) {
            $salon->on_vacation = $request->on_vacation;
        }
        if ($request->has('is_pay_after_service')) {
            $salon->is_pay_after_service = $request->is_pay_after_service;
        }
        if ($request->has('is_serve_outside')) {
            $salon->is_serve_outside = $request->is_serve_outside;
        }
        $salon->save();

        // New images add
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $img = new SalonImages();
                $img->salon_id = $salon->id;
                $img->image = GlobalFunction::saveFileAndGivePath($image);
                $img->save();
            }
        }
        // Deleting if Ids Sent
        if ($request->has("deleteImageIds")) {
            $images = SalonImages::whereIn('id', $request->deleteImageIds)->get();
            foreach ($images as $image) {
                GlobalFunction::deleteFile($image->image);
                $image->delete();
            }
        }

        $salon = Salons::with(['images', 'bankAccount', 'slots'])->find($request->salon_id);

        return response()->json(['status' => true, 'message' => "Salon details updated successfully!", 'data' => $salon]);
    }
}
