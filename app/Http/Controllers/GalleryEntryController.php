<?php


namespace App\Http\Controllers;

use DateTime;
use Auth;
use App\Category;
use App\User;
use App\GalleryEntry;
use App\Comment;
use App\Http\Requests\StoreGalleryEntryRequest;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class GalleryEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $galleryEntries = GalleryEntry::all();
        $users = User::all();
        return view('gallery-entry.index', compact('galleryEntries', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $categories = Category::all();
      return view('gallery-entry.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGalleryEntryRequest $request)
    {
      $galleryEntry = new GalleryEntry();

      $galleryEntry->title = $request->title;
      $galleryEntry->description = $request->description;
      $galleryEntry->tags = ' '.$request->tags.' ';
      $galleryEntry->category_id = $request->category;

      $name = $request->file('image')->getClientOriginalName();
      $path = 'public/images/'.Auth::user()->id.'/';
      $date = new DateTime();
      $request->file('image')->storeAs($path, $date->getTimestamp().$name);
      $galleryEntry->image = $path.$date->getTimestamp().$name;

      Auth::user()->galleryEntries()->save($galleryEntry);
      return redirect('/')->with(['message' => 'Gallery entry successfully submitted']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\GalleryEntry  $galleryEntry
     * @return \Illuminate\Http\Response
     */
    public function show(GalleryEntry $galleryEntry)
    {
      $userComments = User::select('*', 'users.id as userId', 'comments.id as commentId')
      ->join('comments', 'users.id', '=', 'comments.user_id')
      ->where('comments.gallery_entry_id', '=', $galleryEntry->id)
      ->orderBy('comments.created_at')
      ->get();

      $tags = explode(' ', $galleryEntry->tags);
      $galleryEntry = GalleryEntry::select('*', 'users.id as userId', 'gallery_entries.id as galleryEntryId')
      ->join('users', 'user_id', '=', 'users.id')
      ->where('gallery_entries.id', '=', $galleryEntry->id)
      ->first();

      return view('gallery-entry.show', compact('galleryEntry', 'userComments', 'tags'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GalleryEntry  $galleryEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(GalleryEntry $galleryEntry)
    {
        $galleryEntry->tags = trim($galleryEntry->tags);
        $categories = Category::all();
        return view('gallery-entry.edit', compact('galleryEntry', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GalleryEntry  $galleryEntry
     * @return \Illuminate\Http\Response
     */
    public function update(StoreGalleryEntryRequest $request, GalleryEntry $galleryEntry)
    {
      if ($request->image != null) {
        $oldPath = 'public/images/'.Auth::user()->id.'/';

        if(!empty($galleryEntry->image)) {
          Storage::delete($oldPath.$galleryEntry->image);
        }

        $name = $request->file('image')->getClientOriginalName();
        $path = 'public/images/'.Auth::user()->id.'/';
        $date = new DateTime();
        $request->file('image')->storeAs($path, $date->getTimestamp().$name);
        $galleryEntry->image = $path.$date->getTimestamp().$name;
      }

      $galleryEntry->title = $request->title;
      $galleryEntry->description = $request->description;
      $galleryEntry->tags = ' '.$request->tags.' ';
      $galleryEntry->category_id = $request->category;
      $galleryEntry->update();

      return redirect('gallery-entry/'.$galleryEntry->id)->with(['message' => 'Gallery entry successfully edited']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GalleryEntry  $galleryEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(GalleryEntry $galleryEntry)
    {
        // dd($galleryEntry);
        if (!empty($galleryEntry->image)) {
          $oldPath = 'public/images/'.Auth::user()->id.'/';
          Storage::delete($oldPath.$galleryEntry->image);
        }
        $galleryEntry->delete();
        return redirect('/')->with(['message' => 'Gallery entry successfully deleted']);
    }
}
