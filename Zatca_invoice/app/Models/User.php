<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'oracle';
    protected $table      = 'USERS';
    protected $primaryKey = 'USER_ID';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $fillable = ['USER_ID', 'USER_PASSWORD', 'USER_ANAME', 'USER_ENAME'];

    protected $hidden = ['USER_PASSWORD'];

    // Map Laravel's expected auth column names to Oracle column names
    public function getAuthIdentifierName(): string { return 'USER_ID'; }
    public function getAuthIdentifier()             { return $this->USER_ID; }
    public function getAuthPassword(): string       { return $this->USER_PASSWORD ?? ''; }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->USER_ANAME ?? $this->USER_ENAME ?? $this->USER_ID)
            : ($this->USER_ENAME ?? $this->USER_ANAME ?? $this->USER_ID);
    }

    // U_TYPE: if it equals 1 (or whatever "full" is), user is admin
    // Adjust the value below once confirmed from the DB
    public function isAdmin(): bool
    {
        return (int) $this->U_TYPE === 1;
    }

    public function isActive(): bool
    {
        return (int) $this->STATUS  !== 0
            && (int) $this->LOCKED  !== 1
            && (int) $this->DEL_FLAG !== 1;
    }
}
