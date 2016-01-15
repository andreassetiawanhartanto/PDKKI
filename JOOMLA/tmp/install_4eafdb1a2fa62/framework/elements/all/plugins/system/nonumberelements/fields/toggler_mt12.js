// Redirect to new NoNumber! Framework
var all_scripts = document.getElementsByTagName("script");
if ( all_scripts.length ) {
	nn_script = all_scripts[all_scripts.length-1].src.replace( 'nonumberelements', 'nnframework' );
	document.write('<script src="'+nn_script+'" type="text/JavaScript"><\/script>');
}