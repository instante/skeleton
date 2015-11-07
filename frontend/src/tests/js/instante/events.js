define(['instante/events', 'chai', 'sinon', 'squire'], function (Events, Chai, Sinon, SquireFactory)
{
    'use strict';

    var Squire;

    beforeEach(function (done)
    {
        Squire = new SquireFactory();
        done();
    });

    describe('instante/events', function ()
    {
        describe('constructor', function ()
        {
            it('should return an object', function (done)
            {
                Events.should.exist();
                done();
            });

            it('should have [trigger] function', function (done)
            {
                Events.should.have.ownProperty('trigger');
                Events.trigger.should.be.a('Function');
                done();
            });

            it('should have [listen] function', function (done)
            {
                Events.should.have.ownProperty('listen');
                Events.listen.should.be.a('Function');
                done();
            });

            it('should have [addListener] function', function (done)
            {
                Events.should.have.ownProperty('addListener');
                Events.addListener.should.be.a('Function');
                done();
            });

            it('should have [removeListener] function', function (done)
            {
                Events.should.have.ownProperty('removeListener');
                Events.removeListener.should.be.a('Function');
                done();
            });

            it('should have [clearListeners] function', function (done)
            {
                Events.should.have.ownProperty('clearListeners');
                Events.removeListener.should.be.a('Function');
                done();
            });
        });

        describe('trigger', function ()
        {
            it('should call only listeners associated to that event', function (done)
            {
                Squire.require(['instante/events'], function (Events)
                {
                    var eventAListener = Sinon.spy();
                    var eventBListener = Sinon.spy();

                    Events.addListener('eventA', eventAListener);
                    Events.addListener('eventB', eventBListener);
                    Events.trigger('eventA', 'data');
                    Chai.expect(eventAListener.calledOnce).to.be.true;
                    Chai.expect(eventAListener.calledWith('data')).to.be.ok;
                    Chai.expect(eventBListener.called).to.be.false;

                    done();
                });
            });

            it('should not call removed event listener', function (done)
            {
                Squire.require(['instante/events'], function (Events)
                {
                    var eventListener = Sinon.spy();

                    Events.addListener('event', eventListener);
                    Events.removeListener('event', eventListener);
                    Events.trigger('event', 'data');
                    Chai.expect(eventListener.calledOnce).to.be.false();
                    Chai.expect(eventListener.neverCalledWith('data')).to.be.ok();

                    done();
                });
            });
        });

        describe('removeListener', function ()
        {
            it('removes only specified listener', function (done)
            {
                Squire.require(['instante/events'], function (Events)
                {
                    var eventListenerRemoved = Sinon.spy();
                    var eventListenerActive = Sinon.spy();

                    Events.addListener('event', eventListenerRemoved);
                    Events.addListener('event', eventListenerActive);
                    Events.removeListener('event', eventListenerRemoved);
                    Events.trigger('event', 'data');

                    Chai.expect(eventListenerRemoved.called).to.be.false();
                    Chai.expect(eventListenerActive.calledOnce).to.be.true();

                    done();
                });
            });
        });

        describe('clearListeners', function ()
        {
            it('removes all listeners for specified event and keeps others', function (done)
            {
                Squire.require(['instante/events'], function (Events)
                {
                    var eventAListener = Sinon.spy();
                    var eventABListener = Sinon.spy();
                    var eventBListener = Sinon.spy();

                    Events.addListener('eventA', eventAListener);
                    Events.addListener('eventA', eventABListener);
                    Events.addListener('eventB', eventABListener);
                    Events.addListener('eventB', eventBListener);

                    Events.clearListeners('eventA');
                    Events.trigger('eventA', 'dataA');
                    Events.trigger('eventB', 'dataB');

                    Chai.expect(eventAListener.called).to.be.false();
                    Chai.expect(eventABListener.called).to.be.ok();
                    Chai.expect(eventABListener.calledOnce).to.be.ok();
                    Chai.expect(eventABListener.calledWith('dataB')).to.be.ok();
                    Chai.expect(eventBListener.called).to.be.ok();

                    done();
                });
            });
        });
    });
});