<script setup>
import { layoutMethods } from "@/state/helpers";
import { Link, router } from '@inertiajs/vue3';
const logout = () => {
  router.post(route('logout'));
};
</script>

<script>
import { Link } from '@inertiajs/vue3';
import simplebar from "simplebar-vue";

import i18n from "../i18n";

import us_flag from "@assets/images/flags/us.svg";
import sa from "@assets/images/flags/sa.svg";

import img1 from "@assets/images/products/img-1.png";
import img2 from "@assets/images/products/img-2.png";
import img3 from "@assets/images/products/img-3.png";
import img4 from "@assets/images/products/img-4.png";
import img5 from "@assets/images/products/img-5.png";

/**
 * Nav-bar Component
 */
export default {
  data() {
    return {
      languages: [{
        flag: us_flag,
        language: "en",
        title: "English",
      },
      {
        flag: sa,
        language: "ar",
        title: "العربية",
      },
      ],
      cartItems: [
        {
          id: 1,
          productImage: img1,
          productName: "Branded T-Shirts",
          productLink: "/ecommerce/product-details",
          quantity: "10 x $32",
          itemPrice: "320",
        },
        {
          id: 2,
          productImage: img2,
          productName: "Bentwood Chair",
          productLink: "/ecommerce/product-details",
          quantity: "5 x $18",
          itemPrice: "89",
        },
        {
          id: 3,
          productImage: img3,
          productName: "Borosil Paper Cup",
          productLink: "/ecommerce/product-details",
          quantity: "3 x $250",
          itemPrice: "750",
        },
        {
          id: 4,
          productImage: img4,
          productName: "Gray Styled T-Shirt",
          productLink: "/ecommerce/product-details",
          quantity: "1 x $1250",
          itemPrice: "1250",
        },
        {
          id: 5,
          productImage: img5,
          productName: "Stillbird Helmet",
          productLink: "/ecommerce/product-details",
          quantity: "2 x $495",
          itemPrice: "990",
        },
      ],
      lan: i18n.locale,
      text: null,
      flag: null,
      value: null,
      myVar: 1,
    };
  },
  components: {
    simplebar,
    Link,
  },

  methods: {
    ...layoutMethods,
    isCustomDropdown() {
      //Search bar
      var searchOptions = document.getElementById("search-close-options");
      var dropdown = document.getElementById("search-dropdown");
      var searchInput = document.getElementById("search-options");





    },
    toggleHamburgerMenu() {
      var windowSize = document.documentElement.clientWidth;
      let layoutType = document.documentElement.getAttribute("data-layout");

      document.documentElement.setAttribute("data-sidebar-visibility", "show");
      let visiblilityType = document.documentElement.getAttribute("data-sidebar-visibility");

      if (windowSize > 767)
        document.querySelector(".hamburger-icon").classList.toggle("open");

      //For collapse horizontal menu
      if (
        document.documentElement.getAttribute("data-layout") === "horizontal"
      ) {
        document.body.classList.contains("menu") ?
          document.body.classList.remove("menu") :
          document.body.classList.add("menu");
      }

      //For collapse vertical menu

      if (visiblilityType === "show" && (layoutType === "vertical" || layoutType === "semibox")) {
        if (windowSize < 1025 && windowSize > 767) {
          document.body.classList.remove("vertical-sidebar-enable");
          document.documentElement.getAttribute("data-sidebar-size") == "sm" ?
            document.documentElement.setAttribute("data-sidebar-size", "") :
            document.documentElement.setAttribute("data-sidebar-size", "sm");
        } else if (windowSize > 1025) {
          document.body.classList.remove("vertical-sidebar-enable");
          document.documentElement.getAttribute("data-sidebar-size") == "lg" ?
            document.documentElement.setAttribute("data-sidebar-size", "sm") :
            document.documentElement.setAttribute("data-sidebar-size", "lg");
        } else if (windowSize <= 767) {
          document.body.classList.add("vertical-sidebar-enable");
          document.documentElement.setAttribute("data-sidebar-size", "lg");
        }
      }

      //Two column menu
      if (document.documentElement.getAttribute("data-layout") == "twocolumn") {
        document.body.classList.contains("twocolumn-panel") ?
          document.body.classList.remove("twocolumn-panel") :
          document.body.classList.add("twocolumn-panel");
      }
    },
    toggleMenu() {
      this.$parent.toggleMenu();
    },
    toggleRightSidebar() {
      this.$parent.toggleRightSidebar();
    },
    initFullScreen() {
      document.body.classList.toggle("fullscreen-enable");
      if (
        !document.fullscreenElement &&
        /* alternative standard method */
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement
      ) {
        // current working methods
        if (document.documentElement.requestFullscreen) {
          document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
          document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
          document.documentElement.webkitRequestFullscreen(
            Element.ALLOW_KEYBOARD_INPUT
          );
        }
      } else {
        if (document.cancelFullScreen) {
          document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
          document.webkitCancelFullScreen();
        }
      }
    },
    setLanguage(locale, country, flag) {
      this.lan = locale;
      this.text = country;
      this.flag = flag;
      if (document.getElementById("header-lang-img")) {
        document.getElementById("header-lang-img").setAttribute("src", flag);
      }
      i18n.global.locale = locale;
      sessionStorage.setItem("locale", locale);
    },
    toggleDarkMode() {

      if (document.documentElement.getAttribute("data-bs-theme") == "dark") {
        document.documentElement.setAttribute("data-bs-theme", "light");
      } else {
        document.documentElement.setAttribute("data-bs-theme", "dark");
      }

      const mode = document.documentElement.getAttribute("data-bs-theme")
      this.changeMode({
        mode: mode,
      });
    },
    removeItem(cartItem) {
      this.cartItems = this.cartItems.filter(item => item.id !== cartItem.id)
      this.$emit("cart-item-price", this.cartItems.length);
    },
  },

  computed: {
    calculateTotalPrice() {
      return this.cartItems.reduce((total, item) => total + parseFloat(item.itemPrice), 0).toFixed(2);
    },
  },
  mounted() {
    this.lan = i18n.global.locale || sessionStorage.getItem("locale") || "en";
    this.languages.forEach((item) => {
      if (item.language == this.lan) {
        this.text = item.title;
        this.flag = item.flag;
        if (document.getElementById("header-lang-img")) {
          document.getElementById("header-lang-img").setAttribute("src", item.flag);
        }
      }
    });

    document.addEventListener("scroll", function () {
      var pageTopbar = document.getElementById("page-topbar");
      if (pageTopbar) {
        document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50 ? pageTopbar.classList.add(
          "topbar-shadow") : pageTopbar.classList.remove("topbar-shadow");
      }
    });
    if (document.getElementById("topnav-hamburger-icon"))
      document.getElementById("topnav-hamburger-icon").addEventListener("click", this.toggleHamburgerMenu);

    this.isCustomDropdown();
  },
};
</script>

