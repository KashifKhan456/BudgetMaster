<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:3'],
        ]);

        $request->user()->update($validated);

        return Redirect::route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
