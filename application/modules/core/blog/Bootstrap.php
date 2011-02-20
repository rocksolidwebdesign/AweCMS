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

class Blog_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initAutoload()
    {
        $loader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Blog_',
                'basePath' => dirname(__FILE__)
            )
        );

        return $loader;
    }

    public function _initRoutes()
    {
        $em      = $this->getApplication()->getPluginResource('doctrine')->getDoctrine();
        $dql     = "select e from \Entities\Core\Blog\Entry e";
        $entries = $em->createQuery($dql)->getResult();

        $router = Zend_Controller_Front::getInstance()->getRouter();

        // Add page routes
        foreach ($entries as $entry) {
            $router->addRoute($entry->getUrl(),
                new Zend_Controller_Router_Route($entry->getUrl(),
                    array(
                        'module'        => 'cms',
                        'controller'    => 'page',
                        'action'        => 'view',
                        'id'            => $entry->id,
                    )
                )
            );
        }

        // Add default home route for page root
        $router->addRoute("/blog",
            new Zend_Controller_Router_Route("/blog",
                array(
                    'module'        => 'blog',
                    'controller'    => 'entry',
                    'action'        => 'view',
                    'id'            => 1,
                )
            )
        );
    }
}
