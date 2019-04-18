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
	if (node.classList.contains('empty') && node.textContent) {
		node.classList.remove('empty');
		if (!node.className) node.removeAttribute('class');
	} else if (!node.textContent || node.innerHTML.match(/^(\<br)/)) {
		node.classList.add('empty');
	}
}

function directiveNode(node) {
	if (!node || node.nodeName.match(/body|html/i) || !node.textContent.match(/@/)) return;
	if (!node.textContent.match(/@(end|block|component)/i)) return;
	var content = node.textContent.trim();
	if (content.match(/@/g).length > 1) {
		var c = content.split('@');
		for (var i = 0; i < c.length; i++) {
			var str = c[i].trim();
			var klass = ''; //'--directive';
			if (str.match(/^(endcomponent|component)/i)) {
				klass = '--component';
			} else if (str.match(/^(endblock|block)/i)) {
				klass = '--block';
			}
			if (str && klass) node.insertAdjacentHTML('beforebegin','<p class="'+klass+'">@'+str+'</p>');
		}
		node.parentNode.removeChild(node);
	} else {
		var klass = ''; //'--directive';
		if (content.match(/^@(endcomponent|component)/i)) {
			klass = '--component';
		} else if (content.match(/^@(endblock|block)/i)) {
			klass = '--block';
		}
		if (!klass || node.classList.contains(klass)) return;
		node.classList.add(klass);
	}
}
