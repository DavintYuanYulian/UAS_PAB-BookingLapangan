<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingPageController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookings/Index');
    }
    public function use($id)
    {
        return Inertia::render('Bookings/Use', ['id' => $id]);
    }
}
