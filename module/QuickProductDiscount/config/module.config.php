<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'install' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/install[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Install',
                        'action' => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Login',
                        'action' => 'index',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Logout',
                        'action' => 'index',
                    ),
                ),
            ),
            'webhook' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/webhook[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Webhook',
                        'action' => 'index',
                    ),
                ),
            ),
			'payment' => array(
				'type' => 'literal',
				'options' => array(
					'route' => '/payment',
					'defaults' => array(
						'controller' => 'QuickProductDiscount\Controller\Payment',
						'action' => 'index',
					),
				),
			),
			'get-started' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/get-started',
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Manual',
                        'action' => 'index',
                    ),
                ),
            ),
			'product' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/product',
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Product',
                        'action' => 'index',
                    ),
                ),
            ),
			'cron' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cron[/:action][/:id]',
					'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'QuickProductDiscount\Controller\Cron',
                        'action' => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'default' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '',
                    'defaults' => array(
                        '__NAMESPACE__' => '',
                        'controller'    => '',
                        'action'        => '',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
							
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),   
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'QuickProductDiscount\Controller\Index'  	=> 'QuickProductDiscount\Controller\IndexController',
            'QuickProductDiscount\Controller\Install' 	=> 'QuickProductDiscount\Controller\InstallController',
            'QuickProductDiscount\Controller\Login' 	=> 'QuickProductDiscount\Controller\LoginController',
            'QuickProductDiscount\Controller\Logout'	=> 'QuickProductDiscount\Controller\LogoutController',
            'QuickProductDiscount\Controller\Webhook' 	=> 'QuickProductDiscount\Controller\WebhookController',
            'QuickProductDiscount\Controller\Payment' 	=> 'QuickProductDiscount\Controller\PaymentController',
            'QuickProductDiscount\Controller\Manual' 	=> 'QuickProductDiscount\Controller\ManualController',
            'QuickProductDiscount\Controller\Product' 	=> 'QuickProductDiscount\Controller\ProductController',
            'QuickProductDiscount\Controller\Cron' 	=> 'QuickProductDiscount\Controller\CronController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/quick-product-discount/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'session' => array(
        'name'              => '_shop_qdiscount',
        'use_cookies'       => true,
        'cookie_httponly'   => true
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);