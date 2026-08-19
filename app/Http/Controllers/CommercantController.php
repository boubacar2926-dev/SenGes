<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommercantController extends Controller
{
    public function index()
    {
        return view('commercant.dashboard');
    }
}
