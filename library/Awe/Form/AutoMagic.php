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
    // data to populate the form with
    protected $repopulation_data;
    protected $parent_autocrud;
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

    public function getAutocrudForm() // {{{
    {
        //return $this->autocrud_form;
        return $this;
    }
    // }}}
    public function getScaffolding() // {{{
    {
        // Get informationa about this table
        return $this->scaffolding;
    }
    // }}}
    public function __construct($name = null, $columns = null, $data = null, $recurse = true, $scaffold = false) // {{{
    {
        parent::__construct($name);

        global $gANNOTATION_KEYS;
        $this->annotation_keys =  $gANNOTATION_KEYS;

        if ($scaffold)
        {
            $this->is_scaffolding  =  $scaffold;        
            $this->scaffolding_form_docblock = new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Scaffolding Form',
                'longDescription'  => 'This is a scaffolding class generated with Awe Scaffolding Generator.',
                'tags' => array(
                    array( 'name'  => 'version', 'description' => '$Rev:$',),
                    array( 'name'  => 'license', 'description' => 'New BSD',),
                ),
            ));
            $this->scaffolding_form = new Zend_CodeGenerator_Php_Class();
            $this->scaffolding_form
                 ->setName('Foo')
                 ->setDocblock($this->scaffolding_form_docblock);
            $this->scaffolding_form_init_method_body = '';
        }

        $this->recurse_subentities  =  $recurse;         
        $this->repopulation_data    =  $data;            
        $this->entity_columns       =  $columns;         
        $this->auto_subforms        =  array();          

        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');

        if ($scaffold) {
            if ($recurse) {
                $this->addSaveButton('upper_submit');
            }

            foreach ($columns as $def) {
                $this->parseScaffold($def);
            }

            if ($recurse) {
                $this->addSaveButton('lower_submit');
            }

            $this->scaffolding_form->setMethods(array(
                // Method passed as concrete instance
                new Zend_CodeGenerator_Php_Method(array(
                    'name'       => 'init',
                    'parameters' => array(
                        array('name' => 'bar'),
                    ),
                    'body'       => $this->scaffolding_form_init_method_body,
                    'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Initialize and setup the form',
                    )),
                )),
            ));

            $output = $this->scaffolding_form->generate();
            $this->scaffolding = $output;
        } else {
            if ($recurse) {
                $this->addSaveButton('upper_submit');
            }

            foreach ($columns as $def) {
                $this->parseElement($def);
            }

            if ($recurse) {
                $this->addSaveButton('lower_submit');
            }
        }
    }
    // }}}
    protected function parseScaffold($def) // {{{
    {
        // annotation keys
        extract($this->annotation_keys);

        $element = false;
        $element_type = false;

        if (isset($def[$a_awe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def[$a_col]) &&
              isset($def[$a_awe]->type)
            ) {
                $element_type = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if ( isset($def[$a_id]) && !$this->recurse_subentities) {
                $element_type = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def[$a_m21]) &&
              isset($def[$a_awe]->name)
            ) {
                $element_type = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def[$a_12m]) &&
              isset($def[$a_awe]->edit_inline)
            ) {
                $element_type = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def[$a_m2m]) &&
              isset($def[$a_awe]->label)
            ) {
                $element_type = 'foreign_select';
            }

            $this->renderScaffold($def, $element_type);
        }
    }
    // }}}
    protected function parseElement($def) // {{{
    {
        // annotation keys
        extract($this->annotation_keys);

        $element = false;
        $element_type = false;

        if (isset($def[$a_awe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def[$a_col]) &&
              isset($def[$a_awe]->type)
            ) {
                $element_type = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if ( isset($def[$a_id]) && !$this->recurse_subentities) {
                $element_type = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def[$a_m21]) &&
              isset($def[$a_awe]->name)
            ) {
                $element_type = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def[$a_12m]) &&
              isset($def[$a_awe]->edit_inline)
            ) {
                $element_type = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def[$a_m2m]) &&
              isset($def[$a_awe]->label)
            ) {
                $element_type = 'foreign_select';
            }

            $this->renderElement($def, $element_type);
        }
    }
    // }}}
    public function renderScaffold($def, $element_type) // {{{
    {
        $element = false;
        switch ($element_type) {
            case 'primary_key':
                $element = !$element ? $this->scaffoldHiddenPK() : $element;

            case 'entity':
                $element = !$element ? $this->scaffoldFormElement($def) : $element;

            case 'foreign_select':
                //$element = !$element ? $this->scaffoldForeignSelect($def) : $element;

            case 'foreign_key':
                //if (!$element) {
                //    if ($this->recurse_subentities) {
                //        $element = $this->scaffoldForeignDropdown($def);
                //    } else {
                //        $element = $this->scaffoldHiddenFK($def);
                //    }
                //}
                //$subform_name = 'entity';
                //$this->getAutoSubform($subform_name)->addElement($element);
                break;

            case 'foreign_list':
                //if ($this->recurse_subentities) {
                //    $this->scaffoldForeignList($def);
                //}
                break;

            default:
                break;
        }
    }
    // }}}
    public function renderElement($def, $element_type) // {{{
    {
        $element = false;
        switch ($element_type) {
            case 'primary_key':
                $element = !$element ? $this->buildHiddenPK() : $element;

            case 'entity':
                $element = !$element ? $this->buildFormElement($def) : $element;

            case 'foreign_select':
                $element = !$element ? $this->buildForeignSelect($def) : $element;

            case 'foreign_key':
                if (!$element) {
                    if ($this->recurse_subentities) {
                        $element = $this->buildForeignDropdown($def);
                    } else {
                        $element = $this->buildHiddenFK($def);
                    }
                }
                $subform_name = 'entity';
                $this->getAutoSubform($subform_name)->addElement($element);
                break;

            case 'foreign_list':
                if ($this->recurse_subentities) {
                    $this->buildForeignList($def);
                }
                break;

            default:
                break;
        }
    }
    // }}}
    public function scaffoldHiddenPK() // {{{
    {
        $init_method_body  = '';
        //$init_method_body .= "\$element = new $type($column, $params);"

        $init_method_body .= "\$element = new Zend_Form_Element_Hidden('id');\n";
        $init_method_body .= "\$element->setDecorators(array('ViewHelper'));\n";
        $init_method_body .= "\$this->addElement(\$element);\n\n";

        $this->scaffolding_form_init_method_body .= $init_method_body;
    }
    // }}}
    protected function scaffoldFormElement($def) // {{{
    {
        extract($this->annotation_keys);
        $validator_list = array();

        // get information about this form field and how to deal with it
        if (!($params = $def[$a_awe]->params)) {
            $params = array();
        }

        $type       = $def[$a_awe]->type;
        $label      = $def[$a_awe]->label;
        $column     = $def[$a_col]->name;
        $col_type   = $def[$a_col]->type;
        $validators = (array)$def[$a_awe]->validators;

        $init_method_body  = '';
        //$init_method_body .= "\$element = new $type($column, $params);"
        $init_method_body .= "\$element = new $type('$column');\n";
        $init_method_body .= "\$element->setLabel('$label');\n";

        foreach ($validators as $v => $args) {
            $init_method_body .= "\$element->addValidator(new $v());\n";
            //$validator_list[] = new $v((array)$args);
        }
        $init_method_body .= "\$this->addElement(\$element);\n\n";

        $this->scaffolding_form_init_method_body .= $init_method_body;
    }
    // }}}
    public function buildHiddenPK() // {{{
    {
        $element = new Zend_Form_Element_Hidden('id');
        $element->setDecorators(array('ViewHelper'));
        $element->setValue($this->repopulation_data->id);

        return $element;
    }
    // }}}
    public function buildHiddenFK($def) // {{{
    {
        extract($this->annotation_keys);

        $join_column     =  $def[$a_join_column]->name;

        $element = new Zend_Form_Element_Hidden($join_column);
        $element->setDecorators(array('ViewHelper'));
        $element->setValue($this->repopulation_data->id);

        return $element;
    }
    // }}}
    protected function buildFormElement($def) // {{{
    {
        extract($this->annotation_keys);
        $validator_list = array();

        // get information about this form field and how to deal with it
        if (!($params = $def[$a_awe]->params)) {
            $params = array();
        }

        $type       = $def[$a_awe]->type;
        $label      = $def[$a_awe]->label;
        $column     = $def[$a_col]->name;
        $col_type   = $def[$a_col]->type;
        $validators = (array)$def[$a_awe]->validators;

        // create the Zend Form Element
        $element = new $type($column, $params);
        $element->setLabel($label);

        // setup validators
        foreach ($validators as $v => $args) {
            $validator_list[] = new $v((array)$args);
        }
        $element->setValidators($validator_list);

        // Set data
        if ($this->repopulation_data) {
            if ($col_type == 'datetime') {
                $value = $this->repopulation_data->$column->format('Y-m-d');
            } else {
                $value = $this->repopulation_data->$column;
            }
            $element->setValue($value);
        }

        return $element;
    }
    // }}}
    protected function buildForeignDropdown($def) // {{{
    {
        extract($this->annotation_keys);
        $dropdowns = array();

        // get information about this form field and how to deal with it
        $target_entity   =  $def[$a_m21]->targetEntity;
        $join_column     =  $def[$a_join_column]->name;
        $name            =  $def[$a_awe]->name;
        $display_column  =  $def[$a_awe]->display_column;
        $label           =  $def[$a_awe]->label;

        // get dropdown options
        $dql              = "select e from $target_entity e";
        $foreign_entities = $this->doctrine_em->createQuery($dql)->getResult();
        $dropdowns[''] = '';
        foreach ($foreign_entities as $id => $f) {
            $dropdowns[$f->id] = $f->$display_column;
        }

        // create and add to form
        $element = new Zend_Form_Element_Select($join_column);
        $element->setMultiOptions($dropdowns);
        $element->setLabel($label);

        if ($this->repopulation_data && $this->repopulation_data->$name) { 
            $element->setValue($this->repopulation_data->$name->id);
        }

        return $element;
    }
    // }}}
    protected function buildForeignSelect($def) // {{{
    {
        extract($this->annotation_keys);

        $target_entity   = $def[$a_m2m]->targetEntity;
        $label           = $def[$a_awe]->label;
        $display_column  = $def[$a_awe]->display_column;
        $inverse_column  = $def[$a_join_table]->inverseJoinColumns[0]->name;
        $target_id       = str_replace('\\', '_', $target_entity);

        $dql = "select e from $target_entity e";
        $foreign_entities = $this->doctrine_em->createQuery($dql)->getResult();
        $foreign_columns  = $this->getEntityColumnDefs($target_entity);

        $options = array();
        if (count($foreign_entities)) {
            foreach ($foreign_entities as $fe) {
                $options[$fe->id] = $fe->$display_column;
            }
        }

        $attribute = str_replace('_id', '', "{$inverse_column}s");
        $element = new Zend_Form_Element_MultiCheckbox("{$inverse_column}s");
        $element->setMultiOptions($options);
        $element->setLabel($label);

        $values = array();
        foreach ($this->repopulation_data->$attribute as $sub_entity) {
            $values[] = $sub_entity->id;
        }
        $element->setValue($values);

        return $element;
    }
    // }}}
    protected function buildForeignList($def) // {{{
    {
        extract($this->annotation_keys);

        $target_entity   = $def[$a_12m]->targetEntity;
        $label           = $def[$a_awe]->label;
        $edit_inline     = $def[$a_awe]->edit_inline;
        $compact_view    = $def[$a_awe]->compact_view;
        $list_name       = $def[$a_awe]->name;

        $target_id       = str_replace('\\', '_', $target_entity);

        if ($edit_inline && $this->repopulation_data) {
            $foreign_entities = $this->repopulation_data->$list_name;
            $foreign_columns  = $this->getEntityColumnDefs($target_entity);

            $subform = new Zend_Form_SubForm();
            $subform->setLegend($label);

            $x = 0; foreach ($foreign_entities as $foreign_entity) {
                $auto_crud = new Awe_Form_AutoMagic(
                    "{$target_id}_subform", $foreign_columns, $foreign_entity, false);

                $subform->addSubform($auto_crud, $x);

                $x++;
            }

            $this->getAutocrudForm()->addSubform($subform, $target_id);
        }
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
            $this->getAutocrudForm()->addSubform(new Zend_Form_SubForm($this), $name);
            $this->auto_subforms[$name] = $this->getAutocrudForm()->getSubform($name);
        }

        return $this->auto_subforms[$name];
    }
    // }}}
    protected function getEntityColumnDefs($entity) // {{{
    {
        $columns = array();

        // Get informationa about this table
        $rclass = $this->doctrine_em->getClassMetadata($entity)->getReflectionClass();

        // Form field/columnn information comes from the Doctrine Docblock Annotations
        $properties = $rclass->getProperties();
        foreach ($properties as $p) {
            $columns[] = $this->doctrine_ar->getPropertyAnnotations($rclass->getProperty($p->name));
        }

        return $columns;
    }
    // }}}
    protected function getEntityColumnDef($entity, $column) // {{{
    {
        // Get informationa about this table
        $property = $this->doctrine_em
                ->getClassMetadata($entity)
                ->getReflectionClass()
                ->getProperty($column);

        return $this->doctrine_ar->getPropertyAnnotations($property);
    }
    // }}}
}
