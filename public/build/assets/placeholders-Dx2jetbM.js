import{L as h}from"./main-B4G14OJk.js";import{P as u}from"./page-header-DNk6vVyo.js";import{_ as m}from"./card-header-CbzQO_FG.js";import{_ as f}from"./img-1-DTaidu05.js";import{_ as g}from"./img-2-CEPKryFh.js";import{_ as b}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{r as c,c as w,w as a,o as x,a as e,f as l,h as s}from"./app-DcM7ay-z.js";import"./simplebar-vue.esm-Dw2ZmZ-z.js";import"./logo-dark-BbIT5rJd.js";import"./logo-light-BfYujB8V.js";import"./russia-pMTSjNGF.js";const B={components:{Layout:h,PageHeader:u,CardHeader:m}},y=l("p",{class:"text-muted"},"In the example below, we take a typical card component and recreate it with placeholders applied to create a “loading card”.",-1),C={class:"live-preview"},v=l("img",{src:f,class:"card-img-top",alt:"card img"},null,-1),k=l("h5",{class:"card-title"},"Card title",-1),L=l("p",{class:"card-text"},"Some quick example text to build on the card title and make up the bulk of the card's content.",-1),H=l("img",{src:g,class:"card-img-top",alt:"card dummy img"},null,-1),P=l("h5",{class:"card-title placeholder-glow"},[l("span",{class:"placeholder col-6"})],-1),S=l("p",{class:"card-text placeholder-glow"},[l("span",{class:"placeholder col-7"}),l("span",{class:"placeholder col-4"}),l("span",{class:"placeholder col-4"}),l("span",{class:"placeholder col-6"})],-1),$=l("div",{class:"d-none code-view"},[l("pre",{class:"language-markup",style:{height:"275px"}},[s(""),l("code",null,`<!-- Base Examples -->
<BCard>
    <img src="..." class="card-img-top" alt="card img">
    <BCard no-body>
        <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
    </BCard>
</BCard>`),s(`

`),l("code",null,`<BCard aria-hidden="true">
    <img src="assets/images/small/img-2.jpg" class="card-img-top" alt="card dummy img">
    <BCard no-body>
      <BCard title=" " class="placeholder-glow">
        <span class="placeholder col-6"></span>
      </BCard>
      <BCard-text class="placeholder-glow">
        <span class="placeholder col-7"></span>
        <span class="placeholder col-4"></span>
        <span class="placeholder col-4"></span>
        <span class="placeholder col-6"></span>
      </BCard-text>
      <BLink  href="#" tabindex="-1" class="btn btn-primary disabled placeholder col-6"></BLink >
    </BCard>
</BCard>`)])],-1),z=l("p",{class:"text-muted"},[s(" Use "),l("code",null,"w-25,w-50,w-75"),s(" or "),l("code",null,"w-100"),s(" class to placeholder class to set different widths to the placeholder. ")],-1),U=l("div",{class:"live-preview"},[l("span",{class:"placeholder w-50"}),l("span",{class:"placeholder w-75"}),l("span",{class:"placeholder w-25"}),l("span",{class:"placeholder w-100"})],-1),j=l("div",{class:"d-none code-view"},[l("pre",{class:"language-markup"},[s(""),l("code",null,`<!-- Width Sizing-->
<div class="live-preview">
    <span class="placeholder col-6"></span>
    <span class="placeholder w-75"></span>
    <span class="placeholder" style="width: 25%;"></span>
</div>`)])],-1),N=l("p",{class:"text-muted"},[s(" Use "),l("code",null,"placeholder-lg"),s(", "),l("code",null,"placeholder-sm"),s(", or "),l("code",null,"placeholder-xs"),s(" class to placeholder class to set different size placeholder. ")],-1),V={class:"live-preview"},q=l("span",{class:"placeholder placeholder-lg w-100"},null,-1),G=l("span",{class:"placeholder w-100"},null,-1),I=l("span",{class:"placeholder placeholder-sm w-100"},null,-1),R=l("span",{class:"placeholder placeholder-xs w-100"},null,-1),T=l("div",{class:"d-none code-view"},[l("pre",{class:"language-markup"},[s(""),l("code",null,`<!-- Sizing -->
<span class="placeholder col-12 placeholder-lg"></span>`),s(`

`),l("code",null,'<span class="placeholder col-12"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 placeholder-sm"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 placeholder-xs"></span>')])],-1),W=l("p",{class:"text-muted"},[s(" Use "),l("code",null,"bg-"),s(" class with the below-mentioned color variation to set a custom color. ")],-1),D={class:"live-preview"},E=l("span",{class:"placeholder w-100"},null,-1),A=l("span",{class:"placeholder bg-primary w-100"},null,-1),F=l("span",{class:"placeholder bg-secondary w-100"},null,-1),J=l("span",{class:"placeholder bg-success w-100"},null,-1),K=l("span",{class:"placeholder bg-danger w-100"},null,-1),M=l("span",{class:"placeholder bg-warning w-100"},null,-1),O=l("span",{class:"placeholder bg-info w-100"},null,-1),Q=l("span",{class:"placeholder bg-light w-100"},null,-1),X=l("span",{class:"placeholder bg-dark w-100"},null,-1),Y=l("div",{class:"d-none code-view"},[l("pre",{class:"language-markup",style:{height:"275px"}},[s(""),l("code",null,`<!-- Color -->
<span class="placeholder col-12 mb-3"></span>`),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-primary"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-secondary"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-success"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-danger"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-warning"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-info"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-light"></span>'),s(`

`),l("code",null,'<span class="placeholder col-12 mb-3 bg-dark"></span>')])],-1);function Z(ll,el,al,sl,ol,cl){const i=c("PageHeader"),r=c("CardHeader"),p=c("BLink"),t=c("BCardBody"),d=c("BCard"),o=c("BCol"),n=c("BRow"),_=c("Layout");return x(),w(_,null,{default:a(()=>[e(i,{title:"Placeholders",pageTitle:"Base UI"}),e(n,null,{default:a(()=>[e(o,{lg:"12"},{default:a(()=>[e(d,{"no-body":""},{default:a(()=>[e(r,{title:"Default Placeholder"}),e(t,null,{default:a(()=>[y,l("div",C,[e(n,{class:"justify-content-center"},{default:a(()=>[e(o,{xl:"7"},{default:a(()=>[e(n,{class:"justify-content-between"},{default:a(()=>[e(o,{lg:"5",sm:"6"},{default:a(()=>[e(d,{"no-body":""},{default:a(()=>[v,e(t,null,{default:a(()=>[k,L,e(p,{href:"#",class:"btn btn-primary"},{default:a(()=>[s("Go somewhere")]),_:1})]),_:1})]),_:1})]),_:1}),e(o,{lg:"5",sm:"6"},{default:a(()=>[e(d,{"no-body":"","aria-hidden":"true"},{default:a(()=>[H,e(t,null,{default:a(()=>[P,S,e(p,{href:"#",tabindex:"-1",class:"btn btn-primary disabled placeholder col-6"})]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})]),$]),_:1})]),_:1})]),_:1})]),_:1}),e(n,null,{default:a(()=>[e(o,{xxl:"6"},{default:a(()=>[e(d,{"no-body":""},{default:a(()=>[e(r,{title:"Width"}),e(t,null,{default:a(()=>[z,U,j]),_:1})]),_:1})]),_:1}),e(o,{xxl:"6"},{default:a(()=>[e(d,{"no-body":""},{default:a(()=>[e(r,{title:"Sizing"}),e(t,null,{default:a(()=>[N,l("div",V,[e(n,{class:"gap-0"},{default:a(()=>[e(o,{cols:"12"},{default:a(()=>[q]),_:1}),e(o,{cols:"12"},{default:a(()=>[G]),_:1}),e(o,{cols:"12"},{default:a(()=>[I]),_:1}),e(o,{cols:"12"},{default:a(()=>[R]),_:1})]),_:1})]),T]),_:1})]),_:1})]),_:1})]),_:1}),e(n,null,{default:a(()=>[e(o,{lg:"12"},{default:a(()=>[e(d,{"no-body":""},{default:a(()=>[e(r,{title:"Color"}),e(t,null,{default:a(()=>[W,l("div",D,[e(n,{class:"g-2"},{default:a(()=>[e(o,{cols:"12"},{default:a(()=>[E]),_:1}),e(o,{cols:"12"},{default:a(()=>[A]),_:1}),e(o,{cols:"12"},{default:a(()=>[F]),_:1}),e(o,{cols:"12"},{default:a(()=>[J]),_:1}),e(o,{cols:"12"},{default:a(()=>[K]),_:1}),e(o,{cols:"12"},{default:a(()=>[M]),_:1}),e(o,{cols:"12"},{default:a(()=>[O]),_:1}),e(o,{cols:"12"},{default:a(()=>[Q]),_:1}),e(o,{cols:"12"},{default:a(()=>[X]),_:1})]),_:1})]),Y]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}const gl=b(B,[["render",Z]]);export{gl as default};
