/*
Wordpress editor scripts
*/

var editor = tinymce.activeEditor;

editor.on('init', function(e) {
	var nodes = e.target.dom.doc.querySelectorAll('*');
	nodes.forEach(function(node){
		emptyNodeClass(node);
	});
});

editor.on('NodeChange', function(e) {
	var node = e.element;
	emptyNodeClass(node);
	emptyNodeClass(node.previousSibling);
	emptyNodeClass(node.nextSibling);
});

function emptyNodeClass(node) {
	if (!node || !node.nodeName.match(/p/i)) return;
	if (node.classList.contains('empty') && node.textContent) {
		node.classList.remove('empty');
		if (!node.className) node.removeAttribute('class');
	} else if (!node.textContent || node.innerHTML.match(/^(\<br)/)) {
		node.classList.add('empty');
	}
}
