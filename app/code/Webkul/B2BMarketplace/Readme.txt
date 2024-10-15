#Installation

Webkul Magento2 B2b Marketplace Module installation is very easy, please follow the steps for installation-

As b2b Marketplace module is a bundle of few extensions, so will need to install those extensions first.

Step 1 - Installation of Webkul Magento2 Multi Vendor Marketplace Module, for this extract marketplace extension zip and follow it's readme.txt file installation steps.

Step 2 - Installation of Webkul Magento2 Marketplace Vendor Sub-Domain Module, for this extract marketplace-vendor-sub-domain extension zip and follow it's readme.txt file installation steps.

Step 3 - Unzip the b2b-marketplace extension zip and create Webkul(vendor) and B2BMarketplace(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/B2BMarketplace/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

Step 5 - Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 B2b Marketplace module's working process follow user guide - https://webkul.com/blog/magento2-b2b-marketplace/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
