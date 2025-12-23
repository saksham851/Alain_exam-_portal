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
        $baseUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'];
        $objectKey = GhlConfig::OBJECT_KEY;
        $version = GhlConfig::API_VERSION;

        // Base Schema Definition
        $labels = [
            'singular' => 'Exam Portal Result',
            'plural' => 'Exam Portal Results',
        ];
        $description = 'Stores exam results from the portal';
        
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
            'description' => $description,
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
            $responseData = $createResponse->json();
            Log::info("Custom Object Created Successfully: " . json_encode($responseData));

            // Try to get the actual key assigned by GHL
            if (isset($responseData['key'])) {
                $finalObjectKey = $responseData['key'];
            } else if (isset($responseData['data']['key'])) {
                 $finalObjectKey = $responseData['data']['key'];
            } else if (isset($responseData['objectKey'])) {
                 $finalObjectKey = $responseData['objectKey'];
            } else if (isset($responseData['data']['objectKey'])) {
                 $finalObjectKey = $responseData['data']['objectKey'];
            }
            
            Log::info("Custom Object Created. Final Key: {$finalObjectKey}");
            
        } else {
            Log::warning("Custom Object creation returned status {$createResponse->status()}. Response: " . $createResponse->body());
            Log::info("Object may already exist. Proceeding with folder creation using key: {$finalObjectKey}");
        }

        // Wait a bit for the object to be fully registered
        Log::info("Waiting 2 seconds before folder creation...");
        sleep(2);
        
        // 2. Create Custom Folder for the Custom Object (Always attempt, even if object already exists)
        Log::info("Now calling createCustomFolder method...");
        $folderId = $this->createCustomFolder($accessToken, $locationId, $finalObjectKey, $version);
        
        // 3. Create Custom Fields in the Folder
        if ($folderId) {
            Log::info("Folder created successfully with ID: {$folderId}. Now creating custom fields...");
            sleep(1); // Small delay to ensure folder is registered
            $this->createCustomFields($accessToken, $locationId, $finalObjectKey, $folderId, $version);
        } else {
            Log::error("Folder ID not available. Skipping custom fields creation.");
        }

        // 4. Create Menu Link
        Log::info("Now creating menu link...");
        $this->createMenuLink($accessToken, $locationId, $version);
    }

    /**
     * Create a custom folder for the custom object
     */
    protected function createCustomFolder($accessToken, $locationId, $objectKey, $version)
    {
        Log::info("Parameters - LocationId: {$locationId}, ObjectKey: {$objectKey}");
        
        $folderUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['custom_folders'];
        
        $folderBody = [
            'objectKey' => $objectKey,
            'name' => 'Exam Portal Fields',
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
    protected function createCustomFields($accessToken, $locationId, $objectKey, $folderId, $version)
    {
        Log::info("Creating fields for object: {$objectKey} in folder: {$folderId}");
        
        $fieldsUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['custom_fields'];
        
        // Define all custom fields
        $fields = [

            [
                'name' => 'Email',
                'dataType' => 'EMAIL',
                'fieldKey' => "{$objectKey}.email",
                'description' => 'Email address',
                'placeholder' => 'Enter email',
            ],
            [
                'name' => 'Phone',
                'dataType' => 'PHONE',
                'fieldKey' => "{$objectKey}.phone",
                'description' => 'Phone number',
                'placeholder' => 'Enter phone number',
            ],
            [
                'name' => 'IG Score',
                'dataType' => 'NUMERICAL',
                'fieldKey' => "{$objectKey}.ig_score",
                'description' => 'IG Score',
                'placeholder' => 'Enter IG score',
            ],
            [
                'name' => 'DM Score',
                'dataType' => 'NUMERICAL',
                'fieldKey' => "{$objectKey}.dm_score",
                'description' => 'DM Score',
                'placeholder' => 'Enter DM score',
            ],
            [
                'name' => 'Total Score',
                'dataType' => 'NUMERICAL',
                'fieldKey' => "{$objectKey}.total_score",
                'description' => 'Total Score',
                'placeholder' => 'Enter total score',
            ],
            [
                'name' => 'Attempts',
                'dataType' => 'NUMERICAL',
                'fieldKey' => "{$objectKey}.attempts",
                'description' => 'Number of attempts',
                'placeholder' => 'Enter attempts',
            ],
            [
                'name' => 'Status',
                'dataType' => 'TEXT',
                'fieldKey' => "{$objectKey}.status",
                'description' => 'Exam status (Pass/Fail)',
                'placeholder' => 'Enter status',
            ],
            [
                'name' => 'Exam Name',
                'dataType' => 'TEXT',
                'fieldKey' => "{$objectKey}.exam_name",
                'description' => 'Name of the exam',
                'placeholder' => 'Enter exam name',
            ],
        ];

        $successCount = 0;
        $failCount = 0;

        foreach ($fields as $index => $field) {
            $fieldBody = [
                'locationId' => $locationId,
                'name' => $field['name'],
                'description' => $field['description'],
                'placeholder' => $field['placeholder'],
                'showInForms' => true,
                'dataType' => $field['dataType'],
                'fieldKey' => $field['fieldKey'],
                'objectKey' => $objectKey,
                'parentId' => $folderId,
                'position' => $index + 1, // Set position for ordering
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
                Log::info("✓ Field '{$field['name']}' created successfully. Response: " . $fieldResponse->body());
                
                // Store field ID for later use in table configuration
                $field['id'] = $responseData['id'] ?? $responseData['customField']['id'] ?? null;
            } else {
                $failCount++;
                Log::error("✗ Failed to create field '{$field['name']}'. Status {$fieldResponse->status()}: " . $fieldResponse->body());
            }

            // Small delay between field creations
            usleep(300000); // 300ms delay
        }

        Log::info("=== CUSTOM FIELDS CREATION COMPLETE ===");
        Log::info("Success: {$successCount}, Failed: {$failCount}");
        
        // After creating all fields, configure the table view to show them by default
        if ($successCount > 0) {
            Log::info("Configuring table view to show fields by default...");
            sleep(1);
            $this->configureTableView($accessToken, $locationId, $objectKey, $version);
        }
    }

    /**
     * Configure table view to show custom fields by default
     */
    protected function configureTableView($accessToken, $locationId, $objectKey, $version)
    {
        Log::info("=== CONFIGURING TABLE VIEW ===");
        
        // Update the object schema to include the fields in default view
        $schemaUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey;
        
        $schemaBody = [
            'locationId' => $locationId,
            'displayProperties' => [
                "{$objectKey}.name",
                "{$objectKey}.email",
                "{$objectKey}.phone",
                "{$objectKey}.total_score",
                "{$objectKey}.status",
                "{$objectKey}.exam_name",
            ],
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
        $menuUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['custom_menus'];
        
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
