<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Taxonassociations
 *
 * @ORM\Table(
 					name="taxonassociations"
 	)
 * @ORM\Entity
 */
class Taxonassociations
{
  /**
   * @var integer
   *
   * @ORM\Column(name="associd")
   * @ORM\Id
   */
  private $associd;

  /**
   * @var integer
   *
   * @ORM\Column(name="tid")
   */
  private $tid;

  /**
   * @var integer|null
   *
   * @ORM\Column(name="tidassociate")
   */
  private $tidassociate;
  
  /**
   * @var string|null
   *
   * @ORM\Column(name="relationship")
   */
  private $relationship;
  
  /**
   * @var string|null
   *
   * @ORM\Column(name="verbatimsciname")
   */
  private $verbatimsciname;
  
  /**
   * @var string|null
   *
   * @ORM\Column(name="notes")
   */
  private $notes;
}

?>