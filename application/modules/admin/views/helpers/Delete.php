<?php

class Admin_View_Helper_Delete extends Zend_View_Helper_Abstract
{

    public function delete($ask)
    {
        if ($ask): ?>
            <p>
                <?php echo $this->view->translate('Deleting an item needs to be confirmed. Are you sure you want to delete it?'); ?>
                <a class="button"
                   href="<?php echo $this->view->url(array('yes' => 1)); ?>"><?php echo $this->view->translate('Yes') ?></a>
            </p>
        <?php endif;
    }

}
