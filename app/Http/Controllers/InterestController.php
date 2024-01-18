<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{

    public function index() {
        return response()->json(Interest::all());
    }
}
