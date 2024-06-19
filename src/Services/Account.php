<?php

namespace NFService\Cora\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Formater\RequestMaker;
use NFService\Cora\Helpers\QRCodeGenerator;
use NFService\Cora\Validators\AccountValidator;
use stdClass;

class Account
{
    private RequestMaker $req;

    public function __construct(RequestMaker $req)
    {
        $this->req = $req;
    }

    public function consultaDadosConta(array $body): string | GuzzleException | array | stdClass
    {
        $accountValidator = new AccountValidator($body);
        $accountValidator->validateAccount();

        try {
            return $this->req->requisicao('third-party/account', 'GET', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }
    public function consultaSaldo(array $body): string | GuzzleException | array | stdClass
    {
        $accountValidator = new AccountValidator($body);
        $accountValidator->validateBalance();

        try {
            return $this->req->requisicao('third-party/account/balance', 'GET', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }
    public function consultaExtrato(array $body): string | GuzzleException | array | stdClass
    {
        $accountValidator = new AccountValidator($body);
        $accountValidator->validateBankStatement();

        try {
            return $this->req->requisicao('bank-statement/statement', 'GET', queryparams:$body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

}

