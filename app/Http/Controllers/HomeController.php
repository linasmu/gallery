<?php

namespace App\Http\Controllers;

use App\User;
use App\GalleryEntry;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //Patikrina ar vartotojas prisijunges, jei neprisijunges uzkrauna logina
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $galleryEntries = GalleryEntry::all();
      $users = User::all();
      return view('home', compact('galleryEntries'), compact('users'));
    }
}