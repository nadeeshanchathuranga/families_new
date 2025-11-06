<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;

class BulkUploadController extends Controller
{
    public function index()
    {

        return Inertia::render('Bulk/Index');
    }

}
