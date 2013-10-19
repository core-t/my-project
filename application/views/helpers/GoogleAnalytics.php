<?php

class Zend_View_Helper_GoogleAnalytics extends Zend_View_Helper_Abstract {

    public function googleAnalytics() {
        if (APPLICATION_ENV != 'production') {
            return;
        }

        $script = "
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-38324299-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
";
        $this->view->headScript()->appendScript($script);
    }

}

