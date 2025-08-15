<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function portfolios() {
        return $this->hasMany(Portfolio::class);
    }  
    
    public function dividends()
    {
        return $this->hasManyThrough(Dividend::class, Asset::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Por enquanto, vamos permitir o acesso a todos os usuários registrados.
        // No futuro, você pode adicionar uma lógica mais complexa aqui, como:
        // return $this->is_admin === true;
        // ou
        // return str_ends_with($this->email, '@suaempresa.com');
        return true;
    }
}
