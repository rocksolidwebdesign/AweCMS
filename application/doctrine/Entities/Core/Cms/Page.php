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
 * @Table(name="cms_page")
 * @HasLifecycleCallbacks
 */
class Page extends \Entities\Core\AbstractEntity
{
    // Properties
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement()
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Cms\Page", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     label="Parent Page", 
     *     displayColumn="title"
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
     * @ManyToOne(targetEntity="\Entities\Core\Design\Layout")
     * @JoinColumn(name="layout_id", referencedColumnName="id")
     * @awe:AutoFormElement(displayColumn="title")
     * )
     */
    protected $layout;

    /**
     * @Column(name="url", type="string", length=255)
     * @awe:AutoFormElement()
     */
    protected $url;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement()
     */
    protected $title;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Page Content",
     *     type="Awe_Dojo_Form_Element_Editor",
     *     params={"plugins"={"viewSource","undo", "redo", "cut", "copy", "paste", "bold", "italic", "underline", "strikethrough", "insertOrderedList", "insertUnorderedList", "indent", "outdent", "justifyLeft", "justifyRight", "justifyCenter", "justifyFull", "createLink", "insertImage", "fontName",  "formatBlock", "fontSize", "foreColor", "hiliteColor", "fullScreen", "enterKeyHandling", "print", "tabIndent", "toggleDir", "newPage"}},
     *     noList="True"
     * )
     */
    protected $content;

    /** 
     * @OneToMany(targetEntity="\Entities\Core\Cms\Page", mappedBy="parent")
     */
    protected $children;

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
            $parentUrl = $this->parent->getUrl();
            return "$parentUrl/$url";
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

        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
