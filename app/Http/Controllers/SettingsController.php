<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // /settings -> dashboard index
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Display the appearance settings form.
     *
     * @return \Illuminate\View\View
     */
    public function editAppearance()
    {
        // Fetch all settings and convert to an associative array
        $settings = Setting::pluck('value', 'key')->toArray();

        // Define default values for settings that might not exist yet
        $defaults = [
            'brand_name' => 'Citra Nugerah Karya',
            'theme'      => 'light',
            'font'       => 'Nunito',
            'logo_path'  => null,
        ];

        // Merge fetched settings with defaults to ensure all keys are present
        $settings = array_merge($defaults, $settings);

        return view('settings.appearance', compact('settings'));
    }

    /**
     * Update the appearance settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAppearance(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:100',
            'font'       => ['required', Rule::in(['Nunito', 'Inter', 'Roboto', 'Poppins', 'Open Sans'])],
            'logo'       => 'nullable|image|mimes:png|max:2048',
        ]);
        
        $this->putSetting('brand_name', $request->brand_name);
        $this->putSetting('font', $request->font);

        // Handle the logo file upload
        if ($request->hasFile('logo')) {
            // Delete the old logo if it exists
            $oldLogoPath = Setting::where('key', 'logo_path')->value('value');
            if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            // Store the new logo in the 'logos' directory in the 'public' disk
            $path = $request->file('logo')->store('logos', 'public');
            $this->putSetting('logo_path', $path);
        }

        // Clear the application settings cache if any exists
        cache()->forget('app_settings');

        return redirect()->route('settings.appearance')->with('success', 'Appearance updated!');
    }

    /**
     * Helper method to update or create a setting.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function putSetting(string $key, $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
