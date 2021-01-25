<?php

namespace App\ValueObject;

class Account
{
    private string $segmentAccountNumber;
    private array $segmentDigits;
    private array $parsedDigits;
    private string $status;
    private array $alternativeNumbers = [];

    public function __construct(string $segmentAccountNumber, array $segmentDigits)
    {
        $this->segmentAccountNumber = $segmentAccountNumber;
        $this->segmentDigits = $segmentDigits;

        $this->status = AccountStatus::NOT_VERIFIED;
    }

    public function __toString()
    {
        if ($this->status === AccountStatus::CORRECT) {
            return implode('', $this->parsedDigits);
        }

        $alternativeNumbersInfo = '';
        if (count($this->alternativeNumbers) > 1) {
            $alternativeNumbers = [];
            foreach ($this->alternativeNumbers as $alternativeNumber) {
                $alternativeNumbers[] = implode('', $alternativeNumber);
            }
            $alternativeNumbersInfo = '('.implode(', ', $alternativeNumbers).')';
        }

        return implode('', $this->parsedDigits).' '.$this->status.$alternativeNumbersInfo;
    }

    public function getSegmentAccountNumber(): string
    {
        return $this->segmentAccountNumber;
    }

    public function getSegmentDigits(): array
    {
        return $this->segmentDigits;
    }

    public function setParsedDigits(array $parsedDigits): Account
    {
        $this->parsedDigits = $parsedDigits;

        return $this;
    }

    public function addParsedDigit(string $digit): Account
    {
        $this->parsedDigits[] = $digit;

        return $this;
    }

    public function getParsedDigits(): array
    {
        return $this->parsedDigits;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Account
    {
        $this->status = $status;

        return $this;
    }

    public function addAlternativeNumber(array $alternativeNumber): Account
    {
        $this->alternativeNumbers[] = $alternativeNumber;

        return $this;
    }

    public function getAlternativeNumbers(): array
    {
        return $this->alternativeNumbers;
    }
}
