<p align="center">
  <a href="https://www.epay.bg/">
    <img src="skin/frontend/base/default/images/epaybg/epaybg.gif" alt="ePay.bg" width="100" height="60">
  </a>
</p>

# ePay.bg payment module for Magento 1

`CeckosLab_Epaybg` is a community-built Magento 1 payment module for accepting
payments through [ePay.bg](https://www.epay.bg/), the Bulgarian electronic
payment system. It implements the ePay WEB merchant flow and handles payment
status notifications inside Magento.

## Author and contact

Created by [Tsvetan Stoychev](https://basicrum.com/) ([@ceckoslab](https://github.com/ceckoslab)).

- Website: [Basicrum](https://basicrum.com/)
- Email: [tsto@basicrum.com](mailto:tsto@basicrum.com)

## Contributors

Thank you to the people whose work appears in the repository history:

- **Steve Stoychev** — initial Magento module and early configuration and HTTP
  compatibility improvements
- **Martin Grozdanov** ([@mxhellish](https://github.com/mxhellish)) — version
  2.0.3 configuration fixes and Composer/Modman packaging
- **Nikolay Dimitrov** ([@nobodyfamouz](https://github.com/nobodyfamouz)) —
  integration of the version 2.0.3 contribution in
  [pull request #4](https://github.com/ceckoslab/epaybg/pull/4)

The module provides three checkout methods:

- ePay.bg account (Microaccount)
- Direct debit or credit card payment
- Cash at an EasyPay office or B-Pay at an ATM, using a generated 10-digit code

It also includes Bulgarian and English translations, customer email templates,
order status updates for paid, denied, and expired payments, and configuration
at website and store-view scope.

## Legacy status

This is a historical Magento 1 extension. The latest release in this repository
is **2.0.3 (2017)**, and the integration has not been verified against current
Magento/OpenMage releases or the current ePay.bg API.

The sandbox URLs embedded in the module point to an older ePay demo environment.
Before using the extension, compare the implementation with the current
[ePay API documentation](https://kb.epay.bg/en/) and test the complete checkout
and notification flow. Do not deploy it to a production shop without a code and
security review.

This project is not an official product of, or endorsed by, EPAY AD. The ePay.bg
name and logo belong to their respective owner.

## Requirements

- A Magento 1 installation
- An ePay.bg merchant account and contract
- Your ePay customer identification number (CIN), merchant email, and secret key
- A public HTTPS notification URL

The standard and direct-card methods accept BGN, EUR, and USD. The EasyPay and
B-Pay method is limited to BGN by the module.

## Installation

### Modman

From the Magento root directory:

```sh
modman clone https://github.com/ceckoslab/epaybg.git
```

### Manual installation

Copy the repository's `app/` and `skin/` directories into the corresponding
directories in the Magento root. Preserve the directory structure, then clear
Magento's configuration and layout caches. If compilation is enabled, recompile
after installing the module.

## Configuration

1. Sign in to the Magento Admin Panel.
2. Open **System > Configuration > Sales > ePay Bg**.
3. Enter the merchant secret key and either the trader CIN or trader email.
4. Set the payment expiration period.
5. Enable and name the payment methods you want to offer.
6. Configure ePay.bg to send payment notifications to:

   ```text
   https://your-shop.example/epaybg/standard/response
   ```

7. Place test orders and confirm that successful, denied, and expired payments
   produce the expected Magento order states before enabling production mode.

For current onboarding, credentials, demo-environment, and notification setup,
refer to the official [ePay WEB API guide](https://kb.epay.bg/en/web/web/) and
[payment notification guide](https://kb.epay.bg/en/web/notify/).

Contributions, bug reports, and documentation improvements are welcome through
[GitHub issues](https://github.com/ceckoslab/epaybg/issues) and pull requests.

## License

The package is distributed under the
[Open Software License 3.0](https://opensource.org/license/osl-3-0), as declared
in `composer.json` and the source-file headers.
