<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Processor\JsonProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class ActionJsonHandler
{
    public function handle(
        Request $request,
        object $data,
        JsonProcessorInterface $processor,
    ): Response {
        $context = HandlerContext::fromRequest($request);
        $result = $processor->process(
            new ActionDirectPayload(
                data: $data,
                token: $request->query->get('csrfToken') ?? $request->request->get('csrfToken')
            ),
            $context
        );

        return new JsonResponse([
            'success' => $result->success,
            'data' => $result->data
        ], $result->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }
}
