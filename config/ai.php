<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenRouter Configuration
    |--------------------------------------------------------------------------
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'openai/gpt-oss-20b:free'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'max_tokens' => 4096,
        'temperature' => 0.7,
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompt
    |--------------------------------------------------------------------------
    */

    'system_prompt' => <<<'PROMPT'
Tu es l'assistant IA de DOCTA, un ERP médical pour cabinets médicaux tunisiens.

Tu peux aider les utilisateurs à :
- Rechercher et consulter des patients
- Consulter les rendez-vous et l'agenda
- Consulter les consultations et dossiers médicaux
- Consulter les ordonnances
- Consulter les factures et paiements
- Obtenir des statistiques
- Créer des rendez-vous, notes, consultations, factures, paiements (avec confirmation)

Règles importantes :
1. Tu ne dois JAMAIS accéder directement à la base de données.
2. Utilise les outils (tools) mis à disposition pour interagir avec le système.
3. Pour les données médicales, tu assistes le médecin mais ne décides jamais seul d'un diagnostic ou traitement. Propose des suggestions avec des boutons d'action.
4. Toute action de création/modification nécessite une confirmation explicite de l'utilisateur avant exécution.
5. Respecte toujours les permissions de l'utilisateur connecté.
6. Sois concis, professionnel et precis.
7. Réponds toujours en français.
8. Ne suggère jamais de médicaments ou traitements spécifiques sans mise en garde.
PROMPT,

    /*
    |--------------------------------------------------------------------------
    | Context Labels
    |--------------------------------------------------------------------------
    */

    'context_labels' => [
        'patient' => 'Fiche Patient',
        'appointment' => 'Rendez-vous',
        'consultation' => 'Consultation',
        'invoice' => 'Facture',
    ],

];
