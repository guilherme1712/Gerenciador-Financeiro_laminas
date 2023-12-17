<?php

// module/GerenciadorFinanceiro/src/Form/BuscaListadoForm.php

namespace GerenciadorFinanceiro\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Submit;

class BuscaListadoForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->add([
            'type' => Date::class,
            'name' => 'dataDesde',
            'options' => [
                'label' => 'Data Desde',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id'    => 'dataDesde',
                'value' => date('Y-m-01'),
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dataFim',
            'options' => [
                'label' => 'Data Fim',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id'    => 'dataFim', // Corrigido o id para 'dataFim'
                'value' => date("Y-m-t"),
            ],
        ]);

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Buscar',
                'id' => 'submitbutton',
            ],
        ]);

        // // Adiciona o botão "Ver Contas do Mês Atual"
        // $this->add([
        //     'type' => Submit::class,
        //     'name' => 'verMesAtual',
        //     'options' => [
        //         'label' => 'Ver Contas do Mês Atual',
        //     ],
        //     'attributes' => [
        //         'value' => '1',
        //         'class' => 'btn-primary', // Usa a mesma classe CSS do estilo anterior
        //         'id' => 'verMesAtualButton',
        //     ],
        // ]);
    }
}
