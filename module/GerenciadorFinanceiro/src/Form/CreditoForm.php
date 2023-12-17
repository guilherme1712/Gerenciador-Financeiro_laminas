<?php
// module/GerenciadorFinanceiro/src/Form/CreditoForm.php

namespace GerenciadorFinanceiro\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Select;

class CreditoForm extends Form
{
    public function __construct($bancosInfo = null)
    {
        $bancos = $this->formatBancos($bancosInfo);
        parent::__construct('credito');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'data',
            'type' => 'date',
            'options' => [
                'label' => 'Data:',
            ],
            'attributes' => [
                'value' => date("Y-m-d"),
            ],
        ]);

        $this->add([
            'name' => 'descricao',
            'type' => 'text',
            'options' => [
                'label' => 'Descrição:',
            ],
        ]);

        $this->add([
            'name' => 'valor',
            'type' => 'text',
            'options' => [
                'label' => 'Valor:',
            ],
        ]);

        $this->add([
            'name' => 'recorrente',
            'type' => Select::class,
            'options' => [
                'label' => 'Recorrente?',
                'value_options' => [
                    '1' => 'Sim',
                    '0' => 'Não',
                ],
            ],
            'attributes' => [
                'value' => '1',
            ],
        ]);

        $this->add([
            'name' => 'data_termino_recorrente',
            'type' => Date::class,
            'options' => [
                'label' => 'Até quando é recorrente?',
            ],
            'attributes' => [
                // 'min' => date('Y-m-d', strtotime('-15 days')),
                'value' => date('Y-m-d'),
            ],
            'required' => false,
        ]);

        $this->add([
            'name' => 'status',
            'type' => Select::class,
            'options' => [
                'label' => 'Status:',
                'value_options' => [
                    '1' => 'Creditado',
                    '0' => 'Não Creditado',
                ],
            ],
            'attributes' => [
                'value' => '1',
            ],
        ]);

        $this->add([
            'name' => 'banco',
            'type' => Select::class,
            'options' => [
                'label' => 'Banco:',
                'value_options' => $bancos,
            ],
            'attributes' => [
                'value' => '1',
            ],
        ]);

        $this->add([
            'name' => 'categoria',
            'type' => Select::class,
            'options' => [
                'label' => 'Categoria:',
                'value_options' => [
                    '1' => 'Salário',
                    '2' => 'Tranfência',
                    '3' => 'Pix',
                    '4' => 'Outros',
                ],
                'empty_option'  => "Selecionar",
            ],
            'attributes' => [
                'value' => '',
            ],
            'required' => false,
        ]);


        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Adicionar Crédito',
                'id' => 'submitbutton',
            ],
        ]);
    }

    public function formatBancos($bancos)
    {
        $formatedBancos = [];

        foreach($bancos as $banco){
           $formatedBancos[$banco['id_banco']] = $banco['nome'];
        }

        return $formatedBancos;
    }
}
