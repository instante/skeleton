define(['instante/configurator', 'squire', 'chai'], function (Configurator, SquireFactory, Chai)
{
    'use strict';

    var Squire;

    beforeEach(function (done)
    {
        Squire = new SquireFactory();
        done();
    });

    describe('instante/configurator.js', function ()
    {
        describe('constructor', function ()
        {
            it('should return instance', function (done)
            {
                Configurator.should.exist;
                Configurator.should.be.an('Object');
                done();
            });
        });

        describe('getter', function ()
        {
            it('should return null for not configured module', function (done)
            {
                // So we get clean configurator instance
                Squire.require(['instante/configurator'], function (Configurator)
                {
                    Chai.expect(Configurator.getConfig('unknownModule')).to.be.null;
                    done();
                });
            });
        });

        describe('setter', function ()
        {
            //sets config for test/object to {foo:"bar"}
            it('should set config', function (done)
            {
                // So we get clean configurator instance
                Squire.require(['instante/configurator'], function (Configurator)
                {
                    var testConfig = {foo: 'bar'};
                    Configurator.modules = function () {};
                    Configurator.configure('test/object', testConfig);
                    Configurator.getConfig('test/object').should.deep.equal(testConfig);
                    console.log(Configurator.modules);
                    done();
                });

            });
        });

    });
});