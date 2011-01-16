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
 * @package    AweCMS_Resource
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Resource_Router extends Zend_Application_Resource_ResourceAbstract
{
    private $_router;

    public function init()
    {
        return $this->getRouter();
    }

    public function getRouter()
    {
        // CMS Routing
        // ****************************************************************
        // Get pages
        $em    = $this->getBootstrap()->getResource('doctrine');
        $dql   = "select p from \Entities\Cms\Page p where p.url != ''";
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

        // Blog Routing
        // ****************************************************************
        $router->addRoute("blog/archive",
            new Zend_Controller_Router_Route_Regex('blog/archive/(\d+)/(\d+)/(\d+)/(\d+)',
                array(
                    'module' => 'blog',
                    'controller' => 'entry',
                    'action'     => 'view'
                )
            )
        );
    }
}
