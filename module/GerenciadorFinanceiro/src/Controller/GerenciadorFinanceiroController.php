<?php

// module/Application/src/Controller/GerenciadorFinanceiroController.php

namespace GerenciadorFinanceiro\Controller;

use GerenciadorFinanceiro\Form\BuscaListadoForm;
use GerenciadorFinanceiro\Form\CreditoForm;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use GerenciadorFinanceiro\Form\ContaForm;
use Laminas\Mvc\Controller\AbstractActionController;
use GerenciadorFinanceiro\Model\GerenciadorFinanceiro;
use GerenciadorFinanceiro\Model\GerenciadorFinanceiroTable;

class GerenciadorFinanceiroController extends AbstractActionController
{
    private $gerenciadorFinanceiroTable;

    public function __construct(GerenciadorFinanceiroTable $gerenciadorFinanceiroTable)
    {
        $this->gerenciadorFinanceiroTable = $gerenciadorFinanceiroTable;
    }

    public function indexAction()
    {
        $this->layout()->setTemplate('layout');

        $contasInfo = $contas = $this->gerenciadorFinanceiroTable->searchBillings();
        $creditosInfo = $creditos = $this->gerenciadorFinanceiroTable->searchCreditos();

        $totalContas = array_sum(array_column($contas, 'valor'));
        $totalCreditos = array_sum(array_column($creditos, 'valor'));
        $totalMesAtual = ($totalCreditos - $totalContas);

        $today = new \DateTime();
        $lastDayOfMonth = new \DateTime('last day of ' . $today->format('Y-m'));

        $today->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
        $lastDayOfMonth->setTimezone(new \DateTimeZone('America/Sao_Paulo'));

        if ($today->format('d') === $lastDayOfMonth->format('d')) {
            $this->gerenciadorFinanceiroTable->saveTotalMes($totalMesAtual);
        }

        $previousMonth = clone $today;
        $previousMonth->modify('first day of last month');

        $lastTotalMes = $this->gerenciadorFinanceiroTable->searchTotalMes($previousMonth->format('Y-m'));
        $totalMes = ($lastTotalMes !== null ? $lastTotalMes : 0) + $totalMesAtual;

        return new ViewModel([
            'contas' => $contas,
            'creditos' => $creditos,
            'totalContas' => $totalContas,
            'totalCreditos' => $totalCreditos,
            'totalMes' => $totalMes,
            'contasInfo' => $contasInfo,
            'creditosInfo'=> $creditosInfo,
        ]);
    }

    public function addContaAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $bancos = $this->gerenciadorFinanceiroTable->getBancos();
        // dd($bancos);
        $form = new ContaForm($bancos);
        $form->get('submit')->setValue('Adicionar');

        $redirect = null; // Inicializa a variável de redirecionamento

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            // dd($data);
            $data = array_map('htmlspecialchars', $data);

            $form->setData($data);

