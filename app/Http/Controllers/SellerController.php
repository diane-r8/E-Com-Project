<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function dashboard()
    {
        return view('seller.dashboard');
    }

    public function products()
    {
        return view('seller.products');
    }

    public function about()
    {
        return view('seller.about');
    }

    public function contact()
    {
        return view('seller.contact');
    }
}
