<?php

namespace App\Http\Controllers\GhlController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Http\Controllers\GhlController\Services\GHLRecordService;
use App\Http\Controllers\GhlController\Jobs\ProcessGHLRecord;
use App\Http\Controllers\GhlController\GhlConfig;
use App\Models\GoHighLevelToken;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhook data from external sources
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Get all incoming data
            $data = $request->all();
            
            // Log entire request data to a specific file as requested
            $webhookLogFile = storage_path('logs/webhook_receive_full_data.log');
            $logEntry = "=== WEBHOOK RECEIVED AT " . now()->toDateTimeString() . " ===\n";
            $logEntry .= "METHOD: " . $request->method() . " | URL: " . $request->fullUrl() . " | IP: " . $request->ip() . "\n";
            $logEntry .= "HEADERS:\n" . json_encode($request->headers->all(), JSON_PRETTY_PRINT) . "\n";
            $logEntry .= "PAYLOAD (Parsed):\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            $logEntry .= "RAW BODY:\n" . $request->getContent() . "\n";
            $logEntry .= "========================================================================\n\n";
            file_put_contents($webhookLogFile, $logEntry, FILE_APPEND);
            
            // Log the incoming webhook data for debugging
            Log::info('Webhook received:', $data);
            
            // Extract custom data, checking both camelCase and lowercase
            $customData = $data['customData'] ?? $data['customdata'] ?? [];
            
            // Proceed if we have customData or at least a root email
            if (!empty($customData) || !empty($data['email'])) {
                // Extract the data with fallback to root level
                $event = $customData['event'] ?? 'contact.created';
                $ghlContactId = $customData['ghl_contact_id'] ?? $data['contact_id'] ?? null;
                $email = $customData['email'] ?? $data['email'] ?? null;
                $firstName = $customData['firstName'] ?? $data['first_name'] ?? null;
                $lastName = $customData['lastName'] ?? $data['last_name'] ?? null;
                $phone = $customData['phone'] ?? $data['phone'] ?? null;
                $examTitle = $customData['exam_title'] ?? null;
                $examDescription = $customData['exam_description'] ?? null;
                $purchaseDate = $customData['purchase_date'] ?? null;
                
                // If first/last name are empty but full_name exists
                if (empty($firstName) && empty($lastName) && !empty($data['full_name'])) {
                    $nameParts = explode(' ', $data['full_name'], 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';
                }
                
                // Log extracted data
                Log::info('Extracted webhook data:', [
                    'event' => $event,
                    'ghl_contact_id' => $ghlContactId,
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'phone' => $phone,
                    'exam_title' => $examTitle,
                    'exam_description' => $examDescription,
                    'purchase_date' => $purchaseDate,
                ]);
                
                // Validate required fields
                if (empty($email) || empty($firstName) || empty($lastName) || empty($phone)) {
                    Log::warning('Webhook validation failed: Missing required fields');
                    return response()->json([
                        'success' => false,
                        'message' => 'Missing required fields: email, firstName, lastName, and phone are required'
                    ], 400);
                }
                
                // Handle contact.created event
                if ($event === 'contact.created') {
                    // Check if user exists or create new one
                    $user = User::where('email', $email)->first();
                    $isNewUser = false;
                    $generatedPassword = null;

                    if (!$user) {
                        $isNewUser = true;
                        // Generate password in format: firstName@randomnumber
                        $randomNumber = rand(100000, 999999); // 6-digit random number
                        $generatedPassword = $firstName . '@' . $randomNumber;

                        $user = User::create([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'phone' => $phone,
                            'password' => Hash::make($generatedPassword),
                            'role' => 'student', // Default role
                            'status' => 1, // 1 = active, 0 = inactive
                            'is_blocked' => false,
                            'purchase_date' => $purchaseDate ? date('Y-m-d', strtotime($purchaseDate)) : null,
                        ]);

                        Log::info('New user created successfully:', [
                            'user_id' => $user->id,
                            'email' => $email,
                            'generated_password' => $generatedPassword // Log for debugging (remove in production)
                        ]);
                    } else {
                        // Update purchase date if provided
                        if ($purchaseDate) {
                            $user->update(['purchase_date' => date('Y-m-d', strtotime($purchaseDate))]);
                        }

                        Log::info('Existing user found:', [
                            'user_id' => $user->id,
                            'email' => $email
                        ]);
                    }

                    // Assign Exam if provided
                    $examAssignmentStatus = 'Not provided';
                    $examAssignmentMessage = null;

                    if (!empty($examTitle)) {
                        $exam = Exam::where('name', $examTitle)->first();
                        
                        // Check if exam exists and is active (status 1) AND published (is_active 1)
                        if ($exam && $exam->status == 1 && $exam->is_active == 1) {
                            $existingStudentExam = StudentExam::where('student_id', $user->id)
                                ->where('exam_id', $exam->id)
                                ->first();

                            if ($existingStudentExam) {
                                // Just increase the attempts by 3
                                $existingStudentExam->increment('attempts_allowed', 3);
                                $examAssignmentStatus = 'Updated';
                                $examAssignmentMessage = "Existing assignment found. Increased attempts by 3 for '{$exam->name}'.";
                                
                                Log::info('Exam attempts increased for existing assignment:', [
                                    'user_id' => $user->id,
                                    'exam_name' => $exam->name,
                                    'new_attempts_allowed' => $existingStudentExam->attempts_allowed
                                ]);

                                // SYNC TO GHL EXAMS OBJECT
                                $this->syncExamOverviewToGHL($user, $exam, $existingStudentExam, $purchaseDate);
                            } else {
                                $newStudentExam = StudentExam::create([
                                    'student_id' => $user->id, // Assign to the student
                                    'exam_id' => $exam->id,
                                    'expiry_date' => now()->addWeeks(4), // 4 weeks from now
                                    'attempts_allowed' => 3, // 3 attempts
                                    'attempts_used' => 0,
                                    'source' => 'Webhook',
                                    'status' => 1
                                ]);
                                
                                $examAssignmentStatus = 'Assigned';
                                $examAssignmentMessage = "Successfully assigned new exam '{$exam->name}'.";

                                Log::info('Exam assigned successfully:', [
                                    'user_id' => $user->id,
                                    'exam_name' => $exam->name
                                ]);

                                // SYNC TO GHL EXAMS OBJECT
                                $this->syncExamOverviewToGHL($user, $exam, $newStudentExam, $purchaseDate);
                            }
                        } else {
                            $examAssignmentStatus = 'Failed';
                            if (!$exam) {
                                $examAssignmentMessage = "Exam '{$examTitle}' not found.";
                            } elseif ($exam->status != 1) {
                                $examAssignmentMessage = "Exam '{$examTitle}' is deleted.";
                            } elseif ($exam->is_active != 1) {
                                $examAssignmentMessage = "Exam '{$examTitle}' is unpublished (Draft mode).";
                            }
                            
                            Log::warning('Exam assignment passed: ' . $examAssignmentMessage, [
                                'exam_title' => $examTitle
                            ]);
                        }
                    }
                    
                    if ($isNewUser) {
                        // Generate password reset token
                        $token = \Illuminate\Support\Facades\Password::createToken($user);
                        
                        // Generate reset URL
                        $resetUrl = url(route('password.reset', [
                            'token' => $token,
                            'email' => $user->email,
                            ], false));
                        
                        // Send welcome email
                        try {
                            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\NewUserWelcomeMail($user, $generatedPassword, $resetUrl));
                            Log::info('Welcome email sent to: ' . $email);
                        } catch (\Exception $e) {
                            Log::error('Failed to send welcome email: ' . $e->getMessage());
                        }
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Webhook processed successfully',
                        'received_data' => $data,
                        'user_details' => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'is_new_user' => $isNewUser,
                            'password' => $generatedPassword, // Only if new
                            'exam_assignment' => [
                                'status' => $examAssignmentStatus,
                                'message' => $examAssignmentMessage
                            ]
                        ]
                    ], 200);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully (Event not handled or fallthrough)',
                    'received_data' => $data,
                     // Add debug info to understand why it fell through
                    'debug_event_received' => $event
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook data format'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle exam completion webhook and create record in GHL Custom Object
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleExamCompletion(Request $request)
    {
        try {
            // Get all incoming data
            $data = $request->all();
            
            // Log the incoming webhook data for debugging
            Log::info('Exam completion webhook received:', $data);
            
            // Validate required fields
            $requiredFields = ['name', 'email', 'exam_name'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                Log::warning('Exam completion webhook validation failed: Missing required fields', [
                    'missing_fields' => $missingFields
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                    'required_fields' => $requiredFields
                ], 400);
            }
            
            // Prepare the record data for GHL
            $recordData = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'total_score' => $data['total_score'] ?? 0,
                'attempts' => $data['attempts'] ?? 1,
                'status' => $data['status'] ?? '',
                'exam_name' => $data['exam_name'] ?? '',
            ];
            
            Log::info('Prepared record data for GHL:', $recordData);
            
            // Dispatch background job for GHL record creation
            $locationId = $data['location_id'] ?? null;
            ProcessGHLRecord::dispatch($recordData, $locationId);
            
            return response()->json([
                'success' => true,
                'message' => 'Exam completion received and queued for GHL'
            ], 200);
            
        } catch (\Throwable $e) {
            Log::error('Exam completion webhook processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing exam completion webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync Exam Overview to GHL Exams Object
     */
    protected function syncExamOverviewToGHL($user, $exam, $studentExam, $purchaseDate = null)
    {
        try {
            // Get location ID from the first available token
            $token = GoHighLevelToken::first();
            $locationId = $token ? $token->location_id : null;

            if (!$locationId) {
                Log::warning('Cannot sync to GHL: No location ID found in tokens table.');
                return;
            }

            $examPayload = [
                "name" => $user->first_name . ' ' . $user->last_name,
                "email" => $user->email,
                "exam_code" => $exam->exam_code,
                "exam_name" => $exam->name,
                "expiration_date" => $studentExam->expiry_date ? $studentExam->expiry_date->format('Y-m-d') : null,
                "purchase_date" => $purchaseDate,
                "total_attempts" => $studentExam->attempts_allowed,
            ];

            Log::info('Syncing Exam Assignment to GHL:', $examPayload);
            
            // Dispatch to GHL Synchronously to ensure immediate update
            ProcessGHLRecord::dispatchSync($examPayload, GhlConfig::OBJECT_KEY_EXAMS, $locationId);

            Log::info('GHL Sync Job Finished in WebhookController');

        } catch (\Exception $e) {
            Log::error('Error syncing exam to GHL: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
