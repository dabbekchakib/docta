<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

class PrescriptionVerificationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Page de vérification d'une ordonnance à partir du QR Code.
     * Seule l'authentification (rôle médical) permet d'accéder au détail.
     */
    public function show(string $token): View
    {
        $prescription = Prescription::query()
            ->where('verification_token', $token)
            ->with(['patient', 'doctor'])
            ->firstOrFail();

        $this->authorize('view', $prescription);

        return view('prescriptions.verify', ['prescription' => $prescription]);
    }
}
