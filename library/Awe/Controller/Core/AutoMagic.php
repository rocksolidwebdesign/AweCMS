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
 * @package    AweCMS_Core
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Controller_Core_AutoMagic extends Awe_Controller_Core_Protected
{
    // Property Declarations {{{
    protected $entity;
    protected $entity_name;
    protected $entity_label = 'Entity';
    protected $entity_label_plural = 'Entities';

    protected $auto_form;

    protected $doctrine_columns;
    protected $operational_entity_id;
    protected $operational_entity;
    protected $real_entity;

    protected $has_format;
    protected $response_format;
    protected $controller_name;
    protected $recognized_formats = array('json', 'xml', 'jqgjson', 'jqgxml', 'csv');
    // }}}

    public function init() // {{{
    {
        parent::init();

        global $gANNOTATION_KEYS;
        $this->annotation_keys = $gANNOTATION_KEYS;

        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');

        $this->entity_name                =  $this->entity;
        $this->doctrine_columns           =  $this->getEntityColumnDefs($this->entity_name);

        $this->view->controller_name      =  $this->controller_name;
        $this->view->entity_label         =  $this->entity_label;
        $this->view->entity_label_plural  =  $this->entity_label_plural;

        $this->response_format = $this->getRequest()->getParam('format');
        $this->has_format = in_array($this->response_format, $this->recognized_formats);

        if ($this->has_format) {
            $this->_helper->layout()->setLayout('layout_rest');
        }
    }
    // }}}

    // RESTful actions {{{
    public function indexAction() // {{{
    {
        // get records with pagination {{{
        $page  = isset($_GET['page']) ? $_GET['page'] : 1;
        $limit = isset($_GET['rows']) ? $_GET['rows'] : 10;
        $sidx  = isset($_GET['sidx']) ? $_GET['sidx'] : '';
        $sord  = isset($_GET['sord']) ? $_GET['sord'] : '';

        $dql = "SELECT COUNT(e.id) FROM $this->entity_name e";
        $count = $this->doctrine_em->createQuery($dql)->getSingleScalarResult();

        $total_pages  =  $limit && ($count > 0) ? ceil($count / $limit) : 1;
        $page         =  $page > $total_pages ?  $total_pages :  $page;
        $order_by     =  $sidx ?  "ORDER BY e.$sidx $sord"  :  '';
        $start        =  $page ? $limit * $page - $limit : $page;

        $dql          =  "SELECT e FROM $this->entity_name e $order_by";    
        $query        =  $this->doctrine_em->createQuery($dql);             

        if ($limit) {
            $query->setMaxResults($limit);
        }
        if ($start) {
            $query->setFirstResult($start);
        }
        // }}}

        $this->view->entities = $query->getResult();
        $this->view->columns  = $this->doctrine_columns;

        $this->view->page_number   = $page;
        $this->view->total_records = $total_pages;
        $this->view->total_pages   = $total_pages;
        $this->view->record_count  = $count;
        $this->view->start_record  = $start + 1;
        $this->view->end_record    = $start + $limit;

        if ($this->has_format) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/index.$this->response_format");
        } else {
            $this->_helper->getHelper('ViewRenderer')->renderScript('crud/index.phtml');
        }
    }
    // }}}

    public function viewAction() // {{{
    {
        $entity = $this->getOperationalEntity();

        if (!$entity) {
            if ($this->has_format) {
                $this->_helper->getHelper('ViewRenderer')->renderScript("crud/not_found.$this->response_format");
            } else {
                return $this->_helper->redirector->setGoToSimple();
            }
        }

        $this->view->entity = $entity;
        $this->view->columns = $this->doctrine_columns;

        if ($this->has_format) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/view.$this->response_format");
        } else {
            $this->_helper->getHelper('ViewRenderer')->renderScript('crud/view.phtml');
        }
    }
    // }}}

    public function saveAction() // {{{
    {
        if (!$this->has_format) {
            return $this->_helper->redirector->setGoToSimple();
        }

        $form    = $this->getAutoForm('rest_entity');
        $request = $this->getRequest();

        $post['awe_form']['entity'] = $request->getPost();

        if ($request->isPost() && $form->isValid($post)) {
            $post = $form->getValues();

            $this->saveAutoEntity($this->getRealEntity(), $this->doctrine_columns, $post['awe_form']['entity']);

            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/success.$this->response_format");
        }

        $this->view->entity = $this->getRealEntity();
        $this->view->form = $form;
        $this->_helper->getHelper('ViewRenderer')->renderScript("crud/invalid.$this->response_format");
    }
    // }}}

    public function deleteAction() // {{{
    {
        $entity = $this->getOperationalEntity();

        if ($entity) {
            $this->doctrine_em->remove($entity);
            $this->doctrine_em->flush();
        }

        if ($this->has_format) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/delete.$this->response_format");
        } else {
            return $this->_helper->redirector->setGoToSimple();
        }
    }
    // }}}

    public function editAction() // {{{
    {
        $form    = $this->getAutoForm();
        $request = $this->getRequest();

        $post = $request->getPost();
        if ($request->isPost() && $form->isValid($post)) {
            $post = $form->getValues();

            $this->saveAutoEntity($this->getRealEntity(), $this->doctrine_columns, $post['awe_form']['entity']);

            if (!$has_format) {
                $this->saveSubEntities($post);
            }

            return $this->_helper->redirector->setGoToSimple();
        }

        $this->view->id   = $this->getOperationalEntityId();
        $this->view->form = $this->getAutoForm();

        $this->_helper->getHelper('ViewRenderer')->renderScript('crud/edit.phtml');
    }
    // }}}
    // }}}

    // Save Wrappers {{{
    protected function saveAutoEntity(&$entity, $columns, $post, $is_sub_entity = false) // {{{
    {
        extract($this->annotation_keys);
        foreach ($columns as $property => $def) {

            $anno = $def['annotations'];
            if (!isset($anno[$a_awe])) {
                continue;
            }

            // Parse column type {{{
            if (isset($anno[$a_m2m]) && !$is_sub_entity) {
                $element_type = 'many_to_many';
            }
            else if (isset($anno[$a_12m])) {
                $element_type = 'one_to_many';
            }
            else if (isset($anno[$a_m21])) {
                $element_type = 'many_to_one';
            }
            else if (isset($anno[$a_col])) {
                $element_type = 'one_to_one';
            }
            // }}}

            // Set values from form
            switch ($element_type) {
                case 'many_to_many': // {{{
                    if (!isset($post[$property.'_ids'])) {
                        continue;
                    }

                    $value = $post[$property.'_ids'];

                    // get config information
                    $target_entity   =  $anno[$a_m2m]->targetEntity;
                    $inverse_column  =  $anno[$a_join_table]->joinColumns[0]->name;

                    // get diff {{{
                    $get_ids =
                        function ($e)
                        {
                            return (string) $e->id;
                        };

                    $ids    = $entity->$property->map($get_ids)->toArray();
                    $remove = array_diff($ids, $value);
                    $add    = array_diff($value, $ids);
                    // }}}
                    // remove records {{{
                    foreach ($remove as $removal_id) {
                        $find_removed_item =
                            function ($e) use ($removal_id) {
                                return ($e->id == $removal_id);
                            };

                        $item = $entity->$property->find($find_removed_item);
                        if ($item) {
                            $entity->$property->removeElement($item);
                        }
                    } // }}}
                    // add the new records {{{
                    foreach ($add as $addition_id) {
                        $exists_added_item =
                            function ($x, $e) use ($addition_id) {
                                return ($e->id == $addition_id);
                            };

                        $condition = $entity->$property->exists($exists_added_item);

                        if (!$condition) {
                            $item = $this->doctrine_em->getReference($target_entity, $addition_id);
                            $entity->$property->add($item);
                        }
                    } // }}}

                    $this->doctrine_em->flush();
                    break; // }}}
                case 'many_to_one': // {{{
                    if (!isset($post[$property.'_id'])) {
                        continue;
                    }

                    $value  = $post[$property.'_id'];

                    if ($value) {
                        $target = $anno[$a_m21]->targetEntity;
                        $foreign_entity = $this->doctrine_em->getReference($target, $value);
                        if ($foreign_entity) {
                            $entity->$property = $foreign_entity;
                        }
                    }
                    break; // }}}
                case 'one_to_one': // {{{
                    if ($property == 'id' || !isset($post[$property]) ) {
                        continue;
                    }

                    $value = $post[$property];
                    if ($anno[$a_col]->type == 'datetime') {
                        $entity->$property = new DateTime($value);
                    } else {
                        $entity->$property = $value;
                    }
                    break; // }}}
                default:
                    break;
            }
        }

        $this->doctrine_em->persist($entity);
        $this->doctrine_em->flush();
    }
    // }}}

    protected function saveSubentities($post) // {{{
    {
        unset($post['awe_form']['entity']);

        $types = $post['awe_form'];
        foreach ($types as $type => $entities) {

            $name    = str_replace('_', '\\', $type);
            $columns = $this->getEntityColumnDefs($name);

            foreach ($entities as $index => $form_data) {
                $entity = $this->doctrine_em->find($name, $form_data['entity']['id']);
                $this->saveAutoEntity($entity, $columns, $form_data['entity'], true);
            }
        }
    }
    // }}}
    // }}}

    // Accessors {{{
    protected function getAutoForm($form_type = 'main_entity') // {{{{
    {
        if ($this->auto_form == null) {
            $id     = $this->getOperationalEntityId();
            $action = "/admin/$this->controller_name/edit/".($id ? "id/$id" : '');
            $form   = new Zend_Form(array('action' => $action, 'method' => 'post'));

            // Build AutoCrud
            $auto_form = new Awe_Form_AutoMagic($form_type, $this->doctrine_columns, $this->getOperationalEntity());
            $form->addSubform($auto_form, 'awe_form');
            $this->auto_form = $form;
        }
        return $this->auto_form;
    }
    // }}}

    protected function getOperationalEntityId() // {{{
    {
        if ($this->operational_entity_id == null) {
            $this->operational_entity_id = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : '';
        }

        return $this->operational_entity_id;
    }
    // }}}

    protected function getOperationalEntity() // {{{
    {
        if ($this->operational_entity == null) {
            $id = $this->getOperationalEntityId();
            $this->operational_entity = $id ? $this->doctrine_em->find($this->entity_name, $id) : false;
        }

        return $this->operational_entity;
    }
    // }}}

    protected function getRealEntity() // {{{
    {
        if ($this->real_entity == null) {
            $entity = $this->getOperationalEntity();
            $this->real_entity = $entity ? $entity : new $this->entity_name;
        }

        return $this->real_entity;
    }
    // }}}

    protected function getEntityColumnDefs($entity) // {{{
    {
        // Get informationa about this table
        $metadata     = $this->doctrine_em->getClassMetadata($entity);

        // Get information for autgenerating form
        $properties = $metadata->getReflectionProperties();

        // Form field/columnn information comes from the Doctrine Docblock Annotations
        $columns = array();
        foreach ($properties as $name => $p) {
            $columns[$name]['property'] = $p;
            $columns[$name]['annotations'] = $this->doctrine_ar->getPropertyAnnotations($p);
        }

        return $columns;
    }
    // }}}
    // }}}
}
?>
