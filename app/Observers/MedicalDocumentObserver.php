<?php

namespace App\Observers;

use App\Models\MedicalDocument;
use Illuminate\Support\Facades\Auth;

class MedicalDocumentObserver
{
    public function created(MedicalDocument $document): void
    {
        activity('medical_documents')
            ->performedOn($document)
            ->causedBy(Auth::user())
            ->withProperties(['title' => $document->title, 'type' => $document->document_type?->value])
            ->log('Document médical ajouté : '.$document->title);
    }

    public function deleted(MedicalDocument $document): void
    {
        activity('medical_documents')
            ->performedOn($document)
            ->causedBy(Auth::user())
            ->withProperties(['title' => $document->title])
            ->log('Document médical supprimé : '.$document->title);
    }
}
