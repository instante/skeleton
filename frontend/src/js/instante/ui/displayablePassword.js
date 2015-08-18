define('instante/ui/displayablePassword', ['jquery', '$document'], function($, $document)
{
    return new function()
    {
        /**
         * @param $element jQuery object or selector
         */
        this.init = function($element)
        {
            if ($element === undefined) {
                $element = $document;
            } else {
                $element = $($element); //ensure jQuery object from selector
            }
            $(function()
            {
                $element.on('mousedown', '[data-instante-displayable-password] .btn', function()
                {
                    $(this).closest('[data-instante-displayable-password]').find('input').attr('type', 'text');
                })
                    .on('mouseup mouseleave', '[data-instante-displayable-password] .btn', function()
                    {
                        $(this).closest('[data-instante-displayable-password]').find('input').attr('type', 'password');
                    });
            });
        };
    };
});
