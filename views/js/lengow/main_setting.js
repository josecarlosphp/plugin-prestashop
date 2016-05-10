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

(function ($) {
    $(document).ready(function () {
        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        /*$(".lengow_report_mail_address select").select2({
            tags: true,
            width: '100%',
            selectOnClose: true,
            closeOnSelect: false,
            tokenSeparators: [",", " "],
            createTag: function(term, data) {
                var value = term.term;
                if(validateEmail(value)) {
                    return {
                        id: value,
                        text: value
                    };
                }
                return null;
            }
        }).on("select2:open", function (e) {
            $('.select2-dropdown--below').hide();
        }).on("select2:selecting", function(e) {
            $('.select2-search__field').val('');
        }).on("select2:unselect", function (evt) {
            if (!evt.params.originalEvent) {
                return;
            }
            evt.params.originalEvent.stopPropagation();
        });*/

        $('input[name="LENGOW_REPORT_MAIL_ENABLED"]').change(function(){
            var checked = $('input[name="LENGOW_REPORT_MAIL_ENABLED"]').prop('checked');
            if( checked == true ){
                $('.lengow_report_mail_address').slideDown(150);
            }
            else{
                $('.lengow_report_mail_address').slideUp(150);
            }
        });


        displayPreProdMode();
        $("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").on('change', function () {
            displayPreProdMode();
        });

        function displayPreProdMode() {
            if ($("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").prop('checked')) {
                $('#lengow_wrapper_preprod').slideDown(150);
            } else {
                $('#lengow_wrapper_preprod').slideUp(150);
            }
        }

        $('#download_log').on('click', function() {
            if ($('#select_log').val() !== null) {
                window.location.href = $('#select_log').val();
            }
        });
    });

})(lengow_jquery);