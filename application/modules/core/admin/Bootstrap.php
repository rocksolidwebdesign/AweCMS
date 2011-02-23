<?php
/**
 * AweCMS
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   AweCMS
 * @package    AweCMS_Admin
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Admin_',
                'basePath'  => dirname(__FILE__),
            )
        );
        return $autoloader;
    }

    protected function _initRoutes()
    {
        $tables = array(
            'core_design_layout',
            'core_design_layoutcontainer',
            'core_design_widget',
            'core_design_widgetsetmember',
            'core_design_widgetset',
            'core_cms_page',
            'core_blog_entry',
            'core_blog_comment',
            'core_access_user',
            'core_access_group',
            'core_premium_plan',
            'core_premium_subscription',
        );

        $router = Zend_Controller_Front::getInstance()->getRouter();
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($tables as $name) {
            list($section, $namespace, $entity) = explode('_', $name);

            $route = "/jqgrid/$section/$namespace/$entity/list";
            $router->addRoute('jqgrid_'.$name.'_list',
                new Zend_Controller_Router_Route($route,
                    array(
                        'module'        => 'admin',
                        'controller'    => $name,
                        'action'        => 'index',
                        'format'        => 'jqgjson'
                    )
                )
            );

            switch ($method) {
                case 'DELETE':
                    $action = 'delete';
                    break;
                case 'PUT':
                    $action = 'save';
                    break;
                case 'GET':
                default:
                    $action = 'view';
                    break;
            }

            // Add default home route for page root
            $route = "/rest/$section/$namespace/$entity/:id";
            $router->addRoute('rest_'.$name.'_'.$action,
                new Zend_Controller_Router_Route($route,
                    array(
                        'module'        => 'admin',
                        'controller'    => $name,
                        'action'        => $action,
                        'format'        => 'json'
                    )
                )
            );

            switch ($method) {
                case 'PUT':
                    $action = 'save';
                    break;
                case 'GET':
                default:
                    $action = 'index';
                    break;
            }
            $route = "/rest/$section/$namespace/$entity";
            $router->addRoute('rest_'.$name.'_'.$action,
                new Zend_Controller_Router_Route($route,
                    array(
                        'module'        => 'admin',
                        'controller'    => $name,
                        'action'        => $action,
                        'format'        => 'json'
                    )
                )
            );
        }
    }
}
