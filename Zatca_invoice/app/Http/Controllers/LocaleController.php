<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    protected array $supported = ['ar', 'en'];

    public function switch(string $locale)
    {
        if (in_array($locale, $this->supported)) {
            session(['locale' => $locale]);
        }

        return redirect()->back()->withInput();
    }
}
