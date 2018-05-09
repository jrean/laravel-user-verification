<?php

namespace Jrean\UserVerification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationToken extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'expires_at'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        static::creating(function ($token) {
            optional($token->user->confirmationToken)->delete();
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'token';
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', App\User::class));
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        return $this->freshTimestamp()->gt($this->expires_at);
    }
}
