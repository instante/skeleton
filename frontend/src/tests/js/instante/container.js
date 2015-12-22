define(['instante/container', 'squire', 'sinon', 'chai'], function (Container, SquireFactory, Sinon, Chai)
{
    'use strict';

    var Squire;

    beforeEach(function (done)
    {
        Squire = new SquireFactory();
        done();
    });

    describe('instante/container.js', function ()
    {
        describe('constructor', function ()
        {
            it('should return instance', function (done)
            {
                Container.should.exist;
                Container.should.be.an('Object');
                done();
            });

            it('should have [exec] function', function (done)
            {
                Container.should.have.ownProperty('exec');
                Container.exec.should.be.a('Function');
                done();
            })

            it('should have [execModule] function', function (done)
            {
                Container.should.have.ownProperty('execModule');
                Container.execModule.should.be.a('Function');
                done();
            })
        });

        describe('execModule', function ()
        {
            it('should configure test/object', function (done)
            {
                Squire.mock('instante/configurator', {
                    configure: Sinon.spy(),
                    getConfig: Sinon.spy()
                });

                Squire.require(['instante/container', 'instante/configurator'], function (Container, Configurator)
                {
                    var testConfig = {foo: 'bar'};
                    Container.requireModule = Sinon.stub();
                    Container.execModule('test/object', testConfig);
                    Chai.expect(Configurator.configure.calledWith('test/object', testConfig)).to.be.ok;
                    done();
                });
            });

            it('should require test/object when asked for test/object', function (done)
            {
                Container.requireModule = Sinon.spy();
                Container.execModule('test/object', {foo: 'bar'});
                Chai.expect(Container.requireModule.calledWith('test/object')).to.be.ok;
                done();
            });
        });

        describe('exec', function ()
        {
            it('should call execModule for each module in config', function (done)
            {
                var testConfig = [
                    {moduleA: 'moduleAConfig'},
                    {moduleB: 'moduleBConfig'},
                ];
                Container.execModule = Sinon.spy();
                Container.exec(testConfig);

                Chai.expect(Container.execModule.calledWith('moduleA', 'moduleAConfig')).to.be.ok;
                Chai.expect(Container.execModule.calledWith('moduleB', 'moduleBConfig')).to.be.ok;
                Chai.expect(Container.execModule.calledTwice).to.be.ok;

                done();
            });
        });
    })
});