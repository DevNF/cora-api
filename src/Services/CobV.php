<?php

namespace NFService\Cora\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Formater\RequestMaker;
use NFService\Cora\Helpers\QRCodeGenerator;
use NFService\Cora\Validators\CobVValidator;
use NFService\Cora\Helpers\TxidGenerator;
use Ramsey\Uuid\Uuid;
use stdClass;

class CobV extends Cob
{
    private RequestMaker $req;
    private ?string $txid;
    public function __construct(RequestMaker $req, ?string $txid = null)
    {
        parent::__construct($req);

        $this->req = $req;
        $this->txid = $txid;
    }


    public function criar(array $body): string | GuzzleException | array | stdClass
    {

        $cobValidator = new CobVValidator($body);
        $cobValidator->validatePut();

        try {
            try {
                $this->txid = TxidGenerator::generate();

                return $this->req->requisicao("/cobv/{$this->txid}", 'PUT', $body);
            } catch (Exception $e) {
                return $e;
            }

        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function atualizar (array $body): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para atualizar cobranças');
        }

        $cobValidator = new CobVValidator($body);
        $cobValidator->validatePatch();

        try {
            return $this->req->requisicao("/cobv/{$this->txid}", 'PATCH', $body);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function consultar(?array $options = []): string | GuzzleException | array | stdClass
    {
        $query = "?".http_build_query($options);

        if(!empty($this->txid)){
            $query = "/{$this->txid}";
        }

        try {
            return $this->req->requisicao("/cobv{$query}", 'GET');
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function cancelar(): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para cancelar cobranças');
        }

        try {
            return $this->req->requisicao("/cobv/{$this->txid}", 'PATCH', ['status' => 'REMOVIDA_PELO_USUARIO_RECEBEDOR']);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function gerarQRCode(): string | GuzzleException | array | stdClass
    {
        if(empty($this->txid)) {
            throw new Exception('Txid é obrigatório para gerar QRCode');
        }

        try {
            return QRCodeGenerator::generate($this->req->requisicao("/cobv/{$this->txid}", 'GET')->brcode);
        } catch (GuzzleException $e) {
            return $e;
        }
    }
}
