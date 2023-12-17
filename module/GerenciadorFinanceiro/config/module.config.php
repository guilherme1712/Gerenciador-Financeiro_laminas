<?php

// module/GerenciadorFinanceiro/config/module.config.php

namespace GerenciadorFinanceiro;

use Model\ListadoTable;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use GerenciadorFinanceiro\Form\ContaForm;
use Laminas\Db\TableGateway\TableGateway;
use GerenciadorFinanceiro\Form\CreditoForm;

return [
    // 'controllers' => [
    //     'factories' => [
    //         GerenciadorFinanceiroController::class => InvokableFactory::class,
    //     ],
    // ],

    'service_manager' => [
        'factories' => [
            Model\GerenciadorFinanceiroTable::class => function ($container) {
                return new Model\GerenciadorFinanceiroTable($container->get('Laminas\Db\Adapter\Adapter'));
            },
        ],
    ],

    'form_elements' => [
        'factories' => [
            ContaForm::class => InvokableFactory::class,
            CreditoForm::class => InvokableFactory::class,
        ],
    ],

    // 'router' => [
    //     'routes' => [
    //         'gerenciador-financeiro' => [
    //             'type' => Segment::class,
    //             'options' => [
    //                 'route' => '/gerenciador-financeiro[/:action[/:id]]',
    //                 'defaults' => [
    //                     'controller' => Controller\GerenciadorFinanceiroController::class,
    //                     'action' => 'index',
    //                 ],
    //                 'constraints' => [
    //                     'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
    //                     'id' => '[0-9]+',
    //                 ],
    //             ],
    //         ],
    //     ],
    // ],

    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\MenuController::class,
                        'action'     => 'menu',
                    ],
                ],
            ],
            'menu' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/menu',
                    'defaults' => [
                        'controller' => Controller\MenuController::class,
                        'action'     => 'menu',
                    ],
                ],
                'may_terminate' => true,
            ],
            'gerenciador-financeiro' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/gerenciador-financeiro[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\GerenciadorFinanceiroController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'addConta' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/[:controller[/:action]]',
                            'defaults'    => [],
                        ],
                    ],
                    // Adicione outras rotas filhas conforme necessário
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'gerenciador-financeiro' => __DIR__ . '/../view',
        ],
        'template_map'        => [
            'layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout-menu' => __DIR__ . '/../view/layout/layout-menu.phtml',
            'layout-menu-listado' => __DIR__ . '/../view/layout/layout-menu-listado.phtml',
            'layout-geral' => __DIR__ . '/../view/layout/layout-geral.phtml',
        ],
    ],
];
