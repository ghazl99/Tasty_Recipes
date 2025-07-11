<?php

namespace Modules\Auth\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia, HasOneTimePasswords;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor to get the URL of the user's profile photo.
     *
     * @return string
     */

    public function getProfilePhotoUrlAttribute()
    {
        // Attempt to retrieve the first media item from the 'avatars' collection
        $media = $this->getFirstMedia('avatars');

        // If media exists, return the storage URL to the file
        return $media
            ? url('storage/' . $media->id . '/' . $media->file_name)
            // If no media found, generate a default avatar image using ui-avatars.com
            : "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=0D8ABC&color=fff&size=256";
    }
    
    /**
     * Mutator to automatically hash the password before saving.
     *
     * @param  string  $value  The raw password entered by the user.
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        // Hash the password using Laravel's Hash facade (uses bcrypt by default)
        $this->attributes['password'] = Hash::make($value);
    }
}
