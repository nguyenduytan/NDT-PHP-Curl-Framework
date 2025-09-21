<?php
declare(strict_types=1);
namespace ndtan\Curl\Integrations\PSR18;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ndtan\Curl\Core\RequestBuilder;
use ndtan\Curl\Http\Response as NdtResponse;

final class Client implements ClientInterface
{
    /** @var callable(RequestInterface):RequestBuilder */
    private $mapper;
    /** @var callable(NdtResponse):ResponseInterface */
    private $responseFactory;

    public function __construct(callable $mapper, callable $responseFactory)
    {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $builder = ($this->mapper)($request);
        $res = $builder->method($request->getMethod())->send();
        return ($this->responseFactory)($res);
    }
}
