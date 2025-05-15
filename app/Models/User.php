<?php

namespace App\Models;

use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;  
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'username',
        'email',
        'password',
        'user_type', // optional if you're setting default as 'buyer'
        'two_factor_enabled',
        'profile_picture',  //newly added
    ];

    /**
     * Define the relationship with the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');//newly added
    }

    /**
     * Override the default email verification notification to use a custom Mailable.
     */
    public function sendEmailVerificationNotification()
    {
        Mail::to($this->email)->send(new VerifyEmail($this));
    }

    /**
     * Override the default password reset notification to use a custom Mailable.
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)->send(new ResetPassword($this, $token));
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
