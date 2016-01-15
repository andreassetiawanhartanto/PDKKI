<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 * @license     GNU/GPLv3 http://www.artio.net/license/gnu-general-public-license
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$style = $this->infoShown ? 'block' : 'none';
$linkText = $this->infoShown ? JText::_('COM_SEF_INFOTEXT_HIDE') : JText::_('COM_SEF_INFOTEXT_SHOW');
?>
<fieldset>
    <legend><?php echo JText::_('COM_SEF_INFOTEXT_TITLE'); ?> <a href="javascript:void(0)" onclick="jsToggleInfoText();"><span id="jsInfoTextLink" style="font-size: 70%;">(<?php echo $linkText; ?>)</span></a></legend>
    <div id="jsInfoText" style="display: <?php echo $style; ?>;">
        <?php echo $this->infoString; ?>
    </div>
</fieldset>
