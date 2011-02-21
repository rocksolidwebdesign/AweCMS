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
 * @package    AweCMS_Admin_Autocrud
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Form_AutoMagic extends Zend_Form_SubForm
{
    // properties {{{
    protected $repopulation_data;
    protected $parent_autocrud;
    protected $parent_entity;
    protected $autocrud_form;
    protected $entity_columns;
    protected $auto_subforms;
    protected $is_scaffolding;
    protected $scaffolding = array();
    protected $scaffolding_form;
    protected $scaffolding_form_docblock;
    protected $scaffolding_form_init_method_body;
    protected $recurse_subentities;
    protected $doctrine_em;
    protected $doctrine_ar;
    protected $annotation_keys = array();
    // }}}

    public function __construct( // {{{
        $name      =  null,
        $columns   =  null,
        $data      =  null,
        $recurse   =  true,
        $parent    =  null
    ) {
        // initialize parent form
        parent::__construct($name);

        // setup params
        $this->recurse_subentities  =  $recurse;
        $this->repopulation_data    =  $data;
        $this->parent_entity        =  $parent;
        $this->entity_columns       =  $columns;
        $this->auto_subforms        =  array();

        // setup doctrine
        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');

        $this->buildAutoForm();
    }
    // }}}

    protected function buildAutoForm() { // {{{
        global $gANNOTATION_KEYS;
        extract($gANNOTATION_KEYS);

        if ($this->recurse_subentities) {
            $this->addSaveButton('upper_submit');
        }

        foreach ($this->entity_columns as $property_name => $def) {
            if (!isset($def['annotations'][$a_awe])) {
                continue;
            }

            $element_type = false;
            $element = false;

            // annotation keys
            $anno = $def['annotations'];

            // determine element type {{{
            if (isset($anno[$a_id])) {
                $element_type = 'hidden_primary_key';
            }
            else if (isset($anno[$a_m21])) {
                $element_type = $this->recurse_subentities ? 'foreign_dropdown' : 'hidden_foreign_key';
            }
            else if (isset($anno[$a_12m]) && $this->recurse_subentities && $anno[$a_awe]->edit_inline && $this->repopulation_data) {
                $element_type = 'foreign_edit_inline';
            }
            else if (isset($anno[$a_m2m])) {
                $element_type = 'foreign_multi_checkbox';
            }
            else if (isset($anno[$a_col])) {
                $element_type = 'entity';
            }
            // }}}

            // Render that type of element {{{
            switch ($element_type) {
                case 'hidden_primary_key': // {{{
                    $element = new Zend_Form_Element_Hidden('id');
                    $element->setDecorators(array('ViewHelper'));
                    $element->setValue($this->repopulation_data->id);
                    break;
                    // }}}

                case 'hidden_foreign_key': // {{{
                    $element_name  = isset($anno[$a_join_column]->name) ? $anno[$a_join_column]->name : $property_name.'_id';

                    $element = new Zend_Form_Element_Hidden($element_name);
                    $element->setDecorators(array('ViewHelper'));
                    $element->setValue($this->repopulation_data->$property_name->id);
                    break;
                    // }}}

                case 'entity': // {{{
                    // setup properties {{{
                    $label         = $anno[$a_awe]->label       ?  $anno[$a_awe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', ($anno[$a_col]->name ? $anno[$a_col]->name : $property_name))));
                    $type          = $anno[$a_awe]->type        ?  $anno[$a_awe]->type        :  $this->getDefaultElementType($anno[$a_col]->type);
                    $col_type      = $anno[$a_col]->type;
                    $params        = isset($anno[$a_awe]->params) ? $anno[$a_awe]->params : array();
                    $validators    = count((array)$anno[$a_awe]->validators) ? (array)$anno[$a_awe]->validators : $this->getDefaultElementValidators($anno[$a_col]);
                    // }}}

                    // build element {{{
                    $element = new $type($property_name, $params);
                    $element->setLabel($label);

                    $validator_list = array();
                    foreach ($validators as $v => $args) {
                        $validator_list[] = new $v((array)$args);
                    }
                    $element->setValidators($validator_list);
                    // }}}

                    // repopulate data {{{
                    if ($this->repopulation_data) {
                        if ($col_type == 'datetime' || $col_type == 'date') {
                            $value = $this->repopulation_data->$property_name->format('Y-m-d');
                        } else {
                            $value = $this->repopulation_data->$property_name;
                        }
                        $element->setValue($value);
                    }
                    // }}}

                    break;
                    // }}}

                case 'foreign_dropdown': // {{{
                    // setup properties {{{
                    $label           =  $anno[$a_awe]->label       ?  $anno[$a_awe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', ($anno[$a_col]->name ? $anno[$a_col]->name : $property_name))));
                    $target_entity   =  $anno[$a_m21]->targetEntity;
                    $display_column  =  $anno[$a_awe]->display_column;
                    $join_column     =  $anno[$a_join_column]->name;
                    // }}}

                    // get related entities {{{
                    $dql = "select e from $target_entity e";
                    $foreign_entities = $this->doctrine_em->createQuery($dql)->getResult();

                    $dropdowns = array();
                    $dropdowns[''] = '';
                    foreach ($foreign_entities as $id => $f) {
                        $dropdowns[$f->id] = $f->$display_column;
                    }
                    // }}}

                    // build element {{{
                    $element = new Zend_Form_Element_Select($join_column);
                    $element->setMultiOptions($dropdowns);
                    $element->setLabel($label);
                    // }}}

                    // repopulate data {{{
                    if ($this->repopulation_data && $this->repopulation_data->$property_name) {
                        $element->setValue($this->repopulation_data->$property_name->id);
                    }
                    // }}}

                    break;
                    // }}}

                case 'foreign_multi_checkbox': // {{{
                    // setup properties {{{
                    $label           = $anno[$a_awe]->label       ?  $anno[$a_awe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', ($anno[$a_col]->name ? $anno[$a_col]->name : $property_name))));
                    $target_entity   = $anno[$a_m2m]->targetEntity;
                    $display_column  = $anno[$a_awe]->display_column;
                    $inverse_column  = $anno[$a_join_table]->inverseJoinColumns[0]->name;

                    $target_id       = str_replace('\\', '_', $target_entity);
                    $attribute       = str_replace('_id', '', "{$inverse_column}s");
                    // }}}

                    // get related entities {{{
                    $dql = "select e from $target_entity e";
                    $foreign_entities = $this->doctrine_em->createQuery($dql)->getResult();
                    $foreign_columns  = $this->getEntityColumnDefs($target_entity);

                    $options = array();
                    if (count($foreign_entities)) {
                        foreach ($foreign_entities as $fe) {
                            $options[$fe->id] = $fe->$display_column;
                        }
                    }
                    // }}}

                    // build element {{{
                    $element = new Zend_Form_Element_MultiCheckbox("{$inverse_column}s");
                    $element->setMultiOptions($options);
                    $element->setLabel($label);
                    // }}}

                    // repopulate data {{{
                    $values = array();
                    foreach ($this->repopulation_data->$attribute as $sub_entity) {
                        $values[] = $sub_entity->id;
                    }
                    $element->setValue($values);
                    // }}}

                    break;
                    // }}}

                case 'foreign_edit_inline': // {{{
                    // setup properties {{{
                    $label           = $anno[$a_awe]->label  ?  $anno[$a_awe]->label :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', ($anno[$a_col]->name ? $anno[$a_col]->name : $property_name))));
                    $target_entity   = $anno[$a_12m]->targetEntity;
                    $edit_inline     = $anno[$a_awe]->edit_inline;
                    $target_id       = str_replace('\\', '_', $target_entity);
                    $subform_name    = "{$target_id}_subform";
                    // }}}

                    // get sub entities {{{
                    $sub_entities        = $this->repopulation_data->$property_name;
                    $sub_entity_columns  = $this->getEntityColumnDefs($target_entity);
                    // }}}

                    // build sub forms {{{
                    $subform = new Zend_Form_SubForm();
                    $subform->setLegend($label);

                    $recurse = false;
                    $parent  = $this->repopulation_data;
                    $x = 0; foreach ($sub_entities as $sub_entity) {
                        $auto_crud = new Awe_Form_AutoMagic(
                            $subform_name, 
                            $sub_entity_columns, 
                            $sub_entity, 
                            $recurse, 
                            $parent
                        );

                        $subform->addSubform($auto_crud, $x++);
                    }

                    $this->addSubform($subform, $target_id);
                    // }}}

                    break;
                    // }}}
            }
            // }}}

            if ($element) {
                $this->getAutoSubform('entity')->addElement($element);
            }
        }

        if ($this->recurse_subentities) {
            $this->addSaveButton('lower_submit');
        }
    }
    // }}}

    protected function getDefaultElementType($col_type) // {{{
    {
        switch ($col_type) {
            case 'date':
                $element_type = 'Zend_Dojo_Form_Element_DateTextBox';
                $validators   = 'Zend_Dojo_Form_Element_DateTextBox';
                break;
            case 'time':
                $element_type = 'Zend_Dojo_Form_Element_TimeTextBox';
                break;
            case 'datetime':
                $element_type = 'Zend_Dojo_Form_Element_DateTextBox';
                break;
            case 'text':
                $element_type = 'Zend_Dojo_Form_Element_Textarea';
                break;
            case 'string':
            case 'integer':
            default:
                $element_type = 'Zend_Dojo_Form_Element_TextBox';
                break;
        }
        return $element_type;
    }
    // }}}

    protected function getDefaultElementValidators($col_anno) // {{{
    {
        switch ($col_anno->type) {
            case 'string':
                $validators = array(
                    'Zend_Validate_StringLength' => array('min' => 0, 'max' => (int)$col_anno->length)
                );
                break;
            default:
                $validators = array();
                break;
        }
        return $validators;
    }
    // }}}

    protected function addSaveButton($name) // {{{
    {
        $element = new Zend_Form_Element_Submit($name);
        $element->setLabel('Save');
        $this->addElement($element);
    }
    // }}}

    protected function getAutoSubform($name) // {{{
    {
        if (!isset($this->auto_subforms[$name])) {
            $this->addSubform(new Zend_Form_SubForm($this), $name);
            $this->auto_subforms[$name] = $this->getSubform($name);
        }

        return $this->auto_subforms[$name];
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
            //$columns[$name] = $this->doctrine_ar->getPropertyAnnotations($p);
        }

        return $columns;
    }
    // }}}
}
