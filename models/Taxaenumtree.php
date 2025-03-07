<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Taxaenumtree
 *
 * @ORM\Table(name="taxaenumtree", uniqueConstraints={@ORM\UniqueConstraint(name="PRIMARY", columns={"tid", "taxauthid", "parenttid"})}, indexes={@ORM\Index(name="FK_tet_taxa2", columns={"parenttid"}), @ORM\Index(name="FK_tet_taxa", columns={"tid"}), @ORM\Index(name="FK_tet_taxauth", columns={"taxauthid"})})
 * @ORM\Entity
 */
class Taxaenumtree
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initialtimestamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $initialtimestamp = 'CURRENT_TIMESTAMP';

    /**
     * @var \Taxa
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Taxa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tid", referencedColumnName="TID")
     * })
     */
    private $tid;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="parenttid", type="integer", nullable=false)
     */
    private $parenttid;

    /**
     * @var \Taxauthority
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Taxauthority")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taxauthid", referencedColumnName="taxauthid")
     * })
     */
    private $taxauthid;



    /**
     * Set initialtimestamp.
     *
     * @param \DateTime $initialtimestamp
     *
     * @return Taxaenumtree
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

    /**
     * Set tid.
     *
     * @param \Taxa $tid
     *
     * @return Taxaenumtree
     */
    public function setTid(\Taxa $tid)
    {
        $this->tid = $tid;

        return $this;
    }

    /**
     * Get tid.
     *
     * @return \Taxa
     */
    public function getTid()
    {
        return $this->tid;
    }

    /**
     * Set parenttid.
     *
     * @param \Taxa $parenttid
     *
     * @return Taxaenumtree
     */
    public function setParenttid(\Taxa $parenttid)
    {
        $this->parenttid = $parenttid;

        return $this;
    }

    /**
     * Get parenttid.
     *
     * @return \Taxa
     */
    public function getParenttid()
    {
        return $this->parenttid;
    }

    /**
     * Set taxauthid.
     *
     * @param integer $taxauthid
     *
     * @return Taxaenumtree
     */
    public function setTaxauthid($taxauthid)
    {
        $this->taxauthid = $taxauthid;

        return $this;
    }

    /**
     * Get taxauthid.
     *
     * @return integer
     */
    public function getTaxauthid()
    {
        return $this->taxauthid;
    }
}
