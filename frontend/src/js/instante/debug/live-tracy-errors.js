define('instante/debug/live-tracy-errors', ['nette/ajax', 'window', 'jquery'], function($nette, window, $)
{
    $nette.ext('ajaxErrors', {
        error: function(request)
        {
            var logFile = request.getResponseHeader('X-Tracy-Error-Log');
            var style = "position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;";
            if (logFile === null) {
                $('body').append('<div style="' + style + 'background-color:#fcc;text-align:center;color:#000;">'
                                 + 'Nette AJAX request failed with no X-Tracy-Error-Log header provided.'
                                 + '<br><a href="#" onclick="$(this).closest(\'div\').remove();return false;">close</a></div>');
            }
            else {
                var logPage = window.location + "/log/" + logFile;
                //alert(logPage);
                //logFile.substring(logFile.lastIndexOf('/')+1);
                $('body').append("<iframe style='" + style + "' src='" + logPage + "'></iframe>");
            }
        }
    });
});
