<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 15:32
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @package AppBundle\Entity
 */
class OrderItem
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderItem", cascade={"all"})
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Food", inversedBy="orderitem")
     */
    private $food;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return OrderItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set food
     *
     * @param \AppBundle\Entity\Food $food
     *
     * @return OrderItem
     */
    public function setFood(\AppBundle\Entity\Food $food = null)
    {
        $this->food = $food;

        return $this;
    }

    /**
     * Get food
     *
     * @return \AppBundle\Entity\Food
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set order
     *
     * @param \AppBundle\Entity\Order $order
     *
     * @return OrderItem
     */
    public function setOrder(\AppBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \AppBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
