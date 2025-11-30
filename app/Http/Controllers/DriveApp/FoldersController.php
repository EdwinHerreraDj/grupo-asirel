<?php

namespace App\Http\Controllers\DriveApp;

use App\Http\Controllers\Controller;

class FoldersController extends Controller
{
    public function index()
    {
        return view('empresa.drive.folders');
    }
}
