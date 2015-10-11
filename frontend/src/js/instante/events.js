define('instante/events', ['instante/debug/console'], function (DebugConsole)
{

    return new function ()
    {
        var listeners = {};

        this.trigger = function (event, eventData)
        {
            if (listeners.hasOwnProperty(event)) {
                for (var callbackIndex in listeners[event]) {
                    if (listeners[event].hasOwnProperty(callbackIndex)) {
                        try {
                            listeners[event][callbackIndex](eventData);
                        } catch (error) {
                            DebugConsole.warn(error);
                        }
                    }
                }
            }
        };

        this.listen = function (event, callback)
        {
            this.addListener(event, callback)
        };

        this.addListener = function (event, callback)
        {
            if (listeners[event] === undefined) {
                listeners[event] = [];
            }
            listeners[event].push(callback);
        };

        this.removeListener = function (event, callback)
        {
            if (listeners.hasOwnProperty(event)) {
                for (var callbackIndex in listeners[event]) {
                    if (listeners[event].hasOwnProperty(callbackIndex)) {
                        if (listeners[event][callbackIndex] === callback) {
                            delete listeners[event][callbackIndex];
                        }
                    }
                }
            }
        };

        this.clearListeners = function (event)
        {
            if (listeners.hasOwnProperty(event)) {
                delete listeners[event];
            }
        };
    };

});
