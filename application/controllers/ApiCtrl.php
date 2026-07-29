<?php

namespace Loom73\Weave\Controllers;

use Loom73\Beam\User;
use Loom73\Woodframe\Ctrl;

/**
 * The Loom73 API is read only by design.
 * This is enforced in this base data-exposing layer.
 * To protect the endpoint from being publicly available,
 * add $this->requireAuth() to the relevant method.
 */
class ApiCtrl extends Ctrl
{

    public function __construct(
        string $model,
        string $controller,
        string $method
    )
    {
        parent::__construct($model, $controller, $method);
    }

    public function users(?string $username = null): void
    {
        $this->requireAuth();

        if(!$this->requireGet()):
            return;
        endif;
        $Users = new User;
        if($username === null):
            $result = $Users->apiList();
            if($result->fails()):
                $this->jsonError(
                    code: 'query_failed',
                    message: 'Unable to retrieve users',
                    status: 500,
                );
                return;
            endif;

            $this->json([
                'data' => $result->all(),
            ]);
            return;
        endif;

        $result = $Users->apiByUsername($username);

        if ($result->fails()):
            $this->jsonError(
                code: 'query_failed',
                message: 'Unable to retrieve user.',
                status: 500
            );

            return;
        endif;

        if ($result->isEmpty()):
            $this->jsonError(
                code: 'not_found',
                message: 'User not found.',
                status: 404
            );

            return;
        endif;

        $this->json([
            'data' => $result->first(),
        ]);

    }


    protected function json(
        mixed   $data,
        int     $status = 200,
    ): void
    {
        $this->disableRender();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }

    protected function jsonError(
        string  $code,
        string  $message,
        int     $status
    ): void
    {
        $this->json(
            [
                'error' => [
                'code' => $code,
                'message' => $message,
                ]
            ],
            $status
        );
    }


    /** Guard that enforces only GET requests.
     *  Add this to every method/endpoint
     *  you wish to publish in this API:
     *  if(!$this->requireGet()): return; endif;
     */
    protected function requireGet(): bool
    {
        if(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'):
            return true;
        endif;

        header('Allow: GET');

        $this->jsonError(
            code: 'method_not_allowed',
            message: 'This API is read-only',
            status: 405
        );
        return false;
    }
}