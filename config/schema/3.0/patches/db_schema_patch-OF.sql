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

-- 2025-10-21(james): This speeds up finding the last modified user to display in the occurrence editor search 
ALTER TABLE `omoccuredits` ADD INDEX `IX_occid_timestamp` (`occid`, `initialtimestamp`);

-- 2025-10-21(james): This should speed up searching for otherCatalogNumbers (which happens across omoccuridentifiers and omoccurrences)
ALTER TABLE `omoccurrences` ADD Index `IX_occurrences_collid_otherCatalogNumbers` (`collid`, `otherCatalogNumbers`);

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

-- This determines the UI component used for this characteristic in the Identify / Grow Natives
-- filter tools and was probably added during development of those tools.
ALTER TABLE `kmcharacters`
  ADD COLUMN `display` varchar(45) DEFAULT NULL AFTER `sortsequence`;

-- 2025-05-08:
-- Triggers to automatically update `omoccurgeoindex` (used by dynamicmap)
-- when `omoccurrences` is updated.
-- This was previously done by a cronjob outside of symbiota, but the cronjob was not able to
-- gracefully handle updates or deletes.
--
-- NOTE: definer here will be the user that runs the CREATE TRIGGER commands, unless DEFINER is explicity specified
DELIMITER |
CREATE TRIGGER `omoccurrences_insert_omoccurgeoindex` AFTER INSERT ON `omoccurrences`
FOR EACH ROW BEGIN
  IF (NEW.`decimalLatitude` BETWEEN -90 AND 90)
    AND (NEW.`decimalLongitude` BETWEEN -180 AND 180)
    AND NEW.`tidinterpreted` IS NOT NULL
    AND (NEW.`cultivationStatus` IS NULL OR NEW.`cultivationStatus` = 0)
    AND (NEW.`coordinateUncertaintyInMeters` IS NULL OR NEW.`coordinateUncertaintyInMeters` < 10000)
  THEN
    INSERT IGNORE INTO omoccurgeoindex (`tid`,`decimallatitude`,`decimallongitude`)
    VALUES (NEW.`tidinterpreted`,ROUND(NEW.`decimallatitude`, 2),ROUND(NEW.`decimallongitude`, 2));
  END IF;
END;
|

CREATE TRIGGER `omoccurrences_update_omoccurgeoindex` AFTER UPDATE ON `omoccurrences`
FOR EACH ROW BEGIN
  IF (NEW.`decimalLatitude` BETWEEN -90 AND 90)
    AND (NEW.`decimalLongitude` BETWEEN -180 AND 180)
    AND NEW.`tidinterpreted` IS NOT NULL
    AND (NEW.`cultivationStatus` IS NULL OR NEW.`cultivationStatus` = 0)
    AND (NEW.`coordinateUncertaintyInMeters` IS NULL OR NEW.`coordinateUncertaintyInMeters` < 10000)
  THEN
    IF ((SELECT COUNT(`occid`) FROM `omoccurrences` WHERE `tidinterpreted` = OLD.`tidinterpreted` AND ROUND(`decimalLatitude`, 2) = ROUND(OLD.`decimalLatitude`, 2) AND ROUND(`decimalLongitude`, 2) = ROUND(OLD.`decimalLongitude`, 2) AND (`cultivationStatus` IS NULL OR `cultivationStatus` = 0) AND (`coordinateUncertaintyInMeters` IS NULL OR `coordinateUncertaintyInMeters` < 10000)) = 0) THEN
      DELETE FROM omoccurgeoindex WHERE `tid` = OLD.`tidinterpreted` AND `decimalLatitude` = ROUND(OLD.`decimalLatitude`, 2) AND `decimalLongitude` = ROUND(OLD.`decimalLongitude`, 2);
    END IF;

    INSERT IGNORE INTO omoccurgeoindex (`tid`,`decimallatitude`,`decimallongitude`)
    VALUES (NEW.`tidinterpreted`,ROUND(NEW.`decimallatitude`, 2),ROUND(NEW.`decimallongitude`, 2));
  END IF;
END;
|

CREATE TRIGGER `omoccurrences_delete_omoccurgeoindex` AFTER DELETE ON `omoccurrences`
FOR EACH ROW BEGIN
  IF (OLD.`decimalLatitude` BETWEEN -90 AND 90)
    AND (OLD.`decimalLongitude` BETWEEN -180 AND 180)
    AND OLD.`tidinterpreted` IS NOT NULL
    AND (OLD.`cultivationStatus` IS NULL OR OLD.`cultivationStatus` = 0)
    AND (OLD.`coordinateUncertaintyInMeters` IS NULL OR OLD.`coordinateUncertaintyInMeters` < 10000)
    AND ((SELECT COUNT(`occid`) FROM `omoccurrences` WHERE `tidinterpreted` = OLD.`tidinterpreted` AND ROUND(`decimalLatitude`, 2) = ROUND(OLD.`decimalLatitude`, 2) AND ROUND(`decimalLongitude`, 2) = ROUND(OLD.`decimalLongitude`, 2) AND (`cultivationStatus` IS NULL OR `cultivationStatus` = 0) AND (`coordinateUncertaintyInMeters` IS NULL OR `coordinateUncertaintyInMeters` < 10000)) = 0)
  THEN
    DELETE FROM omoccurgeoindex WHERE `tid` = OLD.`tidinterpreted` AND `decimalLatitude` = ROUND(OLD.`decimalLatitude`, 2) AND `decimalLongitude` = ROUND(OLD.`decimalLongitude`, 2);
  END IF;
END;
|
DELIMITER ;
