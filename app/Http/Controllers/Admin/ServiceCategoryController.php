<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ServiceRequests\UpdateService as UpdateRequest;
use App\Http\Requests\ServiceRequests\AddService as AddRequest;
use Illuminate\Http\Request;

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
        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function edit(Request $request) {
        $ecrypted_id = $request->encrypted_id;
        $id = Crypt::decryptString($ecrypted_id);
        $data = ServiceCategory::where('id', '=', $id)->first();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'data' => $data,

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
                'sc_bike_car' => 'required|string|max:255',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'sc_description' => 'nullable',
                'is_status' => 'nullable',
                'created_by' => 'nullable',
                'modified_by' => 'nullable',
            ]);

            // $photoPath = null;
            // if ($request->hasFile('sc_photo')) {
            //     $photoPath = $request->file('sc_photo')->store('services', 'public'); // Store in 'products' folder in 'storage/app/public'
            // }
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
                'sc_bike_car' => $request->sc_bike_car,
                'sc_photo' => $photoPath,
                'sc_description' => $request->sc_description,
                'is_status' => $request->is_status,
                'created_by' => $request->created_by,
                'modified_by' => $request->modified_by,
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
                'sc_bike_car' => 'required|string|max:255',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'sc_description' => 'required',
                'is_status' => 'nullable',
                'created_by' => 'nullable',
                'modified_by' => 'nullable',
            ]);

            $id = $request->id;
            $categoryProduct = ServiceCategory::findOrFail($id);

            // $photoPath = $categoryProduct->sc_photo;
            // if ($request->hasFile('sc_photo')) {
            //     if ($photoPath) {
            //         \Storage::disk('public')->delete($photoPath);
            //     }

            //     $photoPath = $request->file('sc_photo')->store('services', 'public');
            // }
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
                'sc_bike_car' => $request->sc_bike_car,
                'sc_photo' => $photoPath,
                'sc_description' => $request->sc_description,
                'is_status' => $request->is_status,
                'created_by' => $request->created_by,
                'modified_by' => $request->modified_by,
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
}