<?php

namespace App\Services;

use App\Models\Candidat;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ContratService
{
    /**
     * Génère le PDF du contrat de travail d'un candidat affecté et le stocke
     * sur le disque "public" (storage/app/public/contrats/...). Seul le CHEMIN
     * du fichier est destiné à être enregistré en base (jamais le PDF lui-même).
     */
    public static function genererPdf($personne, Candidat $candidat): string
    {
        $pdf = Pdf::loadView('pdf.contrat', [
            'personne' => $personne,
            'departement' => $candidat->affectation,
            'salaireOffre' => $candidat->salaire_propose,
            'responsableNom' => $candidat->responsable_nom,
            'dateAffectation' => Carbon::parse($candidat->date_affectation)->format('d/m/Y'),
        ]);

        $nomFichier = 'contrats/contrat_' . $candidat->personne_id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($nomFichier, $pdf->output());

        return $nomFichier;
    }

    /**
     * Retourne le chemin du dernier contrat déjà envoyé à ce candidat
     * (notification dont le fichier commence par "contrats/"), ou null
     * si aucun contrat n'a jamais été généré pour lui.
     */
    public static function dernierContratExistant(int $personneId): ?string
    {
        $notif = Notification::where('personne_id', $personneId)
            ->where('fichier', 'like', 'contrats/%')
            ->latest()
            ->first();

        return $notif?->fichier;
    }

    /**
     * Génère un contrat pour le candidat et crée la notification associée.
     * Utilisé aussi bien pour l'envoi initial que pour un renvoi manuel.
     */
    public static function genererEtNotifier($personne, Candidat $candidat, string $message, string $type = 'info'): string
    {
        $fichier = self::genererPdf($personne, $candidat);

        Notification::create([
            'personne_id' => $candidat->personne_id,
            'message' => $message,
            'type' => $type,
            'dateEnvoi' => now(),
            'lu' => false,
            'fichier' => $fichier,
        ]);

        return $fichier;
    }
}