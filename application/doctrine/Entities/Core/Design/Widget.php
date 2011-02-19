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
     * @Column(name="dql_query", type="text")
     * @awe:AutoFormElement(
     *     label="DQL Query",
     *     type="Zend_Dojo_Form_Element_Textarea"
     * )
     */
    protected $dql_query;

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
