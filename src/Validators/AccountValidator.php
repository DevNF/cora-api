<?php
namespace NFService\Cora\Validators;
use Exception;
use Valitron\Validator;
class AccountValidator
{

    private array $body;

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function validateAccount()
    {
        $v = (new Validator($this->body));
        // $v->rule('required', ['agency', 'accountNumber', 'accountDigit', 'bankCode', 'bankName']);
        // $v->rule('lengthMax', ['agency', 'accountNumber', 'accountDigit', 'bankCode', 'bankName'], 255);

        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateBalance()
    {

        $v = (new Validator($this->body));
        $v->rule('required', ['balance']);

        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateBankStatement()
    {
        $v = (new Validator($this->body));
        $v->rule('optional', ['start', 'end', 'type', 'transaction_type', 'page', 'perPage', 'aggr']);
        if (!$v->validate()) {
            $errors = $v->errors();
            foreach ($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

}