<template>
  <header id="page-topbar">
    <div class="layout-width">
      <div class="navbar-header">
        <div class="d-flex">
          <!-- LOGO -->
          <div class="navbar-brand-box horizontal-logo">
            <Link href="/" class="logo logo-dark">
              <span class="logo-sm">
                <img src="@assets/images/logo-sm.png" alt="" height="22" />
              </span>
              <span class="logo-lg">
                <img src="@assets/images/logo-dark.png" alt="" height="17" />
              </span>
            </Link>

            <Link href="/" class="logo logo-light">
              <span class="logo-sm">
                <img src="@assets/images/logo-sm.png" alt="" height="22" />
              </span>
              <span class="logo-lg">
                <img src="@assets/images/logo-light.png" alt="" height="17" />
              </span>
            </Link>
          </div>

          <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
            id="topnav-hamburger-icon">
            <span class="hamburger-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </button>

          <!-- App Search-->

        </div>

        <div class="d-flex align-items-center">

          <!-- Language Selector -->
          <BDropdown variant="link" class="ms-1 header-item" toggle-class="btn-icon btn-topbar rounded-circle" menu-class="dropdown-menu-end">
            <template #button-content>
              <img id="header-lang-img" :src="flag || languages.find(l => l.language === lan)?.flag || languages[0].flag" alt="Language" height="16" class="rounded" />
            </template>
            <template v-for="(lang, index) in languages" :key="index">
              <BButton 
                variant="none" 
                class="dropdown-item notify-item language py-2" 
                :class="{ active: lan === lang.language }"
                @click="setLanguage(lang.language, lang.title, lang.flag)"
              >
                <img :src="lang.flag" alt="user-image" class="me-2 rounded" height="18" />
                <span class="align-middle">{{ lang.title }}</span>
              </BButton>
            </template>
          </BDropdown>




          <div class="ms-1 header-item d-none d-sm-flex">
            <BButton type="button" variant="ghost-secondary" class="btn-icon btn-topbar rounded-circle"
              data-toggle="fullscreen" @click="initFullScreen">
              <i class="bx bx-fullscreen fs-22"></i>
            </BButton>
          </div>




          <BDropdown variant="link" class="ms-sm-3 header-item topbar-user" toggle-class="rounded-circle arrow-none" menu-class="dropdown-menu-end" :offset="{ alignmentAxis: -14, crossAxis: 0, mainAxis: 0 }">
            <template #button-content>
              <span class="d-flex align-items-center">
                <img v-if="$page.props.jetstream.managesProfilePhotos" class="rounded-circle header-profile-user" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                <span class="text-start ms-xl-2">
                  <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $page.props.auth.user.name }}</span>
                  <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Founder</span>
                </span>
              </span>
            </template>
            <h6 class="dropdown-header">Welcome {{ $page.props.auth.user.name }}!</h6>
            <Link class="dropdown-item" :href="route('profile.show')"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
            <span class="align-middle">Profile</span>
            </Link>







            <!-- Authentication -->
            <form method="POST" @submit.prevent="logout" class="dropdown-item">
              <BButton variant="none" type="submit" class="btn p-0"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> Logout</BButton>
            </form>
          </BDropdown>
        </div>
      </div>
    </div>
  </header>
</template>
