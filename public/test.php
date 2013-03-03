<style type="text/css">
    div {
        /*width: 20px;*/
        display: inline-block;
    }
</style>

<center>
    <?php

    $number = 80;

    for ($i = 0; $i < $number; $i++) {
        if ($i > 9) {
            $j = 4 * ($i + round($i / 8));
        } else {
            $j = $i;
        }
        for ($a = 0; $a < $number - $j; $a++) {
            echo $i;
        }
        echo '<br/>';
    }
    ?>
</center>