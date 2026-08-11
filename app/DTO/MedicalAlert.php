<?php

namespace App\DTO;

use App\Enums\MedicalAlertSeverity;
use App\Enums\MedicalAlertType;

/**
 * Alerte médicale calculée (objet valeur).
 *
 * Préparé pour être étendu par les futurs modules :
 * interaction médicamenteuse, résultat biologique critique,
 * contre-indication, grossesse, allergie.
 */
final readonly class MedicalAlert
{
    public function __construct(
        public MedicalAlertType $type,
        public MedicalAlertSeverity $severity,
        public string $title,
        public string $message,
        public bool $active = true,
    ) {}

    public function isHighPriority(): bool
    {
        return $this->active && $this->severity->isHighPriority();
    }
}
