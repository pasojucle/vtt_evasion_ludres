/*! For license information please see admin.1e11c1ff.js.LICENSE.txt */
(self.webpackChunk=self.webpackChunk||[]).push([[328],{4819:(t,e,i)=>{"use strict";i(5177),i(7635),i(8203),i(9822),i(3616),i(4245),i(1654),i(5758)},1654:(t,e,i)=>{function n(t){var e=t.target,i=e.parentElement.querySelector('[data-target="'+e.dataset.toggle+'"]'),n=e.classList.contains("active");if(r(),!n){e.classList.toggle("active"),i.classList.toggle("active");var o=e.getBoundingClientRect().top,a=window.innerHeight-o>i.offsetHeight?"active-top":"active-bottom";i.classList.toggle(a)}}function r(){console.log("hideDropdown"),document.querySelectorAll(".dropdown .dropdown-menu.active, button.dropdown-toggle.active").forEach((function(t){t.classList.remove("active"),t.classList.remove("active-top"),t.classList.remove("active-bottom")}))}i(9554),i(1539),i(4747),$(document).ready((function(){$(document).on("click",'button:not(.dropdown-toggle), a[data-toggle="modal"], a.dropdown-item',r),$(document).on("click","button.dropdown-toggle",n)}))},7635:(t,e,i)=>{i(3710),jQuery((function(t){t.datepicker.regional.fr={closeText:"Fermer",prevText:"&#x3c;Préc",nextText:"Suiv&#x3e;",currentText:"Aujourd'hui",monthNames:["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"],monthNamesShort:["Jan","Fev","Mar","Avr","Mai","Jun","Jul","Aou","Sep","Oct","Nov","Dec"],dayNames:["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"],dayNamesShort:["Dim","Lun","Mar","Mer","Jeu","Ven","Sam"],dayNamesMin:["Di","Lu","Ma","Me","Je","Ve","Sa"],weekHeader:"Sm",dateFormat:"dd/mm/yy",timeFormat:"HH:mm",firstDay:1,isRTL:!1,showMonthAfterYear:!1,yearSuffix:"",minDate:"-12M +0D",maxDate:"+12M +0D",numberOfMonths:1,showButtonPanel:!1},t.datepicker.setDefaults(t.datepicker.regional.fr)})),$(document).ready((function(){$(".js-datepicker").each((function(){$(this).datepicker({format:"dd/mm/YYYY",maxDate:new Date($(this).data("max-date")),minDate:new Date($(this).data("min-date")),yearRange:$(this).data("year-range"),changeMonth:!0,changeYear:!0})}))}))},4245:(t,e,i)=>{function n(t){return n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},n(t)}function r(){"use strict";r=function(){return e};var t,e={},i=Object.prototype,o=i.hasOwnProperty,a=Object.defineProperty||function(t,e,i){t[e]=i.value},s="function"==typeof Symbol?Symbol:{},h=s.iterator||"@@iterator",u=s.asyncIterator||"@@asyncIterator",c=s.toStringTag||"@@toStringTag";function l(t,e,i){return Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{l({},"")}catch(t){l=function(t,e,i){return t[e]=i}}function f(t,e,i,n){var r=e&&e.prototype instanceof w?e:w,o=Object.create(r.prototype),s=new D(n||[]);return a(o,"_invoke",{value:Y(t,i,s)}),o}function d(t,e,i){try{return{type:"normal",arg:t.call(e,i)}}catch(t){return{type:"throw",arg:t}}}e.wrap=f;var p="suspendedStart",g="suspendedYield",v="executing",m="completed",y={};function w(){}function b(){}function x(){}var O={};l(O,h,(function(){return this}));var L=Object.getPrototypeOf,P=L&&L(L(I([])));P&&P!==i&&o.call(P,h)&&(O=P);var S=x.prototype=w.prototype=Object.create(O);function E(t){["next","throw","return"].forEach((function(e){l(t,e,(function(t){return this._invoke(e,t)}))}))}function j(t,e){function i(r,a,s,h){var u=d(t[r],t,a);if("throw"!==u.type){var c=u.arg,l=c.value;return l&&"object"==n(l)&&o.call(l,"__await")?e.resolve(l.__await).then((function(t){i("next",t,s,h)}),(function(t){i("throw",t,s,h)})):e.resolve(l).then((function(t){c.value=t,s(c)}),(function(t){return i("throw",t,s,h)}))}h(u.arg)}var r;a(this,"_invoke",{value:function(t,n){function o(){return new e((function(e,r){i(t,n,e,r)}))}return r=r?r.then(o,o):o()}})}function Y(e,i,n){var r=p;return function(o,a){if(r===v)throw new Error("Generator is already running");if(r===m){if("throw"===o)throw a;return{value:t,done:!0}}for(n.method=o,n.arg=a;;){var s=n.delegate;if(s){var h=k(s,n);if(h){if(h===y)continue;return h}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(r===p)throw r=m,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r=v;var u=d(e,i,n);if("normal"===u.type){if(r=n.done?m:g,u.arg===y)continue;return{value:u.arg,done:n.done}}"throw"===u.type&&(r=m,n.method="throw",n.arg=u.arg)}}}function k(e,i){var n=i.method,r=e.iterator[n];if(r===t)return i.delegate=null,"throw"===n&&e.iterator.return&&(i.method="return",i.arg=t,k(e,i),"throw"===i.method)||"return"!==n&&(i.method="throw",i.arg=new TypeError("The iterator does not provide a '"+n+"' method")),y;var o=d(r,e.iterator,i.arg);if("throw"===o.type)return i.method="throw",i.arg=o.arg,i.delegate=null,y;var a=o.arg;return a?a.done?(i[e.resultName]=a.value,i.next=e.nextLoc,"return"!==i.method&&(i.method="next",i.arg=t),i.delegate=null,y):a:(i.method="throw",i.arg=new TypeError("iterator result is not an object"),i.delegate=null,y)}function X(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function z(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function D(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(X,this),this.reset(!0)}function I(e){if(e||""===e){var i=e[h];if(i)return i.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var r=-1,a=function i(){for(;++r<e.length;)if(o.call(e,r))return i.value=e[r],i.done=!1,i;return i.value=t,i.done=!0,i};return a.next=a}}throw new TypeError(n(e)+" is not iterable")}return b.prototype=x,a(S,"constructor",{value:x,configurable:!0}),a(x,"constructor",{value:b,configurable:!0}),b.displayName=l(x,c,"GeneratorFunction"),e.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===b||"GeneratorFunction"===(e.displayName||e.name))},e.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,x):(t.__proto__=x,l(t,c,"GeneratorFunction")),t.prototype=Object.create(S),t},e.awrap=function(t){return{__await:t}},E(j.prototype),l(j.prototype,u,(function(){return this})),e.AsyncIterator=j,e.async=function(t,i,n,r,o){void 0===o&&(o=Promise);var a=new j(f(t,i,n,r),o);return e.isGeneratorFunction(i)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},E(S),l(S,c,"Generator"),l(S,h,(function(){return this})),l(S,"toString",(function(){return"[object Generator]"})),e.keys=function(t){var e=Object(t),i=[];for(var n in e)i.push(n);return i.reverse(),function t(){for(;i.length;){var n=i.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},e.values=I,D.prototype={constructor:D,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(z),!e)for(var i in this)"t"===i.charAt(0)&&o.call(this,i)&&!isNaN(+i.slice(1))&&(this[i]=t)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var i=this;function n(n,r){return s.type="throw",s.arg=e,i.next=n,r&&(i.method="next",i.arg=t),!!r}for(var r=this.tryEntries.length-1;r>=0;--r){var a=this.tryEntries[r],s=a.completion;if("root"===a.tryLoc)return n("end");if(a.tryLoc<=this.prev){var h=o.call(a,"catchLoc"),u=o.call(a,"finallyLoc");if(h&&u){if(this.prev<a.catchLoc)return n(a.catchLoc,!0);if(this.prev<a.finallyLoc)return n(a.finallyLoc)}else if(h){if(this.prev<a.catchLoc)return n(a.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return n(a.finallyLoc)}}}},abrupt:function(t,e){for(var i=this.tryEntries.length-1;i>=0;--i){var n=this.tryEntries[i];if(n.tryLoc<=this.prev&&o.call(n,"finallyLoc")&&this.prev<n.finallyLoc){var r=n;break}}r&&("break"===t||"continue"===t)&&r.tryLoc<=e&&e<=r.finallyLoc&&(r=null);var a=r?r.completion:{};return a.type=t,a.arg=e,r?(this.method="next",this.next=r.finallyLoc,y):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),y},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var i=this.tryEntries[e];if(i.finallyLoc===t)return this.complete(i.completion,i.afterLoc),z(i),y}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var i=this.tryEntries[e];if(i.tryLoc===t){var n=i.completion;if("throw"===n.type){var r=n.arg;z(i)}return r}}throw new Error("illegal catch attempt")},delegateYield:function(e,i,n){return this.delegate={iterator:I(e),resultName:i,nextLoc:n},"next"===this.method&&(this.arg=t),y}},e}function o(t,e,i,n,r,o,a){try{var s=t[o](a),h=s.value}catch(t){return void i(t)}s.done?e(h):Promise.resolve(h).then(n,r)}function a(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var i=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=i){var n,r,o,a,s=[],h=!0,u=!1;try{if(o=(i=i.call(t)).next,0===e){if(Object(i)!==i)return;h=!1}else for(;!(h=(n=o.call(i)).done)&&(s.push(n.value),s.length!==e);h=!0);}catch(t){u=!0,r=t}finally{try{if(!h&&null!=i.return&&(a=i.return(),Object(a)!==a))return}finally{if(u)throw r}}return s}}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return s(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);"Object"===i&&t.constructor&&(i=t.constructor.name);if("Map"===i||"Set"===i)return Array.from(t);if("Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i))return s(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function s(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,n=new Array(e);i<e;i++)n[i]=t[i];return n}function h(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function u(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,f(n.key),n)}}function c(t,e,i){return e&&u(t.prototype,e),i&&u(t,i),Object.defineProperty(t,"prototype",{writable:!1}),t}function l(t,e,i){return(e=f(e))in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}function f(t){var e=function(t,e){if("object"!==n(t)||null===t)return t;var i=t[Symbol.toPrimitive];if(void 0!==i){var r=i.call(t,e||"default");if("object"!==n(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===n(e)?e:String(e)}i(9554),i(1539),i(4747),i(8309),i(9720),i(2564),i(6992),i(8783),i(3948),i(285),i(1637),i(7941),i(8862),i(6649),i(6078),i(2526),i(1817),i(1703),i(6647),i(9653),i(9070),i(9753),i(2165),i(7658),i(7042),i(3710),i(9714),i(1038),i(4916),i(7601),i(8674),i(2443),i(3680),i(3706),i(2703),i(8011),i(489),i(8304),i(5069),document.addEventListener("DOMContentLoaded",(function(){document.querySelectorAll(".media-position").forEach((function(t){new d(t)}))}),!1);var d=function(){"use strict";function t(e){var i=this;h(this,t),l(this,"canvas",void 0),l(this,"context",void 0),l(this,"reader",void 0),l(this,"image",new Image),l(this,"ratio",1),l(this,"startX",0),l(this,"startY",0),l(this,"currentY",0),l(this,"currentX",0),l(this,"zones",[]),l(this,"imagePosition",void 0),l(this,"display",{}),l(this,"gap",10),l(this,"fileInput",void 0),this.display={},this.canvas=e,this.context=e.getContext("2d"),this.ratio,this.reader=new FileReader,this.image.src=e.dataset.src;var n=$(e).closest("form")[0].name;this.image.onload=this.initialize();this.fileInput=document.getElementById(n+"_backgroundFile"),this.fileInput.onchange=function(t){return i.changeImage()}}return c(t,[{key:"initialize",value:function(t){var e=this;this.display.width=this.image.width>this.image.height?400:225,this.zones=[{name:"landscape",outputWidth:1920,outputHeight:1080},{name:"portrait",outputWidth:1080,outputHeight:1920},{name:"square",outputWidth:850,outputHeight:850}],this.ratio=this.display.width/this.image.width,this.display.height=this.image.height*this.ratio,Object.entries(this.zones).forEach((function(t){var i=a(t,2),n=i[0],r=i[1];return e.zones[n].zone=new p(e,r)})),this.image.width>this.image.height?(this.canvas.width=this.image.width*this.ratio,this.canvas.height=this.image.height*this.ratio*this.zones.length,Object.entries(this.zones).forEach((function(t){var i=a(t,2),n=i[0];return i[1].zone.setOrigin(0,(e.display.height+e.gap)*n)}))):(this.canvas.width=this.image.width*this.ratio*this.zones.length,this.canvas.height=this.image.height*this.ratio,Object.entries(this.zones).forEach((function(t){var i=a(t,2),n=i[0];return i[1].zone.setOrigin((e.display.width+e.gap)*n,0)}))),this.imagePosition=$("#"+this.prefix+"_mediaPositionY"),this.run()}},{key:"run",value:function(){console.log("this",this),this.mouseEvents(),self=this,this.interval=setInterval((function(){self.drawImage(),Object.entries(self.zones).forEach((function(t){var e=a(t,2),i=(e[0],e[1]);return self.drawZone(i.zone)}))}),1e3/30)}},{key:"drawImage",value:function(){var t=this;Object.entries(this.zones).forEach((function(e){var i=a(e,2),n=(i[0],i[1]);return t.context.drawImage(t.image,0,0,t.image.width,t.image.height,n.zone.origin.x,n.zone.origin.y,t.display.width,t.display.height)}))}},{key:"mouseEvents",value:function(){self=this,this.canvas.onmousedown=function(t){var e=t.pageY-t.target.offsetTop,i=t.pageX-t.target.offsetLeft;Object.entries(self.zones).forEach((function(t){var n=a(t,2);n[0];return n[1].zone.StartDraggable(i,e)}))},this.canvas.onmousemove=function(t){self.currentY=t.pageY-t.target.offsetTop,self.currentX=t.pageX-t.target.offsetLeft,Object.entries(self.zones).forEach((function(t){var e=a(t,2);e[0];return e[1].zone.move(self)}))},document.onmouseup=function(t){Object.entries(self.zones).forEach((function(t){var e=a(t,2);e[0];return e[1].zone.endDraggable()}))}}},{key:"drawZone",value:function(t){this.context.fillStyle="rgba(0, 0, 0, 0.7)",this.context.fillRect(t.origin.x,t.origin.y,this.display.width,t.positionY),this.context.fillRect(t.origin.x,t.positionY+t.height,this.display.width,this.display.height-t.height-t.positionY),this.context.fillRect(t.origin.x,t.origin.y,t.positionX,this.display.height),this.context.fillRect(t.origin.x+t.positionX+t.width,t.origin.y,this.display.width-t.width-t.positionX,this.display.height)}},{key:"changeImage",value:function(){var t;this.image=new Image,this.image.src=URL.createObjectURL(this.fileInput.files[0]);var e=new FileReader;e.onload=function(e){return(t=t||(i=r().mark((function t(e){var i;return r().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return(i=new Image).src=e.target.result,t.next=4,i.decode();case 4:self.initialize(),Object.entries(self.zones).forEach((function(t){var e=a(t,2);return e[0],e[1].zone.defaultPositions(self.display)}));case 6:case"end":return t.stop()}}),t)})),function(){var t=this,e=arguments;return new Promise((function(n,r){var a=i.apply(t,e);function s(t){o(a,n,r,s,h,"next",t)}function h(t){o(a,n,r,s,h,"throw",t)}s(void 0)}))})).apply(this,arguments);var i},e.readAsDataURL(this.fileInput.files[0])}}]),t}(),p=function(){"use strict";function t(e,i){h(this,t),l(this,"isDraggable",!1),l(this,"width",void 0),l(this,"height",void 0),l(this,"ratio",void 0),l(this,"positionX",void 0),l(this,"positionY",void 0),l(this,"inputselector",void 0),l(this,"imagePositions",void 0),l(this,"origin",{}),l(this,"name",void 0),l(this,"mediaPosition",void 0),l(this,"startY",0),l(this,"startX",0),this.name=i.name,this.mediaPosition=e,i.outputWidth/i.outputHeight<e.display.width/e.display.height?this.ratio=e.display.height/i.outputHeight:this.ratio=e.display.width/i.outputWidth,this.inputselector=document.querySelector("#background_"+i.name+"Position"),this.imagePositions=this.inputselector&&""!==this.inputselector.value?JSON.parse(this.inputselector.value):{positionX:null,positionY:null},this.height=i.outputHeight*this.ratio,this.width=i.outputWidth*this.ratio,null!==this.imagePositions.positionX&&null!==this.imagePositions.positionY?(this.positionX=this.imagePositions.positionX*this.mediaPosition.ratio,this.positionY=this.imagePositions.positionY*this.mediaPosition.ratio):this.defaultPositions(),console.log("zone",this)}return c(t,[{key:"StartDraggable",value:function(t,e){this.startX=t-this.positionX,this.startY=e-this.positionY,e>=this.origin.y+this.positionY&&e<=this.origin.y+this.positionY+this.height&&t>=this.origin.x+this.positionX&&t<=this.origin.x+this.positionX+this.width&&(this.isDraggable=!0,document.body.style.cursor="move")}},{key:"setOrigin",value:function(t,e){this.origin={x:t,y:e}}},{key:"move",value:function(t){this.isDraggable&&(this.positionY=t.currentY-this.startY,this.positionX=t.currentX-this.startX,console.log("mediaPosition.currentX",t.currentX),console.log("this.startX",this.startX),console.log("positionY",this.positionY),this.positionY<0&&(this.positionY=0),this.positionY+this.height>=this.mediaPosition.display.height&&(this.positionY=this.mediaPosition.display.height-this.height),this.positionX<0&&(this.positionX=0),this.positionX+this.width>=this.mediaPosition.display.width&&(this.positionX=this.mediaPosition.display.width-this.width))}},{key:"endDraggable",value:function(){this.isDraggable&&(this.isDraggable=!1,document.body.style.cursor="auto",this.setPositions())}},{key:"setPositions",value:function(){this.inputselector.value=JSON.stringify({positionX:this.positionX/this.mediaPosition.ratio,positionY:this.positionY/this.mediaPosition.ratio}),console.log(this.inputselector.value)}},{key:"defaultPositions",value:function(){this.positionX=(this.mediaPosition.display.width-this.width)/2,this.positionY=(this.mediaPosition.display.height-this.height)/2,this.setPositions()}}]),t}()},4699:(t,e,i)=>{"use strict";var n=i(9781),r=i(7293),o=i(1702),a=i(9518),s=i(1956),h=i(5656),u=o(i(5296).f),c=o([].push),l=n&&r((function(){var t=Object.create(null);return t[2]=2,!u(t,2)})),f=function(t){return function(e){for(var i,r=h(e),o=s(r),f=l&&null===a(r),d=o.length,p=0,g=[];d>p;)i=o[p++],n&&!(f?i in r:u(r,i))||c(g,t?[i,r[i]]:r[i]);return g}};t.exports={entries:f(!0),values:f(!1)}},5069:(t,e,i)=>{"use strict";var n=i(2109),r=i(1702),o=i(3157),a=r([].reverse),s=[1,2];n({target:"Array",proto:!0,forced:String(s)===String(s.reverse())},{reverse:function(){return o(this)&&(this.length=this.length),a(this)}})},3706:(t,e,i)=>{"use strict";var n=i(7854);i(8003)(n.JSON,"JSON",!0)},2703:(t,e,i)=>{"use strict";i(8003)(Math,"Math",!0)},9720:(t,e,i)=>{"use strict";var n=i(2109),r=i(4699).entries;n({target:"Object",stat:!0},{entries:function(t){return r(t)}})},7941:(t,e,i)=>{"use strict";var n=i(2109),r=i(7908),o=i(1956);n({target:"Object",stat:!0,forced:i(7293)((function(){o(1)}))},{keys:function(t){return o(r(t))}})},2443:(t,e,i)=>{"use strict";i(6800)("asyncIterator")},3680:(t,e,i)=>{"use strict";var n=i(5005),r=i(6800),o=i(8003);r("toStringTag"),o(n("Symbol"),"Symbol")}},t=>{t.O(0,[259,615],(()=>{return e=4819,t(t.s=e);var e}));t.O()}]);