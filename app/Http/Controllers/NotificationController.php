<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function marquerLu()
    {
        Notification::where('personne_id', Auth::id())
            ->where('lu', false)
            ->update(['lu' => true]);

        return response()->noContent();
    }
}