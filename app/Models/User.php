<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;


class User extends Authenticatable implements JWTSubject, CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected $fillable = [
        'email',
        'user_name',
        'first_name',
        'last_name',
        'password',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'zipcode',
        'role',


    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'created_at',
        'password',
        'email_verified_at'

    ];


    protected function getDefaultGuardName(): string { return 'api'; }

}
