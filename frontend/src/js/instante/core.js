define('instante/core', ['jquery', 'window', 'nette/core', 'nette/ajax'], function (jQuery, window)
{
    var $ = jQuery;
    var Instante = this;

    var initNetteAjax = function ()
    {
        $.nette.ext('unique', null);
        var nInit = $.nette.ext('init');
        nInit.linkSelector = 'a[data-nette-ajax]';
        nInit.formSelector = 'form[data-nette-ajax]';
        nInit.buttonSelector
            = 'input[type="image"][data-nette-ajax],input[type="submit"][data-nette-ajax],button[data-nette-ajax]';
        $.nette.init();
    }
    /**
     * Custom snippet update API.
     *
     * To add custom snippet update handler for presenter snippet, use:
     * window.Instante.customSnippetHandlers.snippetName = function ($el, html, back) {...}
     * for control snippet, use:
     * window.Instante.customSnippetHandlers.control(controlPath).snippetName = function ($el, html, back) {...}
     * The custom handler should return true if the default handler should also be called afterwards.
     */
    var initCustomSnippetHandlers = function ()
    {
        Instante.customSnippetHandlers = {
            ____controls: {},
            control: function (ident)
            {
                if (typeof this.____controls[ident] === 'undefined') {
                    this.____controls[ident] = {};
                }
                return this.____controls[ident];
            }
        };
        $.nette.ext('snippets').oldUpdateSnippet = $.nette.ext('snippets').updateSnippet;
        $.nette.ext('snippets').updateSnippet = function ($el, html, back)
        {
            var custHandlers = Instante.customSnippetHandlers;
            var snippetId = $el.attr('id').match(/^snippet-(.*)-([^-]*)$/);
            if (snippetId[1] !== '') {
                custHandlers = custHandlers.control(snippetId[1]);
            }
            //concrete snippet handler has higher priority, then * handler comes
            var handlerId = typeof custHandlers[snippetId[2]] !== 'undefined' ? snippetId[2] : '*';
            if (typeof custHandlers[handlerId] === 'undefined' || custHandlers[handlerId]($el, html, back)) {
                $.nette.ext('snippets').oldUpdateSnippet($el, html, back);
            }
        }
    }

    this.DOM = this.DOM || {};
    this.DOM.copyAttribute = function ($source, $target, attr, selector)
    {
        if (typeof selector !== 'undefined') {
            $source = $source.find(selector);
            $target = $target.find(selector);
        }
        $target.attr(attr, $source.attr(attr));
    };

    this.DOM.copyClassState = function ($source, $target, className, selector)
    {
        if (typeof selector !== 'undefined') {
            $source = $source.find(selector);
            $target = $target.find(selector);
        }
        if ($source.hasClass(className)) {
            $target.addClass(className);
        }
        else {
            $target.removeClass(className);
        }
    };

    this.DOM.copyNode = function ($source, $target, selector)
    {
        $target.find(selector).html($source.find(selector).html());
    };
    $(function ()
    {
        initNetteAjax();
        initCustomSnippetHandlers();
    });
    return (window.Instante = this);
});
