<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Constants;
use App\Models\GlobalFunction;
use App\Models\Salons;
use App\Models\Staff;
use App\Models\StaffSlots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BarberController extends Controller
{
    //

    function fetchStaffBookingsByDate(Request $request)
    {
        $rules = [
            'staff_id' => 'required',
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
        }
        $bookings = Bookings::where('status', Constants::orderAccepted)
            ->where('date', $request->date)
            ->where('staff_id', $request->staff_id)
            ->with(['user','service_address'])
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Staff Bookings fetched successfully', $bookings);
    }

    function fetchStaffBookingRequests(Request $request)
    {
        $rules = [
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
        }

        $bookings = Bookings::where('staff_id', $request->staff_id)
            ->where('status', Constants::orderPlacedPending)
            ->with(['user','service_address'])
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Staff Bookings Request fetched successfully', $bookings);
    }

    function fetchStaffData(Request $request){
        $rules = [
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $staff = Staff::where('id', $request->staff_id)->with(['salon'])->withCount(['bookings'])->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "can not find staff!"]);
        }

        return GlobalFunction::sendDataResponse(true,'staff data fetched successfully', $staff);
    }

    function logInStaff(Request $request){
        $rules = [
            'salon_number' => 'required',
            'phone' => 'required',
            'device_token' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $salon = Salons::where('salon_number', $request->salon_number)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }

        $staff = Staff::where([
            'phone'=> $request->phone,
            'salon_id'=> $salon->id,
            ])->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "salon number or phone might be wrong!"]);
        }

        // Staff password check
        if($request->password != $staff->password){
            return response()->json(['status' => false, 'message' => "staff password wrong"]);
        }
        // Staff Status check
        if($staff->status == 0){
            return response()->json(['status' => false, 'message' => "staff is disabled by salon"]);
        }
        $staff->device_token = $request->device_token;
        $staff->save();

        return GlobalFunction::sendDataResponse(true, 'staff log in successfully', $staff);

    }

    function fetchAvailableSlotsOfStaff(Request $request){
        $rules = [
            'staff_id' => 'required',
            'weekday' => Rule::in(1,2,3,4,5,6,7),
            'salon_id' => 'required',
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
        }
         $slots = StaffSlots::where([
            'staff_id'=> $staff->id,
            'weekday'=> $request->weekday,
         ])->get();

         foreach($slots as $slot){
            $slot->available = true;
            $booking = Bookings::where('salon_id', $request->salon_id)
            ->where('date', $request->date)
            ->whereIn('status', [Constants::orderPlacedPending, Constants::orderAccepted])
            ->where('time', $slot->time)
            ->where('staff_id', $request->staff_id)
            ->first();

            if($booking != null){
                $slot->available = false;
            }
         }

         return GlobalFunction::sendDataResponse(true,'slots fetched successfully', $slots);
    }

    function fetchStaffSlots(Request $request){
        $rules = [
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
        }

        $slots = StaffSlots::where('staff_id', $request->staff_id)->get();

        return GlobalFunction::sendDataResponse(true, 'slots fetched successfully', $slots);

    }

    function deleteStaffSlot(Request $request)
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
        $slot = StaffSlots::find($request->slot_id);
        if ($slot == null) {
            return GlobalFunction::sendSimpleResponse(false, 'Slot does not Exists');
        }
        $slot->delete();

        return GlobalFunction::sendSimpleResponse(true, 'Slot deleted successfully!');
    }

    function addStaffSlot(Request $request)
    {
        $rules = [
            'time' => 'required',
            'weekday' => 'required',
            'staff_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $staff = Staff::where('id', $request->staff_id)->first();
        if ($staff == null) {
            return response()->json(['status' => false, 'message' => "staff doesn't exists!"]);
        }

        $slot = StaffSlots::where('time', $request->time)
            ->where('weekday', $request->weekday)
            ->where('staff_id', $staff->id)
            ->first();

        if ($slot == null) {
            $slot = new StaffSlots();
            $slot->time = $request->time;
            $slot->weekday = $request->weekday;
            $slot->staff_id = $request->staff_id;
            $slot->save();

            return GlobalFunction::sendSimpleResponse(true, 'Slot added successfully');
        } else {
            return GlobalFunction::sendSimpleResponse(false, 'This Slot is available already!');
        }
    }
}
