<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTasksFromTopicJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public AiGeneration $generation) {}

    public function handle(GeminiService $gemini): void
    {
        $this->generation->update(['status' => 'processing']);

        try {
            $result = $gemini->breakTopicIntoTasks($this->generation->topic);

            $this->generation->update([
                'status' => 'completed',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            $this->generation->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
