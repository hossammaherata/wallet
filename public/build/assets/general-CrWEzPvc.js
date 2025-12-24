import{L as S}from"./main-DSaa3E_d.js";import{P as R}from"./page-header-WpxB93eJ.js";import{_ as T}from"./card-header-DdZcqS1K.js";import{_ as V}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{b as P,r as s,a0 as z,c as v,w as n,o as x,a as t,f as e,h as o,i as _}from"./app-Baws5krQ.js";import"./simplebar-vue.esm-DAj5T6fG.js";import"./logo-dark-BbIT5rJd.js";import"./logo-light-Bc94pPwb.js";import"./us-DIspYScJ.js";const I={data(){return{ex3CurrentPage:P(2),ex3Rows:P(100),ex4CurrentPage:P(2),ex4Rows:P(100)}},components:{Layout:S,PageHeader:R,CardHeader:T}},k=e("p",{class:"text-muted"},"Popovers example are available with follwing options , Directions are mirrored when using in Bootstrap RTL.",-1),U={class:"live-preview"},H={class:"hstack flex-wrap gap-2"},L=e("div",{class:"d-none code-view"},[e("pre",{class:"language-markup",style:{height:"275px"}},[e("code",null,`<div class="hstack flex-wrap gap-2">
<BButton type="button" variant="light" v-BPopover.click.top="‘Vivamus sagittis lacus vel augue laoreet rutrum faucibus.’" title="Top Popover">
Popover on top
</BButton>
<BButton type="button" variant="light" v-BPopover.click.right="‘Vivamus sagittis lacus vel augue laoreet rutrum faucibus.’" title="Right Popover">
Popover on right
</BButton>
<BButton type="button" variant="light" v-BPopover.click.bottom="‘Vivamus sagittis lacus vel augue laoreet rutrum faucibus.’" title="Bottom Popover">
Popover on bottom
</BButton>
<BButton type="button" variant="light" v-BPopover.click.left="‘Vivamus sagittis lacus vel augue laoreet rutrum faucibus.’" title="Left Popover">
Popover on left
</BButton>
<BButton variant="success" id="popover-button-variant" href="#" tabindex="0">Dismissible popover</BButton>
<BPopover target="popover-button-variant" variant="danger" triggers="focus">
<template #title>Dismissible Popover</template> And here some amazing content. Its very engaging. Right?</BPopover>`)])],-1),D=e("p",{class:"text-muted"},"Tooltip example are available with follwing options, Directions are mirrored when using in Bootstrap RTL.",-1),N={class:"live-preview"},G={class:"hstack flex-wrap gap-2"},A=e("div",{class:"d-none code-view"},[e("pre",{class:"language-markup",style:{height:"275px"}},[e("code",null,`<!-- Tooltips -->
<div class="hstack flex-wrap gap-2">
<BButton type="button" variant="light" v-b-tooltip.hover title="Tooltip on top" >
Tooltip on top
</BButton>
<BButton type="button" variant="light" v-b-tooltip.hover title="Tooltip on right" >
Tooltip on right
</BButton>
<BButton type="button" variant="light" v-b-tooltip.hover title="Tooltip on bottom" >
Tooltip on bottom
</BButton>
<BButton type="button" variant="light" v-b-tooltip.hover title="Tooltip on left" >
Tooltip on left
</BButton>
<BButton type="button" variant="primary" v-b-tooltip.hover title="<em>Tooltip</em> <u>with</u> <b>HTML</b>">
Tooltip with HTML
</BButton>
</div>`)])],-1),M=e("p",{class:"text-muted"}," Indicate the current page’s location within a navigational hierarchy ",-1),j={class:"live-preview"},q=e("i",{class:"ri-home-5-fill"},null,-1),E=e("div",{class:"d-none code-view"},[e("pre",{class:"language-markup",style:{height:"275px"}},[e("code",null,`<nav aria-label="breadcrumb">
<BBreadcrumb>
<BBreadcrumbItem active aria-current="page">Home</BBreadcrumbItem>
</BBreadcrumb>

<nav aria-label="breadcrumb">
<b-breadcrumb>
<BBreadcrumbItem><a href="#">Home</a></BBreadcrumbItem>
<BBreadcrumbItem active aria-current="page">Library</BBreadcrumbItem>
</BBreadcrumb>
</nav>

<nav aria-label="breadcrumb">
<b-breadcrumb>
<BBreadcrumbItem><a href="#">Home</a></BBreadcrumbItem>
<BBreadcrumbItem><a href="#">Base UI</a></BBreadcrumbItem>
<BBreadcrumbItem active aria-current="page">General</BBreadcrumbItem>
</BBreadcrumb>
</nav>

<nav aria-label="breadcrumb">
<b-breadcrumb>
<BBreadcrumbItem><a href="#"><i class="ri-home-5-fill"></i></a></BBreadcrumbItem>
<BBreadcrumbItem><a href="#">Base UI</a></BBreadcrumbItem>
<BBreadcrumbItem active aria-current="page">General</BBreadcrumbItem>
</BBreadcrumb>
</nav>`)])],-1),F={class:"live-preview"},J=e("h5",{class:"fs-15"},"Default Pagination",-1),K=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"pagination"),o(" class to ul element to indicate a series of related content exists across multiple pages. ")],-1),O={"aria-label":"..."},Q={class:"mt-4 mt-lg-0"},W=e("h5",{class:"fs-15"},"Disabled and Active states",-1),X=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"disabled"),o(" class to links that appear un-clickable and "),e("code",null,"active"),o(" class to indicate the current page. ")],-1),Y={"aria-label":"..."},Z={class:"mt-4"},$=e("h5",{class:"fs-15"},"Sizing",-1),tt=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"pagination-lg"),o(" or "),e("code",null,"pagination-sm"),o(" to set different pagination sizes. ")],-1),et={"aria-label":"..."},nt={class:"mt-4"},ot=e("h5",{class:"fs-15"},"Alignment",-1),at=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"justify-content-start"),o(", "),e("code",null,"justify-content-start"),o(", or "),e("code",null,"justify-content-start"),o(", class to pagination class to change the alignment of pagination respectively. ")],-1),it={"aria-label":"Page navigation example"},rt={class:"mt-4"},lt=e("h5",{class:"fs-15"},"Custom Separated Pagination",-1),st=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"pagination-separated"),o(" class to pagination class to set custom separated pagination. ")],-1),dt={class:"mt-4"},ut=e("h5",{class:"fs-15"},"Custom Rounded Pagination",-1),pt=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"pagination-rounded"),o(" class to pagination class to set custom rounded pagination. ")],-1),gt=e("div",{class:"d-none code-view"},[e("pre",{class:"language-markup",style:{height:"275px"}},[e("code",null,`
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>
`),o(`
`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>`),o(`


`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-separated d-flex-wrap">
</BPagination>`),o(`

`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>;`),o(`


`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>`),o(`

`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>`),o(`


`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>`),o(`

`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>`),o(`


`),e("code",null,`  <BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="lg" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" prev-text="←" next-text="→"
  hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap"> </BPagination>
<BPagination v-model="ex4CurrentPage" pills :total-rows="ex4Rows" size="sm" prev-text="←"
  next-text="→" hide-goto-end-buttons="true" class="pagination-rounded d-flex-wrap">
</BPagination>`)])],-1),ct={class:"live-preview"},vt=e("h5",{class:"fs-15"},"Border spinner",-1),xt=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"spinner-border"),o(" class for a lightweight loading indicator. ")],-1),mt={class:"d-flex flex-wrap gap-3 mb-2"},Bt=e("h5",{class:"fs-15"},"Growing spinner",-1),ht=e("p",{class:"text-muted"},[o(" Use "),e("code",null,"spinner-grow"),o(" class for a lightweight spinner with growing effect. ")],-1),bt={class:"d-flex flex-wrap gap-3 mb-2"},ft=e("div",{class:"d-none code-view"},[e("pre",{class:"language-markup",style:{height:"275px"}},[o(""),e("code",null,`<!-- Border spinner -->

<BSpinner variant="primary"></BSpinner>

<BSpinner variant="secondary"></BSpinner>

<BSpinner variant="success"></BSpinner>

<BSpinner variant="info"></BSpinner>

<BSpinner variant="warning"></BSpinner>

<BSpinner variant="danger"></BSpinner>

<BSpinner variant="dark"></BSpinner>

<BSpinner variant="light"></BSpinner>
`),o(`
`),e("code",null,`<!-- Growing spinner -->

<BSpinner type="grow" variant="primary"></BSpinner>

<BSpinner type="grow" variant="secondary"></BSpinner>

<BSpinner type="grow" variant="success"></BSpinner>

<BSpinner type="grow" variant="info"></BSpinner>

<BSpinner type="grow" variant="warning"></BSpinner>

<BSpinner type="grow" variant="danger"></BSpinner>

<BSpinner type="grow" variant="dark"></BSpinner>

<BSpinner type="grow" variant="light"></BSpinner>
`)])],-1);function _t(wt,d,Pt,yt,a,Ct){const y=s("PageHeader"),m=s("CardHeader"),p=s("BButton"),B=s("BPopover"),h=s("BCardBody"),b=s("BCard"),l=s("BCol"),g=s("BRow"),c=s("BBreadcrumbItem"),w=s("BBreadcrumb"),r=s("BPagination"),i=s("BSpinner"),C=s("Layout"),f=z("b-tooltip");return x(),v(C,null,{default:n(()=>[t(y,{title:"General",pageTitle:"Base UI"}),t(g,null,{default:n(()=>[t(l,null,{default:n(()=>[t(b,{"no-body":""},{default:n(()=>[t(m,{title:"Popovers"}),t(h,null,{default:n(()=>[k,e("div",U,[e("div",H,[t(p,{variant:"light",id:"popover-button-top"},{default:n(()=>[o(" Popover on top ")]),_:1}),t(B,{target:"popover-button-top",title:"Top Popover",triggers:"focus",html:"",placement:"top",content:"Vivamus sagittis lacus vel augue laoreet rutrum faucibus."}),t(p,{variant:"light",id:"popover-button-right"},{default:n(()=>[o(" Popover on right ")]),_:1}),t(B,{target:"popover-button-right",title:"Right Popover",triggers:"focus",html:"",placement:"right",content:"Vivamus sagittis lacus vel augue laoreet rutrum faucibus."}),t(p,{variant:"light",id:"popover-button-bottom"},{default:n(()=>[o(" Popover on bottom ")]),_:1}),t(B,{target:"popover-button-bottom",title:"Bottom Popover",triggers:"focus",html:"",placement:"bottom",content:"Vivamus sagittis lacus vel augue laoreet rutrum faucibus."}),t(p,{variant:"light",id:"popover-button-left"},{default:n(()=>[o(" Popover on left ")]),_:1}),t(B,{target:"popover-button-left",title:"Left Popover",triggers:"focus",html:"",placement:"left",content:"Vivamus sagittis lacus vel augue laoreet rutrum faucibus."}),t(p,{variant:"success",id:"popover-button-variant",href:"#",tabindex:"0"},{default:n(()=>[o("Dismissible popover ")]),_:1}),t(B,{target:"popover-button-variant",title:"Dismissible Popover",triggers:"focus",html:"",content:"And here's some amazing content. Its very engaging. Right?"})])]),L]),_:1})]),_:1})]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,null,{default:n(()=>[t(b,{"no-body":""},{default:n(()=>[t(m,{title:"Tooltips"}),t(h,null,{default:n(()=>[D,e("div",N,[e("div",G,[_((x(),v(p,{variant:"light",title:"Tooltip on top"},{default:n(()=>[o("Tooltip on top")]),_:1})),[[f,void 0,void 0,{hover:!0}]]),_((x(),v(p,{variant:"light",title:"Tooltip on right"},{default:n(()=>[o("Tooltip on right ")]),_:1})),[[f,void 0,void 0,{hover:!0,right:!0}]]),_((x(),v(p,{variant:"light",title:"Tooltip on bottom"},{default:n(()=>[o("Tooltip on bottom ")]),_:1})),[[f,void 0,void 0,{hover:!0,bottom:!0}]]),_((x(),v(p,{variant:"light",title:"Tooltip on left"},{default:n(()=>[o("Tooltip on left ")]),_:1})),[[f,void 0,void 0,{hover:!0,left:!0}]]),_((x(),v(p,{variant:"primary",title:"<em>Tooltip</em> <u>with</u> <b>HTML</b>"},{default:n(()=>[o(" Tooltip with HTML")]),_:1})),[[f,void 0,void 0,{hover:!0}]])])]),A]),_:1})]),_:1})]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,null,{default:n(()=>[t(b,{"no-body":""},{default:n(()=>[t(m,{title:"Breadcrumb"}),t(h,null,{default:n(()=>[M,e("div",j,[t(w,{class:"p-3 py-2 bg-light mb-3"},{default:n(()=>[t(c,{href:"#home"},{default:n(()=>[o(" Home ")]),_:1})]),_:1}),t(w,{class:"p-3 py-2 bg-light mb-3"},{default:n(()=>[t(c,{href:"#home"},{default:n(()=>[o(" Home ")]),_:1}),t(c,{href:"#library"},{default:n(()=>[o(" Library ")]),_:1})]),_:1}),t(w,{class:"p-3 py-2 bg-light mb-3"},{default:n(()=>[t(c,{href:"#home"},{default:n(()=>[o(" Home ")]),_:1}),t(c,{href:"#base-ui"},{default:n(()=>[o(" Base UI ")]),_:1}),t(c,{href:"#general"},{default:n(()=>[o(" General ")]),_:1})]),_:1}),t(w,{class:"p-3 py-2 bg-light"},{default:n(()=>[t(c,{href:"#home"},{default:n(()=>[q]),_:1}),t(c,{href:"#base-ui"},{default:n(()=>[o(" Base UI ")]),_:1}),t(c,{href:"#general"},{default:n(()=>[o(" General ")]),_:1})]),_:1})]),E]),_:1})]),_:1})]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,null,{default:n(()=>[t(b,{"no-body":""},{default:n(()=>[t(m,{title:"Pagination"}),t(h,null,{default:n(()=>[e("div",F,[t(g,null,{default:n(()=>[t(l,{lg:"6"},{default:n(()=>[J,K,e("nav",O,[t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true"},{"prev-text":n(()=>[o("←   Prev")]),"next-text":n(()=>[o("Next   →")]),_:1}),t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true"},{"prev-text":n(()=>[o("<   ")]),"next-text":n(()=>[o("   >")]),_:1})])]),_:1}),t(l,{lg:"6"},{default:n(()=>[e("div",Q,[W,X,e("nav",Y,[t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true"},{"prev-text":n(()=>[o("←   Prev")]),"next-text":n(()=>[o("Next   →")]),_:1}),t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true"},{"prev-text":n(()=>[o("<   ")]),"next-text":n(()=>[o("   >")]),_:1})])])]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,{lg:"6"},{default:n(()=>[e("div",Z,[$,tt,e("nav",et,[t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true",size:"lg",class:"d-flex-wrap"},{"prev-text":n(()=>[o("← Prev")]),"next-text":n(()=>[o("Next   →")]),_:1}),t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true",size:"sm"},{"prev-text":n(()=>[o("← Prev")]),"next-text":n(()=>[o("Next   →")]),_:1})])])]),_:1}),t(l,{lg:"6"},{default:n(()=>[e("div",nt,[ot,at,e("nav",it,[t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true",align:"center"},{"prev-text":n(()=>[o("←   Prev")]),"next-text":n(()=>[o("Next   →")]),_:1}),t(r,{"total-rows":9,"per-page":3,"hide-goto-end-buttons":"true",align:"end"},{"prev-text":n(()=>[o("←   Prev")]),"next-text":n(()=>[o("Next   →")]),_:1})])])]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,{lg:"6"},{default:n(()=>[e("div",rt,[lt,st,t(r,{modelValue:a.ex3CurrentPage,"onUpdate:modelValue":d[0]||(d[0]=u=>a.ex3CurrentPage=u),"total-rows":a.ex3Rows,size:"lg","prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-separated d-flex-wrap"},null,8,["modelValue","total-rows"]),t(r,{modelValue:a.ex3CurrentPage,"onUpdate:modelValue":d[1]||(d[1]=u=>a.ex3CurrentPage=u),"total-rows":a.ex3Rows,"prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-separated d-flex-wrap"},null,8,["modelValue","total-rows"]),t(r,{modelValue:a.ex3CurrentPage,"onUpdate:modelValue":d[2]||(d[2]=u=>a.ex3CurrentPage=u),"total-rows":a.ex3Rows,size:"sm","prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-separated d-flex-wrap"},null,8,["modelValue","total-rows"])])]),_:1}),t(l,{lg:"6"},{default:n(()=>[e("div",dt,[ut,pt,t(r,{modelValue:a.ex4CurrentPage,"onUpdate:modelValue":d[3]||(d[3]=u=>a.ex4CurrentPage=u),pills:"","total-rows":a.ex4Rows,size:"lg","prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-rounded d-flex-wrap"},null,8,["modelValue","total-rows"]),t(r,{modelValue:a.ex4CurrentPage,"onUpdate:modelValue":d[4]||(d[4]=u=>a.ex4CurrentPage=u),pills:"","total-rows":a.ex4Rows,"prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-rounded d-flex-wrap"},null,8,["modelValue","total-rows"]),t(r,{modelValue:a.ex4CurrentPage,"onUpdate:modelValue":d[5]||(d[5]=u=>a.ex4CurrentPage=u),pills:"","total-rows":a.ex4Rows,size:"sm","prev-text":"←","next-text":"→","hide-goto-end-buttons":"true",class:"pagination-rounded d-flex-wrap"},null,8,["modelValue","total-rows"])])]),_:1})]),_:1})]),gt]),_:1})]),_:1})]),_:1})]),_:1}),t(g,null,{default:n(()=>[t(l,null,{default:n(()=>[t(b,{"no-body":""},{default:n(()=>[t(m,{title:"Spinners"}),t(h,null,{default:n(()=>[e("div",ct,[t(g,null,{default:n(()=>[t(l,{lg:"6"},{default:n(()=>[e("div",null,[vt,xt,e("div",mt,[t(i,{variant:"primary",label:"Spinning"}),t(i,{variant:"secondary",label:"Spinning"}),t(i,{variant:"success",label:"Spinning"}),t(i,{variant:"info",label:"Spinning"}),t(i,{variant:"warning",label:"Spinning"}),t(i,{variant:"danger",label:"Spinning"}),t(i,{variant:"dark",label:"Spinning"}),t(i,{variant:"light",label:"Spinning"})])])]),_:1}),t(l,{lg:"6"},{default:n(()=>[e("div",null,[Bt,ht,e("div",bt,[t(i,{variant:"primary",type:"grow",label:"Spinning"}),t(i,{variant:"secondary",type:"grow",label:"Spinning"}),t(i,{variant:"success",type:"grow",label:"Spinning"}),t(i,{variant:"info",type:"grow",label:"Spinning"}),t(i,{variant:"warning",type:"grow",label:"Spinning"}),t(i,{variant:"danger",type:"grow",label:"Spinning"}),t(i,{variant:"dark",type:"grow",label:"Spinning"}),t(i,{variant:"light",type:"grow",label:"Spinning"})])])]),_:1})]),_:1})]),ft]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}const Lt=V(I,[["render",_t]]);export{Lt as default};
