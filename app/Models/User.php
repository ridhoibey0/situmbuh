<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;
use App\Models\UserMeasurement;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'phone', 'parent_name', 'bod', 'address_detail', 'province_id', 'regency_id', 'district_id', 'village_id', 'password', 'nik', 'gender', 'avatar', 'roles'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'roles' => UserRole::class,
    ];

    public function children(): HasMany
    {
        return $this->hasMany(Child::class, 'parent_id');
    }

    public function isParent(): bool
    {
        return $this->roles === UserRole::Parent;
    }

    public function isAdmin(): bool
    {
        return $this->roles?->isAdmin() === true;
    }

    public function isStaff(): bool
    {
        return $this->roles?->isStaff() === true;
    }

    public function measurements()
    {
        return $this->hasMany(UserMeasurement::class, 'user_id', 'id');
    }

    public function latestMeasurement()
    {
        return $this->hasOne(UserMeasurement::class, 'user_id', 'id')->latestOfMany();
    }

    public function resultQuestioner(): HasMany
    {
        return $this->hasMany(KpspResult::class, 'user_id', 'id');
    }

    public function latestResultQuestioners()
    {
        return $this->hasMany(KpspResult::class, 'user_id')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('kpsp_results')
                    ->groupBy('user_id', 'age_category_id');
            });
    }



    public function getProfileCompletedAttribute()
    {
        $basicFieldsComplete = $this->name && $this->bod && $this->gender && $this->phone && $this->parent_name;

        $hasCompleteMeasurements = $this->measurements->contains(function ($measurement) {
            return $measurement->height && $measurement->weight && $measurement->head_circumference && $measurement->arm_circumference;
        });

        return $basicFieldsComplete && $hasCompleteMeasurements;
    }
}
