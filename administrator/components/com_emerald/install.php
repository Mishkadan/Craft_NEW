<?php
/**
 * Emerald by MintJoomla
 * a component for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 *
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');


class com_emeraldInstallerScript
{
	function install($parent)
	{
		$this->_updateTables();
		$this->_install();

		return TRUE;
	}

	function uninstall($parent)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SHOW TABLES lIKE '%_emerald_%'");
		$list = $db->loadColumn();

		foreach($list AS $table)
		{
			$db->setQuery("DROP TABLE IF EXISTS `{$table}`");
			//$db->execute();
		}
	}

	function update($parent)
	{
		$this->_updateTables();

		$this->_deleteFiles('/components/com_emerald/controllers/', 'file', array('plans.php', 'payment.php'));
		$this->_deleteFiles('/components/com_emerald/views/', 'folder');
		$this->_deleteFiles('/components/com_emerald/models/', 'file');

		$this->_deleteFiles('/administrator/components/com_emerald/tables/', 'file');
		$this->_deleteFiles('/administrator/components/com_emerald/helpers/', 'file');
		$this->_deleteFiles('/administrator/components/com_emerald/xml/', 'file');
		$this->_deleteFiles('/administrator/components/com_emerald/controllers/', 'file');

		if(JFile::exists(JPATH_ROOT.'/components/com_emerald/library/php/markdown.php'))
		{
			JFile::delete(JPATH_ROOT.'/components/com_emerald/library/php/markdown.php');
		}
	}


	function _deleteFiles($dir, $type, $exclude = array())
	{
		$function = $type . 's';

		if(!JFolder::exists(JPATH_ROOT . $dir))
		{
			return;
		}

		$res = JFolder::$function(JPATH_ROOT . $dir, '', FALSE, TRUE, $exclude, array('\.html', '^em'));

		foreach($res as $r)
		{
			$class = 'J' . ucfirst($type);
			if($class::exists($r))
			{
				$class::delete($r);
			}
		}
	}


	private function _install()
	{
		$db = JFactory::getDbo();

		$db->setQuery('DELETE FROM `#__emerald_states`');
		$db->execute();

		$db->setQuery("INSERT INTO `#__emerald_states`
			VALUES ('1', 'US', 'AL', 'Alabama'), ('2', 'US', 'AK', 'Alaska'), ('3', 'US', 'AZ', 'Arizona'),
			('4', 'US', 'AR', 'Arkansas'), ('5', 'US', 'CA', 'California'), ('6', 'US', 'CO', 'Colorado'), ('7', 'US', 'CT', 'Connecticut'),
			('8', 'US', 'DE', 'Delaware'), ('9', 'US', 'DC', 'District of Columbia'), ('10', 'US', 'FL', 'Florida'), ('11', 'US', 'GA', 'Georgia'),
			('12', 'US', 'HI', 'Hawaii'), ('13', 'US', 'ID', 'Idaho'), ('14', 'US', 'IL', 'Illinois'), ('15', 'US', 'IN', 'Indiana'),
			('16', 'US', 'IA', 'Iowa'), ('17', 'US', 'KS', 'Kansas'), ('18', 'US', 'KY', 'Kentucky'), ('19', 'US', 'LA', 'Louisiana'),
			('20', 'US', 'ME', 'Maine'), ('21', 'US', 'MD', 'Maryland'), ('22', 'US', 'MA', 'Massachusetts'), ('23', 'US', 'MI', 'Michigan'),
			('24', 'US', 'MN', 'Minnesota'), ('25', 'US', 'MS', 'Mississippi'), ('26', 'US', 'MO', 'Missouri'), ('27', 'US', 'MT', 'Montana'),
			('28', 'US', 'NE', 'Nebraska'), ('29', 'US', 'NV', 'Nevada'), ('30', 'US', 'NH', 'New Hampshire'), ('31', 'US', 'NJ', 'New Jersey'),
			('32', 'US', 'NM', 'New Mexico'), ('33', 'US', 'NY', 'New York'), ('34', 'US', 'NC', 'North Carolina'), ('35', 'US', 'ND', 'North Dakota'),
			('36', 'US', 'OH', 'Ohio'), ('37', 'US', 'OK', 'Oklahoma'), ('38', 'US', 'OR', 'Oregon'), ('39', 'US', 'PA', 'Pennsylvania'),
			('40', 'US', 'RI', 'Rhode Island'), ('41', 'US', 'SC', 'South Carolina'), ('42', 'US', 'SD', 'South Dakota'), ('43', 'US', 'TN', 'Tennessee'),
			('44', 'US', 'TX', 'Texas'), ('45', 'US', 'UT', 'Utah'), ('46', 'US', 'VT', 'Vermont'), ('47', 'US', 'VA', 'Virginia'), ('48', 'US', 'WA', 'Washington'),
			('49', 'US', 'WV', 'West Virginia'), ('50', 'US', 'WI', 'Wisconsin'), ('51', 'US', 'WY', 'Wyoming'), ('52', 'CA', 'AB', 'Alberta'),
			('53', 'CA', 'BC', 'British Columbia'), ('54', 'CA', 'MB', 'Manitoba'), ('55', 'CA', 'NB', 'New Brunswick'), ('56', 'CA', 'NL', 'Newfoundland and Labrador'),
			('57', 'CA', 'NT', 'Northwest Territories'), ('58', 'CA', 'NS', 'Nova Scotia'), ('59', 'CA', 'NU', 'Nunavut'), ('60', 'CA', 'ON', 'Ontario'),
			('61', 'CA', 'PE', 'Prince Edward Island'), ('62', 'CA', 'QC', 'Quebec'), ('63', 'CA', 'SK', 'Saskatchewan'), ('64', 'CA', 'YT', 'Yukon'),
			('65', 'AU', 'ACT', 'Australian Capital Territory'), ('66', 'AU', 'NSW', 'New South Wales'), ('67', 'AU', 'AU-NT', 'Northern Terittory'),
			('68', 'AU', 'QLD', 'Queensland'), ('69', 'AU', 'AU-SA', 'South Australia'), ('70', 'AU', 'TAS', 'Tasmania'), ('71', 'AU', 'VIC', 'Victoria'),
			('72', 'AU', 'AU-WA', 'Western Australia'), ('73', 'GR', 'GR-ATT', '????????????'), ('74', 'GR', 'GR-EFV', '????????????'), ('75', 'GR', 'GR-EVT', '??????????????????'),
			('76', 'GR', 'GR-FOK', '????????????'), ('77', 'GR', 'GR-FTH', '????????????????'), ('78', 'GR', 'GR-VIO', '??????????????'), ('79', 'GR', 'GR-HAL', '??????????????????'),
			('80', 'GR', 'GR-IMA', '????????????'), ('81', 'GR', 'GR-KIL', '????????????'), ('82', 'GR', 'GR-PEL', '??????????'), ('83', 'GR', 'GR-PIE', '????????????'),
			('84', 'GR', 'GR-SER', '????????????'), ('85', 'GR', 'GR-THE', '??????????????????????'), ('86', 'GR', 'GR-CHA', '??????????'), ('87', 'GR', 'GR-IRA', '????????????????'),
			('88', 'GR', 'GR-LAS', '????????????'), ('89', 'GR', 'GR-RET', '??????????????'), ('90', 'GR', 'GR-DRA', '??????????'), ('91', 'GR', 'GR-EVR', '??????????'),
			('92', 'GR', 'GR-KAV', '????????????'), ('93', 'GR', 'GR-ROD', '????????????'), ('94', 'GR', 'GR-XAN', '??????????'), ('95', 'GR', 'GR-ART', '????????'),
			('96', 'GR', 'GR-IOA', '????????????????'), ('97', 'GR', 'GR-PRE', '??????????????'), ('98', 'GR', 'GR-THS', '??????????????????'), ('99', 'GR', 'GR-KER', '??????????????'),
			('100', 'GR', 'GR-KEF', '????????????????????'), ('101', 'GR', 'GR-LEF', '??????????????'), ('102', 'GR', 'GR-ZAK', '????????????????'), ('103', 'GR', 'GR-CHI', '????????'),
			('104', 'GR', 'GR-LES', '????????????'), ('105', 'GR', 'GR-SAM', '??????????'), ('106', 'GR', 'GR-ARK', '??????????????'), ('107', 'GR', 'GR-ARG', '????????????????'),
			('108', 'GR', 'GR-KOR', '????????????????'), ('109', 'GR', 'GR-LAK', '??????????????'), ('110', 'GR', 'GR-MES', '????????????????'), ('111', 'GR', 'GR-KYK', '????????????????'),
			('112', 'GR', 'GR-DOD', '????????????????????'), ('113', 'GR', 'GR-KAR', '????????????????'), ('114', 'GR', 'GR-LAR', '????????????'), ('115', 'GR', 'GR-MAG', '????????????????'),
			('116', 'GR', 'GR-TRI', '??????????????'), ('117', 'GR', 'GR-ACH', '??????????'), ('118', 'GR', 'GR-AIT', '??????????????????????????????'), ('119', 'GR', 'GR-ILI', '??????????'),
			('120', 'GR', 'GR-FLO', '??????????????'), ('121', 'GR', 'GR-GRE', '??????????????'), ('122', 'GR', 'GR-KAS', '????????????????'), ('123', 'GR', 'GR-KOZ', '????????????'),
			('124', 'GR', 'GR-AGO', '???????? ????????'), ('125', 'DE', 'DE-BW', 'Baden-W??rttemberg'), ('126', 'DE', 'DE-BY', 'Bayern'), ('127', 'DE', 'DE-BE', 'Berlin'),
			('128', 'DE', 'DE-BB', 'Brandenburg'), ('129', 'DE', 'DE-HB', 'Freie Hansestadt Bremen'), ('130', 'DE', 'DE-HH', 'Hamburg'), ('131', 'DE', 'DE-HE', 'Hessen'),
			('132', 'DE', 'DE-MV', 'Mecklenburg-Vorpommern'), ('133', 'DE', 'DE-NI', 'Niedersachsen'), ('134', 'DE', 'DE-NW', 'Nordrhein-Westfalen'),
			('135', 'DE', 'DE-RP', 'Rheinland-Pfalz'), ('136', 'DE', 'DE-SL', 'Saarland'), ('137', 'DE', 'DE-SN', 'Sachsen'), ('138', 'DE', 'DE-ST', 'Sachsen-Anhalt'),
			('139', 'DE', 'DE-SH', 'Schleswig-Holstein'), ('140', 'DE', 'DE-TH', 'Th??ringen'), ('181', 'CN', 'CN-BJ', 'Beijing Municipality'),
			('182', 'CN', 'CN-TJ', 'Tianjin Municipality'), ('183', 'CN', 'CN-HE', 'Hebei Province'), ('184', 'CN', 'CN-SX', 'Shanxi Province'),
			('185', 'CN', 'CN-NM', 'Nei Mongol Autonomous Region'), ('186', 'CN', 'CN-LN', 'Liaoning Province'), ('187', 'CN', 'CN-JL', 'Jilin Province'),
			('188', 'CN', 'CN-HL', 'Heilongjiang Province'), ('189', 'CN', 'CN-SH', 'Shanghai Municipality'), ('190', 'CN', 'CN-JS', 'Jiangsu Province'),
			('191', 'CN', 'CN-ZJ', 'Zhejiang Province'), ('192', 'CN', 'CN-AH', 'Anhui Province'), ('193', 'CN', 'CN-FJ', 'Fujian Province'),
			('194', 'CN', 'CN-JX', 'Jiangxi Province'), ('195', 'CN', 'CN-SD', 'Shandong Province'), ('196', 'CN', 'CN-HA', 'Henan Province'),
			('197', 'CN', 'CN-HB', 'Hubei Province'), ('198', 'CN', 'CN-HN', 'Hunan Province'), ('199', 'CN', 'CN-GD', 'Guangdong Province'),
			('200', 'CN', 'CN-GX', 'Guangxi Zhuang Autonomous Region'), ('201', 'CN', 'CN-HI', 'Hainan Province'), ('202', 'CN', 'CN-CQ', 'Chongqing Municipality'),
			('203', 'CN', 'CN-SC', 'Sichuan Province'), ('204', 'CN', 'CN-GZ', 'Guizhou Province'), ('205', 'CN', 'CN-YN', 'Yunnan Province'),
			('206', 'CN', 'CN-XZ', 'Xizang Autonomous Region'), ('207', 'CN', 'CN-SN', 'Shaanxi Province'), ('208', 'CN', 'CN-GS', 'Gansu Province'),
			('209', 'CN', 'CN-QH', 'Qinghai Province'), ('210', 'CN', 'CN-NX', 'Ningxia Hui Autonomous Region'), ('211', 'CN', 'CN-XJ', 'Xinjiang Uyghur Autonomous Region'),
			('212', 'CN', 'CN-HK', 'Xianggang Special Administrative Region'), ('213', 'CN', 'CN-MC', 'Aomen Special Administrative Region');");
		$db->execute();

	}

	private function _get_default($field)
	{
		$db = JFactory::getDbo();
		$default = ' DEFAULT ' . $db->q($field->default);
		if($field->default === NULL && $field->null == 'YES')
		{
			$default = 'DEFAULT NULL';
		}
		if(in_array(strtoupper($field->type), array("TINYBLOB", "BLOB", "MEDIUMBLOB", "LONGBLOB", "TINYTEXT", "TEXT", "MEDIUMTEXT", "LONGTEXT")))
		{
			$default = NULL;
		}

		return $default;
	}

	private function _updateTables()
	{
		$prefix = JFactory::getApplication()->getCfg('dbprefix');
		$db     = JFactory::getDbo();


		$db->setQuery("SHOW TABLES lIKE '%_emerald_%'");
		$list = $db->loadColumn();

		if(in_array($prefix . 'emerald_country', $list))
		{
			$db->setQuery("DELETE FROM #__emerald_country");
			$db->execute();
		}

		$tables = JFolder::files(JPATH_ROOT . '/administrator/components/com_emerald/db', '\.json$');

		foreach($tables AS $file)
		{
			if(substr($file, 0, 6) == 'h0o6u_')
			{
				continue;
			}
			$table  = $prefix . str_replace('.json', '', $file);
			$source = json_decode(file_get_contents(JPATH_ROOT . '/administrator/components/com_emerald/db/' . $file));


			if(in_array($table, $list))
			{
				$db->setQuery("DESCRIBE `{$table}`");
				$info = $db->loadObjectList('Field');

				$all_fields  = array();
				$real_fields = array_keys($info);

				foreach($source->fields AS $field)
				{
					if($field->name == 'id' && $file != 'emerald_country.json')
					{
						continue;
					}

					$all_fields[] = $field->name;

					$update = sprintf("`%s` %s %s %s COMMENT '%s'",
						$field->name, strtoupper($field->type), ($field->null == 'YES' ? 'NULL' : 'NOT NULL'),
						$this->_get_default($field), @$field->comment
					);

					$sql = NULL;

					if(empty($info[$field->name]))
					{
						$sql = "ALTER TABLE `{$table}` ADD COLUMN " . $update;
					}
					elseif(
						strtolower($info[$field->name]->Type) != strtolower($field->type) ||
						strtolower($info[$field->name]->Null) != strtolower($field->null) ||
						$info[$field->name]->Default != $field->default
					)
					{
						$sql = "ALTER TABLE `{$table}` CHANGE COLUMN `{$field->name}` " . $update;
					}

					if($sql)
					{
						JFactory::getApplication()->enqueueMessage("Successful update: " . $sql);
						$db->setQuery($sql);
						$db->execute();
					}
				}

				if(!empty($source->primary))
				{
					$all_fields[] = $source->primary;
				}

				$diff = array_diff($real_fields, $all_fields);

				if(!empty($diff))
				{
					$sql = sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", $table, implode("`, DROP COLUMN `", $diff));
					$db->setQuery($sql);
					$db->execute();
				}

				$db->setQuery("SHOW INDEXES FROM `{$table}`");
				$keys = $db->loadObjectList('Key_name');

				$all_keys  = array();
				$real_keys = array_keys($keys);

				if(!empty($source->keys))
				{
					foreach($keys AS $key_name => $key)
					{
						$all_keys[] = $key_name;

						if(empty($keys[$key_name]))
						{
							$array = explode(",", $key->fields);

							$sql = sprintf("ALTER TABLE `%s` ADD INDEX `%s` (%s ASC)",
								$table, $key_name, implode(" ASC, ", $array)
							);
							$db->setQuery($sql);
							$db->execute();
						}
					}
				}

				if(!empty($source->primary))
				{
					$all_fields[] = 'PRIMARY';
				}

				$diff = array_diff($real_keys, $all_keys);

				if(!empty($diff))
				{
					$sql = sprintf("ALTER TABLE `%s` DROP INDEX %s", $table, implode(", DROP INDEX ", $diff));
					$db->setQuery($sql);
					//$db->execute();
				}

				$db->setQuery("SHOW TABLE STATUS WHERE Name = '{$table}'");
				$engine = $db->loadObject()->Engine;

				if($engine != $source->engine)
				{
					$db->setQuery("ALTER TABLE `{$table}` ENGINE = {$source->engine}");
					$db->execute();
				}
			}
			else
			{

				$sql = array();

				if(!empty($source->primary))
				{
					$sql[] = "`{$source->primary}` int(10) unsigned NOT NULL AUTO_INCREMENT";
				}
				foreach($source->fields AS $field)
				{
					if($source->primary == $field->name)
					{
						continue;
					}
					$sql[] = sprintf("`%s` %s %s %s COMMENT '%s'",
						$field->name, strtoupper($field->type), ($field->null == 'YES' ? 'NULL' : 'NOT NULL'),
						$this->_get_default($field), @$field->comment
					);
				}

				if(!empty($source->primary))
				{
					$sql[] = "PRIMARY KEY (`{$source->primary}`)";
				}

				if(!empty($source->keys))
				{
					foreach($source->keys AS $key_name => $key)
					{
						$sql[] = sprintf("%s `%s` (%s)",
							strtoupper($key->type), $key_name, $key->fields
						);
					}
				}

				$query = sprintf("CREATE TABLE IF NOT EXISTS `%s` (%s) ENGINE=%s  DEFAULT CHARSET=utf8;",
					$table, implode(",  ", $sql), $source->engine
				);

				$db->setQuery($query);
				$db->execute();
			}

			$db->setQuery("OPTIMIZE TABLE {$table}");
			$db->execute();
		}

		$db->setQuery("DELETE FROM `#__emerald_plans_actions` WHERE plan_id NOT IN (SELECT id FROM #__emerald_plans)");
		$db->execute();

		$db->setQuery("DELETE FROM `#__emerald_plans_rules` WHERE plan_id NOT IN (SELECT id FROM #__emerald_plans)");
		$db->execute();

		$db->setQuery("UPDATE `#__emerald_subscriptions` SET purchased = created WHERE purchased = '0000-00-00 00:00:00'");
		$db->execute();

		$db->setQuery("UPDATE `#__emerald_subscriptions` SET `fields` = '[]' WHERE `fields` = '' OR `fields` IS NULL");
		$db->execute();

		$db->setQuery("UPDATE `#__emerald_plans_rules` SET `controller` = `option` WHERE `controller` IS NULL");
		$db->execute();

		$db->setQuery("UPDATE #__emerald_plans SET mtime = ctime WHERE mtime IS NULL");
		$db->execute();

		$db->setQuery("INSERT INTO `#__emerald_country` VALUES ('AD', 'Andorra'), ('AE', 'United Arab Emirates'), ('AF', 'Afghanistan'),
			('AG', 'Antigua and Barbuda'), ('AI', 'Anguilla'), ('AL', 'Albania'), ('AM', 'Armenia'), ('AN', 'Netherlands Antilles'),
			('AO', 'Angola'), ('AQ', 'Antarctica'), ('AR', 'Argentina'), ('AS', 'American Samoa'), ('AT', 'Austria'), ('AU', 'Australia'),
			('AW', 'Aruba'), ('AX', '??land Islands'), ('AZ', 'Azerbaijan'), ('BA', 'Bosnia and Herzegovina'), ('BB', 'Barbados'),
			('BD', 'Bangladesh'), ('BE', 'Belgium'), ('BF', 'Burkina Faso'), ('BG', 'Bulgaria'), ('BH', 'Bahrain'), ('BI', 'Burundi'),
			('BJ', 'Benin'), ('BL', 'Saint Barth??lemy'), ('BM', 'Bermuda'), ('BN', 'Brunei'), ('BO', 'Bolivia'), ('BQ', 'British Antarctic Territory'),
			('BR', 'Brazil'), ('BS', 'Bahamas'), ('BT', 'Bhutan'), ('BV', 'Bouvet Island'), ('BW', 'Botswana'), ('BY', 'Belarus'),
			('BZ', 'Belize'), ('CA', 'Canada'), ('CC', 'Cocos [Keeling] Islands'), ('CD', 'Congo - Kinshasa'), ('CF', 'Central African Republic'),
			('CG', 'Congo - Brazzaville'), ('CH', 'Switzerland'), ('CI', 'C??te d???Ivoire'), ('CK', 'Cook Islands'), ('CL', 'Chile'),
			('CM', 'Cameroon'), ('CN', 'China'), ('CO', 'Colombia'), ('CR', 'Costa Rica'), ('CS', 'Serbia and Montenegro'),
			('CT', 'Canton and Enderbury Islands'), ('CU', 'Cuba'), ('CV', 'Cape Verde'), ('CX', 'Christmas Island'), ('CY', 'Cyprus'),
			('CZ', 'Czech Republic'), ('DD', 'East Germany'), ('DE', 'Germany'), ('DJ', 'Djibouti'), ('DK', 'Denmark'), ('DM', 'Dominica'),
			('DO', 'Dominican Republic'), ('DZ', 'Algeria'), ('EC', 'Ecuador'), ('EE', 'Estonia'), ('EG', 'Egypt'), ('EH', 'Western Sahara'),
			('ER', 'Eritrea'), ('ES', 'Spain'), ('ET', 'Ethiopia'), ('FI', 'Finland'), ('FJ', 'Fiji'), ('FK', 'Falkland Islands'),
			('FM', 'Micronesia'), ('FO', 'Faroe Islands'), ('FQ', 'French Southern and Antarctic Territories'), ('FR', 'France'),
			('FX', 'Metropolitan France'), ('GA', 'Gabon'), ('GB', 'United Kingdom'), ('GD', 'Grenada'), ('GE', 'Georgia'),
			('GF', 'French Guiana'), ('GG', 'Guernsey'), ('GH', 'Ghana'), ('GI', 'Gibraltar'), ('GL', 'Greenland'), ('GM', 'Gambia'),
			('GN', 'Guinea'), ('GP', 'Guadeloupe'), ('GQ', 'Equatorial Guinea'), ('GR', 'Greece'), ('GS', 'South Georgia and the South Sandwich Islands'),
			('GT', 'Guatemala'), ('GU', 'Guam'), ('GW', 'Guinea-Bissau'), ('GY', 'Guyana'), ('HK', 'Hong Kong SAR China'),
			('HM', 'Heard Island and McDonald Islands'), ('HN', 'Honduras'), ('HR', 'Croatia'), ('HT', 'Haiti'), ('HU', 'Hungary'),
			('ID', 'Indonesia'), ('IE', 'Ireland'), ('IL', 'Israel'), ('IM', 'Isle of Man'), ('IN', 'India'), ('IO', 'British Indian Ocean Territory'),
			('IQ', 'Iraq'), ('IR', 'Iran'), ('IS', 'Iceland'), ('IT', 'Italy'), ('JE', 'Jersey'), ('JM', 'Jamaica'), ('JO', 'Jordan'),
			('JP', 'Japan'), ('JT', 'Johnston Island'), ('KE', 'Kenya'), ('KG', 'Kyrgyzstan'), ('KH', 'Cambodia'), ('KI', 'Kiribati'),
			('KM', 'Comoros'), ('KN', 'Saint Kitts and Nevis'), ('KP', 'North Korea'), ('KR', 'South Korea'), ('KW', 'Kuwait'),
			('KY', 'Cayman Islands'), ('KZ', 'Kazakhstan'), ('LA', 'Laos'), ('LB', 'Lebanon'), ('LC', 'Saint Lucia'), ('LI', 'Liechtenstein'),
			('LK', 'Sri Lanka'), ('LR', 'Liberia'), ('LS', 'Lesotho'), ('LT', 'Lithuania'), ('LU', 'Luxembourg'), ('LV', 'Latvia'),
			('LY', 'Libya'), ('MA', 'Morocco'), ('MC', 'Monaco'), ('MD', 'Moldova'), ('ME', 'Montenegro'), ('MF', 'Saint Martin'),
			('MG', 'Madagascar'), ('MH', 'Marshall Islands'), ('MI', 'Midway Islands'), ('MK', 'Macedonia'), ('ML', 'Mali'),
			('MM', 'Myanmar [Burma]'), ('MN', 'Mongolia'), ('MO', 'Macau SAR China'), ('MP', 'Northern Mariana Islands'),
			('MQ', 'Martinique'), ('MR', 'Mauritania'), ('MS', 'Montserrat'), ('MT', 'Malta'), ('MU', 'Mauritius'), ('MV', 'Maldives'),
			('MW', 'Malawi'), ('MX', 'Mexico'), ('MY', 'Malaysia'), ('MZ', 'Mozambique'), ('NA', 'Namibia'), ('NC', 'New Caledonia'),
			('NE', 'Niger'), ('NF', 'Norfolk Island'), ('NG', 'Nigeria'), ('NI', 'Nicaragua'), ('NL', 'Netherlands'), ('NO', 'Norway'),
			('NP', 'Nepal'), ('NQ', 'Dronning Maud Land'), ('NR', 'Nauru'), ('NT', 'Neutral Zone'), ('NU', 'Niue'), ('NZ', 'New Zealand'),
			('OM', 'Oman'), ('PA', 'Panama'), ('PC', 'Pacific Islands Trust Territory'), ('PE', 'Peru'), ('PF', 'French Polynesia'),
			('PG', 'Papua New Guinea'), ('PH', 'Philippines'), ('PK', 'Pakistan'), ('PL', 'Poland'), ('PM', 'Saint Pierre and Miquelon'),
			('PN', 'Pitcairn Islands'), ('PR', 'Puerto Rico'), ('PS', 'Palestinian Territories'), ('PT', 'Portugal'),
			('PU', 'U.S. Miscellaneous Pacific Islands'), ('PW', 'Palau'), ('PY', 'Paraguay'), ('PZ', 'Panama Canal Zone'), ('QA', 'Qatar'),
			('RE', 'R??union'), ('RO', 'Romania'), ('RS', 'Serbia'), ('RU', 'Russia'), ('RW', 'Rwanda'), ('SA', 'Saudi Arabia'),
			('SB', 'Solomon Islands'), ('SC', 'Seychelles'), ('SD', 'Sudan'), ('SE', 'Sweden'), ('SG', 'Singapore'), ('SH', 'Saint Helena'),
			('SI', 'Slovenia'), ('SJ', 'Svalbard and Jan Mayen'), ('SK', 'Slovakia'), ('SL', 'Sierra Leone'), ('SM', 'San Marino'),
			('SN', 'Senegal'), ('SO', 'Somalia'), ('SR', 'Suriname'), ('ST', 'S??o Tom?? and Pr??ncipe'), ('SU', 'Union of Soviet Socialist Republics'),
			('SV', 'El Salvador'), ('SY', 'Syria'), ('SZ', 'Swaziland'), ('TC', 'Turks and Caicos Islands'), ('TD', 'Chad'),
			('TF', 'French Southern Territories'), ('TG', 'Togo'), ('TH', 'Thailand'), ('TJ', 'Tajikistan'), ('TK', 'Tokelau'), ('TL', 'Timor-Leste'),
			('TM', 'Turkmenistan'), ('TN', 'Tunisia'), ('TO', 'Tonga'), ('TR', 'T??rkiye'), ('TT', 'Trinidad and Tobago'), ('TV', 'Tuvalu'),
			('TW', 'Taiwan'), ('TZ', 'Tanzania'), ('UA', 'Ukraine'), ('UG', 'Uganda'), ('UM', 'U.S. Minor Outlying Islands'), ('US', 'United States'),
			('UY', 'Uruguay'), ('UZ', 'Uzbekistan'), ('VA', 'Vatican City'), ('VC', 'Saint Vincent and the Grenadines'), ('VD', 'North Vietnam'),
			('VE', 'Venezuela'), ('VG', 'British Virgin Islands'), ('VI', 'U.S. Virgin Islands'), ('VN', 'Vietnam'), ('VU', 'Vanuatu'),
			('WF', 'Wallis and Futuna'), ('WK', 'Wake Island'), ('WS', 'Samoa'), ('YD', 'People`s Democratic Republic of Yemen'),
			('YE', 'Yemen'), ('YT', 'Mayotte'), ('ZA', 'South Africa'), ('ZM', 'Zambia'), ('ZW', 'Zimbabwe'), ('ZZ', 'Unknown or Invalid Region');");
		$db->execute();

	}

	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}

	function postflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}
}
