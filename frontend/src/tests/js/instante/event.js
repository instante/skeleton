define(['instante/event', 'chai', 'sinon', 'squire'], function (Event, Chai, Sinon, SquireFactory)
{
    'use strict';

    var Squire;

    beforeEach(function (done)
    {
        Squire = new SquireFactory();
        done();
    });

    describe('instante/event', function ()
    {
        describe('constructor', function ()
        {
            it('should return an object', function (done)
            {
                Event.should.exist;
                (new Event()).should.be.a('Object');
                done();
            });

            it('should have [trigger] and [listen] function', function (done)
            {
                var e = new Event();
                e.should.have.ownProperty('trigger');
                e.trigger.should.be.a('Function');
                e.should.have.ownProperty('listen');
                e.listen.should.be.a('Function');
                done();
            });
        });

        describe('trigger', function ()
        {
            it('should call only listeners associated to that event', function (done)
            {
                Squire.require(['instante/event'], function (Event)
                {
                    var eventAListener = Sinon.spy();
                    var eventBListener = Sinon.spy();

                    var e = new Event();
                    var ee = new Event();
                    e.listen(eventAListener);
                    ee.listen(eventBListener);
                    e.trigger();
                    Chai.expect(eventAListener.calledOnce).to.be.true;
                    Chai.expect(eventBListener.called).to.be.false;

                    done();
                });
            });
            it('should pass arguments to listeners', function (done)
            {
                Squire.require(['instante/event'], function (Event)
                {
                    var listener = Sinon.spy();

                    var e = new Event();
                    e.listen(listener);
                    e.trigger('foo', 'bar');
                    Chai.expect(listener.calledOnce).to.be.true;
                    Chai.expect(listener.calledWith('foo', 'bar')).to.be.ok;
                    done();
                });
            });
        });
    });
});
