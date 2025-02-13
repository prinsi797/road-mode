<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyMaster as Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Exception;
use DB;

class CompanyMasterController extends Controller {
    protected $handle_name = 'company_master';
    protected $handle_name_plural = 'company_master';

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
        $data = Table::where('id', '=', $id)->first();

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
            // 'bike_car' => 'required|string|max:255',
            'com_name' => 'required|string|max:255',
            'com_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehical_id' => 'required|exists:vehicles,id',
        ]);

        $com_code = $this->generateBranchCode();

        // $photoPath = $request->hasFile('br_photo') ? $request->file('br_photo')->store('branches', 'public') : null;
        // $signPath = $request->hasFile('br_sign') ? $request->file('br_sign')->store('signatures', 'public') : null;
        $photoPath = null;
        if ($request->hasFile('com_logo')) {
            $photoFile = $request->file('com_logo');
            $photoDirectory = public_path('company');

            if (!file_exists($photoDirectory)) {
                mkdir($photoDirectory, 0777, true);
            }

            $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($photoDirectory, $photoName);
            $photoPath = 'company/' . $photoName;
        }

        $branch = Table::create([
            'com_code' => $com_code,
            'com_name' => $request->com_name,
            // 'bike_car' => $request->bike_car,
            'com_logo' => $photoPath,
            'vehical_id' => $request->vehical_id,
            'is_status' => 1,
            'created_by' => auth()->user()->name,
        ]);

        return redirect()
            ->route('admin.' . $this->handle_name_plural . '.index')
            ->with('success', 'New ' . ucfirst($this->handle_name) . ' has been added.');
    }

    private function generateBranchCode() {
        $lastBranch = Table::latest('id')->first();
        return 'COMP' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function update(Request $request) {
        try {
            $id = $request->id;
            $company = Table::findOrFail($id);
            $request->validate([
                // 'bike_car' => 'required|string|max:255',
                'com_name' => 'required|string|max:255',
                'vehical_id' => 'required|exists:vehicles,id',
                'com_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('com_logo')) {
                if ($company->com_logo) {
                    $oldPhotoPath = public_path($company->com_logo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('com_logo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'company/' . $photoName;
                $photoFile->move(public_path('company'), $photoName);
            } else {
                $photoPath = $company->com_logo;
            }

            $company->update([
                'com_name' => $request->com_name,
                // 'bike_car' => $request->bike_car,
                'vehical_id' => $request->vehical_id,
                'com_logo' => $photoPath,
                'is_status' => 1,
                'modified_by' => auth()->user()->name,
            ]);

            return redirect()
                ->route('admin.' . $this->handle_name_plural . '.index')
                ->with('success', 'Comapny has been updated successfully.');
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
    public function toggleStatus(Request $request) {
        try {
            $category = Table::findOrFail($request->id);
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
}
