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
    private string $token;
    private Cora $cora;

    public function __construct(Cora $cora,  bool $debug = false)
    {
        $this->cora = $cora;
        $this->base_url = !$cora->getIsProduction() ? EnvironmentUrls::sandbox_url : EnvironmentUrls::production_url;
        $this->debug = $debug;
        $this->sandbox = !$cora->getIsProduction();
        $this->token = $cora->getToken();
    }

    public function requisicao(string $uri, string $metodo, ?array $corpo = null, ?array $queryparams = null): string | GuzzleException | array | stdClass | null
    {
        try {
            $client = new \GuzzleHttp\Client();
            $access_token = $this->token;

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ];

            if (isset($corpo['idempotencyKey']) && !empty($corpo['idempotencyKey'])) {
                $headers['Idempotency-Key'] = $corpo['idempotencyKey'];
                unset($corpo['idempotencyKey']);
            }

            $response = $client->request($metodo, $this->base_url . $uri, [
                'debug' => $this->debug,
                'headers' => $headers,
                'json' => $corpo,
                'query' => $queryparams
            ]);

            if(strpos($uri, 'endpoints') !== false && strtoupper($metodo) === 'POST') {
                return $response->getBody()->getContents();
            }

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
