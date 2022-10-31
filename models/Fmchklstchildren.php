<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Fmchklstchildren
 *
 * @ORM\Entity
 * @ORM\Table(name="fmchklstchildren", uniqueConstraints={@ORM\UniqueConstraint(name="PRIMARY", columns={"clid","clidchild"})},indexes={@ORM\Index(name="FK_fmchklstchild_clid_idx", columns={"clid"}),@ORM\Index(name="FK_fmchklstchild_child_idx", columns={"clidchild"})})
 * @ORM\Cache("READ_ONLY")
 */
class Fmchklstchildren
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="clid", type="integer")
     * @ORM\Cache("READ_ONLY")
     */
    private $clid;

    /**
     * @var int
     *
     * @ORM\Column(name="clidchild", type="integer")
     * @ORM\Id
     * @ORM\Cache("READ_ONLY")
     */
    private $clidChild;


    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modifiedtimestamp", type="datetime", nullable=true)
     */
    private $modifiedtimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="InitialTimeStamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $initialtimestamp = 'CURRENT_TIMESTAMP';

  	/**
     * Get clid.
     *
     * @return int
     */
    public function getClid()
    {
        return $this->clid;
    }

  	/**
     * Get clid.
     *
     * @return int
     */
    public function getClidChild()
    {
        return $this->clidChild;
    }
    /**
     * Set initialtimestamp.
     *
     * @param \DateTime $initialtimestamp
     *
     * @return \DateTime
     */
    public function setInitialtimestamp($initialtimestamp)
    {
        $this->initialtimestamp = $initialtimestamp;

        return $this;
    }

    /**
     * Get initialtimestamp.
     *
     * @return \DateTime
     */
    public function getInitialtimestamp()
    {
        return $this->initialtimestamp;
    }

}
