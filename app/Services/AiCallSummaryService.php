<?php

namespace App\Services;

use App\Models\CallLog;
use OpenAI;
use RuntimeException;

class AiCallSummaryService
{
    public function summarizeCall(CallLog $callLog): string
    {
        $apiKey = (string) config('services.openai.api_key');
        $model = (string) config('services.openai.model', 'gpt-4o-mini');

        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API key is missing. Add OPENAI_API_KEY to your .env file.');
        }

        $client = OpenAI::client($apiKey);

        $transcript = (string) ($callLog->transcript ?? '');
        $transcript = mb_substr($transcript, 0, 8000);

        $prompt = $this->buildUserPrompt($callLog, $transcript);

        $response = $client->chat()->create([
            'model' => $model,
            'temperature' => 0.3,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a QA call-coaching assistant. Summarize employee calls clearly, factually, and briefly for managers.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        $summary = trim((string) ($response->choices[0]->message->content ?? ''));

        if ($summary === '') {
            throw new RuntimeException('OpenAI returned an empty summary.');
        }

        return $summary;
    }

    private function buildUserPrompt(CallLog $callLog, string $transcript): string
    {
        if (trim($transcript) === '') {
            return 'Transcript is not available. Respond with exactly: "Transcript not available for this call."';
        }

        return <<<PROMPT
Summarize the following call transcript in 2 to 3 short sentences only.
Focus strictly on what happened in the transcript (customer intent, key action taken, and final outcome).
Do not use bullet points, headings, labels, coaching feedback, or extra commentary.
Keep the wording simple and concise.

Transcript:
{$transcript}
PROMPT;
    }
}

