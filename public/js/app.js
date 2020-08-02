let timeoutId = null;

if(navigator.userAgent.indexOf("MSIE") != -1) {
    document.onreadystatechange = function () {
        if (document.readyState === "interactive") {
            initApplication();
        }
    }
} else {
    window.addEventListener('DOMContentLoaded', initApplication);
}

function initApplication(e){
    if (document.getElementById('sceditor')) {
        var textarea = document.getElementById('sceditor');
        sceditor.create(textarea, {
            toolbar: 'bold,italic|bulletlist,orderedlist|table|code|horizontalrule,image|removeformat',
            format: 'bbcode',
            width: '74%',
            height: '400px',
            style: 'sceditor/themes/content/default.min.css'
        });
    }
    
    if (rubricId = document.getElementById('rubricId')) {
        rubricId.addEventListener('change', getChapterList,false);
    }
    document.addEventListener('click', function(event) {
        if (event.target.parentElement.classList.contains("btn")) {
            changeInput(event.target);
        }
    });
    addEventListenerAll(document.querySelectorAll(".link"), "click", snapToSection);
    if (document.querySelectorAll('.articles').length > 0 ) {
        window.addEventListener("scroll", scrolling, false);
    }
    
}
function scrolling(e) {
    url = window.location.href;
    destination = getDestination(url.substring(url.indexOf("#")+1));
    if (window.scrollY > destination) {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setInterval(function(){snap(destination);}, 10);
        window.removeEventListener("scroll", scrolling, false);
    }
}

function addEventListenerAll(list, eventType, callback) {
    for (let i=0; i < list.length; i++) {
        list[i].addEventListener(eventType,callback, true);
    }
}
function snapToSection(e) {
    
    e.preventDefault();
    let href = e.target.href; 
    window.location.href = href;
	let destination = getDestination(href.substring(href.indexOf("#")+1));
        console.log(destination);
	if (timeoutId) clearTimeout(timeoutId);
	timeoutId = setInterval(function(){snap(destination);}, 10);
}
function getDestination(id) {
	let articles = document.querySelectorAll(".article-container");
    let destination = document.querySelector("h1").clientHeight;
    //let destination = document.querySelector("header").clientHeight - document.querySelector("h1").clientHeight;
	for(let i = 0; i < articles.length; i++) {
		if (articles[i].id == id) {
            if (i == 0) destination = 0;
            return destination;
		}
		destination += (articles[i].clientHeight+30);
	}
}

function snap(destination) {
	var bottomLimit = document.querySelector("body").clientHeight - window.innerHeight+120;
	if (destination > bottomLimit) {destination = bottomLimit;}
	if (destination < 0) {destination = 0;}
	var step;
	if(window.pageYOffset==destination){clearTimeout(timeoutId);}
	if (Math.abs(destination - window.pageYOffset)>=20) {
		step = 20;
	} else {
		step = Math.abs(destination - window.pageYOffset);
	}
	if ((destination - window.pageYOffset) < 0) {
		window.scrollBy(0, - step);
	} else if ((destination - window.pageYOffset) > 0) {
		window.scrollBy(0, step);
	}
}
function changeInput(target, formGroup = null, select = null) {
    if ( null != target) {
        formGroup = target.parentElement.parentElement;
        select = formGroup.querySelector('select');
    } else {
        target = formGroup.querySelector('I');
    }
    if (target.classList.contains('fa-plus-square')) {
        select.classList.add("hidden");
        formGroup.querySelector('input[type="text"]').classList.remove("hidden");
        target.classList.remove('fa-plus-square');
        target.classList.add('fa-minus-square');
    } else {
        select.classList.remove("hidden");
        input = formGroup.querySelector('input[type="text"]');
        input.classList.add("hidden");
        input.value = '';
        target.classList.add('fa-plus-square');
        target.classList.remove('fa-minus-square');
    }
    if (select.id == 'rubricId') {
        select = document.getElementById("chapterId");
        formGroup = select.parentElement;
        changeInput(null, formGroup, select);
    }
}

function getChapterList(event) {
    if (event) {
        var data = {'rubricId': event.target.value}
    } else {
        var data = {'rubricId': 1}
    }
    processing(data);
}

function setChapterSelect(options) {
    var selectChapter = document.getElementById('chapterId');
    while (selectChapter.firstChild) {
        selectChapter.firstChild.remove();
    }
    for (var key in options) {
        var option = document.createElement("option");
        option.value = options[key].chapter_id;
        option.text = options[key].chapter_title;
        selectChapter.add(option);
    }
}


function processing(data){
    var formData = new FormData();
    var process = "./process/process_ajax.php";
    var refresh;
    if (data['parentId']) {
        var formElement = document.getElementById(data['parentId']);
        process = formElement.action;
        formData.append('submit', data['submit']);
        for (i=0; i < formElement.length; i++) {
            if ((formElement[i].type != 'submit' && formElement[i].value) || (formElement[i].type == 'submit' && formElement[i].name==data['submitName'])) {
                formData.append(formElement[i].name, formElement[i].value);
            }
        }
    } else {
        for (var key in data) {
            if (key != 'process') {
                formData.append(key, data[key]);
            } else {
                if (data.process.includes("../process/process_")) {
                    process = data.process;
                } else {
                    process = "../process/process_" + data.process+".php";
                }
            }
        }
    }
    
    var xhr;
    if (window.XMLHttpRequest) { // Mozilla, Konqueror/Safari, IE7 ...
        xhr = new XMLHttpRequest();
    } else if (window.ActiveXObject) { // Internet Explorer 6
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', process, true);
    //xhr.responseType = 'json';
    xhr.send(formData);
    xhr.addEventListener('readystatechange', function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status == 200) {
            var responseJson = JSON.parse(this.response);
            console.log(responseJson);
            setChapterSelect(responseJson.chapterList);
        }
    });
}