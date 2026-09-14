<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Rules\ValidRecaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the public contact form.
     */
    public function show(): View
    {
        return view('public.contact');
    }

    /**
     * Validate and store a new contact message.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recaptcha_token' => ['required', 'string', new ValidRecaptcha],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create(collect($validated)->except('recaptcha_token')->all());

        return redirect()
            ->route('contact')
            ->with('success', __('Your message has been sent successfully. We will get back to you soon!'));
    }
}
