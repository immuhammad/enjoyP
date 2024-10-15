/**
 * Webkul Affiliate requirejs.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    config: {
        mixins: {
            'Magento_Ui/js/grid/columns/multiselect': {
                'Webkul_Affiliate/js/multiselect': true
            }
        }
     },
    map: {
        '*': {
            bannerlist: 'Webkul_Affiliate/js/bannerlist',
            preferences: 'Webkul_Affiliate/js/preference',
            requestforaffilateuser: 'Webkul_Affiliate/js/requestforaffilateuser',
            showAllData: 'Webkul_Affiliate/js/showAllData'
        }
    }
};
