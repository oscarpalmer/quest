<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Exception;

class NotFoundException extends ErrorException
{
    public function __construct()
    {
        parent::__construct(404);
    }
}
