# Brevo (formerly Sendinblue) - Magento2 integration module (light) <img src="https://avatars.githubusercontent.com/u/168457?s=40&v=4" alt="magento" /> 
[![Latest Stable Version](https://poser.pugx.org/dadolun95/magento2-brevo-sendinblue-light/v/stable)](https://packagist.org/packages/dadolun95/magento2-brevo-sendinblue-light)

### This module is direct dependency of a [more specific, extended integration module](https://github.com/dadolun95/magento2-brevo-sendinblue) (always open source).
### Install this extension only if you need to synchronize subscribers to Brevo and don't need order synchronization and cart events tracking.


## Features
Syncronization functionality for Brevo (formerly Sendinblue) - Magento2 integration.
This module integrates your Magento2 site with Brevo allowing you to refine your marketing strategy, create automations on many scenarios and create campaigns based on your Magento e-commerce site data.
- Simplified module configuration
- Sendinblue PHP SDK usage
- Debug log feature
- Autonomous synchronization of subscribers data on Brevo
- Implementation of synchronous vs asynchronous data synchronization on Brevo avoiding bottlenecks
- Pageview tracking and user navigation tracking on Brevo

## Compatibility
Fully tested and working on Magento CE(EE) 2.4.4, 2.4.5, 2.4.6

## Installation
You can install this module adding it on app/code folder or with composer.
```
composer require dadolun95/magento2-brevo-sendinblue-light
```
Then you'll need to enable the module and update your database and files:
```
php bin/magento module:enable Dadolun_SibCore Dadolun_SibContactSync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

##### CONFIGURATION
You must enable the module from "Stores > Configurations > Dadolun > Brevo > General" section adding you Brevo API key:
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/99b868ef-ecd8-46fa-8d40-2ceb143573ba)
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/69a5cce9-a74f-45fb-a646-1689fd1c456d)
With the debugger option enabled the module will log each API v3 call result and response code and also observer calls on a dedicated file localed on /var/log/sendinblue-integration.log file.
Remember that letting the debugger enabled on production enviroment can slow down the website.

Enable Brevo visitor tracking in order to register each customer pageview on Brevo via API (always synchronous and client-side):
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/78a1ab0c-520c-48e4-a8e2-c9e6adfed62a)
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/06359428-c2ed-4480-872d-ffe0b7dfed51)
Remember to enable tracking on Brevo, then copy you client key from the js snippet on Magento configurations:
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/ded74101-cc62-4499-9c2c-5afd8853880e)
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/3d106cba-4807-43c3-885f-ea78a9b80ff3)

You must enable contact sync after the initial setup for newsletter subsription synchronization on Brevo:
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/2d2a4ded-52d6-4b60-844c-aa946694df1f)
Choose between "Sync" and "Async" Synchronization type.
- "Sync" mode (not recommended) will create or update subscriber data on Brevo synchronously at each magento2 event (subscription update / order update) making an API call to Brevo
- "Async" mode (recommended) use Magento2 message queue system with a dedicated MySQL-operated queue ([See here message queue configuration guide](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues.html?lang=en)) so you need to configure also magento to use consumer properly updating your app/etc/env.php file (something like that):
```
...
    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 1000,
        'consumers' => [
            'sibContactProcessor'
        ]
    ],
...
```
The module provides a "Sync contact" CTA on adminhtml that move all existing contacts to Brevo (only new subscribers are synced on runtime). 
The "Sync contact" CTA use Magento2 message queue system. So, clicking "Sync Order" you'll only add a complete order synchronization request on queue.
If you had choose the Synchronous mode and you've not configured message queue system on your Magento installation, you will need to run this command from your cli each time you want to perform a complete "Contact Sync" request from adminhtml:
```
php bin/magento queue:consumers:start sibContactProcessor
```
Since Contact synchronization functionality is enabled two Brevo lists are created:
- [Magento Optin Form] > Temp - DOUBLE OPTIN (contacts that need confirmation are moved here temporarely)
- [magento] > subscriptions
Complete the contact sync configuration choosing the Brevo list where you want to synchronize your contacts (subscriptions created list is recommended).
You can also create new lists and folders on Brevo and select it differently for each Magento store configured (Ex: subscriptions_USA for a website working in United States, and subscriptions_EU for a website working in Europe).
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/76e606ff-7de5-42c6-8075-e069b762c00a)

##### SMTP CONFIGURATION
For Magento 2.4.4 and Magento 2.4.5 you can install [Mageplaza](https://www.mageplaza.com/magento-2-smtp/) or [Magepal](https://github.com/magepal/magento2-gmail-smtp-app) SMTP modules.
Use Magento core SMTP configuration feature for 2.4.6 and newer versions instead.
Brevo SMTP settings are located on "Transactional > Settings" section:
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/82af3caa-af3c-4ad9-96ee-caaa5c1804c0)
![image](https://github.com/dadolun95/magento2-brevo-sendinblue/assets/8927461/25e2eb89-4139-4f44-919d-16e5a228d085)


## Contributing
Contributions are very welcome. In order to contribute, please fork this repository and submit a [pull request](https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).
