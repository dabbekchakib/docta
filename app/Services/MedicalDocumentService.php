<?php

namespace App\Services;

use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalDocumentService
{
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    public const MAX_SIZE_KB = 10240;

    /**
     * Enregistre un document médical et son fichier (stockage privé).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(MedicalRecord $record, array $data, UploadedFile $file): MedicalDocument
    {
        $this->validate($file);

        $document = $record->medicalDocuments()->create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        $document->addMedia($file)
            ->usingFileName($this->safeFileName($file))
            ->toMediaCollection('medical_documents');

        return $document;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MedicalDocument $document, array $data, ?UploadedFile $file = null): MedicalDocument
    {
        if ($file) {
            $this->validate($file);

            $document->clearMediaCollection('medical_documents');
            $document->addMedia($file)
                ->usingFileName($this->safeFileName($file))
                ->toMediaCollection('medical_documents');
        }

        $document->fill($data);
        $document->save();

        return $document;
    }

    /**
     * Supprime le document et son fichier.
     */
    public function delete(MedicalDocument $document): void
    {
        $document->clearMediaCollection('medical_documents');
        $document->delete();
    }

    /**
     * Téléchargement sécurisé : le fichier est servi via le contrôleur
     * après vérification d'autorisation et journalisation de l'accès.
     */
    public function download(MedicalDocument $document): StreamedResponse
    {
        $media = $document->file();

        abort_if(! $media, 404, 'Fichier introuvable.');

        activity('medical_documents')
            ->performedOn($document)
            ->causedBy(Auth::user())
            ->log('Téléchargement du document médical');

        $disk = Storage::disk($media->disk);

        $path = $media->getPathRelativeToRoot();

        abort_if(! $disk->exists($path), 404, 'Fichier introuvable.');

        return response()->streamDownload(
            static fn () => print($disk->get($path)),
            $media->file_name,
            ['Content-Type' => $media->mime_type]
        );
    }

    /**
     * Valide le format et la taille du fichier.
     */
    public function validate(UploadedFile $file): void
    {
        abort_unless(in_array($file->getMimeType(), self::ALLOWED_MIMES, true), 422, 'Format de fichier non autorisé (PDF, JPG, JPEG, PNG uniquement).');
        abort_unless($file->getSize() <= self::MAX_SIZE_KB * 1024, 422, 'Le fichier dépasse la taille maximale de '.self::MAX_SIZE_KB.' Ko.');
    }

    /**
     * Sécurise le nom de fichier.
     */
    private function safeFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();

        return preg_replace('/[^A-Za-z0-9._-]/', '-', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            .'.'.strtolower((string) $extension);
    }
}
