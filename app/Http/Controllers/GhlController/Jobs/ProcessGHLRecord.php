<?php

namespace App\Http\Controllers\GhlController\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\GhlController\Services\GHLRecordService;
use Illuminate\Support\Facades\Log;

class ProcessGHLRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $locationId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 30;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     * @param string|null $locationId
     */
    public function __construct(array $payload, ?string $locationId = null)
    {
        $this->payload = $payload;
        $this->locationId = $locationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GHLRecordService $ghlService)
    {
        Log::info('Processing GHL record job for: ' . ($this->payload['email'] ?? 'unknown'));

        $result = $ghlService->createRecord($this->payload, $this->locationId);

        if ($result['success']) {
            Log::info('GHL record processed successfully via job.');
        } else {
            Log::error('GHL record processing failed in job: ' . ($result['message'] ?? 'Unknown error'));
            
            // If it's a transient error (like 429 Rate Limit or 5xx), we might want to manually fail or let Laravel retry
            if (isset($result['status']) && ($result['status'] == 429 || $result['status'] >= 500)) {
                $this->fail(new \Exception('Transient GHL API error: ' . $result['message']));
            }
        }
    }
}
