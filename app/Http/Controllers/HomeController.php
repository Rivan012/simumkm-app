<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $produk = Produk::latest()->paginate(3);
        return view("welcome", compact("produk"));
    }
}
