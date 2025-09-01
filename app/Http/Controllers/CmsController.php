<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function edit()
    {
        $settings = Setting::pluck('value', 'key'); 
        return view('settings.cms', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->only(['title', 'logo', 'font']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('settings.cms')->with('success', 'Appearance updated!');
    }
}
