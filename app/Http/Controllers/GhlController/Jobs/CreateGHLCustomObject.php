<?php

namespace App\Http\Controllers\GhlController\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GhlController\Services\GHLTokenService;
use App\Http\Controllers\GhlController\GhlConfig;

class CreateGHLCustomObject implements ShouldQueue
{
    use Queueable;

    protected $token;

    /**
     * Create a new job instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(GHLTokenService $tokenService): void
    {
        // Refresh token if needed
        $accessToken = $tokenService->getAccessToken($this->token->location_id);

        if (!$accessToken) {
            Log::error("Failed to retrieve valid access token for location: {$this->token->location_id} in CreateGHLCustomObject job.");
            return;
        }

        $locationId = $this->token->location_id;
        $version = GhlConfig::API_VERSION;

        // 1. Create Exams Object
        $this->setupObject($accessToken, $locationId, GhlConfig::OBJECT_KEY_EXAMS, [
            'singular' => 'Exam',
            'plural' => 'Exams',
            'description' => 'Stores assigned exams and their overall status for students',
            'fields' => [
                ['name' => 'Email', 'dataType' => 'EMAIL', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".email", 'description' => 'Student email'],
                ['name' => 'Exam Code', 'dataType' => 'TEXT', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".exam_code", 'description' => 'Code of the exam'],
                ['name' => 'Exam Name', 'dataType' => 'TEXT', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".exam_name", 'description' => 'Name of the exam'],
                ['name' => 'Expiration Date', 'dataType' => 'DATE', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".expiration_date", 'description' => 'Exam validity date'],
                ['name' => 'Purchase Date', 'dataType' => 'TEXT', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".purchase_date", 'description' => 'Date of purchase', 'isUnique' => false],
                ['name' => 'Total Attempts', 'dataType' => 'NUMERICAL', 'fieldKey' => GhlConfig::OBJECT_KEY_EXAMS . ".total_attempts", 'description' => 'Total attempts used'],
            ],
            'display_fields' => [
                GhlConfig::OBJECT_KEY_EXAMS . ".name",
                GhlConfig::OBJECT_KEY_EXAMS . ".email",
                GhlConfig::OBJECT_KEY_EXAMS . ".exam_code",
                GhlConfig::OBJECT_KEY_EXAMS . ".purchase_date",
                GhlConfig::OBJECT_KEY_EXAMS . ".total_attempts",
            ]
        ], $version);

        // 2. Create Exam Tracker Object
        $this->setupObject($accessToken, $locationId, GhlConfig::OBJECT_KEY_TRACKER, [
            'singular' => 'Exam Attempt',
            'plural' => 'Exam Attempts',
            'description' => 'Stores individual exam attempt details',
            'fields' => [
                ['name' => 'Email', 'dataType' => 'EMAIL', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".email", 'description' => 'Student email', 'isUnique' => false],
                ['name' => 'Exam Code', 'dataType' => 'TEXT', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".exam_code", 'description' => 'Associated exam code'],
                ['name' => 'Attempt', 'dataType' => 'NUMERICAL', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".attempt", 'description' => 'Attempt number'],
                ['name' => 'Result', 'dataType' => 'TEXT', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".result", 'description' => 'Pass/Fail'],
                ['name' => 'Earned Points', 'dataType' => 'NUMERICAL', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".earned_points", 'description' => 'Total score'],
                ['name' => 'Exam Length', 'dataType' => 'NUMERICAL', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".exam_length", 'description' => 'Max time in minutes'],
                ['name' => 'Duration', 'dataType' => 'NUMERICAL', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".duration", 'description' => 'Total time taken in minutes'],
                ['name' => 'Submission Date', 'dataType' => 'DATE', 'fieldKey' => GhlConfig::OBJECT_KEY_TRACKER . ".submission_date", 'description' => 'Date of submission'],
            ],
            'display_fields' => [
                GhlConfig::OBJECT_KEY_TRACKER . ".name",
                GhlConfig::OBJECT_KEY_TRACKER . ".email",
                GhlConfig::OBJECT_KEY_TRACKER . ".result",
                GhlConfig::OBJECT_KEY_TRACKER . ".earned_points",
            ]
        ], $version);

        // 3. Create Menu Link
        Log::info("Now creating menu link...");
        $this->createMenuLink($accessToken, $locationId, $version);
    }

    /**
     * Generalized setup for a Custom Object
     */
    protected function setupObject($accessToken, $locationId, $objectKey, $config, $version)
    {
        $baseUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'];

        // Base Schema Definition
        $labels = [
            'singular' => $config['singular'],
            'plural' => $config['plural'],
        ];

        // Primary Property (Used in Create)
        $primaryPropDetails = [
            'key' => "{$objectKey}.name",
            'name' => 'Name',
            'dataType' => 'TEXT',
        ];

        // 1. Create Custom Object (POST)
        $createBody = [
            'labels' => $labels,
            'key' => $objectKey,
            'description' => $config['description'],
            'locationId' => $locationId,
            'primaryDisplayPropertyDetails' => $primaryPropDetails,
        ];

        Log::info("Attempting to create Custom Object: {$objectKey} for location: {$locationId}");

        $createResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Version' => $version,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($baseUrl, $createBody);

        $finalObjectKey = $objectKey;

        if ($createResponse->successful()) {
            Log::info("Custom Object '{$objectKey}' Created Successfully");
        } else {
            Log::warning("Custom Object '{$objectKey}' setup info: " . $createResponse->body());
        }

        // 2. Create Custom Folder
        $folderId = $this->createCustomFolder($accessToken, $locationId, $finalObjectKey, $config['singular'] . " Fields", $version);

        // 3. Create Custom Fields (Proceed even if folder creation failed or folder exists)
        $this->createCustomFields($accessToken, $locationId, $finalObjectKey, $folderId, $config['fields'], $version);

        // 4. Configure Table View
        $this->configureTableView($accessToken, $locationId, $finalObjectKey, $config['display_fields'], $version);

        Log::info("Finished setup for Object: {$objectKey}");
    }

