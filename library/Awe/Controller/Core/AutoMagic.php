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
    protected $entityName;
    protected $entityLabel = 'Entity';
    protected $entityLabelPlural = 'Entities';

    protected $autoForm;

    protected $doctrineColumns;
    protected $operationalEntityId;
    protected $operationalEntity;
    protected $realEntity;

    protected $hasFormat;
    protected $responseFormat;
    protected $controllerName;
    protected $recognizedFormats = array('json', 'xml', 'jqgjson', 'jqgxml', 'csv');
    // }}}

    public function init() // {{{
    {
        parent::init();

        global $gANNOTATION_KEYS;
        $this->annotation_keys = $gANNOTATION_KEYS;

        $this->_doctrine = \Zend_Registry::get('doctrineEm');
        $this->_reader   = \Zend_Registry::get('doctrineAr');

        $this->entityName               =  $this->entity;
        $this->doctrineColumns          =  $this->getEntityColumnDefs($this->entityName);

        $this->view->controllerName     =  $this->controllerName;
        $this->view->entityLabel        =  $this->entityLabel;
        $this->view->entityLabelPlural  =  $this->entityLabelPlural;

        $this->responseFormat = $this->getRequest()->getParam('format');
        $this->hasFormat = in_array($this->responseFormat, $this->recognizedFormats);

        if ($this->hasFormat) {
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

        $dql = "SELECT COUNT(e.id) FROM $this->entityName e";
        $count = $this->_doctrine->createQuery($dql)->getSingleScalarResult();

        $totalPages   =  $limit && ($count > 0) ? ceil($count / $limit) : 1;
        $page         =  $page > $totalPages ?  $totalPages :  $page;
        $orderBy      =  $sidx ?  "ORDER BY e.$sidx $sord"  :  '';
        $start        =  $page ? $limit * $page - $limit : $page;

        $dql          =  "SELECT e FROM $this->entityName e $orderBy";    
        $query        =  $this->_doctrine->createQuery($dql);             

        if ($limit) {
            $query->setMaxResults($limit);
        }
        if ($start) {
            $query->setFirstResult($start);
        }
        // }}}

        $this->view->entities = $query->getResult();
        $this->view->columns  = $this->doctrineColumns;

        $this->view->pageNumber   = $page;
        $this->view->totalRecords = $totalPages;
        $this->view->totalPages   = $totalPages;
        $this->view->recordCount  = $count;
        $this->view->startRecord  = $start + 1;
        $this->view->endRecord    = $start + $limit;

        if ($this->hasFormat) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/index.$this->responseFormat");
        } else {
            $this->_helper->getHelper('ViewRenderer')->renderScript('crud/index.phtml');
        }
    }
    // }}}

    public function viewAction() // {{{
    {
        $entity = $this->getOperationalEntity();

        if (!$entity) {
            if ($this->hasFormat) {
                $this->_helper->getHelper('ViewRenderer')->renderScript("crud/not_found.$this->responseFormat");
            } else {
                return $this->_helper->redirector->setGoToSimple();
            }
        }

        $this->view->entity = $entity;
        $this->view->columns = $this->doctrineColumns;

        if ($this->hasFormat) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/view.$this->responseFormat");
        } else {
            $this->_helper->getHelper('ViewRenderer')->renderScript('crud/view.phtml');
        }
    }
    // }}}

    public function saveAction() // {{{
    {
        if (!$this->hasFormat) {
            return $this->_helper->redirector->setGoToSimple();
        }

        $form    = $this->getAutoForm('rest_entity');
        $request = $this->getRequest();

        $post['awe_form']['entity'] = $request->getPost();

        if ($request->isPost() && $form->isValid($post)) {
            $post = $form->getValues();

            $this->saveAutoEntity($this->getRealEntity(), $this->doctrineColumns, $post['awe_form']['entity']);

            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/success.$this->responseFormat");
        }

        $this->view->entity = $this->getRealEntity();
        $this->view->form = $form;
        $this->_helper->getHelper('ViewRenderer')->renderScript("crud/invalid.$this->responseFormat");
    }
    // }}}

    public function deleteAction() // {{{
    {
        $entity = $this->getOperationalEntity();

        if ($entity) {
            $this->_doctrine->remove($entity);
            $this->_doctrine->flush();
        }

        if ($this->hasFormat) {
            $this->_helper->getHelper('ViewRenderer')->renderScript("crud/delete.$this->responseFormat");
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

            $this->saveAutoEntity($this->getRealEntity(), $this->doctrineColumns, $post['awe_form']['entity']);

            if (!$hasFormat) {
                $this->saveSubEntities($post);
            }

            return $this->_helper->redirector->setGoToSimple();
        }

        $this->view->id            = $this->getOperationalEntityId();

        $pageElements = $this->getAutoEntityDefinition();

        // loop
        $scaffold = true;
        foreach($pageElements as $elm) {
            // Render that type of element
            if ($elm['type'] == 'zendform') {
                $class  = $elm['className'];
                $name   = $elm['elementName'];
                $params = $elm['params'];
            }

            $propertyName = $elm['propertyName'];
            if ($scaffold) {
                // the form element
                $paramsArray = $this->metagenPhpArray($params);
                $formElementDeclaration = "new $class('$name', $params)";
                $repopDataStr = '';
                $formTemplateStr = '';

                switch ($elm['family']) { // {{{
                    case 'hidden_primary_key':
                        $repopDataStr = '$repop_data["id"] = $this->repopData->id;';
                        break;

                    case 'hidden_foreign_key':
                        $repopDataStr = '$repop_data["'.$name.'"] = $this->repopData->'.$propertyName.'->id;';
                        break;

                    case 'entity':
                        $col_type = $elm['data_type'];

                        if ($this->repopData) {
                            if ($col_type == 'datetime' || $col_type == 'date') {
                                $value = '$this->repopData->'.$propertyName.'->format("Y-m-d");';
                            } else {
                                $value = '$this->repopData->'.$propertyName.';';
                            }
                            $repopDataStr = '$repop_data["'.$name.'"] = ' . $value;
                        }

                        break;

                    case 'foreign_dropdown':
                        extract($elm['aweProperties']);

                        $formTemplateStr = <<<FORMTEMPLATE
\$dql = "select e from $targetEntity e";
\$foreign_entities = \$this->_doctrine->createQuery(\$dql)->getResult();

\$multi_options = array();
\$multi_options[''] = '';
foreach (\$foreign_entities as \$id => \$f) {
    \$multi_options[\$f->id] = \$f->$displayColumn;
}
\$element->setMultiOptions(\$dropdowns);
FORMTEMPLATE;

echo "<pre>"; var_dump($formTemplateStr); echo "</pre>"; exit;

$repopDataStr = <<<CONTROLLERTEMPLATE
if (\$this->repopData->$propertyName) {
    \$repop_data["$name"] = \$this->repopData->$propertyName-\>id;
}
CONTROLLERTEMPLATE;
                        break;

                    case 'foreign_multi_checkbox':
                        extract($element['aweProperties']);

                        $formTemplateStr = <<<FORMTEMPLATE
\$dql = "select e from $targetEntity e";
\$foreign_entities = \$this->_doctrine->createQuery(\$dql)->getResult();

\$multi_options = array();
if (count(\$foreign_entities)) {
    foreach (\$foreign_entities as \$fe) {
        \$multi_options[\$fe->id] = \$fe->$displayColumn;
    }
}
\$form->$name-\>setMultiOptions(\$multi_options);
FORMTEMPLATE;

$attribute = str_replace('_id', '', "{$inverseColumn}s");
$repopDataStr = <<<CONTROLLERTEMPLATE
\$values = array();
foreach (\$this->repopData->$attribute as \$sub_entity) {
    \$values[] = \$sub_entity->id;
}
\$repop_data["$name"] = \$values;
CONTROLLERTEMPLATE;

                        break;
                }
                // }}}

                $lines['form'][] = "\$element = $formElementDeclaration;";
                $lines['form'][] = "\$this->addElement(\$element);";
                if ($formTemplateStr) {
                    $lines['controller'][] = $formTemplateStr;
                }
                if ($repopDataStr) {
                    $lines['repop'][] = $repopDataStr;
                }

                // the view
            } else {
                // the form element
                $new_element = new $class($name,$params);

                switch ($elm['family']) { // {{{
                    case 'hidden_primary_key': // {{{
                        if ($this->repopData) {
                            $new_element->value = $this->repopData->id;
                        }
                        break;
                        // }}}

                    case 'hidden_foreign_key': // {{{
                        if ($this->repopData) {
                            $new_element->value = $this->repopData->$propertyName->id;
                        }
                        break;
                        // }}}

                    case 'entity': // {{{
                        $col_type = $elm['data_type'];

                        // repopulate data {{{
                        if ($this->repopData) {
                            if ($col_type == 'datetime' || $col_type == 'date') {
                                $value = $this->repopData->$propertyName->format('Y-m-d');
                            } else {
                                $value = $this->repopData->$propertyName;
                            }
                            $new_element->value = $value;
                        }
                        // }}}

                        break;
                        // }}}

                    case 'foreign_dropdown': // {{{
                        // setup properties {{{
                        extract($element['aweProperties']);

                        // get related entities {{{
                        $dql = "select e from $targetEntity e";
                        $foreign_entities = $this->_doctrine->createQuery($dql)->getResult();

                        $dropdowns = array();
                        $dropdowns[''] = '';
                        foreach ($foreign_entities as $id => $f) {
                            $dropdowns[$f->id] = $f->$displayColumn;
                        }
                        $element->setMultiOptions($dropdowns);
                        // }}}

                        // repopulate data {{{
                        if ($this->repopData && $this->repopData->$propertyName) {
                            $element['value'] = $this->repopData->$propertyName->id;
                        }
                        // }}}

                        break;
                        // }}}

                    case 'foreign_multi_checkbox': // {{{
                        extract($element['aweProperties']);

                        // get related entities {{{
                        $dql = "select e from $targetEntity e";
                        $foreign_entities = $this->_doctrine->createQuery($dql)->getResult();

                        $options = array();
                        if (count($foreign_entities)) {
                            foreach ($foreign_entities as $fe) {
                                $options[$fe->id] = $fe->$displayColumn;
                            }
                        }
                        $element->setMultiOptions($options);
                        // }}}

                        // repopulate data {{{
                        $values = array();
                        $attribute = str_replace('_id', '', "{$inverseColumn}s");
                        foreach ($this->repopData->$attribute as $sub_entity) {
                            $values[] = $sub_entity->id;
                        }
                        $element->setValue($values);
                        // }}}

                        break;
                        // }}}
                    // }}}
                }
                // }}}

                $this->form->addElement($new_element);
            }
        }
        // endloop

        $this->_helper->getHelper('ViewRenderer')->renderScript('crud/edit.phtml');
    }
    // }}}
    // }}}

    // Save Wrappers {{{
    protected function saveAutoEntity(&$entity, $columns, $post, $is_sub_entity = false) // {{{
    {
        extract($this->annotationKeys);
        foreach ($columns as $property => $def) {

            $anno = $def['annotations'];
            if (!isset($anno[$annoKeyAwe])) {
                continue;
            }

            // Parse column type {{{
            if (isset($anno[$annoKeyM2m]) && !$is_sub_entity) {
                $elementType = 'many_to_many';
            }
            else if (isset($anno[$annoKey12m])) {
                $elementType = 'one_to_many';
            }
            else if (isset($anno[$annoKeyM21])) {
                $elementType = 'many_to_one';
            }
            else if (isset($anno[$annoKeyCol])) {
                $elementType = 'one_to_one';
            }
            // }}}

            // Set values from form
            switch ($elementType) {
                case 'many_to_many': // {{{
                    if (!isset($post[$property.'_ids'])) {
                        continue;
                    }

                    $value = $post[$property.'_ids'];

                    // get config information
                    $targetEntity   =  $anno[$annoKeyM2m]->targetEntity;
                    $inverseColumn  =  $anno[$annoKeyJoinTable]->joinColumns[0]->name;

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
                    foreach ($remove as $removalId) {
                        $findRemovedItem =
                            function ($e) use ($removalId) {
                                return ($e->id == $removalId);
                            };

                        $item = $entity->$property->find($findRemovedItem);
                        if ($item) {
                            $entity->$property->removeElement($item);
                        }
                    } // }}}
                    // add the new records {{{
                    foreach ($add as $additionId) {
                        $exists_added_item =
                            function ($x, $e) use ($additionId) {
                                return ($e->id == $additionId);
                            };

                        $condition = $entity->$property->exists($exists_added_item);

                        if (!$condition) {
                            $item = $this->_doctrine->getReference($targetEntity, $additionId);
                            $entity->$property->add($item);
                        }
                    } // }}}

                    $this->_doctrine->flush();
                    break; // }}}
                case 'many_to_one': // {{{
                    if (!isset($post[$property.'_id'])) {
                        continue;
                    }

                    $value  = $post[$property.'_id'];

                    if ($value) {
                        $target = $anno[$annoKeyM21]->targetEntity;
                        $foreignEntity = $this->_doctrine->getReference($target, $value);
                        if ($foreignEntity) {
                            $entity->$property = $foreignEntity;
                        }
                    }
                    break; // }}}
                case 'one_to_one': // {{{
                    if ($property == 'id' || !isset($post[$property]) ) {
                        continue;
                    }

                    $value = $post[$property];
                    if ($anno[$annoKeyCol]->type == 'datetime') {
                        $entity->$property = new DateTime($value);
                    } else {
                        $entity->$property = $value;
                    }
                    break; // }}}
                default:
                    break;
            }
        }

        $this->_doctrine->persist($entity);
        $this->_doctrine->flush();
    }
    // }}}

    protected function saveSubentities($post) // {{{
    {
        unset($post['awe_form']['entity']);

        $types = $post['awe_form'];
        foreach ($types as $type => $entities) {

            $name    = str_replace('_', '\\', $type);
            $columns = $this->getEntityColumnDefs($name);

            foreach ($entities as $index => $formData) {
                $entity = $this->_doctrine->find($name, $formData['entity']['id']);
                $this->saveAutoEntity($entity, $columns, $formData['entity'], true);
            }
        }
    }
    // }}}
    // }}}

    // Accessors {{{
    protected function getAutoEntityDefinition($recurse=true) { // {{{
        global $gANNOTATION_KEYS;
        extract($gANNOTATION_KEYS);

        //if ($recurse) {
        //    $this->addSaveButton('upper_submit');
        //}

        $columns = $this->getEntityColumnDefs($this->entity);

        foreach ($columns as $propertyName => $def) {
            if (!isset($def['annotations'][$annoKeyAwe])) {
                continue;
            }

            $elementType = false;
            $element = false;

            // annotation keys
            $anno = $def['annotations'];

            // determine element type {{{
            if (isset($anno[$annoKeyId])) {
                $elementType = 'hidden_primary_key';
            }
            else if (isset($anno[$annoKeyM21])) {
                $elementType = $recurse ? 'foreign_dropdown' : 'hidden_foreign_key';
            }
            else if (isset($anno[$annoKey12m]) && $recurse && $anno[$annoKeyAwe]->editInline && $this->repopData && !$this->isRestful) {
                $elementType = 'foreign_edit_inline';
            }
            else if (isset($anno[$annoKeyM2m])) {
                $elementType = 'foreign_multi_checkbox';
            }
            else if (isset($anno[$annoKeyCol])) {
                $elementType = 'entity';
            }
            // }}}

            // Render that type of element {{{
            switch ($elementType) {
                case 'hidden_primary_key': // {{{
                    $element['className'] = 'Zend_Form_Element_Hidden';
                    $element['elementName'] = 'id';
                    $element['params'] = array(
                        'decorators' => array('ViewHelper')
                    );

                    break;
                    // }}}

                case 'hidden_foreign_key': // {{{
                    $element['className'] = 'Zend_Form_Element_Hidden';
                    $element['elementName'] = isset($anno[$annoKeyJoinColumn]->name) ? $anno[$annoKeyJoinColumn]->name : $propertyName.'_id';
                    $element['params'] = array(
                        'decorators' => array('ViewHelper')
                    );

                    break;
                    // }}}

                case 'entity': // {{{
                    $validators = count((array)$anno[$annoKeyAwe]->validators) ? (array)$anno[$annoKeyAwe]->validators : $this->getDefaultElementValidators($anno[$annoKeyCol]);
                    $validator_list = array();
                    foreach ($validators as $v => $args) {
                        $validator_list[] = new $v((array)$args);
                    }

                    $element['data_type']  = $anno[$annoKeyCol]->type;
                    $element['elementName']  = $propertyName;
                    $element['className']  = $anno[$annoKeyAwe]->type ? $anno[$annoKeyAwe]->type : $this->getDefaultElementType($anno[$annoKeyCol]->type);
                    $element['params'] = array_merge(
                        isset($anno[$annoKeyAwe]->params) ? $anno[$annoKeyAwe]->params : array(),
                        array(
                            'validators' => $validator_list,
                            'label'  => $anno[$annoKeyAwe]->label ? $anno[$annoKeyAwe]->label : ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName)))),
                        )
                    );

                    break;
                    // }}}

                case 'foreign_dropdown': // {{{
                    $element['aweProperties'] = array(
                        'targetEntity'  => $anno[$annoKeyM21]->targetEntity,
                        'displayColumn' => $anno[$annoKeyAwe]->displayColumn,
                    );
                    $element['elementName'] = $anno[$annoKeyJoinColumn]->name;
                    $element['className'] = 'Zend_Form_Element_Select';
                    $element['params'] = array_merge(
                        array(
                            'label'  => $anno[$annoKeyAwe]->label ? $anno[$annoKeyAwe]->label : ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName)))),
                        )
                    );

                    break;
                    // }}}

                case 'foreign_multi_checkbox': // {{{
                    $element['aweProperties'] = array(
                        'targetEntity'  => $anno[$annoKeyM2m]->targetEntity,
                        'displayColumn' => $anno[$annoKeyAwe]->displayColumn,
                        'inverseColumn' => $anno[$annoKeyJoinTable]->inverseJoinColumns[0]->name,
                    );

                    $element['elementName'] = $element['aweProperties']['inverseColumn'].'s';
                    $element['className'] = 'Zend_Form_Element_Select';
                    $element['params'] = array_merge(
                        array(
                            'label'  => $anno[$annoKeyAwe]->label ? $anno[$annoKeyAwe]->label : ucwords(str_replace('_', ' ', preg_replace('[^a-zA-Z0-9_]','', (isset($anno[$annoKeyCol]) && $anno[$annoKeyCol]->name ? $anno[$annoKeyCol]->name : $propertyName)))),
                        )
                    );

                    break;
                    // }}}
            }
            // }}}

            if ($element) {
                $element['family']       = $elementType;
                $element['type']         = 'zendform';
                $element['propertyName'] = $propertyName;
                $elements[] = $element;
            }
        }

        return $elements;
    }
    // }}}

    protected function getAutoForm($formType = 'main_entity') // {{{{
    {
        if ($this->autoForm == null) {
            $id     = $this->getOperationalEntityId();
            $action = "/admin/$this->controllerName/edit/".($id ? "id/$id" : '');
            $form   = new Zend_Form(array('action' => $action, 'method' => 'post'));

            // Build AutoCrud
            $autoForm = new Awe_Form_AutoMagic($formType, $this->doctrineColumns, $this->getOperationalEntity());
            $form->addSubform($autoForm, 'awe_form');
            $this->autoForm = $form;
        }
        return $this->autoForm;
    }
    // }}}

    protected function getOperationalEntityId() // {{{
    {
        if ($this->operationalEntityId == null) {
            $this->operationalEntityId = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : '';
        }

        return $this->operationalEntityId;
    }
    // }}}

    protected function getOperationalEntity() // {{{
    {
        if ($this->operationalEntity == null) {
            $id = $this->getOperationalEntityId();
            $this->operationalEntity = $id ? $this->_doctrine->find($this->entityName, $id) : false;
        }

        return $this->operationalEntity;
    }
    // }}}

    protected function getRealEntity() // {{{
    {
        if ($this->realEntity == null) {
            $entity = $this->getOperationalEntity();
            $this->realEntity = $entity ? $entity : new $this->entityName;
        }

        return $this->realEntity;
    }
    // }}}

    protected function getEntityColumnDefs($entity) // {{{
    {
        // Get information about this table
        $metadata     = $this->_doctrine->getClassMetadata($entity);

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
    // }}}
    // }}}

    protected function getDefaultElementType($colType) // {{{
    {
        switch ($colType) {
            case 'date':
                $elementType = 'Zend_Dojo_Form_Element_DateTextBox';
                $validators   = 'Zend_Dojo_Form_Element_DateTextBox';
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
    // }}}

    protected function getDefaultElementValidators($columnAnnotation) // {{{
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
    // }}}

    protected function metagenPhpArray($input, $indentLevel=0, $indendLast=false, $indentFirst=false, $addSemicolon=false, $indentWidth=4) { // {{{
        $output = array();

        // wrap in "array("
        $frontWrap = "array(";
        $indent = $this->generateIndent($indentLevel);
        $content_indent = $this->generateIndent($indentLevel+1);
        if ($indentFirst) {
            $frontWrap = $indent.$frontWrap;
        }
        $output[] = $frontWrap;
        foreach ($input as $key => $value) {

            // add the key along with
            if (is_string($key)) {
                $key = "'$key'";
            }
            $line = "$key => ";

            if (is_array($value)) {
                // if value is array 
                $lines = $this->metagenPhpArray($value, $indentLevel+1);
                $line = $content_indent.$line.array_shift($lines); // get the "array(" front wrapper part
                $output[] = $line;
                foreach ($lines as $l) {
                    $output[] = $l;
                }
            } else {
                // if value is string or number
                if (is_string($value)) {
                    $value = "'$value'";
                }
                $line .= $value;
                $output[] = $line;
            }

            // add indent and trailing comma
            $output[count($output)-1] = $content_indent.$output[count($output)-1];
            $output[count($output)-1] .= ',';
        }

        // end wrap
        $endWrap = ")";
        if ($indendLast) {
            $endWrap = $indent.$endWrap;
        }
        $output[] = $endWrap;

        return $output;
    }
    // }}}

    protected function generateIndent($indentLevel, $indentWidth=4) { // {{{
        $indent_size = $indentLevel*$indentWidth;
        return str_repeat(' ', $indent_size);
    }
    // }}}
}
?>
