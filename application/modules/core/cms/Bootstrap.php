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
 * @package    AweCMS_Cms
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Cms_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initAutoload()
    {
        $loader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Cms_',
                'basePath' => dirname(__FILE__)
            )
        );

        return $loader;
    }

    public function _initRoutes()
    {
        $em    = $this->getApplication()->getPluginResource('doctrine')->getDoctrine();
        $dql   = "select p from \Entities\Core\Cms\Page p where p.url != ''";
        $pages = $em->createQuery($dql)->getResult();

        $router = Zend_Controller_Front::getInstance()->getRouter();

        // Add page routes
        foreach ($pages as $page) {
            $router->addRoute($page->getUrl(),
                new Zend_Controller_Router_Route($page->getUrl(),
                    array(
                        'module'        => 'cms',
                        'controller'    => 'page',
                        'action'        => 'view',
                        'id'            => $page->id,
                    )
                )
            );
        }

        // Add default home route for page root
        $router->addRoute("/",
            new Zend_Controller_Router_Route("/",
                array(
                    'module'        => 'cms',
                    'controller'    => 'page',
                    'action'        => 'view',
                    'id'            => 1,
                )
            )
        );
    }
}
