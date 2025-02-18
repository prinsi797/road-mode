<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ServiceRequests\UpdateService as UpdateRequest;
use App\Http\Requests\ServiceRequests\AddService as AddRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

class ServiceCategoryController extends Controller {
    protected $handle_name = 'Service Categories';
    protected $handle_name_plural = 'service_categories';

    public function index() {
        $all_count = ServiceCategory::count();
        $trashed_count = ServiceCategory::onlyTrashed()->count();

        return kview($this->handle_name_plural . '.index', [
            'ajax_route' => route('admin.' . $this->handle_name_plural . '.ajax'),
            'delete_route' => route('admin.' . $this->handle_name_plural . '.delete'),
            'create_route' => route('admin.' . $this->handle_name_plural . '.create'),
            'table_status' => 'all', // all, trashed
            'all_count' => $all_count,
            'trashed_count' => $trashed_count,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function create() {
        $vehicals = DB::table('vehicles')->get();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'vehicles' => $vehicals,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function edit(Request $request) {
        $ecrypted_id = $request->encrypted_id;
        $id = Crypt::decryptString($ecrypted_id);
        $vehicles = DB::table('vehicles')->get();
        $data = ServiceCategory::where('id', '=', $id)->first();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'data' => $data,
            'vehicles' => $vehicles,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function show(Request $request) {
        $id = Crypt::decryptString($request->encrypted_id);
        $data = ServiceCategory::findOrFail($id);

        return kview($this->handle_name_plural . '.show', [
            'data' => $data,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'sc_name' => 'required',
                // 'sc_bike_car' => 'required|in:bike,car',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'sc_description' => 'nullable',
                'vehical_id' => 'required|exists:vehicles,id',
            ]);

            $photoPath = null;
            if ($request->hasFile('sc_photo')) {
                $targetDirectory = public_path('services');
                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory, 0777, true);
                }
                $file = $request->file('sc_photo');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($targetDirectory, $fileName);
                $photoPath = 'services/' . $fileName;
            }
            $categoryProduct = ServiceCategory::create([
                'sc_name' => $request->sc_name,
                // 'sc_bike_car' => $request->sc_bike_car,
                'sc_photo' => $photoPath,
                'vehical_id' => $request->vehical_id,
                'sc_description' => $request->sc_description,
                'is_status' =>  1,
                'created_by' => auth()->user()->name,
            ]);

            return redirect()
                ->route('admin.' . $this->handle_name_plural . '.index')
                ->with('success', 'New ' . ucfirst($this->handle_name) . ' has been added.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function update(Request $request) {
        try {
            $request->validate([
                'sc_name' => 'required',
                // 'sc_bike_car' => 'required|in:bike,car',
                'vehical_id' => 'required|exists:vehicles,id',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'sc_description' => 'required',
            ]);

            $id = $request->id;
            $categoryProduct = ServiceCategory::findOrFail($id);

            $photoPath = $categoryProduct->sc_photo;

            if ($request->hasFile('sc_photo')) {
                if ($photoPath && file_exists(public_path($photoPath))) {
                    unlink(public_path($photoPath));
                }
                $targetDirectory = public_path('services');
                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory, 0777, true);
                }
                $file = $request->file('sc_photo');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($targetDirectory, $fileName);
                $photoPath = 'services/' . $fileName;
            }

            $categoryProduct->update([
                'sc_name' => $request->sc_name,
                // 'sc_bike_car' => $request->sc_bike_car,
                'sc_photo' => $photoPath,
                'vehical_id' => $request->vehical_id,
                'sc_description' => $request->sc_description,
                'is_status' =>  1,
                'modified_by' => auth()->user()->name,
            ]);

            return redirect()
                ->route('admin.' . $this->handle_name_plural . '.index')
                ->with('success', ucfirst($this->handle_name) . ' has been updated.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function delete(Request $request) {
        $action = $request->action;
        $is_bulk = $request->is_bulk;
        $data_id = $request->data_id;

        try {
            switch ($action) {
                case 'restore':
                    if ($is_bulk) {
                        ServiceCategory::onlyTrashed()->whereIn('id', explode(",", $data_id))->restore();
                    } else {
                        ServiceCategory::onlyTrashed()->findOrFail($data_id)->restore();
                    }
                    break;

                case 'trash':
                    if ($is_bulk) {
                        ServiceCategory::whereIn('id', explode(",", $data_id))->delete();
                    } else {
                        ServiceCategory::findOrFail($data_id)->delete();
                    }
                    break;

                case 'delete':
                    if ($is_bulk) {
                        ServiceCategory::withTrashed()->whereIn('id', explode(",", $data_id))->forceDelete();
                    } else {
                        ServiceCategory::withTrashed()->findOrFail($data_id)->forceDelete();
                    }
                    break;
            }

            return 1;
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function ajax(Request $request) {
        $current_page = $request->page_number;
        if (isset($request->limit)) {
            $limit = $request->limit;
        } else {
            $limit = 10;
        }
        $offset = (($current_page - 1) * $limit);
        $modalObject = new ServiceCategory();
        if (isset($request->string)) {
            $string = $request->string;
            $modalObject = $modalObject->where('name', 'like', "%" . $request->string . "%");
            // $modalObject = $modalObject->orWhere('name','like',"%".$request->string."%");
        }

        $all_trashed = $request->all_trashed;
        if ($all_trashed == "trashed") {
            $modalObject = $modalObject->onlyTrashed();
        }

        $total_records = $modalObject->count();
        $modalObject = $modalObject->offset($offset);
        $modalObject = $modalObject->take($limit);
        $data = $modalObject->get();

        if (isset($request->page_number) && $request->page_number != 1) {
            $page_number = $request->page_number + $limit - 1;
        } else {
            $page_number = 1;
        }
        $pagination = array(
            "offset" => $offset,
            "total_records" => $total_records,
            "item_per_page" => $limit,
            "total_pages" => ceil($total_records / $limit),
            "current_page" => $current_page,
        );

        return kview($this->handle_name_plural . '.ajax', compact('data', 'page_number', 'limit', 'offset', 'pagination'));
    }

    // public function toggleStatus(Request $request, $id) {
    //     try {
    //         $category = ServiceCategory::findOrFail($id);
    //         $category->is_status = $request->is_status;
    //         $category->save();

    //         return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //     }
    // }
    // public function toggleStatus(Request $request, $id) {
    //     try {
    //         Log::info('Request Data: ', $request->all()); // Logs the incoming data

    //         $category = ServiceCategory::findOrFail($id);
    //         $category->is_status = $request->is_status; // Update the status
    //         $category->save(); // Save to the database

    //         return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //     }
    // }
    // public function toggleStatus(Request $request) {
    //     $id = $request->input('id');
    //     $currentStatus = $request->input('status');

    //     $category = ServiceCategory::findOrFail($id);
    //     $newStatus = !$currentStatus;
    //     $category->is_status = $newStatus;
    //     $category->save();

    //     return response()->json([
    //         'success' => true,
    //         'new_status' => $newStatus,
    //     ]);
    // }
    public function toggleStatus(Request $request) {
        try {
            $category = ServiceCategory::findOrFail($request->id);
            $category->is_status = !$category->is_status;
            $category->save();

            return response()->json([
                'success' => true,
                'new_status' => $category->is_status,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }
    // public function toggleStatus(Request $request) {
    //     $id = $request->input('id');
    //     $currentStatus = $request->input('status');

    //     $category = ServiceCategory::findOrFail($id);
    //     $newStatus = !$currentStatus;
    //     $category->is_status = $newStatus;
    //     $category->save();

    //     return response()->json([
    //         'success' => true,
    //         'new_status' => $newStatus,
    //     ]);
    // }
}