    /**
     * Create a custom folder for the custom object
     */
    protected function createCustomFolder($accessToken, $locationId, $objectKey, $folderName, $version)
    {
        Log::info("Parameters - LocationId: {$locationId}, ObjectKey: {$objectKey}");

        $endpoint = str_replace('{locationId}', $locationId, GhlConfig::ENDPOINTS['custom_folders']);
        $folderUrl = GhlConfig::API_BASE_URL . $endpoint;

        $folderBody = [
            'objectKey' => $objectKey,
            'name' => $folderName,
            'locationId' => $locationId
        ];

        Log::info("Folder API URL: {$folderUrl}");
        Log::info("Folder Request Body: " . json_encode($folderBody));
        Log::info("Attempting to create Custom Folder for object: {$objectKey}");

        $folderResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Version' => $version,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($folderUrl, $folderBody);

        Log::info("Folder API Response Status: {$folderResponse->status()}");
        Log::info("Folder API Response Body: " . $folderResponse->body());

        if ($folderResponse->successful()) {
            $folderData = $folderResponse->json();
            Log::info("Custom Folder Created Successfully: " . json_encode($folderData));

            // Extract folder ID if available
            $folderId = $folderData['folder']['id'] ?? $folderData['id'] ?? 'unknown';
            Log::info("Folder ID: {$folderId}");

            return $folderId;
        } else {
            Log::error("Failed to create Custom Folder. Status {$folderResponse->status()}: " . $folderResponse->body());
            return null;
        }
    }

    /**
     * Create custom fields in the folder
     */
    protected function createCustomFields($accessToken, $locationId, $objectKey, $folderId, $fields, $version)
    {
        Log::info("Creating fields for object: {$objectKey} in folder: {$folderId}");

        $endpoint = str_replace('{locationId}', $locationId, GhlConfig::ENDPOINTS['custom_fields']);
        $fieldsUrl = GhlConfig::API_BASE_URL . $endpoint;

        // Fields are now passed as parameter

        $successCount = 0;
        $failCount = 0;

        foreach ($fields as $index => $field) {
            $fieldBody = [
                'locationId' => $locationId,
                'name' => $field['name'],
                'description' => $field['description'] ?? '',
                'placeholder' => $field['placeholder'] ?? 'Enter ' . $field['name'],
                'showInForms' => true,
                'dataType' => $field['dataType'],
                'fieldKey' => $field['fieldKey'],
                'objectKey' => $objectKey,
                'parentId' => $folderId,
                'position' => $index + 1, // Set position for ordering
                'isUnique' => $field['isUnique'] ?? false,
            ];

            Log::info("Creating field: {$field['name']} with key: {$field['fieldKey']}");
            Log::info("Field Request Body: " . json_encode($fieldBody));

            $fieldResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($fieldsUrl, $fieldBody);

            if ($fieldResponse->successful()) {
                $successCount++;
                $responseData = $fieldResponse->json();
                Log::info("✓ Field '{$field['name']}' created/verified successfully. Response: " . $fieldResponse->body());

                // Store field ID for later use in table configuration
                $field['id'] = $responseData['id'] ?? $responseData['customField']['id'] ?? null;
            } else {
                $failCount++;
                Log::warning("✗ Field '{$field['name']}' creation info (might already exist). Status {$fieldResponse->status()}: " . $fieldResponse->body());
            }

            // Small delay between field creations
            usleep(300000); // 300ms delay
        }

        Log::info("=== CUSTOM FIELDS CREATION COMPLETE ===");
        Log::info("Success: {$successCount}, Failed: {$failCount}");

    }

