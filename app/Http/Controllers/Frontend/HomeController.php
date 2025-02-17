<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CompanyMaster;
use App\Models\ModelMaster;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class HomeController extends Controller {
    public function index(Request $request) {
        $services = ServiceCategory::where('is_status', '1')->get();
        return view('frontend.index', compact('services'));
    }

    public function getManufacturers() {
        $manufacturers = CompanyMaster::all();
        // dd($manufacturers);
        // die;
        return response()->json($manufacturers);
    }

    public function getModels($manufacturer_id) {
        $models = ModelMaster::where('com_id', $manufacturer_id)->get();
        return response()->json($models);
    }

    public function about(Request $request) {
        return view('frontend.about');
    }

    public function service(Request $request) {
        return view('frontend.service');
    }
    public function booking(Request $request) {
        return view('frontend.booking');
    }
    public function contact(Request $request) {
        return view('frontend.contact');
    }
    public function testimonial(Request $request) {
        return view('frontend.testimonial');
    }

    public function team(Request $request) {
        return view('frontend.team');
    }
}