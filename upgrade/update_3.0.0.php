<?php
/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

if (!$installation) {
    exit();
}

Configuration::deleteByName('LENGOW_ID_CUSTOMER');
Configuration::deleteByName('LENGOW_ID_GROUP');
Configuration::deleteByName('LENGOW_TOKEN');
Configuration::deleteByName('LENGOW_SWITCH_V3');
Configuration::deleteByName('LENGOW_IMAGE_TYPE');
Configuration::deleteByName('LENGOW_FEED_MANAGEMENT');
Configuration::deleteByName('LENGOW_FORCE_PRICE');

// alter log import table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_product\'')) {
    if (!$this->_checkFieldExists('lengow_product', 'id')) {
        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'lengow_product DROP PRIMARY KEY');
        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'lengow_product ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
}

//LENGOW_DEBUG => LENGOW_IMPORT_PREPROD_ENABLED
//LENGOW_CRON => LENGOW_CRON_ENABLED
//LENGOW_IMPORT_SHIPPED_BY_MP => LENGOW_IMPORT_SHIPPED_BY_MP_ENABLED
//LENGOW_MP_SHIPPING_METHOD => LENGOW_IMPORT_CARRIER_MP_ENABLED
//LENGOW_REPORT_MAIL => LENGOW_REPORT_MAIL_ENABLED
//LENGOW_EMAIL_ADDRESS => LENGOW_REPORT_MAIL_ADDRESS
//LENGOW_IMPORT_SINGLE => LENGOW_IMPORT_SINGLE_ENABLED
//LENGOW_IS_IMPORT => LENGOW_IMPORT_IN_PROGRESS
//LENGOW_TRACKING => LENGOW_TRACKING_ENABLED

$configurations = array(
    'LENGOW_LOGO_URL',
    'LENGOW_EXPORT_NEW',
    'LENGOW_EXPORT_FIELDS',
    'LENGOW_EXPORT_FULLNAME',
    'LENGOW_IMAGES_COUNT',
    'LENGOW_IMPORT_METHOD_NAME',
    'LENGOW_EXPORT_FEATURES',
    'LENGOW_FLOW_DATA',
    'LENGOW_CRON_EDITOR',
    'LENGOW_EXPORT_SELECTION',
    'LENGOW_EXPORT_ALL_VARIATIONS',
    'LENGOW_EXPORT_TIMEOUT',
);
foreach ($configurations as $configuration) {
    Configuration::deleteByName($configuration);
}
