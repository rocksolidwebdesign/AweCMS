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
 * @package    AweCMS_Admin_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Admin_Awe_ScaffoldingController extends Awe_Controller_Admin
{
    /**
     * indexAction
     */
    public function indexAction()
    {
        $models_dir = APPLICATION_PATH . '/doctrine/Entities';

        $dirs = scandir($models_dir);

        $this->view->entities = array();
        $this->view->dirs = $dirs;

        foreach ($dirs as $d) {
            if (!in_array($d, array('.','..'))) {
                $entities = scandir($models_dir . "/$d");

                foreach ($entities as $e) {
                    if (strstr($e, '.php') !== false) {
                        $entity_name = str_replace('.php', '', $e);
                        $this->view->entities[] = "$d\\$entity_name";
                    }
                }
            }
        }
    }

    /**
     * generateAction
     */
    public function generateAction()
    {
        if ($_POST['other_entity_name']) {
            $this->entity = $_POST['other_entity_name'];
        } else if ($_POST['selected_entity']) {
            $this->entity = $_POST['selected_entity'];
        } else {
            die('No entity selected, please click the back button and try again.');
        }

        global $gANNOTATION_KEYS;
        $this->annotation_keys = $gANNOTATION_KEYS;

        $this->doctrine_em = \Zend_Registry::get('doctrine_entity_manager');
        $this->doctrine_ar = \Zend_Registry::get('doctrine_annotation_reader');

        // Get informationa about this table
        $metadata     = $this->doctrine_em->getClassMetadata($this->entity);
        $this->rclass = $metadata->getReflectionClass();

        // Get information for autgenerating form
        $properties = $this->rclass->getProperties();

        // Form field/columnn information comes from the Doctrine Docblock Annotations
        $columns = array();
        foreach ($properties as $p) {
            $columns[] = $this->doctrine_ar->getPropertyAnnotations($this->rclass->getProperty($p->name));
        }

        $data = null;
        $recurse = true;
        $scaffold = true;

        $auto_crud = new Awe_Form_AutoMagic('main_entity', $columns, $data, $recurse, $scaffold);

        $php_form = $auto_crud->getScaffolding();
        echo "<pre>"; var_dump($php_form); echo "</pre>"; exit;
        $edit_template = '<'.'?php echo \$this->form; ?'.'>';
    }
}
?>
