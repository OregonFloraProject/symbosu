<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Fmdyncltaxalink
 * @ORM\Entity
 * @ORM\Table(name="fmdyncltaxalink", uniqueConstraints={@ORM\UniqueConstraint(name="PRIMARY", columns={"dynclid","tid"})},indexes={@ORM\Index(name="FK_dyncltaxalink_taxa", columns={"tid"})}  )
 */
class Fmdyncltaxalink
{
    /**
     * @var int
     *
     *
     * @ORM\Id
     * @ORM\Column(name="dynclid", type="integer", nullable=false)
     */
    private $dynclid;
    
    /**
     * @var int
     *
     *
     * @ORM\Id
     * @ORM\Column(name="tid", type="integer", nullable=false)
     */
    private $tid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="InitialTimeStamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $initialtimestamp;

    /**
     * Set dynclid.
     *
     * @param int $dynclid
     *
     * @return Fmdyncltaxalink
     */
    public function setDynclid($dynclid)
    {
        $this->dynclid = $dynclid;

        return $this;
    }

    /**
     * Get dynclid.
     *
     * @return int
     */
    public function getDynclid()
    {
        return $this->dynclid;
    }

    /**
     * Set tid.
     *
     * @param int $tid
     *
     * @return Fmdyncltaxalink
     */
    public function setTid($tid)
    {
        $this->tid = $tid;

        return $this;
    }

    /**
     * Get tid.
     *
     * @return int
     */
    public function getTid()
    {
        return $this->tid;
    }

    /**
     * Set initialtimestamp.
     *
     * @param \DateTime $initialtimestamp
     *
     * @return Fmdyncltaxalink
     */
    public function setInitialtimestamp(\DateTime $initialtimestamp)
    {
        $this->initialtimestamp = $initialtimestamp;

        return $this;
    }

    /**
     * Get initialtimestamp.
     *
     * @return \DateTime|null
     */
    public function getInitialtimestamp()
    {
        return $this->initialtimestamp;
    }

}
