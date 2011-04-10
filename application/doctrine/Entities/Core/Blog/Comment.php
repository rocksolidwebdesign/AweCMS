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
     * @awe:AutoFormElement()
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Entities\Core\Blog\Entry")
     * @JoinColumn(name="entry_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     label="Parent Entry",
     *     displayColumn="title"
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
     * @awe:AutoFormElement()
     */
    protected $title;

    /**
     * @Column(name="content", type="text")
     * @awe:AutoFormElement(
     *     label="Entry Body",
     *     noList="True",
     *     type="Awe_Dojo_Form_Element_Editor",
     *     params={"plugins"={"prettyprint","breadcrumb","viewSource","undo", "redo", "cut", "copy", "paste", "bold", "italic", "underline", "strikethrough", "insertOrderedList", "insertUnorderedList", "indent", "outdent", "justifyLeft", "justifyRight", "justifyCenter", "justifyFull", "createLink", "insertImage", "fontName",  "formatBlock", "fontSize", "foreColor", "hiliteColor", "fullScreen", "enterKeyHandling", "print", "tabIndent", "toggleDir", "newPage", "insertTable", "modifyTable", "InsertTableRowBefore", "InsertTableRowAfter", "insertTableColumnBefore", "insertTableColumnAfter", "deleteTableRow", "deleteTableColumn", "colorTableCell", "tableContextMenu"}}
     * )
     */
    protected $content;

    /**
     * @Column(name="pub_date", type="datetime")
     * @awe:AutoFormElement(label="Published On")
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
