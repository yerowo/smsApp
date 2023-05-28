<?php

namespace App\Services;

class SmsService
{
    private $prefixesFromText;

    public function __construct()
    {
        $this->prefixesFromText = $this->loadPrefixesFromTextFile();
    }

    public function calculateUnitCharge($processRecipients)
    {
        $unitCharges = [];
        $addUnitCharges = 0.0;
        $unitCount = 0;

        foreach ($processRecipients as $recipient) {
            $prefix = substr($recipient, 0, 6);

            if (strpos($recipient, $prefix) === 0 && isset($this->prefixesFromText[$prefix])) {
                $unitCharges[] = $prefix . '-' . $this->prefixesFromText[$prefix];
                $addUnitCharges += $this->prefixesFromText[$prefix];
                $unitCount++;
            } else {
                // Handle other foreign numbers or prefixes not found in the array
                // Add your logic here if needed
            }
        }

        $unitCharges = implode("\n", $unitCharges);

        return [$unitCharges, $addUnitCharges, $unitCount];
    }

    public function processRecipientNumbers($recipient)
    {
        $recipient = str_replace(',', '', $recipient);
        $numbers = preg_split("/[,\\n]/", $recipient);
        $processedNumbers = [];

        foreach ($numbers as $number) {
            $number = trim($number);

            if (strpos($number, '0') === 0) {
                $number = '234' . ltrim($number, '0');
            }

            $processedNumbers[] = $number;
        }

        return $processedNumbers;
    }

    public function loadPrefixesFromTextFile()
    {
        $prefixes = [];
        $filePath = resource_path('views/PriceList.txt');

        try {
            $fileContent = file_get_contents($filePath);

            if ($fileContent !== false) {
                foreach (explode(PHP_EOL, $fileContent) as $line) {
                    $line = trim($line);

                    if (!empty($line)) {
                        [$values, $charge] = explode('=', $line);
                        $prefixes[trim($values)] = trim($charge);
                    }
                }
            } else {
                throw new \Exception("Failed to read file content");
            }
        } catch (\Throwable $e) {
            // Handle the exception (e.g., log an error, display a message, etc.)
            throw new \Exception("Failed to load prefixes: " . $e->getMessage());
        }

        return $prefixes;
    }
}
