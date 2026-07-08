<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Personne extends Authenticatable
{
    use Notifiable;

    protected $table = 'personnes';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'departement',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public static function passwordRules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
        ];
    }

    public static function passwordMessages(): array
    {
        return [
            'password.min'   => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&).',
        ];
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCandidat(): bool
    {
        return $this->role === 'candidat';
    }

    public function isRH(): bool
    {
        return $this->role === 'rh';
    }

    public function candidat()
    {
        return $this->hasOne(Candidat::class, 'personne_id');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'personne_id');
    }

    public function conges()
    {
        return $this->hasMany(Conge::class, 'personne_id');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'personne_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'personne_id');
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class, 'personne_id');
    }

    public function fichePaies()
    {
        return $this->hasMany(FichePaie::class, 'personne_id');
    }
}