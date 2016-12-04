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
class Food
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $cost;

    /**
     * @ORM\Column(type="boolean")
     */
    private $available;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="food")
     */
    private $orderitem;
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
     * Set name
     *
     * @param string $name
     *
     * @return Food
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return Food
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return Food
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available
     *
     * @return boolean
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Food
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    function __toString()
    {
        return $this->id." ".$this->name;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderitem = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add orderitem
     *
     * @param \AppBundle\Entity\OrderItem $orderitem
     *
     * @return Food
     */
    public function addOrderitem(\AppBundle\Entity\OrderItem $orderitem)
    {
        $this->orderitem[] = $orderitem;

        return $this;
    }

    /**
     * Remove orderitem
     *
     * @param \AppBundle\Entity\OrderItem $orderitem
     */
    public function removeOrderitem(\AppBundle\Entity\OrderItem $orderitem)
    {
        $this->orderitem->removeElement($orderitem);
    }

    /**
     * Get orderitem
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderitem()
    {
        return $this->orderitem;
    }
}
