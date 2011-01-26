<?php
/*
 * AweCMS
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */

namespace Entities\Premium;

require_once __DIR__.'/../../AbstractEntity.php';

/**
 * @Entity
 * @Table(name="premium_plan")
 * @HasLifecycleCallbacks
 */
class Plan extends \Entities\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Title", 
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $title;

    /**
     * @Column(name="free_trial", type="integer")
     * @awe:AutoFormElement(
     *     label="Free Trial Period (in days)",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_Int"=""}
     * )
     */
    protected $free_trial;

    /**
     * @Column(name="price", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Price",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_Float"=""}
     * )
     */
    protected $price;

    /**
     * @Column(name="duration", type="integer")
     * @awe:AutoFormElement(
     *     label="Normal Duration (in days)",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_Int"=""}
     * )
     */
    protected $duration;

    /**
     * @Column(name="auto_renew", type="integer")
     * @awe:AutoFormElement(
     *     label="Renew Automatically?",
     *     type="Zend_Dojo_Form_Element_CheckBox"
     * )
     */
    protected $auto_renew;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Description",
     *     type="Zend_Dojo_Form_Element_Editor",
     *     no_list="True"
     * )
     */
    protected $content;

    /** @Column(type="datetime") */
    private $created_at;

    /** @Column(type="datetime") */
    private $updated_at;

    /** @PreUpdate */
    public function updated()
    {
        $this->updated_at = new \DateTime("now");
    }

    public function __construct()
    {
        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
