<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\GalleryEntry;
use App\Category;
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
      // dd(Auth::user());
      $galleryEntries = GalleryEntry::select('*', 'gallery_entries.id as galleryEntryId',
      'users.id as userId')
      ->join('users', 'user_id', '=', 'users.id')
      ->orderBy('galleryEntryId', 'desc')
      ->paginate(20);
      $categories = Category::all();
      return view('home', compact('galleryEntries', 'categories'));
    }
}