    /**
     * Configure table view to show custom fields by default
     */
    protected function configureTableView($accessToken, $locationId, $objectKey, $displayProperties, $version)
    {
        Log::info("=== CONFIGURING TABLE VIEW ===");

        // Update the object schema to include the fields in default view
        $schemaUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey;

        $schemaBody = [
            'locationId' => $locationId,
            'displayProperties' => $displayProperties,
        ];

        Log::info("Schema Update URL: {$schemaUrl}");
        Log::info("Schema Update Body: " . json_encode($schemaBody));

        $schemaResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Version' => $version,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->patch($schemaUrl, $schemaBody);

        if ($schemaResponse->successful()) {
            Log::info("✓ Table view configured successfully. Response: " . $schemaResponse->body());
        } else {
            Log::warning("Table view configuration returned status {$schemaResponse->status()}: " . $schemaResponse->body());
            Log::info("Fields are created but may need manual configuration in GHL UI.");
        }
    }

    /**
     * Create a menu link in GoHighLevel
     */
    protected function createMenuLink($accessToken, $locationId, $version)
    {
        Log::info("=== CREATING MENU LINK ===");

        // Construct the custom menu URL (correct endpoint from GHL API docs)
        $endpoint = str_replace('{locationId}', $locationId, GhlConfig::ENDPOINTS['custom_menus']);
        $menuUrl = GhlConfig::API_BASE_URL . $endpoint;

        // Get the exam portal URL from environment or use placeholder
        $examPortalUrl = env('EXAM_PORTAL_URL', 'https://example.com');

        // Request body according to official GHL API documentation
        $menuLinkBody = [
            'title' => 'Exam Portal',
            'url' => $examPortalUrl,
            'icon' => [
                'name' => 'graduation-cap', // FontAwesome icon for education/exam
                'fontFamily' => 'fas' // Font Awesome Solid
            ],
            'showOnCompany' => true, // Show on agency level
            'showOnLocation' => true, // Show on sub-account level
            'showToAllLocations' => true, // Show to all sub-accounts
            'locations' => [], // Empty array since showToAllLocations is true
            'openMode' => 'new_tab', // Options: 'new_tab', 'iframe', 'current_tab'
            'userRole' => 'all', // Which user roles can see this
            'allowCamera' => false, // Only for iframe mode
            'allowMicrophone' => false, // Only for iframe mode
        ];

        Log::info("Menu Link API URL: {$menuUrl}");
        Log::info("Menu Link Request Body: " . json_encode($menuLinkBody));
        Log::info("Attempting to create custom menu: Exam Portal");

        $menuLinkResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Version' => $version,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($menuUrl, $menuLinkBody);

        Log::info("Menu Link API Response Status: {$menuLinkResponse->status()}");
        Log::info("Menu Link API Response Body: " . $menuLinkResponse->body());

        if ($menuLinkResponse->successful()) {
            $menuLinkData = $menuLinkResponse->json();
            Log::info("✓ Custom Menu 'Exam Portal' created successfully: " . json_encode($menuLinkData));

            // Extract menu link ID if available for future reference
            $menuLinkId = $menuLinkData['id'] ?? $menuLinkData['customMenu']['id'] ?? 'unknown';
            Log::info("Custom Menu ID: {$menuLinkId}");

            return $menuLinkId;
        } else {
            Log::error("✗ Failed to create Custom Menu. Status {$menuLinkResponse->status()}: " . $menuLinkResponse->body());
            return null;
        }
    }
}
