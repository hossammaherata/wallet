import{L as p}from"./main-C4AzBLHy.js";import{P as h}from"./page-header-BZ_NBl-q.js";import{_ as f}from"./card-header-AogAJ3Zq.js";import{_ as v}from"./img-1-DTaidu05.js";import{_ as C}from"./img-2-CEPKryFh.js";import{_ as I}from"./img-3-DK__eNi3.js";import{_ as B}from"./img-4-BivurQMA.js";import{_ as w}from"./img-5-CUgvwN14.js";import{_ as b}from"./img-6-B3jhxWU1.js";import{_ as k}from"./img-7-BBkb8MrE.js";import{_ as y}from"./img-8-DlaxalRz.js";import{_ as x}from"./img-9-BIDA38jV.js";import{_ as S}from"./img-10-tERk_nk8.js";import{_ as D}from"./img-11-y7K3gsfX.js";import{_ as T}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{r as a,c as W,w as o,o as U,a as e,f as s,h as l}from"./app-DAJLD3VL.js";import"./simplebar-vue.esm-Bl3jGYa5.js";import"./logo-dark-BbIT5rJd.js";import"./logo-light-Bc94pPwb.js";import"./us-DIspYScJ.js";const H={data(){return{Img1:v,Img2:C,Img3:I,Img4:B,Img5:w,Img6:b,Img7:k,Img8:y,Img9:x,Img10:S,Img11:D}},components:{Layout:p,PageHeader:h,CardHeader:f}},V=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"slide"),l(" class to set carousel with slides. Note the presence of the "),s("code",null,"d-block"),l(" and "),s("code",null,"w-100"),l(" class on carousel images to prevent browser default image alignment. ")],-1),A={class:"live-preview"},G=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup"},[l(""),s("code",null,`<!-- Slides Only -->
  <BCarousel :interval="2000" class="carousel slide">
    <BCarouselSlide active :img-src="Img1" />
    <BCarouselSlide :img-src="Img2" />
    <BCarouselSlide :img-src="Img3" />
  </BCarousel>
`)])],-1),L=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"carousel-control-prev"),l(" and "),s("code",null,"carousel-control-next"),l(" class with <button> or <a> tag element to show carousel with control navigation. ")],-1),N={class:"live-preview"},P=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup"},[l(""),s("code",null,`<!-- With Controls -->
  <BCarousel class="carousel slide" :interval="2000" controls>
    <BCarouselSlide active :img-src="Img4" />
    <BCarouselSlide :img-src="Img5" />
    <BCarouselSlide :img-src="Img6" />
  </BCarousel>
`)])],-1),j=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"carousel-indicators"),l(" class with <ol> element to show carousel with indicators. ")],-1),q={class:"live-preview"},O=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup"},[l(""),s("code",null,`<!-- With Indicators -->
  <BCarousel class="carousel slide" :interval="2000" controls indicators>
    <BCarouselSlide active :img-src="Img3" />
    <BCarouselSlide :img-src="Img2" />
    <BCarouselSlide :img-src="Img1" />
  </BCarousel>
`)])],-1),R=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"carousel-caption"),l(" class to add captions to the carousel. ")],-1),Y={class:"live-preview"},z=s("h5",{class:"text-white"},"Sunrise above a beach",-1),E=s("p",{class:"text-white-50"}," You've probably heard that opposites attract. The same is true for fonts. Don't be afraid to combine font styles that are different but complementary. ",-1),F=s("h5",{class:"text-white"},"Working from home little spot",-1),J=s("p",{class:"text-white-50"}," Consistency piques people’s interest is that it has become more and more popular over the years, which is excellent. ",-1),K=s("h5",{class:"text-white"}," Dramatic clouds at the Golden Gate Bridge ",-1),M=s("p",{class:"text-white-50"}," Increase or decrease the letter spacing depending on the situation and try, try again until it looks right, and each letter. ",-1),Q=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup",style:{height:"375px"}},[l(""),s("code",null,`<!-- With Captions -->
  <BCarousel class="carousel slide" :interval="2000" controls indicators>
    <BCarouselSlide active :img-src="Img7">
      <h5 class="text-white">Sunrise above a beach</h5>
      <p class="text-white-50">
        You've probably heard that opposites attract. The same
        is true for fonts. Don't be afraid to combine font
        styles that are different but complementary.
      </p>
    </BCarouselSlide>
    <BCarouselSlide :img-src="Img2">
      <h5 class="text-white">Working from home little spot</h5>
      <p class="text-white-50">
        Consistency piques people’s interest is that it has
        become more and more popular over the years, which is
        excellent.
      </p>
    </BCarouselSlide>
    <BCarouselSlide :img-src="Img9">
      <h5 class="text-white">
        Dramatic clouds at the Golden Gate Bridge
      </h5>
      <p class="text-white-50">
        Increase or decrease the letter spacing depending on the
        situation and try, try again until it looks right, and
        each letter.
      </p>
    </BCarouselSlide>
  </BCarousel>
