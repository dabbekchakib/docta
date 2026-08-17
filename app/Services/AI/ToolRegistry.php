<?php

namespace App\Services\AI;

use App\Services\AI\Tools\AIToolInterface;
use App\Services\AI\Tools\GetStatsTool;
use App\Services\AI\Tools\SearchPatientsTool;
use App\Services\AI\Tools\ViewAppointmentTool;
use App\Services\AI\Tools\ViewConsultationTool;
use App\Services\AI\Tools\ViewDoctorAgendaTool;
use App\Services\AI\Tools\ViewInvoiceTool;
use App\Services\AI\Tools\ViewPaymentsTool;
use App\Services\AI\Tools\ViewPrescriptionTool;
use App\Services\AI\Tools\ViewPatientTool;
use App\Services\AI\Tools\CreateAppointmentTool;
use App\Services\AI\Tools\CreateConsultationTool;
use App\Services\AI\Tools\CreateInvoiceTool;
use App\Services\AI\Tools\RecordPaymentTool;
use App\Services\AI\Tools\CreateNoteTool;
use App\Services\AI\Tools\ConfirmActionTool;
use Illuminate\Support\Collection;

class ToolRegistry
{
    /** @var Collection<int, AIToolInterface> */
    private Collection $tools;

    public function __construct()
    {
        $this->tools = collect();
        $this->registerDefaults();
    }

    private function registerDefaults(): void
    {
        // Tools de consultation (lecture)
        $this->register(new SearchPatientsTool);
        $this->register(new ViewPatientTool);
        $this->register(new ViewAppointmentTool);
        $this->register(new ViewDoctorAgendaTool);
        $this->register(new ViewConsultationTool);
        $this->register(new ViewPrescriptionTool);
        $this->register(new ViewInvoiceTool);
        $this->register(new ViewPaymentsTool);
        $this->register(new GetStatsTool);

        // Tools d'action (écriture - nécessitent confirmation)
        $this->register(new CreateAppointmentTool);
        $this->register(new CreateConsultationTool);
        $this->register(new CreateInvoiceTool);
        $this->register(new RecordPaymentTool);
        $this->register(new CreateNoteTool);

        // Tool de confirmation
        $this->register(new ConfirmActionTool);
    }

    public function register(AIToolInterface $tool): void
    {
        $this->tools->push($tool);
    }

    public function get(string $name): ?AIToolInterface
    {
        return $this->tools->first(fn (AIToolInterface $t): bool => $t->getName() === $name);
    }

    /**
     * @return Collection<int, AIToolInterface>
     */
    public function all(): Collection
    {
        return $this->tools;
    }

    /**
     * Retourne les tools autorisés pour un utilisateur donné, au format OpenRouter.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOpenRouterToolsForUser(\App\Models\User $user): array
    {
        return $this->tools
            ->filter(fn (AIToolInterface $tool): bool => $tool->authorize($user))
            ->map(fn (AIToolInterface $tool): array => $tool->toOpenRouterFormat())
            ->values()
            ->all();
    }

    /**
     * Retourne les tools de lecture (pas de confirmation) pour un utilisateur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getReadOnlyToolsForUser(\App\Models\User $user): array
    {
        return $this->tools
            ->filter(fn (AIToolInterface $tool): bool => $tool->authorize($user) && ! $tool->requiresConfirmation())
            ->map(fn (AIToolInterface $tool): array => $tool->toOpenRouterFormat())
            ->values()
            ->all();
    }
}
