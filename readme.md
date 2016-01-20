
      ___           _              _                   //
     |_ _|_ __  ___| |_ __ _ _ __ | |_ ___       .∩∩.//
      | || '_ \/ __| __/ _` | '_ \| __/ _ \     .∩∩∩∩.
      | || | | \__ \ || (_| | | | | ||  __/    \     ) /
     |___|_| |_|___/\__\__,_|_| |_|\__\___|     \_____/



[![Build Status](https://travis-ci.org/instante/skeleton.svg?branch=master)](https://travis-ci.org/instante/skeleton)
[![Downloads this Month](https://img.shields.io/packagist/dm/instante/skeleton.svg)](https://packagist.org/packages/instante/skeleton)
[![Latest stable](https://img.shields.io/packagist/v/instante/skeleton.svg)](https://packagist.org/packages/instante/skeleton)


Create new application using Instante:
--------------------------------------

1. install skeleton using composer:

        composer create-project instante/skeleton .

2. customize this readme.md to correspond to your new project (and remove this "Create new application" section)
3. use bin/deployment/init-project.php to initialize your new project or customize composer.json, frontend/package.json,
 frontend/bower.json with your own project name, description, license etc.
4. initialize new git repository in project's folder:

        git init
        bin/git/setup-git.{cmd|sh}
        git add .
        git commit -m "initial commit"


Deploy application:
------------------------

1. install dependencies by executing `composer install` from project root
2. Ensure that the database schema exists and is empty. Optionally, you may create one extra database schema for tests.
3. Ensure that the www server has write access to these folders
    - temp
    - log
4. setup local environment using bin/deployment/deploy-project.php

Develop/compile frontend:
------------------------

install node.js, then use shell commands:

        # setup
        # install grunt CLI and bower as global node.js module
        your-project/frontend$ npm install -g grunt-cli
        your-project/frontend$ npm install -g bower
        
        # install local grunt packages
        your-project/frontend$ npm install
        
        # install local bower components
        your-project/frontend$ bower install
        
        # start watchdog
        your-project/frontend$ grunt

the watchdog starts to automatically compile less and js on any change.

Managing composer packages:
---------------------------

To install new dependency - library:

1. add the dependency to composer.json
2. run `composer update --lock` - the --lock parameter preserves versions of other libraries.
