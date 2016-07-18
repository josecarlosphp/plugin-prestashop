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

/**
 * Lengow List Class
 */
class LengowList
{
    /**
    * All params for lengow grid
    */
    protected $fields_list;
    protected $collection;
    protected $total;
    protected $identifier;
    protected $selection;
    protected $selectionCondition;
    protected $controller;
    protected $shopId;
    protected $currentPage;
    protected $nbPerPage;
    protected $nbMaxPage;
    protected $sql;
    protected $id;
    protected $ajax;
    protected $context;
    protected $locale;
    protected $toolbox;

    /**
     * Construct
     */
    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->fields_list = $params['fields_list'];
        $this->identifier = $params['identifier'];
        $this->selection = $params['selection'];
        $this->selectionCondition = isset($params['selection_condition']) ? $params['selection_condition'] : false ;
        $this->controller = $params['controller'];
        $this->shopId = isset($params['shop_id']) ? $params['shop_id'] : null;
        $this->currentPage = isset($params['current_page']) ? $params['current_page'] : 1;
        $this->nbPerPage = isset($params['nbPerPage']) ? $params['nbPerPage'] : 20;
        $this->sql = $params['sql'];
        $this->ajax = isset($params['ajax']) ? (bool)$params['ajax'] : false;
        $this->orderValue = isset($params['order_value']) ? $params['order_value'] : '';
        $this->orderColumn = isset($params['order_column']) ? $params['order_column'] : '';
        $this->toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $this->locale = new LengowTranslation();
        $this->context = Context::getContext();
        if (_PS_VERSION_ < 1.5) {
            $this->context->smarty->ps_language = $this->context->language;
        }
    }

    /**
     * Display Table Header
     *
     * @return string
     */
    public function displayHeader($order)
    {
        $tableClass = (count($this->collection) == 0 ? 'table_no_result' : '');
        $newOrder = ( empty($this->orderValue) || $this->orderValue == "ASC" ) ? 'DESC' : "ASC";
        $html ='<table class="lengow_table table table-bordered table-striped table-hover '.$tableClass.'"
            id="table_'.$this->id.'">';
        $html.='<thead>';
        $html.='<tr>';

        if ($this->selection && !$this->toolbox) {
            $html.='<th></th>';
        }
        foreach ($this->fields_list as $key => $values) {
            $orderClass = '';
            if (isset($values['filter_key']) && $order == $values['filter_key']) {
                $orderClass = 'order';
            }
            $html.='<th>';
            if (isset($values['filter_order']) && $values['filter_order']) {
                $html.='<a href="#" class="table_order '.$orderClass.'" data-order="'.$newOrder.'"
                    data-column="'.$values['filter_key'].'">'.$values['title'].'</a>';
            } else {
                $html.=$values['title'];
            }
            $html.='</th>';
        }
        $html.='</tr>';

        $html.='<tr class="lengow_filter">';
        if ($this->selection && !$this->toolbox) {
            $html.='<th><input type="checkbox" id="select_'.$this->id.'"
                class="lengow_select_all lengow_link_tooltip"/></th>';
        }
        foreach ($this->fields_list as $key => $values) {
            $html.= '<th>';
            if (isset($values['filter']) && $values['filter']) {
                $type = isset($values['filter_type']) ? $values['filter_type'] : 'text';
                $name = 'table_'.$this->id.'[' . $key . ']';
                if (isset($_REQUEST['table_'.$this->id][$key])) {
                    $value = $_REQUEST['table_'.$this->id][$key];
                } else {
                    $value = '';
                }
                switch ($type) {
                    case 'text':
                        $html.= '<input type="text" class="focus_'.$key.'" name="'.$name.'" value="'.$value.'" />';
                        break;
                    case 'select':
                        $html.='<select class="form-control" name="'.$name.'">';
                        $html.='<option value="" '.($value ? 'selected' : '').'></option>';
                        foreach ($values['filter_collection'] as $row) {
                            $selected =  $row['id'] == $value ? 'selected' : '';
                            $html.='<option value="'.$row['id'].'" '.$selected.'>'.$row['text'].'</option>';
                        }
                        $html.= '</select>';
                        break;
                    case 'date':
                        $from = isset($value['from']) ? $value['from'] : null;
                        $to = isset($value['to']) ? $value['to'] : null;
                        $html.= '<div class="lengow_datepicker_box"><input type="text" name="'.$name.'[from]"
                            placeholder="'.$this->locale->t('product.screen.date_from').'"
                            value="'.$from.'" class="lengow_datepicker" />';
                        $html.= '<input type="text" name="'.$name.'[to]"
                        placeholder="'.$this->locale->t('product.screen.date_to').'"
                        value="'.$to.'" class="lengow_datepicker" /></div>';
                        break;
                }
            } elseif (isset($values['button_search']) && $values['button_search']) {
                $html.= '<input type="submit" value="'.$this->locale->t('product.screen.button_search').'"
                    class="lgw-btn lgw-btn-white">';
            }
            $html.= '</th>';
        }
        $html.= '</tr>';
        $html.= '</thead>';
        return $html;
    }

    /**
     * Display Table Content
     *
     * @return string
     */
    public function displayContent()
    {
        $html = '<tbody>';
        if (count($this->collection) == 0) {
            $html.= '<tr><td colspan="100%" align="center"><div id="lengow_no_result_message">
                <span class="img_no_result"></span>
                <h2 class="title_no_result">'.$this->locale->t('product.screen.no_result_found').'</h2>
                </div></td></tr>';
        } else {
            foreach ($this->collection as $item) {
                $html.= $this->displayRow($item);
            }
        }
        $html.= '</tbody>';
        return $html;
    }

    /**
     * Display Table Row
     *
     * @param string $item
     *
     * @return string
     */
    public function displayRow($item)
    {
        $lengow_link = new LengowLink();
        $html = '';
        $html.= '<tr id='.$this->id.'_'.$item[$this->identifier].' class="table_row">';/*ici*/
        if ($this->selection && !$this->toolbox) {
            if ($this->selectionCondition) {
                if ($item[$this->selectionCondition] > 0) {
                    $html.='<td class="no-link"> <input type="checkbox" class="lengow_selection"
                    name="selection['.$item[$this->identifier].']" value="1"></td>';
                } else {
                    $html.='<td></td>';
                }
            } else {
                $html.='<td class="no-link"><input type="checkbox" class="lengow_selection"
                    name="selection['.$item[$this->identifier].']" value="1"></td>';
            }
        }
        foreach ($this->fields_list as $key => $values) {
            if (isset($values['display_callback'])) {
                $value = call_user_func_array($values['display_callback'], array($key, $item[$key], $item));
            } else {
                if (isset($values['type'])) {
                    switch ($values["type"]) {
                        case 'date':
                            $value = Tools::dateFormat(
                                array('date' => $item[$key], 'full' => true),
                                $this->context->smarty
                            );
                            break;
                        case 'price':
                            if (isset($item['currency'])) {
                                $value = Tools::displayPrice($item[$key], $this->getCurrencyByCode($item['currency']));
                            } else {
                                $value = Tools::displayPrice($item[$key]);
                            }
                            break;
                        case 'switch_product':
                            $status = $this->toolbox ? 'disabled' : '';
                            $value = '<div class="lgw-switch '.($item[$key] ? 'checked' : '')
                                .'"><label><div><span></span><input type="checkbox"
                                data-size="mini"
                                class="lengow_switch_product"
                                data-on-text="'.$this->locale->t('product.screen.button_yes').'"
                                data-off-text="'.$this->locale->t('product.screen.button_no').'"
                                name="lengow_product_selection['.$item[$this->identifier].']"
                                lengow_product_selection_'.$item[$this->identifier].'"
                                data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'"
                                data-action="select_product"
                                data-id_shop="'.$this->shopId.'"
                                data-id_product="'.$item[$this->identifier].'" ' .
                                $status . ' ' .
                                'value="1" '.($item[$key] ? 'checked="checked"' : '').'/></div></label></div>';
                            break;
                        case 'flag_country':
                            if ($item[$key]) {
                                $isoCode = Tools::strtoupper($item[$key]);
                                $value = '<img src="/modules/lengow/views/img/flag/'.$isoCode.'.png"
                                class="lengow_link_tooltip"
                                data-original-title="'.LengowCountry::getNameByIso($isoCode).'"/>';
                            } else {
                                $value = '';
                            }
                            break;
                        default:
                            $value = $item[$key];
                    }
                } else {
                    $value = $item[$key];
                }
            }
            $class = isset($values['class']) ? $values['class'] : '';
            $html.= '<td class="'.$class.'">'.$value.'</td>';
        }
        $html.= '</tr>';
        return $html;
    }

    /**
     * Display Table Footer
     *
     * @return string
     */
    public function displayFooter()
    {
        $html = '</table>';
        return $html;
    }

    /**
     * Display Table (Header + Content + Footer)
     *
     * @return string
     */
    public function display()
    {
        $lengow_link = new LengowLink();
        $html = '<form id="form_table_'.$this->id.'" class="lengow_form_table"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'">';
        $html.= '<input type="hidden" name="p" value="'.$this->currentPage.'" />';
        $html.= '<input type="hidden" name="order_value" value="'.$this->orderValue.'" />';
        $html.= '<input type="hidden" name="order_column" value="'.$this->orderColumn.'" />';
        $html.= $this->displayHeader($this->orderColumn).$this->displayContent().$this->displayFooter();
        $html.= '<input type="submit" value="Search" style="visibility: hidden"/>';
        $html.= '</form>';
        return $html;
    }

    /**
     * Execute Queries (Collection + Total)
     *
     * @return mixed
     */
    public function executeQuery()
    {
        $sql = $this->buildQuery();
        $sqlTotal = $this->buildQuery(true);
        $this->collection = Db::getInstance()->executeS($sql, true, false);
        if (isset($this->sql['select_having']) && $this->sql['select_having']) {
            Db::getInstance()->executeS($sqlTotal);
            $this->total = Db::getInstance()->NumRows();
        } else {
            $this->total = Db::getInstance()->getValue($sqlTotal, false);
        }
        $this->nbMaxPage = ceil($this->total / $this->nbPerPage);
        $this->paginationFrom = ($this->currentPage-1) * $this->nbPerPage + 1;
        if ($this->total == 0) {
            $this->paginationFrom = 0;
        }
        $this->paginationTo = $this->paginationFrom + $this->nbPerPage - 1;
        if ($this->currentPage >= $this->nbMaxPage) {
            $this->paginationTo = $this->total;
        }
        if ($this->nbMaxPage > 0 && $this->currentPage > $this->nbMaxPage) {
            $this->currentPage = $this->nbMaxPage;
            return $this->executeQuery();
        }
        return $this->collection;
    }

    /**
     * Get Row
     *
     * @param string $where
     *
     * @return array
     */
    public function getRow($where)
    {
        if (!isset($this->sql["where"])) {
            $this->sql["where"] = array();
        }
        $tmp = $this->sql["where"];
        $this->sql["where"][] = $where;
        $sql = $this->buildQuery();
        $collection = Db::getInstance()->executeS($sql, true, false);
        $this->sql["where"] = $tmp;
        return $collection[0];
    }

    /**
     * Find value by key in fieldlist
     *
     * @param $keyToSeach key search in field list
     *
     * @return boolean
     */
    public function findValueByKey($keyToSeach)
    {
        foreach ($this->fields_list as $key => $value) {
            if ($keyToSeach == $key) {
                return $value;
            }
        }
        return false;
    }

    /**
     * Build Query
     *
     * @param bool $total      Execute Total Query
     * @param bool $select_all
     *
     * @return string sql query
     */
    public function buildQuery($total = false, $select_all = false)
    {
        $where = isset($this->sql["where"]) ? $this->sql["where"] : array();
        $having = array();
        if (isset($_REQUEST['table_'.$this->id])) {
            foreach ($_REQUEST['table_'.$this->id] as $key => $value) {
                if ($fieldValue = $this->findValueByKey($key)) {
                    $type = isset($fieldValue['type']) ? $fieldValue['type'] : 'text';
                    switch ($type) {
                        case 'log_status':
                            if (Tools::strlen($value)>0) {
                                switch ($value) {
                                    case 1:
                                        $having[] = ' '.pSQL($fieldValue['filter_key']).' IS NULL';
                                        break;
                                    case 2:
                                        $having[] = ' '.pSQL($fieldValue['filter_key']).' IS NOT NULL';
                                        break;
                                }
                            }
                            break;
                        case 'select':
                        case 'text':
                            if (Tools::strlen($value)>0) {
                                $where[] = ' '.pSQL($fieldValue['filter_key']).' LIKE "%'.pSQL($value).'%"';
                            }
                            break;
                        case 'date':
                            $from = isset($value['from']) ? $value['from'] : null;
                            $to = isset($value['to']) ? $value['to'] : null;
                            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $from) &&
                                preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $to) ) {

                                $from = DateTime::createFromFormat('d/m/Y', $from);
                                $from = $from->format('Y-m-d');
                                $to = DateTime::createFromFormat('d/m/Y', $to);
                                $to = $to->format('Y-m-d');

                                $where[] = ' '.pSQL($fieldValue['filter_key']).'
                                BETWEEN "'.$from.' 00:00:00" AND "'.$to.' 23:59:59"';
                            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $from)) {
                                $from = DateTime::createFromFormat('d/m/Y', $from);
                                $from = $from->format('Y-m-d');
                                $where[] = ' '.pSQL($fieldValue['filter_key']).' >= "'.$from.' 00:00:00"';
                            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $to)) {
                                $to = DateTime::createFromFormat('d/m/Y', $to);
                                $to = $to->format('Y-m-d');
                                $where[] = ' '.pSQL($fieldValue['filter_key']).' <= "'.$to.' 23:59:59"';
                            }
                            break;
                    }
                }
            }
        }
        if ($total) {
            $value = $this->findValueByKey($this->identifier);
            $first_column = $value['filter_key'];
            if (isset($this->sql['select_having']) && $this->sql['select_having']) {
                $sql = 'SELECT "'.pSQL($first_column).'" ';
                $sql.= ', '.join(',', $this->sql['select_having']);
            } else {
                $sql = 'SELECT COUNT("'.pSQL($first_column).'") as total';
            }

        } elseif ($select_all == true) {
            $sql = 'SELECT '.$this->fields_list['id_product']['filter_key'];
        } else {
            $sql = 'SELECT '.join(', ', $this->sql["select"]);
        }
        if (isset($this->sql['select_having']) && $this->sql['select_having']) {
            $sql.= ', '.join(',', $this->sql['select_having']);
        }
        $sql.= ' '.$this->sql["from"].' ';
        if ($this->sql["join"]) {
            $sql.= join(' ', $this->sql["join"]);
        }
        if ($where) {
            $sql .= ' WHERE '.join(' AND ', $where);
        }
        if ($having) {
            $sql .= ' HAVING '.join(' AND ', $having);
        }
        if (!$total && !$select_all) {
            if (Tools::strlen($this->orderColumn) > 0 && in_array($this->orderValue, array("ASC","DESC"))) {
                $sql .= ' ORDER BY '.pSQL($this->orderColumn).' '.$this->orderValue;
                if (isset($this->sql["order"])) {
                    $sql .= ', '.$this->sql["order"];
                }
            } else {
                if (isset($this->sql["order"])) {
                    $sql .= ' ORDER BY '.$this->sql["order"];
                }
            }
            if ($this->currentPage < 1) {
                $this->currentPage = 1;
            }
            $sql.= ' LIMIT '.($this->currentPage-1)* $this->nbPerPage.','.$this->nbPerPage;
        }
        return $sql;
    }

    /**
     * Update collection
     *
     * @param array $collection
     */
    public function updateCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Render pagination
     *
     * @param array $params
     *
     * @return string
     */
    public function renderPagination($params = array())
    {
        $nav_class = isset($params["nav_class"]) ? $params["nav_class"] : '';
        $lengow_link = new LengowLink();
        $totalPage = ceil($this->total / $this->nbPerPage);
        $html = '<nav id="nav_'.$this->id.'" class="'.$nav_class.'">';
        $html.= '<div class="lgw-pagination-pages">';
        $html.= '<span class="lengow_number">'.$this->paginationFrom.'</span> -
        <span class="lengow_number">'.$this->paginationTo.'</span>
         '.$this->locale->t('product.table.pagination_of').' <span class="lengow_number">'.$this->total.'</span>';
        $html.='</div>';
        if ($totalPage<=1) {
            return $html.'</nav>';
        }
        $html.= '<ul class="lgw-pagination-btns lgw-pagination-arrow">';
        $class = ($this->currentPage == 1) ? 'disabled' : '';
        $html.= '<li class="'.$class.'"><a href="#" data-page="'.($this->currentPage-1).'"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->currentPage-1).'"
        ><i class="fa fa-angle-left"></i></a></li>';
        $class = ($this->currentPage == $this->nbMaxPage) ? 'disabled' : '';
        $html.= '<li class="'.$class.'"><a href="#" data-page="'.($this->currentPage+1).'"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->currentPage+1).'"
        ><i class="fa fa-angle-right"></i></a></li>';
        $html.= '</ul>';
        $html.= '<ul class="lgw-pagination-btns lgw-pagination-numbers">';
        if ($this->nbMaxPage > 7) {
            $showLastSeparation = false;
            $class = ($this->currentPage == 1) ? 'disabled' : '';
            $html.= '<li class="'.$class.'"><a href="#" data-page="1"
            data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p=1">1</a></li>';
            $from = $this->currentPage - 1;
            $to = $this->currentPage + 1;
            if ($from <= 2) {
                $from = 2;
                $to = $from + 3;
            } else {
                $html.= '<li><a href="#" class="disable">...</a></li>';
            }
            if ($to > ($this->nbMaxPage-1)) {
                $to = $this->nbMaxPage - 1;
            } else {
                if ($this->currentPage < ($this->nbMaxPage-2)) {
                    $showLastSeparation = true;
                }
            }
            for ($i = $from; $i <= $to; $i++) {
                $html.= '<li>';
                $class = ($i == $this->currentPage) ? 'disabled' : '';
                $html.= '<li class="' . $class . '"><a href="#" data-page="'.$i.'"
                data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.$i.'">'.
                    $i.'</a></li>';
                $html.= '</li>';
            }
            if ($showLastSeparation) {
                $html .= '<li class="disabled"><a href="#">...</a></li>';
            }
            $class = ($this->currentPage == $this->nbMaxPage) ? 'disabled' : '';
            $html.= '<li class="' . $class . '"><a href="#" data-page="'.$this->nbMaxPage.'"
            data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->nbMaxPage).'"
            >'.$this->nbMaxPage.'</a></li>';
        } else {
            for ($i = 1; $i <= $totalPage; $i++) {
                $class = ($i == $this->currentPage) ? 'disabled' : '';
                $html .= '<li class="' . $class . '"><a href="#"  data-page="'.$i.'"
                    data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.$i.'">'
                    .$i.'</a></li>';
            }
        }
        $html.= '</ul></nav>';
        return $html;
    }

    /**
     * Get currency by code
     *
     * @param string $isoCode
     *
     * @return array
     */
    private function getCurrencyByCode($isoCode)
    {
        $currency = null;
        if ($isoCode) {
            if (isset($this->currencyCode[$isoCode])) {
                return $this->currencyCode[$isoCode];
            }
            $currency = Currency::getCurrency(Currency::getIdByIsoCode($isoCode));
            if ($currency) {
                $this->currencyCode[$isoCode] = $currency;
            } else {
                $this->currencyCode[$isoCode] = $this->context->currency;
            }
            return $this->currencyCode[$isoCode];
        } else {
            return $this->context->currency;
        }
    }

    /**
     * Get total product
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }
}
