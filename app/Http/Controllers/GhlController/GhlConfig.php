<?php

namespace App\Http\Controllers\GhlController;

class GhlConfig
{
    public const VERSION_ID = '693a42c5c515cfdbdf604462';
    
    public const APP_URL = 'https://app.gohighlevel.com/v2/location/';
    
    public const AUTH_URL = 'https://marketplace.gohighlevel.com/oauth/chooselocation';
    
    public const TOKEN_URL = 'https://services.leadconnectorhq.com/oauth/token';

    public const SCOPES = [
        'businesses.readonly',
        'businesses.write',
        'contacts.readonly',
        'contacts.write',
        'objects/schema.readonly',
        'objects/schema.write',
        'objects/record.readonly',
        'objects/record.write',
        'associations.write',
        'associations.readonly',
        'associations/relation.readonly',
        'associations/relation.write',
        'locations/customValues.readonly',
        'locations.readonly',
        'locations/customFields.readonly',
        'locations/customFields.write',
        'locations/customValues.write',
        'locations/tasks.readonly',
        'locations/tasks.write',
        'locations/templates.readonly',
        'locations/tags.write',
        'locations/tags.readonly',
        // 'custom-menu-link.write'
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
        'custom_menus' => '/custom-menus',
    ];
}
