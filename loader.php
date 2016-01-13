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


error_reporting(E_ALL);
ini_set("display_errors", 1);
define('_PS_MODULE_LENGOW_DIR_', _PS_MODULE_DIR_.'lengow'.$sep);

$notInPresta14 = array('lengow.specificprice.class.php', 'lengow.gender.class.php');
$GLOBALS['OVERRIDE_FOLDER'] = 'override';
$GLOBALS['INSTALL_FOLDER'] = 'install';
$GLOBALS['MODELS_FOLDER'] = 'models';
$GLOBALS['FILES'] = array();

if (_PS_VERSION_ < '1.5') {
    require_once _PS_MODULE_LENGOW_DIR_.'backward_compatibility'.$sep.'backward.php';
}

$directory = _PS_MODULE_LENGOW_DIR_ . 'interface/';
$listClassFile = array_diff(scandir($directory), array('..', '.'));

foreach ($listClassFile as $list) {
    require_once $directory . $list;
}

if (_PS_VERSION_ < '1.5') {
    $directory = _PS_MODULE_LENGOW_DIR_ . 'models/';
    $listClassFile = array_diff(scandir($directory), array('..', '.'));

    foreach ($listClassFile as $list) {
        if(in_array($list, $notInPresta14) && _PS_VERSION_ < '1.5'){
            continue;
        }
        require_once $directory . $list;
    }
} else {
    spl_autoload_register('lengowAutoloader');
}

function lengowAutoloader($class){
    $directory = _PS_MODULE_LENGOW_DIR_ . 'models/';
    if (substr($class, 0, 6) == 'Lengow') {
        include $directory.str_replace('lengow', 'lengow.', strtolower($class)).'.class.php';
    }
}
