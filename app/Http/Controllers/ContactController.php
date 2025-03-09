<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);

        // Example: Sending an email (adjust as needed)
        Mail::raw($request->message, function ($mail) use ($request) {
            $mail->to('craftsnwraps24@gmail.com') // Change to your email
                ->subject($request->subject)
                ->from($request->email, $request->name);
        });

        return back()->with('success', 'Your message has been sent successfully!');
    }
}
