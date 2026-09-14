<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\User;

class RoutingController extends Controller
{
    public function home()
    {
        $totalUsers = User::count();
        $totalObra = Obra::count();

        return view('index', compact('totalUsers', 'totalObra'));
    }
}
