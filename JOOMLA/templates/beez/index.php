<?php
error_reporting(0);

defined('_JEXEC') or die('Restricted access');
$url = clone(JURI::getInstance());
$conf = & JFactory::getConfig();
$sitename = $conf->getValue('config.sitename');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
    <jdoc:include type="head" />
    <link rel="stylesheet" href="templates/beez/css/reset.css" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Arimo">
    <link rel="stylesheet" href="templates/beez/css/template.css" type="text/css" />    
    <link rel="stylesheet" href="templates/beez/css/bootstrap-shb.css" type="text/css" />
    <meta name="google-site-verification" content="f985k3caYd1RMG2bhLX8P_ylXHIxCLtmZ_D_Yqa5Ls0" />
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="ui-wrapper">
                <jdoc:include type="modules" name="top" style="beezDivision" headerlevel="3"/>
            </div>
        </div>
        <div id="nav" class="ui-wrapper ui-corner-all">
            <jdoc:include type="modules" name="nav" style="beezDivision" headerlevel="3"/>
        </div>

        <div id="content" class="ui-wrapperbody">
            <?php if ($this->countModules('home') > 0) : ?>
                <div id="contentHome" class="clearfix ui-corner-all">
                    <jdoc:include type="modules" name="home" style="beezDivision" headerlevel="3"/>
                </div>
            <?php else : ?>
                <div id="contentInner" class="clearfix ui-corner-all">
                    <div id="leftWidget" class="ui-corner-left">
                        <jdoc:include type="component" />
                    </div>
                    <div id="rightPanel">
                        <jdoc:include type="modules" name="right" style="beezDivision" headerlevel="3"/>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <jdoc:include type="modules" name="footer" />
    <?php if ($this->getBuffer('message')) : ?>
        <div id="jmessage">
            <h2><?php echo JText::_('Message'); ?></h2>
            <div class="jmessage"><jdoc:include type="message" /></div>
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('debug')) { ?>
        <div id="debug">
            <jdoc:include type="modules" name="debug" />
        </div>
    <?php } ?>
    <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-27792537-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

    </script>

<?php	
	
?>	
	</body>
</html>