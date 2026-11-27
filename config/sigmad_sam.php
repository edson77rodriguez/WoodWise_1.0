<?php
/** SAM interactivo usa el MISMO servidor/ngrok y token del V0.9U. */
return [
    'enabled' => filter_var(env('SIGMAD_SAM_INTERACTIVE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) env('AI_API_URL', ''), '/'),
    'token' => (string) env('AI_V09U_SHARED_TOKEN', ''),
];
