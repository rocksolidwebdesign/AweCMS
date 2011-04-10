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
 * @category   Awe
 * @package    AweCMS
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Controller_Frontend_Widget_Layout extends Awe_Controller_Frontend
{
    protected $_doctrine;

    public function renderLayoutWidgets($containers)
    {
        foreach ($containers as $container) {

            $results = array();
            foreach ($container->widget_set->widget_set_members as $member) {
                $widgetType = $member->widget->data_source_type;

                $entities = '';
                if ($widgetType) {
                    ucwords(strtolower($widgetType));
                    $method = "widgetDataSource$widgetType";
                    $entities = method_exists($this, $method) ? $this->$method($member->widget) : '';
                }

                $templateFile        =    $member->widget->template_file;                                                   
                $templateVarName     =    $member->widget->template_var_name ? $member->widget->template_var_name : 'entities';
                $vars                =    array($templateVarName => $entities);                                            

                $results[$member->display_order] = $this->renderDynamicTemplate($templateFile,$vars);
            }

            foreach ($results as $result) {
                $this->view
                    ->placeholder($container->placeholder_name)
                    ->append($result);
            }
        }
    }

    public function widgetDataSourceProperty($widget)
    {
        $property = $widget->data_source_property;
        return $this->getCurrentEntity()->$property;
    }

    public function widgetDataSourceMethod($widget)
    {
        $method = $widget->data_source_method;
        return $this->getCurrentEntity()->$method();
    }

    public function widgetDataSourceCode($widget)
    {
        $key = 'awe_controller_frontend_widget_layout_widget_data_source_code';
        $GLOBALS[$key] = $widget->data_source_code;
        return include "var://$key";
    }

    public function widgetDataSourceFile($widget)
    {
        if (file_exists($widget->data_source_phpfile)) {
            return include($widget->data_source_phpfile);
        }
        return '';
    }

    public function getDoctrineEm()
    {
        if (!$this->_doctrine) {
            $this->_doctrine = $this->getInvokeArg('bootstrap')
                ->getResource('doctrine');
        }

        return $this->_doctrine;
    }

    public function widgetDataSourceDql($widget)
    {
        $dql  =  $widget->data_source_dql;

        if (!$dql) {
            $entityName    =  $widget->dql_entity_name;
            $orderBy       =  $widget->dql_order_by;

            $dql = "SELECT e FROM $entityName e";
            if ($orderBy) {
                $dql .= " ORDER BY e.$orderBy";
            }
        }

        $maxResults = $widget->dql_max_results ? $widget->dql_max_results : 1;

        return $this->getDoctrineEm()
            ->createQuery($dql)
            ->setMaxResults($maxResults)
            ->getResult();
    }
}
