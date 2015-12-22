require.config({
    //this is relative to Gruntfile.js !!
    baseUrl: '/src/js',
    paths: {
        squire: '../../node_modules/squirejs/src/Squire',
        chai: '../../node_modules/chai/chai',
        sinon: '../../vendor/sinon-1.15.4'
    }
});

// load frontend config
require(['chai', 'require-config'], function (Chai)
{

    // inject .should to Object prototype
    Chai.should();

    // path correction because of require.config.baseUrl and files are globbed from Gruntfile.js level
    testPathname = '../../' + testPathname;

    // mocha setup
    mocha.setup({ui: 'bdd'});

    // run chosen test file
    require([testPathname], function ()
    {
        if (window.mochaPhantomJS) {
            mochaPhantomJS.run();
        } else {
            mocha.run();
        }
    });
});



