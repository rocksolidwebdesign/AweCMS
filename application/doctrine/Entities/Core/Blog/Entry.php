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

namespace Entities\Core\Blog;

/**
 * @Entity
 * @Table(name="blog_entry")
 * @HasLifecycleCallbacks
 */
class Entry extends \Entities\Core\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @Column(name="permalink", type="string", length=255)
     * @awe:AutoFormElement()
     */
    protected $permalink;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement()
     */
    protected $title;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Design\Layout")
     * @JoinColumn(name="layout_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     name="layout", 
     *     label="Layout", 
     *     displayColumn="title"
     * )
     */
    protected $layout;

    /**
     * @Column(name="pub_date", type="datetime")
     * @awe:AutoFormElement()
     */
    protected $pub_date;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Entry Body",
     *     type="Awe_Dojo_Form_Element_Editor",
     *     params={"plugins"={"viewSource","undo", "redo", "cut", "copy", "paste", "bold", "italic", "underline", "strikethrough", "insertOrderedList", "insertUnorderedList", "indent", "outdent", "justifyLeft", "justifyRight", "justifyCenter", "justifyFull", "createLink", "insertImage", "fontName",  "formatBlock", "fontSize", "foreColor", "hiliteColor", "fullScreen", "enterKeyHandling", "print", "tabIndent", "toggleDir", "newPage"}},
     *     noList="True"
     * )
     */
    protected $content;

    /**
     * @OneToMany(targetEntity="\Entities\Core\Blog\Comment", mappedBy="entry")
     * @awe:AutoFormElement(
     *     label="Comments",
     *     name="comments",
     *     editInline=1,
     *     compactView=0
     * )
     */
    protected $comments;

    /*
     * getUrl()
     *
     * return: string - The url of this page including its parents
     */
    public function getUrl()
    {
        $rootUrl      = "/blog";
        $archiveUrl   = $this->getArchiveUrl();
        $permalinkUrl = $this->getPermalinkUrl();
        $url = "$rootUrl/$archiveUrl/$permalinkUrl";

        return $url;
    }

    public function getArchiveUrl()
    {
        return $this->pub_date->format('m/d/Y');
    }

    public function getPermalinkUrl()
    {
        return ($this->permalink ? $this->permalink : $this->id);
    }

    /*
     * getBreadcrumbs()
     *
     * param: $result array - Recursive url input chain
     * return: array - Parents and grandparents leading to the root
     */
    public function getBreadcrumbs($url = 'UNINITIALIZED', $result = array())
    {
        $url = $url == 'UNINITIALIZED' ? $this->getUrl() : $url;
        $url = $url ? $url : '/';

        preg_match('#^(.*)/([^/]{1,})$#',$url,$matches);

        $crumbs  = isset($matches[1]) ? $matches[1] : '';
        $current = isset($matches[2]) ? $matches[2] : '';

        $title = ($this->getPermalinkUrl() == $current ? $this->title : 
            ($current == 'blog' ? 'Blog' : 
                ($current == '' ? 'Home' : $current)
            )
        );

        // generate the breadcrumb for this page
        $crumb = array(
            'url' => $url,
            'title' => $title,
        );

        // prepend it to the list of crumbs
        array_unshift($result, $crumb);

        // if this page has a parent
        if ($url != '/') {
            $url = $crumbs;
            // add the parent's breadcrumb to the result
            return $this->getBreadcrumbs($url, $result);
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
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();

        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
