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

namespace Entities\Core\Access;

/**
 * @Entity
 * @Table(name="access_group")
 * @HasLifecycleCallbacks
 */
class Group extends \Entities\Core\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @Column(name="title", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Label",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $title;

    /**
     * @Column(name="codename", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Unique ID",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $codename;

    /**
     * @ManyToMany(targetEntity="\Entities\Core\Access\User", mappedBy="groups")
     * @JoinTable(
     *      name="access_user_group",
     *      joinColumns={@JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     * @awe:AutoFormElement(
     *     label="Users",
     *     displayColumn="username"
     * )
     */
    public $users;

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
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();

        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
