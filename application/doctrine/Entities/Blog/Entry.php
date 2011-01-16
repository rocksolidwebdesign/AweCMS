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

namespace Entities\Blog;

require_once __DIR__.'/../../AbstractEntity.php';

/**
 * @Entity
 * @Table(name="blog_entry")
 * @HasLifecycleCallbacks
 */
class Entry extends \Entities\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @Column(name="permalink", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Permalink",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $permalink;

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
     * @Column(name="pub_date", type="datetime")
     * @awe:AutoFormElement(
     *     label="Published On",
     *     type="Zend_Dojo_Form_Element_DateTextBox"
     * )
     */
    protected $pub_date;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Entry Body",
     *     type="Zend_Dojo_Form_Element_Editor",
     *     no_list="True"
     * )
     */
    protected $content;

    /**
     * @OneToMany(targetEntity="\Entities\Blog\Comment", mappedBy="entry")
     * @awe:AutoFormElement(
     *     label="Comments",
     *     name="comments",
     *     edit_inline=1,
     *     compact_view=0
     * )
     */
    protected $comments;

    /** @Column(type="datetime") */
    private $created_at;

    /** @Column(type="datetime") */
    private $updated_at;

    /** @PreUpdate */
    public function updated()
    {
        $this->updated_at = new DateTime("now");
    }

    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();

        $this->created_at = $this->updated_at = new DateTime("now");
    }
}
