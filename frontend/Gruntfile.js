module.exports = function(grunt)
{
    'use strict';
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        'clean': {
            options: {
                force: true
            },
            tests: {
                src: ['tmp']
            }
        },
        mocha_require_phantom: {
            options: {
                base: 'src',
                main: 'tests/tests-bootstrap',
                // this is relative to Gruntfile.js + options.base !!
                requireLib: '../bower_components/requirejs/require.js',
                files: ['tests/js/**/*.js']
            },
            browser: {
                options: {
                    keepAlive: true
                }
            },
            cli: {
                options: {
                    keepAlive: false
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-mocha-require-phantom');


    grunt.registerTask('tests-cli', 'Runs tests in CLI', ['clean:tests', 'mocha_require_phantom:cli']);
    grunt.registerTask('tests-browser', 'Runs tests in Browser', ['clean:tests', 'mocha_require_phantom:browser']);

};
