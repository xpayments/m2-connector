# X-Payments 1x-3x connector for Magento 2
This extension connects your Magento 2 store with a self-hosted X-Payments 1.x-3.x installation.

For new deployments please use the [X-Payments Cloud connector](https://github.com/xpayments/m2-cloud) extension.

### Installation
Copy these files into the `<mage-dir>/app/code/CDev/XPaymentsConnector/` directory and run:
```sh
bin/magento module:enable CDev_XPaymentsConnector
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
```
Then open **Stores -> Settings -> X-Payments Connector** and import the configuration bundle from your X-Payments installation.

### License
Open Software License 3.0 ([LICENSE.txt](LICENSE.txt)); Academic Free License 3.0 ([LICENSE_AFL.txt](LICENSE_AFL.txt)).

Copyright (c) 2010-present X-Cart Holdings LLC.

### Support
If you have any questions, please [contact us](https://www.x-payments.com/contact-us).
