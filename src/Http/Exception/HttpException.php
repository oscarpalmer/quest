<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Http\Exception;

use Exception;
use Throwable;

class HttpException extends Exception
{
    protected int $status;

    public function __construct(int $status, Throwable $error = null)
    {
        parent::__construct((string) $status, $status, $error);
    }
}
