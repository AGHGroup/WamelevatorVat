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

    protected $fillable = ['USER_ID', 'USER_PASSWORD', 'USER_ANAME', 'USER_ENAME', 'ZATCA_INVOICES', 'WAMELEVATOR'];

    protected $hidden = ['USER_PASSWORD'];

    // Map Laravel's expected auth column names to Oracle column names
    public function getAuthIdentifierName(): string { return 'user_id'; }
    public function getAuthIdentifier()             { return $this->user_id ?? $this->USER_ID; }
    public function getAuthPassword(): string       { return $this->user_password ?? $this->USER_PASSWORD ?? ""; }

    public function getNameAttribute(): string
    {
        return (string) (app()->getLocale() === 'ar'
            ? ($this->user_aname ?? $this->USER_ANAME ?? $this->user_ename ?? $this->USER_ENAME ?? $this->user_id ?? $this->USER_ID ?? '')
            : ($this->user_ename ?? $this->USER_ENAME ?? $this->user_aname ?? $this->USER_ANAME ?? $this->user_id ?? $this->USER_ID ?? ''));
    }

    // U_TYPE: if it equals 1 (or whatever "full" is), user is admin
    // Adjust the value below once confirmed from the DB
    public function isAdmin(): bool
    {
        return (int) ($this->u_type ?? $this->U_TYPE ?? 0) === 1;
    }

    public function hasZatcaAccess(): bool
    {
        return (int)($this->zatca_invoices ?? $this->ZATCA_INVOICES ?? 0) === 1;
    }

    public function hasWamelevatorAccess(): bool
    {
        return (int)($this->wamelevator ?? $this->WAMELEVATOR ?? 0) === 1;
    }

    public function isActive(): bool
    {
        // Only block when a column is explicitly set to a blocking value
        $locked  = $this->locked   ?? $this->LOCKED   ?? null;
        $delFlag = $this->del_flag ?? $this->DEL_FLAG ?? null;
        $status  = $this->status   ?? $this->STATUS   ?? null;

        if ($locked  !== null && (int)$locked  === 1) return false;
        if ($delFlag !== null && (int)$delFlag === 1) return false;

        return true;
    }
}

