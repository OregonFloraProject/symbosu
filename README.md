This fork of the Symbiota code is actively being developed by the Biodiversity Knowledge Integration Center (BioKIC, https://github.com/BioKIC) development team at Arizona State University.
Even though BioKIC code developments are regularly pushed back to this repository, we recommend that you download/fork code directly from the
BioKIC/Symbiota repository (https://github.com/BioKIC/Symbiota) to ensure that you obtain the most recently code changes.

# Welcome to the Symbiota code repository

## ABOUT THIS SOFTWARE

The Symbiota Software Project is building a library of webtools to aid biologists in establishing specimen based virtual floras and faunas. This project developed from the realization that complex, information rich biodiversity portals are best built through collaborative efforts between software developers, biologist, wildlife managers, and citizen scientist. The central premise of this open source software project is that through a partnership between software engineers and the scientific community, higher quality and more publicly useful biodiversity portals can be built. An open source software framework allows the technicians to create the tools, thus freeing the biologist to concentrate their efforts on the curation of quality datasets. In this manner, we can create something far greater than a single entity is capable of doing on their own.

More information about this project can be accessed through [https://symbiota.org](https://symbiota.org).

For documentation and user guides please visit [Symbiota Docs](https://symbiota.org/docs).

## ACKNOWLEDGEMENTS

Symbiota has been generously funded by the National Science Foundation (DBI-0743827) from 15 July 2008 to 30 June 2011 (Estimated). The Global Institute of Sustainability (GIOS) at Arizona State University has also been a major supporters of the Symbiota initiative since the very beginning. Arizona State University Vascular Plant and Lichen Herbarium have been intricately involved in the development from the start. Sky Island Alliance and the Arizona-Sonora Desert Museum have both been long-term participants in the development of this product.

## FEATURES

- Specimen Search Engine
  - Taxonomic Thesaurus for querying taxonomic synonyms
  - Google Map and Google Earth mapping capabilities
  - Dynamic species list generated from specimens records
- Flora/Fauna Management System
  - Static species list (local floras/faunas)
- Interactive Identification Keys
  - Key generation for are species list within system
  - Key generator based on a point locality
- Image Library

## LIMITATIONS

- Tested thoroughly on Linux and Windows operating systems
- Code should work with an PHP enabled web server, though central development and testing done using Apache HTTP Server

## INSTALLATION

Please read the [INSTALL.md](docs/INSTALL.md) file for installation instructions.

## UPDATES

Please read the [UPDATE.md](docs/UPDATE.md) file for instructions on how to update Symbiota.

# [OregonFlora](https://oregonflora.org/)

A [Symbiota](http://symbiota.org) portal focusing on the vascular plants of Oregon. For the Symbiota README, see 
above. In addition to the basic Symbiota environment,
**PHP >= 7** is required for this project, along with the **php-apcu** package.

This site features content that diverges significantly from the Symbiota project, while still adhering to the 
Symbiota database structure and maintaining core Symbiota features.
For the site content that differs from Symbiota, which includes the home page, garden page, inventories, identify tool, and taxonomic profiles,
OregonFlora development differs in the following ways: 
   - [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) is used to access the database, providing 
     an asynchronous JSON API. This is done mainly to decouple the PHP-based server-side code from the front end.
     [Composer](https://getcomposer.org/) is used as the build system.
   - For the front end, [ReactJS](https://reactjs.org) and [Less](http://lesscss.org/) are used. 
   [NodeJS](https://nodejs.org/) is used as the build system.
   - Wherever possible, PHP backend code is separated from the HTML/CSS/JS frontend. This backend code exposes data
   to the frontend using asynchronous JSON. For example:
        - Site's navbar is in [js/react/src/header](./js/react/src/header) and consumes data exposed by 
        [webservices/autofillsearch.php](./webservices/autofillsearch.php)
        - [Garden page](https://oregonflora.org/garden/index.php) frontend is in
            [js/react/src/garden](./js/react/src/garden) and consumes data exposed by the backend in 
            [garden/rpc/api.php](./garden/rpc/api.php)
        - [Taxa page](https://oregonflora.org/checklists/dynamicmap.php?interface=key) frontend is in
            [js/react/src/taxa](./js/react/src/taxa) and consumes data exposed by the backend in 
            [taxa/rpc/api.php](./taxa/rpc/api.php) 
        - [Identify page](https://oregonflora.org/taxa/search.php?search=cat) frontend is in
            [js/react/src/taxa](./js/react/src/identify) and consumes data exposed by the backend in 
            [taxa/rpc/api.php](./ident/rpc/api.php) 
        - [Inventory page](https://oregonflora.org/projects/index.php) frontend is in
            [js/react/src/taxa](./js/react/src/inventory) and consumes data exposed by the backend in 
            [taxa/rpc/api.php](./projects/rpc/api.php) 
        - [Checklist page](https://oregonflora.org/checklists/checklist.php?cl=14&pid=1) frontend is in
            [js/react/src/taxa](./js/react/src/explore) and consumes data exposed by the backend in 
            [taxa/rpc/api.php](./checklists/rpc/api.php) 
   - These changes have made some of the original Symbiota code unneeded, but it has been left in wherever possible
   for compatibility, as most code is not React/Doctrine based (yet).

### To build the back end:
1. Follow the [Symbiota installation instructions](docs/INSTALL.md) 
for Apache, PHP, and MariaDB/MySQL
2. Install Composer for PHP
3. Run the following in the repository root to install the PHP dependencies: `composer install`
4. Run the following in the repository root to generate Doctine's proxy classes `doctrine orm:generate-proxies temp/proxies/`. In a
development environment, you can set IS_DEV to true in [symbini.php](./config/symbini_template.php) to do this automatically
every time you make changes to the Doctrine-based PHP code.

### To build the front end:
Install NodeJS and run the following from [js/react](./js/react)
1. Install the NodeJS dependences: `npm install`
2. Build the React- and Less-based pages: `npm run build`


For a development server that watches for changes in .js/.jsx/.less files and automatically rebuilds them: `npm run devstart`
from the [js/react](./js/react) directory.

