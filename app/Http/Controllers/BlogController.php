<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\Blog;

use App\Models\Category;

use App\Models\Author;
use App\Models\BlogReimage;

use App\Models\SiteContent;

use App\Models\User;

use App\Models\BlogImages;

use App\Models\BlogCategory;

use App\Models\Languages;

use App\Models\BlogTranslation;
use Log;
use App\Models\Vote;
use App\Models\Notification;
use App\Models\CustomNotification;

use App\Models\BlogViewCount;
use App\Models\BlogActionLog;

use App\Models\DeviceToken;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Facebook\Exceptions\FacebookSDKException as FacebookSDKException;

use Facebook\Facebook as Facebook;

use Illuminate\Support\Facades\Http;
use UploadImage as Image;

use File;

use Twitter;

use Auth;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Carbon\Carbon;





class BlogController extends Controller
{

    private $api;

    private $accessToken;

    private $pageToken;

    private $pageId;



    private function resolveOpenAiRewriteModel(): string
    {
        // Default to a fast, cost-effective model for interactive rewrites.
        $raw = (string) env('OPENAI_REWRITE_MODEL', 'gpt-4o-mini');
        $model = trim($raw) !== '' ? trim($raw) : (string) env('OPENAI_MODEL', 'gpt-4o-mini');

        $normalized = strtolower(trim($model));
        $aliases = [
            'gpt4o mini' => 'gpt-4o-mini',
            'gpt4o-mini' => 'gpt-4o-mini',
            'gpt-4o mini' => 'gpt-4o-mini',
            '4o-mini' => 'gpt-4o-mini',
            'chatgpt5 mini' => 'gpt-5-mini',
            'chatgpt-5-mini' => 'gpt-5-mini',
            'chatgpt5-mini' => 'gpt-5-mini',
            'gpt5 mini' => 'gpt-5-mini',
            'gpt5-mini' => 'gpt-5-mini',
            'gpt-5 mini' => 'gpt-5-mini',
        ];

        return $aliases[$normalized] ?? $model;
    }

    private function openAiRewriteMaxCompletionTokens(): int
    {
        $tokens = (int) env('OPENAI_REWRITE_MAX_COMPLETION_TOKENS', 500);
        if ($tokens <= 0) {
            $tokens = 500;
        }
        return $tokens;
    }

    private function openAiRewriteTimeoutSeconds(): int
    {
        $seconds = (int) env('OPENAI_REWRITE_TIMEOUT_SECONDS', 45);
        if ($seconds <= 0) {
            $seconds = 45;
        }
        return $seconds;
    }

    private function openAiRewriteConnectTimeoutSeconds(): int
    {
        $seconds = (int) env('OPENAI_REWRITE_CONNECT_TIMEOUT_SECONDS', 20);
        if ($seconds <= 0) {
            $seconds = 20;
        }
        return $seconds;
    }

    private function openAiRewriteRetries(): int
    {
        $retries = (int) env('OPENAI_REWRITE_RETRIES', 1);
        if ($retries < 0) {
            $retries = 0;
        }
        return min(3, $retries);
    }

    private function openAiRewriteForceIpv4(): bool
    {
        $raw = env('OPENAI_REWRITE_FORCE_IPV4', '1');
        return filter_var($raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function clampTemperature($value): float
    {
        $temp = (float) $value;
        if (!is_finite($temp)) {
            return 0.5;
        }
        return max(0.0, min(1.0, $temp));
    }

    private function countWords(string $text): int
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return 0;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return is_array($words) ? count($words) : 0;
    }

    private function clipToMaxWords(string $text, int $maxWords, bool $sentenceAware): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($maxWords <= 0 || $text === '')
            return $text;

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($words) || count($words) <= $maxWords) {
            return $sentenceAware ? $this->ensureCompleteSentences($text) : $text;
        }

        $clipped = implode(' ', array_slice($words, 0, $maxWords));
        if (!$sentenceAware)
            return trim($clipped);

        // Always end on a natural sentence boundary for descriptions.
        $punctuation = ['.', '!', '?', '।', '"', '”', '»', ')'];
        $bestPos = -1;
        foreach ($punctuation as $p) {
            $pos = function_exists('mb_strrpos') ? mb_strrpos($clipped, $p, 0, 'UTF-8') : strrpos($clipped, $p);
            if ($pos !== false && $pos > $bestPos)
                $bestPos = (int) $pos;
        }

        if ($bestPos !== -1) {
            $clipped = function_exists('mb_substr')
                ? mb_substr($clipped, 0, $bestPos + 1, 'UTF-8')
                : substr($clipped, 0, $bestPos + 1);
        } else {
            return $this->ensureCompleteSentences($clipped);
        }

