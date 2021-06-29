# Dadolun_SibContactSync

## Features
Contact Syncronization functionality for Sendinblue - Magento2 integration.

This module sync all your Magento2 subscribers to Sendinblue.

These are the default built in contact attributes:
- FIRSTNAME
- LASTNAME
- MAGENTO_LANG
- CLIENT (customer subscribed only)
- COMPANY (customer subscribed only)
- CITY (customer subscribed only)
- COUNTRY_ID (customer subscribed only)
- POSTCODE (customer subscribed only)
- STREET (customer subscribed only)
- REGION (customer subscribed only)
- STORE_ID (customer subscribed only)


Double optin and email confirmation are demanded to Magento2 (Different behavior from the official module).


## Installation
You can install this module adding it on app/code folder or with composer.
##### COMPOSER
You need to update your composer.json "repositories" node:
```
{
    "type": "vcs",
    "url":  "git@github.com:dadolun95/magento2-sib-contact-sync.git"
}
```
Then execute this command:
```
composer require dadolun95/magento2-sib-contact-sync
```
Then you'll need to enable the module and update your database:
```
php bin/magento module:enable Dadolun_SibContactSync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
##### CONFIGURATION
You must enable the contact sync from "Stores > Configurations > Dadolun > Sendinblue > Contact Sync" section.
The module provides a "Sync contact" CTA on adminhtml that move all existing contacts to Sendinblue (only new subscribers are synced on runtime).
Since sync functionality is enabled two Sendinblue lists are created:
- [Magento Optin Form] > Temp - DOUBLE OPTIN (contacts that need confirmation are moved here temporarely)
- [magento] > subscriptions

## Contributing
Contributions are very welcome. In order to contribute, please fork this repository and submit a [pull request](https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).
