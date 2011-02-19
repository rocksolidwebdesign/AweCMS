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
 * @Table(name="access_user")
 * @HasLifecycleCallbacks
 */
class User extends \Entities\Core\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @Column(name="user_type", type="string", length=255)
     * @awe:AutoFormElement(label="User Type", type="Zend_Dojo_Form_Element_TextBox", validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}})
     */
    protected $user_type;


    /**
     * @Column(name="username", type="string", length=255)
     * @awe:AutoFormElement(label="Username", type="Zend_Dojo_Form_Element_TextBox", validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}})
     */
    protected $username;

    /**
     * @Column(name="password", type="string", length=255)
     * @awe:AutoFormElement(label="Password", type="Zend_Dojo_Form_Element_TextBox", validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}})
     */
    protected $password;

    /**
     * @Column(name="email", type="string", length=255)
     * @awe:AutoFormElement(label="Email", type="Zend_Dojo_Form_Element_TextBox", validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}, "Zend_Validate_EmailAddress"=""})
     */
    protected $email;

    /**
     * @ManyToMany(targetEntity="\Entities\Core\Access\Group", inversedBy="users")
     * @JoinTable(name="access_user_group",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     * @awe:AutoFormElement(label="Groups", add_method="addGroup", display_column="title")
     */
    public $groups;

    /**
     * @OneToMany(targetEntity="\Entities\Core\Premium\Subscription", mappedBy="user")
     * @awe:AutoFormElement(
     *     label="Subscriptions",
     *     edit_inline=1
     * )
     */
    protected $subscriptions;

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
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subscriptions = new \Doctrine\Common\Collections\ArrayCollection();

        $this->created_at = $this->updated_at = new \DateTime("now");
    }
}
