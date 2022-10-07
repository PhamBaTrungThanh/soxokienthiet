<?php

namespace App\Controllers;

use App\Models\Lottery;
use Illuminate\Http\Request;

class TinkerController
{
    public function index(Request $request)
    {
        $lottery = (new Lottery())->latest();
        dd($lottery);
    }
}
