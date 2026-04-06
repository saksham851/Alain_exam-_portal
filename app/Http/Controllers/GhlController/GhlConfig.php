<?php

namespace App\Http\Controllers\GhlController;

class GhlConfig
{
    public const VERSION_ID = '6981c39178b42f8b4e29f060';

    public const APP_URL = 'https://app.gohighlevel.com/v2/location/';

    public const AUTH_URL = 'https://marketplace.gohighlevel.com/oauth/chooselocation';

    public const TOKEN_URL = 'https://services.leadconnectorhq.com/oauth/token';
    public const SCOPES = [
        'contacts.write',
        'contacts.readonly',
        'objects/schema.write',
        'objects/schema.readonly',
        'objects/record.write',
        'objects/record.readonly',
        'locations.readonly',
        'locations/customFields.readonly',
        'locations/customFields.write'
    ];

    public const DEFAULTS = [
        'response_type' => 'code',
        'grant_type' => 'authorization_code',
        'user_type' => 'Location',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ];

    public const API_BASE_URL = 'https://services.leadconnectorhq.com';

    public const API_VERSION = '2021-07-28';

    public const OBJECT_KEY = 'custom_objects.exam_portal_result';

    public const ENDPOINTS = [
        'objects' => '/objects/',
        'custom_fields' => '/custom-fields/',
        'custom_folders' => '/custom-fields/folder',
        'custom_menus' => '/locations/{locationId}/custom-menus',
    ];
}
