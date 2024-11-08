<?php

namespace MalteKuhr\LaravelGpt\Services\JsonPathFinderService;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class JsonPathFinderService
{
    /**
     * Find the position of a value in a JSON string based on a given path.
     *
     * @param string $rawJson The raw JSON string to search in
     * @param string $path The dot notation path to the value
     * @return array|null Returns ['start' => int, 'end' => int] or null if not found
     * @throws \JsonException When JSON is invalid
     */
    public function findPosition(string $rawJson, string $path): ?array
    {
        // First validate and decode the JSON
        $decoded = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);
        
        // Clean JSON by removing whitespace outside of strings
        $cleanedJson = $this->cleanJson($rawJson);
        
        // Get the mapping of removed whitespace positions
        $removedIndices = $this->findRemovedIndices($rawJson, $cleanedJson);
        
        // Find the position in cleaned JSON
        $match = $this->determineStringPosition($cleanedJson, $path, $decoded);
        
        if (empty($match)) {
            return null;
        }

        // Adjust positions based on removed whitespace
        $startIndex = $match['start'] + count(array_filter($removedIndices, fn($index) => $index <= $match['start']));
        $endIndex = $match['end'] + count(array_filter($removedIndices, fn($index) => $index <= $match['end']));

        return [
            'start' => $startIndex,
            'end' => $endIndex
        ];
    }

    /**
     * Clean JSON string by removing whitespace outside of strings.
     */
    private function cleanJson(string $rawJson): string
    {
        return preg_replace_callback(
            '/("(?:\\\\.|[^\\\\"])*")|[\s\n\r\t]+/',
            fn($m) => $m[1] ?? '',
            $rawJson
        );
    }

    /**
     * Find indices of removed whitespace characters.
     */
    private function findRemovedIndices(string $originalJson, string $cleanedJson): array
    {
        $removedIndices = [];
        $originalIndex = 0;
        $cleanedIndex = 0;

        while ($originalIndex < strlen($originalJson)) {
            if ($cleanedIndex >= strlen($cleanedJson) || 
                $originalJson[$originalIndex] !== $cleanedJson[$cleanedIndex]) {
                $removedIndices[] = max(0, $cleanedIndex - 1);
                $originalIndex++;
            } else {
                $originalIndex++;
                $cleanedIndex++;
            }
        }

        return $removedIndices;
    }

    /**
     * Determine the position of a value in the JSON string based on its path.
     */
    private function determineStringPosition(string $rawJson, string $path, array $decoded): ?array
    {
        $value = json_encode(Arr::get($decoded, $path), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($value === null) {
            return null;
        }

        $pathParts = explode('.', $path);
        $parentPath = implode('.', array_slice($pathParts, 0, -1));
        $key = end($pathParts);

        if ($parentPath) {
            $parentIsAssoc = Arr::isAssoc(Arr::get($decoded, $parentPath));
        } else {
            $parentIsAssoc = Arr::isAssoc($decoded);
        }

        $matches = $this->findMatches($rawJson, $key, $value, $parentIsAssoc);
        
        if (empty($matches)) {
            return null;
        }

        $currentIndex = $this->findCurrentMatch($path, $decoded, $value, $key, $parentIsAssoc) - 1;
        
        return $matches[$currentIndex] ?? null;
    }

    /**
     * Find all matches for a value in the JSON string.
     */
    private function findMatches(string $rawJson, string $key, string $value, bool $parentIsAssoc): array
    {
        $matches = [];

        if ($parentIsAssoc) {
            if (Str::contains($value, "{")) {
                $detectValue = Str::random(strlen($value));
                $detectJson = str_replace($value, $detectValue, $rawJson);
                $detectKey = $key == $value ? $detectValue : $key;
            } else {
                $detectJson = $rawJson;
                $detectValue = $value;
                $detectKey = $key;
            }

            $pattern = '/"' . preg_quote($detectKey, '/') . '"\s*:\s*' . preg_quote($detectValue, '/') . '/';
            preg_match_all($pattern, $detectJson, $matchResults, PREG_OFFSET_CAPTURE);

            foreach ($matchResults[0] as $match) {
                $matchedValue = $detectValue === $value ? $match[0] : str_replace($detectValue, $value, $match[0]);
                $processedValue = substr($matchedValue, strpos($matchedValue, ':') + 1);
                $processedValue = trim($processedValue);
                $isString = substr($processedValue, 0, 1) === '"' && substr($processedValue, -1) === '"';
                $processedValue = preg_replace('/^"|"$/', '', $processedValue);
                $difference = strlen($matchedValue) - strlen($processedValue);

                $start = $match[1] + $difference;
                if ($isString) {
                    $start -= 1;
                }

                $matches[] = [
                    'value' => $processedValue,
                    'start' => $start,
                    'end' => $start + strlen($processedValue)
                ];
            }
        } else {
            $pattern = '/(?<=\[|\,)\s*' . preg_quote($value, '/') . '\s*(?=[,\]])/';
            preg_match_all($pattern, $rawJson, $matchResults, PREG_OFFSET_CAPTURE);

            foreach ($matchResults[0] as $match) {
                $processedValue = trim($match[0]);
                $processedValue = preg_replace('/^"|"$/', '', $processedValue);
                $removedBracketsLengthStart = strlen($match[0]) - strlen(ltrim($match[0], '"'));
                $removedBracketsLengthEnd = strlen($match[0]) - strlen(rtrim($match[0], '"'));
                $start = $match[1] + $removedBracketsLengthStart;
                $end = $match[1] + strlen($match[0]) - $removedBracketsLengthEnd;

                $matches[] = [
                    'value' => $processedValue,
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        return $matches;
    }

    /**
     * Find the current match index for a given path.
     */
    private function findCurrentMatch(?string $path, array $array, string $value, string $originalKey, bool $parentIsAssoc): int
    {
        $matches = 0;
        foreach ($array as $key => $item) {
            $onPath = false;

            if (!is_null($path) && (str_starts_with($path, "$key.") || $path == $key)) {
                $onPath = true;
            }

            if (json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) == $value && ((!$parentIsAssoc && !Arr::isAssoc($array)) || $key == $originalKey)) {
                $matches++;
            } else if (is_array($item)) {
                if ($onPath) {
                    $offset = strlen($key . '.');
                    $newPath = substr($path, $offset);
                    $matches += $this->findCurrentMatch($newPath, $item, $value, $originalKey, $parentIsAssoc);
                } else {
                    $matches += $this->findCurrentMatch($path, $item, $value, $originalKey, $parentIsAssoc);
                }
            }

            if ($onPath) {
                return $matches;
            }
        }

        return $matches;
    }
}