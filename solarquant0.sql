-- MySQL dump 10.13  Distrib 5.5.58, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: solarquant
-- ------------------------------------------------------
-- Server version	5.5.58-0+deb8u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `consumption_datum`
--

DROP TABLE IF EXISTS `consumption_datum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumption_datum` (
  `consumption_datum_id` int(11) NOT NULL AUTO_INCREMENT,
  `volts` double DEFAULT NULL,
  `node_id` int(11) NOT NULL,
  `source_id` varchar(255) NOT NULL,
  `when_logged` datetime NOT NULL,
  `when_entered` datetime NOT NULL,
  `amps` double DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`consumption_datum_id`),
  KEY `consump1` (`when_logged`,`node_id`,`consumption_datum_id`),
  KEY `part_of_when_logged_consumption` (`when_logged`),
  KEY `cd_main_index` (`consumption_datum_id`,`when_logged`,`amps`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumption_datum`
--

LOCK TABLES `consumption_datum` WRITE;
/*!40000 ALTER TABLE `consumption_datum` DISABLE KEYS */;
/*!40000 ALTER TABLE `consumption_datum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumption_input_pattern`
--

DROP TABLE IF EXISTS `consumption_input_pattern`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumption_input_pattern` (
  `consumption_input_pattern_id` int(11) NOT NULL AUTO_INCREMENT,
  `trial_name` varchar(255) NOT NULL,
  `pattern_set_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `time_of_day_weight` float NOT NULL,
  `day_of_year_weight` float NOT NULL,
  `is_monday_weight` float NOT NULL,
  `is_tuesday_weight` float NOT NULL,
  `is_wednesday_weight` float NOT NULL,
  `is_thursday_weight` float NOT NULL,
  `is_friday_weight` float NOT NULL,
  `is_saturday_weight` float NOT NULL,
  `is_sunday_weight` float NOT NULL,
  `barometric_pressure_weight` float NOT NULL,
  `humidity_outside_weight` float NOT NULL DEFAULT '0',
  `temperature_outside_weight` float NOT NULL,
  `is_condition_clear_weight` float NOT NULL,
  `is_condition_clear_night_weight` float NOT NULL,
  `is_condition_fewclouds_weight` float NOT NULL,
  `is_condition_fewcloudsnight_weight` float NOT NULL,
  `is_condition_fog_weight` float NOT NULL,
  `is_condition_overcast_weight` float NOT NULL,
  `is_condition_severealert_weight` float NOT NULL,
  `is_condition_showers_weight` float NOT NULL,
  `is_condition_showers_scattered_weight` float NOT NULL,
  `is_condition_snow_weight` float NOT NULL,
  `is_condition_storm_weight` float NOT NULL,
  `is_condition_general_haze_weight` float NOT NULL DEFAULT '0',
  `is_condition_general_windy_weight` float NOT NULL DEFAULT '0',
  `is_condition_general_wet_weight` float NOT NULL DEFAULT '0',
  `is_condition_general_clear_weight` float NOT NULL DEFAULT '0',
  `is_condition_general_cloudy_weight` float NOT NULL DEFAULT '0',
  `temperature_hotter_weight` float NOT NULL,
  `temperature_colder_weight` float NOT NULL,
  `kilowatt_hours_weight` float NOT NULL,
  `sse` double DEFAULT NULL,
  PRIMARY KEY (`consumption_input_pattern_id`),
  KEY `set_node_start` (`consumption_input_pattern_id`,`node_id`,`start_datetime`,`end_datetime`),
  KEY `cip_trialname_index` (`consumption_input_pattern_id`,`trial_name`),
  KEY `cip_main_index` (`consumption_input_pattern_id`,`node_id`,`pattern_set_id`,`trial_name`,`start_datetime`,`end_datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumption_input_pattern`
--

LOCK TABLES `consumption_input_pattern` WRITE;
/*!40000 ALTER TABLE `consumption_input_pattern` DISABLE KEYS */;
/*!40000 ALTER TABLE `consumption_input_pattern` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inputpattern_extensions`
--

DROP TABLE IF EXISTS `inputpattern_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inputpattern_extensions` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `consumption_input_pattern_id` int(11) NOT NULL,
  `predicted_kilowatt_hours_weight` float NOT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `ie_main_index` (`extension_id`,`consumption_input_pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inputpattern_extensions`
--

LOCK TABLES `inputpattern_extensions` WRITE;
/*!40000 ALTER TABLE `inputpattern_extensions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inputpattern_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node`
--

DROP TABLE IF EXISTS `node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node` (
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `node_type_id` int(11) DEFAULT NULL,
  `subscribed_source_ids` varchar(255) NOT NULL,
  `weather_node_id` int(11) NOT NULL DEFAULT '0',
  `location` varchar(100) NOT NULL DEFAULT '',
  `latitude` float NOT NULL DEFAULT '0',
  `longitude` float NOT NULL DEFAULT '0',
  `time_zone` varchar(40) DEFAULT NULL,
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `total_panels` int(11) NOT NULL DEFAULT '0',
  `total_watts` double DEFAULT NULL,
  `scaling_factor` double DEFAULT NULL,
  `charge_controller_model_id` int(11) NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  `total_amp_hours` float NOT NULL DEFAULT '0',
  `total_batteries` int(11) NOT NULL DEFAULT '0',
  `gst_offset` int(11) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `address_id` int(10) unsigned NOT NULL DEFAULT '0',
  `volts` int(11) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `wc_identifier` varchar(100) DEFAULT NULL,
  `is_subscribed_for_training` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`),
  KEY `node1` (`location`),
  KEY `n_main_index` (`node_id`,`node_type_id`,`location`,`is_subscribed_for_training`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node`
--

LOCK TABLES `node` WRITE;
/*!40000 ALTER TABLE `node` DISABLE KEYS */;
/*!40000 ALTER TABLE `node` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_source`
--

DROP TABLE IF EXISTS `node_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_source` (
  `node_id` int(11) NOT NULL,
  `sourceId` varchar(255) NOT NULL,
  `source_type_id` int(11) NOT NULL,
  KEY `ns_main_index` (`node_id`,`sourceId`,`source_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_source`
--

LOCK TABLES `node_source` WRITE;
/*!40000 ALTER TABLE `node_source` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pattern_set`
--

DROP TABLE IF EXISTS `pattern_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pattern_set` (
  `pattern_set_id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_set_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `notes` text,
  `pattern_set_type_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pattern_set_id`),
  KEY `status_id` (`status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pattern_set`
--

LOCK TABLES `pattern_set` WRITE;
/*!40000 ALTER TABLE `pattern_set` DISABLE KEYS */;
/*!40000 ALTER TABLE `pattern_set` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patternset_node_match`
--

DROP TABLE IF EXISTS `patternset_node_match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patternset_node_match` (
  `pattern_set_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `sourceId` varchar(255) DEFAULT NULL,
  `match_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`match_id`),
  KEY `main_index` (`pattern_set_id`,`node_id`),
  KEY `pnm_main_index` (`pattern_set_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patternset_node_match`
--

LOCK TABLES `patternset_node_match` WRITE;
/*!40000 ALTER TABLE `patternset_node_match` DISABLE KEYS */;
/*!40000 ALTER TABLE `patternset_node_match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `power_datum`
--

DROP TABLE IF EXISTS `power_datum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `power_datum` (
  `power_datum_id` int(11) NOT NULL AUTO_INCREMENT,
  `volts` double DEFAULT NULL,
  `pv_volts` double DEFAULT NULL,
  `pv_amps` double DEFAULT NULL,
  `battery_volts` double DEFAULT NULL,
  `battery_amps` double DEFAULT NULL,
  `dc_volts` double DEFAULT NULL,
  `dc_amps` double DEFAULT NULL,
  `ac_volts` double DEFAULT NULL,
  `ac_amps` double DEFAULT NULL,
  `amp_hours` double DEFAULT NULL,
  `kilowatt_hours` double NOT NULL,
  `node_id` int(11) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `when_logged` datetime NOT NULL,
  `when_entered` datetime NOT NULL,
  `amps` double DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`power_datum_id`),
  KEY `whenLogged` (`when_logged`,`node_id`,`power_datum_id`),
  KEY `part_of_when_logged_power` (`when_logged`),
  KEY `pd_main_index` (`power_datum_id`,`when_logged`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `power_datum`
--

LOCK TABLES `power_datum` WRITE;
/*!40000 ALTER TABLE `power_datum` DISABLE KEYS */;
/*!40000 ALTER TABLE `power_datum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solar_error`
--

DROP TABLE IF EXISTS `solar_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solar_error` (
  `solar_error_id` int(11) NOT NULL AUTO_INCREMENT,
  `when_logged` datetime NOT NULL,
  `module` varchar(255) NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`solar_error_id`),
  KEY `se_main_index` (`solar_error_id`,`when_logged`),
  KEY `se_module_index` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solar_error`
--

LOCK TABLES `solar_error` WRITE;
/*!40000 ALTER TABLE `solar_error` DISABLE KEYS */;
INSERT INTO `solar_error` VALUES (1,'2017-11-16 21:11:01','cron_createTrainingFiles','processed NOT chocked, theFilePointer'),(2,'2017-11-16 21:11:01','Node::getPatternSetIds','INFO: sql:SELECT p.pattern_set_id \n		FROM  `pattern_set` AS p\n		INNER JOIN patternset_node_match AS pnm\n		WHERE p.pattern_set_id = pnm.pattern_set_id AND p.status_id = 5 status:trainingFileUnderway'),(3,'2017-11-16 21:11:01','cron_createTrainingFiles','found 0 subscribedNodes'),(4,'2017-11-16 21:11:01','Node::getPatternSetIds','INFO: sql:SELECT p.pattern_set_id \n		FROM  `pattern_set` AS p\n		INNER JOIN patternset_node_match AS pnm\n		WHERE p.pattern_set_id = pnm.pattern_set_id AND p.status_id = 7 status:questioningFileUnderway'),(5,'2017-11-16 21:11:01','cron_checkEmergent','sizeof(activeQuestioningPatternSets):0'),(6,'2017-11-16 21:11:01','cron_checkEmergent','sizeof(activePatternSets) 0 sizeof(activeQuestioningPatternSets):0'),(7,'2017-11-16 21:11:01','cron_checkEmergent','no activepatternsets or activeQuestioningPatternSets ');
/*!40000 ALTER TABLE `solar_error` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `time_zone`
--

DROP TABLE IF EXISTS `time_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `time_zone` (
  `time_zone_id` int(11) NOT NULL,
  `time_zone` varchar(40) NOT NULL,
  PRIMARY KEY (`time_zone_id`),
  KEY `tz1` (`time_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `time_zone`
--

LOCK TABLES `time_zone` WRITE;
/*!40000 ALTER TABLE `time_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `time_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_datum`
--

DROP TABLE IF EXISTS `training_datum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_datum` (
  `training_datum_id` int(11) NOT NULL AUTO_INCREMENT,
  `training_file_id` int(11) NOT NULL,
  `batch` int(11) NOT NULL,
  `epoch` int(11) NOT NULL,
  `when_logged` datetime NOT NULL,
  `sse` float NOT NULL,
  PRIMARY KEY (`training_datum_id`),
  KEY `td_main_index` (`training_datum_id`,`training_file_id`,`when_logged`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_datum`
--

LOCK TABLES `training_datum` WRITE;
/*!40000 ALTER TABLE `training_datum` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_datum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_file`
--

DROP TABLE IF EXISTS `training_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_file` (
  `training_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_set_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `input_weights_file_name` varchar(255) NOT NULL,
  `output_weights_file_name` varchar(255) NOT NULL,
  `input_file_name` varchar(255) NOT NULL,
  `output_file_name` varchar(255) NOT NULL,
  `emergent_log_file_name` varchar(255) NOT NULL,
  `start_training` datetime NOT NULL,
  `stop_training` datetime NOT NULL,
  `current_sse` float NOT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `filename` varchar(100) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`training_file_id`),
  KEY `tf_main_index` (`pattern_set_id`,`created_on`,`status_id`,`start_training`,`stop_training`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_file`
--

LOCK TABLES `training_file` WRITE;
/*!40000 ALTER TABLE `training_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather_datum`
--

DROP TABLE IF EXISTS `weather_datum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weather_datum` (
  `weather_datum_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) DEFAULT NULL,
  `weather_source_id` int(11) NOT NULL,
  `sky_conditions` varchar(255) DEFAULT NULL,
  `weather_condition` varchar(255) DEFAULT NULL,
  `temperature_celsius` double DEFAULT NULL,
  `humidity` double DEFAULT NULL,
  `barometric_pressure` double DEFAULT NULL,
  `barometer_delta` varchar(20) DEFAULT NULL,
  `visibility` double DEFAULT NULL,
  `when_entered` datetime NOT NULL,
  `when_logged` datetime NOT NULL,
  `node_id` int(11) NOT NULL,
  `reporting_node_id` int(11) NOT NULL,
  `uv_index` int(11) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`weather_datum_id`),
  KEY `sort2` (`node_id`,`when_entered`,`when_logged`,`weather_datum_id`,`reporting_node_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather_datum`
--

LOCK TABLES `weather_datum` WRITE;
/*!40000 ALTER TABLE `weather_datum` DISABLE KEYS */;
/*!40000 ALTER TABLE `weather_datum` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-11-16 21:11:55
