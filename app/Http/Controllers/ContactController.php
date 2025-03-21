<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle contact form submission and send email.
     */
    public function send(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Send email to admin
        Mail::raw(
            "Message from: {$validated['name']} ({$validated['email']})\n\n{$validated['message']}",
            function ($message) use ($validated) {
                $message->to('craftsnwraps24@gmail.com')
                    ->subject('New Contact Form Submission');
            }
        );

        // Redirect back with success message
        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }

    
}