        return trim($clipped);
    }

    private function ensureCompleteSentences(string $text): string
    {
        $text = trim($text);
        if ($text === '')
            return $text;

        $sentenceEnders = ['.', '!', '?', '।', '"', '”', '»', ')'];
        $lastChar = function_exists('mb_substr') ? mb_substr($text, -1, 1, 'UTF-8') : substr($text, -1);
        if (in_array($lastChar, $sentenceEnders, true))
            return $text;

        $bestPos = -1;
        foreach ($sentenceEnders as $p) {
            $pos = function_exists('mb_strrpos') ? mb_strrpos($text, $p, 0, 'UTF-8') : strrpos($text, $p);
            if ($pos !== false && $pos > $bestPos)
                $bestPos = (int) $pos;
        }

        if ($bestPos !== -1) {
            $text = function_exists('mb_substr') ? mb_substr($text, 0, $bestPos + 1, 'UTF-8') : substr($text, 0, $bestPos + 1);
        } elseif (strlen($text) > 5) {
            $isHindiText = (bool) preg_match('/[\x{0900}-\x{097F}]/u', $text);
            $text .= ($isHindiText ? ' ।' : '.');
        }

        return trim($text);
    }

    private function estimateRewriteMaxTokens(int $maxWords): int
    {
        // Generous upper bound: ~4 tokens/word + a large buffer to avoid mid-sentence cutoffs.
        $estimate = (int) ceil(($maxWords * 4) + 120);
        return max(150, min(1200, $estimate));
    }

    private function callOpenAiChatCompletions(array $messages, float $temperature, ?int $maxTokens = null): array
    {
        $apiKey = (string) env('OPENAI_API_KEY', env('chatgpt_key'));
        if (trim($apiKey) === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not set');
        }

        $tokenLimit = (int) ($maxTokens ?? $this->openAiRewriteMaxCompletionTokens());
        if ($tokenLimit <= 0) {
            $tokenLimit = 300;
        }

        $client = new \GuzzleHttp\Client([
            'timeout' => $this->openAiRewriteTimeoutSeconds(),
            'connect_timeout' => $this->openAiRewriteConnectTimeoutSeconds(),
        ]);

        $payload = [
            'model' => $this->resolveOpenAiRewriteModel(),
            'messages' => $messages,
            'temperature' => $temperature,
            // Chat Completions expects `max_tokens`. Some newer variants prefer `max_completion_tokens`.
            // Start with `max_tokens` and swap automatically if the API rejects it.
            'max_tokens' => $tokenLimit,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $curl = [
            CURLOPT_DNS_CACHE_TIMEOUT => 300,
        ];
        if ($this->openAiRewriteForceIpv4() && defined('CURL_IPRESOLVE_V4')) {
            $curl[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }

        $attempts = 1 + $this->openAiRewriteRetries();
        $response = null;
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                try {
                    $response = $client->post('https://api.openai.com/v1/chat/completions', [
                        'headers' => $headers,
                        'json' => $payload,
                        'curl' => $curl,
                    ]);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    // Some models reject certain optional parameters (e.g. temperature). If so, retry once without that param.
                    $optionalParams = ['temperature', 'top_p', 'frequency_penalty', 'presence_penalty'];
                    $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : '';
                    $decoded = $body !== '' ? json_decode($body, true) : null;

                    $param = null;
                    if (is_array($decoded) && isset($decoded['error']['param']) && is_string($decoded['error']['param'])) {
                        $param = $decoded['error']['param'];
                    }
                    if (
                        !$param
                        && is_array($decoded)
                        && isset($decoded['error']['message'])
                        && is_string($decoded['error']['message'])
                        && preg_match('/Unrecognized request argument supplied: ([a-zA-Z0-9_]+)/', $decoded['error']['message'], $m)
                    ) {
                        $param = $m[1];
                    }

                    // Handle token parameter differences.
                    if ($param === 'max_tokens' && array_key_exists('max_tokens', $payload)) {
                        unset($payload['max_tokens']);
                        $payload['max_completion_tokens'] = $tokenLimit;
                    } elseif ($param === 'max_completion_tokens' && array_key_exists('max_completion_tokens', $payload)) {
                        unset($payload['max_completion_tokens']);
                        $payload['max_tokens'] = $tokenLimit;
                    } elseif ($param && in_array($param, $optionalParams, true) && array_key_exists($param, $payload)) {
                        unset($payload[$param]);
                    } else {
                        throw $e;
                    }

                    $response = $client->post('https://api.openai.com/v1/chat/completions', [
                        'headers' => $headers,
                        'json' => $payload,
                        'curl' => $curl,
                    ]);
                }

                break;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                $lastException = $e;
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // Retry common cURL transient errors (DNS/timeout) when there is no HTTP response.
                if (!$e->hasResponse()) {
                    $ctx = method_exists($e, 'getHandlerContext') ? $e->getHandlerContext() : [];
                    $errno = (int) ($ctx['errno'] ?? 0);
                    if (in_array($errno, [6, 7, 28], true)) {
                        $lastException = $e;
                    } else {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }

            if ($attempt < $attempts) {
                // Small backoff to avoid hammering DNS/network.
                usleep((int) (200000 * $attempt));
            }
        }

        if (!$response) {
            throw $lastException ?: new \RuntimeException('OpenAI request failed');
        }

        $decoded = json_decode($response->getBody(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid OpenAI response');
        }

        return $decoded;
    }



    public function __construct(Facebook $fb)
    {

        $this->middleware(function ($request, $next) use ($fb) {

            $this->accessToken = config('services.facebook.user_token');

            $fb->setDefaultAccessToken($this->accessToken);

            $this->api = $fb;

            return $next($request);
        });



        $this->middleware('permission:blog-list|blog-create|blog-edit|blog-delete', ['only' => ['index', 'addBlog', 'editBlog', 'deleteBlog', 'deleteMultipleBlog', 'bulkUpdateSchedule', 'changeBlogStatus', 'sendBlogNotification', 'analytics']]);

        $this->middleware('permission:blog-create', ['only' => ['addBlog']]);

        $this->middleware('permission:blog-edit', ['only' => ['editBlog']]);

        $this->middleware('permission:blog-delete', ['only' => ['deleteBlog']]);

        $this->middleware('permission:blog-status', ['only' => ['changeBlogStatus']]);

        $this->middleware('permission:blog-send-notification', ['only' => ['sendBlogNotification']]);

        $this->middleware('permission:blog-analytics', ['only' => ['analytics']]);
    }



    /**

     * Show Blog view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */
    public function generateText(Request $request)
    {
        try {
            $fieldType = $request->input('fieldType'); // e.g. title, description
            $rawInputText = trim((string) $request->input('title'));
            $tone = $request->input('tone'); // e.g. friendly
            $maxWords = (int) $request->input('words'); // Word limit
            $creativity = $this->clampTemperature($request->input('creativity')); // Temperature
            $translate = $request->input('translate') === 'yes';
            $targetLang = $request->input('targetLang', 'en'); // 'en' or 'hi'

            // Force a consistently happy/positive tone for interactive rewrites (UI may still send a value).
            $sentiment = 'positive';

            $toneLabel = $tone ? (string) $tone : 'professional';
            $sentimentLabel = $sentiment ? (string) $sentiment : 'positive';

            if (!in_array($fieldType, ['title', 'description'], true)) {
                return response()->json(['error' => 'Invalid fieldType'], 422);
            }

            $inputText = html_entity_decode(strip_tags($rawInputText), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $inputText = preg_replace('/\s+/u', ' ', (string) $inputText);
            $inputText = trim((string) $inputText);

            if ($inputText === '') {
                return response()->json(['error' => 'Empty input text'], 422);
            }

            $isHindi = (bool) preg_match('/[\x{0900}-\x{097F}]/u', $inputText);

            if ($maxWords <= 0) {
                $maxWords = $fieldType === 'title' ? 25 : 80;
            }
            $maxWords = max(1, min(500, $maxWords));

            $minWords = $maxWords;
            if ($fieldType == "title")
                $minWords = max(0, $maxWords - 5);

            if ($fieldType == "description")
                $minWords = max(0, $maxWords - 10);

            $minWords = max(1, min($maxWords, $minWords));

            $fieldLabel = $fieldType === 'title' ? 'news headline' : 'news summary';

            $fieldSpecificRules = '';
            if ($fieldType === 'title') {
                $fieldSpecificRules .= "- Output a high-impact, urgent news bulletin headline (internal 'Breaking News' style).\n";
                $fieldSpecificRules .= "- Use 'Bulletin' format: extremely direct, minimal, and punchy.\n";
                $fieldSpecificRules .= "- Use internal punctuation like colons (:) or dashes (—) instead of conjunctions (e.g., 'Bhabanipur Polls: Mamata Files Nomination').\n";
                $fieldSpecificRules .= "- Use 'Headlinese': omit articles (a, an, the) and forms of 'to be' (is, are, was, were).\n";
                $fieldSpecificRules .= "- Always use present tense for recent events.\n";
                $fieldSpecificRules .= "- NEVER end the headline with a period or any punctuation at the very end.\n";
                $fieldSpecificRules .= "- Focus on the 'Who' and 'What' in as few words as possible.\n";
                $fieldSpecificRules .= "- CRITICAL: Stay strictly within the word limit and keep it to one single line.\n";
            } else {
                $fieldSpecificRules .= "- Output as plain paragraph text (no headings, bullets, or emojis).\n";
                $fieldSpecificRules .= "- Start with a clear introduction in the first line.\n";
                $fieldSpecificRules .= "- Expand with relevant details and contexts.\n";
                $fieldSpecificRules .= "- CRITICAL: Every sentence MUST be fully complete. NEVER stop mid-sentence. Always end with (. ! ? or ।).\n";
                $fieldSpecificRules .= "- If a sentence is cut due to the word limit, it will be discarded. Finish your points within the target length.\n";
                $fieldSpecificRules .= "- Finish the current sentence before stopping. It is better to write fewer words than to leave a sentence incomplete.\n";
                $fieldSpecificRules .= "- End with a positive or neutral closing sentence.\n";
            }

            $targetLangName = ($targetLang === 'hi') ? 'Hindi' : 'English';
            $systemPrompt = "You are an elite news editor and bulletin headline specialist.\n"
                . "Rewrite the user's input as a high-impact {$fieldLabel}.\n"
                . "Core Requirements:\n"
                . "- Output Language: {$targetLangName}.\n"
                . "- If the input is in a different language than {$targetLangName}, you MUST translate it to {$targetLangName} while rewriting.\n"
                . "- Professional, neutral, and factual news style.\n"
                . "- Target tone: {$toneLabel}. Target sentiment: positive and upbeat.\n"
                . "- Creativity level: {$creativity} on a 0 to 1 scale. Lower means minimal rewording; higher means more original phrasing while preserving meaning.\n"
                . "- Use third-person voice only. Do NOT use first-person or team language (I, we, our, us) and do NOT address the reader (you, your).\n"
                . "- Avoid hype, marketing language, emojis, and calls-to-action.\n"
                . "- Do not add facts, names, dates, quotes, or claims that are not in the input.\n"
                . "- If more length is needed, expand only by rephrasing, clarifying, and restating existing information (no new factual claims).\n"
                . $fieldSpecificRules
                . "- Never return an empty response.\n"
                . "- Output plain text only (no quotes, no labels, no markdown).";

            $userPrompt = "Rewrite this {$fieldType}.\n"
                . "Critical: Strictly ensure the total length is between {$minWords} and {$maxWords} words. It is better to be slightly under than over.\n\n"
                . "Input:\n"
                . "```text\n{$inputText}\n```";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ];

            $maxTokens = $this->estimateRewriteMaxTokens($maxWords);

            $responseBody = $this->callOpenAiChatCompletions($messages, $creativity, $maxTokens);
            $text = trim((string) ($responseBody['choices'][0]['message']['content'] ?? ''));
            $text = trim($text, "\"' \t\n\r\0\x0B");
            if ($fieldType === 'title') {
                $text = rtrim($text, ".");
            }

            // If the model was cut off by token limit (finish_reason=length), clean up incomplete sentences.
            $finishReason = $responseBody['choices'][0]['finish_reason'] ?? 'stop';
            if ($finishReason === 'length' && $fieldType === 'description' && $text !== '') {
                $text = $this->ensureCompleteSentences($text);
            }

            if ($text === '') {
                $nonEmptySystemPrompt = $systemPrompt . "\nCritical: Output a non-empty rewrite.";
                if ($fieldType === 'title') {
                    $nonEmptySystemPrompt .= " Follow the news bulletin headline style (punchy, high-impact, single line, no period).";
                    $nonEmptyUserPrompt = "Rewrite the text as a high-impact news bulletin headline. Ensure it is NOT empty and follows the 'Breaking News' bulletin format.\n\n"
                        . "Text:\n"
                        . "```text\n{$inputText}\n```";
                } else {
                    $nonEmptySystemPrompt .= " Follow the required structure (Introduction -> Details -> Positive/Neutral Closing).";
                    $nonEmptyUserPrompt = "Rewrite the text again. Keep it faithful to the input. Output must:\n"
                        . "1. Start with a short, clear introduction in the first line summarizing the main news in one sentence.\n"
                        . "2. Expand with relevant details and contexts.\n"
                        . "3. End with a positive or forward-looking sentence (or neutral/balanced for negative news).\n"
                        . "Critical: Strictly do NOT exceed {$maxWords} words. Write as a single plain paragraph without line breaks.\n\n"
                        . "Text:\n"
                        . "```text\n{$inputText}\n```";
                }

                $nonEmptyMessages = [
                    ['role' => 'system', 'content' => $nonEmptySystemPrompt],
                    ['role' => 'user', 'content' => $nonEmptyUserPrompt],
                ];

                $retryBody = $this->callOpenAiChatCompletions($nonEmptyMessages, $creativity, $maxTokens);
                if (!empty($retryBody['choices'][0]['message']['content'])) {
                    $responseBody = $retryBody;
                    $text = trim((string) ($responseBody['choices'][0]['message']['content'] ?? ''));
                    $text = trim($text, "\"' \t\n\r\0\x0B");
                    if ($fieldType === 'title') {
                        $text = rtrim($text, ".");
                    }
                }
            }

            // Safety pass: if model slips into first/second-person voice, retry once with stricter instruction.
            $needsRetry = false;
            if ($isHindi) {
                $needsRetry = (bool) preg_match('/\b(मैं|हम|हमारे|हमारी|हमारा|हमें|आप|तुम|तुम्हारा|आपका)\b/u', $text);
            } else {
                $needsRetry = (bool) preg_match('/\b(i|i\\s*\'?m|me|my|mine|myself|we|we\\s*\'?re|us|our|ours|ourselves|you|you\\s*\'?re|your|yours|yourself|yourselves)\b/i', $text);
            }

            if ($needsRetry) {
                $retrySystemPrompt = $systemPrompt . "\n"
                    . "Critical: Remove any first-person, team, or second-person language. Keep a strictly third-person news voice.\n";

                if ($fieldType === 'title') {
                    $retrySystemPrompt .= "Critical: Maintain the high-impact news bulletin style. Do NOT use a multi-sentence or summary format.\n";
                    $retryUserPrompt = "Rewrite the headline again, keeping meaning but fixing voice and ensuring it is a punchy news bulletin headline.\n"
                        . "Critical: Strictly do NOT exceed {$maxWords} words. NEVER end with a period.\n\n"
                        . "Text to rewrite:\n"
                        . "```text\n{$text}\n```";
                } else {
                    $retrySystemPrompt .= "Critical: Maintain the required structure: (Intro sentence -> Details -> Positive/Neutral closing sentence).\n";
                    $retryUserPrompt = "Rewrite the text again, keeping meaning but fixing voice and ensuring:\n"
                        . "1. Start with a short, clear introduction in the first line summarizing the main news.\n"
                        . "2. The body provides supporting details and context.\n"
                        . "3. The closing line is positive or forward-looking (or neutral/balanced for negative news).\n"
                        . "Target about {$maxWords} words and do NOT exceed {$maxWords} words. Output as a single plain paragraph.\n\n"
                        . "Text to rewrite:\n"
                        . "```text\n{$text}\n```";
                }

                $retryMessages = [
                    ['role' => 'system', 'content' => $retrySystemPrompt],
                    ['role' => 'user', 'content' => $retryUserPrompt],
                ];

                $retryBody = $this->callOpenAiChatCompletions($retryMessages, $creativity, $maxTokens);
                if (!empty($retryBody['choices'][0]['message']['content'])) {
                    $responseBody = $retryBody;
                }
            }

            $finalText = trim((string) ($responseBody['choices'][0]['message']['content'] ?? ''));
            $finalText = trim($finalText, "\"' \t\n\r\0\x0B");
            if ($fieldType === 'title') {
                $finalText = rtrim($finalText, ".");
            }

            // Enforce max word count deterministically
            $count = $this->countWords($finalText);
            if ($count > $maxWords) {
                // Strictly enforce limit as per user request.
                $finalText = $this->clipToMaxWords($finalText, $maxWords, ($fieldType !== 'title'));
            }
            if ($finalText !== '' && $fieldType !== 'title') {
                $finalText = $this->ensureCompleteSentences($finalText);
            }

            if ($finalText === '') {
                $finalText = $inputText;
            }

            // --- Optional Translation (to the opposite language) ---
            $translatedText = '';
            if ($translate) {
                $oppositeLang = ($targetLang === 'hi') ? 'English' : 'Hindi';
                $translateSystemPrompt = "You are a professional news translator. Translate the following {$fieldLabel} into {$oppositeLang}.\n"
                    . "Maintain the same news style, tone, and formatting.\n"
                    . "Output plain text only.";

                $translateMessages = [
                    ['role' => 'system', 'content' => $translateSystemPrompt],
                    ['role' => 'user', 'content' => $finalText],
                ];

                // Translations usually need slightly more tokens than the source (especially En->Hi)
                $transMaxTokens = (int) ($maxTokens * 1.5);
                $transResponse = $this->callOpenAiChatCompletions($translateMessages, 0.3, $transMaxTokens);
                $translatedText = trim((string) ($transResponse['choices'][0]['message']['content'] ?? ''));
                $translatedText = trim($translatedText, "\"' \t\n\r\0\x0B");
                if ($fieldType === 'description' && $translatedText !== '') {
                    $translatedText = $this->ensureCompleteSentences($translatedText);
                }
                if ($fieldType === 'title') {
                    $translatedText = rtrim($translatedText, ".");
                }
            }

            if (isset($responseBody['choices'][0]['message']['content'])) {
                $responseBody['choices'][0]['message']['content'] = $finalText;
            }
            $responseBody['translatedText'] = $translatedText;
            $responseBody['isHindiSource'] = $isHindi;

            \Helpers::clearOpenAiQuotaExpired();
            return response()->json($responseBody);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            \Helpers::markOpenAiQuotaExpiredIfNeeded($body);
            \Log::error('OpenAI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('OpenAI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error: ' . $e->getMessage()], 500);
        }
    }
    public function generateImage(Request $request)
    {

        try {

            // Get the image URL from the request
            $imageUrl = $request->input('imageUrl');
            $blog_id = $request->input('id');
            $check = BlogReimage::where('blog_id', $blog_id)->count();
            if ($check >= 3) {
                return response()->json(['msg' => 'You API calling is limited to number of 3 only', 'type' => 'error']);
            }
            // Check if image URL is provided
            if (!$imageUrl) {
                return response()->json(['msg' => 'Invalid or missing image URL', 'type' => 'error']);
            }

            // Set the API key
            $apiKey = env('STABLE_DEFUSION_KEY');

            // Create HTTP client
            $client = new \GuzzleHttp\Client();

            // Make the API request
            $response = $client->post('https://clipdrop-api.co/reimagine/v1/reimagine', [
                'headers' => [
                    'x-api-key' => $apiKey,
                ],
                'multipart' => [
                    [
                        'name' => 'image_file',
                        'contents' => fopen($imageUrl, 'r'), // Open the image URL for reading
                    ],
                ],
            ]);

            // Check if the request was successful (status code 200)
            if ($response->getStatusCode() === 200) {
                // Get the reimagined image data from the response body
                $reimaginedImageData = $response->getBody()->getContents();

                // Save the reimagined image to a file on the server
                $imageName = 'reimagined_image_' . time() . '.jpg'; // Generate a unique filename
                $imagePath = public_path('/upload/blog/banner/temp_banner/' . $imageName); // Define the path where the image will be saved
                file_put_contents($imagePath, $reimaginedImageData);
                // save file in DB
                BlogReimage::create([
                    'image' => $imageName,
                    'blog_id' => $blog_id
                ]);
                return response()->json(['blog_id' => $blog_id, 'reimage' => $imageName, 'msg' => 'Reimage generated Successfully..!', 'type' => 'success']);
            } else {
                // Handle unsuccessful response
                $msg = 'API request failed with status code: ' . $response->getStatusCode();
                return response()->json(['msg' => $msg, 'type' => 'error'], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            // Handle internal errors
            $error = 'Internal error: ' . $e->getMessage();
            \Log::error($error);
            return response()->json(['msg' => $error, 'type' => 'error'], $response->getStatusCode());
        }
    }

    private function generateAiTempImage($title, $description, $blog_id)
    {
        try {
            $prompt = "Create a photorealistic news thumbnail image (no watermark or text) that represents the following article. Title: " . trim($title) . ". Description: " . trim($description) . ". Use realistic lighting and natural colors. Avoid cartoon or illustration styles. Use a 3:2 aspect ratio and ensure the subject fills the frame (no borders, no empty margins).";
            $apiKey = env('OPENAI_API_KEY', env('chatgpt_key'));
            if (!$apiKey) {
                return ['success' => false, 'message' => 'OpenAI API key is missing'];
            }

            // Limit prompt length (approx tokens) to reduce image-generation usage.
            $maxPromptTokens = (int) env('OPENAI_IMAGE_PROMPT_MAX_TOKENS', 250);
            if ($maxPromptTokens <= 0) {
                $maxPromptTokens = 250;
            }
            $maxPromptChars = $maxPromptTokens * 4; // rough: ~4 chars/token
            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                if (mb_strlen($prompt, 'UTF-8') > $maxPromptChars) {
                    $prompt = mb_substr($prompt, 0, $maxPromptChars, 'UTF-8');
                }
            } else {
                if (strlen($prompt) > $maxPromptChars) {
                    $prompt = substr($prompt, 0, $maxPromptChars);
                }
            }

            // Try models in order of preference depending on what the API key supports
            $configuredModel = env('OPENAI_IMAGE_MODEL', '');
            $modelsToTry = $configuredModel
                ? [$configuredModel]
                : ['gpt-image-1', 'dall-e-3', 'dall-e-2'];

            $response = null;
            $lastBody = '';
            foreach ($modelsToTry as $imageModel) {
                // Set size/quality per model
                if ($imageModel === 'dall-e-2') {
                    // dall-e-2: no quality param, supports 256x256/512x512/1024x1024
                    $imageSize = '1024x1024';
                    $payload = ['model' => $imageModel, 'prompt' => $prompt, 'n' => 1, 'size' => $imageSize];
                } elseif ($imageModel === 'dall-e-3') {
                    // dall-e-3: quality = 'standard' or 'hd'
                    $imageSize = '1024x1024';
                    $payload = ['model' => $imageModel, 'prompt' => $prompt, 'n' => 1, 'size' => $imageSize, 'quality' => 'standard'];
                } else {
                    // gpt-image-1: quality = 'low', 'medium', 'high', or 'auto'
                    $imageSize = '1024x1024';
                    $payload = ['model' => $imageModel, 'prompt' => $prompt, 'n' => 1, 'size' => $imageSize, 'quality' => 'medium'];
                }

                $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/images/generations', $payload);
                $lastBody = $response->body();

                if ($response->successful()) {
                    break; // Model worked, stop trying
                }

                $decodedErr = json_decode($lastBody, true);
                $errMsg = $decodedErr['error']['message'] ?? '';
                // If error is "model does not exist", try the next one; otherwise stop
                if (stripos($errMsg, 'does not exist') === false && stripos($errMsg, 'model_not_found') === false) {
                    break;
                }
                Log::info("AI image: model '{$imageModel}' not available, trying next...");
            }

            if ($response->failed()) {
                $body = $response->body();
                \Helpers::markOpenAiQuotaExpiredIfNeeded($body);
                Log::warning('AI image generation failed: ' . $body);
                $message = 'Image generation failed';
                if ($body) {
                    $decoded = json_decode($body, true);
                    if (isset($decoded['error']['message'])) {
                        $message = $decoded['error']['message'];
                    }
                }
                return ['success' => false, 'message' => $message];
            }

            \Helpers::clearOpenAiQuotaExpired();
            $body = $response->json();
            $imageData = null;
            if (isset($body['data'][0]['b64_json'])) {
                $imageData = base64_decode($body['data'][0]['b64_json']);
            } elseif (isset($body['data'][0]['url'])) {
                $url = $body['data'][0]['url'];
                $imageData = @file_get_contents($url);
            }

            if (!$imageData) {
                return ['success' => false, 'message' => 'Empty image data'];
            }

            $minBytes = 120 * 1024;
            $maxBytes = 600 * 1024;
            $tempBase = public_path('/upload/blog/banner/temp_banner/');
            @mkdir($tempBase, 0755, true);
            $name = 'ai_' . time() . rand() . '.jpg';

            $img = Image::make($imageData);
            $img = $this->resizeToCanvas($img, 1200, 800);
            $encoded = $this->encodeJpegWithinSize($img, $minBytes, $maxBytes);
            if (!$encoded) {
                return ['success' => false, 'message' => 'Failed to encode image'];
            }
            $tempPath = $tempBase . $name;
            $written = @file_put_contents($tempPath, $encoded['data']);
            if ($written === false || !file_exists($tempPath)) {
                return ['success' => false, 'message' => 'Temp image write failed'];
            }

            return [
                'success' => true,
                'name' => $name,
                'url' => url('upload/blog/banner/temp_banner/' . $name),
            ];
        } catch (\Exception $e) {
            Log::warning('AI temp image failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Image generation error'];
        }
    }

    public function generateAiImageForBlog(Request $request)
    {
        try {
            $blogId = $request->input('blog_id');
            $blog = Blog::find($blogId);
            if (!$blog) {
                return response()->json(['status' => false, 'message' => 'Blog not found']);
            }

            $title = $blog->title ?? '';
            $description = $blog->description ?? $blog->short_description ?? '';
            $result = $this->generateAiTempImage($title, $description, $blog->id);

            if (!$result['success']) {
                return response()->json(['status' => false, 'message' => $result['message'] ?? 'Failed']);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'temp_name' => $result['name'],
                    'url' => $result['url'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Generate AI image failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error generating image']);
        }
    }

    public function saveAiImageForBlog(Request $request)
    {
        try {
            $blogId = $request->input('blog_id');
            $tempName = $request->input('temp_name');
            if (!$blogId || !$tempName) {
                return response()->json(['status' => false, 'message' => 'Missing data']);
            }

            $tempPath = public_path('/upload/blog/banner/temp_banner/' . $tempName);
            if (!file_exists($tempPath)) {
                return response()->json(['status' => false, 'message' => 'Temp image not found: ' . $tempPath]);
            }
            if (!is_readable($tempPath)) {
                return response()->json(['status' => false, 'message' => 'Temp image not readable']);
            }

            $basePath = public_path('/upload/blog/banner/');
            @mkdir($basePath . 'original/', 0755, true);
            @mkdir($basePath . '800/', 0755, true);
            @mkdir($basePath . '360/', 0755, true);
            @mkdir($basePath . '200/', 0755, true);

            $destination = $basePath . 'original/' . $tempName;
            if (!@rename($tempPath, $destination)) {
                $copied = @copy($tempPath, $destination);
                if (!$copied) {
                    return response()->json(['status' => false, 'message' => 'Failed to move temp image']);
                }
                @unlink($tempPath);
            }

            $this->resizeToCanvas(Image::make($destination), 800, 533)->save($basePath . '800/' . $tempName, 85, 'jpg');
            $this->resizeToCanvas(Image::make($destination), 360, 240)->save($basePath . '360/' . $tempName, 85, 'jpg');
            $this->resizeToCanvas(Image::make($destination), 200, 133)->save($basePath . '200/' . $tempName, 85, 'jpg');

            BlogImages::insert([
                'blog_id' => $blogId,
                'image' => $tempName,
                'created_at' => now()
            ]);

            return response()->json(['status' => true, 'message' => 'Image saved']);
        } catch (\Exception $e) {
            Log::warning('Save AI image failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error saving image: ' . $e->getMessage()]);
        }
    }

    private function encodeJpegWithinSize($img, $minBytes, $maxBytes)
    {
        $best = null;
        $bestDiff = PHP_INT_MAX;
        for ($q = 90; $q >= 10; $q -= 5) {
            $data = (string) $img->encode('jpg', $q);
            $size = strlen($data);
            if ($size >= $minBytes && $size <= $maxBytes) {
                return ['data' => $data, 'quality' => $q, 'size' => $size];
            }
            $diff = min(abs($size - $minBytes), abs($size - $maxBytes));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = ['data' => $data, 'quality' => $q, 'size' => $size];
            }
        }
        return $best;
    }


    private function resizeToCanvas($img, $width, $height)
    {
        $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        // Ensure exact target dimensions when aspect ratio already matches closely.
        if (method_exists($img, 'width') && method_exists($img, 'height')) {
            $currentW = (int) $img->width();
            $currentH = (int) $img->height();
            if ($currentW > 0 && $currentH > 0 && ($currentW !== (int) $width || $currentH !== (int) $height)) {
                $currentRatio = $currentW / $currentH;
                $targetRatio = ((int) $width) / ((int) $height);
                if (abs($currentRatio - $targetRatio) < 0.02) {
                    $img->resize($width, $height);
                }
            }
        }
        return $img;
    }

    public function getReimage(Request $request)
    {
        $blog_id = $request->blog_id;

        // Retrieve BlogReimages based on the provided blog_id
        $blogReimages = BlogReimage::where('blog_id', $blog_id)->get();

        // Render the HTML view with the retrieved images
        $html = view('super-admin.blog.reimage', ['reimages' => $blogReimages])->render();
        return response()->json(['html' => $html, 'msg' => 'Reimage generating Successfully..!', 'type' => 'success']);
    }

    public function index(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        // Defensive: if `/blog/{id}/logs` accidentally hits this action (route cache / older routes),
        // return logs JSON instead of trying to render the listing with a numeric "layout".
        if ((string) $theme === 'logs' && is_numeric($layout)) {
            return $this->blogActionLogs($request, (int) $layout);
        }

        $allowedLayouts = ['side-menu', 'top-menu', 'simple-menu'];
        if (!in_array((string) $layout, $allowedLayouts, true)) {
            $layout = 'side-menu';
        }

        $allowedThemes = ['light', 'dark'];
        if (!in_array((string) $theme, $allowedThemes, true)) {
            $segTheme = (string) $request->segment(3);
            $theme = in_array($segTheme, $allowedThemes, true) ? $segTheme : 'light';
        }

        $blog = Blog::getAllBlog($request->all());


        $category = Category::getAllActiveCategory();

        return view('super-admin/blog.index', [

            'theme' => $theme,

            'page_name' => 'index',

            'side_menu' => array(),

            'layout' => $layout,

            'category' => $category,

            'blog' => $blog,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/blog/side-menu/light') . '" class="breadcrumb--active">' . trans("admin.blog_post") . '</a>'

        ]);
    }

    /**
     * Bulk update schedule date/time for multiple blogs
     */
    // Bulk update handled below (single implementation kept)



    /**

     * Show Blog view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function addBlog(Request $request, $layout = 'side-menu', $theme = 'light')
    {

        $category = Category::getAllActiveCategory();

        $author = Author::getAllActiveAuthors();

        $Languages = Languages::orderBy('name', 'ASC')->get();

        $theme = $request->segment(3);

        return view('super-admin/blog.create', [

            'theme' => $theme,

            'page_name' => 'create',

            'side_menu' => array(),

            'layout' => $layout,

            'category' => $category,

            'languages' => $Languages,

            'voice_accent' => config('constant.voice_accent'),

            'author' => $author,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/blog/side-menu/light') . '" class="breadcrumb">' . trans("admin.blog_post") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/add-blog') . '/" class="breadcrumb--active">' . trans("admin.add_blog") . '</a>'

        ]);
    }

    public static function sendNotificationTest()
    {
        // This should be the OneSignal Player ID, not the FCM token
        $playerIds = ['2e826c16-4914-4a3b-bfc9-4cf11282dc00', 'e62de3bb-2a69-4b1d-bea6-f8666a3faf09', '87e8ebe2-e87d-4d5c-848f-0d8bf2c0c398'];

        // Your OneSignal credentials
        $ONESIGNAL_APP_ID = '594e3b1c-8fde-4c45-9679-db27b75c25a6';
        $ONESIGNAL_REST_API_KEY = 'YWI5MDA2NTktNmJkMS00MWRjLWJkMzctMzZmYjA5Y2NkMzFm';

        // Notification content
        $data = [
            'app_id' => $ONESIGNAL_APP_ID, // Your OneSignal App ID
            'contents' => [
                'en' => 'web did you receive it for test player ID 0.1?',
            ],
            'headings' => [
                'en' => 'web Notification Title iPHONE 0.1',
            ],
            'include_player_ids' => $playerIds, // Use the OneSignal player ID here
        ];

        // Send the notification via OneSignal API
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $ONESIGNAL_REST_API_KEY,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $data);

        // Return or log the response to check for errors
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->body(); // to see the error details
        }
    }


    //     public static function sendNotificationTest()
    // {

    //     $fcmToken = ['dJLr1SYCQcekLL3VB691-3:APA91bFUqzdaHLSHNCgMzykqYpR3EbLT1IRGAeAprU1A3GvXHFgxDDRdc2eSL_oStfxN4ypp5V_z_Rzce43f4YLszeF0PUtxa3xMuB1cn6TuCy78nh26-WtwWhPI2eBMlfk3VXArvYc_ '];
    //     $ONESIGNAL_APP_ID = '594e3b1c-8fde-4c45-9679-db27b75c25a6';
    //     $ONESIGNAL_REST_API_KEY = 'YWI5MDA2NTktNmJkMS00MWRjLWJkMzctMzZmYjA5Y2NkMzFm';
    //      $data = [
    //             'app_id' => $ONESIGNAL_APP_ID, // Your OneSignal App ID
    //             'contents' => [
    //                 'en' => 'web did you recieve it for test i-phon 0.1?',
    //             ],
    //             'headings' => [
    //                 'en' => 'web Notification Title iPHONE 0.1',
    //             ],

    //             'include_player_ids' => $fcmToken, // Use the FCM device token here
    //         ];

    //         $response = Http::withHeaders([
    //             'Authorization' => 'Basic ' . $ONESIGNAL_REST_API_KEY, 
    //             'Content-Type' => 'application/json',
    //         ])->post('https://onesignal.com/api/v1/notifications', $data);
    //         dd($response);

    //         return;
    // }




    public static function getAccessToken($serviceAccountPath)
    {

        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        // dd($serviceAccount['private_key']);
        $now = time();
        $expirationTime = $now + 3600; // Token expiration time (1 hour)
        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expirationTime,
        ]));

        $message = $jwtHeader . '.' . $jwtPayload;
        $signature = '';
        openssl_sign($message, $signature, $serviceAccount['private_key'], 'SHA256');

        $jwtAssertion = $message . '.' . base64_encode($signature);

        // Step 4: Make the request to get the access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwtAssertion
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($response, true);
        return $tokenData['access_token'];
    }


    /**

     * Show Edit Blog view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function editBlog(Request $request, $layout = 'side-menu', $theme = 'light', $id = 0)
    {
        $max_words = SiteContent::where('key', 'news_max_words')->first()->value ?? 60;
        $category = Category::getAllActiveCategory();

        $author = Author::getAllActiveAuthors();

        $blog = Blog::find($id);

        $blogCategoryArr = array();

        $theme = $request->segment(3);

        if ($blog) {

            if ($blog->schedule_date) {

                $blog->schedule_time = date("H:i", strtotime($blog->schedule_date));

                $blog->schedule_date = date("Y-m-d", strtotime($blog->schedule_date));
            }

            $blogCategory = BlogCategory::where('blog_id', $id)->get();

            if (count($blogCategory)) {

                foreach ($blogCategory as $blogCategory_data) {

                    array_push($blogCategoryArr, $blogCategory_data->category_id);
                }
            }

            if (count($blogCategoryArr)) {

                $blog->blog_category_id = $blogCategoryArr;
            }
        }



        $blogImages = BlogImages::where('blog_id', $id)->get();



        $blogTranslations = BlogTranslation::where('blog_id', $id)->get()->keyBy('language_code');
        $selectedLanguageCodes = $blogTranslations->keys()->values()->all();
        $language_code = array_values(array_unique(array_merge(['en', 'hi'], $selectedLanguageCodes)));

        $language = Languages::whereIn('language', $language_code)->get();



        return view('super-admin/blog.edit', [

            'theme' => $theme,

            'page_name' => 'create',

            'side_menu' => array(),

            'layout' => $layout,

            'category' => $category,

            'author' => $author,

            'voice_accent' => config('constant.voice_accent'),

            'blogImages' => $blogImages,

            'language' => $language,

            'blog' => $blog,

            'max_words' => $max_words,
            'blogTranslations' => $blogTranslations,
            'selectedLanguageCodes' => $language_code,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/blog/side-menu/light') . '" class="breadcrumb">' . trans("admin.blog_post") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/edit-blog' . '/' . $layout . '/' . $theme) . '/' . $id . '" class="breadcrumb--active">' . trans("admin.edit_blog") . '</a>'



        ]);
    }





    /**

     * upload blog thumb image

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */





    public function uploadBlogThumbImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/blog/thumb/original/') . $name;

                    $basePath = public_path('/upload/blog/thumb/');

                    $c = \Helpers::compress_image($file, $destination, 30, $name, $basePath, true);
                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }





    /**

     * upload banner image

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function uploadBannerImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/blog/banner/original/') . $name;

                    $basePath = public_path('/upload/blog/banner/');

                    $c = \Helpers::compress_image($file, $destination, 30, $name, $basePath, true);
                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }



    /**

     * upload audio file

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function uploadAudioFIle(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                $data = [];

                if ($post['audio_file'] != '') {

                    if ($post['audio_file'] != '') {

                        $images = $post['audio_file'];

                        $post['audio_file'] = time() . rand() . '.' . $images->getClientOriginalExtension();

                        $destinationPath = public_path('/upload/blog/audio');

                        $images->move($destinationPath, $post['audio_file']);

                        $name = $post['audio_file'];

                        $data['fullpath'] = url('/upload/blog/audio') . '/' . $post['audio_file'];

                        $data['name'] = $name;
                    }
                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $data));
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }



    /**

     * Function to Upload Category image.

     * @return Response

     */



    public function uploadMultipleBannerImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $data['images'] = [];

                $data['images_url'] = [];

                $files = $request->file('image');

                foreach ($files as $file) {

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    array_push($data['images'], $name);

                    $destination = public_path('/upload/blog/banner/original/') . $name;

                    $basePath = public_path('/upload/blog/banner/');

                    $c = \Helpers::compress_image($file, $destination, 30, $name, $basePath, true);

                    if ($c) {

                        $img_url = url('/upload/blog/banner/original') . '/' . $name;

                        array_push($data['images_url'], $img_url);
                    }
                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $data));
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }



    public function addUpdateblogTest(Request $request)
    {

        if ($request->ajax()) {

            $post = $request->all();

            if (isset($post['schedule_date'])) {

                if (date('Y-m-d', strtotime($post['schedule_date'])) < date('Y-m-d')) {

                    return response(\Helpers::sendFailureAjaxResponse(__('Shedule date must be grater than or equal to current date'), []));
                }
            }

            $languages = [];

            $postData = [];

            if (isset($post['language_code'])) {

                $languages = $post['language_code'];

                unset($post['language_code']);
            }



            if (isset($post['language[]'])) {

                unset($post['language[]']);
            }

            $data['prefield'] = $post;

            $submittype = (!isset($post['submittype'])) ? 'save' : $post['submittype'];



            $add_blog_image = array();

            unset($post['_token']);

            if (isset($post['submittype'])) {

                unset($post['submittype']);
            }



            $postData = $post;

            // Remove AI helper and translation fields to prevent SQL Unknown Column errors
            unset(
                $postData['tone'], $postData['creativity'], $postData['sentimate'], 
                $postData['words'], $postData['location'], $postData['translate'],
                $postData['edit_lang'], $postData['title_en'], $postData['title_hi'],
                $postData['description_en'], $postData['description_hi']
            );

            if (isset($postData['image'])) {
                unset($postData['image']);
            }





            if (isset($post['is_featured']) && $post['is_featured'] == 'on') {

                $postData['is_featured'] = 1;
            } else {

                $postData['is_featured'] = 0;
            }



            if (isset($post['audio_file_upload'])) {

                $postData['audio_file'] = $post['audio_file_upload'];
            }

            if (isset($post['video_url']) && $post['video_url'] != '') {

                $postData['content_type'] = 'video';
            } elseif (isset($post['audio_file_upload']) && $post['audio_file_upload'] != '') {

                $postData['content_type'] = 'audio';
            } else {

                $postData['content_type'] = 'text';
            }



            if (isset($post['is_slider']) && $post['is_slider'] == 'on') {

                $postData['is_slider'] = 1;
            } else {

                $postData['is_slider'] = 0;
            }

            if (isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on') {

                $postData['is_editor_picks'] = 1;
            } else {

                $postData['is_editor_picks'] = 0;
            }

            if (isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on') {

                $postData['is_weekly_top_picks'] = 1;
            } else {

                $postData['is_weekly_top_picks'] = 0;
            }



            if (isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on') {

                $postData['is_voting_enable'] = 1;
            } else {

                $postData['is_voting_enable'] = 0;
            }



            $postData['created_by'] = Auth::User()->id;



            if (isset($post['schedule_date']) && $post['schedule_date'] != '') {

                if (isset($post['schedule_time']) && $post['schedule_time'] != '') {

                    $date = date("Y-m-d", strtotime($post['schedule_date']));

                    $time = date("H:i:s", strtotime($post['schedule_time']));

                    $postData['schedule_date'] = $date . " " . $time;
                }
            } else {

                $postData['schedule_date'] = date("Y-m-d H:i:s");
            }



            unset($postData['schedule_time']);

            unset($postData['audio_file_upload']);

            unset($postData['category_id']);

            unset($postData['is_location_radius']);

            $validate = [

                'title' => 'required',

                'slug' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                $error = '';

                $errors = (array) $data['error'];

                foreach ($errors as $row) {

                    foreach ($validate as $key => $value) {

                        if (isset($row[$key])) {

                            $error = $row[$key];
                        }
                    }
                }

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), $data['prefield']));
            } else {



                $add_blog_image = array();

                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {

                    if (isset($post['schedule_time']) && $post['schedule_time'] != '') {

                        $date = date("Y-m-d", strtotime($post['schedule_date']));

                        $time = date("H:i:s", strtotime($post['schedule_time']));

                        $postData['schedule_date'] = $date . " " . $time;
                    } else {

                        $postData['schedule_date'] = date("Y-m-d H:i:s");
                    }
                }



                if (isset($post['slug'])) {

                    $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();

                    if ($slugExist) {

                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                    }

                    $slug = $post['slug'];
                } else {

                    $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
                }



                $postData['slug'] = $slug;

                $postData['status'] = 2;

                $postData['created_at'] = date('Y-m-d H:i:s');

                $id = Blog::insertGetId($postData);

                if ($id) {

                    if (isset($post['image'])) {

                        if (count($post['image'])) {

                            for ($v = 0; $v < count($post['image']); $v++) {

                                $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));

                                array_push($add_blog_image, $image_id);
                            }
                        }
                    }

                    if (isset($post['category_id'])) {

                        if (count($post['category_id'])) {

                            for ($x = 0; $x < count($post['category_id']); $x++) {

                                BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
                            }
                        }
                    }

                    for ($g = 0; $g < count($languages); $g++) {

                        $injectTransLation = array(

                            'blog_id' => $id,

                            'language_code' => $languages[$g],

                            'title' => $postData['title'],

                            'tags' => $postData['tags'],

                            'description' => $postData['description'],

                            'seo_title' => $postData['seo_title'],

                            'seo_keyword' => $postData['seo_keyword'],

                            'seo_tag' => $postData['seo_tag'],

                            'seo_description' => $postData['seo_description'],

                            'is_location_radius' => (isset($post['is_location_radius']) && $post['is_location_radius'] == 'on') ? 1 : 0,

                            'created_at' => date("Y-m-d H:i:s"),

                        );

                        BlogTranslation::insertGetId($injectTransLation);
                    }

                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                } else {

                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                }
            }
        } else {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
        }
    }


    public function blogActionLogs(Request $request, $id)
    {

        try {
            $blogId = (int) $id;
            if ($blogId <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid blog id'], 422);
            }



            $blog = Blog::select('id', 'title', 'created_by', 'status')->where('id', $blogId)->first();
            if (!$blog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found.',
                    'blog_id' => $blogId,
                    'logs' => [],
                ]);
            }
            $addedById = $blog && $blog->created_by ? (int) $blog->created_by : null;
            $addedByName = null;
            if ($addedById) {
                $u = User::select('id', 'name')->where('id', $addedById)->first();
                if ($u) {
                    $addedByName = (string) $u->name;
                }
            }

            $logs = BlogActionLog::where('blog_id', $blogId)
                ->with(['user'])
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();

            $payload = $logs->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                    'action' => (string) $row->action,
                    'user_id' => $row->user_id !== null ? (int) $row->user_id : null,
                    'user_name' => $row->user ? (string) $row->user->name : null,
                    'ip' => $row->ip ? (string) $row->ip : null,
                    'meta' => $row->meta,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'blog_id' => $blogId,
                'blog_title' => $blog ? (string) $blog->title : null,
                'blog_status' => $blog ? (int) $blog->status : null,
                'added_by_id' => $addedById,
                'added_by_name' => $addedByName,
                'logs' => $payload
            ]);
        } catch (\Exception $e) {
            \Log::error('blogActionLogs error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal error'], 500);
        }
    }


    /**

     * add update blog

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    // public function addUpdateblog(Request $request)
    // {
    //     try {
    //         if ($request->ajax()) {
    //             $post = $request->all();
    //             if (isset($post['schedule_date'])) {
    //                 if (date('Y-m-d', strtotime($post['schedule_date'])) < date('Y-m-d')) {
    //                     return response(\Helpers::sendFailureAjaxResponse(__('Shedule date must be grater than or equal to current date'), []));
    //                 }
    //             }
    //             $languages = [];
    //             if (isset($post['language_code'])) {
    //                 $languages = $post['language_code'];
    //                 unset($post['language_code']);
    //             }
    //             if (isset($post['language[]'])) {
    //                 unset($post['language[]']);
    //             }
    //             $data['prefield'] = $post;
    //             $submittype = (!isset($post['submittype'])) ? 'save' : $post['submittype'];
    //             $add_blog_image = array();
    //             unset($post['_token']);
    //             if (isset($post['submittype'])) {
    //                 unset($post['submittype']);
    //             }
    //             if (!isset($post['status'])) {
    //                 $post['status'] = 1;
    //             }
    //             $postData = $post;
    //             if (isset($postData['image'])) {
    //                 unset($postData['image']);
    //             }
    //             if (isset($post['is_featured']) && $post['is_featured'] == 'on') {
    //                 $postData['is_featured'] = 1;
    //             } else {
    //                 $postData['is_featured'] = 0;
    //             }
    //             if (isset($post['audio_file_upload'])) {
    //                 $postData['audio_file'] = $post['audio_file_upload'];
    //             }
    //             if (isset($post['video_url']) && $post['video_url'] != '') {
    //                 $postData['content_type'] = 'video';
    //             } elseif (isset($post['audio_file_upload']) && $post['audio_file_upload'] != '') {
    //                 $postData['content_type'] = 'audio';
    //             } else {
    //                 $postData['content_type'] = 'text';
    //             }
    //             if (isset($post['is_slider']) && $post['is_slider'] == 'on') {
    //                 $postData['is_slider'] = 1;
    //             } else {
    //                 $postData['is_slider'] = 0;
    //             }
    //             if (isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on') {
    //                 $postData['is_editor_picks'] = 1;
    //             } else {
    //                 $postData['is_editor_picks'] = 0;
    //             }
    //             if (isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on') {
    //                 $postData['is_weekly_top_picks'] = 1;
    //             } else {
    //                 $postData['is_weekly_top_picks'] = 0;
    //             }
    //             if (isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on') {
    //                 $postData['is_voting_enable'] = 1;
    //             } else {
    //                 $postData['is_voting_enable'] = 0;
    //             }
    //             $postData['created_by'] = Auth::User()->id;

    //             // $prefix = 'ab';
    //             // $numberDigits = 3;
    //             // $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //             // $blog_code = $prefix . $numberPart;
    //             // $postData['blog_code'] = $blog_code;
    //             if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                 $prefix = 'ab';
    //                 $numberDigits = 3;
    //                 $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                 $blog_code = $prefix . $numberPart;
    //                 $postData['blog_code'] = $blog_code;
    //             }

    //             if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                 if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                     $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                     $time = date("H:i:s", strtotime($post['schedule_time']));
    //                     $postData['schedule_date'] = $date . " " . $time;
    //                 }
    //             } else {
    //                 $postData['schedule_date'] = date("Y-m-d H:i:s");
    //             }
    //             unset($postData['schedule_time']);
    //             unset($postData['audio_file_upload']);
    //             if ($submittype == 'draft') {
    //                 $validate = [
    //                     'title' => 'required',
    //                     'slug' => 'required',
    //                 ];
    //                 $validator = Validator::make($post, $validate);
    //                 if ($validator->fails()) {
    //                     $data['error'] = $validator->errors();
    //                     $error = '';
    //                     $errors = (array) $data['error'];
    //                     foreach ($errors as $row) {
    //                         foreach ($validate as $key => $value) {
    //                             if (isset($row[$key])) {
    //                                 $error = $row[$key];
    //                             }
    //                         }
    //                     }
    //                     return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), $data['prefield']));
    //                 } else {
    //                     $add_blog_image = array();
    //                     if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                         if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                             $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                             $time = date("H:i:s", strtotime($post['schedule_time']));
    //                             $postData['schedule_date'] = $date . " " . $time;
    //                         } else {
    //                             $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                         }
    //                     }
    //                     if (isset($post['slug'])) {
    //                         $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
    //                         if ($slugExist) {
    //                             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                         }
    //                         $slug = $post['slug'];
    //                     } else {
    //                         $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
    //                     }

    //                     // $prefix = 'ab';
    //                     // $numberDigits = 3;
    //                     // $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     // $blog_code = $prefix . $numberPart;
    //                     // $postData['blog_code'] = $blog_code;
    //                     if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                         $prefix = 'ab';
    //                         $numberDigits = 3;
    //                         $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                         $blog_code = $prefix . $numberPart;
    //                         $postData['blog_code'] = $blog_code;
    //                     }

    //                     $postData['slug'] = $slug;
    //                     $postData['status'] = 2;
    //                     $postData['created_at'] = date('Y-m-d H:i:s');
    //                     $id = Blog::insertGetId($postData);
    //                     if ($id) {
    //                         if (isset($post['image'])) {
    //                             if (count($post['image'])) {
    //                                 for ($v = 0; $v < count($post['image']); $v++) {
    //                                     $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                     array_push($add_blog_image, $image_id);
    //                                 }
    //                             }
    //                         }
    //                         if (isset($post['category_id'])) {
    //                             if (count($post['category_id'])) {
    //                                 for ($x = 0; $x < count($post['category_id']); $x++) {
    //                                     BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
    //                                 }
    //                             }
    //                         }
    //                         for ($g = 0; $g < count($languages); $g++) {
    //                             $injectTransLation = array(
    //                                 'blog_id' => $id,
    //                                 'language_code' => $languages[$g],
    //                                 'title' => $postData['title'],
    //                                 'tags' => $postData['tags'],
    //                                 'description' => $postData['description'],
    //                                 'seo_title' => $postData['seo_title'],
    //                                 'seo_keyword' => $postData['seo_keyword'],
    //                                 'seo_tag' => $postData['seo_tag'],
    //                                 'seo_description' => $postData['seo_description'],
    //                                 'created_at' => date("Y-m-d H:i:s"),
    //                             );
    //                             BlogTranslation::insertGetId($injectTransLation);
    //                         }
    //                         return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
    //                     } else {
    //                         return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
    //                     }
    //                 }
    //             } else {
    //                 if ($post['status'] == 2) {
    //                     if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                         if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                             $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                             $time = date("H:i:s", strtotime($post['schedule_time']));
    //                             $postData['schedule_date'] = $date . " " . $time;
    //                         } else {
    //                             $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                         }
    //                     }
    //                     $postData['status'] = 1;
    //                 }
    //                 if (isset($post['id']) && $post['id'] != '' & $post['id'] != 0) {
    //                     unset($postData['created_by']);
    //                     if (isset($post['image'])) {
    //                         if (count($post['image'])) {
    //                             for ($v = 0; $v < count($post['image']); $v++) {
    //                                 $image_id = BlogImages::insert(array('blog_id' => $post['id'], 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                 array_push($add_blog_image, $image_id);
    //                             }
    //                         }
    //                     }
    //                     if (isset($post['category_id'])) {
    //                         if (count($post['category_id'])) {
    //                             $checkBlog = BlogCategory::where('blog_id', $post['id'])->delete();
    //                             for ($z = 0; $z < count($post['category_id']); $z++) {
    //                                 BlogCategory::insertGetId(array('blog_id' => $post['id'], 'category_id' => $post['category_id'][$z], 'created_at' => date('Y-m-d H:i:s')));
    //                             }
    //                         }
    //                     }
    //                     if (isset($post['slug'])) {
    //                         $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', $post['id'])->count();
    //                         if ($slugExist) {
    //                             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                         }
    //                         $slug = $post['slug'];
    //                     } else {
    //                         $slug = \Helpers::createSlug($postData['title'], 'blog', $post['id'], false);
    //                     }
    //                     $postData['slug'] = $slug;

    //                     // $prefix = 'ab';
    //                     // $numberDigits = 3;
    //                     // $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     // $blog_code = $prefix . $numberPart;
    //                     // $postData['blog_code'] = $blog_code;
    //                     if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                         $prefix = 'ab';
    //                         $numberDigits = 3;
    //                         $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                         $blog_code = $prefix . $numberPart;
    //                         $postData['blog_code'] = $blog_code;
    //                     }

    //                     if (isset($postData['language'])) {
    //                         unset($postData['language']);
    //                     }
    //                     Blog::where('id', $post['id'])->update($postData);
    //                     $blogtransExist = BlogTranslation::where('blog_id', $post['id'])->where('language_code', $post['language'])->first();
    //                     $injectTransLation = array(
    //                         'blog_id' => $post['id'],
    //                         'language_code' => $post['language'],
    //                         'title' => $postData['title'],
    //                         'tags' => $postData['tags'],
    //                         'description' => $postData['description'],
    //                         'seo_title' => $postData['seo_title'],
    //                         'seo_keyword' => $postData['seo_keyword'],
    //                         'seo_tag' => $postData['seo_tag'],
    //                         'seo_description' => $postData['seo_description'],
    //                     );
    //                     if ($blogtransExist) {
    //                         BlogTranslation::where('id', $blogtransExist->id)->update($injectTransLation);
    //                     } else {
    //                         $injectTransLation['created_at'] = date("Y-m-d H:i:s");
    //                         BlogTranslation::insertGetId($injectTransLation);
    //                     }
    //                 } else {
    //                     if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                         if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                             $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                             $time = date("H:i:s", strtotime($post['schedule_time']));
    //                             $postData['schedule_date'] = $date . " " . $time;
    //                         } else {
    //                             $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                         }
    //                     }
    //                     if (isset($post['slug'])) {
    //                         $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
    //                         if ($slugExist) {
    //                             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                         }
    //                         $slug = $post['slug'];
    //                     } else {
    //                         $slug = $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
    //                     }
    //                     $postData['slug'] = $slug;
    //                     unset($postData['category_id']);
    //                     $postData['created_at'] = date('Y-m-d H:i:s');

    //                     // $prefix = 'ab';
    //                     // $numberDigits = 3;
    //                     // $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     // $blog_code = $prefix . $numberPart;
    //                     // $postData['blog_code'] = $blog_code;
    //                     if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                         $prefix = 'ab';
    //                         $numberDigits = 3;
    //                         $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                         $blog_code = $prefix . $numberPart;
    //                         $postData['blog_code'] = $blog_code;
    //                     }

    //                     $id = Blog::insertGetId($postData);
    //                     $post['id'] = $id;
    //                     if ($id) {
    //                         for ($g = 0; $g < count($languages); $g++) {
    //                             $injectTransLation = array(
    //                                 'blog_id' => $id,
    //                                 'language_code' => $languages[$g],
    //                                 'title' => $postData['title'],
    //                                 'tags' => $postData['tags'],
    //                                 'description' => $postData['description'],
    //                                 'seo_title' => $postData['seo_title'],
    //                                 'seo_keyword' => $postData['seo_keyword'],
    //                                 'seo_tag' => $postData['seo_tag'],
    //                                 'seo_description' => $postData['seo_description'],
    //                                 'created_at' => date("Y-m-d H:i:s"),
    //                             );
    //                             BlogTranslation::insertGetId($injectTransLation);
    //                         }
    //                         if (isset($post['image'])) {
    //                             if (count($post['image'])) {
    //                                 for ($v = 0; $v < count($post['image']); $v++) {
    //                                     $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                     array_push($add_blog_image, $image_id);
    //                                 }
    //                             }
    //                         }
    //                         if (isset($post['category_id'])) {
    //                             if (count($post['category_id'])) {
    //                                 for ($x = 0; $x < count($post['category_id']); $x++) {
    //                                     BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
    //                                 }
    //                             }
    //                         }
    //                         $this->uploadPostOnSocial($id);
    //                         $blog_data = BlogImages::whereIn('id', $add_blog_image)->get();
    //                         foreach ($blog_data as $blog_image_notification) {
    //                             if ($blog_image_notification->image != null || $blog_image_notification->image != '') {
    //                                 $blog_image_notification->image = url('upload/blog/banner/original/' . $blog_image_notification->image);
    //                             } else {
    //                                 $blog_image_notification->image = url('upload/blog/banner/default.jpg');
    //                             }
    //                         }
    //                         if (setting('enable_notifications')) {
    //                             if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                 $user = User::where('active', 1)->get();
    //                                 $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                 foreach ($user as $detail) {
    //                                     if ($detail->device_token != null) {
    //                                         \Helpers::sendNotification($detail->device_token, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                                     }
    //                                 }
    //                                 $non_logged_in = DeviceToken::get();
    //                                 if (count($non_logged_in)) {
    //                                     foreach ($non_logged_in as $non_logged_in_data) {
    //                                         if ($non_logged_in_data->device_token != null) {
    //                                             \Helpers::sendNotification($non_logged_in_data->device_token, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
    //                     }
    //                 }
    //                 if ($post['status'] == 2) {
    //                     if (isset($post['image'])) {
    //                         $image = url('upload/blog/banner/default.jpg');
    //                         $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
    //                         if ($blogImageInfo) {
    //                             $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //                         }
    //                         if (setting('enable_notifications')) {
    //                             if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                 $user = User::where('active', 1)->get();
    //                                 $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                 foreach ($user as $detail) {
    //                                     if ($detail->device_token != null) {
    //                                         \Helpers::sendNotification($detail->device_token, $postData['title'], '', $setting->value, $image, $post['id']);
    //                                     }
    //                                 }
    //                                 $non_logged_in = DeviceToken::get();
    //                                 if (count($non_logged_in)) {
    //                                     foreach ($non_logged_in as $non_logged_in_data) {
    //                                         if ($non_logged_in_data->device_token != null) {
    //                                             \Helpers::sendNotification($non_logged_in_data->device_token, $postData['title'], '', $setting->value, $image, $post['id']);
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         $image = url('upload/blog/banner/default.jpg');
    //                         $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
    //                         if ($blogImageInfo) {
    //                             $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //                         }
    //                         if (setting('enable_notifications')) {
    //                             if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                 $user = User::where('active', 1)->get();
    //                                 $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                 foreach ($user as $detail) {
    //                                     if ($detail->device_token != null) {
    //                                         \Helpers::sendNotification($detail->device_token, $postData['title'], '', $setting->value, $image, $post['id']);
    //                                     }
    //                                 }
    //                                 $non_logged_in = DeviceToken::get();
    //                                 if (count($non_logged_in)) {
    //                                     foreach ($non_logged_in as $non_logged_in_data) {
    //                                         if ($non_logged_in_data->device_token != null) {
    //                                             \Helpers::sendNotification($non_logged_in_data->device_token, $postData['title'], '', $setting->value, $image, $post['id']);
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                     $this->uploadPostOnSocial($post['id']);
    //                 }
    //                 return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
    //             }
    //         } else {
    //             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
    //         }
    //     } catch (\Exception $ex) {
    //         return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error') . $ex));
    //     }
    // }


    //   public function addUpdateblog(Request $request)
    //     {
    //         try {
    //             if ($request->ajax()) {
    //                 $post = $request->all();
    //                 if (isset($post['schedule_date'])) {
    //                     if (date('Y-m-d', strtotime($post['schedule_date'])) < date('Y-m-d')) {
    //                         return response(\Helpers::sendFailureAjaxResponse(__('Shedule date must be grater than or equal to current date'), []));
    //                     }
    //                 }
    //                 $languages = [];
    //                 if (isset($post['language_code'])) {
    //                     $languages = $post['language_code'];
    //                     unset($post['language_code']);
    //                 }
    //                 if (isset($post['language[]'])) {
    //                     unset($post['language[]']);
    //                 }
    //                 $data['prefield'] = $post;
    //                 $submittype = (!isset($post['submittype'])) ? 'save' : $post['submittype'];
    //                 $add_blog_image = array();
    //                 unset($post['_token']);
    //                 if (isset($post['submittype'])) {
    //                     unset($post['submittype']);
    //                 }
    //                 if (!isset($post['status'])) {
    //                     $post['status'] = 1;
    //                 }
    //                 $postData = $post;
    //                 if (isset($postData['image'])) {
    //                     unset($postData['image']);
    //                 }
    //                 if (isset($post['is_featured']) && $post['is_featured'] == 'on') {
    //                     $postData['is_featured'] = 1;
    //                 } else {
    //                     $postData['is_featured'] = 0;
    //                 }
    //                 if (isset($post['audio_file_upload'])) {
    //                     $postData['audio_file'] = $post['audio_file_upload'];
    //                 }
    //                 if (isset($post['video_url']) && $post['video_url'] != '') {
    //                     $postData['content_type'] = 'video';
    //                 } elseif (isset($post['audio_file_upload']) && $post['audio_file_upload'] != '') {
    //                     $postData['content_type'] = 'audio';
    //                 } else {
    //                     $postData['content_type'] = 'text';
    //                 }
    //                 if (isset($post['is_slider']) && $post['is_slider'] == 'on') {
    //                     $postData['is_slider'] = 1;
    //                 } else {
    //                     $postData['is_slider'] = 0;
    //                 }
    //                 if (isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on') {
    //                     $postData['is_editor_picks'] = 1;
    //                 } else {
    //                     $postData['is_editor_picks'] = 0;
    //                 }
    //                 if (isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on') {
    //                     $postData['is_weekly_top_picks'] = 1;
    //                 } else {
    //                     $postData['is_weekly_top_picks'] = 0;
    //                 }
    //                 if (isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on') {
    //                     $postData['is_voting_enable'] = 1;
    //                 } else {
    //                     $postData['is_voting_enable'] = 0;
    //                 }
    //                 $postData['created_by'] = Auth::User()->id;
    //                  if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                     $prefix = 'ab';
    //                     $numberDigits = 3;
    //                     $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     $blog_code = $prefix . $numberPart;
    //                     $postData['blog_code'] = $blog_code;
    //                 }
    //                 if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                     if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                         $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                         $time = date("H:i:s", strtotime($post['schedule_time']));
    //                         $postData['schedule_date'] = $date . " " . $time;
    //                     }
    //                 } else {
    //                     $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                 }
    //                 unset($postData['schedule_time']);
    //                 unset($postData['audio_file_upload']);
    //                 if ($submittype == 'draft') {
    //                     $validate = [
    //                         'title' => 'required',
    //                         'slug' => 'required',
    //                     ];
    //                     $validator = Validator::make($post, $validate);
    //                     if ($validator->fails()) {
    //                         $data['error'] = $validator->errors();
    //                         $error = '';
    //                         $errors = (array) $data['error'];
    //                         foreach ($errors as $row) {
    //                             foreach ($validate as $key => $value) {
    //                                 if (isset($row[$key])) {
    //                                     $error = $row[$key];
    //                                 }
    //                             }
    //                         }
    //                         return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), $data['prefield']));
    //                     } else {
    //                         $add_blog_image = array();
    //                         if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                             if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                                 $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                                 $time = date("H:i:s", strtotime($post['schedule_time']));
    //                                 $postData['schedule_date'] = $date . " " . $time;
    //                             } else {
    //                                 $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                             }
    //                         }
    //                         if (isset($post['slug'])) {
    //                             $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
    //                             if ($slugExist) {
    //                                 return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                             }
    //                             $slug = $post['slug'];
    //                         } else {
    //                             $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
    //                         }
    //                           if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                     $prefix = 'ab';
    //                     $numberDigits = 3;
    //                     $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     $blog_code = $prefix . $numberPart;
    //                     $postData['blog_code'] = $blog_code;
    //                 }
    //                         $postData['slug'] = $slug;
    //                         $postData['status'] = 2;
    //                         $postData['created_at'] = date('Y-m-d H:i:s');
    //                         $id = Blog::insertGetId($postData);
    //                         if ($id) {
    //                             if (isset($post['image'])) {
    //                                 if (count($post['image'])) {
    //                                     for ($v = 0; $v < count($post['image']); $v++) {
    //                                         $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                         array_push($add_blog_image, $image_id);
    //                                     }
    //                                 }
    //                             }
    //                             if (isset($post['category_id'])) {
    //                                 if (count($post['category_id'])) {
    //                                     for ($x = 0; $x < count($post['category_id']); $x++) {
    //                                         BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
    //                                     }
    //                                 }
    //                             }
    //                             for ($g = 0; $g < count($languages); $g++) {
    //                                 $injectTransLation = array(
    //                                     'blog_id' => $id,
    //                                     'language_code' => $languages[$g],
    //                                     'title' => $postData['title'],
    //                                     'tags' => $postData['tags'],
    //                                     'description' => $postData['description'],
    //                                     'swipe_text' => $postData['swipe_text'],
    //                                     'seo_title' => $postData['seo_title'],
    //                                     'seo_keyword' => $postData['seo_keyword'],
    //                                     'seo_tag' => $postData['seo_tag'],
    //                                     'seo_description' => $postData['seo_description'],
    //                                     'created_at' => date("Y-m-d H:i:s"),
    //                                 );
    //                                 BlogTranslation::insertGetId($injectTransLation);
    //                             }
    //                             return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
    //                         } else {
    //                             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
    //                         }
    //                     }
    //                 } else {
    //                     if ($post['status'] == 2) {
    //                         if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                             if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                                 $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                                 $time = date("H:i:s", strtotime($post['schedule_time']));
    //                                 $postData['schedule_date'] = $date . " " . $time;
    //                             } else {
    //                                 $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                             }
    //                         }
    //                         $postData['status'] = 1;
    //                     }
    //                     if (isset($post['id']) && $post['id'] != '' & $post['id'] != 0) {
    //                         unset($postData['created_by']);
    //                         if (isset($post['image'])) {
    //                             if (count($post['image'])) {
    //                                 for ($v = 0; $v < count($post['image']); $v++) {
    //                                     $image_id = BlogImages::insert(array('blog_id' => $post['id'], 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                     array_push($add_blog_image, $image_id);
    //                                 }
    //                             }
    //                         }
    //                         if (isset($post['category_id'])) {
    //                             if (count($post['category_id'])) {
    //                                 $checkBlog = BlogCategory::where('blog_id', $post['id'])->delete();
    //                                 for ($z = 0; $z < count($post['category_id']); $z++) {
    //                                     BlogCategory::insertGetId(array('blog_id' => $post['id'], 'category_id' => $post['category_id'][$z], 'created_at' => date('Y-m-d H:i:s')));
    //                                 }
    //                             }
    //                         }
    //                         if (isset($post['slug'])) {
    //                             $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', $post['id'])->count();
    //                             if ($slugExist) {
    //                                 return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                             }
    //                             $slug = $post['slug'];
    //                         } else {
    //                             $slug = \Helpers::createSlug($postData['title'], 'blog', $post['id'], false);
    //                         }
    //                         $postData['slug'] = $slug;
    //                     if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                     $prefix = 'ab';
    //                     $numberDigits = 3;
    //                     $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     $blog_code = $prefix . $numberPart;
    //                     $postData['blog_code'] = $blog_code;
    //                 }
    //             if (isset($postData['language'])) {
    //                             unset($postData['language']);
    //                         }
    //                         Blog::where('id', $post['id'])->update($postData);
    //                         $blogtransExist = BlogTranslation::where('blog_id', $post['id'])->where('language_code', $post['language'])->first();
    //                         $injectTransLation = array(
    //                             'blog_id' => $post['id'],
    //                             'language_code' => $post['language'],
    //                             'title' => $postData['title'],
    //                             'tags' => $postData['tags'],
    //                             'description' => $postData['description'],
    //                             'swipe_text' => $postData['swipe_text'],
    //                             'seo_title' => $postData['seo_title'],
    //                             'seo_keyword' => $postData['seo_keyword'],
    //                             'seo_tag' => $postData['seo_tag'],
    //                             'seo_description' => $postData['seo_description'],
    //                         );
    //                         if ($blogtransExist) {
    //                             BlogTranslation::where('id', $blogtransExist->id)->update($injectTransLation);
    //                         } else {
    //                             $injectTransLation['created_at'] = date("Y-m-d H:i:s");
    //                             BlogTranslation::insertGetId($injectTransLation);
    //                         }
    //                     } else {
    //                         if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                             if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
    //                                 $date = date("Y-m-d", strtotime($post['schedule_date']));
    //                                 $time = date("H:i:s", strtotime($post['schedule_time']));
    //                                 $postData['schedule_date'] = $date . " " . $time;
    //                             } else {
    //                                 $postData['schedule_date'] = date("Y-m-d H:i:s");
    //                             }
    //                         }
    //                         if (isset($post['slug'])) {
    //                             $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
    //                             if ($slugExist) {
    //                                 return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
    //                             }
    //                             $slug = $post['slug'];
    //                         } else {
    //                             $slug = $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
    //                         }
    //                         $postData['slug'] = $slug;
    //                         unset($postData['category_id']);
    //                         $postData['created_at'] = date('Y-m-d H:i:s');
    //                   if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
    //                     $prefix = 'ab';
    //                     $numberDigits = 3;
    //                     $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
    //                     $blog_code = $prefix . $numberPart;
    //                     $postData['blog_code'] = $blog_code;
    //                 }
    //                         $id = Blog::insertGetId($postData);
    //                         $post['id'] = $id;
    //                         if ($id) {
    //                             for ($g = 0; $g < count($languages); $g++) {
    //                                 $injectTransLation = array(
    //                                     'blog_id' => $id,
    //                                     'language_code' => $languages[$g],
    //                                     'title' => $postData['title'],
    //                                     'tags' => $postData['tags'],
    //                                     'description' => $postData['description'],
    //                                     'swipe_text' => $postData['swipe_text'],
    //                                     'seo_title' => $postData['seo_title'],
    //                                     'seo_keyword' => $postData['seo_keyword'],
    //                                     'seo_tag' => $postData['seo_tag'],
    //                                     'seo_description' => $postData['seo_description'],
    //                                     'created_at' => date("Y-m-d H:i:s"),
    //                                 );
    //                                 BlogTranslation::insertGetId($injectTransLation);
    //                             }
    //                             if (isset($post['image'])) {
    //                                 if (count($post['image'])) {
    //                                     for ($v = 0; $v < count($post['image']); $v++) {
    //                                         $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
    //                                         array_push($add_blog_image, $image_id);
    //                                     }
    //                                 }
    //                             }
    //                             if (isset($post['category_id'])) {
    //                                 if (count($post['category_id'])) {
    //                                     for ($x = 0; $x < count($post['category_id']); $x++) {
    //                                         BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
    //                                     }
    //                                 }
    //                             }
    //                             $this->uploadPostOnSocial($id);
    //                             $blog_data = BlogImages::whereIn('id', $add_blog_image)->get();
    //                             foreach ($blog_data as $blog_image_notification) {
    //                                 if ($blog_image_notification->image != null || $blog_image_notification->image != '') {
    //                                     $blog_image_notification->image = url('upload/blog/banner/original/' . $blog_image_notification->image);
    //                                 } else {
    //                                     $blog_image_notification->image = url('upload/blog/banner/default.jpg');
    //                                 }
    //                             }
    //                             $fcmTokens = [];
    //                             if (setting('enable_notifications')) {
    //                                 if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
    //                                     // if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                         $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                         $non_logged_in = DeviceToken::get();
    //                                         if (count($non_logged_in)) {
    //                                             foreach ($non_logged_in as $non_logged_in_data) {
    //                                                 if ($non_logged_in_data->device_token != null) {
    //                                                     array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                                 }
    //                                             }
    //                                             if(count($fcmTokens))
    //                 {
    //                     \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                         CustomNotification::create([
    //                             'title' => $blog->title,
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                         }
    //                                 } else {

    //                                     $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                     $non_logged_in = DeviceToken::get();
    //                                     if (count($non_logged_in)) {
    //                                         foreach ($non_logged_in as $non_logged_in_data) {
    //                                             if ($non_logged_in_data->device_token != null) {
    //                                                 array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                             }
    //                                         }
    //                                         if(count($fcmTokens))
    //                 {
    //                      \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);

    //                         CustomNotification::create([
    //                             'title' =>$postData['title'],
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                     }
    //                                 }
    //                             }
    //                         } else {
    //                             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
    //                         }
    //                     }
    //                     if ($post['status'] == 2) {
    //                         if (isset($post['image'])) {
    //                             $image = url('upload/blog/banner/default.jpg');
    //                             $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
    //                             if ($blogImageInfo) {
    //                                 $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //                             }
    //                             if (setting('enable_notifications')) {
    //                                 if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                     $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                     $non_logged_in = DeviceToken::get();
    //                                     if (count($non_logged_in)) {
    //                                         foreach ($non_logged_in as $non_logged_in_data) {
    //                                             if ($non_logged_in_data->device_token != null) {
    //                                                 array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                             }
    //                                         }
    //                                         if(count($fcmTokens))
    //                 {
    //                      \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                         CustomNotification::create([
    //                             'title' => $postData['title'],
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                     }
    //                                 } else {
    //                                     $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                     $non_logged_in = DeviceToken::get();
    //                                     if (count($non_logged_in)) {
    //                                         foreach ($non_logged_in as $non_logged_in_data) {
    //                                             if ($non_logged_in_data->device_token != null) {
    //                                                 array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                             }
    //                                         }
    //                                         if(count($fcmTokens))
    //                 {
    //                      \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                         CustomNotification::create([
    //                             'title' => $postData['title'],
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                     }
    //                                 }
    //                             }
    //                         } else {
    //                             $image = url('upload/blog/banner/default.jpg');
    //                             $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
    //                             if ($blogImageInfo) {
    //                                 $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //                             }
    //                             if (setting('enable_notifications')) {
    //                                 if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
    //                                     $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                     $non_logged_in = DeviceToken::get();
    //                                     if (count($non_logged_in)) {
    //                                         foreach ($non_logged_in as $non_logged_in_data) {
    //                                             if ($non_logged_in_data->device_token != null) {
    //                                                 array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                             }
    //                                         }
    //                                         if(count($fcmTokens))
    //                 {
    //                      \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                         CustomNotification::create([
    //                             'title' =>$postData['title'],
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                     }
    //                                 } else {
    //                                     $setting = SiteContent::where('key', 'firebase_msg_key')->first();
    //                                     $non_logged_in = DeviceToken::get();
    //                                     if (count($non_logged_in)) {
    //                                         foreach ($non_logged_in as $non_logged_in_data) {
    //                                             if ($non_logged_in_data->device_token != null) {
    //                                                 array_push($fcmTokens, $non_logged_in_data->device_token);
    //                                             }
    //                                         }
    //                                         if(count($fcmTokens))
    //                 {
    //                      \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);
    //                         CustomNotification::create([
    //                             'title' => $postData['title'],
    //                             'post_id' => $id,
    //                             'type' => 'All'
    //                         ]);
    //                 }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                         $this->uploadPostOnSocial($post['id']);
    //                     }
    //                     return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
    //                 }
    //             } else {
    //                 return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
    //             }
    //         } catch (\Exception $ex) {
    //             return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error') . $ex));
    //         }
    //     }
    function wordCountClean($text)
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);

        // remove punctuation but keep letters and numbers
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

        // normalize spaces
        $text = preg_replace('/\s+/', ' ', trim($text));

        return count(explode(' ', $text));
    }
    public function addUpdateblog(Request $request)
    {

        $send_notification = false;
        try {
            if (!$request->ajax()) {
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }

            $post = $request->all();
            $detail = Blog::where('id', $post['id'] ?? 0)->first();

            // description validation (word count)
            $validate = [
                'description' => [
                    'sometimes',
                    'required',
                    function ($attribute, $value, $fail) {
                        $max_word = SiteContent::where('key', 'news_max_words')->first()->value ?? 60;
                        $max_words = $max_word;
                        $words = $this->wordCountClean($value);
                        if ($words > $max_words) {
                            $fail('The ' . $attribute . ' may not be more than ' . ($max_words) . ' words.');
                        }
                    },
                ],
                'description_hi' => [
                    'sometimes',
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if (empty(trim((string) strip_tags((string) $value)))) return;
                        $max_word = SiteContent::where('key', 'news_max_words')->first()->value ?? 60;
                        $max_words = $max_word;
                        $words = $this->wordCountClean($value);
                        if ($words > $max_words) {
                            $fail('The Hindi Description may not be more than ' . ($max_words) . ' words.');
                        }
                    },
                ],
            ];
            $validator = Validator::make($post, $validate, [], [
                'description' => 'English Description',
                'description_hi' => 'Hindi Description'
            ]);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing'), $data));
            }

            // remove unnecessary fields
            $post = $request->except(['tone', 'creativity', 'sentimate', 'words', 'location', 'translate']);

            // Determine submit type early so we can skip side-effects (schedule/status) for partial saves
            $submittype = (!isset($post['submittype']) || $post['submittype'] === null || $post['submittype'] === '') ? 'save' : (string) $post['submittype'];

            $actorId = Auth::check() ? (int) Auth::id() : null;
            $canChangeStatus = Auth::check() && Auth::user() && Auth::user()->can('blog-status');
            $requestId = isset($post['id']) ? (int) $post['id'] : 0;
            $originalStatus = $detail ? (int) $detail->status : null;
            $editLang = isset($post['edit_lang']) ? trim((string) $post['edit_lang']) : '';
            $providedHindiContent =
                (isset($post['title_hi']) && trim((string) $post['title_hi']) !== '') ||
                (isset($post['description_hi']) && trim((string) strip_tags((string) $post['description_hi'])) !== '');

            // For the top "Update" button on edit page: save basic details only, do not publish/change status/schedule.
            if ($submittype === 'update_only' && $detail) {
                $post['status'] = $detail->status;
                $post['slug'] = $detail->slug;
                unset(
                    $post['schedule_date'],
                    $post['schedule_time'],
                    $post['is_featured'],
                    $post['is_slider'],
                    $post['is_editor_picks'],
                    $post['is_weekly_top_picks'],
                    $post['is_voting_enable'],
                    $post['VotingQuestion'],
                    $post['optiontype'],
                    $post['audio_file_upload'],
                    $post['url'],
                    $post['source_name'],
                    $post['video_url'],
                    $post['voice'],
                    $post['blog_accent_code'],
                    $post['seo_title'],
                    $post['seo_keyword'],
                    $post['seo_tag'],
                    $post['seo_description']
                );
            }

            // Full save without changing status (requested "Save" button in edit).
            if ($submittype === 'save_keep_status' && $detail) {
                $post['status'] = $detail->status;
            }

            // Normalize schedule datetime:
            // - If user selects a past date/time, bump it to "now"
            // - If user selects a future date/time, keep it as-is
            if (
                !in_array($submittype, ['update_only'], true) &&
                isset($post['schedule_date']) &&
                trim((string) $post['schedule_date']) !== ''
            ) {
                $dateStr = trim((string) $post['schedule_date']);
                $timeStr = (isset($post['schedule_time']) && trim((string) $post['schedule_time']) !== '')
                    ? trim((string) $post['schedule_time'])
                    : '00:00';

                if (!isset($post['schedule_time']) || trim((string) $post['schedule_time']) === '') {
                    $post['schedule_time'] = $timeStr;
                }

                $scheduleAt = null;
                try {
                    $scheduleAt = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $timeStr);
                } catch (\Exception $ex) {
                    try {
                        $scheduleAt = Carbon::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $timeStr);
                    } catch (\Exception $ex2) {
                        $scheduleAt = null;
                    }
                }

                if ($scheduleAt) {
                    $now = Carbon::now();
                    if ($scheduleAt->lt($now)) {
                        $post['schedule_date'] = $now->format('Y-m-d');
                        $post['schedule_time'] = $now->format('H:i');
                    }
                }
            }

            // languages (expecting language_code as array)
            $languages = [];
            if (isset($post['language_code'])) {
                $languages = is_array($post['language_code']) ? $post['language_code'] : [$post['language_code']];
                unset($post['language_code']);
            }
            if (isset($post['language[]'])) {
                unset($post['language[]']);
            }

            $data['prefield'] = $post;

            $add_blog_image = [];
            unset($post['_token']);
            if (isset($post['submittype'])) {
                unset($post['submittype']);
            }

            // Status is admin-controlled: non-admin updates should never change it.
            if (!isset($post['status']) || $post['status'] === null || $post['status'] === '') {
                $post['status'] = $detail ? $detail->status : 2;
            }
            if (!$canChangeStatus) {
                $post['status'] = $detail ? $detail->status : 2;
            }

            $postData = $post;

            // Translation fields (edit page sends both English + Hindi)
            $translationsInput = [
                'en' => [
                    'title' => $postData['title_en'] ?? ($postData['title'] ?? null),
                    'description' => $postData['description_en'] ?? ($postData['description'] ?? null),
                ],
                'hi' => [
                    'title' => $postData['title_hi'] ?? null,
                    'description' => $postData['description_hi'] ?? null,
                ],
            ];
            // Always keep Blog table in English
            if (array_key_exists('title_en', $postData)) {
                $postData['title'] = $postData['title_en'];
            }
            if (array_key_exists('description_en', $postData)) {
                $postData['description'] = $postData['description_en'];
            }
            unset($postData['title_en'], $postData['title_hi'], $postData['description_en'], $postData['description_hi'], $postData['edit_lang']);

            // Text-to-speech: single "Accent / Voice" select in UI.
            // Persist BOTH columns: `blog_accent_code` and `voice` (derived from accent).
            $accent = isset($postData['blog_accent_code']) ? trim((string) $postData['blog_accent_code']) : '';
            $voiceKey = isset($postData['voice']) ? trim((string) $postData['voice']) : '';

            // Backwards-compatible: if a voice is provided but accent is missing, infer accent (if possible).
            if ($accent === '' && $voiceKey !== '') {
                $derivedAccent = \Helpers::resolveBlogSpeechAccentForVoice($voiceKey);
                if (is_string($derivedAccent) && $derivedAccent !== '') {
                    $accent = $derivedAccent;
                    $postData['blog_accent_code'] = $derivedAccent;
                }
            }

            // If an accent is present, always derive voice from it (single supported voice per accent).
            if ($accent !== '') {
                $resolvedVoice = \Helpers::resolveBlogSpeechVoiceForAccent($accent);
                if (is_string($resolvedVoice) && $resolvedVoice !== '') {
                    $postData['voice'] = $resolvedVoice;
                }
            } elseif ($detail) {
                // Prevent overwriting existing values with empty selection on edit.
                unset($postData['blog_accent_code'], $postData['voice']);
            }

            if (isset($postData['image'])) {
                unset($postData['image']);
            }

            // handle reimage copy and insert
            if (isset($postData['reimage'])) {
                $reimage_id = $postData['reimage'];
                $reimageData = BlogReimage::where('id', $reimage_id)->first();
                if ($reimageData) {
                    $image_name = $reimageData->image;
                    $blog_id = $reimageData->blog_id;

                    $old_path = public_path('upload/blog/banner/temp_banner/' . $image_name);
                    $new_path1 = public_path('upload/blog/banner/original/' . $image_name);
                    $new_path2 = public_path('upload/blog/banner/360/' . $image_name);

                    if (File::exists($old_path)) {
                        File::copy($old_path, $new_path1);
                        File::copy($old_path, $new_path2);
                    }

                    BlogImages::insert(['blog_id' => $blog_id, 'image' => $image_name, 'created_at' => date('Y-m-d H:i:s')]);
                }
                unset($postData['reimage']);
            }

            if ($submittype !== 'update_only') {
                // flags and fields normalization
                $postData['is_featured'] = (isset($post['is_featured']) && $post['is_featured'] == 'on') ? 1 : 0;
                if (isset($post['audio_file_upload'])) {
                    $postData['audio_file'] = $post['audio_file_upload'];
                }

                if (isset($post['video_url']) && $post['video_url'] != '') {
                    $postData['content_type'] = 'video';
                } elseif (isset($post['audio_file_upload']) && $post['audio_file_upload'] != '') {
                    $postData['content_type'] = 'audio';
                } else {
                    $postData['content_type'] = 'text';
                }

                $postData['is_slider'] = (isset($post['is_slider']) && $post['is_slider'] == 'on') ? 1 : 0;
                $postData['is_editor_picks'] = (isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on') ? 1 : 0;
                $postData['is_weekly_top_picks'] = (isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on') ? 1 : 0;
                $postData['is_voting_enable'] = (isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on') ? 1 : 0;

                $postData['created_by'] = Auth::User()->id;

                // schedule combine
                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                    if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                        $date = date("Y-m-d", strtotime($post['schedule_date']));
                        $time = date("H:i:s", strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date . " " . $time;
                    }
                } else {
                    $postData['schedule_date'] = date("Y-m-d H:i:s");
                }

                unset($postData['schedule_time']);
                unset($postData['audio_file_upload']);
            } else {
                // basic update: avoid overwriting schedule/visibility/tts/seo/audio/source fields
                unset($postData['schedule_date'], $postData['schedule_time'], $postData['audio_file_upload']);
            }

            // helper: prepare translation rows to insert
            $prepareTranslationRow = function ($blogId, $lang, $postDataArr) {
                return [
                    'blog_id' => $blogId,
                    'language_code' => $lang,
                    'title' => $postDataArr['title'] ?? null,
                    'tags' => $postDataArr['tags'] ?? null,
                    'description' => $postDataArr['description'] ?? null,
                    'seo_title' => $postDataArr['seo_title'] ?? null,
                    'seo_keyword' => $postDataArr['seo_keyword'] ?? null,
                    'seo_tag' => $postDataArr['seo_tag'] ?? null,
                    'seo_description' => $postDataArr['seo_description'] ?? null,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ];
            };

            $upsertBlogTranslation = function (int $blogId, string $lang, array $values) {
                $now = date("Y-m-d H:i:s");
                $existing = BlogTranslation::where('blog_id', $blogId)->where('language_code', $lang)->first();
                if ($existing) {
                    $values['updated_at'] = $now;
                    BlogTranslation::where('id', $existing->id)->update($values);
                    return $existing->id;
                }
                $values['blog_id'] = $blogId;
                $values['language_code'] = $lang;
                $values['created_at'] = $now;
                $values['updated_at'] = $now;
                return BlogTranslation::insertGetId($values);
            };

            // ---------- DRAFT ----------
            if ($submittype == 'draft') {

                $validate = [
                    'title' => 'required',
                    'slug' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                    $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), $data['prefield']));
                }

                if (isset($post['slug'])) {
                    $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
                    if ($slugExist) {
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                    }
                    $slug = $post['slug'];
                } else {
                    $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
                }

                $postData['slug'] = $slug;
                $postData['status'] = 2;
                $postData['created_at'] = date('Y-m-d H:i:s');

                $id = Blog::insertGetId($postData);

                if ($id) {
                    // images
                    if (isset($post['image']) && count($post['image'])) {
                        for ($v = 0; $v < count($post['image']); $v++) {
                            $image_id = BlogImages::insertGetId(['blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')]);
                            array_push($add_blog_image, $image_id);
                        }
                    }

                    // categories
                    if (isset($post['category_id']) && count($post['category_id'])) {
                        for ($x = 0; $x < count($post['category_id']); $x++) {
                            BlogCategory::insertGetId(['blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')]);
                        }
                    }

                    // insert translations for languages
                    if (!empty($languages)) {
                        foreach ($languages as $lang) {
                            $row = $prepareTranslationRow($id, $lang, $postData);
                            // BlogTranslation::insertGetId($row);
                        }
                    } else {
                        // if no languages provided, insert default 'en' translation (optional)
                        $row = $prepareTranslationRow($id, 'en', $postData);
                        // BlogTranslation::insertGetId($row);
                    }

                    BlogActionLog::record('blog_draft_created', (int) $id, $actorId, [
                        'source' => 'admin_blog_form',
                        'submittype' => 'draft',
                        'status' => 2,
                        'is_featured' => (int) ($postData['is_featured'] ?? 0),
                    ]);

                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                } else {
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                }
            }

            // ---------- Non-draft: if status==2 convert to published (admin-only) ----------
            if ($canChangeStatus && !in_array($submittype, ['update_only', 'save_keep_status'], true) && (int) $post['status'] === 2) {
                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                    if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                        $date = date("Y-m-d", strtotime($post['schedule_date']));
                        $time = date("H:i:s", strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date . " " . $time;
                    } else {
                        // $postData['schedule_date'] = date("Y-m-d H:i:s");
                    }
                }
                $postData['status'] = 1;
                if (isset($postData['is_featured'])) {
                    if ($postData['is_featured'] == 1) {
                        $send_notification = true;
                        if (isset($postData['schedule_date'])) {
                            if (strtotime($postData['schedule_date']) <= strtotime(date("Y-m-d H:i:s"))) {
                                $this->sendBlogNotification_unpublish($post['id'], $postData['title']);
                            }
                        } else {
                            $this->sendBlogNotification_unpublish($post['id'], $postData['title']);
                        }
                    }
                }
            }

            // ---------- EDIT ----------
            if (isset($post['id']) && $post['id'] != '' && $post['id'] != 0) {
                unset($postData['created_by']);

                // add new images if any
                if (isset($post['image']) && count($post['image'])) {
                    for ($v = 0; $v < count($post['image']); $v++) {
                        $image_id = BlogImages::insert(['blog_id' => $post['id'], 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')]);
                        array_push($add_blog_image, $image_id);
                    }
                }

                // categories - delete existing and insert new
                if (isset($post['category_id']) && count($post['category_id'])) {
                    BlogCategory::where('blog_id', $post['id'])->delete();
                    for ($z = 0; $z < count($post['category_id']); $z++) {
                        BlogCategory::insertGetId(['blog_id' => $post['id'], 'category_id' => $post['category_id'][$z], 'created_at' => date('Y-m-d H:i:s')]);
                    }
                }

                // slug check/generate
                if (isset($post['slug'])) {
                    $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', $post['id'])->count();
                    if ($slugExist) {
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                    }
                    $slug = $post['slug'];
                } else {
                    $slug = \Helpers::createSlug($postData['title'], 'blog', $post['id'], false);
                }
                $postData['slug'] = $slug;

                if (isset($postData['language'])) {
                    unset($postData['language']);
                }

                // transaction: update blog, delete old translations, insert new translations
                DB::beginTransaction();
                try {
                    Blog::where('id', $post['id'])->update($postData);

                    $enTitle = $translationsInput['en']['title'] ?? ($postData['title'] ?? null);
                    $enDescription = $translationsInput['en']['description'] ?? ($postData['description'] ?? null);
                    $hiTitle = $translationsInput['hi']['title'] ?? '';
                    $hiDescription = $translationsInput['hi']['description'] ?? '';

                    // If Hindi fields are empty, keep a usable fallback instead of inserting blanks
                    if (trim((string) $hiTitle) === '') {
                        $hiTitle = $enTitle;
                    }
                    if (trim((string) strip_tags((string) $hiDescription)) === '') {
                        $hiDescription = $enDescription;
                    }

                    $upsertBlogTranslation((int) $post['id'], 'en', [
                        'title' => $enTitle,
                        'description' => $enDescription,
                    ]);
                    $upsertBlogTranslation((int) $post['id'], 'hi', [
                        'title' => $hiTitle,
                        'description' => $hiDescription,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Blog update failed: ' . $e->getMessage());
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                }

                $blog_data = BlogImages::whereIn('id', $add_blog_image)->get();

                // (notification logic unchanged, kept if you need it)
                if (isset($postData['is_featured']) && !in_array($submittype, ['update_only', 'save_keep_status'])) {
                    if ($postData['is_featured'] == 1) {
                        $send_notification = true;
                        $this->sendBlogNotification_unpublish($post['id'], $postData['title']);
                    }
                }
            } else {
                // ---------- CREATE ----------
                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                    if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                        $date = date("Y-m-d", strtotime($post['schedule_date']));
                        $time = date("H:i:s", strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date . " " . $time;
                    } else {
                        $postData['schedule_date'] = date("Y-m-d H:i:s");
                    }
                }

                if (isset($post['slug'])) {
                    $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
                    if ($slugExist) {
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                    }
                    $slug = $post['slug'];
                } else {
                    $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
                }

                $postData['slug'] = $slug;
                unset($postData['category_id']);
                $postData['created_at'] = date('Y-m-d H:i:s');

                $id = Blog::insertGetId($postData);
                $post['id'] = $id;

                if ($id) {
                    // create translations
                    if (!empty($languages)) {
                        foreach ($languages as $lang) {
                            $row = $prepareTranslationRow($id, $lang, $postData);
                            BlogTranslation::insertGetId($row);
                        }
                    } else {
                        $row = $prepareTranslationRow($id, 'en', $postData);
                        BlogTranslation::insertGetId($row);
                    }

                    // Ensure English + Hindi rows exist (Hindi falls back to English if empty)
                    $enTitle = $translationsInput['en']['title'] ?? ($postData['title'] ?? null);
                    $enDescription = $translationsInput['en']['description'] ?? ($postData['description'] ?? null);
                    $hiTitle = $translationsInput['hi']['title'] ?? '';
                    $hiDescription = $translationsInput['hi']['description'] ?? '';
                    if (trim((string) $hiTitle) === '') {
                        $hiTitle = $enTitle;
                    }
                    if (trim((string) strip_tags((string) $hiDescription)) === '') {
                        $hiDescription = $enDescription;
                    }
                    $upsertBlogTranslation((int) $id, 'en', [
                        'title' => $enTitle,
                        'description' => $enDescription,
                    ]);
                    $upsertBlogTranslation((int) $id, 'hi', [
                        'title' => $hiTitle,
                        'description' => $hiDescription,
                    ]);

                    // images
                    if (isset($post['image']) && count($post['image'])) {
                        for ($v = 0; $v < count($post['image']); $v++) {
                            $image_id = BlogImages::insertGetId(['blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')]);
                            array_push($add_blog_image, $image_id);
                        }
                    }

                    // categories
                    if (isset($post['category_id']) && count($post['category_id'])) {
                        for ($x = 0; $x < count($post['category_id']); $x++) {
                            BlogCategory::insertGetId(['blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')]);
                        }
                    }

                    $this->uploadPostOnSocial($id);

                    $blog_data = BlogImages::whereIn('id', $add_blog_image)->get();
                    foreach ($blog_data as $blog_image_notification) {
                        if ($blog_image_notification->image != null || $blog_image_notification->image != '') {
                            $blog_image_notification->image = url('upload/blog/banner/original/' . $blog_image_notification->image);
                        } else {
                            $blog_image_notification->image = url('upload/blog/banner/default.jpg');
                        }
                    }

                    // notification logic unchanged
                } else {
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                }
            }

            // Send Notifications (unchanged)
            $postStatus = (int) ($post['status'] ?? 0);
            $isFeatured = (int) ($postData['is_featured'] ?? 0);
            if ($postStatus === 2 && $isFeatured === 1) {
                $this->uploadPostOnSocial($post['id']);
            }

            $savedId = isset($post['id']) ? (int) $post['id'] : 0;
            if ($savedId > 0) {
                $meta = [
                    'source' => 'admin_blog_form',
                    'submittype' => $submittype,
                    'is_featured' => (int) ($postData['is_featured'] ?? 0),
                ];

                if ($requestId > 0) {
                    BlogActionLog::record('blog_updated', $savedId, $actorId, $meta);
                } else {
                    $meta['status'] = (int) ($postData['status'] ?? 0);
                    BlogActionLog::record('blog_created', $savedId, $actorId, $meta);
                }

                $toStatus = isset($postData['status']) ? (int) $postData['status'] : null;
                if ($originalStatus !== null && $toStatus !== null && $toStatus !== $originalStatus) {
                    BlogActionLog::record('blog_status_changed', $savedId, $actorId, [
                        'source' => 'admin_blog_form',
                        'from_status' => $originalStatus,
                        'to_status' => $toStatus,
                    ]);
                }

                $translatedLangs = [];
                if ($editLang !== '' && $editLang !== 'en') {
                    $translatedLangs[] = $editLang;
                }
                if ($providedHindiContent) {
                    $translatedLangs[] = 'hi';
                }
                $translatedLangs = array_values(array_unique(array_filter($translatedLangs)));
                if (!empty($translatedLangs)) {
                    BlogActionLog::record('blog_translated', $savedId, $actorId, [
                        'source' => 'admin_blog_form',
                        'languages' => $translatedLangs,
                    ]);
                }
            }

            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
        } catch (\Exception $ex) {
            \Log::error('addUpdateblog exception: ' . $ex->getMessage());
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error') . $ex));
        }
    }


    public function addUpdateblog_old(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->ajax()) {

                $post = $request->all();
                if (isset($post['schedule_date'])) {
                    if (date('Y-m-d', strtotime($post['schedule_date'])) < date('Y-m-d')) {
                        return response(\Helpers::sendFailureAjaxResponse(__('Shedule date must be grater than or equal to current date'), []));
                    }
                }
                $languages = [];
                if (isset($post['language_code'])) {
                    $languages = $post['language_code'];
                    unset($post['language_code']);
                }
                if (isset($post['language[]'])) {
                    unset($post['language[]']);
                }
                $data['prefield'] = $post;
                $submittype = (!isset($post['submittype'])) ? 'save' : $post['submittype'];
                $add_blog_image = array();
                unset($post['_token']);
                if (isset($post['submittype'])) {
                    unset($post['submittype']);
                }
                if (!isset($post['status'])) {
                    $post['status'] = 1;
                }
                $postData = $post;
                if (isset($postData['image'])) {
                    unset($postData['image']);
                }
                if (isset($post['is_featured']) && $post['is_featured'] == 'on') {
                    $postData['is_featured'] = 1;
                } else {
                    $postData['is_featured'] = 0;
                }
                if (isset($post['audio_file_upload'])) {
                    $postData['audio_file'] = $post['audio_file_upload'];
                }
                if (isset($post['video_url']) && $post['video_url'] != '') {
                    $postData['content_type'] = 'video';
                } elseif (isset($post['audio_file_upload']) && $post['audio_file_upload'] != '') {
                    $postData['content_type'] = 'audio';
                } else {
                    $postData['content_type'] = 'text';
                }
                if (isset($post['is_slider']) && $post['is_slider'] == 'on') {
                    $postData['is_slider'] = 1;
                } else {
                    $postData['is_slider'] = 0;
                }
                if (isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on') {
                    $postData['is_editor_picks'] = 1;
                } else {
                    $postData['is_editor_picks'] = 0;
                }
                if (isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on') {
                    $postData['is_weekly_top_picks'] = 1;
                } else {
                    $postData['is_weekly_top_picks'] = 0;
                }
                if (isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on') {
                    $postData['is_voting_enable'] = 1;
                } else {
                    $postData['is_voting_enable'] = 0;
                }
                $postData['created_by'] = Auth::User()->id;
                if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
                    $prefix = 'ab';
                    $numberDigits = 3;
                    $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
                    $blog_code = $prefix . $numberPart;
                    $postData['blog_code'] = $blog_code;
                }
                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                    if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                        $date = date("Y-m-d", strtotime($post['schedule_date']));
                        $time = date("H:i:s", strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date . " " . $time;
                    }
                } else {
                    $postData['schedule_date'] = date("Y-m-d H:i:s");
                }
                unset($postData['schedule_time']);
                unset($postData['audio_file_upload']);
                if ($submittype == 'draft') {
                    $validate = [
                        'title' => 'required',
                        'slug' => 'required',
                    ];
                    $validator = Validator::make($post, $validate);
                    if ($validator->fails()) {
                        $data['error'] = $validator->errors();
                        $error = '';
                        $errors = (array) $data['error'];
                        foreach ($errors as $row) {
                            foreach ($validate as $key => $value) {
                                if (isset($row[$key])) {
                                    $error = $row[$key];
                                }
                            }
                        }
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), $data['prefield']));
                    } else {

                        if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                            if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                                $date = date("Y-m-d", strtotime($post['schedule_date']));
                                $time = date("H:i:s", strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date . " " . $time;
                            } else {
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }
                        }
                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                            }
                            $slug = $post['slug'];
                        } else {
                            $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
                        }
                        if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
                            $prefix = 'ab';
                            $numberDigits = 3;
                            $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
                            $blog_code = $prefix . $numberPart;
                            $postData['blog_code'] = $blog_code;
                        }
                        $postData['slug'] = $slug;
                        $postData['status'] = 2;
                        $postData['created_at'] = date('Y-m-d H:i:s');
                        $id = Blog::insertGetId($postData);
                        if ($id) {
                            if (isset($post['image'])) {
                                if (count($post['image'])) {
                                    for ($v = 0; $v < count($post['image']); $v++) {
                                        $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
                                        array_push($add_blog_image, $image_id);
                                    }
                                }
                            }
                            if (isset($post['category_id'])) {
                                if (count($post['category_id'])) {
                                    for ($x = 0; $x < count($post['category_id']); $x++) {
                                        BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
                                    }
                                }
                            }
                            for ($g = 0; $g < count($languages); $g++) {
                                $injectTransLation = array(
                                    'blog_id' => $id,
                                    'language_code' => $languages[$g],
                                    'title' => $postData['title'],
                                    'tags' => $postData['tags'],
                                    'description' => $postData['description'],
                                    'swipe_text' => $postData['swipe_text'],
                                    'seo_title' => $postData['seo_title'],
                                    'seo_keyword' => $postData['seo_keyword'],
                                    'seo_tag' => $postData['seo_tag'],
                                    'seo_description' => $postData['seo_description'],
                                    'created_at' => date("Y-m-d H:i:s"),
                                );
                                BlogTranslation::insertGetId($injectTransLation);
                            }
                            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                        } else {
                            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                        }
                    }
                } else {
                    if ($post['status'] == 2) {
                        if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                            if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                                $date = date("Y-m-d", strtotime($post['schedule_date']));
                                $time = date("H:i:s", strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date . " " . $time;
                            } else {
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }
                        }
                        $postData['status'] = 1;
                    }
                    if (isset($post['id']) && $post['id'] != '' & $post['id'] != 0) {
                        unset($postData['created_by']);
                        if (isset($post['image'])) {
                            if (count($post['image'])) {
                                for ($v = 0; $v < count($post['image']); $v++) {
                                    $image_id = BlogImages::insert(array('blog_id' => $post['id'], 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
                                    array_push($add_blog_image, $image_id);
                                }
                            }
                        }
                        if (isset($post['category_id'])) {
                            if (count($post['category_id'])) {
                                $checkBlog = BlogCategory::where('blog_id', $post['id'])->delete();
                                for ($z = 0; $z < count($post['category_id']); $z++) {
                                    BlogCategory::insertGetId(array('blog_id' => $post['id'], 'category_id' => $post['category_id'][$z], 'created_at' => date('Y-m-d H:i:s')));
                                }
                            }
                        }
                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', $post['id'])->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                            }
                            $slug = $post['slug'];
                        } else {
                            $slug = \Helpers::createSlug($postData['title'], 'blog', $post['id'], false);
                        }
                        $postData['slug'] = $slug;
                        if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
                            $prefix = 'ab';
                            $numberDigits = 3;
                            $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
                            $blog_code = $prefix . $numberPart;
                            $postData['blog_code'] = $blog_code;
                        }
                        if (isset($postData['language'])) {
                            unset($postData['language']);
                        }
                        Blog::where('id', $post['id'])->update($postData);
                        $blogtransExist = BlogTranslation::where('blog_id', $post['id'])->where('language_code', $post['language'])->first();
                        $injectTransLation = array(
                            'blog_id' => $post['id'],
                            'language_code' => $post['language'],
                            'title' => $postData['title'],
                            'tags' => $postData['tags'],
                            'description' => $postData['description'],
                            'swipe_text' => $postData['swipe_text'],
                            'seo_title' => $postData['seo_title'],
                            'seo_keyword' => $postData['seo_keyword'],
                            'seo_tag' => $postData['seo_tag'],
                            'seo_description' => $postData['seo_description'],
                        );
                        if ($blogtransExist) {
                            BlogTranslation::where('id', $blogtransExist->id)->update($injectTransLation);
                        } else {
                            $injectTransLation['created_at'] = date("Y-m-d H:i:s");
                            BlogTranslation::insertGetId($injectTransLation);
                        }
                    } else {
                        if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                            if (isset($post['schedule_time']) && $post['schedule_time'] != '') {
                                $date = date("Y-m-d", strtotime($post['schedule_date']));
                                $time = date("H:i:s", strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date . " " . $time;
                            } else {
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }
                        }
                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->where('id', '!=', 0)->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                            }
                            $slug = $post['slug'];
                        } else {
                            $slug = $slug = \Helpers::createSlug($postData['title'], 'blog', 0, false);
                        }
                        $postData['slug'] = $slug;
                        unset($postData['category_id']);
                        $postData['created_at'] = date('Y-m-d H:i:s');
                        if (isset($post['category_id']) && in_array(12, $post['category_id'])) {
                            $prefix = 'ab';
                            $numberDigits = 3;
                            $numberPart = str_pad(mt_rand(1, pow(10, $numberDigits) - 1), $numberDigits, '0', STR_PAD_LEFT);
                            $blog_code = $prefix . $numberPart;
                            $postData['blog_code'] = $blog_code;
                        }
                        $id = Blog::insertGetId($postData);
                        $post['id'] = $id;
                        if ($id) {
                            for ($g = 0; $g < count($languages); $g++) {
                                $injectTransLation = array(
                                    'blog_id' => $id,
                                    'language_code' => $languages[$g],
                                    'title' => $postData['title'],
                                    'tags' => $postData['tags'],
                                    'description' => $postData['description'],
                                    'swipe_text' => $postData['swipe_text'],
                                    'seo_title' => $postData['seo_title'],
                                    'seo_keyword' => $postData['seo_keyword'],
                                    'seo_tag' => $postData['seo_tag'],
                                    'seo_description' => $postData['seo_description'],
                                    'created_at' => date("Y-m-d H:i:s"),
                                );
                                BlogTranslation::insertGetId($injectTransLation);
                            }
                            if (isset($post['image'])) {
                                if (count($post['image'])) {
                                    for ($v = 0; $v < count($post['image']); $v++) {
                                        $image_id = BlogImages::insertGetId(array('blog_id' => $id, 'image' => $post['image'][$v], 'created_at' => date('Y-m-d H:i:s')));
                                        array_push($add_blog_image, $image_id);
                                    }
                                }
                            }
                            if (isset($post['category_id'])) {
                                if (count($post['category_id'])) {
                                    for ($x = 0; $x < count($post['category_id']); $x++) {
                                        BlogCategory::insertGetId(array('blog_id' => $id, 'category_id' => $post['category_id'][$x], 'created_at' => date('Y-m-d H:i:s')));
                                    }
                                }
                            }
                            $this->uploadPostOnSocial($id);
                            $blog_data = BlogImages::whereIn('id', $add_blog_image)->get();
                            foreach ($blog_data as $blog_image_notification) {
                                if ($blog_image_notification->image != null || $blog_image_notification->image != '') {
                                    $blog_image_notification->image = url('upload/blog/banner/original/' . $blog_image_notification->image);
                                } else {
                                    $blog_image_notification->image = url('upload/blog/banner/default.jpg');
                                }
                            }
                            $fcmTokens = [];
                            if (setting('enable_notifications')) {
                                if (isset($post['schedule_date']) && $post['schedule_date'] != '') {
                                    // if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $blog_data[0]->image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => strval($id), // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $id,
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $id,
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                } else {

                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $blog_data[0]->image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => strval($id), // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $id,
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $blog_data[0]->image, $id);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $id,
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                        }
                    }
                    if ($post['status'] == 2) {
                        if (isset($post['image'])) {
                            $image = url('upload/blog/banner/default.jpg');
                            $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
                            if ($blogImageInfo) {
                                $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
                            }
                            $fcmTokens = [];
                            if (setting('enable_notifications')) {
                                if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => $post['id'], // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $post['id'],
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $image, $post['id']);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $post['id'],
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                } else {
                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => strval($post['id']), // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $post['id'],
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $image, $post['id']);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $post['id'],
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $image = url('upload/blog/banner/default.jpg');
                            $blogImageInfo = BlogImages::where('blog_id', $post['id'])->first();
                            if ($blogImageInfo) {
                                $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
                            }
                            $fcmTokens = [];
                            if (setting('enable_notifications')) {
                                if (date("Y-m-d H:i", strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")) {
                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => strval($post['id']), // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $post['id'],
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $image, $post['id']);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $post['id'],
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                } else {
                                    $setting = SiteContent::where('key', 'firebase_msg_key')->first();
                                    $non_logged_in = DeviceToken::get();
                                    if (count($non_logged_in)) {
                                        foreach ($non_logged_in as $non_logged_in_data) {
                                            if ($non_logged_in_data->device_token != null) {
                                                array_push($fcmTokens, $non_logged_in_data->device_token);
                                            }
                                        }
                                        // if (count($fcmTokens)) {
                                        //     $data = [
                                        //         'title' => $postData['title'], // Optional title for reference in app
                                        //         'imageUrl' => $image, // Optional image URL for reference in app (retrieved earlier)
                                        //         'id' => strval($post['id']), // Add custom data for your app
                                        //     ];
                                        //     \Helpers::sendNotification($fcmTokens, null, null, $setting->value, $data);
                                        //     CustomNotification::create([
                                        //         'title' => $postData['title'],
                                        //         'post_id' => $post['id'],
                                        //         'type' => 'All'
                                        //     ]);
                                        // }
                                        if (count($fcmTokens)) {
                                            \Helpers::sendNotification($fcmTokens, $postData['title'], '', $setting->value, $image, $post['id']);

                                            if (CustomNotification::where('title', $postData['title'])->exists()) {
                                            } else {
                                                CustomNotification::create([
                                                    'title' => $postData['title'],
                                                    'post_id' => $post['id'],
                                                    'type' => 'All'
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $this->uploadPostOnSocial($post['id']);
                    }
                    DB::commit();
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                }
            } else {
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error') . $ex));
        }
    }
    /**

     * Method to delete Blog

     * @param array $request post data, id

     */

    public function deleteBlog(Request $request, $id)
    {

        Blog::where('id', $id)->delete();

        BlogTranslation::where('blog_id', $id)->delete();

        return back()->with('success', __('message_alerts.blog_deleted_success'));
    }

    /**
     * Method to delete multiple Blogs
     * @param Request $request
     */
    public function deleteMultipleBlog(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', __('message_alerts.select_record'));
        }

        Blog::whereIn('id', $ids)->delete();
        BlogTranslation::whereIn('blog_id', $ids)->delete();

        return back()->with('success', __('message_alerts.blog_deleted_success'));
    }

    /**
     * Bulk update schedule date/time for multiple blogs
     */
    public function bulkUpdateSchedule(Request $request)
    {
        try {
            $data = $request->all();
            $ids  = isset($data['ids']) && is_array($data['ids']) ? $data['ids'] : [];

            // Allowed visibility DB columns (mapped from field_key)
            $allowedVisKeys = [
                'is_featured',
                'is_slider',
                'is_editor_picks',
                'is_weekly_top_picks',
            ];

            $schedule = isset($data['schedule_date']) ? trim($data['schedule_date']) : null;
            $status   = isset($data['status']) ? $data['status'] : '';

            // Legacy shorthand keys (old clients sending 'featured'/'slider')
            $legacyFeatured = isset($data['featured']) ? $data['featured'] : '';
            $legacySlider   = isset($data['slider'])   ? $data['slider']   : '';

            // Collect dynamic visibility fields from payload
            $visUpdates = [];
            foreach ($allowedVisKeys as $key) {
                if (isset($data[$key]) && $data[$key] !== '') {
                    $visUpdates[$key] = intval($data[$key]);
                }
            }
            // Legacy fallback
            if ($legacyFeatured !== '') {
                $visUpdates['is_featured'] = intval($legacyFeatured);
            }
            if ($legacySlider !== '') {
                $visUpdates['is_slider'] = intval($legacySlider);
            }

            Log::info('bulkUpdateSchedule payload', [
                'ids_count'  => count($ids),
                'ids'        => $ids,
                'data'       => $data,
                'visUpdates' => $visUpdates,
            ]);

            $hasAnyChange = $schedule || $status !== '' || !empty($visUpdates);

            if (empty($ids) || !$hasAnyChange) {
                Log::warning('bulkUpdateSchedule invalid request', ['ids' => $ids, 'data' => $data]);
                return response()->json(['status' => false, 'message' => __('message_alerts.invalid_request')]);
            }

            $updateData = [];

            if ($schedule) {
                $normalized = $schedule;
                try {
                    $dt = new \DateTime($schedule);
                    $normalized = $dt->format('Y-m-d H:i:s');
                } catch (\Exception $ex) {
                    $normalized = $schedule;
                }
                $updateData['schedule_date'] = $normalized;
            }

            if ($status !== '') {
                $st = intval($status);
                if (in_array($st, [0, 1, 2], true)) {
                    $updateData['status'] = $st;
                }
            }

            // Merge all visibility field updates
            foreach ($visUpdates as $col => $val) {
                $updateData[$col] = $val;
            }

            $affected = Blog::whereIn('id', $ids)->update($updateData);

            Log::info('bulkUpdateSchedule result', [
                'updateData'   => $updateData,
                'affected_rows' => $affected,
            ]);

            if ($affected === 0) {
                return response()->json(['status' => false, 'message' => 'No rows updated', 'affected' => $affected]);
            }

            return response()->json(['status' => true, 'message' => __('message_alerts.record_updated'), 'affected' => $affected]);
        } catch (\Exception $e) {
            Log::error('bulkUpdateSchedule error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }



    /**

     * Method to delete Blog Image

     * @param array $request post data, id

     */

    public function deleteBlogImage(Request $request, $id)
    {

        $BlogImagesData = BlogImages::find($id);

        BlogImages::where('id', $id)->delete();

        $remainImages = BlogImages::where('blog_id', $BlogImagesData->blog_id)->get();

        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.banner_image_deleted_success'), []));
    }



    /**

     * Method to change status of blog

     * @param array $request post data ,id ,status

     */

    public function changeBlogStatus(Request $request, $id, $status)
    {
        if (auth()->check() && auth()->user()->type !== 'admin') {
            return back()->with('error', 'Only admin can change blog status.');
        }

        $blog = Blog::select('id', 'status')->where('id', $id)->first();
        $fromStatus = $blog ? (int) $blog->status : null;

        $post['status'] = $status;
        $post['id'] = $id;
        Blog::updateBlog($post);

        if ($blog) {
            BlogActionLog::record('blog_status_changed', (int) $id, Auth::check() ? (int) Auth::id() : null, [
                'source' => 'change_status_route',
                'from_status' => $fromStatus,
                'to_status' => (int) $status,
            ]);
        }

        return back()->with('success', __('message_alerts.status_changed_success'));
    }

    /**
     * Cron-style endpoint: find blogs scheduled within minute precision and return IDs.
     *
     * Query params:
     * - publish=1  => also publish (status=1) any due blogs still in draft (status=2)
     * - lookback=N => include N previous minutes (default 0)
     * - key=...    => if CRON_SECRET is set, must match
     */
    public function scheduledBlogsByMinute(Request $request)
    {
        $baseQuery = Blog::query()
            ->select('id', 'status', 'schedule_date')
            ->where('status', 1)
            ->where('is_featured', 1)
            ->whereNotNull('schedule_date')
            ->whereBetween('schedule_date', [
                now()->subMinutes(5),
                now()
            ])->get();

        foreach ($baseQuery as $key => $value) {
            $blog_id = $value['id'];
            $this->sendBlogNotification_unpublish($blog_id, null);
        }
    }



    public function executeCron(Request $request)
    {



        if (setting('enable_notifications')) {

            $blog = Blog::where('schedule_date', '>=', date("Y-m-d H:i"))->where('schedule_date', '<', date('Y-m-d H:i', strtotime("+ 15 minutes")))->get();

            $image = url('upload/blog/banner/default.jpg');

            foreach ($blog as $row) {

                $blogImageInfo = BlogImages::where('blog_id', $row->id)->first();

                if ($blogImageInfo) {

                    $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
                }

                $user = User::where('active', 1)->get();

                $setting = SiteContent::where('key', 'firebase_msg_key')->first();

                foreach ($user as $detail) {

                    if ($detail->device_token != null) {

                        \Helpers::sendNotification([$detail->device_token], $row->title, '', $setting->value, $image, $row->id);
                    }
                }

                $non_logged_in = DeviceToken::get();
                // return dd($non_logged_in);

                if (count($non_logged_in)) {

                    foreach ($non_logged_in as $non_logged_in_data) {

                        if ($non_logged_in_data->device_token != null) {

                            \Helpers::sendNotification([$non_logged_in_data->device_token], $row->title, '', $setting->value, $image, $row->id);
                        }
                    }
                }
            }
        }
    }

    public function executeCategoryCron()
    {
        try {
            $setting = SiteContent::where('key', 'feature_category_auto_remove')->first();
            // Early return if setting not found
            if (!$setting || !$setting->value) {
                \Log::warning('Feature category auto remove setting not found or empty');
                return false;
            }

            // Bulk update - much more efficient than individual updates
            $updatedCount = Blog::where('schedule_date', '<', Carbon::today()->subDays($setting->value))
                ->where('is_featured', '1')
                ->orWhere('is_slider', '1')
                // ->limit(500)
                ->update(['is_featured' => '0', 'is_slider' => '0']);

            \Log::info("Successfully updated {$updatedCount} featured blogs");
            return true;
        } catch (\Throwable $th) {
            \Log::error('Category Cron Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
            return false;
        }
    }



    public function AddCategoryToBlogs(Request $request)
    {

        $blog = Blog::whereNotNull('category_id')->get();

        foreach ($blog as $row) {

            $notification = array(

                'blog_id' => $row->id,

                'category_id' => $row->category_id,

                'created_at' => date("Y-m-d H:i:s"),

            );

            BlogCategory::insertGetId($notification);
        }

        return "Category added to blog successfully";
    }





    /**

     * upload post in social media

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */





    function uploadPostOnSocial($blog_id)
    {

        $img = false;

        $imgName = false;

        $row = BlogImages::where('blog_id', $blog_id)->first();

        $lastblog = Blog::find($blog_id);
        if (isset($row->image)) {
            $img = public_path() . "/upload/blog/banner/original/" . $row->image;

            if (is_file($img)) {

                $imgName = $row->image;

                $id = Blog::where('id', $blog_id)->update(['scial_media_image' => $imgName]);



                if (setting('fb_share') == 1) {

                    $facebook = public_path('upload/social-media-post/facebook/') . $imgName;

                    $this->load($img);

                    $this->resize(1200, 630);

                    $this->save($facebook, IMAGETYPE_JPEG);

                    sleep(2);

                    \Helpers::generateWatermarkImage($facebook, $imgName, 'facebook');
                }



                if (setting('twitter_share') == 1) {

                    $twitter = public_path('upload/social-media-post/twitter/') . $imgName;

                    $this->load($img);

                    $this->resize(1024, 512);

                    $this->save($twitter, IMAGETYPE_JPEG);

                    sleep(2);

                    \Helpers::generateWatermarkImage($twitter, $imgName, 'twitter');
                }



                if (setting('instagram_share') == 1) {



                    $instagram = public_path('upload/social-media-post/instagram/') . $imgName;

                    $this->load($img);

                    $this->resize(1080, 1080);

                    $this->save($instagram, IMAGETYPE_JPEG);

                    sleep(2);

                    \Helpers::generateWatermarkImage($instagram, $imgName, 'instagram');
                }



                $blogDesc = 'Follow @' . setting('site_name') . 'Latest News & Updates App. Stay Blessed! Stay Connected!';



                $tags = '';

                if ($lastblog->tags != '') {

                    $explodeTag = explode(',', $lastblog->tags);

                    for ($c = 0; $c < count($explodeTag); $c++) {

                        $tags = $tags . ' #' . $explodeTag[$c];
                    }
                }

                $status = $lastblog->title . ' ' . $blogDesc . ' ' . $tags;



                // facebook page post



                if (setting('fb_share') == 1) {

                    $fbImage = public_path("/upload/social-media-post/facebook/") . $row->image;

                    if (is_file($fbImage)) {

                        $fbUserId = config('services.facebook.user_id');

                        $pages = $this->api->get(

                            '/' . $fbUserId . '/accounts',

                            $this->accessToken

                        );

                        $pagesResponse = $pages->getDecodedBody();

                        if (isset($pagesResponse['data'][0]) && !empty($pagesResponse['data'][0])) {

                            $this->pageToken = $pagesResponse['data'][0]['access_token'];

                            $this->pageId = $pagesResponse['data'][0]['id'];

                            $img = $fbImage;

                            $this->api->setDefaultAccessToken($this->pageToken);

                            $response = $this->api->post('/' . $this->pageId . '/photos', [

                                'message' => $status,

                                'source' => $this->api->fileToUpload($img),

                            ])->getGraphNode()->asArray();
                        }
                    }
                }



                if (setting('twitter_share') == 1) {

                    // twitter post

                    $tweetImage = public_path() . "/upload/social-media-post/twitter/" . $row->image;

                    if (is_file($tweetImage)) {

                        $uploaded_media = Twitter::uploadMedia(['media' => File::get($tweetImage)]);

                        $tweets = Twitter::postTweet(['status' => $status, 'media_ids' => $uploaded_media->media_id_string]);

                        Blog::where('id', $blog_id)->update(array('tweet_published' => 1));
                    }
                }
            }
        }
    }



    /**

     * convert image size functions

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    function load($filename)
    {

        $image_info = getimagesize($filename);

        $this->image_type = $image_info[2];

        if ($this->image_type == IMAGETYPE_JPEG) {

            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {

            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {

            $this->image = imagecreatefrompng($filename);
        }

        unset($image_info);
    }



    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {

        if ($image_type == IMAGETYPE_JPEG) {

            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image, $filename);
        }

        if ($permissions != null) {

            chmod($filename, $permissions);
        }
    }



    function output($image_type = IMAGETYPE_JPEG)
    {

        if ($image_type == IMAGETYPE_JPEG) {

            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image);
        }
    }



    function getWidth()
    {

        return imagesx($this->image);
    }



    function getHeight()
    {

        return imagesy($this->image);
    }



    function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();

        $width = $this->getWidth() * $ratio;

        $this->resize($width, $height);
    }



    function resizeToWidth($width)
    {

        $ratio = $width / $this->getWidth();

        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }



    function scale($scale)
    {

        $width = $this->getWidth() * $scale / 100;

        $height = $this->getheight() * $scale / 100;

        $this->resize($width, $height);
    }



    function resize($width, $height)
    {

        $new_image = imagecreatetruecolor($width, $height);

        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }



    /**

     * Show Blog view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function slider(Request $request, $layout = 'side-menu', $theme = 'light')
    {

        $blog = Blog::getAllSliderBlog($request->all());

        $theme = $request->segment(3);

        return view('super-admin/blog.slider', [

            'theme' => $theme,

            'page_name' => 'index',

            'side_menu' => array(),

            'layout' => $layout,

            'blog' => $blog,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/blog/side-menu/light') . '" class="breadcrumb--active">' . trans("admin.slider_post") . '</a>'

        ]);
    }



    /**

     * update category

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function update(Request $request)
    {

        $posts = Blog::all();

        foreach ($posts as $post) {

            foreach ($request->order as $order) {

                if ($order['id'] == $post->id) {

                    Blog::where('id', $post->id)->update(['order' => $order['position']]);
                }
            }
        }

        return response(__('message_alerts.record_updated'), 200);
    }

    /**

     * Method to change status of blog

     * @param array $request post data ,id ,status

     */

    public function deleteSliderPost(Request $request, $id)
    {

        $post['is_slider'] = 0;

        $post['id'] = $id;

        Blog::updateBlog($post);

        return back()->with('success', __('message_alerts.slider_post_deleted_success'));
    }



    // public function sendBlogNotification($id)
    // {

    //     if (setting('enable_notifications')) {
    //         $blog = Blog::findOrFail($id);
    //         $blogCategories = BlogCategory::where('blog_id', $blog->id)->get();
    //         foreach ($blogCategories as $blogCategory) {
    //             if ($blogCategory->category_id == 12) {
    //                 return back()->with('error', __('Notification Not Sent for Personalization Category'));
    //             }
    //         }
    //         $image = url('upload/blog/banner/default.jpg');
    //         $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();
    //         if ($blogImageInfo) {
    //             $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //         }
    //         $users = User::where('active', 1)->get();
    //         foreach ($users as $user) {
    //             if ($user->device_token != null) {
    //                 \Helpers::sendNotification($user->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id);

    //             }
    //         }
    //         $notification = Notification::create([
    //             'user_id' => $users->id,
    //             'title' => $blog->title,
    //             'notificationId' => $id,
    //             'image' => $image,
    //         ]);

    //         // $nonLoggedIn = DeviceToken::get();
    //         // if (count($nonLoggedIn)) {
    //         //     foreach ($nonLoggedIn as $nonLoggedInData) {
    //         //         if ($nonLoggedInData->device_token != null) {
    //         //             \Helpers::sendNotification($nonLoggedInData->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id);
    //         //         }
    //         //     }
    //         // }
    //     }
    //     return back()->with('success', __('message_alerts.notification_sent'));
    // }




    // public function sendBlogNotification($id)
    // {


    //     $blog = Blog::findOrFail($id);

    //     $blogCategories = BlogCategory::where('blog_id', $blog->id)->get();

    //     foreach ($blogCategories as $blogCategory) {
    //         if ($blogCategory->category_id == 12) {
    //             return back()->with('error', __('Notification Not Sent for Personalization Category'));
    //         }
    //     }

    //     $image = url('upload/blog/banner/default.jpg');
    //     $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();
    //     if ($blogImageInfo) {
    //         $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
    //     }

    //     $users = User::where('active', 1)->get();
    //     $fcmTokens = [];



    //     $nonLoggedIn = DeviceToken::get();
    //     // return dd($nonLoggedIn);
    //     if (count($nonLoggedIn)) {
    //         foreach ($nonLoggedIn as $nonLoggedInData) {
    //             if ($nonLoggedInData->device_token != null) {
    //                 array_push($fcmTokens, $nonLoggedInData->device_token);

    //             }
    //         }
    //         if (count($fcmTokens)) {
    //             $data = [
    //                 'title' => $blog->title, // Optional title for reference in app
    //                 'imageUrl' => $image, // Optional image URL for reference in app (retrieved earlier)
    //                 'id' => $id, // Add custom data for your app
    //             ];
    //             \Helpers::sendNotification($fcmTokens, null, null, setting('firebase_msg_key'), $data);
    //             CustomNotification::create([
    //                 'title' => $blog->title,
    //                 'post_id' => $id,
    //                 'type' => 'All'
    //             ]);

    //         }
    //     }

    //     return back()->with('success', __('message_alerts.notification_sent'));
    // }

    public function sendBlogNotification($id)
    {

        // if (setting('enable_notifications')) {
        $blog = Blog::findOrFail($id);
        if (intval($blog->status) !== 1) {
            return back()->with('failure', __('Notification Not Sent for Unpublished Blog'));
        }
        $blogCategories = BlogCategory::where('blog_id', $blog->id)->get();

        foreach ($blogCategories as $blogCategory) {
            if ($blogCategory->category_id == 12) {
                return back()->with('error', __('Notification Not Sent for Personalization Category'));
            }
        }

        $image = url('upload/blog/banner/default.jpg');
        $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();
        if ($blogImageInfo) {
            $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
        }

        $users = User::where('active', 1)->where('is_notiifcation', 1)->where('fcm_token', '!=', NULL)->get();
        $fcmTokens = [];

        $tokens = [];



        foreach ($users as $user) {
            if (!empty($user->fcm_token)) {
                $tokens[] = $user->fcm_token;
            }
        }
        $non_logged_in = DeviceToken::whereNotIn('device_token', $tokens)->get();
        if (count($non_logged_in)) {
            foreach ($non_logged_in as $non_logged_in_data) {
                if ($non_logged_in_data->device_token != null) {
                    $tokens[] = $non_logged_in_data->device_token;
                }
            }
        }
        if (!empty($tokens)) {
            \Helpers::sendNotification($tokens, $blog->title, '', setting('firebase_msg_key'), $image, $id);
            CustomNotification::create([
                'title' => $blog->title,
                'desc' => '',
                'post_id' => $id,
                'type' => 'All',
            ]);
        }

        // foreach ($users as $user) {
        //     // if ($user->device_token != null) {
        //     //     \Helpers::sendNotification($user->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id);
        //     //     Notification::create([
        //     //         'user_id' => $users->id,
        //     //         'title' => $blog->title,
        //     //         'notificationId' => $id,
        //     //         'image' => $image,
        //     //     ]);
        //     // }
        //     if ($user->device_token != null) {
        //         \Helpers::sendTestNotification($user->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id, 'global');
        //         Notification::create([
        //             'user_id' => $user->id,
        //             'title' => $blog->title,
        //             'notificationId' => $id,
        //             'image' => $image,
        //         ]);
        //         if (CustomNotification::where('title', $blog->title)->exists()) {
        //         } else {
        //             CustomNotification::create([
        //                 'title' => $blog->title,
        //                 'post_id' => $id,
        //                 'type' => 'All'
        //             ]);
        //         }
        //         // CustomNotification::truncate();
        //         // Notification::truncate();
        //     }
        // }

        // $nonLoggedIn = DeviceToken::where('device_token', '!=', 'BLACKLISTED')->get();

        // if (count($nonLoggedIn)) {
        //     foreach ($nonLoggedIn as $nonLoggedInData) {
        //         if ($nonLoggedInData->device_token != null) {
        //             array_push($fcmTokens, $nonLoggedInData->device_token);
        //         }
        //     }
        //     if (count($fcmTokens)) {
        //         \Helpers::sendNotification($fcmTokens, $blog->title, '', setting('firebase_msg_key'), $image, $id);
        //         if (CustomNotification::where('title', $blog->title)->exists()) {
        //         } else {
        //             CustomNotification::create([
        //                 'title' => $blog->title,
        //                 'post_id' => $id,
        //                 'type' => 'All'
        //             ]);
        //         }
        //     }
        // }
        // }
        return back()->with('success', __('message_alerts.notification_sent'));
    }

    public function sendBlogNotification_unpublish($id, $title)
    {

        // if (setting('enable_notifications')) {
        $blog = Blog::findOrFail($id);

        if ($blog->schedule_date > date("Y-m-d H:i:s")) {
            return true;
        }
        if ($title == null) {
            $title = $blog->title;
        }

        $notification_send_status = CustomNotification::where('post_id', $id)->exists();
        if ($notification_send_status) {
            return true;
        }

        $blogCategories = BlogCategory::where('blog_id', $blog->id)->get();

        foreach ($blogCategories as $blogCategory) {
            if ($blogCategory->category_id == 12) {
                return true;
            }
        }

        $image = url('upload/blog/banner/default.jpg');
        $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();
        if ($blogImageInfo) {
            $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
        }

        $users = User::where('active', 1)->where('is_notiifcation', 1)->where('fcm_token', '!=', NULL)->get();
        $fcmTokens = [];

        $tokens = [];


        $tokens = $users
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        // Get only non-null device tokens that are not already in $tokens
        $nonLoggedIn = DeviceToken::whereNotNull('device_token')
            ->whereNotIn('device_token', $tokens)
            ->pluck('device_token')
            ->toArray();

        // Merge and final clean-up (just in case)
        $tokens = array_values(array_unique(array_filter(array_merge($tokens, $nonLoggedIn))));
        $tokens = array_values(
            array_filter($tokens, function ($v) {
                return !is_null($v) && $v !== '';
            })
        );

        if (!empty($tokens)) {
            CustomNotification::create([
                'title' => $title,
                'desc' => '',
                'post_id' => $id,
                'type' => 'All',
            ]);
            \Helpers::sendNotification($tokens, $title, '', setting('firebase_msg_key'), $image, $id);
        }
        return true;
    }
    /**

     * send notification of specific blog

     * @param array $request post data ,id ,status

     */






    public function sendBlogNotificationold($id)
    {

        if (setting('enable_notifications')) {



            $blog = Blog::findOrfail($id);

            $image = url('upload/blog/banner/default.jpg');

            $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();

            if ($blogImageInfo) {

                $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
            }

            $user = User::where('active', 1)->where('is_notiifcation', 1)->get();

            foreach ($user as $detail) {

                if ($detail->device_token != null) {

                    \Helpers::sendNotification($detail->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id);
                }
            }

            $non_logged_in = DeviceToken::get();

            if (count($non_logged_in)) {

                foreach ($non_logged_in as $non_logged_in_data) {

                    if ($non_logged_in_data->device_token != null) {

                        \Helpers::sendNotification($non_logged_in_data->device_token, $blog->title, '', setting('firebase_msg_key'), $image, $id);
                    }
                }
            }
        }

        return back()->with('success', __('message_alerts.notification_sent'));
    }



    public function convertimage(Request $request)
    {

        $posts = Blog::orderBy('id', 'DESC')->get();

        foreach ($posts as $post) {

            $blgImg = BlogImages::where('blog_id', $post->id)->get();

            foreach ($blgImg as $blgImgRow) {

                $destination_url = public_path('/upload/blog/banner/original/') . $blgImgRow->image;

                $name = $blgImgRow->image;

                if (is_file($destination_url)) {

                    $basePath = public_path('/upload/blog/banner/');

                    $img = \UploadImage::make($destination_url);



                    $img->resize(800, null, function ($constraint) {

                        $constraint->aspectRatio();
                    })->save($basePath . '800/' . $name);



                    $img->resize(360, null, function ($constraint) {

                        $constraint->aspectRatio();
                    })->save($basePath . '360/' . $name);
                }
            }
        }

        echo 'done';
        die;
    }



    public function validateSlug(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                if ($post['slug'] != '') {

                    $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($post['slug']))->count();

                    if ($slugExist) {

                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'), []));
                    } else {

                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), []));
                    }
                } else {

                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
                }
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }



    public function analytics(Request $request, $layout = 'side-menu', $theme = 'light', $id)
    {


        $analytics['blogDetail'] = Blog::where('id', $id)->first();

        $analytics['blog_view'] = BlogViewCount::where('blog_id', $id)->orderBy('id', 'DESC')->with('user')->get()->toArray();

        $analytics['search_view'] = BlogViewCount::where('blog_id', $id)->whereaction('search')->count();

        $analytics['vote'] = Vote::where('blog_id', $id)->orderBy('id', 'DESC')->with('user')->select('id', 'user_id', 'vote', 'created_at')->get()->toArray();

        $analytics['vote_sum'] = Vote::where('blog_id', $id)->sum('vote');

        $analytics['voting'] = Blog::where('id', $id)->select('is_voting_enable', 'VotingQuestion')->first();

        $theme = $request->segment(3);

        return view('super-admin.blog.analytics', compact('analytics', 'layout', 'theme'));
    }



    public function deleteImageFromFolders(Request $request)
    {

        $blog_images = User::get();

        $banner360Images = array();

        $banner800Images = array();

        $bannerOriginalImages = array();

        $images = array();

        if (count($blog_images)) {

            foreach ($blog_images as $blog_images_data) {

                // if (file_exists(public_path('upload/blog/banner/original/'.$blog_images_data->image))) { 

                //     // $sourcePath = public_path('upload/blog/banner/original/'.$blog_images_data->image);

                //     // $destinationPath = public_path('upload/blog/banner/original_blog/'.$blog_images_data->image);

                //     Storage::move(public_path('/upload/blog/banner/original/'.$blog_images_data->image), public_path('/upload/blog/banner/original_blog/'.$blog_images_data->image));

                // }





                // Move the file using the Storage facade



                // if (file_exists(public_path('upload/blog/banner/360/'.$blog_images_data->image))) {

                //     unlink(public_path('upload/blog/banner/360/'.$blog_images_data->image));

                // }

                // if (file_exists(public_path('upload/blog/banner/800/'.$blog_images_data->image))) {

                //     unlink(public_path('upload/blog/banner/800/'.$blog_images_data->image));

                // }

                // if (file_exists(public_path('upload/blog/banner/original/'.$blog_images_data->image))) {

                //     unlink(public_path('upload/blog/banner/original/'.$blog_images_data->image));

                // }



                // $originalPath = public_path('upload/blog/banner/original/'.$blog_images_data->image);

                // $destinationPath = public_path('upload/blog/banner/original_blog/'.$blog_images_data->image);



                // if (file_exists($originalPath)) {

                //     if (!file_exists(dirname($destinationPath))) {

                //         Storage::makeDirectory(dirname($destinationPath), 0777, true);

                //     }

                //     Storage::move($originalPath, $destinationPath);

                // } else {

                //     // Handle the case when the file doesn't exist

                // }

                $originalPath = $blog_images_data->photo;

                $destinationPath = public_path('upload/blog/banner/800_image/' . $blog_images_data->photo);



                if (File::exists($originalPath)) {

                    File::move($originalPath, $destinationPath);
                } else {

                    // Handle the case when the file doesn't exist

                }
            }
        }

        // Storage::delete($banner360Images);

        // Storage::delete($banner800Images);

        // Storage::delete($bannerOriginalImages);

    }
}
