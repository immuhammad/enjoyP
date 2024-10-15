define(
    [
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal",
    'mage/mage'
    ],
    function ($, $t, modal) {
        "use strict";
        $.widget(
            'affiliate.showAllData',
            {
                _create: function () {
                    $.validator.addMethod(
                            'multiple-emails', function (value, element) { 
                                var success = true;
                                $.each(value.split(","), function(index, email) {
                                    if(!/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email)) {
                                        success = false;
                                    }
                                });
                                return success;
                                }, $.mage.__('Some of the emails are not valid.'));
                    var importOpts = this.options;
                    $('body').delegate(
                        '#show',
                        'click',
                        function () {
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                width:'200px',
                                title: $t('Products <input type="text" id="aff_campaign_product_search">'),
                                buttons: [{
                                    text: $.mage.__('Ok'),
                                    class: 'okbutton',
                                    click: function () {
                                        backdata();
                                        this.closeModal();
                                    }
                                }]
                            };
                            var cont = $(this).parents('.showData').find('.banner_content').html();
                            cont = $('<div />').addClass('innerContent').append(cont);
                            modal(options, cont);
                            cont.modal('openModal');
                        }
                    );
                    function backdata()
                    {
                      $('.innerContent').each(function () {
                        $('.banner_content').val($(this).html());
                      });
                    }
                    $('body').delegate(
                        '.showdata',
                        'click',
                        function () {
                           if ($(this).hasClass("showfield")) {
                                $(this).removeClass("showfield");
                                var textFieldData=$('#textshow').val();
                                var dataVal=$(this).attr('data');
                                var restData = textFieldData.replace(dataVal+' ','');
                                $('#textshow').val(restData);
                           } else {
                                var newVal = "";
                                var dataVal=$(this).attr('data');
                                $(this).addClass("showfield");
                                var oldVal = $('#textshow').val()+' ';
                                newVal = oldVal+dataVal+' ';
                                $('#textshow').val(newVal);
                            }
                        }
                    );



                    $('body').on("keyup","#aff_campaign_product_search",function () {
                        var search=$(this).val();
                        var allProducts=$(".showdata div div");
                        $(allProducts).each(function () {
                            if ($(this).text().search(new RegExp(search, "i"))!=-1) {
                                $(this).closest(".showdata").show();
                            } else {
                                $(this).closest(".showdata").hide();
                            }
                        });
                    });
                    $('body').delegate(
                        '.action-close',
                        'click',
                        function () {
                          $('.innerContent').each(function () {
                            $('.banner_content').val($(this).html());
                          });

                        }
                    );
                    function isEmail(email) {
                        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                        return regex.test(email);
                    }
                }
            }
        );
        return $.affiliate.showAllData;
    }
);
