# Scheduled Export
[![Build Status](https://travis-ci.org/DivanteLtd/pimcore-scheduled-export.svg?branch=master)](https://travis-ci.org/DivanteLtd/pimcore-scheduled-export)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/2c51a42a456c47c9971a7e2a48bcd6f4)](https://www.codacy.com/app/Divante/pimcore-scheduled-export?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=DivanteLtd/pimcore-scheduled-export&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/v/stable)](https://packagist.org/packages/divante-ltd/pimcore-scheduled-export)
[![Total Downloads](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/downloads)](https://packagist.org/packages/divante-ltd/pimcore-scheduled-export)
[![License](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/license)](https://github.com/DivanteLtd/divante-ltd/pimcore-scheduled-export/blob/master/LICENSE)

Scheduled Export lets you run ordinary grid exports in background.

![Scheduled Export](docs/example.jpeg?raw=true "Scheduled Export")

**Table of Contents**
- [Scheduled Export](#scheduled-export)
	- [Compatibility](#compatibility)
	- [Installing/Getting started](#installinggetting-started)
	- [Requirements](#requirements)
	- [Configuration](#configuration)
	- [Testing](#testing)
	- [Contributing](#contributing)
	- [Licence](#licence)
	- [Standards & Code Quality](#standards--code-quality)
	- [About Authors](#about-authors)

## Compatibility

This module is compatible with Pimcore 5.5.0 and higher.

## Installing/Getting startedr

```bash
composer require divante-ltd/pimcore-scheduled-export
```

Enable the Bundle:
```bash
./bin/console pimcore:bundle:enable DivanteScheduledExportBundle
```

## Configuration

In Pimcore panel select Extensions click Install and Enable.

## Testing
Unit Tests:
```bash
PIMCORE_TEST_DB_DSN="mysql://username:password@localhost/pimcore_test" \
    vendor/bin/phpunit
```

Functional Tests:
```bash
PIMCORE_TEST_DB_DSN="mysql://username:password@localhost/pimcore_test" \
    vendor/bin/codecept run -c tests/codeception.dist.yml
```

## Contributing
If you'd like to contribute, please fork the repository and use a feature branch. Pull requests are warmly welcome.

## Licence 
CoreShop VsBridge source code is completely free and released under the 
[GNU General Public License v3.0](https://github.com/DivanteLtd/divante-ltd/pimcore-scheduled-export/blob/master/LICENSE).

## Standards & Code Quality
This module respects all Pimcore 5 code quality rules and our own PHPCS and PHPMD rulesets.

## About Authors
![Divante-logo](http://divante.co/logo-HG.png "Divante")

We are a Software House from Europe, existing from 2008 and employing about 150 people. Our core competencies are built 
around Magento, Pimcore and bespoke software projects (we love Symfony3, Node.js, Angular, React, Vue.js). 
We specialize in sophisticated integration projects trying to connect hardcore IT with good product design and UX.

We work for Clients like INTERSPORT, ING, Odlo, Onderdelenwinkel and CDP, the company that produced The Witcher game. 
We develop two projects: [Open Loyalty](http://www.openloyalty.io/ "Open Loyalty") - an open source loyalty program 
and [Vue.js Storefront](https://github.com/DivanteLtd/vue-storefront "Vue.js Storefront").

We are part of the OEX Group which is listed on the Warsaw Stock Exchange. Our annual revenue has been growing at a 
minimum of about 30% year on year.

Visit our website [Divante.co](https://divante.co/ "Divante.co") for more information.
