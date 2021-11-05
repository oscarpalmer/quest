<?php declare(strict_types=1);

namespace oscarpalmer\Quest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    public ServerRequestInterface $request;
    public ResponseInterface $response;

    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
