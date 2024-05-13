<?php

namespace NFService\Cora\Formater;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Options\EnvironmentUrls;
use NFService\Cora\Cora;
use stdClass;


class RequestMaker
{
    private bool $debug;
    private bool $sandbox;
    private string $base_url;
    private Cora $cora;
    private array $certificatePub;
    private array $certificatePriv;

    public function __construct(Cora $cora, bool $debug = false)
    {

        if($cora->getIsProduction()) {
            if(empty($cora->getCertificatePub())) {
                throw new Exception('Caminho do certificado público é obrigatório');
            }
            if(empty($cora->getCertificatePriv())) {
                throw new Exception('Caminho do certificado privado é obrigatório');
            }

        }
        $this->cora = $cora;
        $this->base_url = !$cora->getIsProduction() ? EnvironmentUrls::sandbox_url : EnvironmentUrls::production_url;
        $this->debug = $debug;
        $this->sandbox = !$cora->getIsProduction();
        $this->certificatePub = $cora->getCertificatePub();
        $this->certificatePriv = $cora->getCertificatePriv();
    }

    public function requisicao(string $uri, string $metodo, ?array $corpo = null): string | GuzzleException | array | stdClass | null
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request($metodo, $this->base_url . $uri, [
                'debug' => $this->debug,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->cora->getToken(),
                    'client_id' => $this->cora->getClientId(),
                    'Content-Type' => 'application/json'
                ],
                'json' => $corpo,
                'cert' => $this->certificatePub,
                'ssl_key' => $this->certificatePriv
            ]);


            return json_decode($response->getBody()->getContents());

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if($e->hasResponse()) {
                $res = json_decode($e->getResponse()->getBody()->getContents());

                if(!empty($res->detail)) return [
                    'message' => $res->detail,
                    'violacoes' => isset($res->violacoes) ? $res->violacoes : null
                ];
            }

            return $e->getMessage();
        } catch (\Exception $e) {
            return $e;
        }

    }
}
