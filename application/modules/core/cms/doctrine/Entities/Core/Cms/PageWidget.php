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

namespace Entities\Core\Cms;

/**
 * @Entity
 * @Table(name="cms_page_widget")
 * @HasLifecycleCallbacks
 */
class PageWidget extends \Entities\Core\AbstractEntity
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
     * @Column(name="code_name", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Var Name in Layout",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $code_name;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Cms\Page", inversedBy="widgets")
     * @JoinColumn(name="page_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="page", 
     *     label="Page", 
     *     display_column="title"
     * )
     */
    protected $page;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Cms\Widget", inversedBy="pages")
     * @JoinColumn(name="widget_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="widget", 
     *     label="Widget", 
     *     display_column="title"
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
