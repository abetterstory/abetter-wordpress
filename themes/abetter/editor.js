/*
Wordpress editor scripts
*/

var editor = tinymce.activeEditor;

editor.on('init', function(e) {
	var nodes = e.target.dom.doc.querySelectorAll('*');
	nodes.forEach(function(node){
		directiveNode(node);
		emptyNodeClass(node);
	});
});

editor.on('NodeChange', function(e) {
	var node = e.element;
	directiveNode(node);
	emptyNodeClass(node);
	emptyNodeClass(node.previousSibling);
	emptyNodeClass(node.nextSibling);
});

function emptyNodeClass(node) {
	if (!node || !node.nodeName.match(/p/i)) return;
	if (node.classList.contains('empty') && (node.textContent || node.innerHTML)) {
		node.classList.remove('empty');
		if (!node.className) node.removeAttribute('class');
	} else if ((!node.textContent && !node.innerHTML) || node.innerHTML.match(/^(\<br)/)) {
		node.classList.add('empty');
	}
}

function directiveNode(node) {
	if (!node || node.nodeName.match(/body|html/i) || !node.textContent.match(/@/)) return;
	if (!node.textContent.match(/@(end|block|classname|slot|component)/i)) return;
	var content = node.innerHTML.trim();
	if (content.match(/@/g).length > 1) {
		var parent = node.parentNode;
		var fix = content.replace(/@([^@<]+)/g,'<p>@$1</p>');
		node.insertAdjacentHTML('beforebegin',fix);
		parent.removeChild(node);
		for (var i = 0; i < parent.children.length; i++) {
			directiveNode(parent.children[i]);
		}
	} else {
		var klass = ''; //'--directive';
		if (content.match(/^@(endcomponent|component)/i)) {
			klass = '--component';
		} else if (content.match(/^@(endslot|slot)/i)) {
			klass = '--slot';
		} else if (content.match(/^@(classname)/i)) {
			klass = '--var';
		} else if (content.match(/^@(endblock|block)/i)) {
			klass = '--block';
		}
		if (!klass || node.classList.contains(klass)) return;
		node.classList.add(klass);
	}
}
