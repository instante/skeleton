define('instante/configurator', [], function ()
{

    return new function ()
    {
        var modules = {};

        this.configure = function (moduleName, moduleConfig)
        {
            modules[moduleName] = moduleConfig;
        };

        this.getConfig = function (moduleName)
        {
            if (modules.hasOwnProperty(moduleName)) {
                return modules[moduleName];
            } else {
                return null;
            }
        }
    };

});