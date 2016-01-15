var jsCrawlerUrlsBatch = 15;

var jsCrawlerRunningTimer = null;
var jsCrawlerRunningCounter = 0;

var jsCrawlerMaxLevel = 0;
var jsCrawlerCurrentUrls = new Array();
var jsCrawlerNextUrls = new Array();
var jsCrawlerCrawledUrls = new Array();

var jsCrawlerFoundUrls = 0;

// Crawler's state
// 0 - before started
// 1 - running
// 2 - finished
var jsCrawlerState = 0;
var jsCrawlerRequestCancel = false;

function jsCrawlerUpdateRunning()
{
    jsCrawlerRunningCounter++;
    if (jsCrawlerRunningCounter > 3) {
        jsCrawlerRunningCounter = 0;
    }
    
    var s = '';
    for (var i = 0; i < jsCrawlerRunningCounter; i++) {
        s += ' .';
    }
    
    $('crawlerRunningValue').innerHTML = jsCrawlerTextRunning + s;
}

function jsCrawlerButtonClicked()
{
    if (jsCrawlerState == 0) {
        jsCrawlerStartCrawl();
    }
    else if (jsCrawlerState == 1) {
        jsCrawlerCancel();
    }
    else {
        jsCrawlerFinish();
    }
}

function jsCrawlerStartCrawl()
{
    jsCrawlerState = 1;
    
    var root = jsCrawlerRootUrl + $('crawlerRootUrl').value;
    jsCrawlerMaxLevel = $('crawlerMaxLevel').value;
    jsCrawlerCurrentUrls = new Array(root);
    jsCrawlerFoundUrls = 1;
    
    $('crawlerRootUrl').disabled = true;
    $('crawlerMaxLevel').disabled = true;
    //$('crawlerButton').disabled = true;
    $('crawlerButton').value = jsCrawlerTextCancel;
    $('crawlerRunningValue').innerHTML = jsCrawlerTextRunning;
    
    jsCrawlerRunningTimer = setInterval('jsCrawlerUpdateRunning()', 500);
    
    jsCrawlerCrawl(0);
}

function jsCrawlerFinish()
{
    submitform();
}

function jsCrawlerSuccess()
{
    jsCrawlerEnd();
    $('crawlerRunningValue').innerHTML = jsCrawlerTextSuccess;
    $('crawlerRunningValue').style.fontWeight = 'bold';
    $('crawlerRunningValue').style.color = 'green';
}

function jsCrawlerError()
{
    jsCrawlerEnd();
    $('crawlerRunningValue').innerHTML = jsCrawlerTextError;
    $('crawlerRunningValue').style.fontWeight = 'bold';
    $('crawlerRunningValue').style.color = 'red';
}

function jsCrawlerCancel()
{
    jsCrawlerRequestCancel = true;
    jsCrawlerEnd();
    $('crawlerRunningValue').innerHTML = jsCrawlerTextCancelled;
    $('crawlerRunningValue').style.fontWeight = 'bold';
    $('crawlerRunningValue').style.color = 'red';
}

function jsCrawlerEnd()
{
    jsCrawlerState = 2;
    clearInterval(jsCrawlerRunningTimer);
    $('crawlerButton').value = jsCrawlerTextFinish;
    //$('crawlerButton').disabled = false;
}

function jsCrawlerCrawl(level)
{
    // Update counts
    $('crawlerCrawledValue').innerHTML = jsCrawlerCrawledUrls.length;
    $('crawlerUrlsValue').innerHTML = jsCrawlerFoundUrls;
    
    // Check cancelled
    if (jsCrawlerRequestCancel) {
        return;
    }
    
    // Check level
    if (level > jsCrawlerMaxLevel) {
        jsCrawlerSuccess();
        return;
    }
    
    // Update level
    $('crawlerLevelValue').innerHTML = level + '/' + jsCrawlerMaxLevel;
    
    // Prepare URL
    var url = jsCrawlerScriptUrl;
    var max = (jsCrawlerCurrentUrls.length < jsCrawlerUrlsBatch) ? jsCrawlerCurrentUrls.length : jsCrawlerUrlsBatch;
    
    for (var i = 0; i < max; i++) {
        url += '&url[]='+encodeURIComponent(jsCrawlerCurrentUrls[i]);
    }
    
    // Call request
    var a = new Ajax(url, {
            method: 'get',
            onComplete: function(response) {
                try {
                    var data = Json.evaluate(response);
                }
                catch (err) {
                    jsCrawlerError();
                    return;
                }
                
                // Check cancelled
                if (jsCrawlerRequestCancel) {
                    return;
                }
                
                // Move crawled URLs to another array
                for (var i = 0; i < data.crawled; i++) {
                    var cur = jsCrawlerCurrentUrls.shift();
                    jsCrawlerCrawledUrls.push(cur);
                }
                
                // Handle found URLs if this is not last level
                if (level < jsCrawlerMaxLevel) {
                    for (var i = 0; i < data.found.length; i++) {
                        var cur = data.found[i];
                        
                        // Check if URL has already been crawled or is already scheduled to be crawled
                        if (jsCrawlerCrawledUrls.contains(cur) || jsCrawlerCurrentUrls.contains(cur) || jsCrawlerNextUrls.contains(cur)) {
                            continue;
                        }
                        
                        jsCrawlerNextUrls.push(cur);
                        jsCrawlerFoundUrls++;
                    }
                }
                
                // Check if there are any URLs left for current level
                if (jsCrawlerCurrentUrls.length > 0) {
                    // Continue with current level
                    jsCrawlerCrawl(level);
                }
                else {
                    // No more URLs, continue with next level
                    jsCrawlerCurrentUrls = jsCrawlerNextUrls;
                    jsCrawlerNextUrls = new Array();
                    jsCrawlerCrawl(level + 1);
                }
            }
    }).request();
}