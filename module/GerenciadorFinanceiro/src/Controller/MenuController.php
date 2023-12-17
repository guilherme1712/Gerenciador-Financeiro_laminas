<?php

// module/Application/src/Controller/GerenciadorFinanceiroController.php

namespace GerenciadorFinanceiro\Controller;

use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use GerenciadorFinanceiro\Form\ContaForm;
use Laminas\Mvc\Controller\AbstractActionController;
use GerenciadorFinanceiro\Model\GerenciadorFinanceiro;
use GerenciadorFinanceiro\Model\GerenciadorFinanceiroTable;

class MenuController extends AbstractActionController
{
    private $gerenciadorFinanceiroTable;
    private $contaForm;

    public function __construct(GerenciadorFinanceiroTable $gerenciadorFinanceiroTable)
    {
        $this->gerenciadorFinanceiroTable = $gerenciadorFinanceiroTable;
        $bancos = $this->gerenciadorFinanceiroTable->getBancos();
        $this->contaForm = new ContaForm($bancos);
    }

    public function menuAction()
    {
        $this->layout()->setTemplate('layout-menu');

        // $menu = [
        //     [
        //         'id'   => 31,
        //         'name' => 'MENU',
        //         'link' => 'gerenciador-financeiro/addConta',
        //         'icon' => 'bll-icon bll-Asistencia_estancias_especiales_1',
        //     ],
        //     // Adicione outros itens do menu conforme necessário
        // ];

        return new ViewModel();
    }
}
