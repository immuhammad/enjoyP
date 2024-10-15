/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 define('js/theme', [
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    $('body').on('click','#tab_block_vendor_attribute_edit_tab_view', function () {
        setTimeout(function () {
           $('.wkrequired-entry').parent().parent().parent().prev().addClass('wkrequired-entryicon');
       }, 10);
     });

     $('body').on('hover', function () {
         setTimeout(function () {
            $('.wkrequired-entry').parent().parent().parent().prev().addClass('wkrequired-entryicon');
        }, 1000);
      });

    $('body').on('change','.wkrm', function () {
      var wkrmValue = $(this).val();
      if (parseInt(wkrmValue)) {
        $('body').find('#customfields_'+$(this).attr('data')).attr('disabled','disabled');
        $('body').find('#customfields_'+$(this).attr('data')+'_hidden').removeAttr('disabled');
      } else {
        $('body').find('#customfields_'+$(this).attr('data')).removeAttr('disabled');
        $('body').find('#customfields_'+$(this).attr('data')+'_hidden').attr('disabled','disabled');
      }
    });
    $('body').on('change','input', function () {
        if ($(this).attr("type") == 'file') {
          var ext_arr = $(this).parent().find('.data-extension').attr('data').split(",");
          var new_ext_arr = [];
          for (var i = 0; i < ext_arr.length; i++) {
            new_ext_arr.push(ext_arr[i]);
            new_ext_arr.push(ext_arr[i].toUpperCase());
          }
          if (new_ext_arr.indexOf($(this).val().split("\\").pop().split(".").pop()) < 0) {
              var self = $(this);
              self.val('');
              $('<div>').html('Invalid Extension. Allowed extensions are '+ $(this).parent().find('.data-extension').attr('data'))
              .modal({
                  title: 'Attention!',
                  autoOpen: true,
                  buttons: [{
                   text: 'Ok',
                      attr: {
                          'data-action': 'cancel'
                      },
                      'class': 'action',
                      click: function () {
                          self.val('');
                          this.closeModal();
                      }
                  }]
              });
          }

          var fileName = $(this).val();
          var pos = fileName.indexOf('fakepath')+9;
          if (fileName.substr(pos).length > 90) {
              var self = $(this);
              self.val('');
              $('<div>').html($.mage.__('Filename is too long; must be 90 characters or less'))
              .modal({
                  title: 'Attention!',
                  autoOpen: true,
                  buttons: [{
                   text: 'Ok',
                      attr: {
                          'data-action': 'cancel'
                      },
                      'class': 'action',
                      click: function () {
                          self.val('');
                          this.closeModal();
                      }
                  }]
              });
          }
        }
    });
});
