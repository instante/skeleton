module.exports = function (grunt)
{
    'use strict';

    var jscripts = {
        common: [
            'require-config',
            '../../bower_components/requirejs/require',
            // include all modules that should be known (compiled) here
            // you can omit those that are listed as dependencies of loaded modules
            'instante/container'
        ],
        front: [ //example: a module with custom scripts
        ]
    };
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        path: {
            out: '../www/',
            src: 'src/',
            node: 'node_modules/',
            bower: 'bower_components/'
        },
        jscriptsFront: [].concat(jscripts.common, jscripts.front),
        jscripts: [].concat(jscripts.common, jscripts.front, ['<%= path.src %>/js/**/*.js']),

        watch: {
            scripts: {
                files: ['<%= jscripts %>'],
                tasks: ['newer:requirejs', 'requirejs-dependencies']
            },
            styles: {
                files: [
                    '<%= path.src %>less/**'
                ],
                tasks: ['less'],
                options: {
                    livereload: 9090
                }
            },
            gruntfile: {
                files: [
                    'Gruntfile.js',
                    'package.json'
                ],
                tasks: ['default', 'watch']
            }
        },
        requirejs: {
            front: {
                options: {
                    baseUrl: "<%= path.src %>js/",
                    mainConfigFile: "<%= path.src %>js/require-config.js",
                    name: "bootstrap",
                    out: "<%= path.out %>js/script.min.js",
                    optimize: "uglify2",
                    generateSourceMaps: true,
                    preserveLicenseComments: false,
                    include: [].concat(jscripts.common, jscripts.front)
                }
            }
        },
        less: {
            build: {
                options: {
                    paths: [
                        '<%= path.src %>less'
                    ],
                    cleancss: true,
                    compress: true,
                    sourceMap: true,
                    sourceMapURL: 'main.min.css.map',
                    sourceMapRootpath: '../../frontend/',
                    sourceMapFilename: '<%= path.out %>css/main.min.css.map'
                },
                files: {
                    '<%= path.out %>css/main.async.min.css': [
                        '<%= path.src %>less/main.async.less'
                    ],
                    '<%= path.out %>css/main.min.css': [
                        '<%= path.src %>less/main.less'
                    ]
                }
            }
        },
        csssplit: {
            dist: {
                src: ['<%= path.out %>css/main.min.css'],
                dest: '<%= path.out %>css/main.min.css',
                options: {
                    maxSelectors: 4095,
                    maxPages: 3,
                    suffix: '.ie-'
                }
            }
        },
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

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-csssplit');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-text-replace');
    grunt.loadNpmTasks('grunt-mocha-require-phantom');
    grunt.loadNpmTasks('grunt-newer');

    grunt.registerTask('default', ['dev']);
    grunt.registerTask('dist', ['newer:requirejs', 'requirejs-dependencies', 'less:build']);
    grunt.registerTask('dev', ['dist', 'watch']);

    grunt.registerTask('requirejs-dependencies', 'Generates dependency tree of requirejs modules for PHP', function ()
    {
        var madge = require('madge');
        var dependencies = madge(['./src/js'], {
            format: 'amd',
            findNestedDependencies: true,
            requireConfig: './src/js/require-config.js'
        });

        grunt.file.write('RequireJSDependencies.json', JSON.stringify(dependencies.tree, null, 4));
    });

    grunt.registerTask('tests-cli', 'Runs tests in CLI', ['clean:tests', 'mocha_require_phantom:cli']);
    grunt.registerTask('tests-browser', 'Runs tests in Browser', ['clean:tests', 'mocha_require_phantom:browser']);

};
