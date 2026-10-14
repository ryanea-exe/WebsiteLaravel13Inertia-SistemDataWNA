<?php

namespace App\Models;

use App\Concerns\HasTeams;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property string|null $name
 * @property string $username
 * @property string|null $email
 * @property string $password
 * @property UserRole $role
 * @property string|null $foto
 * @property Carbon|null $terakhir_login
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $foto_url
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read Collection<int, Team> $teams
 */
#[Fillable(['nama', 'name', 'username', 'email', 'password', 'role', 'foto', 'terakhir_login', 'current_team_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTeams, Notifiable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'foto_url',
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
            'terakhir_login' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Check if user has user role.
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::User;
    }

    /**
     * Keep name and nama synchronized for compatibility.
     *
     * @return Attribute<string, string|null>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?: ($this->attributes['nama'] ?? ''),
            set: fn (?string $value) => [
                'name' => $value,
                'nama' => $this->attributes['nama'] ?? $value,
            ],
        );
    }

    /**
     * Get the authenticated streaming URL for the user avatar photo.
     *
     * @return Attribute<string|null, never>
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->foto && $this->id ? route('data-user.photo', $this->id) : null
        );
    }

    /**
     * Scope query to search by nama, username, or email.
     *
     * @param  Builder<User>  $query
     */
    public function scopeSearch(Builder $query, string $search): void
    {
        $term = '%'.trim($search).'%';
        $query->where(function (Builder $sub) use ($term): void {
            $sub->where('nama', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhere('email', 'like', $term);
        });
    }

    /**
     * Scope query by role string or enum.
     *
     * @param  Builder<User>  $query
     */
    public function scopeRole(Builder $query, UserRole|string $role): void
    {
        $value = $role instanceof UserRole ? $role->value : $role;
        $query->where('role', $value);
    }
}
