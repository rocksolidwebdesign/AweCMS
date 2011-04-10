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
 * @Table(name="design_widget_set_member")
 * @HasLifecycleCallbacks
 */
class WidgetSetMember extends \Entities\Core\AbstractEntity
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
     * @Column(name="display_order", type="string", length=255)
     * @awe:AutoFormElement(
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     label="Display Order",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $display_order;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Design\WidgetSet", inversedBy="widget_set_members")
     * @JoinColumn(name="widget_set_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="widget_set", 
     *     label="Widget Set", 
     *     displayColumn="title"
     * )
     */
    protected $widget_set;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Design\Widget", inversedBy="widget_set_members")
     * @JoinColumn(name="widget_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="widget", 
     *     label="Widget", 
     *     displayColumn="title"
     * )
     */
    protected $widget;

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
