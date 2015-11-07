define('instante/container', ['instante/core', 'instante/configurator'], function (Instante, Configurator)
{

    return new function ()
    {
        this.exec = function (startupData)
        {
            // initialize modules
            for (var i in startupData) {
                var moduleData = startupData[i];
                for (var moduleName in moduleData) {
                    if (moduleData.hasOwnProperty(moduleName)) {
                        this.execModule(moduleName, moduleData[moduleName]);
                    }
                }
            }
        };

        this.execModule = function (moduleName, moduleData)
        {
            Configurator.configure(moduleName, moduleData);
            this.requireModule(moduleName);
        };

        this.requireModule = function (moduleName)
        {
            require([moduleName]);
        };

        /**
         * Makes possible to get module configuration via self.config, like:
         * <code>
         * define('some/module', [], function me() {
         *     console.log(me.config);
         * }
         * </code>
         */
        var initAutoConfigureModules = function ()
        {
            var setupAutoConfigureContext = function (context)
            {
                var oldExecCb = context.execCb;
                context.execCb = function (name, callback, args, exports)
                {
                    callback.config = Configurator.getConfig(name);
                    return oldExecCb.apply(context, arguments);
                }
            }
            for (var c in require.s.contexts) {
                var context = require.s.contexts[c];
                setupAutoConfigureContext(context);
            }
            var oldNewContext = require.s.newContext;
            require.s.newContext = function (name)
            {
                var newContext = oldNewContext(name);
                setupAutoConfigureContext(newContext);
                return newContext;
            }
        }

        initAutoConfigureModules();

        var startupData = {};
        if (document.getElementById('instanteStartupJS') !== null) {
            var jsonString = document.getElementById('instanteStartupJS').innerHTML;
            startupData = JSON.parse(jsonString);
        }

        this.exec(startupData);
    };

});