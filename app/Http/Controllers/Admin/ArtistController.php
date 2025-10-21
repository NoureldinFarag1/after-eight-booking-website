<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    public function index()
    {
        $artists = Artist::orderBy('name')->paginate(20);
        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        return view('admin.artists.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('artist_images', 'public');
        }

        Artist::create([
            'name' => $data['name'],
            'photo_url' => $photoPath,
        ]);

        return redirect()->route('admin.artists.index')->with('success', 'Artist created.');
    }

    public function edit(Artist $artist)
    {
        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'remove_photo' => 'nullable|in:1',
        ]);

        $updates = ['name' => $data['name']];

        if ($request->boolean('remove_photo') && $artist->photo_url) {
            Storage::disk('public')->delete($artist->photo_url);
            $updates['photo_url'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($artist->photo_url) {
                Storage::disk('public')->delete($artist->photo_url);
            }
            $updates['photo_url'] = $request->file('photo')->store('artist_images', 'public');
        }

        $artist->update($updates);

        return redirect()->route('admin.artists.index')->with('success', 'Artist updated.');
    }

    public function destroy(Artist $artist)
    {
        // detach from events first
        $artist->events()->detach();
        if ($artist->photo_url) {
            Storage::disk('public')->delete($artist->photo_url);
        }
        $artist->delete();
        return redirect()->route('admin.artists.index')->with('success', 'Artist deleted.');
    }
}
