(function() {
    var image, imageW, imageH, imgPos, crosshair, input;
    var showCrosshair = false;
    
    function findPosition(oElement)
    {
        if(typeof( oElement.offsetParent ) != "undefined")
        {
            for(var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent)
            {
                posX += oElement.offsetLeft;
                posY += oElement.offsetTop;
            }
            return {x: posX, y: posY};
        }
        else
        {
            return {x: oElement.x, y: oElement.y };
        }
    }
    
    function registerFocalPoint(e)
    {
        if(!showCrosshair) {
            return;
        }
        var posX = 0;
        var posY = 0;
        if (!e) var e = window.event;
        if (e.pageX || e.pageY) {
            posX = e.pageX;
            posY = e.pageY;
        } else if (e.clientX || e.clientY) {
            posX = e.clientX + document.body.scrollLeft
                + document.documentElement.scrollLeft;
            posY = e.clientY + document.body.scrollTop
                + document.documentElement.scrollTop;
        }
    
        posX = posX - imgPos.x;
        posY = posY - imgPos.y;
        
        var relX = Math.round((posX / imageW) * 100) / 100;
        var relY = Math.round((posY / imageH) * 100) / 100;
        
        input.value = relX + ',' + relY;
        
        displayCrosshair();
        
    }
    
    function displayCrosshair()
    {
        var relPos = input.value.split(',');
        var posX = relPos[0] * imageW;
        var posY = relPos[1] * imageH;
    
        crosshair.style.left = (posX - 64) + 'px';
        crosshair.style.top = (posY - 64) + 'px';
        crosshair.style.display = 'block';
    }
    
    function getParam(name) {
        var url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }
    
    window.onload = function() {
        image = document.querySelector('.wp_attachment_holder .wp_attachment_image img');
        imgPos = findPosition(image);
        imageW = image.width;
        imageH = image.height;
        image.addEventListener('click', registerFocalPoint);
        crosshair = document.createElement('img');
        crosshair.setAttribute('src', assets.crosshair);
        crosshair.style.position = 'absolute';
        crosshair.style.display = 'none';
        crosshair.style.pointerEvents = 'none';
        image.parentNode.appendChild(crosshair);
        image.parentNode.style.position = 'relative';
        input = document.getElementById('attachments-' + getParam('post') + '-focal_point');
        input.setAttribute('readonly', '');
        if(!input.value) {
            input.value = '0.5,0.5';
        }
        var button = document.createElement('button');
        button.setAttribute('class', 'button');
        button.setAttribute('type', 'button');
        button.innerText = 'Toggle Focal Point Crosshair';
        button.addEventListener('click', function() {
            showCrosshair = !showCrosshair;
            if(showCrosshair) {
                displayCrosshair();
            } else {
                crosshair.style.display = 'none';
            }
        });
        input.parentNode.appendChild(button);
    }
})();
