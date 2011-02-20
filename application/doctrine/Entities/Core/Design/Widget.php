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

namespace Entities\Core\Design;

/**
 * @Entity
 * @Table(name="design_widget")
 * @HasLifecycleCallbacks
 */
class Widget extends \Entities\Core\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(
     *     label="Id"
     * )
     */
    protected $id;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement(
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     label="Title",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $title;

    /**
     * @Column(name="template_file", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="PHTML Template File",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $template_file;

    /**
     * @Column(name="data_source_type", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Data Source Type",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $data_source_type;

    /**
     * @Column(name="data_source_dql", type="text")
     * @awe:AutoFormElement(
     *     label="Data Source DQL",
     *     no_list="True",
     *     type="Zend_Dojo_Form_Element_Textarea"
     * )
     */
    protected $data_source_dql;

    /**
     * @Column(name="dql_max_results", type="integer")
     * @awe:AutoFormElement(
     *     label="DQL Limit",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     no_list="True",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $dql_max_results;

    /**
     * @Column(name="dql_entity_name", type="string", length=255)
     * @awe:AutoFormElement(
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     label="DQL Entity",
     *     no_list="True",
     *     validators={"Zend_Validate_StringLength"={ "min"=0, "max"=255 } }
     * )
     */
    protected $dql_entity_name;

    /**
     * @Column(name="dql_order_by", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="DQL Order By",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     no_list="True",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $dql_order_by;

    /**
     * @Column(name="data_source_code", type="text")
     * @awe:AutoFormElement(
     *     label="Data Source: Code",
     *     no_list="True",
     *     type="Zend_Dojo_Form_Element_Textarea"
     * )
     */
    protected $data_source_code;

    /**
     * @Column(name="data_source_method", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Data Source: Method",
     *     no_list="True",
     *     type="Zend_Dojo_Form_Element_TextBox"
     * )
     */
    protected $data_source_method;

    /**
     * @Column(name="data_source_property", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Data Source: Property",
     *     no_list="True",
     *     type="Zend_Dojo_Form_Element_TextBox"
     * )
     */
    protected $data_source_property;

    /**
     * @Column(name="data_source_phpfile", type="string", length=2000)
     * @awe:AutoFormElement(
     *     label="Data Source: Include File",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     no_list="True",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=2000}}
     * )
     */
    protected $data_source_phpfile;

    /**
     * @Column(name="template_var_name", type="string", length="255")
     * @awe:AutoFormElement(
     *     label="Data Source: Var Name",
     *     no_list="True",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $template_var_name;

    /**
     * @OneToMany(targetEntity="\Entities\Core\Design\WidgetSetMember", mappedBy="widget")
     */
    protected $widget_set_members;

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
