<?php

namespace App\Http\Controllers\GhlController\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GoHighLevelToken;
// GHLTokenService is in the same namespace, so no import needed, but typehint requires it to be resolved.
// Since it's in the same namespace, `public function __construct(GHLTokenService $tokenService)` works.
use App\Http\Controllers\GhlController\GhlConfig;

class GHLRecordService
{
    /**
     * Create a record in GHL Custom Object
     * 
     * @param array $recordData
     * @param string $locationId (optional - will fetch from DB if not provided)
     * @return array
     */
    protected $tokenService;

    public function __construct(GHLTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Create a record in GHL Custom Object
     * 
     * @param array $recordData
     * @param string $locationId (optional - will fetch from DB if not provided)
     * @return array
     */
    public function createRecord(array $recordData, ?string $locationId = null)
    {
        try {
            // Get the access token and location ID
            if (!$locationId) {
                // If no location ID provided, try to find the latest valid token to get a location ID
                // This is a bit ambiguous if there are multiple locations. 
                // We'll mimic old behavior: get latest token.
                $token = GoHighLevelToken::latest()->first();
                if (!$token) {
                     Log::error('No GHL token found in database');
                     return ['success' => false, 'message' => 'No GHL token found'];
                }
                $locationId = $token->location_id;
            }

            $accessToken = $this->tokenService->getAccessToken($locationId);
            
            if (!$accessToken) {
                return [
                    'success' => false, 
                    'message' => 'Unable to retrieve valid access token'
                ];
            }

            $objectKey = GhlConfig::OBJECT_KEY;
            $version = GhlConfig::API_VERSION;
            $recordUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey . '/records';

            // Map the incoming data to GHL custom field keys
            $properties = $this->mapToCustomFields($recordData, $objectKey);

            Log::info('Mapped properties:', $properties);

            // Prepare the request body
            $requestBody = [
                'locationId' => $locationId,
                'properties' => $properties
            ];

            Log::info('Creating GHL record with data:', $requestBody);

            // Make the API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($recordUrl, $requestBody);

            // Handle 401 Unauthorized - Retry logic
            if ($response->status() === 401) {
                Log::warning("Received 401 from GHL. Forcing token refresh for location {$locationId} and retrying.");
                
                $accessToken = $this->tokenService->forceRefreshToken($locationId);
                
                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post($recordUrl, $requestBody);
                } else {
                    Log::error("Failed to force refresh token for location {$locationId}");
                    return [
                        'success' => false,
                        'message' => 'Authentication failed. Please re-connect GHL.'
                    ];
                }
            }

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('GHL record created successfully:', $responseData);
                
                return [
                    'success' => true,
                    'message' => 'Record created successfully',
                    'data' => $responseData
                ];
            } else {
            // Handle 400 Bad Request
            if ($response->status() === 400) {
                Log::error('Failed to create GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());
                
                return [
                    'success' => false,
                    'message' => 'Failed to create record in GHL',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }

                Log::error('Failed to create GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());
                
                return [
                    'success' => false,
                    'message' => 'Failed to create record in GHL',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while creating GHL record: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Map incoming exam data to GHL custom field keys
     * 
     * @param array $data
     * @param string $objectKey
     * @return array
     */
    protected function mapToCustomFields(array $data, string $objectKey)
    {
        $customFields = [];

        Log::info('mapToCustomFields - Input data:', $data);
        Log::info('mapToCustomFields - Object key:', ['objectKey' => $objectKey]);

        // Map each field to its corresponding GHL custom field key
        $fieldMapping = [
            'name' => 'name',
            'email' => 'email',
            'total_score' => 'total_score',
            'attempts' => 'attempts',
            'status' => 'status',
            'exam_name' => 'exam_name',
        ];

        foreach ($fieldMapping as $dataKey => $fieldKey) {
            if (isset($data[$dataKey]) && $data[$dataKey] !== null && $data[$dataKey] !== '') {
                $customFields[$fieldKey] = $data[$dataKey];
                Log::info("Mapped field: {$dataKey} => {$fieldKey}", ['value' => $customFields[$fieldKey]]);
            } else {
                Log::warning("Skipped field: {$dataKey}", [
                    'isset' => isset($data[$dataKey]),
                    'value' => $data[$dataKey] ?? 'not set',
                    'is_null' => ($data[$dataKey] ?? null) === null,
                    'is_empty' => ($data[$dataKey] ?? '') === ''
                ]);
            }
        }

        Log::info('mapToCustomFields - Output:', $customFields);

        return $customFields;
    }


    /**
     * Update an existing record in GHL Custom Object
     * 
     * @param string $recordId
     * @param array $recordData
     * @param string $locationId (optional)
     * @return array
     */
    public function updateRecord(string $recordId, array $recordData, ?string $locationId = null)
    {
        try {
            // Get the access token and location ID
            if (!$locationId) {
                $token = GoHighLevelToken::latest()->first();
                
                if (!$token) {
                    Log::error('No GHL token found in database');
                    return [
                        'success' => false,
                        'message' => 'No GHL token found'
                    ];
                }
                
                $locationId = $token->location_id;
            }

            $accessToken = $this->tokenService->getAccessToken($locationId);

            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Unable to retrieve valid access token'
                ];
            }

            $objectKey = GhlConfig::OBJECT_KEY;
            $version = GhlConfig::API_VERSION;
            $recordUrl = GhlConfig::API_BASE_URL . GhlConfig::ENDPOINTS['objects'] . $objectKey . '/records/' . $recordId;

            // Map the incoming data to GHL custom field keys
            $properties = $this->mapToCustomFields($recordData, $objectKey);

            // Prepare the request body - GHL API expects only 'properties'
            $requestBody = [
                'locationId' => $locationId,
                'properties' => $properties
            ];

            Log::info('Updating GHL record with data:', $requestBody);

            // Make the API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => $version,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($recordUrl, $requestBody);

            // Handle 401 Unauthorized - Retry logic
            if ($response->status() === 401) {
                Log::warning("Received 401 from GHL. Forcing token refresh for location {$locationId} and retrying.");
                
                $accessToken = $this->tokenService->forceRefreshToken($locationId);
                
                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->put($recordUrl, $requestBody);
                } else {
                    Log::error("Failed to force refresh token for location {$locationId}");
                    return [
                        'success' => false,
                        'message' => 'Authentication failed. Please re-connect GHL.'
                    ];
                }
            }

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('GHL record updated successfully:', $responseData);
                
                return [
                    'success' => true,
                    'message' => 'Record updated successfully',
                    'data' => $responseData
                ];
            } else {
                // Check for invalid phone number error and retry without phone
                $responseBody = $response->body();
                if ($response->status() === 400 && str_contains($responseBody, 'Invalid phone number')) {
                    Log::warning('GHL record update failed due to invalid phone. Retrying without phone.', [
                        'omitted_phone' => $properties['phone'] ?? 'unknown'
                    ]);
                    
                    // Remove phone from properties and request body
                    unset($properties['phone']);
                    $requestBody['properties'] = $properties;
                    
                    // Retry the request
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Version' => $version,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->put($recordUrl, $requestBody);
                    
                    if ($response->successful()) {
                        $responseData = $response->json();
                        Log::info('GHL record updated successfully (retry without phone):', $responseData);
                        
                        return [
                            'success' => true,
                            'message' => 'Record updated successfully (phone omitted)',
                            'data' => $responseData
                        ];
                    }
                }

                Log::error('Failed to update GHL record. Status: ' . $response->status() . ', Response: ' . $response->body());
                
                return [
                    'success' => false,
                    'message' => 'Failed to update record in GHL',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while updating GHL record: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }
}
