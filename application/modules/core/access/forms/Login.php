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
 * @package    AweCMS_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Access_Form_Login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttribs(array('id' => 'login_form', 'class' => 'login_form'));

        // Username
        $label = 'Username';
        $validators = array();
        $validator = new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING);
        $validator->setMessages(array(
            Zend_Validate_NotEmpty::IS_EMPTY  => "$label is required and must not be empty",
        ));
        $validators[] = $validator;
        $min = 4; $max = 20;
        $validator = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
        $validator->setMessages(array(
            Zend_Validate_StringLength::INVALID   => "$label contains invalid type data",
            Zend_Validate_StringLength::TOO_SHORT => "$label must be at least $min characters",
            Zend_Validate_StringLength::TOO_LONG  => "$label must be less than $max characters",
        ));
        $validators[] = $validator;
        $element = new Zend_Form_Element_Text('username', array(
            'required'   => true,
            'label'      => "$label",
            'filters'    => array(new Zend_Filter_StringTrim),
            'validators' => $validators,
        ));
        $this->addElement($element);

        // Password
        $label = 'Password';
        $validators = array();
        $validator = new Zend_Validate_NotEmpty();
        $validator->setMessages(array(
            Zend_Validate_NotEmpty::IS_EMPTY  => "$label is required and must not be empty",
        ));
        $validators[] = $validator;
        $min = 4; $max = 20;
        $validator = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
        $validator->setMessages(array(
            Zend_Validate_StringLength::INVALID   => "$label contains invalid type data",
            Zend_Validate_StringLength::TOO_SHORT => "$label must be at least $min characters",
            Zend_Validate_StringLength::TOO_LONG  => "$label must be less than $max characters",
        ));
        $validators[] = $validator;
        $element = new Zend_Form_Element_Password('password', array(
            'required'   => true,
            'label'      => "$label",
            'filters'    => array(new Zend_Filter_StringTrim),
            'validators' => $validators,
        ));
        $this->addElement($element);

        // Submit Button
        $label = 'Login';
        $element = new Zend_Form_Element_Submit('submit_button', array(
            'label' => "$label",
            'ignore' => true,
            'class' => 'proxy',
        ));
        $decorators = array(
            'ViewHelper',
            //array(array('input_wrapper' => 'HtmlTag'), array('tag' => 'div', 'id' => 'submit-wrap')),
            'Errors',
        );
        //$element->setDecorators($decorators);
        $this->addElement($element);

        // Hash
        //$element = new Zend_Form_Element_Hash('csrf_hash', array(
        //    'ignore' => true,
        //));
        //$decorators = array(
        //    'ViewHelper',
        //    array(array('input_wrapper' => 'HtmlTag'), array('tag' => 'div', 'style' => 'display: none;')),
        //);
        //$element->setDecorators($decorators);
        //$this->addElement($element);
    }
}
