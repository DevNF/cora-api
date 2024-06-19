<?php

namespace NFService\Cora\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Formater\RequestMaker;
use NFService\Cora\Helpers\QRCodeGenerator;
use NFService\Cora\Validators\BoletoValidator;
use NFService\Cora\Services\Account;
use Ramsey\Uuid\Uuid;
use stdClass;

class Boleto extends Account
{
    private RequestMaker $req;
    private ?string $coraid;
    public function __construct(RequestMaker $req, ?string $coraid = null)
    {
        parent::__construct($req);

        $this->req = $req;
        $this->coraid = $coraid;
    }

    public function criarBoleto(array $body): string | GuzzleException | array | stdClass
    {
        $cobValidator = new BoletoValidator($body);
        $cobValidator->validateInvoice();

        try {
            return $this->req->requisicao("v2/invoices/", 'POST', $body);
            } catch (GuzzleException $e) {
            return $e;
        }

    }

    public function criarCarne(array $body): string | GuzzleException | array | stdClass
    {
        $cobValidator = new BoletoValidator($body);
        $cobValidator->validateInstallment();

        try {
            return $this->req->requisicao("v2/invoices/installments", 'POST', $body);
        } catch (GuzzleException $e) {
            return $e;
        }

    }

    public function consultarBoletos(array $body): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("v2/invoices", 'GET', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function consultarDetalhesBoleto(string $invoice_id): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("v2/invoices/{$invoice_id}", 'GET');
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function cancelarBoleto(string $invoice_id): string | GuzzleException | array | stdClass | null
    {
        try {
            $response = $this->req->requisicao("v2/invoices/{$invoice_id}", 'DELETE');

            if ($response === null) {
                return null;
            }

            return $response;
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function pagarBoleto(array $body): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("invoices/pay", 'POST', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    /**
     * Consulta de notificações do boleto
     *
     * @param string $invoice_id
     * @return string | GuzzleException | array | stdClass
     */
    public function consultarNotificacoesBoleto(string $invoice_id): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("v2/invoices/{$invoice_id}/notifications", 'GET');
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    /**
     * Cancele as notificações agendadas para um boleto
     *
     * @param string $invoice_id
     * @return string | GuzzleException | array | stdClass
     */
    public function cancelarNotificacoesBoleto(string $invoice_id): string | GuzzleException | array | stdClass
    {
        try {
            return $this->req->requisicao("v2/invoices/{$invoice_id}/notifications", 'DELETE');
        } catch (GuzzleException $e) {
            return $e;
        }
    }
}

