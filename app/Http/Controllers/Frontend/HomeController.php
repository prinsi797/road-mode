<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller {
    public function index(Request $request) {
        return view('frontend.index');
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