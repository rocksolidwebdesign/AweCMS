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
    // properties
    protected $repopData;
    protected $parentAutoCrud;
    protected $parentEntity;
    protected $autoCrudForm;
    protected $entityColumns;
    protected $autoSubForms;
    protected $isScaffolding;
    protected $isRestful = false;
    protected $scaffolding = array();
    protected $scaffoldingForm;
    protected $scaffoldingFormDocBlock;
    protected $scaffoldingFormInitMethodBody;
    protected $recurseSubEntities;
    protected $_doctrine;
    protected $_reader;
    protected $annotationKeys = array();

    public function __construct( //
        $name      =  null,
        $columns   =  null,
        $data      =  null,
        $recurse   =  true,
        $parent    =  null
    ) {
        // initialize parent form
        parent::__construct($name);

        // setup params
        $this->isRestful           =  $name == 'rest_entity' ? true : false;
        $this->recurseSubEntities  =  $recurse;
        $this->repopData           =  $data;
        $this->parentEntity        =  $parent;
        $this->entityColumns       =  $columns;
        $this->autoSubForms        =  array();

        // setup doctrine
        $this->_doctrine = \Zend_Registry::get('doctrineEm');
        $this->_reader   = \Zend_Registry::get('doctrineAr');

        $this->buildAutoForm();
    }

    protected function buildAutoForm() { //
        global $gANNOTATION_KEYS;
        extract($gANNOTATION_KEYS);

        if ($this->recurseSubEntities) {
            $this->addSaveButton('upper_submit');
        }

        foreach ($this->entityColumns as $propertyName => $def) {
            if (!isset($def['annotations'][$annoKeyAwe])) {
                continue;
            }

            $elementType = false;
            $element = false;

            // annotation keys
            $anno = $def['annotations'];

            // determine element type
            if (isset($anno[$annoKeyId])) {
                $elementType = 'hidden_primary_key';
            }
            else if (isset($anno[$annoKeyM21])) {
                $elementType = $this->recurseSubEntities ? 'foreign_dropdown' : 'hidden_foreign_key';
            }
            else if (isset($anno[$annoKey12m]) && $this->recurseSubEntities && $anno[$annoKeyAwe]->editInline && $this->repopData && !$this->isRestful) {
                $elementType = 'foreign_editInline';
            }
            else if (isset($anno[$annoKeyM2m])) {
                $elementType = 'foreign_multi_checkbox';
            }
            else if (isset($anno[$annoKeyCol])) {
                $elementType = 'entity';
            }

            // Render that type of element
            switch ($elementType) {
                case 'hidden_primary_key': //
                    $element = new Zend_Form_Element_Hidden('id');
                    $element->setDecorators(array('ViewHelper'));
                    if ($this->repopData) {
                        $element->setValue($this->repopData->id);
                    }
                    break;

                case 'hidden_foreign_key': //
                    $elementName  = isset($anno[$annoKeyJoinColumn]->name) ? $anno[$annoKeyJoinColumn]->name : $propertyName.'_id';

                    $element = new Zend_Form_Element_Hidden($elementName);
                    $element->setDecorators(array('ViewHelper'));
                    $element->setValue($this->repopData->$propertyName->id);
                    break;

                case 'entity': //
                    // setup properties

                    // use a label param if set, 
                    // otherwise use the @Column annotation's name property
                        // replace underscores with spaces and capitalize each word
                    // otherwise default to the property name of the object
                    $label         = $anno[$annoKeyAwe]->label       ?  $anno[$annoKeyAwe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName))));
                    $type          = $anno[$annoKeyAwe]->type        ?  $anno[$annoKeyAwe]->type        :  $this->getDefaultElementType($anno[$annoKeyCol]->type);
                    $colType       = $anno[$annoKeyCol]->type;
                    $params        = isset($anno[$annoKeyAwe]->params) ? $anno[$annoKeyAwe]->params : array();
                    $validators    = count((array)$anno[$annoKeyAwe]->validators) ? (array)$anno[$annoKeyAwe]->validators : $this->getDefaultElementValidators($anno[$annoKeyCol]);

                    // build element
                    $element = new $type($propertyName, $params);
                    $element->setLabel($label);

                    $validatorList = array();
                    foreach ($validators as $v => $args) {
                        $validatorList[] = new $v((array)$args);
                    }
                    $element->setValidators($validatorList);

                    // repopulate data
                    if ($this->repopData) {
                        if ($colType == 'datetime' || $colType == 'date') {
                            $value = $this->repopData->$propertyName->format('Y-m-d');
                        } else {
                            $value = $this->repopData->$propertyName;
                        }
                        $element->setValue($value);
                    }

                    break;

                case 'foreign_dropdown': //
                    // setup properties
                    $label           =  $anno[$annoKeyAwe]->label       ?  $anno[$annoKeyAwe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName))));
                    $targetEntity    =  $anno[$annoKeyM21]->targetEntity;
                    $displayColumn  =  $anno[$annoKeyAwe]->displayColumn;
                    $join_column     =  $anno[$annoKeyJoinColumn]->name;

                    // get related entities
                    $dql = "select e from $targetEntity e";
                    $foreignEntities = $this->_doctrine->createQuery($dql)->getResult();

                    $dropdowns = array();
                    $dropdowns[''] = '';
                    foreach ($foreignEntities as $id => $f) {
                        $dropdowns[$f->id] = $f->$displayColumn;
                    }

                    // build element
                    $element = new Zend_Form_Element_Select($join_column);
                    $element->setMultiOptions($dropdowns);
                    $element->setLabel($label);

                    // repopulate data
                    if ($this->repopData && $this->repopData->$propertyName) {
                        $element->setValue($this->repopData->$propertyName->id);
                    }

                    break;

                case 'foreign_multi_checkbox': //
                    // setup properties
                    $label           = $anno[$annoKeyAwe]->label       ?  $anno[$annoKeyAwe]->label       :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName))));
                    $targetEntity    = $anno[$annoKeyM2m]->targetEntity;
                    $displayColumn   = $anno[$annoKeyAwe]->displayColumn;
                    $inverseColumn  = $anno[$annoKeyJoinTable]->inverseJoinColumns[0]->name;

                    $targetId        = str_replace('\\', '_', $targetEntity);
                    $attribute       = str_replace('_id', '', "{$inverseColumn}s");

                    // get related entities
                    $dql = "select e from $targetEntity e";
                    $foreignEntities = $this->_doctrine->createQuery($dql)->getResult();
                    $foreignColumns  = $this->getEntityColumnDefs($targetEntity);

                    $options = array();
                    if (count($foreignEntities)) {
                        foreach ($foreignEntities as $fe) {
                            $options[$fe->id] = $fe->$displayColumn;
                        }
                    }

                    // build element
                    $element = new Zend_Form_Element_MultiCheckbox("{$inverseColumn}s");
                    $element->setMultiOptions($options);
                    $element->setLabel($label);

                    // repopulate data
                    $values = array();
                    foreach ($this->repopData->$attribute as $subEntity) {
                        $values[] = $subEntity->id;
                    }
                    $element->setValue($values);

                    break;

                case 'foreign_editInline': //
                    // setup properties
                    $label          = $anno[$annoKeyAwe]->label  ?  $anno[$annoKeyAwe]->label :  ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName))));
                    $targetEntity   = $anno[$annoKey12m]->targetEntity;
                    $editInline     = $anno[$annoKeyAwe]->editInline;
                    $targetId       = str_replace('\\', '_', $targetEntity);
                    $subformName    = "{$targetId}_subform";

                    // get sub entities
                    $sub_entities        = $this->repopData->$propertyName;
                    $subEntityColumns  = $this->getEntityColumnDefs($targetEntity);

                    // build sub forms
                    $subform = new Zend_Form_SubForm();
                    $subform->setLegend($label);

                    $recurse = false;
                    $parent  = $this->repopData;
                    $x = 0; foreach ($sub_entities as $subEntity) {
                        $autoCrudForm = new Awe_Form_AutoMagic(
                            $subformName, 
                            $subEntityColumns, 
                            $subEntity, 
                            $recurse, 
                            $parent
                        );

                        $subform->addSubform($autoCrudForm, $x++);
                    }

                    $this->addSubform($subform, $targetId);

                    break;
            }

            if ($element) {
                $this->getAutoSubform('entity')->addElement($element);
            }
        }

        if ($this->recurseSubEntities) {
            $this->addSaveButton('lower_submit');
        }
    }

    protected function getDefaultElementType($colType) //
    {
        switch ($colType) {
            case 'date':
                $elementType = 'Zend_Dojo_Form_Element_DateTextBox';
                $validators  = 'Zend_Dojo_Form_Element_DateTextBox';
                break;
            case 'time':
                $elementType = 'Zend_Dojo_Form_Element_TimeTextBox';
                break;
            case 'datetime':
                $elementType = 'Zend_Dojo_Form_Element_DateTextBox';
                break;
            case 'text':
                $elementType = 'Zend_Dojo_Form_Element_Textarea';
                break;
            case 'string':
            case 'integer':
            default:
                $elementType = 'Zend_Dojo_Form_Element_TextBox';
                break;
        }
        return $elementType;
    }

    protected function getDefaultElementValidators($columnAnnotation) //
    {
        switch ($columnAnnotation->type) {
            case 'string':
                $validators = array(
                    'Zend_Validate_StringLength' => array('min' => 0, 'max' => (int)$columnAnnotation->length)
                );
                break;
            default:
                $validators = array();
                break;
        }
        return $validators;
    }

    protected function addSaveButton($name) //
    {
        $element = new Zend_Form_Element_Submit($name);
        $element->setLabel('Save');
        $this->addElement($element);
    }

    protected function getAutoSubform($name) //
    {
        if (!isset($this->autoSubForms[$name])) {
            $this->addSubform(new Zend_Form_SubForm($this), $name);
            $this->autoSubForms[$name] = $this->getSubform($name);
        }

        return $this->autoSubForms[$name];
    }

    protected function getEntityColumnDefs($entity) //
    {
        // Get information about this table
        $metadata   = $this->_doctrine->getClassMetadata($entity);

        // Get information for autgenerating form
        $properties = $metadata->getReflectionProperties();

        // Form field/columnn information comes from the Doctrine Docblock Annotations
        $columns = array();
        foreach ($properties as $name => $p) {
            $columns[$name]['property'] = $p;
            $columns[$name]['annotations'] = $this->_reader->getPropertyAnnotations($p);
        }

        return $columns;
    }
}
