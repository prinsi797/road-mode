<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryProduct;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class ProductController extends Controller {
    protected $handle_name = 'category_products';
    protected $handle_name_plural = 'category_products';

    public function index() {
        $all_count = CategoryProduct::count();
        $trashed_count = CategoryProduct::onlyTrashed()->count();

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
        $categories = ServiceCategory::all();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.store'),
            'edit' => 0,
            'categories' => $categories,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function edit(Request $request) {
        $ecrypted_id = $request->encrypted_id;
        $id = Crypt::decryptString($ecrypted_id);
        $data = CategoryProduct::where('id', '=', $id)->first();

        $categories = ServiceCategory::all();

        return kview($this->handle_name_plural . '.manage', [
            'index_route' => route('admin.' . $this->handle_name_plural . '.index'),
            'form_action' => route('admin.' . $this->handle_name_plural . '.update'),
            'edit' => 1,
            'data' => $data,
            'categories' => $categories,
            'module_names' => [
                'singular' => $this->handle_name,
                'plural' => $this->handle_name_plural,
            ],
        ]);
    }
    public function show(Request $request) {
        $id = Crypt::decryptString($request->encrypted_id);
        $data = CategoryProduct::findOrFail($id);

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
                'category_id' => 'required',
                'name' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'price' => 'required|numeric',
                'sell_price' => 'nullable|numeric',
                'suggestion' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('products', 'public'); // Store in 'products' folder in 'storage/app/public'
            }

            $categoryProduct = CategoryProduct::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'photo' => $photoPath,
                'price' => $request->price,
                'sell_price' => $request->sell_price,
                'suggestion' => $request->suggestion,
                'description' => $request->description,
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
                'id' => 'required|exists:category_products,id',
                'category_id' => 'required',
                'name' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
                'price' => 'required|numeric',
                'sell_price' => 'nullable|numeric',
                'suggestion' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $id = $request->id;
            $categoryProduct = CategoryProduct::findOrFail($id);

            $photoPath = $categoryProduct->photo; // Keep the existing photo path
            if ($request->hasFile('photo')) {
                // Delete the old photo if it exists
                if ($photoPath) {
                    \Storage::disk('public')->delete($photoPath);
                }

                // Store the new photo
                $photoPath = $request->file('photo')->store('products', 'public');
            }

            $categoryProduct->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'photo' => $photoPath,
                'price' => $request->price,
                'sell_price' => $request->sell_price,
                'suggestion' => $request->suggestion,
                'description' => $request->description,
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
                        CategoryProduct::onlyTrashed()->whereIn('id', explode(",", $data_id))->restore();
                    } else {
                        CategoryProduct::onlyTrashed()->findOrFail($data_id)->restore();
                    }
                    break;

                case 'trash':
                    if ($is_bulk) {
                        CategoryProduct::whereIn('id', explode(",", $data_id))->delete();
                    } else {
                        CategoryProduct::findOrFail($data_id)->delete();
                    }
                    break;

                case 'delete':
                    if ($is_bulk) {
                        CategoryProduct::withTrashed()->whereIn('id', explode(",", $data_id))->forceDelete();
                    } else {
                        CategoryProduct::withTrashed()->findOrFail($data_id)->forceDelete();
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
        $modalObject = new CategoryProduct();
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