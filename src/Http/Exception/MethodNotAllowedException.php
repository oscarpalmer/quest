<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Http\Exception;

use Throwable;

class MethodNotAllowedException extends HttpException
{
    public function __construct(Throwable $error = null)
    {
        parent::__construct(405, $error);
    }
}