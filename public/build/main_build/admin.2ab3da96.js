/*! For license information please see admin.2ab3da96.js.LICENSE.txt */
(self.webpackChunk=self.webpackChunk||[]).push([[328],{4819:(t,i,e)=>{"use strict";e(9139),e(7635),e(1302),e(9822),e(3616),e(4245),e(7562),e(1654),e(5758),e(2824)},2824:(t,i,e)=>{function n(t){t.preventDefault();var i=$(this).attr("href");navigator.clipboard.writeText(i)}function o(t){t.preventDefault();var i=t.target.getAttribute("href");fetch(i).then((function(t){return t.json()})).then((function(t){console.log(t),navigator.clipboard.writeText(t)}))}e(1539),e(8674),$(document).ready((function(){$(document).on("click",'a[data-clipboard="1"]',n),$(document).on("click",".email-to-clipboard",o)}))},1654:(t,i,e)=>{function n(t){var i=t.target,e=i.parentElement.querySelector('[data-target="'+i.dataset.toggle+'"]'),n=i.classList.contains("active");if(o(),!n){i.classList.toggle("active"),e.classList.toggle("active");var r=i.getBoundingClientRect().top,s=window.innerHeight-r>e.offsetHeight?"active-top":"active-bottom";e.classList.toggle(s)}}function o(){console.log("hideDropdown"),document.querySelectorAll(".dropdown .dropdown-menu.active, button.dropdown-toggle.active").forEach((function(t){t.classList.remove("active"),t.classList.remove("active-top"),t.classList.remove("active-bottom")}))}e(9554),e(1539),e(4747),$(document).ready((function(){$(document).on("click",'button:not(.dropdown-toggle), a[data-toggle="modal"], a.dropdown-item',o),$(document).on("click","button.dropdown-toggle",n)}))},4245:(t,i,e)=>{function n(t){return n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},n(t)}function o(){"use strict";o=function(){return i};var t,i={},e=Object.prototype,r=e.hasOwnProperty,s=Object.defineProperty||function(t,i,e){t[i]=e.value},a="function"==typeof Symbol?Symbol:{},h=a.iterator||"@@iterator",c=a.asyncIterator||"@@asyncIterator",u=a.toStringTag||"@@toStringTag";function l(t,i,e){return Object.defineProperty(t,i,{value:e,enumerable:!0,configurable:!0,writable:!0}),t[i]}try{l({},"")}catch(t){l=function(t,i,e){return t[i]=e}}function f(t,i,e,n){var o=i&&i.prototype instanceof w?i:w,r=Object.create(o.prototype),a=new I(n||[]);return s(r,"_invoke",{value:Y(t,e,a)}),r}function d(t,i,e){try{return{type:"normal",arg:t.call(i,e)}}catch(t){return{type:"throw",arg:t}}}i.wrap=f;var p="suspendedStart",g="suspendedYield",v="executing",y="completed",m={};function w(){}function b(){}function x(){}var E={};l(E,h,(function(){return this}));var L=Object.getPrototypeOf,O=L&&L(L(_([])));O&&O!==e&&r.call(O,h)&&(E=O);var P=x.prototype=w.prototype=Object.create(E);function j(t){["next","throw","return"].forEach((function(i){l(t,i,(function(t){return this._invoke(i,t)}))}))}function k(t,i){function e(o,s,a,h){var c=d(t[o],t,s);if("throw"!==c.type){var u=c.arg,l=u.value;return l&&"object"==n(l)&&r.call(l,"__await")?i.resolve(l.__await).then((function(t){e("next",t,a,h)}),(function(t){e("throw",t,a,h)})):i.resolve(l).then((function(t){u.value=t,a(u)}),(function(t){return e("throw",t,a,h)}))}h(c.arg)}var o;s(this,"_invoke",{value:function(t,n){function r(){return new i((function(i,o){e(t,n,i,o)}))}return o=o?o.then(r,r):r()}})}function Y(i,e,n){var o=p;return function(r,s){if(o===v)throw new Error("Generator is already running");if(o===y){if("throw"===r)throw s;return{value:t,done:!0}}for(n.method=r,n.arg=s;;){var a=n.delegate;if(a){var h=X(a,n);if(h){if(h===m)continue;return h}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(o===p)throw o=y,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);o=v;var c=d(i,e,n);if("normal"===c.type){if(o=n.done?y:g,c.arg===m)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(o=y,n.method="throw",n.arg=c.arg)}}}function X(i,e){var n=e.method,o=i.iterator[n];if(o===t)return e.delegate=null,"throw"===n&&i.iterator.return&&(e.method="return",e.arg=t,X(i,e),"throw"===e.method)||"return"!==n&&(e.method="throw",e.arg=new TypeError("The iterator does not provide a '"+n+"' method")),m;var r=d(o,i.iterator,e.arg);if("throw"===r.type)return e.method="throw",e.arg=r.arg,e.delegate=null,m;var s=r.arg;return s?s.done?(e[i.resultName]=s.value,e.next=i.nextLoc,"return"!==e.method&&(e.method="next",e.arg=t),e.delegate=null,m):s:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,m)}function S(t){var i={tryLoc:t[0]};1 in t&&(i.catchLoc=t[1]),2 in t&&(i.finallyLoc=t[2],i.afterLoc=t[3]),this.tryEntries.push(i)}function z(t){var i=t.completion||{};i.type="normal",delete i.arg,t.completion=i}function I(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(S,this),this.reset(!0)}function _(i){if(i||""===i){var e=i[h];if(e)return e.call(i);if("function"==typeof i.next)return i;if(!isNaN(i.length)){var o=-1,s=function e(){for(;++o<i.length;)if(r.call(i,o))return e.value=i[o],e.done=!1,e;return e.value=t,e.done=!0,e};return s.next=s}}throw new TypeError(n(i)+" is not iterable")}return b.prototype=x,s(P,"constructor",{value:x,configurable:!0}),s(x,"constructor",{value:b,configurable:!0}),b.displayName=l(x,u,"GeneratorFunction"),i.isGeneratorFunction=function(t){var i="function"==typeof t&&t.constructor;return!!i&&(i===b||"GeneratorFunction"===(i.displayName||i.name))},i.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,x):(t.__proto__=x,l(t,u,"GeneratorFunction")),t.prototype=Object.create(P),t},i.awrap=function(t){return{__await:t}},j(k.prototype),l(k.prototype,c,(function(){return this})),i.AsyncIterator=k,i.async=function(t,e,n,o,r){void 0===r&&(r=Promise);var s=new k(f(t,e,n,o),r);return i.isGeneratorFunction(e)?s:s.next().then((function(t){return t.done?t.value:s.next()}))},j(P),l(P,u,"Generator"),l(P,h,(function(){return this})),l(P,"toString",(function(){return"[object Generator]"})),i.keys=function(t){var i=Object(t),e=[];for(var n in i)e.push(n);return e.reverse(),function t(){for(;e.length;){var n=e.pop();if(n in i)return t.value=n,t.done=!1,t}return t.done=!0,t}},i.values=_,I.prototype={constructor:I,reset:function(i){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(z),!i)for(var e in this)"t"===e.charAt(0)&&r.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=t)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(i){if(this.done)throw i;var e=this;function n(n,o){return a.type="throw",a.arg=i,e.next=n,o&&(e.method="next",e.arg=t),!!o}for(var o=this.tryEntries.length-1;o>=0;--o){var s=this.tryEntries[o],a=s.completion;if("root"===s.tryLoc)return n("end");if(s.tryLoc<=this.prev){var h=r.call(s,"catchLoc"),c=r.call(s,"finallyLoc");if(h&&c){if(this.prev<s.catchLoc)return n(s.catchLoc,!0);if(this.prev<s.finallyLoc)return n(s.finallyLoc)}else if(h){if(this.prev<s.catchLoc)return n(s.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<s.finallyLoc)return n(s.finallyLoc)}}}},abrupt:function(t,i){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc<=this.prev&&r.call(n,"finallyLoc")&&this.prev<n.finallyLoc){var o=n;break}}o&&("break"===t||"continue"===t)&&o.tryLoc<=i&&i<=o.finallyLoc&&(o=null);var s=o?o.completion:{};return s.type=t,s.arg=i,o?(this.method="next",this.next=o.finallyLoc,m):this.complete(s)},complete:function(t,i){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&i&&(this.next=i),m},finish:function(t){for(var i=this.tryEntries.length-1;i>=0;--i){var e=this.tryEntries[i];if(e.finallyLoc===t)return this.complete(e.completion,e.afterLoc),z(e),m}},catch:function(t){for(var i=this.tryEntries.length-1;i>=0;--i){var e=this.tryEntries[i];if(e.tryLoc===t){var n=e.completion;if("throw"===n.type){var o=n.arg;z(e)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(i,e,n){return this.delegate={iterator:_(i),resultName:e,nextLoc:n},"next"===this.method&&(this.arg=t),m}},i}function r(t,i,e,n,o,r,s){try{var a=t[r](s),h=a.value}catch(t){return void e(t)}a.done?i(h):Promise.resolve(h).then(n,o)}function s(t,i){return function(t){if(Array.isArray(t))return t}(t)||function(t,i){var e=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=e){var n,o,r,s,a=[],h=!0,c=!1;try{if(r=(e=e.call(t)).next,0===i){if(Object(e)!==e)return;h=!1}else for(;!(h=(n=r.call(e)).done)&&(a.push(n.value),a.length!==i);h=!0);}catch(t){c=!0,o=t}finally{try{if(!h&&null!=e.return&&(s=e.return(),Object(s)!==s))return}finally{if(c)throw o}}return a}}(t,i)||function(t,i){if(!t)return;if("string"==typeof t)return a(t,i);var e=Object.prototype.toString.call(t).slice(8,-1);"Object"===e&&t.constructor&&(e=t.constructor.name);if("Map"===e||"Set"===e)return Array.from(t);if("Arguments"===e||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(e))return a(t,i)}(t,i)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,i){(null==i||i>t.length)&&(i=t.length);for(var e=0,n=new Array(i);e<i;e++)n[e]=t[e];return n}function h(t,i){if(!(t instanceof i))throw new TypeError("Cannot call a class as a function")}function c(t,i){for(var e=0;e<i.length;e++){var n=i[e];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,f(n.key),n)}}function u(t,i,e){return i&&c(t.prototype,i),e&&c(t,e),Object.defineProperty(t,"prototype",{writable:!1}),t}function l(t,i,e){return(i=f(i))in t?Object.defineProperty(t,i,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[i]=e,t}function f(t){var i=function(t,i){if("object"!==n(t)||null===t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var o=e.call(t,i||"default");if("object"!==n(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===i?String:Number)(t)}(t,"string");return"symbol"===n(i)?i:String(i)}e(9554),e(1539),e(4747),e(8309),e(9720),e(2564),e(6992),e(8783),e(3948),e(285),e(1637),e(7941),e(8862),e(6649),e(6078),e(2526),e(1817),e(1703),e(6647),e(9653),e(9070),e(9753),e(2165),e(7658),e(7042),e(3710),e(9714),e(1038),e(4916),e(7601),e(8674),e(2443),e(3680),e(3706),e(2703),e(8011),e(489),e(8304),e(5069),document.addEventListener("DOMContentLoaded",(function(){document.querySelectorAll(".media-position").forEach((function(t){new d(t)}))}),!1);var d=function(){"use strict";function t(i){var e=this;h(this,t),l(this,"canvas",void 0),l(this,"context",void 0),l(this,"reader",void 0),l(this,"image",new Image),l(this,"ratio",1),l(this,"startX",0),l(this,"startY",0),l(this,"currentY",0),l(this,"currentX",0),l(this,"zones",[]),l(this,"imagePosition",void 0),l(this,"display",{}),l(this,"gap",10),l(this,"fileInput",void 0),this.display={},this.canvas=i,this.context=i.getContext("2d"),this.ratio,this.reader=new FileReader,this.image.src=i.dataset.src;var n=$(i).closest("form")[0].name;this.image.onload=this.initialize();this.fileInput=document.getElementById(n+"_backgroundFile"),this.fileInput.onchange=function(t){return e.changeImage()}}return u(t,[{key:"initialize",value:function(t){var i=this;this.display.width=this.image.width>this.image.height?400:225,this.zones=[{name:"landscape",outputWidth:1920,outputHeight:1080},{name:"portrait",outputWidth:1080,outputHeight:1920},{name:"square",outputWidth:850,outputHeight:850}],this.ratio=this.display.width/this.image.width,this.display.height=this.image.height*this.ratio,Object.entries(this.zones).forEach((function(t){var e=s(t,2),n=e[0],o=e[1];return i.zones[n].zone=new p(i,o)})),this.image.width>this.image.height?(this.canvas.width=this.image.width*this.ratio,this.canvas.height=this.image.height*this.ratio*this.zones.length,Object.entries(this.zones).forEach((function(t){var e=s(t,2),n=e[0];return e[1].zone.setOrigin(0,(i.display.height+i.gap)*n)}))):(this.canvas.width=this.image.width*this.ratio*this.zones.length,this.canvas.height=this.image.height*this.ratio,Object.entries(this.zones).forEach((function(t){var e=s(t,2),n=e[0];return e[1].zone.setOrigin((i.display.width+i.gap)*n,0)}))),this.imagePosition=$("#"+this.prefix+"_mediaPositionY"),this.run()}},{key:"run",value:function(){console.log("this",this),this.mouseEvents(),self=this,this.interval=setInterval((function(){self.drawImage(),Object.entries(self.zones).forEach((function(t){var i=s(t,2),e=(i[0],i[1]);return self.drawZone(e.zone)}))}),1e3/30)}},{key:"drawImage",value:function(){var t=this;Object.entries(this.zones).forEach((function(i){var e=s(i,2),n=(e[0],e[1]);return t.context.drawImage(t.image,0,0,t.image.width,t.image.height,n.zone.origin.x,n.zone.origin.y,t.display.width,t.display.height)}))}},{key:"mouseEvents",value:function(){self=this,this.canvas.onmousedown=function(t){var i=t.pageY-t.target.offsetTop,e=t.pageX-t.target.offsetLeft;Object.entries(self.zones).forEach((function(t){var n=s(t,2);n[0];return n[1].zone.StartDraggable(e,i)}))},this.canvas.onmousemove=function(t){self.currentY=t.pageY-t.target.offsetTop,self.currentX=t.pageX-t.target.offsetLeft,Object.entries(self.zones).forEach((function(t){var i=s(t,2);i[0];return i[1].zone.move(self)}))},document.onmouseup=function(t){Object.entries(self.zones).forEach((function(t){var i=s(t,2);i[0];return i[1].zone.endDraggable()}))}}},{key:"drawZone",value:function(t){this.context.fillStyle="rgba(0, 0, 0, 0.7)",this.context.fillRect(t.origin.x,t.origin.y,this.display.width,t.positionY),this.context.fillRect(t.origin.x,t.positionY+t.height,this.display.width,this.display.height-t.height-t.positionY),this.context.fillRect(t.origin.x,t.origin.y,t.positionX,this.display.height),this.context.fillRect(t.origin.x+t.positionX+t.width,t.origin.y,this.display.width-t.width-t.positionX,this.display.height)}},{key:"changeImage",value:function(){this.image=new Image,this.image.src=URL.createObjectURL(this.fileInput.files[0]);var t=new FileReader;t.onload=function(){var t,i=(t=o().mark((function t(i){var e;return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return(e=new Image).src=i.target.result,t.next=4,e.decode();case 4:self.initialize(),Object.entries(self.zones).forEach((function(t){var i=s(t,2);return i[0],i[1].zone.defaultPositions(self.display)}));case 6:case"end":return t.stop()}}),t)})),function(){var i=this,e=arguments;return new Promise((function(n,o){var s=t.apply(i,e);function a(t){r(s,n,o,a,h,"next",t)}function h(t){r(s,n,o,a,h,"throw",t)}a(void 0)}))});return function(t){return i.apply(this,arguments)}}(),t.readAsDataURL(this.fileInput.files[0])}}]),t}(),p=function(){"use strict";function t(i,e){h(this,t),l(this,"isDraggable",!1),l(this,"width",void 0),l(this,"height",void 0),l(this,"ratio",void 0),l(this,"positionX",void 0),l(this,"positionY",void 0),l(this,"inputselector",void 0),l(this,"imagePositions",void 0),l(this,"origin",{}),l(this,"name",void 0),l(this,"mediaPosition",void 0),l(this,"startY",0),l(this,"startX",0),this.name=e.name,this.mediaPosition=i,e.outputWidth/e.outputHeight<i.display.width/i.display.height?this.ratio=i.display.height/e.outputHeight:this.ratio=i.display.width/e.outputWidth,this.inputselector=document.querySelector("#background_"+e.name+"Position"),this.imagePositions=this.inputselector&&""!==this.inputselector.value?JSON.parse(this.inputselector.value):{positionX:null,positionY:null},this.height=e.outputHeight*this.ratio,this.width=e.outputWidth*this.ratio,null!==this.imagePositions.positionX&&null!==this.imagePositions.positionY?(this.positionX=this.imagePositions.positionX*this.mediaPosition.ratio,this.positionY=this.imagePositions.positionY*this.mediaPosition.ratio):this.defaultPositions(),console.log("zone",this)}return u(t,[{key:"StartDraggable",value:function(t,i){this.startX=t-this.positionX,this.startY=i-this.positionY,i>=this.origin.y+this.positionY&&i<=this.origin.y+this.positionY+this.height&&t>=this.origin.x+this.positionX&&t<=this.origin.x+this.positionX+this.width&&(this.isDraggable=!0,document.body.style.cursor="move")}},{key:"setOrigin",value:function(t,i){this.origin={x:t,y:i}}},{key:"move",value:function(t){this.isDraggable&&(this.positionY=t.currentY-this.startY,this.positionX=t.currentX-this.startX,console.log("mediaPosition.currentX",t.currentX),console.log("this.startX",this.startX),console.log("positionY",this.positionY),this.positionY<0&&(this.positionY=0),this.positionY+this.height>=this.mediaPosition.display.height&&(this.positionY=this.mediaPosition.display.height-this.height),this.positionX<0&&(this.positionX=0),this.positionX+this.width>=this.mediaPosition.display.width&&(this.positionX=this.mediaPosition.display.width-this.width))}},{key:"endDraggable",value:function(){this.isDraggable&&(this.isDraggable=!1,document.body.style.cursor="auto",this.setPositions())}},{key:"setPositions",value:function(){this.inputselector.value=JSON.stringify({positionX:this.positionX/this.mediaPosition.ratio,positionY:this.positionY/this.mediaPosition.ratio}),console.log(this.inputselector.value)}},{key:"defaultPositions",value:function(){this.positionX=(this.mediaPosition.display.width-this.width)/2,this.positionY=(this.mediaPosition.display.height-this.height)/2,this.setPositions()}}]),t}()},7562:(t,i,e)=>{function n(t){var i=document.querySelector('label[for="'+t.target.id+'"]');t.target.dataset.switchOn&&t.target.dataset.switchOff&&(i.innerHTML=t.target.checked?t.target.dataset.switchOn:t.target.dataset.switchOff)}e(9554),e(1539),e(4747),$(document).ready((function(){document.querySelectorAll('.switch input[type="checkbox"]').forEach((function(t){return t.addEventListener("change",n)}))}))},7941:(t,i,e)=>{"use strict";var n=e(2109),o=e(7908),r=e(1956);n({target:"Object",stat:!0,forced:e(7293)((function(){r(1)}))},{keys:function(t){return r(o(t))}})}},t=>{t.O(0,[808,885],(()=>{return i=4819,t(t.s=i);var i}));t.O()}]);