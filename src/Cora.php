<?php

namespace NFService\Cora;

use App\Models\Account;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Cora\Formater\RequestMaker;
use NFService\Cora\Options\EnvironmentUrls;
use NFService\Cora\Services\Account as ServicesAccount;
use NFService\Cora\Services\Boleto as ServicesBoleto;
use NFService\Cora\Services\Webhook;

class Cora
{
    private string $base_url;
    private bool $isProduction;
    private string $client_id;
    private string $client_secret_id;
    private string $refresh_token;
    private string $code;
    private string $account_id;
    private string $token;
    private int $expires_in;
    private RequestMaker $requestMaker;

    public function __construct(
        bool $isProduction = true,
        string $client_id,
        string $client_secret_id,
        string $code = '',
        string $account_id = null,
        string $token = null
    ) {
        if(empty($client_id)) {
            throw new Exception('Client ID é obrigatório');
        }
        if(empty($client_secret_id)) {
            throw new Exception('Client Secret é obrigatório');
        }

        $this->isProduction = $isProduction;
        $this->base_url = $isProduction ? EnvironmentUrls::auth_url_production : EnvironmentUrls::auth_url_sandbox;
        $this->client_id = $client_id;
        $this->client_secret_id = $client_secret_id;
        $this->code = $code;
        $this->account_id = $account_id;
        $this->token = $token ?? $this->gerarToken();
        $this->requestMaker = new RequestMaker($this, false);
        $this->expires_in = 0;
    }

    public function gerarToken(): string | GuzzleException
    {

        if(!empty($this->token)) {
            return $this->token;
        }

        $client = new \GuzzleHttp\Client();

        try {
            $env = (string) config('app.environment')[config('app.env')];
            $redirect_uri = match($env) {
                '1' => EnvironmentUrls::redirect_uri_production,
                '2' => EnvironmentUrls::redirect_uri_local,
                '3' => EnvironmentUrls::redirect_uri_sandbox,
            };
            $authorization = base64_encode($this->client_id . ':' . $this->client_secret_id);
            $redirectUri = $redirect_uri . '?account_id=' . $this->account_id;

            $response = $client->request('POST', $this->base_url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . $authorization,
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $this->code,
                    'redirect_uri' => $redirectUri,
                ],
            ]);


            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            $this->expires_in = time() + $responseData['expires_in'];
            $this->token = $responseData['access_token'];
            $this->refresh_token = $responseData['refresh_token'];

            $responseData['expires_in'] = $this->expires_in;
            $responseBody = json_encode($responseData);

            return $responseBody;
        } catch (GuzzleException $e) {
            $errorMessage = $e->getMessage();

            // Extrair o erro e a descrição do erro da mensagem de erro
            preg_match('/"error":\\"([^\\"]+)",\\"error_description":\\"([^\\"]+)\\"/', $errorMessage, $matches);

            if (count($matches) == 3) {
                $error = $matches[1];
                $errorDescription = $matches[2];

                $errorData = [
                    'errors' => $error . ' - ' . $errorDescription
                ];

                return json_encode($errorData);
            } else {
                return $errorMessage;
            }
        }
    }

    public function refreshToken(string $refresh_token): string | GuzzleException
    {
        $client = new \GuzzleHttp\Client();

        try {
            $authorization = base64_encode($this->client_id . ':' . $this->client_secret_id);

            $response = $client->request('POST', $this->base_url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . $authorization,
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token,
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            $this->expires_in = time() + $responseData['expires_in'];
            $this->token = $responseData['access_token'];
            $this->refresh_token = $responseData['refresh_token'];

            $responseData['expires_in'] = $this->expires_in;
            $responseBody = json_encode($responseData);

            return $responseBody;
        } catch (GuzzleException $e) {
            $errorMessage = $e->getMessage();

            // Extrair o erro e a descrição do erro da mensagem de erro
            preg_match('/"error":\\"([^\\"]+)",\\"error_description":\\"([^\\"]+)\\"/', $errorMessage, $matches);

            if (count($matches) == 3) {
                $error = $matches[1];
                $errorDescription = $matches[2];

                $errorData = [
                    'errors' => $error . ' - ' . $errorDescription
                ];

                return json_encode($errorData);
            } else {
                return $errorMessage;
            }
        }
    }

    public function getToken(): string
    {
        if(empty($this->token)) {
            $this->gerarToken();
        }

        return $this->token;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function getClientSecretId(): string
    {
        return $this->client_secret_id;
    }

    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAccountId(): string
    {
        return $this->account_id;
    }

    public function getExpiresIn(): int
    {
        return $this->expires_in;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function getIsProduction(): bool
    {
        return $this->isProduction;
    }

    public function getRequestMaker(): RequestMaker {
        return $this->requestMaker;
    }

    public function account(string $token = null, bool $debug = false): ServicesAccount
    {
        return new ServicesAccount($this->getRequestMaker());
    }

    public function boleto(string $token = null, bool $debug = false): ServicesBoleto
    {
        return new ServicesBoleto($this->getRequestMaker(), $token);
    }

    public function webhook(string $chave = null, bool $debug = false): Webhook
    {
        return new Webhook($this->getRequestMaker(), $chave);
    }
}
