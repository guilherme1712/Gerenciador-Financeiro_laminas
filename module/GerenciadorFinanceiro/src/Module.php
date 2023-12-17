<?php

// module/GerenciadorFinanceiro/src/Module.php

namespace GerenciadorFinanceiro;

use GerenciadorFinanceiro\Model\ListadoTable;
use Model\MenuTableTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use GerenciadorFinanceiro\Model\GerenciadorFinanceiroPrototype;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\GerenciadorFinanceiroTable::class => function ($container) {
                    $tableGateway = $container->get(Model\GerenciadorFinanceiroTableGateway::class);
                    return new Model\GerenciadorFinanceiroTable($tableGateway);
                },
                Model\GerenciadorFinanceiroTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new GerenciadorFinanceiroPrototype());
                    return new TableGateway('gerenciadorFinanceiro', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\GerenciadorFinanceiroController::class => function ($container) {
                    return new Controller\GerenciadorFinanceiroController(
                        $container->get(Model\GerenciadorFinanceiroTable::class)
                    );
                },
                Controller\MenuController::class => function ($container) {
                    return new Controller\MenuController(
                        $container->get(Model\GerenciadorFinanceiroTable::class)
                    );
                },
            ],
        ];
    }
}
