<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyMaster;
use App\Models\ModelMaster as Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Exception;

class ModelMasterController extends Controller {
    protected $handle_name = 'model_master';
    protected $handle_name_plural = 'model_master';

    public function index() {
        $all_count = Table::count();
        $trashed_count = Table::onlyTrashed()->count();

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
        $company = CompanyMaster::orderBy('com_name')->get(['id', 'com_name']);

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'company' => $company,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }

    public function edit(Request $request) {
        $ecrypted_id = $request->encrypted_id;
        $id = Crypt::decryptString($ecrypted_id);
        $data = Table::where('id', '=', $id)->first();
        $company = CompanyMaster::all(); // Fetch all cities

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'company' => $company,
            'data' => $data,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }

    public function show(Request $request) {
        $id = Crypt::decryptString($request->encrypted_id);
        $data = Table::findOrFail($id);

        return kview($this->handle_name_plural . '.show', [
            'data' => $data,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'model_name' => 'required|string|max:255',
            'model_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'com_id' => 'required|exists:company_master,id',
            'model_description' => 'required',
            'is_status' => 'required|boolean',
            'created_by' => 'required',
            'modified_by' => 'nullable',
        ]);

        $model_code = $this->generateModelCode();

        $photoPath = null;
        if ($request->hasFile('model_photo')) {
            $photoFile = $request->file('model_photo');
            $photoDirectory = public_path('models');

            if (!file_exists($photoDirectory)) {
                mkdir($photoDirectory, 0777, true);
            }

            $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($photoDirectory, $photoName);
            $photoPath = 'models/' . $photoName;
        }

        $branch = Table::create([
            'model_code' => $model_code,
            'model_name' => $request->model_name,
            'model_photo' => $photoPath,
            'com_id' => $request->com_id,
            'model_description' => $request->model_description,
            'is_status' => $request->is_status,
            'created_by' => $request->created_by,
            'modified_by' => $request->modified_by,
        ]);

        return redirect()
            ->route('admin.' . $this->handle_name_plural . '.index')
            ->with('success', 'New ' . ucfirst($this->handle_name) . ' has been added.');
    }

    private function generateModelCode() {
        $lastBranch = Table::latest('id')->first();
        return 'MODEL' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function update(Request $request) {
        try {
            $id = $request->id;
            $branch = Table::findOrFail($id);
            $request->validate([
                'model_name' => 'required|string|max:255',
                'model_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'com_id' => 'required|exists:company_master,id',
                'model_description' => 'required',
                'is_status' => 'required|boolean',
                'created_by' => 'required',
                'modified_by' => 'nullable',
            ]);

            if ($request->hasFile('model_photo')) {
                if ($branch->model_photo) {
                    $oldPhotoPath = public_path($branch->model_photo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('model_photo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'models/' . $photoName;
                $photoFile->move(public_path('models'), $photoName);
            } else {
                $photoPath = $branch->br_photo;
            }

            $branch->update([
                'model_name' => $request->model_name,
                'model_photo' => $photoPath,
                'com_id' => $request->com_id,
                'model_description' => $request->model_description,
                'is_status' => $request->is_status,
                'created_by' => $request->created_by,
                'modified_by' => $request->modified_by,
            ]);

            return redirect()
                ->route('admin.' . $this->handle_name_plural . '.index')
                ->with('success', 'Branch has been updated successfully.');
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
                        Table::onlyTrashed()->whereIn('id', explode(",", $data_id))->restore();
                    } else {
                        Table::onlyTrashed()->findOrFail($data_id)->restore();
                    }
                    break;

                case 'trash':
                    if ($is_bulk) {
                        Table::whereIn('id', explode(",", $data_id))->delete();
                    } else {
                        Table::findOrFail($data_id)->delete();
                    }
                    break;

                case 'delete':
                    if ($is_bulk) {
                        Table::withTrashed()->whereIn('id', explode(",", $data_id))->forceDelete();
                    } else {
                        Table::withTrashed()->findOrFail($data_id)->forceDelete();
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
        $modalObject = new Table();
        if (isset($request->string)) {
            $string = $request->string;
            $modalObject = $modalObject->where('br_code', 'like', "%" . $request->string . "%");
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