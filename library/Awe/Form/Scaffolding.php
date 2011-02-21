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
    public function __construct($name = null, $columns = null, $data = null, $recurse = true, $parent = null, $scaffold = false) // {{{
    {
        parent::__construct($name);

        global $gANNOTATION_KEYS;
        $this->annotation_keys =  $gANNOTATION_KEYS;

        if ($scaffold) { // {{{
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
        // }}}
        // setup params {{{
        $this->recurse_subentities  =  $recurse;
        $this->repopulation_data    =  $data;
        $this->parent_entity        =  $parent;
        $this->entity_columns       =  $columns;
        $this->auto_subforms        =  array();
        // }}}
        // setup doctrine {{{
        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');
        // }}}
        // Main Loop {{{
        if ($scaffold) { // {{{
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
            // }}}
        } else { // {{{
            if ($recurse) {
                $this->addSaveButton('upper_submit');
            }

            foreach ($columns as $name => $def) {
                $element_type = $this->parseElementType($def);
                $this->parseElement($def, $element_type);
            }

            if ($recurse) {
                $this->addSaveButton('lower_submit');
            }
        }
        // }}}
        // }}}
    }
    // }}}
    protected function parseScaffold($def) // {{{
    {
        // annotation keys
        extract($this->annotation_keys);

        $element = false;
        $element_type = false;

        if (isset($def['annotations'][$a_awe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def['annotations'][$a_col]) &&
              isset($def['annotations'][$a_awe]->type)
            ) {
                $element_type = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if ( isset($def['annotations'][$a_id]) && !$this->recurse_subentities) {
                $element_type = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def['annotations'][$a_m21]) &&
              isset($def['annotations'][$a_awe]->name)
            ) {
                $element_type = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def['annotations'][$a_12m]) &&
              isset($def['annotations'][$a_awe]->edit_inline)
            ) {
                $element_type = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def['annotations'][$a_m2m]) &&
              isset($def['annotations'][$a_awe]->label)
            ) {
                $element_type = 'foreign_select';
            }

            $this->renderScaffold($def, $element_type);
        }
    }
    // }}}
    protected function parseElementType($def) // {{{
    {
        // annotation keys
        extract($this->annotation_keys);

        $element = false;
        $element_type = false;

        if (isset($def['annotations'][$a_awe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def['annotations'][$a_col]) &&
              isset($def['annotations'][$a_awe]->type)
            ) {
                $element_type = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if (isset($def['annotations'][$a_id]) && !$this->recurse_subentities) {
                $element_type = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def['annotations'][$a_m21]) &&
              isset($def['annotations'][$a_awe]->name)
            ) {
                $element_type = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def['annotations'][$a_12m]) &&
              isset($def['annotations'][$a_awe]->edit_inline)
            ) {
                $element_type = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def['annotations'][$a_m2m]) &&
              isset($def['annotations'][$a_awe]->label)
            ) {
                $element_type = 'foreign_select';
            }

        }

        return $element_type;
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
        if (!($params = $def['annotations'][$a_awe]->params)) {
            $params = array();
        }

        $type       = $def['annotations'][$a_awe]->type;
        $label      = $def['annotations'][$a_awe]->label;
        $column     = $def['annotations'][$a_col]->name;
        $col_type   = $def['annotations'][$a_col]->type;
        $validators = (array)$def['annotations'][$a_awe]->validators;

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