`)])],-1),X=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"carousel-fade"),l(" class to the carousel to animate slides with a fade transition instead of a slide. ")],-1),Z={class:"live-preview"},$=s("div",{class:"d-none code-view"},[s("pre",null,[l(""),s("code",{class:"language-markup"},`
<!-- With Crossfade Animation -->
<BCarousel class="carousel slide carousel-fade" controls indicators>
  <BCarouselSlide active :img-src="Img1" />
  <BCarouselSlide :img-src="Img2" />
  <BCarouselSlide :img-src="Img3" />
</BCarousel>
`)])],-1),ee=s("p",{class:"text-muted"},[l(" Use "),s("code",null,'data-bs-interval=" "'),l(" to a carousel-item to change the amount of time to delay between automatically cycling to the next item. ")],-1),se={class:"live-preview"},oe=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup"},[l(""),s("code",null,`<!-- Individual Slide -->
  <BCarousel class="carousel slide" :interval="2000" controls>
    <BCarouselSlide active :img-src="Img1" />
    <BCarouselSlide :img-src="Img11" />
    <BCarouselSlide :img-src="Img10" />
  </BCarousel>
`)])],-1),le=s("p",{class:"text-muted"},[l(" Carousels support swiping left/right on touchscreen devices to move between slides. This can be disabled using the "),s("code",null,"data-bs-touch"),l(" attribute. The example below also does not include the "),s("code",null,"data-bs-ride attribute"),l(" and has "),s("code",null,'data-bs-interval="false"'),l(" so it doesn’t autoplay. ")],-1),te={class:"live-preview"},ie=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup"},[l(""),s("code",null,`<!-- Disable Touch Swiping -->
  <BCarousel class="carousel slide" :interval="2000" controls no-touch="true">
    <BCarouselSlide active :img-src="Img9" />
    <BCarouselSlide :img-src="Img8" />
    <BCarouselSlide :img-src="Img7" />
  </BCarousel>
`)])],-1),ae=s("p",{class:"text-muted"},[l(" Use "),s("code",null,"carousel-dark"),l(" class to the carousel for darker controls, indicators, and captions. ")],-1),ne={class:"live-preview"},re=s("div",{class:"carousel-caption d-none d-md-block"},[s("h5",null,"Drawing a sketch"),s("p",null," Too much or too little spacing, as in the example below, can make things unpleasant for the reader. ")],-1),ce=s("div",{class:"carousel-caption d-none d-md-block"},[s("h5",null,"Blue clock on a pastel background"),s("p",null," In some designs, you might adjust your tracking to create a certain artistic effect asked them what graphic design tips they live. ")],-1),de=s("div",{class:"carousel-caption d-none d-md-block"},[s("h5",null,"Working at a coffee shop"),s("p",null," A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring. ")],-1),ue=s("div",{class:"d-none code-view"},[s("pre",{class:"language-markup",style:{height:"375px"}},[l(""),s("code",null,`<!-- Dark Variant -->
  <BCarousel class="carousel slide carousel-dark" :interval="10000" controls>
    <BCarouselSlide active :img-src="Img4">
      <div class="carousel-caption d-none d-md-block">
        <h5>Drawing a sketch</h5>
        <p>
          Too much or too little spacing, as in the example below,
          can make things unpleasant for the reader.
        </p>
      </div>
    </BCarouselSlide>
    <BCarouselSlide :img-src="Img5">
      <div class="carousel-caption d-none d-md-block">
        <h5>Blue clock on a pastel background</h5>
        <p>
          In some designs, you might adjust your tracking to
          create a certain artistic effect asked them what graphic
          design tips they live.
        </p>
      </div>
    </BCarouselSlide>
    <BCarouselSlide :img-src="Img6">
      <div class="carousel-caption d-none d-md-block">
        <h5>Working at a coffee shop</h5>
        <p>
          A wonderful serenity has taken possession of my entire
          soul, like these sweet mornings of spring.
        </p>
      </div>
    </BCarouselSlide>
  </BCarousel>
