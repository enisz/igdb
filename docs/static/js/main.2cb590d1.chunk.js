(this["webpackJsonpdocumentation-app"]=this["webpackJsonpdocumentation-app"]||[]).push([[0],{344:function(e,t,c){},352:function(e,t,c){},355:function(e,t,c){"use strict";c.r(t);var s=c(1),a=c.n(s),n=c(23),r=c.n(n),i=(c(70),c(15)),o=c.n(i),l=c(6),d=c(2),j=c(4);function h(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=Object(s.useState)(e),c=Object(j.a)(t,2),a=c[0],n=c[1],r=Object(d.g)(),i=function(e){e.preventDefault(),r.push({pathname:"/search",search:"?q="+encodeURIComponent(a)})};return{searchTerm:a,setSearchTerm:n,handleSearch:i}}var b=c(0);function m(e){var t=e.searchBar,c=void 0!==t&&t,a=e.searchValue,n=void 0===a?"":a,r=e.hamburger,i=void 0===r||r,o=h(n),d=o.searchTerm,j=o.setSearchTerm,m=o.handleSearch;return Object(s.useEffect)((function(){window.jQuery("#docs-sidebar-toggler").on("click",(function(){window.jQuery("#docs-sidebar").hasClass("sidebar-visible")?window.jQuery("#docs-sidebar").removeClass("sidebar-visible").addClass("sidebar-hidden"):window.jQuery("#docs-sidebar").removeClass("sidebar-hidden").addClass("sidebar-visible")}))}),[]),Object(b.jsx)("header",{className:"header fixed-top",children:Object(b.jsx)("div",{className:"branding docs-branding",children:Object(b.jsxs)("div",{className:"container-fluid position-relative py-2",children:[Object(b.jsxs)("div",{className:"docs-logo-wrapper",children:[i&&Object(b.jsxs)("button",{id:"docs-sidebar-toggler",className:"docs-sidebar-toggler docs-sidebar-visible mr-2 d-xl-none",type:"button",children:[Object(b.jsx)("span",{}),Object(b.jsx)("span",{}),Object(b.jsx)("span",{})]}),Object(b.jsx)("div",{className:"site-logo",children:Object(b.jsxs)(l.c,{to:"/home",className:"navbar-brand",children:[Object(b.jsx)("img",{className:"logo-icon mr-2",src:"".concat(".","/coderdocs-logo.svg"),alt:"logo"}),Object(b.jsxs)("span",{className:"logo-text",children:["IGDB",Object(b.jsx)("span",{className:"text-alt",children:"Wrapper"})]})]})})]}),Object(b.jsxs)("div",{className:"docs-top-utilities d-flex justify-content-end align-items-center",children:[c&&Object(b.jsx)("div",{className:"top-search-box d-none d-lg-flex",children:Object(b.jsxs)("form",{className:"search-form",onSubmit:m,children:[Object(b.jsx)("input",{type:"text",placeholder:"Search the docs...",name:"search",className:"form-control search-input",value:d,onChange:function(e){return j(e.target.value)}}),Object(b.jsx)("button",{type:"submit",className:"btn search-btn",value:"Search",children:Object(b.jsx)("i",{className:"fas fa-search"})})]})}),Object(b.jsx)("a",{href:"https://github.com/enisz/igdb/archive/master.zip",className:"btn btn-primary d-none d-lg-flex ml-3",children:"Download"})]})]})})})}function u(){return Object(b.jsx)("footer",{className:"footer",children:Object(b.jsx)("div",{className:"footer-bottom text-center py-5",children:Object(b.jsxs)("small",{className:"copyright",children:["Designed with ",Object(b.jsx)("i",{className:"fas fa-heart",style:{color:"#fb866a"}})," by ",Object(b.jsx)("a",{className:"theme-link",href:"http://themes.3rdwavemedia.com",target:"_blank",rel:"noreferrer",children:"Xiaoying Riley"})," for developers"]})})})}var x=c(39),p=c.n(x),f=new p.a("WrapperDocsDB",{env:"BROWSER",persistenceMethod:"memory"}),O=function(e){return f.getCollection("templates").find(e)},g=c(36),v=c(10);c(80);function N(){var e=localStorage.getItem("client_id")||sessionStorage.getItem("client_id"),t=localStorage.getItem("access_token")||sessionStorage.getItem("access_token"),c=Object(s.useState)(null==e?"":e),a=Object(j.a)(c,2),n=a[0],r=a[1],i=Object(s.useState)(null==t?"":t),o=Object(j.a)(i,2),l=o[0],d=o[1],h=Object(s.useState)(null!=localStorage.getItem("client_id")||null!=localStorage.getItem("access_token")),b=Object(j.a)(h,2),m=b[0],u=b[1];return Object(s.useEffect)((function(){m?(""!==n?(sessionStorage.removeItem("client_id"),localStorage.setItem("client_id",n)):(sessionStorage.removeItem("client_id"),localStorage.removeItem("client_id")),""!==l?(sessionStorage.removeItem("access_token"),localStorage.setItem("access_token",l)):(sessionStorage.removeItem("access_token"),localStorage.removeItem("access_token"))):(""!==n?(localStorage.removeItem("client_id"),sessionStorage.setItem("client_id",n)):(localStorage.removeItem("client_id"),sessionStorage.removeItem("client_id")),""!==l?(localStorage.removeItem("access_token"),sessionStorage.setItem("access_token",l)):(localStorage.removeItem("access_token"),sessionStorage.removeItem("access_token")))}),[n,l,m]),{clientId:n,setClientId:r,accessToken:l,setAccessToken:d,storeTokens:m,setStoreTokens:u}}var w=c(62);function y(){var e=Object(s.useState)(O({parents:{$size:0}})),t=Object(j.a)(e,1)[0],c=h(""),a=c.searchTerm,n=c.setSearchTerm,r=c.handleSearch,i=Object(s.useRef)(null),o=N(),d=o.clientId,x=o.setClientId,p=o.accessToken,f=o.setAccessToken,y=o.storeTokens,k=o.setStoreTokens;return Object(s.useEffect)((function(){i.current=new g.Modal(document.getElementById("exampleModal"),{backdrop:!0,keyboard:!0,focus:!0})}),[]),Object(b.jsxs)(b.Fragment,{children:[Object(b.jsx)(m,{hamburger:!1}),Object(b.jsxs)("div",{className:"page-header theme-bg-dark py-5 text-center position-relative",children:[Object(b.jsx)("div",{className:"theme-bg-shapes-right"}),Object(b.jsx)("div",{className:"theme-bg-shapes-left"}),Object(b.jsxs)("div",{className:"container",children:[Object(b.jsx)("h1",{className:"page-heading single-col-max mx-auto",children:"Documentation"}),Object(b.jsx)("div",{className:"page-intro single-col-max mx-auto",children:"IGDB PHP API Wrapper"}),Object(b.jsx)("div",{className:"main-search-box pt-3 d-block mx-auto",children:Object(b.jsxs)("form",{className:"search-form w-100",onSubmit:r,children:[Object(b.jsx)("input",{type:"text",placeholder:"Search the docs...",name:"search",className:"form-control search-input",value:a,onChange:function(e){return n(e.target.value)}}),Object(b.jsx)("button",{type:"submit",className:"btn search-btn",value:"Search",children:Object(b.jsx)("i",{className:"fas fa-search"})})]})}),Object(b.jsxs)("div",{className:"github-container d-sm-none",children:[Object(b.jsx)(v.a,{href:"https://github.com/enisz","data-size":"small","data-show-count":"true","aria-label":"Follow @enisz on GitHub",children:"Follow @enisz"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb","data-icon":"octicon-star","data-size":"small","data-show-count":"true","aria-label":"Star enisz/igdb on GitHub",children:"Star"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb/subscription","data-icon":"octicon-eye","data-size":"small","data-show-count":"true","aria-label":"Watch enisz/igdb on GitHub",children:"Watch"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb/fork","data-icon":"octicon-repo-forked","data-size":"small","data-show-count":"true","aria-label":"Fork enisz/igdb on GitHub",children:"Fork"})]}),Object(b.jsxs)("div",{className:"github-container d-none d-sm-block",children:[Object(b.jsx)(v.a,{href:"https://github.com/enisz","data-size":"large","data-show-count":"true","aria-label":"Follow @enisz on GitHub",children:"Follow @enisz"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb","data-icon":"octicon-star","data-size":"large","data-show-count":"true","aria-label":"Star enisz/igdb on GitHub",children:"Star"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb/subscription","data-icon":"octicon-eye","data-size":"large","data-show-count":"true","aria-label":"Watch enisz/igdb on GitHub",children:"Watch"}),Object(b.jsx)(v.a,{href:"https://github.com/enisz/igdb/fork","data-icon":"octicon-repo-forked","data-size":"large","data-show-count":"true","aria-label":"Fork enisz/igdb on GitHub",children:"Fork"})]})]})]}),Object(b.jsx)("div",{className:"page-content",children:Object(b.jsxs)("div",{className:"container",children:[Object(b.jsx)("div",{className:"row mt-5",children:Object(b.jsx)("div",{className:"col-12 text-center",children:Object(b.jsx)(w.a,{to:"/home",onClick:function(){return i.current.toggle()},children:"Add your tokens to the example codes"})})}),Object(b.jsx)("div",{className:"docs-overview py-5",children:Object(b.jsx)("div",{className:"row justify-content-center",children:t.length>0&&t.map((function(e){return Object(b.jsx)("div",{className:"col-12 col-lg-4 py-3",children:Object(b.jsx)("div",{className:"card shadow-sm",children:Object(b.jsxs)("div",{className:"card-body",children:[Object(b.jsxs)("h5",{className:"card-title mb-3",children:[Object(b.jsx)("span",{className:"theme-icon-holder card-icon-holder mr-2",children:Object(b.jsx)("i",{className:"fas ".concat(e.icon)})}),Object(b.jsx)("span",{className:"card-title-text",children:e.title})]}),Object(b.jsx)("div",{className:"card-text",children:e.overview}),Object(b.jsx)(l.c,{to:"/documentation#".concat(e.slug),className:"card-link-mask"})]})})},e.id)}))})})]})}),Object(b.jsx)("div",{className:"modal fade",ref:i,id:"exampleModal",tabIndex:"-1","aria-labelledby":"exampleModalLabel","aria-hidden":"true",children:Object(b.jsx)("div",{className:"modal-dialog modal-lg",children:Object(b.jsxs)("div",{className:"modal-content",children:[Object(b.jsx)("div",{className:"modal-header",children:Object(b.jsx)("h5",{className:"modal-title",id:"exampleModalLabel",children:"IGDB Tokens in the documentation"})}),Object(b.jsx)("div",{className:"modal-body",children:Object(b.jsx)("div",{className:"container-fluid",children:Object(b.jsxs)("div",{className:"row",children:[Object(b.jsxs)("div",{className:"col-12",children:[Object(b.jsx)("p",{children:"The documenation has a lot of example codes where the IGDB wrapper class has to be instantiated with your personal tokens. If you provide your tokens in the form below, the example codes will have your own tokens and you will have copy-paste codes in the documentation."}),Object(b.jsx)("p",{children:'These tokens are not stored anywhere else but in your browser. If you check the "Remember my tokens" checkbox, your tokens will be stored in your local storage and will be filled automatically on your next visit.'})]}),Object(b.jsxs)("div",{className:"col-12 col-lg-6 mb-3",children:[Object(b.jsx)("label",{htmlFor:"client-id",children:"Client ID"}),Object(b.jsx)("input",{type:"text",placeholder:"Client ID",id:"client-id",className:"form-control form-control-sm",value:d,onChange:function(e){return x(e.target.value)},autoComplete:"off"})]}),Object(b.jsxs)("div",{className:"col-12 col-lg-6 mb-3",children:[Object(b.jsx)("label",{htmlFor:"access-token",children:"Access Token"}),Object(b.jsx)("input",{type:"text",placeholder:"Access Token",id:"access-token",className:"form-control form-control-sm",value:p,onChange:function(e){return f(e.target.value)},autoComplete:"off"})]}),Object(b.jsx)("div",{className:"col-12 col-lg-6 mb-3",children:Object(b.jsxs)("div",{className:"form-check",children:[Object(b.jsx)("input",{className:"form-check-input",type:"checkbox",id:"flexCheckDefault",checked:y,onChange:function(e){return k(e.target.checked)}}),Object(b.jsx)("label",{className:"form-check-label",htmlFor:"flexCheckDefault",children:"Remember my tokens"})]})})]})})}),Object(b.jsxs)("div",{className:"modal-footer",children:[Object(b.jsx)("button",{className:"btn btn-danger btn-sm",onClick:function(){f(""),x("")},children:"Delete the tokens"}),Object(b.jsx)("button",{type:"button",className:"btn btn-primary btn-sm","data-bs-dismiss":"modal",onClick:function(){return i.current.toggle()},children:"Close"})]})]})})}),Object(b.jsx)(u,{})]})}var k=c(24),S=c.n(k),I=function(e){return document.body.classList.add(e)},C=function(e){return document.body.classList.remove(e)};function z(e){var t=e.paragraph;return Object(b.jsx)("li",{className:"nav-item section-title",style:{marginTop:"16px"},children:Object(b.jsxs)("a",{className:"nav-link scrollto",href:"#".concat(t.slug),children:[Object(b.jsx)("span",{className:"theme-icon-holder mr-2",children:Object(b.jsx)("i",{className:"fas ".concat(t.icon)})}),t.title]})},"sidebar-main-link-".concat(t.id))}function T(e){var t=e.paragraph;return Object(b.jsx)("li",{className:"nav-item",children:Object(b.jsx)("a",{className:"nav-link scrollto",href:"#".concat(t.slug),style:{paddingLeft:t.level-1+"5px"},children:t.title})})}function F(){var e=Object(s.useState)(O({})),t=Object(j.a)(e,1)[0],c=h(""),a=c.searchTerm,n=c.setSearchTerm,r=c.handleSearch;return Object(s.useEffect)((function(){window.jQuery(window).on("resize",(function(){window.jQuery(window).width()>=1200?window.jQuery("#docs-sidebar").addClass("sidebar-visible").removeClass("sidebar-hidden"):window.jQuery("#docs-sidebar").addClass("sidebar-hidden").removeClass("sidebar-visible")})).resize(),window.jQuery("a.scrollto").on("click",(function(e){var t=this.hash;window.jQuery("body").scrollTo(t,800,{offset:-69,axis:"y"}),window.jQuery("#docs-sidebar").hasClass("sidebar-visible")&&window.jQuery(window).width()<1200&&window.jQuery("#docs-sidebar-toggler").click()})),window.jQuery("body").scrollspy({target:"#docs-nav",offset:100})}),[]),Object(b.jsxs)("div",{id:"docs-sidebar",className:"docs-sidebar",children:[Object(b.jsx)("div",{className:"top-search-box d-lg-none p-3",children:Object(b.jsxs)("form",{className:"search-form",onSubmit:r,children:[Object(b.jsx)("input",{type:"text",placeholder:"Search the docs...",name:"search",className:"form-control search-input",value:a,onChange:function(e){return n(e.target.value)}}),Object(b.jsx)("button",{type:"submit",className:"btn search-btn",value:"Search",children:Object(b.jsx)("i",{className:"fas fa-search"})})]})}),Object(b.jsx)("nav",{id:"docs-nav",className:"docs-nav navbar",children:Object(b.jsx)("ul",{className:"section-items list-unstyled nav flex-column pb-3",children:t.length>0&&t.map((function(e){return null==e.parent?Object(b.jsx)(z,{paragraph:e},"sidebar-main-link-".concat(e.id)):Object(b.jsx)(T,{paragraph:e},"sidebar-sub-link-".concat(e.id))}))})})]})}var D=c(63),_=c.n(D),E=c(17),B=c.n(E),Q=c(40),P=c.n(Q),R=c(64),L=c.n(R);function W(e){var t=e.src,c=e.alt,s=e.group;return Object(b.jsxs)("figure",{className:"figure docs-figure py-3",children:[Object(b.jsx)("a",{href:t,"data-title":c,"data-lightbox":s,children:Object(b.jsx)("img",{className:"figure-img img-fluid shadow rounded",src:t,alt:c})}),Object(b.jsxs)("figcaption",{className:"figure-caption mt-3",children:[Object(b.jsx)("i",{className:"fas fa-info-circle mr-2"})," ",c]})]})}function G(e){var t=e.content,c=N(),s=c.clientId,n=c.accessToken,r=P.a.Parser,i=[{shouldProcessNode:function(e){return e.name&&"blockquote"===e.name},processNode:function(e,t){var c,s=B()(L.a.renderToString(t)).substr(2,9),a=new RegExp("^\\:([a-z]*)","i"),n=t.find((function(e){return"\n"!==e})),r=("string"===typeof n.props.children?n.props.children:n.props.children.find((function(e){return"string"===typeof e}))).match(a);switch(null!=r?r[1]:"info"){case"warn":case"warning":c={title:"Warning",icon:"exclamation-triangle",class:"warning"};break;case"success":case"tip":c={title:"Tip",icon:"thumbs-up",class:"success"};break;case"danger":c={title:"Danger",icon:"exclamation-circle",class:"danger"};break;case"info":case"note":default:c={title:"Note",icon:"info-circle",class:"info"}}return Object(b.jsx)("div",{className:"callout-block callout-block-".concat(c.class),children:Object(b.jsxs)("div",{className:"content",children:[Object(b.jsxs)("h4",{className:"callout-title",children:[Object(b.jsx)("span",{className:"callout-icon-holder mr-1",children:Object(b.jsx)("i",{className:"fas fa-".concat(c.icon)})}),c.title]}),"string"===typeof n.props.children?n.props.children.replace(a,""):n.props.children.map((function(e){return"string"===typeof e?e.replace(a,""):e}))]})},"callout-".concat(s))}},{shouldProcessNode:function(e){return e.name&&"a"===e.name&&e.attribs.href.startsWith("#")},processNode:function(e,t){return Object(b.jsx)("a",{href:"".concat(e.attribs.href),className:"scrollto",children:t})}},{shouldProcessNode:function(e){return e.parent&&e.parent.name&&"code"===e.parent.name&&(""!==s||""!==n)},processNode:function(e,t){return""!==s&&(e.data=e.data.replace("{client_id}",s)),""!==n&&(e.data=e.data.replace("{access_token}",n)),e.data}},{shouldProcessNode:function(e){return e.name&&"p"===e.name&&e.children.find((function(e){return e.name&&"img"===e.name}))},processNode:function(e,t){var c=t[0].props;return Object(b.jsx)(W,{src:"".concat(".","/").concat(c.src),alt:c.alt,group:B()(c.src).substr(2,9)},"".concat(B()(c.src+c.alt)))}},{shouldProcessNode:function(e){return e.name&&"table"===e.name},processNode:function(e,t){return Object(b.jsx)("div",{className:"table-responsive",children:Object(b.jsx)("table",{className:"table table-striped table-hover",children:t})},Math.random())}},{shouldProcessNode:function(e){return!0},processNode:new P.a.ProcessNodeDefinitions(a.a).processDefaultNode}];return Object(b.jsx)(b.Fragment,{children:(new r).parseWithInstructions(t,(function(){return!0}),i)})}function M(e){var t=e.parentId,c=Object(s.useState)(O({parent:t})),n=Object(j.a)(c,1)[0];return Object(b.jsx)(b.Fragment,{children:n.length>0&&n.map((function(e){return Object(b.jsxs)(s.Fragment,{children:[Object(b.jsxs)("section",{className:"docs-section",id:e.slug,children:[a.a.createElement("h".concat(e.level),{className:"section-heading"},e.title),Object(b.jsx)(G,{content:e.body.html})]}),O({parent:e.id}).length>0&&Object(b.jsx)(M,{parentId:e.id},"section-"+B()(e.id))]},e.id)}))})}var A=c(361),H=c(65);A.a.addDefaultLocale(H);var $=new A.a("en-US");function U(){var e;e="docs-page",Object(s.useEffect)((function(){return e instanceof Array?e.map(I):I(e),function(){e instanceof Array?e.map(C):C(e)}}),[e]);var t=Object(s.useState)(O({parents:{$size:0}})),c=Object(j.a)(t,1)[0];return Object(s.useEffect)((function(){_.a.highlightAll(),setTimeout((function(){if(window.location.hash){var e=window.location.hash;window.jQuery("body").scrollTo(e,800,{offset:-69,axis:"y"})}}),200)}),[]),Object(b.jsxs)(b.Fragment,{children:[Object(b.jsx)(m,{searchBar:!0}),Object(b.jsxs)("div",{className:"docs-wrapper",children:[Object(b.jsx)(F,{}),Object(b.jsx)("div",{className:"docs-content",children:Object(b.jsx)("div",{className:"container",children:c.length>0&&c.map((function(e){return Object(b.jsxs)("article",{className:"docs-article",id:e.slug,children:[Object(b.jsxs)("header",{className:"docs-header",children:[Object(b.jsxs)("h1",{className:"docs-heading",children:[e.title,"\xa0",Object(b.jsxs)("span",{className:"docs-time",children:[Object(b.jsx)("i",{className:"far fa-clock mr-1"}),"Last updated:\xa0",e.timestamp?Object(b.jsxs)(b.Fragment,{children:[S.a.parse("j",new Date(e.timestamp)),Object(b.jsx)("sup",{children:S.a.parse("o",new Date(e.timestamp))}),"\xa0 of\xa0",S.a.parse("F, Y",new Date(e.timestamp)),"\xa0 (",$.format(new Date(e.timestamp)),")"]}):Object(b.jsx)(b.Fragment,{children:"Not published yet"})]})]}),Object(b.jsx)("section",{className:"docs-intro",children:Object(b.jsx)(G,{content:e.body.html})})]}),Object(b.jsx)(M,{parentId:e.id})]},e.id)}))})})]}),Object(b.jsx)(u,{})]})}function q(){var e=Object(d.h)(),t=decodeURIComponent(new URLSearchParams(e.search).get("q")),c=Object(s.useState)([]),a=Object(j.a)(c,2),n=a[0],r=a[1];Object(s.useEffect)((function(){r(O({$or:[{"body.stripped":{$contains:t}},{title:{$contains:t}}]}))}),[t]);var i=function(e){return 1===e.level?e.icon:O({id:e.parents[0]})[0].icon},o=function(e){var t=[];if(e.parents.length>0)for(var c in e.parents){var s=e.parents[c];t.push(O({id:s})[0].title)}return t.push(e.title),t};return Object(b.jsxs)(b.Fragment,{children:[Object(b.jsx)(m,{searchBar:!0,searchValue:t}),Object(b.jsxs)("div",{className:"page-header theme-bg-dark py-5 text-center position-relative",children:[Object(b.jsx)("div",{className:"theme-bg-shapes-right"}),Object(b.jsx)("div",{className:"theme-bg-shapes-left"}),Object(b.jsxs)("div",{className:"container",children:[Object(b.jsx)("h1",{className:"page-heading single-col-max mx-auto",children:"Search Results"}),Object(b.jsxs)("div",{className:"page-intro single-col-max mx-auto",children:[n.length,' matches for "',t,'"']})]})]}),Object(b.jsx)("div",{className:"page-content",children:Object(b.jsx)("div",{className:"container",children:Object(b.jsxs)("div",{className:"docs-overview py-5",children:[Object(b.jsx)("div",{className:"row",children:Object(b.jsx)("div",{className:"col-12",children:n.length?Object(b.jsxs)(b.Fragment,{children:[Object(b.jsxs)("h6",{children:[n.length,' matches for "',t,'"']}),Object(b.jsx)("hr",{})]}):Object(b.jsx)(b.Fragment,{children:Object(b.jsxs)("h6",{children:['No matches for "',t,'"']})})})}),Object(b.jsx)("div",{className:"row justify-content-center",children:n.length>0&&n.map((function(e){return c=e,Object(b.jsx)("div",{className:"col-12 py-3",children:Object(b.jsx)("div",{className:"card shadow-sm",children:Object(b.jsxs)("div",{className:"card-body",children:[Object(b.jsxs)("h5",{className:"card-title mb-3",children:[Object(b.jsx)("span",{className:"theme-icon-holder card-icon-holder mr-2",children:Object(b.jsx)("i",{className:"fas ".concat(i(c))})}),Object(b.jsxs)("span",{className:"card-title-text",children:[o(c).map((function(e,c){return Object(b.jsxs)(s.Fragment,{children:[c>0&&Object(b.jsx)("i",{className:"fas fa-angle-right"}),Object(b.jsxs)("span",{children:[" ",Object(b.jsx)(G,{content:e.replace(new RegExp("(".concat(t,")"),"gi"),"<mark>$1</mark>")})," "]})]},"path-".concat(e))}))," "]})]}),Object(b.jsx)("div",{className:"card-text",children:Object(b.jsx)(G,{content:c.body.stripped.replace(new RegExp("(".concat(t,")"),"gi"),"<mark>$1</mark>")})}),Object(b.jsx)(l.c,{to:"/documentation#".concat(c.slug),className:"card-link-mask"})]})})},c.id);var c}))})]})})}),Object(b.jsx)(u,{})]})}function J(){return Object(b.jsx)(l.a,{basename:"/igdb",children:Object(b.jsxs)(d.d,{children:[Object(b.jsx)(d.a,{from:"/",to:"/home",exact:!0}),Object(b.jsx)(d.b,{path:"/home",component:y,exact:!0}),Object(b.jsx)(d.b,{path:"/documentation",component:U,exact:!0}),Object(b.jsx)(d.b,{path:"/search",component:q,exact:!0})]})})}window.jQuery=o.a;var V=function(e){e&&e instanceof Function&&c.e(3).then(c.bind(null,362)).then((function(t){var c=t.getCLS,s=t.getFID,a=t.getFCP,n=t.getLCP,r=t.getTTFB;c(e),s(e),a(e),n(e),r(e)}))};c(344);function X(){return Object(b.jsx)("div",{className:"loader",children:"Loading..."})}var Y;c(345),c(346),c(347),c(350),c(351),c(352),c(353),c(354);(Y="".concat(".","/database.json"),new Promise((function(e,t){fetch(Y).then((function(e){return e.json()})).then((function(t){(f=new p.a("WrapperDocsDB",{env:"BROWSER",persistenceMethod:"memory"})).loadJSONObject(t),e()})).catch((function(e){return t(e)}))}))).then((function(){return r.a.render(Object(b.jsx)(a.a.StrictMode,{children:Object(b.jsx)(J,{})}),document.getElementById("root"))})).catch((function(e){r.a.render(Object(b.jsx)(a.a.StrictMode,{children:Object(b.jsxs)("div",{children:[Object(b.jsx)("p",{children:"Failed to load database!"}),Object(b.jsx)("pre",{dangerouslySetInnerHTML:{__html:e}})]})}),document.getElementById("root"))})),r.a.render(Object(b.jsx)(a.a.StrictMode,{children:Object(b.jsxs)("div",{style:{position:"absolute",top:0,right:0,bottom:0,left:0,textAlign:"center"},children:[Object(b.jsx)(X,{}),Object(b.jsx)("div",{children:"Loading Database..."})]})}),document.getElementById("root")),V()},70:function(e,t,c){},80:function(e,t,c){}},[[355,1,2]]]);
//# sourceMappingURL=main.2cb590d1.chunk.js.map