
      ___           _              _                   //
     |_ _|_ __  ___| |_ __ _ _ __ | |_ ___       .∩∩.//
      | || '_ \/ __| __/ _` | '_ \| __/ _ \     .∩∩∩∩.
      | || | | \__ \ || (_| | | | | ||  __/    \     ) /
     |___|_| |_|___/\__\__,_|_| |_|\__\___|     \_____/

0. Create new application using Instante:
    1. install skeleton using composer:
```
composer create-project instante/skeleton .
```
    1. customize this readme.md to correspond to your new project (and remove this "Create new application" section)
    2. use bin/init-project.php to initialize your new project or customize composer.json, frontend/package.json,
       frontend/bower.json with your own project name, description, license etc.
    3. initialize new git repository in project's folder:
```
git init
bin/git/setup-git.{cmd|sh}
git add .
git commit -m "initial commit"
```


1. Deploy application:
    a) setup git behavior using bin/git/setup-git.{cmd|sh} - disables autocrlf and sets processes to automatically
       recompile grunt if there is a conflict in minified css / js files
    b) install dependencies by typing `composer install` to command line in project root
    c) create file `app/config/environment` with single line containing one of(development|stage|production)
    d) Copy `app/config/local.neon.example` to `app/config/local.neon` and adjust needed configuration (database login etc.)
       Ensure that the database schema exists and is empty.
    e) Ensure that these folders exist and the www server has write access to them:
        - temp
        - log
        - temp/sessions
    f) call `php www/index.php orm:generate-proxies`
    g) call `php www/index.php migrations:migrate`
---------
    h) for running tests, copy "bin/run-tests.cmd.example" to "bin/run-tests.cmd"
      (executable under both unix/windows) and adjust path to php.ini.


2. Develop/compile frontend:

install node.js, then use shell commands:
```
# setup
# install grunt CLI and bower globally to your node.js, if you haven't installed it before
your-project/frontend$ npm install -g grunt-cli
your-project/frontend$ npm install -g bower

# install local grunt packages
your-project/frontend$ npm install

# start watchdog
your-project/frontend$ grunt
```
the watchdog starts to automatically compile less and js on any change.

3.composer:
install new dependency - library:
  1) add the dependency to composer.json
  2) run "composer update --lock" - the --lock parameter preserves versions of other libraries.
