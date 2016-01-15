/* File moved... this is for backward compatibility */
var all_scripts = document.getElementsByTagName("script");
if ( all_scripts.length ) {
	nn_script_root = all_scripts[all_scripts.length-1].src.replace( /[^\/]*\.js$/, '' );
	document.write('<script src="'+nn_script_root+'toggler.js" type="text/JavaScript"><\/script>');
}