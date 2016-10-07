<?php

namespace Marello\Bundle\ReturnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Model\ExtendReturnEntity;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ShippingBundle\Entity\HasShipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * @ORM\Entity(repositoryClass="ReturnEntityRepository")
 * @ORM\Table(name="marello_return_return")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *      defaultValues={
 *          "workflow"={
 *              "active_workflow"="marello_return_workflow",
 *              "show_step_in_grid"=true
 *          },
 *          "ownership"={
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *      }
 * )
 */
class ReturnEntity extends ExtendReturnEntity implements
    DerivedPropertyAwareInterface,
    ShippingAwareInterface
{

    use HasShipment;
    
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $returnNumber;

    /**
     * @var Collection|ReturnItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ReturnBundle\Entity\ReturnItem",
     *     mappedBy="return",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          }
     *      }
     * )
     */
    protected $returnItems;

    /**
     * @var SalesChannel
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    protected $salesChannel;

    /**
     * @var string
     *
     * @ORM\Column(name="saleschannel_name",type="string", nullable=false)
     */
    protected $salesChannelName;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * ReturnEntity constructor.
     */
    public function __construct()
    {
        $this->returnItems = new ArrayCollection();
    }

    /**
     * Copies product sku and name to attributes within this return item.
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        $this->organization = $order->getOrganization();

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnNumber()
    {
        return $this->returnNumber;
    }

    /**
     * @param string $returnNumber
     *
     * @return $this
     */
    public function setReturnNumber($returnNumber)
    {
        $this->returnNumber = $returnNumber;

        return $this;
    }

    /**
     * @return Collection|ReturnItem[]
     */
    public function getReturnItems()
    {
        return $this->returnItems;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function addReturnItem(ReturnItem $item)
    {
        $this->returnItems->add($item->setReturn($this));

        return $this;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function removeReturnItem(ReturnItem $item)
    {
        $this->returnItems->removeElement($item);

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowItem
     *
     * @return $this
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * @param WorkflowStep $workflowStep
     *
     * @return $this
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->returnNumber) {
            $this->setReturnNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return SalesChannel
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }

    /**
     * @param SalesChannel $salesChannel
     *
     * @return $this
     */
    public function setSalesChannel($salesChannel = null)
    {
        $this->salesChannel = $salesChannel;
        if ($this->salesChannel) {
            $this->salesChannelName = $this->salesChannel->getName();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannelName()
    {
        return $this->salesChannelName;
    }

    public function __toString()
    {
        return (string) $this->getReturnNumber();
    }

    /***
     * ==============================
     * ShippingAwareInterface methods
     * ==============================
     */

    /**
     * @return string
     */
    public function getShippingWeight()
    {
        $weight = array_reduce(
            $this
                ->getReturnItems()
                ->map(function (ReturnItem $item) {
                    $weight = $item->getOrderItem()->getProduct()->getWeight();

                    return ($weight ?: 0) * $item->getOrderItem()->getQuantity();
                })
                ->toArray(),
            function ($carry, $value) {
                return $carry + $value;
            },
            0
        );

        return $weight;
    }

    /**
     * @return string
     */
    public function getShippingDescription()
    {
        $description = '';

        foreach ($this->getReturnItems() as $item) {
            $description .= sprintf(
                "%s, ",
                $item->getOrderItem()->getProductName()
            );
        }

        return rtrim($description, ', ');
    }
}
