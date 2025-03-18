<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOTP extends Mailable implements ShouldQueue // Ensure it can be queued
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     *
     * @param int $otp
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your OTP for Login Verification')
                    ->view('emails.otp')
                    ->with([
                        'otp' => $this->otp,
                        'expires_in' => 2 // Minutes until expiration
                    ]);
    }
}
