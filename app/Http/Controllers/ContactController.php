<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session; 


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

        try {
            Mail::raw($request->message, function ($mail) use ($request) {
                $mail->to('craftsnwraps24@gmail.com') // Change to your email
                    ->subject($request->subject)
                    ->from($request->email, $request->name);
            });

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());
            return back()->with('success', 'Your message has been submitted! (Note: Email might not have been sent)');
        }
    }
}

