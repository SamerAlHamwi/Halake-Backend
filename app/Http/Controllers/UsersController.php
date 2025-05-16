<?php

namespace App\Http\Controllers;

use App\Models\Banners;
use App\Models\Bookings;
use App\Models\Constants;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use App\Models\SalonCategories;
use App\Models\Salons;
use App\Models\Services;
use App\Models\UserNotification;
use App\Models\Users;
use App\Models\UserWalletRechargeLogs;
use App\Models\UserWalletStatements;
use App\Models\UserWithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    //
    function users()
    {
        return view('users');
    }

    function viewUserProfile($id)
    {
        $user = Users::find($id);
        $settings = GlobalSettings::first();
        $totalBookings = Bookings::where('user_id', $id)->count();
        return view('viewUserProfile', [
            'user' => $user,
            'settings' => $settings,
            'totalBookings' => $totalBookings,
        ]);
    }

    function blockUserFromAdmin($id)
    {
        $user = Users::find($id);
        $user->is_block = 1;
        $user->save();

        return GlobalFunction::sendSimpleResponse(true, 'User blocked successfully!');
    }
    function unblockUserFromAdmin($id)
    {
        $user = Users::find($id);
        $user->is_block = 0;
        $user->save();

        return GlobalFunction::sendSimpleResponse(true, 'User unblocked successfully!');
    }

    function fetchUserBookingsList(Request $request)
    {
        $totalData =  Bookings::where('user_id', $request->userId)->count();
        $rows = Bookings::where('user_id', $request->userId)
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
            $result = Bookings::where('user_id', $request->userId)
                ->with(['user', 'salon'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Bookings::where('user_id', $request->userId)
                ->with(['user', 'salon'])
                ->where(function ($query) use ($search) {
                    $query->Where('booking_id', 'LIKE', "%{$search}%")
                        ->orWhere('payable_amount', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Bookings::where('user_id', $request->userId)
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

            $salon = '';
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
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
                $salon,
                $status,
                $dateTime,
                $settings->currency . $item->service_amount,
                $settings->currency . $item->discount_amount,
                $settings->currency . $item->subtotal,
                $settings->currency . $item->total_tax_amount,
                $payableAmount,
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

    function fetchUserWalletStatementList(Request $request)
    {
        $totalData =  UserWalletStatements::where('user_id', $request->userId)->count();
        $rows = UserWalletStatements::where('user_id', $request->userId)->orderBy('id', 'DESC')->get();
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
            $result = UserWalletStatements::where('user_id', $request->userId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWalletStatements::where('user_id', $request->userId)
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
            $totalFiltered = UserWalletStatements::where('user_id', $request->userId)
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

    function fetchUserWalletRechargeLogsList(Request $request)
    {
        $userId = $request->userId;
        $totalData =  UserWalletRechargeLogs::where('user_id', $userId)->count();
        $rows = UserWalletRechargeLogs::where('user_id', $userId)->orderBy('id', 'DESC')->get();
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
            $result = UserWalletRechargeLogs::where('user_id', $userId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWalletRechargeLogs::where('user_id', $userId)
                ->where(function ($query) use ($search) {
                    $query->Where('amount', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_summary', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_id', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWalletRechargeLogs::where('user_id', $userId)
                ->where(function ($query) use ($search) {
                    $query->Where('amount', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_summary', 'LIKE', "%{$search}%")
                        ->orWhere('transaction_id', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $gateway = GlobalFunction::detectPaymentGateway($item->gateway);

            $data[] = array(
                $settings->currency . $item->amount,
                $gateway,
                $item->transaction_id,
                $item->transaction_summary,
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

    function fetchUserWithdrawRequestsList(Request $request)
    {
        $userId = $request->userId;
        $totalData =  UserWithdrawRequest::where('user_id', $userId)->with(['user'])->count();
        $rows = UserWithdrawRequest::where('user_id', $userId)->with(['user'])->orderBy('id', 'DESC')->get();
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
            $result = UserWithdrawRequest::where('user_id', $userId)->with(['user'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result = UserWithdrawRequest::where('user_id', $userId)->with(['user'])
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWithdrawRequest::where('user_id', $userId)->with(['user'])
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $holder = '<span class="text-dark font-weight-bold font-14">' . $item->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $item->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $item->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $item->swift_code . '</span></div>';
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
                GlobalFunction::formateDatabaseTime($item->created_at),
                $item->summary,
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

    function fetchUsersList(Request $request)
    {
        $totalData =  Users::count();
        $rows = Users::orderBy('id', 'DESC')->get();

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
            $result = Users::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Users::where(function ($query) use ($search) {
                $query->Where('identity', 'LIKE', "%{$search}%")
                    ->orWhere('fullname', 'LIKE', "%{$search}%");
            })->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Users::where(function ($query) use ($search) {
                $query->Where('identity', 'LIKE', "%{$search}%")
                    ->orWhere('fullname', 'LIKE', "%{$search}%");
            })->count();
        }
        $data = array();
        foreach ($result as $item) {

            if ($item->profile_image == null) {
                $image = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($item->profile_image);
                $image = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $bookingCount = Bookings::where('user_id', $item->id)->count();

            $view = '<a href="' . route('viewUserProfile', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';

            $block = "";
            if ($item->is_block == 0) {
                $block = '<a href="" class="mr-2 btn btn-danger text-white block" rel=' . $item->id . ' >' . __("Block") . '</a>';
            } else {
                $block = '<a href="" class="mr-2 btn btn-success text-white unblock" rel=' . $item->id . ' >' . __("Unblock") . '</a>';
            }

            $action = $view  . $block;

            $data[] = array(
                $image,
                $item->identity,
                $item->fullname,
                $bookingCount,
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

    function rejectUserWithdrawal(Request $request)
    {
        $item = UserWithdrawRequest::find($request->id);
        if ($request->has('summary')) {
            $item->summary = $request->summary;
        }
        $item->status = Constants::statusWithdrawalRejected;
        $item->save();

        $summary = '(Rejected) Withdraw request :' . $item->request_number;
        // Adding wallet statement
        GlobalFunction::addUserStatementEntry(
            $item->user->id,
            null,
            $item->amount,
            Constants::credit,
            Constants::deposit,
            $summary
        );

        //adding money to user wallet
        $item->user->wallet = $item->user->wallet + $item->amount;
        $item->user->save();

        return GlobalFunction::sendSimpleResponse(true, 'request rejected successfully');
    }
    function completeUserWithdrawal(Request $request)
    {
        $item = UserWithdrawRequest::find($request->id);
        if ($request->has('summary')) {
            $item->summary = $request->summary;
        }
        $item->status = Constants::statusWithdrawalCompleted;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'request completed successfully');
    }

    function fetchUserCompletedWithdrawalsList(Request $request)
    {
        $totalData =  UserWithdrawRequest::where('status', Constants::statusWithdrawalCompleted)->with('user')->count();
        $rows = UserWithdrawRequest::where('status', Constants::statusWithdrawalCompleted)->with('user')->orderBy('id', 'DESC')->get();
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
            $result = UserWithdrawRequest::where('status', Constants::statusWithdrawalCompleted)
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWithdrawRequest::where('status', Constants::statusWithdrawalCompleted)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWithdrawRequest::where('status', Constants::statusWithdrawalCompleted)
                ->with('user')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $holder = '<span class="text-dark font-weight-bold font-14">' . $item->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $item->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $item->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $item->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-success text-white"rel="' . $item->id . '">' . __('Completed') . '</span>';
            $amountData = $amount . $status;

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $user,
                $item->summary,
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
    function fetchUserRejectedWithdrawalsList(Request $request)
    {
        $totalData =  UserWithdrawRequest::where('status', Constants::statusWithdrawalRejected)->with('user')->count();
        $rows = UserWithdrawRequest::where('status', Constants::statusWithdrawalRejected)->with('user')->orderBy('id', 'DESC')->get();
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
            $result = UserWithdrawRequest::where('status', Constants::statusWithdrawalRejected)
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWithdrawRequest::where('status', Constants::statusWithdrawalRejected)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWithdrawRequest::where('status', Constants::statusWithdrawalRejected)
                ->with('user')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhere('summary', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $holder = '<span class="text-dark font-weight-bold font-14">' . $item->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $item->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $item->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $item->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-danger text-white"rel="' . $item->id . '">' . __('Rejected') . '</span>';
            $amountData = $amount . $status;

            $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">
                        ' . $item->user->fullname . '</span></a>';


            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $user,
                $item->summary,
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
    function fetchUserPendingWithdrawalsList(Request $request)
    {
        $totalData =  UserWithdrawRequest::where('status', Constants::statusWithdrawalPending)->with('user')->count();
        $rows = UserWithdrawRequest::where('status', Constants::statusWithdrawalPending)->with('user')->orderBy('id', 'DESC')->get();
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
            $result = UserWithdrawRequest::where('status', Constants::statusWithdrawalPending)
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  UserWithdrawRequest::where('status', Constants::statusWithdrawalPending)
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->with('user')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = UserWithdrawRequest::where('status', Constants::statusWithdrawalPending)
                ->with('user')
                ->where(function ($query) use ($search) {
                    $query->where('request_number', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('holder', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->Where('fullname', 'LIKE', "%{$search}%");
                        });
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $holder = '<span class="text-dark font-weight-bold font-14">' . $item->holder . '</span>';
            $bank_title = '<div class="bank-details"><span>' . $item->bank_title . '</span>';
            $account_number = '<span>' . __('Account : ') .  $item->account_number . '</span>';
            $swift_code = '<span>' . __('Swift Code : ') . $item->swift_code . '</span></div>';
            $bankDetails = $holder . $bank_title . $account_number . $swift_code;

            $user = "";
            if ($item->user != null) {
                $user = '<a href="' . route('viewUserProfile', $item->user->id) . '"><span class="badge bg-primary text-white">' . $item->user->fullname . '</span></a>';
            }

            // Amount & Status
            $amount = '<span class="text-dark font-weight-bold font-16">' . $settings->currency . $item->amount . '</span><br>';
            $status = '<span class="badge bg-warning text-white"rel="' . $item->id . '">' . __('Pending') . '</span>';
            $amountData = $amount . $status;

            $complete = '<a href="" class="mr-2 btn btn-success text-white complete" rel=' . $item->id . ' >' . __("Complete") . '</a>';
            $reject = '<a href="" class="mr-2 btn btn-danger text-white reject" rel=' . $item->id . ' >' . __("Reject") . '</a>';
            // $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action =  $complete . $reject;


            $data[] = array(
                $item->request_number,
                $bankDetails,
                $amountData,
                $user,
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

    function userWithdraws()
    {
        return view('userWithdraws');
    }
    function deleteMyUserAccount(Request $request)
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

        UserWalletStatements::where('user_id', $user->id)->delete();
        $user->delete();

        return GlobalFunction::sendSimpleResponse(true, "User account deleted successfully");
    }

    function fetchUserWithdrawRequests(Request $request)
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

        $withdraws = UserWithdrawRequest::where('user_id', $user->id)
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'withdraw requests fetched successfully!', $withdraws);
    }
    function submitUserWithdrawRequest(Request $request)
    {
        $rules = [
            'user_id' => 'required',
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

        $user = Users::find($request->user_id);
        if ($user == null) {
            return response()->json(['status' => false, 'message' => "User doesn't exists!"]);
        }
        if ($user->wallet < 1) {
            return response()->json(['status' => false, 'message' => "Not enough balance to withdraw!"]);
        }

        $withdraw = new UserWithdrawRequest();
        $withdraw->user_id = $user->id;
        $withdraw->request_number = GlobalFunction::generateUserWithdrawRequestNumber();
        $withdraw->bank_title = GlobalFunction::cleanString($request->bank_title);
        $withdraw->amount = $user->wallet;
        $withdraw->account_number = GlobalFunction::cleanString($request->account_number);
        $withdraw->holder = GlobalFunction::cleanString($request->holder);
        $withdraw->swift_code = GlobalFunction::cleanString($request->swift_code);
        $withdraw->save();

        $summary = 'Withdraw request :' . $withdraw->request_number;
        // Adding wallet statement
        GlobalFunction::addUserStatementEntry(
            $user->id,
            null,
            $user->wallet,
            Constants::debit,
            Constants::withdraw,
            $summary
        );

        //resetting users wallet
        $user->wallet = 0;
        $user->save();

        return GlobalFunction::sendSimpleResponse(true, 'withdraw request submitted successfully!');
    }
    function fetchWalletStatement(Request $request)
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
        $statement = UserWalletStatements::where('user_id', $user->id)
            ->offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Statement Data fetched successfully!', $statement);
    }
    function addMoneyToUserWallet(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'amount' => 'required',
            'transaction_id' => 'required',
            'transaction_summary' => 'required',
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
        $user->wallet = $user->wallet + $request->amount;
        $user->save();
        // Adding Statement entry
        GlobalFunction::addUserStatementEntry(
            $user->id,
            null,
            $request->amount,
            Constants::credit,
            Constants::deposit,
            $request->transaction_summary
        );
        // Recharge Wallet History
        $rechargeLog = new UserWalletRechargeLogs();
        $rechargeLog->user_id = $user->id;
        $rechargeLog->amount = $request->amount;
        $rechargeLog->gateway = $request->gateway;
        $rechargeLog->transaction_id = $request->transaction_id;
        $rechargeLog->transaction_summary = $request->transaction_summary;
        $rechargeLog->save();

        return GlobalFunction::sendSimpleResponse(true, 'Money added to wallet successfully!');
    }

    function fetchFavoriteData(Request $request)
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
        $services = Services::whereIn('id', explode(',', $user->favourite_services))->with(['images', 'salon'])->get();
        $salons = Salons::whereIn('id', explode(',', $user->favourite_salons))->with(['images', 'slots'])->get();
        foreach ($salons as $salon) {
            $salonCats = SalonCategories::whereIn('id', explode(',', $salon->salon_categories))->get();
            $salon->salonCats = $salonCats;
        }
        $data = array(
            'services' => $services,
            'salons' => $salons,
        );
        return GlobalFunction::sendDataResponse(true, 'Favorite data fetched successfully!', $data);
    }

    function editUserDetails(Request $request)
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
        if ($request->has('phone_number')) {
            $user->phone_number = GlobalFunction::cleanString($request->phone_number);
        }
        if ($request->has('fullname')) {
            $user->fullname = GlobalFunction::cleanString($request->fullname);
        }
        if ($request->has('favourite_salons')) {
            $user->favourite_salons = GlobalFunction::cleanString($request->favourite_salons);
        }
        if ($request->has('favourite_services')) {
            $user->favourite_services = GlobalFunction::cleanString($request->favourite_services);
        }
        if ($request->has('is_notification')) {
            $user->is_notification = $request->is_notification;
        }
        if ($request->has('profile_image')) {
            $user->profile_image = GlobalFunction::saveFileAndGivePath($request->profile_image);
        }
        $user->save();

        $user = Users::where('id', $user->id)->withCount('bookings')->first();
        return GlobalFunction::sendDataResponse(true, 'user updated successfully', $user);
    }

    function fetchNotification(Request $request)
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

        $notifications = UserNotification::offset($request->start)
            ->limit($request->count)
            ->orderBy('id', 'DESC')
            ->get();

        return GlobalFunction::sendDataResponse(true, 'Data fetched successfully', $notifications);
    }

    function registerUser(Request $request)
    {
        $rules = [
            'identity' => 'required',
            'device_type' => [Rule::in(1, 2)],
            'device_token' => 'required',
            'login_type' => [Rule::in(1, 2, 3)],
            'is_login' => [Rule::in(0, 1)], //0=register 1=login
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $user = Users::where('identity', $request->identity)->first();
        if($request->is_login == 1 && $user == null){
               return GlobalFunction::sendSimpleResponse(false, 'user not found');
        }

        if ($user != null) {
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            $user->login_type = $request->login_type;
            $user->save();
        } else {
            $user = new Users();
            $user->identity = $request->identity;
            $user->fullname = GlobalFunction::cleanString($request->fullname);
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            $user->login_type = $request->login_type;
            $user->email = $request->identity;
            $user->save();
        }
        $user = Users::where('id', $user->id)->withCount('bookings')->first();
        return GlobalFunction::sendDataResponse(true, 'User registration successful', $user);
    }
    function fetchUserDetails(Request $request)
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

        $user = Users::where('id', $request->user_id)->withCount('bookings')->first();
        return GlobalFunction::sendDataResponse(true, 'User details fetched successful', $user);
    }
}
