<?php

namespace NFService\Cora\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Formater\RequestMaker;
use NFService\Cora\Helpers\TxidGenerator;
use NFService\Cora\Validators\WebhookValidator;
use stdClass;

class Webhook
{
    private RequestMaker $req;

    public function __construct(RequestMaker $req)
    {
        $this->req = $req;
    }


    public function criarWebhook(array $body): string | GuzzleException | array | stdClass | null
    {

        $webhookValidator = new WebhookValidator($body);
        $webhookValidator->validateWebhook();

        try {
            return $this->req->requisicao("endpoints", 'POST', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function listarWebhooks(?array $options = []): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("endpoints", 'GET');
        } catch (GuzzleException $e) {
            return $e;
        }

    }

    public function deletarWebhook(string $id): string | GuzzleException | array | stdClass | null
    {
        try {
            return $this->req->requisicao("endpoints/{$id}", 'DELETE');
        } catch (GuzzleException $e) {
            return $e;
        }
    }
}