`)])],-1);function me(ge,_e,pe,he,t,fe){const g=a("PageHeader"),n=a("CardHeader"),i=a("BCarouselSlide"),r=a("BCarousel"),c=a("BCardBody"),d=a("BCard"),u=a("BCol"),m=a("BRow"),_=a("Layout");return U(),W(_,null,{default:o(()=>[e(g,{title:"Carousel",pageTitle:"Base UI"}),e(m,null,{default:o(()=>[e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"Slides Only"}),e(c,null,{default:o(()=>[V,s("div",A,[e(r,{interval:2e3,ride:"carousel",class:"carousel slide"},{default:o(()=>[e(i,{active:"","img-src":t.Img1},null,8,["img-src"]),e(i,{"img-src":t.Img2},null,8,["img-src"]),e(i,{"img-src":t.Img3},null,8,["img-src"])]),_:1})]),G]),_:1})]),_:1})]),_:1}),e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"With Controls"}),e(c,null,{default:o(()=>[L,s("div",N,[e(r,{class:"carousel slide",ride:"carousel",interval:2e3,controls:""},{default:o(()=>[e(i,{active:"","img-src":t.Img4},null,8,["img-src"]),e(i,{"img-src":t.Img5},null,8,["img-src"]),e(i,{"img-src":t.Img6},null,8,["img-src"])]),_:1})]),P]),_:1})]),_:1})]),_:1})]),_:1}),e(m,null,{default:o(()=>[e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"with Indicators"}),e(c,null,{default:o(()=>[j,s("div",q,[e(r,{class:"carousel slide",ride:"carousel",interval:2e3,controls:"",indicators:""},{default:o(()=>[e(i,{active:"","img-src":t.Img3},null,8,["img-src"]),e(i,{"img-src":t.Img2},null,8,["img-src"]),e(i,{"img-src":t.Img1},null,8,["img-src"])]),_:1})]),O]),_:1})]),_:1})]),_:1}),e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"with Captions"}),e(c,null,{default:o(()=>[R,s("div",Y,[e(r,{class:"carousel slide",ride:"carousel",interval:2e3,controls:"",indicators:""},{default:o(()=>[e(i,{active:"","img-src":t.Img7},{default:o(()=>[z,E]),_:1},8,["img-src"]),e(i,{"img-src":t.Img2},{default:o(()=>[F,J]),_:1},8,["img-src"]),e(i,{"img-src":t.Img9},{default:o(()=>[K,M]),_:1},8,["img-src"])]),_:1})]),Q]),_:1})]),_:1})]),_:1})]),_:1}),e(m,null,{default:o(()=>[e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"Crossfade Animation"}),e(c,null,{default:o(()=>[X,s("div",Z,[e(r,{class:"carousel slide carousel-fade",ride:"carousel",interval:2e3,controls:"",indicators:""},{default:o(()=>[e(i,{active:"","img-src":t.Img1},null,8,["img-src"]),e(i,{"img-src":t.Img2},null,8,["img-src"]),e(i,{"img-src":t.Img3},null,8,["img-src"])]),_:1})]),$]),_:1})]),_:1})]),_:1}),e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"Individual carousel-item Interval"}),e(c,null,{default:o(()=>[ee,s("div",se,[e(r,{class:"carousel slide",ride:"carousel",interval:2e3,controls:""},{default:o(()=>[e(i,{active:"","img-src":t.Img1},null,8,["img-src"]),e(i,{"img-src":t.Img11},null,8,["img-src"]),e(i,{"img-src":t.Img10},null,8,["img-src"])]),_:1})]),oe]),_:1})]),_:1})]),_:1})]),_:1}),e(m,null,{default:o(()=>[e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"Disable Touch Swiping"}),e(c,null,{default:o(()=>[le,s("div",te,[e(r,{class:"carousel slide",interval:2e3,controls:"","no-touch":"true"},{default:o(()=>[e(i,{active:"","img-src":t.Img9},null,8,["img-src"]),e(i,{"img-src":t.Img8},null,8,["img-src"]),e(i,{"img-src":t.Img7},null,8,["img-src"])]),_:1})]),ie]),_:1})]),_:1})]),_:1}),e(u,{xl:"6"},{default:o(()=>[e(d,{"no-body":""},{default:o(()=>[e(n,{title:"Dark Variant"}),e(c,null,{default:o(()=>[ae,s("div",ne,[e(r,{class:"carousel slide carousel-dark",ride:"carousel",interval:2e3,controls:""},{default:o(()=>[e(i,{active:"","img-src":t.Img4},{default:o(()=>[re]),_:1},8,["img-src"]),e(i,{"img-src":t.Img5},{default:o(()=>[ce]),_:1},8,["img-src"]),e(i,{"img-src":t.Img6},{default:o(()=>[de]),_:1},8,["img-src"])]),_:1})]),ue]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}const Pe=T(H,[["render",me]]);export{Pe as default};
