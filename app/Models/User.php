<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; 
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'employee_id',
        'facility',
        'profile_photo_path',
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
     * Portal role key (admin, procurement_officer, etc.).
     */
    public function portalRoleKey(): ?string
    {
        foreach (array_keys(config('portal.roles', [])) as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function portalRoleMeta(): ?array
    {
        $role = $this->portalRoleKey();

        if (! $role) {
            return null;
        }

        return array_merge(config("portal.roles.{$role}", []), ['key' => $role]);
    }

    public function roleLabel(): string
    {
        return $this->portalRoleMeta()['label'] ?? 'Portal user';
    }

    public function facilityLabel(): string
    {
        if (filled($this->facility)) {
            return $this->facility;
        }

        return match ($this->portalRoleKey()) {
            'admin' => 'Department of Health (NDoH), Port Moresby',
            'procurement_officer' => 'NDoH Central Procurement, Port Moresby',
            'store_manager' => 'Lae AMS Regional Warehouse',
            'pharmacy_manager', 'pharmacist' => 'Modilon General Hospital, Madang',
            default => 'MediTrack PNG',
        };
    }

    public function inventoryScopeLabel(): ?string
    {
        return $this->portalRoleMeta()['inventory_label'] ?? null;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn (string $part) => substr($part, 0, 1))->implode(''));
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->profile_photo_path)) {
            return null;
        }

        return '/storage/'.$this->profile_photo_path;
    }

    public function deleteProfilePhoto(): void
    {
        if (! $this->profile_photo_path) {
            return;
        }

        Storage::disk('public')->delete($this->profile_photo_path);
        $this->profile_photo_path = null;
    }
}
