# Scheduled Export
[![Analysis Actions](https://github.com/DivanteLtd/pimcore-scheduled-export/workflows/Analysis/badge.svg?branch=master)](https://github.com/DivanteLtd/pimcore-scheduled-export/actions)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/2c51a42a456c47c9971a7e2a48bcd6f4)](https://www.codacy.com/app/Divante/pimcore-scheduled-export?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=DivanteLtd/pimcore-scheduled-export&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/v/stable)](https://packagist.org/packages/divante-ltd/pimcore-scheduled-export)
[![Total Downloads](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/downloads)](https://packagist.org/packages/divante-ltd/pimcore-scheduled-export)
[![License](https://poser.pugx.org/divante-ltd/pimcore-scheduled-export/license)](https://github.com/DivanteLtd/divante-ltd/pimcore-scheduled-export/blob/master/LICENSE)

Scheduled Export lets you run ordinary grid exports in the background or using cli.

![Scheduled Export](docs/example.png?raw=true "Scheduled Export")

**Table of Contents**
- [Scheduled Export](#scheduled-export)
	- [Compatibility](#compatibility)
	- [Installing/Getting started](#installinggetting-started)
	- [Requirements](#requirements)
	- [Usage](#Usage)
	- [Testing](#testing)
	- [Contributing](#contributing)
	- [Licence](#licence)
	- [Standards & Code Quality](#standards--code-quality)
	- [About Authors](#about-authors)

## Compatibility

This module is compatible with Pimcore 6.3.0 and higher.

## Installing

```bash
composer require divante-ltd/pimcore-scheduled-export
```

Make sure the dependencies are enabled and installed:
```bash
./bin/console pimcore:bundle:enable ProcessManagerBundle
./bin/console pimcore:bundle:install ProcessManagerBundle
```

Enable the Bundle:
```bash
./bin/console pimcore:bundle:enable DivanteScheduledExportBundle
```

## Requirements

* Pimcore 6.3
* [ProcessManager](https://github.com/elements-at/ProcessManager)

## Usage
Prepare a gridconfig that you want to export, open up ProcessManager, create a new Scheduled Export Executable.
In the configuration window, select the folder you want to export, gridconfig and where the exported file should be saved.

Adjust other settings at your will.

Configure the schedule using ProcessManager's cron settings or run the export manually.
Keep in mind, that currently there is no progress display support, so export will not be visible in Processes tab of the ProcessManager.

You can also run the export from the cli if desired:

```bash
bin/console scheduled-export:start -g 3 -f '/Product Data/Cars' -a '/Export' --filename 'cars' -t 1 --format '%s' -c 'o_key like "%Giu%"' --only-changes 1
```   
Type `bin/console scheduled-export:start --help` to get detailed description of the parameters.
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
Pimcore Scheduled Export source code is completely free and released under the 
[GNU General Public License v3.0](https://github.com/DivanteLtd/divante-ltd/pimcore-scheduled-export/blob/master/LICENSE).

## Standards & Code Quality
This module respects all Pimcore code quality rules and our own PHPCS and PHPMD rulesets.

## About Authors
![Divante-logo](https://www.divante.com/hubfs/raw_assets/public/Divante_March_2021/images/logo-new.svg "Divante")

We are a Software House from Europe, existing from 2008 and employing about 150 people. Our core competencies are built 
around Magento, Pimcore and bespoke software projects (we love Symfony3, Node.js, Angular, React, Vue.js). 
We specialize in sophisticated integration projects trying to connect hardcore IT with good product design and UX.

We work for Clients like INTERSPORT, ING, Odlo, Onderdelenwinkel and CDP, the company that produced The Witcher game. 
We develop two projects: [Open Loyalty](http://www.openloyalty.io/ "Open Loyalty") - an open source loyalty program 
and [Vue.js Storefront](https://github.com/DivanteLtd/vue-storefront "Vue.js Storefront").

We are part of the OEX Group which is listed on the Warsaw Stock Exchange. Our annual revenue has been growing at a 
minimum of about 30% year on year.

Visit our website [Divante.co](https://divante.co/ "Divante.co") for more information.
