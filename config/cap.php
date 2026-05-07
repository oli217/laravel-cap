<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Endpoint de l'instance Cap
    |--------------------------------------------------------------------------
    |
    | L'URL complète de votre instance Cap auto-hébergée, incluant le site-key.
    | Exemple : https://cap.example.com/votre-site-key/
    |
    */
    'endpoint' => env('CAP_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | Clé secrète
    |--------------------------------------------------------------------------
    |
    | La clé secrète générée dans votre tableau de bord Cap.
    | Ne jamais exposer cette valeur côté client.
    |
    */
    'secret' => env('CAP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Nom du champ token
    |--------------------------------------------------------------------------
    |
    | Le nom du champ hidden injecté automatiquement par le widget Cap
    | dans le formulaire parent. Modifiable via l'attribut data-cap-hidden-field-name.
    |
    */
    'token_field' => env('CAP_TOKEN_FIELD', 'cap-token'),

    /*
    |--------------------------------------------------------------------------
    | Timeout de vérification
    |--------------------------------------------------------------------------
    |
    | Délai (en secondes) avant abandon de la requête vers /siteverify.
    |
    */
    'timeout' => (int) env('CAP_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Mode fail-open
    |--------------------------------------------------------------------------
    |
    | Quand true, toute erreur de communication vers l'instance Cap (réseau,
    | timeout, erreur serveur 5xx) laisse passer la requête plutôt que de la
    | bloquer. Un token explicitement invalide (success: false) est toujours
    | refusé, quel que soit ce paramètre.
    |
    | Recommandé en production si la disponibilité du service prime sur la
    | sécurité anti-spam.
    |
    */
    'fail_open' => (bool) env('CAP_FAIL_OPEN', false),
];
