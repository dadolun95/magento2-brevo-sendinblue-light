# Brevo Magento2 Module <img src="https://avatars.githubusercontent.com/u/168457?s=40&v=4" alt="magento" />

### Dadolun_SibContactSync

[![Latest Stable Version](https://poser.pugx.org/dadolun95/magento2-sib-contact-sync/v/stable)](https://packagist.org/packages/dadolun95/magento2-sib-contact-sync)

## Features
Contact Syncronization functionality for Brevo (Sendinblue previously) - Magento2 integration.

This module sync all your Magento2 subscribers to Brevo.

These are the default built in contact attributes:
- FIRSTNAME
- LASTNAME
- MAGENTO_LANG
- CLIENT (subscribed customer only)
- COMPANY (subscribed customer only)
- CITY (subscribed customer only)
- COUNTRY_ID (subscribed customer only)
- POSTCODE (subscribed customer only)
- STREET (subscribed customer only)
- REGION (subscribed customer only)
- STORE_ID (subscribed customer only)


Double optin and email confirmation are demanded to Magento2.

## Compatibility
Magento CE(EE) 2.4.4, 2.4.5, 2.4.6

## Installation
You can install this module adding it on app/code folder or with composer.
##### COMPOSER
```
composer require dadolun95/magento2-sib-order-sync
```
##### MAGENTO
Then you'll need to enable the module and update your database:
```
php bin/magento module:enable Dadolun_SibCore
php bin/magento module:enable Dadolun_SibContactSync
php bin/magento module:enable Dadolun_SibOrderSync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

##### CONFIGURATION
You must enable the contact sync from "Stores > Configurations > Dadolun > Brevo > Contact Sync" section.
The module provides a "Sync contacts" CTA on adminhtml that move all existing contacts to Brevo (only new subscribers are synced on runtime).
Since sync functionality is enabled two Brevo lists are created:
- [Magento Optin Form] > Temp - DOUBLE OPTIN (contacts that need confirmation are moved here temporarely)
- [magento] > subscriptions
Pay attention to the "Sync Type" configuration, you must choose between "Async" and "Sync" mode.
- "Sync" mode (not recommended) will create or update subscribers data on Brevo synchronously at each magento2 event (subscription update / customer address save) making an API call to Brevo
- "Async" mode (recommended) use Magento2 message queue system with a dedicated MySQL-operated queue ([See here message queue configuration guide](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues.html?lang=en)) so you need to configure also magento to use consumer properly updating your app/etc/env.php file (something like that):
```
...
    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 1000,
        'consumers' => [
            'sibContactProcessor',
            'sibOrderProcessor',
        ]
    ],
...
```
The "Sync contacts" CTA use Magento2 message queue system. Clicking "Sync contacts" you'll only add a complete subscriber list synchronization request on queue. So, if you set up "Sync" as "Sync Type" and you've not configured message queue system on your Magento installation, you will need to run this command from your cli each time you want to perform a "Contacts Sync" request from adminhtml:
```
php bin/magento queue:consumers:start sibContactProcessor
```

## Contributing
Contributions are very welcome. In order to contribute, please fork this repository and submit a [pull request](https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).
