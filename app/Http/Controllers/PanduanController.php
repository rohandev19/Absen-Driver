<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanduanController extends Controller
{
    /**
     * Tampilkan panduan untuk Admin Master & Admin Service.
     */
    public function admin()
    {
        return view('admin.panduan.index');
    }

    /**
     * Tampilkan panduan eksklusif untuk Customer.
     */
    public function customer()
    {
        return view('customer.panduan.index');
    }
}
