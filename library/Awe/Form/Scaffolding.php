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
 * @package    AweCMS_Admin_AutoCrud
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Form_AutoMagic extends Zend_Form_SubForm
{
    // data to populate the form with
    protected $repopData;
    protected $parentAutoCrud;
    protected $parentEntity;
    protected $autoCrudForm;
    protected $entityColumns;
    protected $autoSubForms;
    protected $isScaffolding;
    protected $scaffolding = array();
    protected $scaffoldingForm;
    protected $scaffoldingFormDocBlock;
    protected $scaffoldingFormInitMethodBody;
    protected $recurseSubEntities;
    protected $_doctrine;
    protected $_reader;
    protected $annotationKeys = array();

    public function getAutoCrudForm()
    {
        //return $this->autoCrudForm;
        return $this;
    }

    public function getScaffolding()
    {
        // Get informationa about this table
        return $this->scaffolding;
    }

    public function __construct($name = null, $columns = null, $data = null, $recurse = true, $parent = null, $scaffold = false)
    {
        parent::__construct($name);

        global $gANNOTATION_KEYS;
        $this->annotationKeys =  $gANNOTATION_KEYS;

        if ($scaffold) {
            $this->isScaffolding  =  $scaffold;
            $this->scaffoldingFormDocBlock = new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Scaffolding Form',
                'longDescription'  => 'This is a scaffolding class generated with Awe Scaffolding Generator.',
                'tags' => array(
                    array( 'name'  => 'version', 'description' => '$Rev:$',),
                    array( 'name'  => 'license', 'description' => 'New BSD',),
                ),
            ));
            $this->scaffoldingForm = new Zend_CodeGenerator_Php_Class();
            $this->scaffoldingForm
                 ->setName('Foo')
                 ->setDocblock($this->scaffoldingFormDocBlock);
            $this->scaffoldingFormInitMethodBody = '';
        }

        // setup params
        $this->recurseSubEntities  =  $recurse;
        $this->repopData           =  $data;
        $this->parentEntity        =  $parent;
        $this->entityColumns       =  $columns;
        $this->autoSubForms        =  array();

        // setup doctrine
        $this->_doctrine = \Zend_Registry::get('doctrineEm');
        $this->_reader   = \Zend_Registry::get('doctrineAr');

        // Main Loop
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

            $this->scaffoldingForm->setMethods(array(
                // Method passed as concrete instance
                new Zend_CodeGenerator_Php_Method(array(
                    'name'       => 'init',
                    'parameters' => array(
                        array('name' => 'bar'),
                    ),
                    'body'       => $this->scaffoldingFormInitMethodBody,
                    'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Initialize and setup the form',
                    )),
                )),
            ));

            $output = $this->scaffoldingForm->generate();
            $this->scaffolding = $output;

        } else {
            if ($recurse) {
                $this->addSaveButton('upper_submit');
            }

            foreach ($columns as $name => $def) {
                $elementType = $this->parseElementType($def);
                $this->parseElement($def, $elementType);
            }

            if ($recurse) {
                $this->addSaveButton('lower_submit');
            }
        }
    }

    protected function parseScaffold($def)
    {
        // annotation keys
        extract($this->annotationKeys);

        $element = false;
        $elementType = false;

        if (isset($def['annotations'][$annoKeyAwe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def['annotations'][$annoKeyCol]) &&
              isset($def['annotations'][$annoKeyAwe]->type)
            ) {
                $elementType = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if ( isset($def['annotations'][$annoKeyId]) && !$this->recurseSubEntities) {
                $elementType = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def['annotations'][$annoKeyM21]) &&
              isset($def['annotations'][$annoKeyAwe]->name)
            ) {
                $elementType = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def['annotations'][$annoKey12m]) &&
              isset($def['annotations'][$annoKeyAwe]->editInline)
            ) {
                $elementType = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def['annotations'][$annoKeyM2m]) &&
              isset($def['annotations'][$annoKeyAwe]->label)
            ) {
                $elementType = 'foreign_select';
            }

            $this->renderScaffold($def, $elementType);
        }
    }

    protected function parseElementType($def)
    {
        // annotation keys
        extract($this->annotationKeys);

        $element = false;
        $elementType = false;

        if (isset($def['annotations'][$annoKeyAwe])) {

            // regular input (this entity has exactly one)
            if (
              isset($def['annotations'][$annoKeyCol]) &&
              isset($def['annotations'][$annoKeyAwe]->type)
            ) {
                $elementType = 'entity';
            }

            // if it's the primary key for one of these sub entities
            else if (isset($def['annotations'][$annoKeyId]) && !$this->recurseSubEntities) {
                $elementType = 'primary_key';
            }

            // dropdown of foreign values (this entity has one of many)
            else if (
              isset($def['annotations'][$annoKeyM21]) &&
              isset($def['annotations'][$annoKeyAwe]->name)
            ) {
                $elementType = 'foreign_key';
            }

            // editable list of foreign values (this entity has many)
            else if (
              isset($def['annotations'][$annoKey12m]) &&
              isset($def['annotations'][$annoKeyAwe]->editInline)
            ) {
                $elementType = 'foreign_list';
            }

            // selectable list of foreign values
            else if (
              isset($def['annotations'][$annoKeyM2m]) &&
              isset($def['annotations'][$annoKeyAwe]->label)
            ) {
                $elementType = 'foreign_select';
            }

        }

        return $elementType;
    }

    public function renderScaffold($def, $elementType)
    {
        $element = false;
        switch ($elementType) {
            case 'primary_key':
                $element = !$element ? $this->scaffoldHiddenPK() : $element;

            case 'entity':
                $element = !$element ? $this->scaffoldFormElement($def) : $element;

            case 'foreign_select':
                //$element = !$element ? $this->scaffoldForeignSelect($def) : $element;

            case 'foreign_key':
                //if (!$element) {
                //    if ($this->recurseSubEntities) {
                //        $element = $this->scaffoldForeignDropdown($def);
                //    } else {
                //        $element = $this->scaffoldHiddenFK($def);
                //    }
                //}
                //$subformName = 'entity';
                //$this->getAutoSubform($subformName)->addElement($element);
                break;

            case 'foreign_list':
                //if ($this->recurseSubEntities) {
                //    $this->scaffoldForeignList($def);
                //}
                break;

            default:
                break;
        }
    }

    public function scaffoldHiddenPK()
    {
        $initMethodBody  = '';
        //$initMethodBody .= "\$element = new $type($column, $params);"

        $initMethodBody .= "\$element = new Zend_Form_Element_Hidden('id');\n";
        $initMethodBody .= "\$element->setDecorators(array('ViewHelper'));\n";
        $initMethodBody .= "\$this->addElement(\$element);\n\n";

        $this->scaffoldingFormInitMethodBody .= $initMethodBody;
    }

    protected function scaffoldFormElement($def)
    {
        extract($this->annotationKeys);
        $validatorList = array();

        // get information about this form field and how to deal with it
        if (!($params = $def['annotations'][$annoKeyAwe]->params)) {
            $params = array();
        }

        $type       = $def['annotations'][$annoKeyAwe]->type;
        $label      = $def['annotations'][$annoKeyAwe]->label;
        $column     = $def['annotations'][$annoKeyCol]->name;
        $colType    = $def['annotations'][$annoKeyCol]->type;
        $validators = (array)$def['annotations'][$annoKeyAwe]->validators;

        $initMethodBody  = '';
        //$initMethodBody .= "\$element = new $type($column, $params);"
        $initMethodBody .= "\$element = new $type('$column');\n";
        $initMethodBody .= "\$element->setLabel('$label');\n";

        foreach ($validators as $v => $args) {
            $initMethodBody .= "\$element->addValidator(new $v());\n";
            //$validatorList[] = new $v((array)$args);
        }
        $initMethodBody .= "\$this->addElement(\$element);\n\n";

        $this->scaffoldingFormInitMethodBody .= $initMethodBody;
    }

    protected function addSaveButton($name)
    {
        $element = new Zend_Form_Element_Submit($name);
        $element->setLabel('Save');
        $this->addElement($element);
    }

    protected function getAutoSubform($name)
    {
        if (!isset($this->autoSubForms[$name])) {
            $this->getAutoCrudForm()->addSubform(new Zend_Form_SubForm($this), $name);
            $this->autoSubForms[$name] = $this->getAutoCrudForm()->getSubform($name);
        }

        return $this->autoSubForms[$name];
    }

    protected function getEntityColumnDefs($entity)
    {
        // Get informationa about this table
        $metadata     = $this->_doctrine->getClassMetadata($entity);

        // Get information for autgenerating form
        $properties = $metadata->getReflectionProperties();

        // Form field/columnn information comes from the Doctrine Docblock Annotations
        $columns = array();
        foreach ($properties as $name => $p) {
            $columns[$name]['property'] = $p;
            $columns[$name]['annotations'] = $this->_reader->getPropertyAnnotations($p);
            //$columns[$name] = $this->_reader->getPropertyAnnotations($p);
        }

        return $columns;
    }

}
