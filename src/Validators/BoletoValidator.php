<?php
namespace NFService\Cora\Validators;
use Exception;
use Valitron\Validator;
class BoletoValidator
{

    private array $body;
    private string $moneyFormat = '/^\d{1,10}\.\d{2}$/';

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function validateInvoice()
    {
        // throw new \Exception('Response: '.json_encode($this->body['code']), true);

        $v = (new Validator($this->body));
        $v->rule('required', ['code', 'customer', 'services', 'payment_terms']);
        $v->rule('array', ['customer', 'services', 'payment_terms']);
        $v->rule('required', 'customer.document.identity');
        $v->rule('required', 'customer.name');
        $v->rule('lengthMax', 'customer.name', 260);
        $v->rule('email', 'customer.email');
        $v->rule('lengthMax', 'customer.email', 60);
        $v->rule('required', 'customer.document');
        $v->rule('required', 'customer.document.identity');
        $v->rule('regex', 'customer.document.identity', '/^\d{1,14}$/');
        $v->rule('required', 'customer.document.type');
        $v->rule('in', 'customer.document.type', ['CPF', 'CNPJ']);
        $v->rule('required', 'customer.address');
        $v->rule('required', 'customer.address.street');
        $v->rule('required', 'payment_terms.due_date');
        $v->rule('dateFormat', 'payment_terms.due_date', 'Y-m-d');
        $v->rule('date', 'payment_terms.due_date', function ($data) {
            return strtotime($data['payment_terms']['due_date']) >= strtotime(date('Y-m-d'));
        });
        $v->rule('lengthMax', 'customer.address.street', 60);
        $v->rule('required', 'customer.address.number');
        $v->rule('lengthMax', 'customer.address.number', 8);
        $v->rule('required', 'customer.address.district');
        $v->rule('lengthMax', 'customer.address.district', 60);
        $v->rule('required', 'customer.address.city');
        $v->rule('lengthMax', 'customer.address.city', 60);
        $v->rule('required', 'customer.address.state');
        $v->rule('regex', 'customer.address.state', '/^\w{2}$/');
        $v->rule('requiredWith', 'customer.address.complement', ['customer.address.complement', '!empty']);
        $v->rule('lengthMax', 'customer.address.complement', 60);
        $v->rule('required', 'customer.address.zip_code');
        $v->rule('regex', 'customer.address.zip_code', '/^\d{5}(?:-?\d{3})?$/');
        $v->rule('required', 'services');
        $v->rule('required', 'payment_terms.due_date');
        $v->rule('dateFormat', 'payment_terms.due_date', 'Y-m-d');
        $v->rule('numeric', 'payment_terms.fine.rate')->rule('requiredWith','payment_terms.fine');
        $v->rule('numeric', 'payment_terms.interest.rate')->rule('requiredWith','payment_terms.interest');
        $v->rule('numeric', 'payment_terms.discount.value')->rule('requiredWith','payment_terms.discount');
        $v->rule('in', 'payment_terms.discount.type', ['FIXED', 'PERCENT'])->rule('requiredWith','payment_terms.discount');

        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateInstallment()
    {
        $v = (new Validator($this->body));
        $v->rule('integer', 'installment.number');
        $v->rule('integer', 'installment.number_first');
        $v->rule('required', 'installment.number');
        $v->rule('required', 'installment.number_first');
        $v->rule('between', 'installment.number', 1, 24);
        $v->rule('between', 'installment.number_first', 1, 24);
        $v->rule('array', ['customer', 'service', 'payment_terms', 'installment', 'payment_forms']);
        $v->rule('required', ['customer', 'service', 'installment', 'payment_forms']);
        $v->rule('requiredWithout', 'installment.number', 'installment.number_first');
        $v->rule('required', 'payment_forms.*', ['BANK_SLIP']);
        $v->rule('array', 'payment_forms');
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateGetInstallment()
    {
        $v = (new Validator($this->body));
        $v->rule('dateFormat', 'start', 'Y-m-d');
        $v->rule('dateFormat', 'end', 'Y-m-d');
        $v->rule('in', 'state', ['CANCELLED', 'DRAFT', 'LATE', 'OPEN', 'PAID', 'RECURRENCE_DRAFT']);
        $v->rule('regex', 'search', '/^\d{1,14}$/');
        $v->rule('integer', 'page');
        $v->rule('integer', 'perPage')->between(1, 200);
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateInstallmentID()
    {
        $v = (new Validator($this->body));
        $v->rule('required', 'invoice_id');
        $v->rule('string', 'invoice_id');
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateDelete()
    {
        $v = (new Validator($this->body));
        $v->rule('required', 'id');
        $v->rule('string', 'id');
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }

    public function validateInvoiceNotifications()
    {
        $v = (new Validator($this->body));
        $v->rule('required', 'invoice_id');
        $v->rule('string', 'invoice_id');
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }


    public function validateDeleteNotifications()
    {
        $v = (new Validator($this->body));
        $v->rule('required', 'invoice_id');
        $v->rule('string', 'invoice_id');
        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new Exception('Erro de validação: ' . implode(', ', $errors));
        }
        return true;
    }
    public function validatePatch()
    {
        $v = (new Validator($this->body));
        $v->rule('required', ['calendario', 'devedor', 'valor']);
        $v->rule('array', ['calendario', 'devedor', 'valor']);
        $v->rule('required', ['calendario.dataDeVencimento',
        'calendario.validadeAposVencimento', 'devedor.nome', 'valor.original', 'chave']);
        $v->rule('numeric', 'valor.original');
        $v->rule('regex', 'valor.original', $this->moneyFormat);
        $v->rule('regex', 'valor.multa.valorPerc', $this->moneyFormat);
        $v->rule('regex', 'valor.juros.valorPerc', $this->moneyFormat);
        $v->rule('regex', 'valor.abatimento.valorPerc', $this->moneyFormat);
        $v->rule('regex', 'valor.desconto.descontoDataFixa.*.valorPerc', $this->moneyFormat);
        $v->rule('dateFormat', 'valor.desconto.descontoDataFixa.*.data', 'Y-m-d');

        $v->rule('required', 'chave', 77);
        $v->rule('lengthMax', 'chave', 77);
        $v->rule('lengthMax', 'solicitacaoPagador', 140);
        $v->rule('lengthMax', 'infoAdicionais.*.nome', 50);
        $v->rule('lengthMax', 'infoAdicionais.*.valor', 200);
        $v->rule('requiredWithout', 'devedor.cpf', 'devedor.cnpj');
        $v->rule('requiredWithout', 'devedor.cnpj', 'devedor.cpf');

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
