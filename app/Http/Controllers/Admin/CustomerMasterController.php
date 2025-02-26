<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMaster as Table;
use DB;
use Illuminate\Http\Request;

class CustomerMasterController extends Controller {
    protected $handle_name = 'customer_master';
    protected $handle_name_plural = 'customer_master';

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

        $services = DB::table('branch_master')->get();
        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'services' => $services,
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

        // $categories = ServiceCategory::all();
        $services = DB::table('branch_master')->get();
        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'data' => $data,
            'services' => $services,
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
        try {
            $request->validate([
                'cust_name' => 'required|string|max:255',
                'cust_city' => 'required|string',
                'cust_res_address' => 'required',
                'cust_pick_default_addr' => 'required',
                'cust_email' => 'nullable|string',
                'cust_for_branch_id' => 'nullable',
                'cust_password' => 'nullable|string',
                'cust_package_id' => 'nullable',
                'is_package_selected' => 'nullable',
                'cust_pack_start_date' => 'nullable|date',
                'cust_pack_end_date' => 'nullable|date',
                'cust_is_pack_renew' => 'nullable|year',
                'cust_is_noti_req' => 'nullable',
                'cust_mobile_no' => 'nullable',
                'cust_whtapp_no' => 'nullable',
                'cust_com_id' => 'nullable|exists:company_master,id',
                'cust_model_id' => 'nullable|exists:model_master,id',
                'cust_vehicle_no' => 'nullable',
                'is_pack_expire' => 'nullable',
                'is_renreable' => 'nullable',
                // 'package_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                // 'service_id' => 'required|array',
                // 'service_id.*' => 'required|exists:service_cat_master,id',
            ]);

            $pack_code = $this->generatePackageCode();

            $photoPath = null;
            if ($request->hasFile('package_logo')) {
                $photoFile = $request->file('package_logo');
                $photoDirectory = public_path('packages');

                if (!file_exists($photoDirectory)) {
                    mkdir($photoDirectory, 0777, true);
                }

                $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move($photoDirectory, $photoName);
                $photoPath = 'packages/' . $photoName;
            }
            $serviceIds = implode(',', $request->service_id);

            $categoryProduct = Table::create([
                'pack_code' => $pack_code,
                'service_id' => $serviceIds,
                'pack_name' => $request->pack_name,
                'pack_duration' => $request->pack_duration,
                'pack_other_faci' => $request->pack_other_faci,
                'pack_description' => $request->pack_description,
                'pack_net_amt' => $request->pack_net_amt,
                'package_logo' => $photoPath,
                'is_status' => 1,
                'created_by' => auth()->user()->name,
            ]);

            return redirect()
                ->route('admin.' . $this->handle_name_plural . '.index')
                ->with('success', 'New ' . ucfirst($this->handle_name) . ' has been added.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    private function generatePackageCode() {
        $lastBranch = Table::latest('id')->first();
        return 'PACK' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }
    public function update(Request $request) {
        try {
            $request->validate([
                'pack_name' => 'required|string|max:255',
                'pack_other_faci' => 'required|string',
                'pack_description' => 'required|string',
                'pack_net_amt' => 'required|string',
                'pack_duration' => 'nullable',
                'package_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'service_id' => 'required|array',
                'service_id.*' => 'required|exists:service_cat_master,id',
            ]);

            $id = $request->id;
            $categoryProduct = Table::findOrFail($id);

            if ($request->hasFile('package_logo')) {
                if ($categoryProduct->package_logo) {
                    $oldPhotoPath = public_path($categoryProduct->package_logo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('package_logo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'packages/' . $photoName;
                $photoFile->move(public_path('packages'), $photoName);
            } else {
                $photoPath = $categoryProduct->package_logo;
            }

            $serviceIds = implode(',', $request->service_id);

            $categoryProduct->update([
                'service_id' => $serviceIds,
                'pack_name' => $request->pack_name,
                'package_logo' => $photoPath,
                'pack_duration' => $request->pack_duration,
                'pack_other_faci' => $request->pack_other_faci,
                'pack_description' => $request->pack_description,
                'pack_net_amt' => $request->pack_net_amt,
                'is_status' => 1,
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
