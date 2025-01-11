--
-- 2025-01-01: Patches to pull in changes to db_schema-3.0.sql
-- from commit 7a6b002f8855c57ce30209a05801544e38fba08d
--

ALTER TABLE `agents`
  CHANGE COLUMN `guid` `guid` varchar(150) DEFAULT NULL,
  CHANGE COLUMN `taxonomicGroups` `taxonomicGroups` varchar(150) DEFAULT NULL,
  CHANGE COLUMN `collectionsAt` `collectionsAt` varchar(150) DEFAULT NULL;

/*
We don't want this change

ALTER TABLE `institutions`
  CHANGE COLUMN `notes` `notes` varchar(250) DEFAULT NULL;
*/

ALTER TABLE `uploadspectemp`
  CHANGE COLUMN `taxonRemarks` `taxonRemarks` text DEFAULT NULL,
  CHANGE COLUMN `identificationReferences` `identificationReferences` text DEFAULT NULL,
  CHANGE COLUMN `identificationRemarks` `identificationRemarks` text DEFAULT NULL;
