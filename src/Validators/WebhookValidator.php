<?php
namespace NFService\Cora\Validators;
use Exception;
use Valitron\Validator;
class WebhookValidator
{

    private array $body;

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function validateWebhook()
    {
        $v = (new Validator($this->body));
        $v->rule('required', ['url', 'resource', 'trigger']);
        $v->rule('LengthMax', 'url', 200);

        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }


}
