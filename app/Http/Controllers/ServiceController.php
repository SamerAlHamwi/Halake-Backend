<?php

namespace App\Http\Controllers;

use App\Models\Constants;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use App\Models\SalonCategories;
use App\Models\SalonImages;
use App\Models\Salons;
use App\Models\ServiceImages;
use App\Models\Services;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    //
    function services()
    {
        return view('services');
    }
    function deleteServiceImage($id)
    {

        $serviceImg = ServiceImages::find($id);
        GlobalFunction::deleteFile($serviceImg->image);
        $serviceImg->delete();

        return Globalfunction::sendSimpleResponse(true, 'Image deleted successfully');
    }

    function updateService_Admin(Request $request)
    {
        $service = Services::find($request->id);
        $service->title = $request->title;
        $service->category_id = $request->category_id;
        $service->gender = $request->gender;
        $service->about = $request->about;
        $service->price = $request->price;
        $service->service_time = $request->service_time;
        $service->discount = $request->discount;
        $service->save();

        return GlobalFunction::sendSimpleResponse(true, 'Service Updated successfully!');
    }
    function viewService($id)
    {
        $service = Services::with(['images', 'salon'])->find($id);
        $categories = SalonCategories::where('is_deleted', 0)->get();
        $settings = GlobalSettings::first();
        return view('viewService', [
            'service' => $service,
            'settings' => $settings,
            'categories' => $categories
        ]);
    }
    function deleteService_Admin($id)
    {
        $service = Services::where('id', $id)->first();
        if ($service == null) {
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }
        $serviceImages = ServiceImages::where('service_id', $service->id)->get();
        foreach ($serviceImages as $image) {
            GlobalFunction::deleteFile($image->image);
            $image->delete();
        }
        $service->delete();
        return response()->json(['status' => true, 'message' => "Service has been deleted!"]);
    }
    function changeStaffStatus_Admin($id, $status)
    {
        $item = Staff::find($id);
        $item->status = $status;
        $item->save();

        return GlobalFunction::sendSimpleResponse(true, 'Status changed successfully');
    }
    function changeServiceStatus_Admin($id, $status)
    {
        $service = Services::find($id);
        $service->status = $status;
        $service->save();

        return GlobalFunction::sendSimpleResponse(true, 'Status changed successfully');
    }
    function fetchAllServicesList(Request $request)
    {
        $totalData =  Services::count();
        $rows = Services::with(['images'])->orderBy('id', 'DESC')->get();
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
            $result = Services::with(['images'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Services::with(['images'])
                ->where(function ($query) use ($search) {
                    $query->Where('service_number', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Services::with(['images'])
                ->where(function ($query) use ($search) {
                    $query->Where('service_number', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $firstImage = $item->images->get(0);

            if ($firstImage == null) {
                $serviceImg = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($firstImage->image);
                $serviceImg = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $view = '<a href="' . route('viewService', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';
            // $delete = '<a href="" class="mr-2 btn btn-danger text-white " rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $view  . $delete;

            $salon = "";
            if ($item->salon != null) {
                $salon = '<a href="' . route('viewSalonProfile', $item->salon->id) . '"><span class="badge bg-primary text-white">' . $item->salon->salon_name . '</span></a>';
            }

            $gender = "";
            if ($item->gender == Constants::salonGenderMale) {
                $gender = '<span  class="badge bg-info text-white ">' . __("Male") . '</span>';
            } else if ($item->gender == Constants::salonGenderFemale) {
                $gender = '<span  class="badge bg-danger text-white ">' . __("Female") . '</span>';
            } else if ($item->gender == Constants::salonGenderUnisex) {
                $gender = '<span  class="badge bg-primary text-white ">' . __("Unisex") . '</span>';
            }

            $onOff = "";
            if ($item->status == Constants::statusServiceOn) {
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

            $data[] = array(
                $item->service_number,
                $serviceImg,
                $item->title,
                $item->category->title,
                $item->service_time,
                $settings->currency . $item->price,
                $item->discount . "%",
                $gender,
                $salon,
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
    function fetchSalonServicesList(Request $request)
    {
        $totalData =  Services::where('salon_id', $request->salonId)->count();
        $rows = Services::with(['images'])->where('salon_id', $request->salonId)->orderBy('id', 'DESC')->get();
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
            $result = Services::with(['images'])
                ->where('salon_id', $request->salonId)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result =  Services::with(['images'])
                ->where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('service_number', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Services::with(['images'])
                ->where('salon_id', $request->salonId)
                ->where(function ($query) use ($search) {
                    $query->Where('service_number', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%");
                })
                ->count();
        }
        $data = array();
        foreach ($result as $item) {

            $firstImage = $item->images->get(0);

            if ($firstImage == null) {
                $serviceImg = '<img src="http://placehold.jp/150x150.png" width="50" height="50">';
            } else {
                $imgUrl = GlobalFunction::createMediaUrl($firstImage->image);
                $serviceImg = '<img src="' . $imgUrl . '" width="50" height="50">';
            }

            $view = '<a href="' . route('viewService', $item->id) . '" class="mr-2 btn btn-info text-white " rel=' . $item->id . ' >' . __("View") . '</a>';
            // $delete = '<a href="" class="mr-2 btn btn-danger text-white " rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $delete = '<a href="" class="mr-2 btn btn-danger text-white delete" rel=' . $item->id . ' >' . __("Delete") . '</a>';
            $action = $view  . $delete;

            $gender = "";
            if ($item->gender == Constants::salonGenderMale) {
                $gender = '<span  class="badge bg-info text-white ">' . __("Male") . '</span>';
            } else if ($item->gender == Constants::salonGenderFemale) {
                $gender = '<span  class="badge bg-danger text-white ">' . __("Female") . '</span>';
            } else if ($item->gender == Constants::salonGenderUnisex) {
                $gender = '<span  class="badge bg-primary text-white ">' . __("Unisex") . '</span>';
            }

            $onOff = "";
            if ($item->status == Constants::statusServiceOn) {
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

            $data[] = array(
                $item->service_number,
                $serviceImg,
                $item->title,
                $item->category->title,
                $item->service_time,
                $settings->currency . $item->price,
                $item->discount . "%",
                $gender,
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

    function fetchService(Request $request)
    {
        $rules = [
            'service_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $service = Services::where('id', $request->service_id)->with(['images', 'salon', 'salon.images','salon.slots'])->first();
        $categories = SalonCategories::whereIn('id', explode(',', $service->salon->salon_categories))->get();
        $service->salon->salonCats = $categories;
        if ($service == null) {
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }

        return GlobalFunction::sendDataResponse(true, 'data fetched successfully', $service);
    }

    function changeServiceStatus(Request $request)
    {
        $rules = [
            'service_id' => 'required',
            'salon_id' => 'required',
            'status' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $service = Services::where('id', $request->service_id)->first();
        if ($service == null) {
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }
        if ($service->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "This salon doesn't own this service!"]);
        }
        $service->status  = $request->status;
        $service->save();
        return GlobalFunction::sendSimpleResponse(true, 'Service status changed!');
    }
    function fetchServicesByCatOfSalon(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'category_id' => 'required',
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
        $services = Services::where('salon_id', $salon->id)
            ->where('category_id', $request->category_id)
            ->with('images')
            ->orderBy('id', 'DESC')
            ->get();
        return GlobalFunction::sendDataResponse(true, 'Services fetched successfully!', $services);
    }
    function fetchAllServicesOfSalon(Request $request)
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
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }

        $services = Services::where('salon_id', $salon->id)->with(['images', 'category'])->orderBy('id', 'DESC')->get();
        return GlobalFunction::sendDataResponse(true, 'Services fetched successfully!', $services);
    }
    function deleteService(Request $request)
    {
        $rules = [
            'service_id' => 'required',
            'salon_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $service = Services::where('id', $request->service_id)->first();
        if ($service == null) {
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }
        if ($service->salon_id != $request->salon_id) {
            return response()->json(['status' => false, 'message' => "This salon doesn't own this service!"]);
        }

        $serviceImages = ServiceImages::where('service_id', $service->id)->get();
        foreach ($serviceImages as $image) {
            GlobalFunction::deleteFile($image->image);
            $image->delete();
        }
        $service->delete();
        return response()->json(['status' => true, 'message' => "Service has been deleted!"]);
    }
    function editService(Request $request)
    {
        $rules = [
            'service_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $service = Services::where('id', $request->service_id)->first();
        if ($service == null) {
            return response()->json(['status' => false, 'message' => "Service doesn't exists!"]);
        }
        if ($request->has('category_id')) {
            $service->category_id = $request->category_id;
        }
        if ($request->has('title')) {
            $service->title = GlobalFunction::cleanString($request->title);
        }
        if ($request->has('price')) {
            $service->price = $request->price;
        }
        if ($request->has('discount')) {
            $service->discount = $request->discount;
        }
        if ($request->has('gender')) {
            $service->gender = $request->gender;
        }
        if ($request->has('service_time')) {
            $service->service_time = $request->service_time;
        }
        if ($request->has('about')) {
            $service->about = GlobalFunction::cleanString($request->about);
        }

        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $img = new ServiceImages();
                $img->service_id = $service->id;
                $img->image = GlobalFunction::saveFileAndGivePath($image);
                $img->save();
            }
        }

        // Deleting images if Ids Sent
        if ($request->has("deleteImageIds")) {
            $images = ServiceImages::whereIn('id', $request->deleteImageIds)->get();
            foreach ($images as $image) {
                if ($image->service_id != $service->id) {
                    return response()->json(['status' => false, 'message' => 'This image is not attached with this service!']);
                }
                GlobalFunction::deleteFile($image->image);
                $image->delete();
            }
        }

        $service->save();
        return response()->json(['status' => true, 'message' => 'Service updated successfully!']);
    }

    function addServiceToSalon(Request $request)
    {
        $rules = [
            'salon_id' => 'required',
            'category_id' => 'required',
            'title' => 'required',
            'price' => 'required',
            'service_time' => 'required',
            'gender' => 'required',
            'images' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $salon = Salons::with(['images', 'slots'])->where('id', $request->salon_id)->first();
        $category = SalonCategories::where('id', $request->category_id)->first();
        if ($salon == null) {
            return response()->json(['status' => false, 'message' => "Salon doesn't exists!"]);
        }
        if ($salon->status != Constants::statusSalonActive) {
            return response()->json(['status' => false, 'message' => "Salon is not active!"]);
        }
        if ($category == null) {
            return response()->json(['status' => false, 'message' => "Category doesn't exists!"]);
        }

        $service = new Services();
        $service->service_number = GlobalFunction::generateServiceNumber();
        $service->salon_id = $request->salon_id;
        $service->category_id = $request->category_id;
        $service->title = GlobalFunction::cleanString($request->title);
        $service->price = GlobalFunction::cleanString($request->price);
        $service->service_time = GlobalFunction::cleanString($request->service_time);
        if ($request->has('discount')) {
            $service->discount = $request->discount;
        }
        $service->discount = $request->discount;
        $service->gender = $request->gender;
        $service->about = GlobalFunction::cleanString($request->about);
        $service->save();

        foreach ($request->images as $image) {
            $img = new ServiceImages();
            $img->service_id = $service->id;
            $img->image = GlobalFunction::saveFileAndGivePath($image);
            $img->save();
        }

        return response()->json(['status' => true, 'message' => 'Service added successfully!']);
    }
}
