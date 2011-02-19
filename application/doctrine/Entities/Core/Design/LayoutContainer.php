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
 * @Table(name="design_layout_container")
 * @HasLifecycleCallbacks
 */
class LayoutContainer extends \Entities\Core\AbstractEntity
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
     * @Column(name="placeholder_name", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Placeholder Name",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $placeholder_name;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Design\Layout", inversedBy="layout_containers")
     * @JoinColumn(name="layout_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="layout", 
     *     label="Layout", 
     *     display_column="title"
     * )
     */
    protected $layout;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Design\WidgetSet", inversedBy="layout_containers")
     * @JoinColumn(name="widget_set_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="widget_set", 
     *     label="Widget Set", 
     *     display_column="title"
     * )
     */
    protected $widget_set;

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
