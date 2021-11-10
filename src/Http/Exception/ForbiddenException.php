<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Http\Exception;

use Throwable;

class ForbiddenException extends HttpException
{
    public function __construct(Throwable $error)
    {
        parent::__construct(403, $error);
    }
}
