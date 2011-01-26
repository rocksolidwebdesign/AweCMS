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

namespace Entities\Cms;

require_once __DIR__.'/../../AbstractEntity.php';

/**
 * @Entity
 * @Table(name="cms_page")
 * @HasLifecycleCallbacks
 */
class Page extends \Entities\AbstractEntity
{
    // Properties
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Entities\Cms\Page", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     label="Parent Page", 
     *     name="parent", 
     *     display_column="title"
     * )
     */
    protected $parent;

    /**
     * @Column(name="auth_required", type="integer")
     * @awe:AutoFormElement(
     *     label="Login Required",
     *     type="Zend_Dojo_Form_Element_CheckBox"
     * )
     */
    protected $auth_required;

    /**
     * @Column(name="url", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="URL",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $url;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement(
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     label="Title",
     *     validators={
     *         "Zend_Validate_StringLength"={
     *             "min"=0,
     *             "max"=255
     *         }
     *     }
     * )
     */
    protected $title;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Page Content",
     *     type="Zend_Dojo_Form_Element_Editor",
     *     no_list="True"
     * )
     */
    protected $content;

    /** 
     * @OneToMany(targetEntity="\Entities\Cms\Page", mappedBy="parent")
     */
    protected $children;

    /**
     * @OneToMany(targetEntity="\Entities\Cms\PageWidget", mappedBy="page")
     */
    protected $widgets;

    /*
     * getUrl()
     *
     * return: string - The url of this page including its parents
     */
    public function getUrl()
    {
        $url = $this->url;

        // if the parent URL is not the home page
        if ($this->parent) {
            $parent_url = $this->parent->getUrl();
            return "$parent_url/$url";
        }

        return $url;
    }

    /*
     * getBreadcrumbs()
     *
     * param: $result array - Recursive url input chain
     * return: array - Parents and grandparents leading to the root
     */
    public function getBreadcrumbs($result = array())
    {
        $url = $this->getUrl();
        $url = $url ? $url : '/';

        // generate the breadcrumb for this page
        $crumb = array(
            'url' => $url,
            'title' => $this->title
        );

        // prepend it to the list of crumbs
        array_unshift($result, $crumb);

        // if this page has a parent
        if ($this->parent) {
            // add the parent's breadcrumb to the result
            return $this->parent->getBreadcrumbs($result);
        }

        return $result;
    }

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
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->widgets = new \Doctrine\Common\Collections\ArrayCollection();

        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
