# OpenMapGenerator v1.2 novembre 2022

OpenMapGenerator have been created for easily manage maps and locations using OpenStreetMap and Leaflet.

Maps can be:

+ public (everyone can show map)
+ restricted (only people with map id and password can show map)
+ private (only owner can show map)

Locations can be personalized with:

+ name
+ description
+ icon
+ external link

Navigation assistants can be called from locations:

+ OpenStreetMap
+ Sygic
+ Waze
+ Google Maps

## Requirements

This project uses:

+ Symfony 6.1
+ Composer 2.2
+ PHP 8.1
+ MySQL 8.0

## Local installation

Get project from GitHub.

Enter project directory:

`cd OpenMapGenerator_directory`

Create .env.dev.local file containing:

```
APP_SECRET=0123456789abcdef0123456789abcdef
DATABASE_URL="mysql://user:password@host:port/database"
EMAILHOST=smtp.myemail.service
EMAILPORT=465
EMAILUSER=mysmtpusername
EMAILPASS=mysmtppassword
EMAILFROM=emailused@tosend.emails
```

Eventually update config/services.yaml parameters:

+ `app.locales` and `app.localesString` to add locales
+ `app.defaultLocale` to change default locale
+ `app.enableLog` to enable application logs
+ `app.linesPerPage` to define lists pagination
+ `app.linesPerPage4admin` to define lists pagination for administration section
+ `app.passwordMinLength` to define minimum password length
+ `app.iconDir` to define map icons directory
+ `app.defaultIcon` to define map default icon (must exists in icons directory)
+ `app.editorName` to define the editor name in terms of use.
+ `app.supportEmail` to define the e-mail that will recieve support requests.

Get Symfony application components:

`composer update`

Initialize database:

`php bin/console doctrine:database:create`

`php bin/console doctrine:migration:migrate`

Then add datas in database using fixtures:

`php bin/console doctrine:fixtures:load`

Start application server:

`symfony server:start`

[Go to local website](http://localhost:8000)

## Usage

A full help have been created online, go to Help in the application menu.

## License

Copyright © 2022, philippe@croue.com, under GNU license.

See LICENSE file for more information.
