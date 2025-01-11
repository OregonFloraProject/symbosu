--
-- Patches required to bring a clean installation of Symbiota 3.1 to parity with OregonFlora
--

-- JGM: This index is needed by OregonFlora checklist code, otherwise it's very slow
-- (was dropped by Symbiota in 3.0 schema)
ALTER TABLE `fmvouchers`
  ADD INDEX `chklst_taxavouchers` (`TID`, `CLID`);

-- 2024-08-16(eric):
-- This is used to speed up selecting thumbnail images for many tids,
-- as the minimum value for sortsequence per tid is not always 1.
-- See commit a00f621c66c69206faf944b4910e29353a151624
ALTER TABLE images ADD INDEX Index_tid_sortsequence (tid, sortsequence);

-- 2024-12-05:
-- Add new table `taxonassociations` for storing relationships between taxa.
CREATE TABLE `taxonassociations` (
  `associd` int(10) NOT NULL AUTO_INCREMENT COMMENT 'unique identifier for this association  within the portal',
  `tid` int(10) unsigned NOT NULL COMMENT 'subject taxon identifier',
  `associationType` varchar(150) DEFAULT NULL COMMENT 'selection from list of descriptors of the type of record the object of the relationship is',
  `tidAssociate` int(10) unsigned DEFAULT NULL COMMENT 'object taxon identifier',
  `relationship` varchar(150) NOT NULL COMMENT 'the term or phrase that describes the relationship between the subject and the object, chosen from ctcontrolvocabterm',
  `subType` varchar(45) DEFAULT NULL COMMENT 'relationship subtype, chosen from ctcontrolvocabterm',
  `relatedResourceID` varchar(250) DEFAULT NULL COMMENT 'object identifier of verbatim reference of external resource; visible text of hyperlink ',
  `basisOfRecord` varchar(45) DEFAULT NULL COMMENT 'basisOfRecord of object (internal objects only, I think) ',
  `resourceUrl` varchar(250) DEFAULT NULL COMMENT 'url of external object',
  `verbatimSciname` varchar(250) DEFAULT NULL COMMENT 'sciname used for object of relationship',
  `locationOnHost` varchar(250) DEFAULT NULL COMMENT 'attribute specific to parasite/host relationship',
  `notes` varchar(250) DEFAULT NULL COMMENT 'relationship remarks',
  `recordID` varchar(45) DEFAULT NULL COMMENT 'GUID for internally defined association',
  `accordingTo` varchar(45) DEFAULT NULL COMMENT 'reference asserting the relationship',
  `establishedDate` datetime DEFAULT NULL,
  `modifiedUid` int(10) unsigned DEFAULT NULL COMMENT 'foreign key on users.uid',
  `modifiedTimestamp` datetime DEFAULT NULL,
  `createdUid` int(10) unsigned DEFAULT NULL COMMENT 'foreign key on users.uid',
  `initialTimestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`associd`),
  KEY `tid` (`tid`),
  KEY `tidAssociate` (`tidAssociate`),
  KEY `modifiedUid` (`modifiedUid`),
  KEY `createdUid` (`createdUid`),
  CONSTRAINT `FK_taxonassociations_createdUid` FOREIGN KEY (`createdUid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_taxonassociations_modifiedUid` FOREIGN KEY (`modifiedUid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_taxonassociations_tid` FOREIGN KEY (`tid`) REFERENCES `taxa` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_taxonassociations_tidassociate` FOREIGN KEY (`tidAssociate`) REFERENCES `taxa` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 2025-01-01:
-- This reverts a change that was made by Symbiota.
-- This large column size is needed for pulling in data from GBIF,
-- a feature that James created that we use (though it is currently broken in base Symbiota).
ALTER TABLE `institutions`
  CHANGE COLUMN `notes` `notes` varchar(19500) DEFAULT NULL;
