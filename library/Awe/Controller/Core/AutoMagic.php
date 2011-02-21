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
    protected $entity;
    protected $entity_label = 'Entity';
    protected $entity_label_plural = 'Entities';
    protected $controller_name;
    protected $doctrine_columns;
    protected $em;

    public function init()
    {
        parent::init();

        global $gANNOTATION_KEYS;
        $this->annotation_keys = $gANNOTATION_KEYS;

        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');

        $this->doctrine_columns           =  $this->getEntityColumnDefs($this->entity);                  
        $this->view->controller_name      =  $this->controller_name;    
        $this->view->entity_label         =  $this->entity_label;       
        $this->view->entity_label_plural  =  $this->entity_label_plural;
    }

    public function indexAction()
    {
        // Get Records
        $dql = "select e from $this->entity e";
        $this->view->entities = $this->doctrine_em->createQuery($dql)->getResult();
        $this->view->columns = $this->doctrine_columns;

        $this->_helper->getHelper('ViewRenderer')->renderScript('crud/index.phtml');
    }

    public function deleteAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $entity = $id ? $this->doctrine_em->find($this->entity, $id) : false;
        if ($entity)
        {
            $this->doctrine_em->remove($entity);
            $this->doctrine_em->flush();
        }

        return $this->_helper->redirector->setGoToSimple();
    }

    public function getEntityColumnDefs($entity)
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
            //$columns[$name] = $this->doctrine_ar->getPropertyAnnotations($p);
        }

        return $columns;
    }

    public function saveAutoEntity(&$entity, $columns, $post, $is_sub_entity = false)
    {
        extract($this->annotation_keys);
        foreach ($columns as $property => $def) {

            $anno = $def['annotations'];
            if (isset($anno[$a_awe])) {
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

                switch ($element_type) {
                    case 'many_to_many': // {{{
                        $value = $post[$property.'_ids'];

                        // get config information
                        $target_entity   =  $anno[$a_m2m]->targetEntity;               
                        $table_name      =  $anno[$a_join_table]->name;                
                        $inverse_column  =  $anno[$a_join_table]->joinColumns[0]->name;
                        $add_method      =  $anno[$a_awe]->add_method;                 

                        // Determine what has been added and removed {{{
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
                        $value = $post[$property.'_id'];

                        if ($value) {
                            $target = $anno[$a_m21]->targetEntity;
                            $foreign_entity = $this->doctrine_em->getReference($target, $value);
                            if ($foreign_entity) {
                                $entity->$property = $foreign_entity;
                            }
                        }
                        break; // }}}
                    case 'one_to_one': // {{{
                        if ($property != 'id') {
                            $value = $post[$property];
                            if ($anno[$a_col]->type == 'datetime') {
                                $entity->$property = new DateTime($value);
                            } else {
                                $entity->$property = $value;
                            }
                        }
                        break; // }}}
                    default:
                        break;
                }
            }
        }

        $this->doctrine_em->persist($entity);
        $this->doctrine_em->flush();
    }

    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $entity = $id ? $this->doctrine_em->find($this->entity, $id) : false;

        // Build Form
        $settings = array(
            'action' => "/admin/$this->controller_name/edit/".($id ? "id/$id" : ''),
            'method' => 'post',
        );

        $form      = new Zend_Form($settings);

        // Build AutoCrud
        $auto_crud = new Awe_Form_AutoMagic('main_entity', $this->doctrine_columns, $entity);
        $form->addSubform($auto_crud, 'awe_form');

        $entity = $entity ? $entity : new $this->entity;

        extract($this->annotation_keys);

        // Form Submission and Save {{{
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $post = $form->getValues();

            $columns = $this->getEntityColumnDefs($this->entity);
            $this->saveAutoEntity($entity, $columns, $post['awe_form']['entity']);

            // Save Sub Entities {{{
            unset($post['awe_form']['entity']);
            $sub_entity_types = $post['awe_form'];
            foreach ($sub_entity_types as $sub_entity_type => $sub_entity_list) {
                $sub_entity_name = str_replace('_', '\\', $sub_entity_type);
                $sub_entity_columns = $this->getEntityColumnDefs($sub_entity_name);
                // Process each entity
                foreach ($sub_entity_list as $index => $sub_entity_form) {
                    $sub_post = $sub_entity_form['entity'];

                    // Get informationa about this table
                    $sub_entity = $this
                        ->doctrine_em
                        ->find($sub_entity_name, $sub_post['id']);

                    $this->saveAutoEntity($sub_entity, $sub_entity_columns, $sub_post, true);
                }
            }

            return $this->_helper->redirector->setGoToSimple();

            // }}}
            //// Save Main Entity {{{
            //foreach ($entity_values as $key => $value) {
            //    // Many To Many {{{
            //    if (strpos($key, '_ids') > 0) {
            //        // Get the column definition
            //        $property = str_replace('_id', '', $key);
            //        $def = $this->doctrine_ar->getPropertyAnnotations(
            //            $rclass->getProperty($property)
            //        );

            //        // if it includes a reference to the relationship
            //        if (isset($def['annotations'][$a_m2m])) {
            //            // get config information
            //            $target_entity   =  $def['annotations'][$a_m2m]->targetEntity;               
            //            $table_name      =  $def['annotations'][$a_join_table]->name;                
            //            $inverse_column  =  $def['annotations'][$a_join_table]->joinColumns[0]->name;
            //            $add_method      =  $def['annotations'][$a_awe]->add_method;                 

            //            // Determine what has been added and removed
            //            $get_ids =
            //                function ($e)
            //                {
            //                    return (string) $e->id;
            //                };

            //            $ids = $entity->$property->map($get_ids)->toArray();
            //            $remove = array_diff($ids, $value);
            //            $add    = array_diff($value, $ids);

            //            // remove records
            //            foreach ($remove as $removal_id) {
            //                $find_removed_item =
            //                    function ($e) use ($removal_id) {
            //                        return ($e->id == $removal_id);
            //                    };

            //                $item = $entity->$property->find($find_removed_item);
            //                if ($item) {
            //                    $entity->$property->removeElement($item);
            //                }
            //            }
            //            // add the new records
            //            foreach ($add as $addition_id) {
            //                $exists_added_item =
            //                    function ($x, $e) use ($addition_id) {
            //                        return ($e->id == $addition_id);
            //                    };

            //                $condition = $entity->$property->exists($exists_added_item);

            //                if (!$condition) {
            //                    $item = $this->doctrine_em->getReference($target_entity, $addition_id);
            //                    $entity->$property->add($item);
            //                }
            //            }

            //            $this->doctrine_em->flush();
            //        }
            //    }
            //    // }}}
            //    // Many To One {{{
            //    else if (strpos($key, '_id') > 0)
            //    {
            //        // Get the column definition
            //        // the input name minus the "_id" part
            //        $property = substr($key,0,strlen($key)-3);
            //        $def = $this->doctrine_ar->getPropertyAnnotations(
            //            $rclass->getProperty($property)
            //        );

            //        // If it includes a reference to the relationship
            //        if (isset($def['annotations'][$a_m21])) {
            //            // update the relationship
            //            $target = $def['annotations'][$a_m21]->targetEntity;
            //            if ($value) {
            //                $foreign_entity = $this->doctrine_em->getReference($target, $value);
            //                if ($foreign_entity) {
            //                    $entity->$property = $foreign_entity;
            //                }
            //            }
            //        }
            //    }
            //    // }}}
            //    // One To One {{{
            //    else
            //    {
            //        $def = $this->doctrine_ar->getPropertyAnnotations(
            //            $rclass->getProperty($key)
            //        );

            //        if ($def['annotations'][$a_col]->type == 'datetime') {
            //            $entity->$key = new DateTime($value);
            //        } else {
            //            $entity->$key = $value;
            //        }
            //    }
            //    // }}}
            //}

            //$this->doctrine_em->persist($entity);
            //$this->doctrine_em->flush();

            //unset($post['awe_form']['entity']);
            // }}}
            //// Save Sub Entities {{{
            //$sub_entity_types = $post['awe_form'];
            //foreach ($sub_entity_types as $sub_entity_type => $sub_entity_list) {
            //    // Process each entity
            //    foreach ($sub_entity_list as $index => $sub_entity_form) {
            //        $post_values = $sub_entity_form['entity'];
            //        $doctrine_entity = str_replace('_', '\\', $sub_entity_type);

            //        $sub_entity = $this->doctrine_em->find($doctrine_entity, $post_values['id']);
            //        $metadata   = $this->doctrine_em->getClassMetadata($doctrine_entity);
            //        $rclass = $metadata->getReflectionClass();

            //        // Process each column
            //        foreach ($post_values as $key => $value) {
            //            if (strpos($key, '_id') > 0) {
            //            // Many To One {{{
            //                // Get the column definition
            //                // the input name minus the "_id" part
            //                $property = substr($key,0,strlen($key)-3);
            //                $def = $this->doctrine_ar->getPropertyAnnotations(
            //                    $rclass->getProperty($property)
            //                );

            //                // If it includes a reference to the relationship
            //                if (isset($def['annotations'][$a_m21])) {
            //                    // update the relationship
            //                    $target = $def['annotations'][$a_m21]->targetEntity;
            //                    $target_class = ltrim($target, '\\');
            //                    $foreign_entity = $this->doctrine_em->getReference($target, $value);
            //                    if ($target_class != get_class($entity))
            //                    {
            //                        $sub_entity->$property = $foreign_entity;
            //                    }
            //                }
            //            // }}}
            //            } else {
            //            // One To One {{{
            //                if ($key != 'id') {
            //                    $def = $this->doctrine_ar->getPropertyAnnotations(
            //                        $rclass->getProperty($key)
            //                    );

            //                    if ($def['annotations'][$a_col]->type == 'datetime') {
            //                        $sub_entity->$key = new DateTime($value);
            //                    } else {
            //                        $sub_entity->$key = $value;
            //                    }
            //                }
            //            // }}}
            //            }
            //        }

            //        // Save the entity
            //        $this->doctrine_em->persist($sub_entity);
            //        $this->doctrine_em->flush();
            //    }
            //}
            //// }}}

            //return $this->_helper->redirector->setGoToSimple();
        }
        // }}}

        $this->view->id = $id;
        $this->view->form = $form;

        $this->_helper->getHelper('ViewRenderer')->renderScript('crud/edit.phtml');
    }
}
?>
