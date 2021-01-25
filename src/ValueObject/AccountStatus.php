<?php

namespace App\ValueObject;

class AccountStatus
{
    public const NOT_VERIFIED = 'NVR';
    public const CORRECT = 'COR';
    public const CHECKSUM_ERROR = 'ERR';
    public const ILLEGAL_CHARACTER = 'ILL';
    public const ALTERNATIVE_NUMBERS = 'AMB';
}
