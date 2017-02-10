/**
 * Instante simple JS event object constructor.
 *
 * Example usage:
 *
 * <code>
 *  define(['instante/event'], function(Event) {
 *      var click = new Event();
 *      click.listen(function(arg) {
 *          console.log('clicked:', arg);
 *      });
 *      ...
 *      click.trigger('the big red button');
 *  });
 * </code>
 */
define(function()
{
    return function()
    {
        var handlers = [];
        this.trigger = function()
        {
            for (var k in handlers) {
                var handler = handlers[k];
                handler.apply(null, arguments);
            }
        };
        this.listen = function(handler)
        {
            handlers.push(handler);
        };
    };
});
