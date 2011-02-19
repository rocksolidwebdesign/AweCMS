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
 * @Table(name="design_layout")
 * @HasLifecycleCallbacks
 */
class Layout extends \Entities\Core\AbstractEntity
{
    // Properties
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
     * @Column(name="layout_template", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Layout Template",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $layout_template;

    /**
     * @OneToMany(targetEntity="\Entities\Core\Design\LayoutContainer", mappedBy="layout")
     */
    protected $layout_containers;

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
