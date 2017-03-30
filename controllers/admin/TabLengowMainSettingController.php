<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Admin Lengow Main Setting 14 Class
 */
class AdminLengowMainSetting14 extends AdminTab
{
    /**
     * Construct
     */
    public function __construct()
    {
        $lengowController = new LengowMainSettingController();
        $lengowController->postProcess();

        parent::__construct();
        
        $lengowController->display();
    }

    /**
     * Display data page
     */
    public function display()
    {
    }
}
