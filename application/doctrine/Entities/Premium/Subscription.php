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

namespace Entities\Premium;

require_once __DIR__.'/../../AbstractEntity.php';

/**
 * @Entity
 * @Table(name="premium_subscription")
 * @HasLifecycleCallbacks
 */
class Subscription extends \Entities\AbstractEntity
{
    /**
     * @Id @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @awe:AutoFormElement(label="Id")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Entities\Access\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @awe:AutoFormElement(
     *     label="Username",
     *     name="user",
     *     display_column="username"
     * )
     */
    protected $user;

    /**
     * @Column(name="first_name", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="First Name",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $first_name;

    /**
     * @Column(name="last_name", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Last Name",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $last_name;

    /**
     * @Column(name="email", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Email",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}, "Zend_Validate_EmailAddress"=""}
     * )
     */
    protected $email;

    /**
     * @Column(name="phone", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Phone",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $phone;

    /**
     * @Column(name="card_last4", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Last 4",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $card_last4;

    /**
     * @Column(name="gateway_status", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Gateway Status",
     *     type="Zend_Dojo_Form_Element_TextBox",
     *     validators={"Zend_Validate_StringLength"={"min"=0, "max"=255}}
     * )
     */
    protected $gateway_status;

    /**
     * @Column(name="auto_renew", type="boolean")
     * @awe:AutoFormElement(
     *     label="Auto Renew",
     *     type="Zend_Form_Element_Checkbox"
     * )
     */
    protected $auto_renew;

    /**
     * @Column(name="expires_on", type="datetime")
     * @awe:AutoFormElement(
     *     label="Expires On", 
     *     type="Zend_Dojo_Form_Element_DateTextBox",
     *     params={"datePattern"="yyyy-MM-dd"}
     * )
     */
    protected $expires_on;

    /**
     * @Column(name="amount", type="string", length=255)
     * @awe:AutoFormElement(
     *     label="Price Paid", type="Zend_Dojo_Form_Element_TextBox", validators={"Zend_Validate_Float"=""}
     * )
     */
    protected $amount;

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
        $this->created_at = $this->updated_at = new DateTime("now");
    }
}
