<?php defined('_JEXEC') or die('Restricted access'); // no direct access ?>

    <div class="articleintro<?php echo $params->get( 'moduleclass_sfx' ) ?>">
    <?php foreach ($items as $item) {
       echo $item->introtext;
     }?>
    </div>
