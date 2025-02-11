<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaMaster;
use App\Models\BranchMaster as Table;
use App\Models\CityMaster;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;

class BranchMasterController extends Controller {
    protected $handle_name = 'branch_master';
    protected $handle_name_plural = 'branch_master';

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
        $cities = CityMaster::orderBy('city_name')->get(['id', 'city_name']);
        $areas = AreaMaster::orderBy('area_name')->get(['id', 'area_name']);

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'cities' => $cities,
            'areas' => $areas,
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
        $cities = CityMaster::all(); // Fetch all cities
        $areas = AreaMaster::all();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'cities' => $cities,
            'areas' => $areas,
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
            'br_address' => 'required|string|max:255',
            'br_owner_name' => 'required|string|max:255',
            'br_owner_email' => 'required|email|max:255',
            'br_mobile' => 'required|string|max:15',
            'br_city' => 'nullable',
            'br_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'br_sign' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'br_state' => 'required|string|max:255',
            'br_start_Date' => 'required|date',
            'br_end_date' => 'nullable|date|after_or_equal:br_start_Date',
            'br_renew_year' => 'nullable',
            'br_connection_link' => 'nullable',
            'br_db_name' => 'nullable|string|max:255',
            'br_user_name' => 'nullable|string|max:255',
            'br_password' => 'nullable|string|max:255',
            'br_city_id' => 'required|exists:city_master,id',
            'br_area_id' => 'required|exists:area_master,id',
            'br_pin_code' => 'nullable|string|max:10',
        ]);

        $branch_code = $this->generateBranchCode();

        // $photoPath = $request->hasFile('br_photo') ? $request->file('br_photo')->store('branches', 'public') : null;
        // $signPath = $request->hasFile('br_sign') ? $request->file('br_sign')->store('signatures', 'public') : null;
        $photoPath = null;
        if ($request->hasFile('br_photo')) {
            $photoFile = $request->file('br_photo');
            $photoDirectory = public_path('branches');

            if (!file_exists($photoDirectory)) {
                mkdir($photoDirectory, 0777, true);
            }

            $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($photoDirectory, $photoName);
            $photoPath = 'branches/' . $photoName;
        }

        // Handle br_sign
        $signPath = null;
        if ($request->hasFile('br_sign')) {
            $signFile = $request->file('br_sign');
            $signDirectory = public_path('signatures');

            if (!file_exists($signDirectory)) {
                mkdir($signDirectory, 0777, true);
            }

            $signName = uniqid() . '_' . time() . '.' . $signFile->getClientOriginalExtension();
            $signFile->move($signDirectory, $signName);
            $signPath = 'signatures/' . $signName;
        }

        $branch = Table::create([
            'br_code' => $branch_code,
            'br_address' => $request->br_address,
            'br_owner_name' => $request->br_owner_name,
            'br_owner_email' => $request->br_owner_email,
            'br_mobile' => $request->br_mobile,
            'br_city' => $request->br_city,
            'br_photo' => $photoPath,
            'br_sign' => $signPath,
            'br_state' => $request->br_state,
            'br_start_Date' => $request->br_start_Date,
            'br_end_date' => $request->br_end_date,
            'br_renew_year' => $request->br_renew_year,
            'br_connection_link' => $request->br_connection_link,
            'br_db_name' => $request->br_db_name,
            'br_user_name' => $request->br_user_name,
            'br_password' => bcrypt($request->br_password),
            'br_city_id' => $request->br_city_id,
            'br_area_id' => $request->br_area_id,
            'br_pin_code' => $request->br_pin_code,
            'is_status' => 1,
            'created_by' => auth()->user()->name,
        ]);

        return redirect()
            ->route('admin.' . $this->handle_name_plural . '.index')
            ->with('success', 'New ' . ucfirst($this->handle_name) . ' has been added.');
    }

    private function generateBranchCode() {
        $lastBranch = Table::latest('id')->first();
        return 'BR' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function update(Request $request) {
        try {
            $id = $request->id;
            $branch = Table::findOrFail($id);
            $request->validate([
                'br_address' => 'required|string|max:255',
                'br_owner_name' => 'required|string|max:255',
                'br_owner_email' => 'required|email|max:255',
                'br_mobile' => 'required|string|max:15',
                'br_city' => 'nullable',
                'br_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_sign' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_state' => 'required|string|max:255',
                'br_start_Date' => 'required|date',
                'br_end_date' => 'nullable|date|after_or_equal:br_start_Date',
                'br_renew_year' => 'nullable',
                'br_connection_link' => 'nullable',
                'br_db_name' => 'nullable|string|max:255',
                'br_user_name' => 'nullable|string|max:255',
                'br_password' => 'nullable|string|max:255',
                'br_city_id' => 'required|exists:city_master,id',
                'br_area_id' => 'required|exists:area_master,id',
                'br_pin_code' => 'nullable|string|max:10',
            ]);

            if ($request->hasFile('br_photo')) {
                if ($branch->br_photo) {
                    $oldPhotoPath = public_path($branch->br_photo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('br_photo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'branches/' . $photoName;
                $photoFile->move(public_path('branches'), $photoName);
            } else {
                $photoPath = $branch->br_photo;
            }

            // Handling br_sign
            if ($request->hasFile('br_sign')) {
                if ($branch->br_sign) {
                    $oldSignPath = public_path($branch->br_sign);
                    if (file_exists($oldSignPath)) {
                        unlink($oldSignPath);
                    }
                }
                $signFile = $request->file('br_sign');
                $signName = time() . '_sign.' . $signFile->getClientOriginalExtension();
                $signPath = 'signatures/' . $signName;
                $signFile->move(public_path('signatures'), $signName);
            } else {
                $signPath = $branch->br_sign;
            }
            $branch->update([
                'br_address' => $request->br_address,
                'br_owner_name' => $request->br_owner_name,
                'br_owner_email' => $request->br_owner_email,
                'br_mobile' => $request->br_mobile,
                'br_city' => $request->br_city,
                'br_photo' => $photoPath,
                'br_sign' => $signPath,
                'br_state' => $request->br_state,
                'br_start_Date' => $request->br_start_Date,
                'br_end_date' => $request->br_end_date,
                'br_renew_year' => $request->br_renew_year,
                'br_connection_link' => $request->br_connection_link,
                'br_db_name' => $request->br_db_name,
                'br_user_name' => $request->br_user_name,
                'br_password' => $request->filled('br_password') ? bcrypt($request->br_password) : $branch->br_password, // Password update only if changed
                'br_city_id' => $request->br_city_id,
                'br_area_id' => $request->br_area_id,
                'br_pin_code' => $request->br_pin_code,
                'is_status' => 1,
                'modified_by' => auth()->user()->name,
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