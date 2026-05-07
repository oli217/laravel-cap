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
];
