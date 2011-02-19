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
 * @Table(name="blog_comment")
 * @HasLifecycleCallbacks
 */
class Comment extends \Entities\Core\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Blog\Entry")
     * @JoinColumn(name="entry_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     label="Blog Entry",
     *     name="entry",
     *     display_column="title"
     * )
     */
    protected $entry;

    /**
     * @Column(name="approved", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Approved",
     *     type="Zend_Dojo_Form_Element_CheckBox"
     * )
     */
    protected $approved;

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
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Entry Body",
     *     type="Zend_Dojo_Form_Element_Editor",
     *     no_list="True"
     * )
     */
    protected $content;

    /**
     * @Column(name="pub_date", type="datetime")
     * @awe:AutoFormElement(
     *     label="Published On",
     *     type="Zend_Dojo_Form_Element_DateTextBox"
     * )
     */
    protected $pub_date;

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
