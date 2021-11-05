<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Exception;

use Exception;

class ErrorException extends Exception {
    protected int $status;

    public function __construct(int $status)
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
