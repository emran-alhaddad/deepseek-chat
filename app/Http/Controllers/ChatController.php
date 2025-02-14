<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeepSeekService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    protected DeepSeekService $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    public function streamChat(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        return new StreamedResponse(function () use ($request) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo "retry: 500\n"; // Keeps SSE connection alive
            flush();

            $stream = $this->deepSeekService->streamResponse($request->message);

            foreach (explode("\n", $stream) as $chunk) {
                $decoded = json_decode(trim($chunk), true);

                if (isset($decoded['response']) && !empty($decoded['response'])) {
                    echo "data: " . $decoded['response'] . "\n\n";
                    flush();
                    usleep(50000); // Simulate real-time typing effect
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
