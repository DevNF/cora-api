<?php
namespace NFService\Cora\Options;

class EnvironmentUrls
{
    // urls para Parceria Cora (existem url para api direto, porém utilizaremos Parceria)
    public const auth_url_sandbox = 'https://api.stage.cora.com.br/oauth/token';
    public const auth_url_production = 'https://api.cora.com.br/oauth/token';
    public const production_url = 'https://api.cora.com.br/';
    public const sandbox_url = 'https://api.stage.cora.com.br/';
}