            if ($form->isValid()) {
                // Se recorrente for 1 e data_termino_recorrente estiver definido
                if ($data['recorrente'] == 1 && isset($data['data_termino_recorrente'])) {
                    $dates = [];
                    $currentDate = new \DateTimeImmutable($data['data']);

                    while ($currentDate <= new \DateTimeImmutable($data['data_termino_recorrente'])) {
                        $dates[] = $currentDate->format('Y-m-d');
                        $currentDate = $currentDate->modify('+1 month');
                    }

                    // Salve uma conta para cada data mensal
                    foreach ($dates as $date) {
                        // Atualize a data_termino_recorrente para a data na primeira iteração
                        if (!isset($dataTerminoRecorrente)) {
                            $dataTerminoRecorrente = $date;
                        }

                        // Atualize a data para o mês atual
                        $data['data_termino_recorrente'] = $date;
                        // Defina o status com base na iteração
                        $data['status'] = ($date === reset($dates)) ? $data['status'] : 0;
                        // Salve a conta mensal com os campos adicionais
                        $this->gerenciadorFinanceiroTable->saveConta($data);
                    }

                    // Restaure a data_termino_recorrente para o valor inicial após o loop
                    $data['data_termino_recorrente'] = $dataTerminoRecorrente;
                } else {
                    // Se não for recorrente ou data_termino_recorrente não estiver definido, salve a conta única
                    $this->gerenciadorFinanceiroTable->saveConta($data);
                }

                // Define o redirecionamento após o loop
                $redirect = $this->redirect()->toRoute('menu');
            }
        }

        // Redireciona se necessário
        if ($redirect !== null) {
            return $redirect;
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function editContaAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'addConta']);
        }

        try {
            $conta = $this->gerenciadorFinanceiroTable->getConta($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'index']);
        }

        $bancos = $this->gerenciadorFinanceiroTable->getBancos();
        $form = new ContaForm($bancos);
        $form->bind($conta);
        $form->get('submit')->setAttribute('value', 'Salvar');

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $contaData = $form->getData()->getArrayCopy();
                $this->gerenciadorFinanceiroTable->saveConta($contaData);
                return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'index']);
            }
        }

        return new ViewModel([
            'id' => $id,
            'form' => $form,
        ]);
    }

    public function deleteContaAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('gerenciador-financeiro');
        }

        if ($request->isPost()) {
            $del = $request->getPost('del', 'Não');

            if ($del == 'Sim') {
                $id = (int) $request->getPost('id');
                $this->gerenciadorFinanceiroTable->deleteConta($id);
            }

            return $this->redirect()->toRoute('gerenciador-financeiro');
        }

        return new ViewModel([
            'id' => $id,
            'conta' => $this->gerenciadorFinanceiroTable->getConta($id),
        ]);
    }

    public function addCreditoAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $bancos = $this->gerenciadorFinanceiroTable->getBancos();
        $form = new CreditoForm($bancos);
        $form->get('submit')->setValue('Adicionar Crédito');

        $redirect = null; // Inicializa a variável de redirecionamento

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            // dd($data);
            $data = array_map('htmlspecialchars', $data);

            $form->setData($data);

            if ($form->isValid()) {
                // Se recorrente for 1 e data_termino_recorrente estiver definido
                if ($data['recorrente'] == 1 && isset($data['data_termino_recorrente'])) {
                    $dates = [];
                    $currentDate = new \DateTimeImmutable($data['data']);

                    while ($currentDate <= new \DateTimeImmutable($data['data_termino_recorrente'])) {
                        $dates[] = $currentDate->format('Y-m-d');
                        $currentDate = $currentDate->modify('+1 month');
                    }

                    // Salve um crédito para cada data mensal
                    foreach ($dates as $date) {
                        // Atualize a data_termino_recorrente para a data na primeira iteração
                        if (!isset($dataTerminoRecorrente)) {
                            $dataTerminoRecorrente = $date;
                        }

                        // Atualize a data para o mês atual
                        $data['data_termino_recorrente'] = $date;

                        // Defina o status com base na iteração
                        $data['status'] = ($date === reset($dates)) ? $data['status'] : 0;

                        // Salve o crédito mensal com os campos adicionais
                        $this->gerenciadorFinanceiroTable->saveCredito($data);
                    }

                    // Restaure a data_termino_recorrente para o valor inicial após o loop
                    $data['data_termino_recorrente'] = $dataTerminoRecorrente;
                } else {
                    // Se não for recorrente ou data_termino_recorrente não estiver definido, salve o crédito único
                    $this->gerenciadorFinanceiroTable->saveCredito($data);
                }

                // Define o redirecionamento após salvar o crédito
                $redirect = $this->redirect()->toRoute('menu');
            }
        }

        // Redireciona se necessário
        if ($redirect !== null) {
            return $redirect;
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }


    public function editCreditoAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'addCredito']);
        }

        try {
            $credito = $this->gerenciadorFinanceiroTable->getCredito($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'index']);
        }

        $bancos = $this->gerenciadorFinanceiroTable->getBancos();
        $form = new CreditoForm($bancos);
        $form->bind($credito);
        $form->get('submit')->setAttribute('value', 'Salvar');

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $creditoData = $form->getData()->getArrayCopy();
                $this->gerenciadorFinanceiroTable->saveCredito($creditoData);
                return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'index']);
            }
        }

        return new ViewModel([
            'id' => $id,
            'form' => $form,
        ]);
    }

    public function deleteCreditoAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('gerenciador-financeiro');
        }

        if ($request->isPost()) {
            $del = $request->getPost('del', 'Não');

            if ($del == 'Sim') {
                $id = (int) $request->getPost('id');
                $this->gerenciadorFinanceiroTable->deleteCredito($id);
            }

            return $this->redirect()->toRoute('gerenciador-financeiro', ['action' => 'index']);
        }

        return new ViewModel([
            'id' => $id,
            'credito' => $this->gerenciadorFinanceiroTable->getCredito($id),
        ]);
    }

    public function buscaListadoContasAction()
    {
        $this->layout()->setTemplate('layout');

        $form = new BuscaListadoForm();

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function listadoContasAction()
    {
        $this->layout()->setTemplate('layout');

        /** @var Request $request */
        $request = $this->getRequest();

        $listadoContas = [];
        $totalContas = 0;

        if ($request->isPost()) {

            $formData = [
                "dataDesde" => htmlspecialchars($request->getPost("dataDesde") ?? date('Y-m-d')),
                "dataFim" => htmlspecialchars($request->getPost("dataFim") ?? date('Y-m-d')),
            ];

            $listadoContas = $this->gerenciadorFinanceiroTable->getListadoContas($formData);
            $totalContas = array_sum(array_column($listadoContas, 'valor'));
        } elseif ($request->getPost('view') === 'mesAtual') {

            $formData = [
                "dataDesde" => date('Y-m-01'),
                "dataFim" => date('Y-m-t'),
            ];
            $listadoContas = $this->gerenciadorFinanceiroTable->getListadoContas($formData);
            $totalContas = array_sum(array_column($listadoContas, 'valor'));
        }

        return new ViewModel([
            'listadoContas' => $listadoContas,
            'totalContas' => $totalContas,
        ]);
    }


    public function buscaListadoCreditoAction()
    {
        $this->layout()->setTemplate('layout');

        $form = new BuscaListadoForm();

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function listadoCreditoAction()
    {
        $this->layout()->setTemplate('layout');

       /** @var Request $request */
        $request = $this->getRequest();

        $listadoContas = [];

        if ($request->isPost()) {
            $formData = [
                "dataDesde" => htmlspecialchars($request->getPost("dataDesde") ?? date('Y:m:d')),
                "dataFim" => htmlspecialchars($request->getPost("dataFim") ?? date('Y:m:d')),
            ];

            $listadoCreditos = $this->gerenciadorFinanceiroTable->getListadoCredito($formData);
            $totalCreditos = array_sum(array_column($listadoContas,'valor'));
        }

        return new ViewModel([
            'listadoCreditos' => $listadoCreditos,
            'totalCreditos' => $totalCreditos,
        ]);
    }
}
