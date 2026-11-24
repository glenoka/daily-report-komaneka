<?php

namespace App\Services\Normalizer;

class ReportNormalizerService
{
    /**
     * Regex pattern matching recognized leading list markers.
     */
    protected const BULLET_CHARS = '•●○◦▪▫‣⁃';
    protected const DASH_CHARS = '-–—';
    protected const CHECKBOX_CHARS = '☐☑☒✓✔';
    protected const COMMON_LIST_EMOJIS = '📌📍🔹🔸▶►👉➡️✔️✅⚠️⭐💡⚡';

    /**
     * Normalize multi-line text input into an array of clean string items.
     *
     * @param string|null $input
     * @return array<int, string>
     */
    public function normalizeToArray(?string $input): array
    {
        if ($input === null || trim($input) === '') {
            return [];
        }

        // 1. Normalize line endings to LF
        $normalized = str_replace(["\r\n", "\r"], "\n", $input);

        // 2. Split by LF
        $rawLines = explode("\n", $normalized);
        $items = [];

        foreach ($rawLines as $rawLine) {
            $line = trim($rawLine);

            // 3. Skip blank lines
            if ($line === '') {
                continue;
            }

            // 4. Strip recognized list prefixes repeatedly where safe
            $cleaned = $this->cleanLine($line);

            if ($cleaned !== '') {
                $items[] = $cleaned;
            }
        }

        return $items;
    }

    /**
     * Normalize multi-line text and return it as clean newline-separated text.
     *
     * @param string|null $input
     * @return string
     */
    public function normalizeToCleanText(?string $input): string
    {
        $items = $this->normalizeToArray($input);
        return implode("\n", $items);
    }

    /**
     * Clean leading list markers from a single line of text safely.
     *
     * @param string $line
     * @return string
     */
    public function cleanLine(string $line): string
    {
        $current = trim($line);
        $maxPasses = 5;

        for ($pass = 0; $pass < $maxPasses; $pass++) {
            $previous = $current;

            // 1. Bullet characters: •, ●, ○, ◦, ▪, ▫, ‣, ⁃
            $current = preg_replace('/^[' . self::BULLET_CHARS . ']\s*/u', '', $current);

            // 2. Checkboxes: [ ], [x], [X], ☐, ☑, ☒, ✓, ✔
            $current = preg_replace('/^(?:\[[ xX]\]|[' . self::CHECKBOX_CHARS . '])\s*/u', '', $current);

            // 3. Dashes & Asterisks when used as list markers (must have trailing whitespace or lead to another marker)
            $current = preg_replace('/^(?:[' . self::DASH_CHARS . ']|\*)\s+/u', '', $current);

            // 4. Numeric markers: 1., 2), 10., 1:, 1- (followed by whitespace)
            $current = preg_replace('/^\d{1,3}[\.\)\:\-]\s+/u', '', $current);

            // 5. Alphabetic markers: a., B), a), B. (single letter followed by dot/paren and whitespace)
            $current = preg_replace('/^[a-zA-Z][\.\)]\s+/u', '', $current);

            // 6. Common list emojis (followed by whitespace or next marker)
            $current = preg_replace('/^[' . self::COMMON_LIST_EMOJIS . ']\s*/u', '', $current);

            $current = trim($current);

            // If no change occurred in this pass, we are done
            if ($current === $previous || $current === '') {
                break;
            }
        }

        return $current;
    }
}
