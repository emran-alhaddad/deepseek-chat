<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeepSeekService
{
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiUrl = config('services.ollama.url', 'http://localhost:11434/api/generate');
        $this->model = config('services.ollama.model', 'deepseek-r1:1.5b');
    }

    public function streamResponse(string $message)
    {
        $response = Http::withOptions([
            'stream' => true, // Enable streaming
        ])->post($this->apiUrl, [
            'model' => $this->model,
            'prompt' => $message,
            'stream' => true // Ensure DeepSeek is in streaming mode
        ]);

        return $response->body(); // Returns the raw streaming response
    }
}
