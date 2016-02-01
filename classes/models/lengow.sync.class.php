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

class LengowSync extends SpecificPrice
{
    public function __construct()
    {

    }

    public static function getSyncData()
    {
        $data = array();
        $data['domain_name'] = $_SERVER["SERVER_NAME"];
        $data['global_token'] = LengowMain::getToken();
        $data['email'] = LengowConfiguration::get('PS_SHOP_EMAIL');

        if (_PS_VERSION_ < '1.5') {
            $results = array(array('id_shop' => 1));
        } else {
            if ($currentShop = Shop::getContextShopID()) {
                $results = array(array('id_shop' => $currentShop));
            } else {
                $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'shop WHERE active = 1';
                $results = Db::getInstance()->ExecuteS($sql);
            }
        }
        foreach ($results as $row) {
            $shopId = $row['id_shop'];

            $lengowExport = new LengowExport(array("shop_id" => $shopId));
            $shop = new LengowShop($shopId);
            $data['shops'][$row['id_shop']]['token'] = LengowMain::getToken($shopId);
            $data['shops'][$row['id_shop']]['name'] = $shop->name;
            $data['shops'][$row['id_shop']]['domain'] = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url'] = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['import_url'] = LengowMain::getImportUrl($shop->id);
            $data['shops'][$row['id_shop']]['nb_product_total'] = $lengowExport->getTotalProduct();
            $data['shops'][$row['id_shop']]['nb_product_exported'] = $lengowExport->getTotalExportProduct();
        }
        return $data;
    }

    public static function sync($params)
    {
        foreach ($params as $shop_token => $values) {
            if ($shop = LengowShop::findByToken($shop_token)) {
                foreach ($values as $k => $v) {
                    if (!in_array($k, array('account_id', 'access_token', 'secret_token'))) {
                        continue;
                    }
                    LengowConfiguration::updateValue('LENGOW_'.Tools::strtoupper($k), $v, false, null, $shop->id);
                }
            }
        }
    }
}
