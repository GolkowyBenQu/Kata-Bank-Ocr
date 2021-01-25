<?php

namespace App\Service;

use App\ValueObject\Account;
use App\ValueObject\AccountStatus;
use App\ValueObject\Segment;
use RuntimeException;

class OcrScannerService
{
    private const LINE_LENGTH = 27;
    private const SEGMENT_LINE_COUNT = 4;
    private const SEGMENT_DIGIT_HEIGHT = 3;
    private const SEGMENT_DIGIT_WIDTH = 3;

    /**
     * @param string $filepath
     *
     * @return Account[]
     */
    public function scanFile(string $filepath): array
    {
        $file = fopen($filepath, 'rb');
        if (!$file) {
            throw new RuntimeException('Can\'t open file');
        }

        $scannedAccounts = $this->scanSegmentAccounts($file);
        fclose($file);

        $this->translateAccountNumbers($scannedAccounts);

        return $scannedAccounts;
    }

    private function scanSegmentAccounts($file): array
    {
        $scannedAccounts = [];
        $segmentNumberString = '';
        $segmentAccountNumbers = [];

        $segmentLineIndex = 0;
        while (($line = fgets($file)) !== false) {
            $segmentLineIndex++;

            if ($segmentLineIndex === self::SEGMENT_LINE_COUNT) {
                $segmentLineIndex = 0;
                continue;
            }

            $segmentNumberString .= $line;
            $line = str_replace("\n", '', $line);

            for ($charIndex = 0; $charIndex < self::LINE_LENGTH; $charIndex++) {
                $digitIndex = $charIndex / self::SEGMENT_DIGIT_WIDTH;
                $segmentAccountNumbers[$digitIndex] .= $line[$charIndex] ?? ' ';
            }

            if ($segmentLineIndex % self::SEGMENT_DIGIT_HEIGHT === 0) {
                $scannedAccounts[] = new Account($segmentNumberString, $segmentAccountNumbers);
                $segmentNumberString = '';
                $segmentAccountNumbers = [];
            }
        }

        return $scannedAccounts;
    }

    /**
     * @param Account[] $accounts
     *
     * @return array
     */
    private function translateAccountNumbers(array $accounts): array
    {
        foreach ($accounts as $account) {
            $segmentDigits = $account->getSegmentDigits();

            $illegalCount = 0;
            foreach ($segmentDigits as $segmentDigit) {
                $parsedDigit = Segment::findNumberBySegment($segmentDigit);

                if ($parsedDigit === '?') {
                    $account->setStatus(AccountStatus::ILLEGAL_CHARACTER);
                    $illegalCount++;
                }

                $account->addParsedDigit($parsedDigit);
            }

            if ($illegalCount > 1) {
                continue;
            }

            $this->validateAccount($account);

            if ($account->getStatus() !== AccountStatus::CORRECT) {
                $this->guessProperNumber($account);
            }
        }

        return $accounts;
    }

    private function validateAccount(Account $account): void
    {
        if ($account->getStatus() === AccountStatus::ILLEGAL_CHARACTER) {
            return;
        }

        $status = AccountStatus::CORRECT;

        if (!$this->isValidNumber($account->getParsedDigits())) {
            $status = AccountStatus::CHECKSUM_ERROR;
        }

        $account->setStatus($status);
    }

    private function isValidNumber(array $parsedNumber): bool
    {
        $checksum = 0;
        $digitIndex = 9;
        foreach ($parsedNumber as $parsedDigit) {
            $checksum += $digitIndex * $parsedDigit;
            $digitIndex--;
        }

        return $checksum % 11 === 0;
    }

    private function guessProperNumber(Account $account): void
    {
        if ($account->getStatus() === AccountStatus::ILLEGAL_CHARACTER) {
            $this->guessIllegalCharacter($account);
        }

        if ($account->getStatus() === AccountStatus::CHECKSUM_ERROR) {
            $this->guessChecksumError($account);
        }

        if (count($account->getAlternativeNumbers()) === 1) {
            $account->setParsedDigits($account->getAlternativeNumbers()[0]);
            $account->setStatus(AccountStatus::CORRECT);
        } else if (count($account->getAlternativeNumbers()) > 0) {
            $account->setStatus(AccountStatus::ALTERNATIVE_NUMBERS);
        }
    }

    private function generateAlternativeNumbers(
        Account $account,
        int $illegalCharacterIndex,
        array $similarDigits
    ): void {
        $parsedDigits = $account->getParsedDigits();
        foreach ($similarDigits as $similarDigit) {
            $newNumber = $parsedDigits;
            $newNumber[$illegalCharacterIndex] = $similarDigit;

            if ($this->isValidNumber($newNumber)) {
                $account->addAlternativeNumber($newNumber);
            }
        }
    }

    private function guessIllegalCharacter(Account $account): void
    {
        $illegalCharacterIndex = array_search('?', $account->getParsedDigits(), true);
        $illegalSegment = $account->getSegmentDigits()[$illegalCharacterIndex];

        $similarDigits = Segment::findSimilarDigits($illegalSegment);
        $this->generateAlternativeNumbers($account, $illegalCharacterIndex, $similarDigits);
    }

    private function guessChecksumError(Account $account): void
    {
        $segmentIndex = 0;
        foreach ($account->getSegmentDigits() as $segmentDigit) {
            $similarDigits = Segment::findSimilarDigits($segmentDigit);
            $this->generateAlternativeNumbers($account, $segmentIndex, $similarDigits);
            $segmentIndex++;
        }
    }
}
