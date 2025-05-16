<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Banners;
use App\Models\Bookings;
use App\Models\Constants;
use App\Models\Coupons;
use App\Models\FaqCats;
use App\Models\Faqs;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use App\Models\PlatformEarningHistory;
use App\Models\SalonCategories;
use App\Models\SalonNotifications;
use App\Models\SalonPayoutHistory;
use App\Models\SalonReviews;
use App\Models\Salons;
use App\Models\Taxes;
use App\Models\UserNotification;
use App\Models\UserWalletRechargeLogs;
use App\Models\UserWithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Google\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    function pushNotificationToSingleUser(Request $request){
        $client = new Client();
        $client->setAuthConfig('googleCredentials.json');
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken();
        $accessToken = $accessToken['access_token'];

        // Log::info($accessToken);
        $contents = File::get(base_path('googleCredentials.json'));
        $json = json_decode(json: $contents, associative: true);

        $url = 'https://fcm.googleapis.com/v1/projects/'.$json['project_id'].'/messages:send';
        // $notificationArray = array('title' => $title, 'body' => $message);

        // $device_token = $user->device_token;

        $fields = $request->json()->all();

        // $fields = array(
        //     'message'=> [
        //         'token'=> $device_token,
        //         'notification' => $notificationArray,
        //     ]
        // );

        $headers = array(
            'Content-Type:application/json',
            'Authorization:Bearer ' . $accessToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // print_r(json_encode($fields));
        $result = curl_exec($ch);
        Log::debug($result);

        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        // return $response;
        return response()->json(['result'=> $result, 'fields'=> $fields]);
    }

    function uploadFileGivePath(Request $request)
    {
        $rules = [
            'file' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $path = GlobalFunction::saveFileAndGivePath($request->file);

        return response()->json([
            'status' => true,
            'message' => 'file uploaded successfully',
            'path' => $path
        ]);
    }

    function fetchAllTaxList(Request $request)
    {
        $totalData =  Taxes::count();
        $rows = Taxes::orderBy('id', 'DESC')->get();

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
            $result = Taxes::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Taxes::where(function ($query) use ($search) {
                $query->Where('tax_title', 'LIKE', "%{$search}%")
                    ->orWhere('value', 'LIKE', "%{$search}%");
            })->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Taxes::where(function ($query) use ($search) {
                $query->Where('tax_title', 'LIKE', "%{$search}%")
                    ->orWhere('value', 'LIKE', "%{$search}%");
            })->count();
        }
        $data = array();
        foreach ($result as $item) {

            $type = '';
            if ($item->type == Constants::taxFixed) {
                $type = '<span class="badge bg-primary text-white">' . __('Fixed') . '</span>';
            }
            if ($item->type == Constants::taxPercent) {
                $type = '<span class="badge bg-primary text-white">' . __('Percent') . '</span>';
            }

            $onOff = "";
            if ($item->status == 1) {
                $onOff = '<label class="switch ">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff" checked>
                                <span class="slider round"></span>
                            </label>';
            } else {
                $onOff = '<label class="switch ">
                                <input rel=' . $item->id . ' type="checkbox" class="onoff">
                                <span class="slider round"></span>
                            </label>';
            }

            $edit = '<a data-taxtitle="' . $item->tax_title . '" data-type="' . $item->type . '" href="" data-value="' . $item->value . '"  class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $edit  . $delete;

            $data[] = array(
                $item->tax_title,
                $type,
                $item->value,
                $onOff,
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

    function changeTaxStatus($id, $value)
    {
        $item = Taxes::find($id);
        $item->status = $value;
        $item->save();

        return response()->json(['status' => true, 'message' => 'value changes successfully']);
    }
    function deleteTaxItem($id)
    {
        $item = Taxes::find($id);
        $item->delete();
        return GlobalFunction::sendSimpleResponse(true, 'item deleted successfully!');
    }

    function addTaxItem(Request $request)
    {
        $item = new Taxes();
        $item->tax_title = $request->tax_title;
        $item->value = $request->value;
        $item->type = $request->type;
        $item->status = 1;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'item added successfully!');
    }
    function editTaxItem(Request $request)
    {
        $item = Taxes::find($request->id);
        $item->tax_title = $request->tax_title;
        $item->value = $request->value;
        $item->type = $request->type;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'item edited successfully!');
    }

    function updatePaymentSettings(Request $request)
    {
        $settings = GlobalSettings::first();
        $settings->payment_gateway = $request->payment_gateway;

        $settings->stripe_secret = $request->stripe_secret;
        $settings->stripe_publishable_key = $request->stripe_publishable_key;
        $settings->stripe_currency_code = $request->stripe_currency_code;

        $settings->razorpay_key = $request->razorpay_key;
        $settings->razorpay_currency_code = $request->razorpay_currency_code;

        $settings->paystack_secret_key = $request->paystack_secret_key;
        $settings->paystack_public_key = $request->paystack_public_key;
        $settings->paystack_currency_code = $request->paystack_currency_code;

        $settings->paypal_client_id = $request->paypal_client_id;
        $settings->paypal_secret_key = $request->paypal_secret_key;
        $settings->paypal_currency_code = $request->paypal_currency_code;

        $settings->flutterwave_public_key = $request->flutterwave_public_key;
        $settings->flutterwave_encryption_key = $request->flutterwave_encryption_key;
        $settings->flutterwave_secret_key = $request->flutterwave_secret_key;
        $settings->flutterwave_currency_code = $request->flutterwave_currency_code;

        $settings->sslcommerz_store_id = $request->sslcommerz_store_id;
        $settings->sslcommerz_store_passwd = $request->sslcommerz_store_passwd;
        $settings->sslcommerz_currency_code = $request->sslcommerz_currency_code;

        $settings->save();

        return GlobalFunction::sendSimpleResponse(true, 'value changed successfully');
    }

    function changePassword(Request $request)
    {
        $admin = Admin::where('user_type', 1)->first();
        if ($admin->user_password == $request->old_password) {
            $admin->user_password = $request->new_password;
            $admin->save();
            return response()->json(['status' => true, 'message' => 'Password changed successfully']);
        } else {
            return response()->json(['status' => false, 'message' => 'Incorrect Old password !']);
        }
    }
    function updateGlobalSettings(Request $request)
    {
        $settings = GlobalSettings::first();
        $settings->currency = $request->currency;
        $settings->comission = $request->comission;
        $settings->min_amount_payout_salon = $request->min_amount_payout_salon;
        $settings->max_minus_balance_for_postpay_option = $request->max_minus_balance_for_postpay_option;
        $settings->max_order_at_once = $request->max_order_at_once;
        $settings->support_email = $request->support_email;
        $settings->save();

        return GlobalFunction::sendSimpleResponse(true, 'value changed successfully');
    }
    function settings(Request $request)
    {
        $settings = GlobalSettings::first();
        return view('settings', [
            'data' => $settings
        ]);
    }
    function editSalonNotification(Request $request)
    {
        $item = SalonNotifications::find($request->id);
        $item->title = $request->title;
        $item->description = $request->description;
        $item->save();
        return GlobalFunction::sendSimpleResponse(true, 'Salon Notification edited successfully');
    }
    function editUserNotification(Request $request)
    {
        $item = UserNotification::find($request->id);
        $item->title = $request->title;
        $item->description = $request->description;
        $item->save();
        return GlobalFunction::sendSimpleResponse(true, 'User Notification edited successfully');
    }
    function addSalonNotification(Request $request)
    {
        $item = new SalonNotifications();
        $item->title = $request->title;
        $item->description = $request->description;
        $item->save();
        GlobalFunction::sendPushNotificationToSalons($item->title, $item->description);
        return GlobalFunction::sendSimpleResponse(true, 'Salon Notification added successfully');
    }
    function addUserNotification(Request $request)
    {
        $item = new UserNotification();
        $item->title = $request->title;
        $item->description = $request->description;
        $item->save();
        GlobalFunction::sendPushNotificationToUsers($item->title, $item->description);
        return GlobalFunction::sendSimpleResponse(true, 'User Notification added successfully');
    }
    function notifications()
    {
        return view('notifications');
    }
    function banners()
    {
        return view('banners');
    }
    function deleteSalonNotification($id)
    {
        $item = SalonNotifications::find($id);
        $item->delete();

        return GlobalFunction::sendSimpleResponse(true, 'Salon Notification deleted successfully');
    }
    function deleteUserNotification($id)
    {
        $item = UserNotification::find($id);
        $item->delete();

        return GlobalFunction::sendSimpleResponse(true, 'User Notification deleted successfully');
    }
    function deleteBanner($id)
    {
        $item = Banners::find($id);
        GlobalFunction::deleteFile($item->image);
        $item->delete();

        return GlobalFunction::sendSimpleResponse(true, 'Banner deleted successfully');
    }
    function deleteSalonCat($id)
    {
        $cat = SalonCategories::find($id);
        $cat->is_deleted = 1;
        $cat->save();

        return GlobalFunction::sendSimpleResponse(true, 'cat deleted successfully');
    }
    function addBanner(Request $request)
    {
        $item = new Banners();
        $item->image = GlobalFunction::saveFileAndGivePath($request->image);
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'Banner added successfully');
    }
    function addSalonCat(Request $request)
    {
        $cat = new SalonCategories();
        $cat->title = $request->title;
        $cat->icon = GlobalFunction::saveFileAndGivePath($request->icon);
        $cat->save();

        return GlobalFunction::sendSimpleResponse(true, 'cat added successfully');
    }

    function salonCategories()
    {
        return view('salonCategories');
    }

    function userWalletRecharge()
    {
        return view('userWalletRecharge');
    }
    function fetchSalonNotificationList(Request $request)
    {
        $totalData =  SalonNotifications::count();
        $rows = SalonNotifications::orderBy('id', 'DESC')->get();

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
            $result = SalonNotifications::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonNotifications::Where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonNotifications::Where('id', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $title = '<span class="text-dark font-weight-bold font-16">' . $item->title . '</span><br>';
            $desc = '<span>' . $item->description . '</span>';
            $notification = $title . $desc;

            $edit = '<a href="" data-description="' . $item->description . '" data-title="' . $item->title . '" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $edit . $delete;


            $data[] = array(
                $notification,
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
    function fetchUserNotificationList(Request $request)
    {
        $totalData =  UserNotification::count();
        $rows = UserNotification::orderBy('id', 'DESC')->get();

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
            $result = UserNotification::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserNotification::Where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserNotification::Where('id', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $title = '<span class="text-dark font-weight-bold font-16">' . $item->title . '</span><br>';
            $desc = '<span>' . $item->description . '</span>';
            $notification = $title . $desc;

            $edit = '<a href="" data-description="' . $item->description . '" data-title="' . $item->title . '" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $edit . $delete;


            $data[] = array(
                $notification,
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
    function fetchBannersList(Request $request)
    {
        $totalData =  Banners::count();
        $rows = Banners::orderBy('id', 'DESC')->get();

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
            $result = Banners::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Banners::Where('id', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Banners::Where('id', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {


            $imgUrl = "http://placehold.jp/150x150.png";

            $imgUrl = GlobalFunction::createMediaUrl($item->image);
            $img = '<img src="' . $imgUrl . '" width="300" height="120">';


            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $delete;


            $data[] = array(
                $img,
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
    function fetchSalonCategoriesList(Request $request)
    {
        $totalData =  SalonCategories::where('is_deleted', 0)->count();
        $rows = SalonCategories::where('is_deleted', 0)->orderBy('id', 'DESC')->get();
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
            $result = SalonCategories::where('is_deleted', 0)->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonCategories::where('is_deleted', 0)
                ->Where('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonCategories::where('is_deleted', 0)
                ->Where('title', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {


            $imgUrl = "http://placehold.jp/150x150.png";
            if ($item->icon == null) {
                $img = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($item->icon);
                $img = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $edit = '<a data-icon="' . $imgUrl . '" data-title="' . $item->title . '" href="" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $edit . $delete;


            $data[] = array(
                $img,
                $item->title,
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
    function fetchWalletRechargeList(Request $request)
    {
        $totalData =  UserWalletRechargeLogs::count();
        $rows = UserWalletRechargeLogs::orderBy('id', 'DESC')->get();
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
            $result = UserWalletRechargeLogs::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWalletRechargeLogs::Where('amount', 'LIKE', "%{$search}%")
                ->orWhere('transaction_summary', 'LIKE', "%{$search}%")
                ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWalletRechargeLogs::Where('amount', 'LIKE', "%{$search}%")
                ->orWhere('transaction_summary', 'LIKE', "%{$search}%")
                ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $gateway = GlobalFunction::detectPaymentGateway($item->gateway);

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $summary = '<span class="itemDescription">'. $item->transaction_summary .'</span>';

            $data[] = array(
                $user,
                $settings->currency . $item->amount,
                $gateway,
                $item->transaction_id,
                $summary,
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
    function fetchPlatformEarningsList(Request $request)
    {
        $totalData =  PlatformEarningHistory::count();
        $rows = PlatformEarningHistory::orderBy('id', 'DESC')->get();
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
            $result = PlatformEarningHistory::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  PlatformEarningHistory::Where('earning_number', 'LIKE', "%{$search}%")
                ->orWhere('amount', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = PlatformEarningHistory::Where('earning_number', 'LIKE', "%{$search}%")
                ->orWhere('amount', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $delete;

            $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">
                        ' . $item->salon->salon_name . '</span></a>';
            $data[] = array(
                $item->earning_number,
                $settings->currency . $item->amount,
                $item->booking != null ? $settings->currency . $item->booking->payable_amount : '',
                $item->commission_percentage . '%',
                $item->booking != null ? $item->booking->booking_id : '',
                $salon,
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

    function platformEarnings()
    {
        return view('platformEarnings');
    }
    function getFaqCats()
    {
        $cats = FaqCats::all();
        return GlobalFunction::sendDataResponse(true, 'cats fetched successfully!', $cats);
    }
    function editSalonCat(Request $request)
    {
        $item = SalonCategories::find($request->id);
        $item->title = $request->title;
        if ($request->has('icon')) {
            $item->icon = GlobalFunction::saveFileAndGivePath($request->icon);
        }

        $item->save();
        return GlobalFunction::sendSimpleResponse(true, 'Salon Cat edited successfully');
    }
    function editFaq(Request $request)
    {
        $faq = Faqs::find($request->id);
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->category_id = $request->category_id;
        $faq->save();
        return GlobalFunction::sendSimpleResponse(true, 'FAQ edited successfully');
    }

    function addFaq(Request $request)
    {
        $faq = new Faqs();
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->category_id = $request->category_id;
        $faq->save();
        return GlobalFunction::sendSimpleResponse(true, 'FAQ added successfully');
    }

    function deleteFaq($id)
    {
        $faqCat = Faqs::find($id);
        $faqCat->delete();
        return GlobalFunction::sendSimpleResponse(true, 'Faq deleted successfully');
    }
    function deletePlatformEarningItem($id)
    {
        $item = PlatformEarningHistory::find($id);
        $item->delete();
        return GlobalFunction::sendSimpleResponse(true, 'Earning history deleted successfully');
    }
    function deleteFaqCat($id)
    {
        $faqCat = FaqCats::find($id);
        $faqCat->delete();
        Faqs::where('category_id', $id)->delete();
        return GlobalFunction::sendSimpleResponse(true, 'Category deleted successfully');
    }
    function editFaqCategory(Request $request)
    {
        $faqCat = FaqCats::find($request->id);
        $faqCat->title = $request->title;
        $faqCat->save();
        return GlobalFunction::sendSimpleResponse(true, 'Category edited successfully');
    }
    function addFaqCategory(Request $request)
    {
        $faqCat = new FaqCats();
        $faqCat->title = $request->title;
        $faqCat->save();
        return GlobalFunction::sendSimpleResponse(true, 'Category added successfully');
    }
    function faqs()
    {
        $cats = FaqCats::all();
        return view('faqs', [
            'cats' => $cats
        ]);
    }
    function fetchFaqList(Request $request)
    {
        $totalData =  Faqs::count();
        $rows = Faqs::orderBy('id', 'DESC')->get();

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
            $result = Faqs::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Faqs::Where('question', 'LIKE', "%{$search}%")
                ->orWhere('answer', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Faqs::Where('question', 'LIKE', "%{$search}%")
                ->orWhere('answer', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $edit = '<a data-cat="' . $item->category_id . '" data-answer="' . $item->answer . '" data-question="' . $item->question . '" href="" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $edit . $delete;

            $category = '<span class="badge bg-primary text-white">' . $item->category->title . '</span>';

            $data[] = array(
                $item->question,
                $item->answer,
                $category,
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
    function fetchFaqCatsList(Request $request)
    {
        $totalData =  FaqCats::count();
        $rows = FaqCats::orderBy('id', 'DESC')->get();

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
            $result = FaqCats::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  FaqCats::Where('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = FaqCats::Where('title', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = array();
        foreach ($result as $item) {
            $edit = '<a data-title="' . $item->title . '" href="" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $edit . $delete;

            $data[] = array(
                $item->title,
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
    function fetchAllReviewsList(Request $request)
    {
        $totalData =  SalonReviews::with(['booking', 'salon'])->count();
        $rows = SalonReviews::with(['booking', 'salon'])->orderBy('id', 'DESC')->get();

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
            $result = SalonReviews::with(['booking', 'salon'])

                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  SalonReviews::with(['booking', 'salon'])
                ->whereHas('booking', function ($q) use ($search) {
                    $q->where('booking_id', 'LIKE', "%{$search}%");
                })
                ->orWhere('comment', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SalonReviews::with(['booking', 'salon'])
                ->whereHas('booking', function ($q) use ($search) {
                    $q->where('booking_id', 'LIKE', "%{$search}%");
                })
                ->orWhere('comment', 'LIKE', "%{$search}%")
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

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $action = $delete;
            $data[] = array(
                $ratingBar,
                $item->comment,
                $item->booking != null ? $item->booking->booking_id : "",
                $salon,
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
    function reviews()
    {
        return view('reviews');
    }
    function deleteCoupon($id)
    {
        $coupon = Coupons::find($id);
        $coupon->delete();
        return GlobalFunction::sendSimpleResponse(true, 'coupon deleted successfully!');
    }
    function editCouponItem(Request $request)
    {
        $coupon = Coupons::find($request->id);
        $coupon->coupon = $request->coupon;
        $coupon->percentage = $request->percentage;
        $coupon->max_discount_amount = $request->max_discount_amount;
        $coupon->min_order_amount = $request->min_order_amount;
        $coupon->heading = $request->heading;
        $coupon->description = $request->description;
        $coupon->save();

        return GlobalFunction::sendSimpleResponse(true, 'coupon edited successfully!');
    }
    function addCouponItem(Request $request)
    {
        $coupon = new Coupons();
        $coupon->coupon = $request->coupon;
        $coupon->percentage = $request->percentage;
        $coupon->max_discount_amount = $request->max_discount_amount;
        $coupon->min_order_amount = $request->min_order_amount;
        $coupon->heading = $request->heading;
        $coupon->description = $request->description;
        $coupon->save();

        return GlobalFunction::sendSimpleResponse(true, 'coupon added successfully!');
    }
    function fetchAllCouponsList(Request $request)
    {
        $totalData =  Coupons::count();
        $rows = Coupons::orderBy('id', 'DESC')->get();
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
            $result = Coupons::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Coupons::where(function ($query) use ($search) {
                $query->Where('coupon', 'LIKE', "%{$search}%")
                    ->orWhere('heading', 'LIKE', "%{$search}%")
                    ->orWhere('min_order_amount', 'LIKE', "%{$search}%")
                    ->orWhere('max_discount_amount', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Coupons::where(function ($query) use ($search) {
                $query->Where('coupon', 'LIKE', "%{$search}%")
                    ->orWhere('heading', 'LIKE', "%{$search}%")
                    ->orWhere('max_discount_amount', 'LIKE', "%{$search}%")
                    ->orWhere('min_order_amount', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })->count();
        }
        $data = array();
        foreach ($result as $item) {
            $edit = '<a data-description="' . $item->description . '" data-heading="' . $item->heading . '" data-minOrderAmount="' . $item->min_order_amount . '" data-maxDiscAmount="' . $item->max_discount_amount . '" data-coupon="' . $item->coupon . '" data-percentage="' . $item->percentage . '" href="" class="mr-2 btn btn-primary text-white edit" rel=' . $item->id . ' >' . __("Edit") . '</a>';

            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $edit  . $delete;

            $data[] = array(
                $item->coupon,
                $item->heading,
                $item->percentage . '%',
                $item->description,
                $settings->currency . $item->max_discount_amount,
                $settings->currency . $item->min_order_amount,
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

    function coupons()
    {
        $settings = GlobalSettings::first();
        return view('coupons', [
            'settings' => $settings
        ]);
    }

    function index()
    {
        $settings = GlobalSettings::first();

        $salonRequests = Salons::where('status', Constants::statusSalonPending)->count();
        $activeSalons = Salons::where('status', Constants::statusSalonActive)->count();
        $bannedSalons = Salons::where('status', Constants::statusSalonBanned)->count();
        $singUpOnlySalons = Salons::where('status', Constants::statusSalonSignUpOnly)->count();

        // Today Bookings
        $todayTotalBookings = Bookings::whereDate('created_at', Carbon::now())->count();
        $todayTotalPendingBookings = Bookings::whereDate('created_at', Carbon::today())->where('status', Constants::orderPlacedPending)->count();
        $todayTotalAcceptedBookings = Bookings::whereDate('created_at', Carbon::today())->where('status', Constants::orderAccepted)->count();
        $todayTotalCompletedBookings = Bookings::whereDate('created_at', Carbon::today())->where('status', Constants::orderCompleted)->count();
        $todayTotalCancelledBookings = Bookings::whereDate('created_at', Carbon::today())->where('status', Constants::orderCancelled)->count();
        $todayTotalDeclinedBookings = Bookings::whereDate('created_at', Carbon::today())->where('status', Constants::orderDeclined)->count();

        // Last 7 days
        $last7date = Carbon::now()->subDays(7);
        $last7daysTotalBookings = Bookings::where('created_at', '>=', $last7date)->count();
        $last7daysTotalPendingBookings = Bookings::where('created_at', '>=', $last7date)->where('status', Constants::orderPlacedPending)->count();
        $last7daysTotalAcceptedBookings = Bookings::where('created_at', '>=', $last7date)->where('status', Constants::orderAccepted)->count();
        $last7daysTotalCompletedBookings = Bookings::where('created_at', '>=', $last7date)->where('status', Constants::orderCompleted)->count();
        $last7daysTotalCancelledBookings = Bookings::where('created_at', '>=', $last7date)->where('status', Constants::orderCancelled)->count();
        $last7daysTotalDeclinedBookings = Bookings::where('created_at', '>=', $last7date)->where('status', Constants::orderDeclined)->count();

        // last 30 days
        $last30date = Carbon::now()->subDays(30);
        $last30daysTotalBookings = Bookings::where('created_at', '>=', $last30date)->count();
        $last30daysTotalPendingBookings = Bookings::where('created_at', '>=', $last30date)->where('status', Constants::orderPlacedPending)->count();
        $last30daysTotalAcceptedBookings = Bookings::where('created_at', '>=', $last30date)->where('status', Constants::orderAccepted)->count();
        $last30daysTotalCompletedBookings = Bookings::where('created_at', '>=', $last30date)->where('status', Constants::orderCompleted)->count();
        $last30daysTotalCancelledBookings = Bookings::where('created_at', '>=', $last30date)->where('status', Constants::orderCancelled)->count();
        $last30daysTotalDeclinedBookings = Bookings::where('created_at', '>=', $last30date)->where('status', Constants::orderDeclined)->count();

        // last 90 days
        $last90date = Carbon::now()->subDays(90);
        $last90daysTotalBookings = Bookings::where('created_at', '>=', $last90date)->count();
        $last90daysTotalPendingBookings = Bookings::where('created_at', '>=', $last90date)->where('status', Constants::orderPlacedPending)->count();
        $last90daysTotalAcceptedBookings = Bookings::where('created_at', '>=', $last90date)->where('status', Constants::orderAccepted)->count();
        $last90daysTotalCompletedBookings = Bookings::where('created_at', '>=', $last90date)->where('status', Constants::orderCompleted)->count();
        $last90daysTotalCancelledBookings = Bookings::where('created_at', '>=', $last90date)->where('status', Constants::orderCancelled)->count();
        $last90daysTotalDeclinedBookings = Bookings::where('created_at', '>=', $last90date)->where('status', Constants::orderDeclined)->count();

        // last 180 days
        $last180date = Carbon::now()->subDays(180);
        $last180daysTotalBookings = Bookings::where('created_at', '>=', $last180date)->count();
        $last180daysTotalPendingBookings = Bookings::where('created_at', '>=', $last180date)->where('status', Constants::orderPlacedPending)->count();
        $last180daysTotalAcceptedBookings = Bookings::where('created_at', '>=', $last180date)->where('status', Constants::orderAccepted)->count();
        $last180daysTotalCompletedBookings = Bookings::where('created_at', '>=', $last180date)->where('status', Constants::orderCompleted)->count();
        $last180daysTotalCancelledBookings = Bookings::where('created_at', '>=', $last180date)->where('status', Constants::orderCancelled)->count();
        $last180daysTotalDeclinedBookings = Bookings::where('created_at', '>=', $last180date)->where('status', Constants::orderDeclined)->count();

        // All time bookings
        $allTimeTotalBookings = Bookings::count();
        $allTimeTotalPendingBookings = Bookings::where('status', Constants::orderPlacedPending)->count();
        $allTimeTotalAcceptedBookings = Bookings::where('status', Constants::orderAccepted)->count();
        $allTimeTotalCompletedBookings = Bookings::where('status', Constants::orderCompleted)->count();
        $allTimeTotalDeclinedBookings = Bookings::where('status', Constants::orderDeclined)->count();
        $allTimeTotalCancelledBookings = Bookings::where('status', Constants::orderCancelled)->count();

        // Platform Earnings
        $todayEarnings = PlatformEarningHistory::whereDate('created_at', Carbon::now())->sum('amount');
        $last7DaysEarnings = PlatformEarningHistory::where('created_at', '>=', $last7date)->sum('amount');
        $last30DaysEarnings = PlatformEarningHistory::where('created_at', '>=', $last30date)->sum('amount');
        $last90DaysEarnings = PlatformEarningHistory::where('created_at', '>=', $last90date)->sum('amount');
        $last180DaysEarnings = PlatformEarningHistory::where('created_at', '>=', $last180date)->sum('amount');
        $allTimeDaysEarnings = PlatformEarningHistory::sum('amount');

        // Withdrawals
        $pendingSalonPayouts = SalonPayoutHistory::where('status', 0)->sum('amount');
        $completedSalonPayouts = SalonPayoutHistory::where('status', 1)->sum('amount');
        $pendingUserPayouts = UserWithdrawRequest::where('status', 0)->sum('amount');

        // Recharges
        $todayRecharges = UserWalletRechargeLogs::whereDate('created_at', Carbon::now())->sum('amount');
        $last7DaysRecharges = UserWalletRechargeLogs::where('created_at', '>=', $last7date)->sum('amount');
        $last30DaysRecharges = UserWalletRechargeLogs::where('created_at', '>=', $last30date)->sum('amount');
        $last90DaysRecharges = UserWalletRechargeLogs::where('created_at', '>=', $last90date)->sum('amount');
        $last180DaysRecharges = UserWalletRechargeLogs::where('created_at', '>=', $last180date)->sum('amount');
        $allTimeRecharges = UserWalletRechargeLogs::sum('amount');

        return view('index', [
            'settings' => $settings,

            // Wallet recharges User
            'todayRecharges' => GlobalFunction::roundNumber($todayRecharges),
            'last7DaysRecharges' => GlobalFunction::roundNumber($last7DaysRecharges),
            'last30DaysRecharges' => GlobalFunction::roundNumber($last30DaysRecharges),
            'last90DaysRecharges' => GlobalFunction::roundNumber($last90DaysRecharges),
            'last180DaysRecharges' => GlobalFunction::roundNumber($last180DaysRecharges),
            'allTimeRecharges' => GlobalFunction::roundNumber($allTimeRecharges),


            // Payouts
            'pendingSalonPayouts' => GlobalFunction::roundNumber($pendingSalonPayouts),
            'completedSalonPayouts' => GlobalFunction::roundNumber($completedSalonPayouts),
            'pendingUserPayouts' => GlobalFunction::roundNumber($pendingUserPayouts),


            // Platform Earnings
            'todayEarnings' => GlobalFunction::roundNumber($todayEarnings),
            'last7DaysEarnings' => GlobalFunction::roundNumber($last7DaysEarnings),
            'last30DaysEarnings' => GlobalFunction::roundNumber($last30DaysEarnings),
            'last90DaysEarnings' => GlobalFunction::roundNumber($last90DaysEarnings),
            'last180DaysEarnings' => GlobalFunction::roundNumber($last180DaysEarnings),
            'allTimeDaysEarnings' => GlobalFunction::roundNumber($allTimeDaysEarnings),

            'salonRequests' => $salonRequests,
            'activeSalons' => $activeSalons,
            'bannedSalons' => $bannedSalons,
            'singUpOnlySalons' => $singUpOnlySalons,
            // Today
            'todayTotalBookings' => $todayTotalBookings,
            'todayTotalPendingBookings' => $todayTotalPendingBookings,
            'todayTotalAcceptedBookings' => $todayTotalAcceptedBookings,
            'todayTotalCompletedBookings' => $todayTotalCompletedBookings,
            'todayTotalCancelledBookings' => $todayTotalCancelledBookings,
            'todayTotalDeclinedBookings' => $todayTotalDeclinedBookings,
            // Last 7 days
            'last7daysTotalBookings' => $last7daysTotalBookings,
            'last7daysTotalPendingBookings' => $last7daysTotalPendingBookings,
            'last7daysTotalAcceptedBookings' => $last7daysTotalAcceptedBookings,
            'last7daysTotalCompletedBookings' => $last7daysTotalCompletedBookings,
            'last7daysTotalCancelledBookings' => $last7daysTotalCancelledBookings,
            'last7daysTotalDeclinedBookings' => $last7daysTotalDeclinedBookings,
            // Last 30 days
            'last30daysTotalBookings' => $last30daysTotalBookings,
            'last30daysTotalPendingBookings' => $last30daysTotalPendingBookings,
            'last30daysTotalAcceptedBookings' => $last30daysTotalAcceptedBookings,
            'last30daysTotalCompletedBookings' => $last30daysTotalCompletedBookings,
            'last30daysTotalCancelledBookings' => $last30daysTotalCancelledBookings,
            'last30daysTotalDeclinedBookings' => $last30daysTotalDeclinedBookings,
            // Last 90 days
            'last90daysTotalBookings' => $last90daysTotalBookings,
            'last90daysTotalPendingBookings' => $last90daysTotalPendingBookings,
            'last90daysTotalAcceptedBookings' => $last90daysTotalAcceptedBookings,
            'last90daysTotalCompletedBookings' => $last90daysTotalCompletedBookings,
            'last90daysTotalCancelledBookings' => $last90daysTotalCancelledBookings,
            'last90daysTotalDeclinedBookings' => $last90daysTotalDeclinedBookings,
            // Last 180 days
            'last180daysTotalBookings' => $last180daysTotalBookings,
            'last180daysTotalPendingBookings' => $last180daysTotalPendingBookings,
            'last180daysTotalAcceptedBookings' => $last180daysTotalAcceptedBookings,
            'last180daysTotalCompletedBookings' => $last180daysTotalCompletedBookings,
            'last180daysTotalCancelledBookings' => $last180daysTotalCancelledBookings,
            'last180daysTotalDeclinedBookings' => $last180daysTotalDeclinedBookings,

            // All time
            'allTimeTotalBookings' => $allTimeTotalBookings,
            'allTimeTotalPendingBookings' => $allTimeTotalPendingBookings,
            'allTimeTotalAcceptedBookings' => $allTimeTotalAcceptedBookings,
            'allTimeTotalCompletedBookings' => $allTimeTotalCompletedBookings,
            'allTimeTotalCancelledBookings' => $allTimeTotalCancelledBookings,
            'allTimeTotalDeclinedBookings' => $allTimeTotalDeclinedBookings,

        ]);
    }

    function fetchFaqCats(Request $request)
    {
        $faqCats = FaqCats::with('faqs')->get();

        return GlobalFunction::sendDataResponse(true, 'Data fetch successfully', $faqCats);
    }
}
