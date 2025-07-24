<?php

namespace A17\Twill\Http\Controllers\Admin;

use Alexstericris\AlecrisAiApis\Services\ApiResponseParser;
use Alexstericris\AlecrisAiApis\Services\GeminiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    use Concerns\FormSubmitOptions;

    public function prompt(Request $request, GeminiService $geminiService, ApiResponseParser $parser)
    {
        $response = $geminiService->prompt([
            'contents' => [
                [
                    "role" => "user",
                    'parts' => [
                        ['text' => $request->get('prompt')],
                    ]
                ],
            ]
        ]);
        $candidates = $response->json('candidates');
        foreach ($candidates as $candidate) {
            foreach ($candidate['content']['parts'] as $part) {
                return $parser->parseTailwindClass($part['text']);
            }
        }
        return '';
    }
}
