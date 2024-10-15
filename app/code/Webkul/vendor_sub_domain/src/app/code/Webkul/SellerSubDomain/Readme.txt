#Installation

Note: For the proper working of this module need to do some server configuration so please check Server Configuration section before the installation of this module.

Magento2 Vendor Subdomain module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and SellerSubDomain(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/SellerSubDomain/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#Server Configuration

Inorder to create wildcard domain for global access please check the server-configuration.pdf file.

#User Guide

For Magento2 Vendor Subdomain module's working process follow user guide - https://webkul.com/blog/magento2-vendor-subdomain/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
