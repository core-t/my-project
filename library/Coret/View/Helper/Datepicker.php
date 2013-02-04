<?php

class Coret_View_Helper_Datepicker extends Zend_View_Helper_Abstract {

    public function datepicker($nazwa) {
        $script = <<<EOF
$(document).ready(function () {
    $( '#$nazwa' ).datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth : true,
        changeYear : true,
        yearRange: '-100y:c+nn',
                    dayNamesMin: ['Nd','Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So'],
                    monthNamesShort: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
                    firstDay: 1,

    });
});
EOF;
        $this->view->headScript()->appendScript($script);
    }

}