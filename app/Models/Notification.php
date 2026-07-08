<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'message',
        'type',
        'dateEnvoi',
        'lu',
        'fichier',
        'personne_id',
    ];

    protected $casts = [
        'dateEnvoi' => 'date',
        'lu' => 'boolean',
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public static function envoyer(int $personneId, string $message, string $type = 'info', ?string $fichier = null): self
    {
        return self::create([
            'message' => $message,
            'type' => $type,
            'dateEnvoi' => now(),
            'lu' => false,
            'fichier' => $fichier,
            'personne_id' => $personneId,
        ]);
    }
}