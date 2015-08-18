```
#!



                       ██╗███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
 ▄ ██╗▄▄ ██╗▄▄ ██╗▄    ██║████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝    ▄ ██╗▄▄ ██╗▄▄ ██╗▄
  ████╗ ████╗ ████╗    ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   █████╗       ████╗ ████╗ ████╗
 ▀╚██╔▀▀╚██╔▀▀╚██╔▀    ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ██╔══╝      ▀╚██╔▀▀╚██╔▀▀╚██╔▀
   ╚═╝   ╚═╝   ╚═╝     ██║██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████╗      ╚═╝   ╚═╝   ╚═╝
                       ╚═╝╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝



```

0. Create new application using Instante:
    a) copy instante folder (without .git subfolder) to your new project folder
    c) setup new git repository
    d) customize this readme.md to correspond to your new project (and this section, 0, is not needed anymore)
    e) continue to Deploy application

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


2.Develop/compile LESS:
as all PHP LESS compilers have been found too slow or not up to date,
we recommend using grunt for on-the-fly LESS development.

install node.js, then use shell commands:
```
# setup
your-project/app/less$ npm install -g grunt-cli
your-project/app/less$ npm install
# start watchdog
your-project/app/less$ grunt
```
the watchdog starts to automatically compile less on any change.

3.composer:
install new dependency - library:
  1) add the dependency to composer.json
  2) run "composer update --lock" - the --lock parameter preserves versions of other libraries.
