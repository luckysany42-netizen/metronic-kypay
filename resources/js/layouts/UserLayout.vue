<template>
  <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

      <!-- ===== HEADER ===== -->
      <div id="kt_app_header" class="app-header">
        <div class="app-container container-fluid d-flex align-items-stretch justify-content-between">

          <!-- Mobile sidebar toggle -->
          <div class="d-flex align-items-center d-lg-none ms-n3 me-1">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
              <KTIcon icon-name="abstract-14" icon-class="fs-2 fs-md-1" />
            </div>
          </div>

          <!-- Mobile logo -->
          <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <router-link to="/user/dashboard" class="d-lg-none">
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay-small.png')" class="h-30px" />
            </router-link>
          </div>

          <!-- Header right -->
          <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">

            <!-- Header menu kiri (Desktop) -->
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch d-none d-lg-flex"
              data-kt-swapper="true"
              data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
              data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}"
            >
              <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" data-kt-menu="true">
                <!-- Dashboard -->
                <div class="menu-item me-lg-1">
                  <router-link to="/user/dashboard" class="menu-link" active-class="active">
                    <span class="menu-title">Dashboard</span>
                  </router-link>
                </div>
                <!-- KyPay Wallet -->
                <div class="menu-item me-lg-1">
                  <router-link to="/user/wallet" class="menu-link" active-class="active">
                    <span class="menu-title">Wallet</span>
                  </router-link>
                </div>
                <!-- Account dropdown -->
                <div
                  data-kt-menu-trigger="click"
                  data-kt-menu-placement="bottom-start"
                  class="menu-item menu-lg-down-accordion me-lg-1"
                >
                  <span class="menu-link py-3" :class="{ active: route.path.includes('/user/profile') }">
                    <span class="menu-title">Account</span>
                    <span class="menu-arrow d-lg-none"></span>
                  </span>
                  <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-175px">
                    <div class="menu-item">
                      <router-link to="/user/profile" class="menu-link" active-class="active">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">My Profile</span>
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navbar kanan -->
            <div class="app-navbar flex-shrink-0">

              <!-- Theme mode toggle -->
              <div class="app-navbar-item ms-1 ms-md-3">
                <a
                  href="#"
                  class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                  data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                  data-kt-menu-attach="parent"
                  data-kt-menu-placement="bottom-end"
                >
                  <KTIcon v-if="themeMode === 'light'" icon-name="night-day" icon-class="fs-2" />
                  <KTIcon v-else icon-name="moon" icon-class="fs-2" />
                </a>
                <KTThemeModeSwitcher />
              </div>

              <!-- User avatar dropdown -->
              <div class="app-navbar-item ms-1 ms-md-3">
                <div
                  class="cursor-pointer symbol symbol-35px"
                  data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                  data-kt-menu-attach="parent"
                  data-kt-menu-placement="bottom-end"
                >
                  <img v-if="avatarUrl" :src="avatarUrl" alt="user" class="rounded-circle object-fit-cover" />
                  <div v-else class="symbol-label fw-bold bg-primary text-white fs-6">
                    {{ authStore.user.name?.charAt(0).toUpperCase() ?? 'U' }}
                  </div>
                </div>
                <!-- User dropdown menu -->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-250px" data-kt-menu="true">
                  <div class="menu-item px-3">
                    <div class="menu-content d-flex align-items-center px-3">
                      <div class="symbol symbol-50px me-5">
                        <img v-if="avatarUrl" :src="avatarUrl" alt="user" class="rounded-circle object-fit-cover" />
                        <div v-else class="symbol-label fw-bold bg-primary text-white fs-4">
                          {{ authStore.user.name?.charAt(0).toUpperCase() ?? 'U' }}
                        </div>
                      </div>
                      <div class="d-flex flex-column">
                        <div class="fw-bold d-flex align-items-center fs-5">
                          {{ authStore.user.name }}
                          <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">User</span>
                        </div>
                        <span class="fw-semibold text-muted fs-7">{{ authStore.user.email }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="separator my-2"></div>
                  <div class="menu-item px-5">
                    <router-link to="/user/profile" class="menu-link px-5">My Profile</router-link>
                  </div>
                  <div class="separator my-2"></div>
                  <div class="menu-item px-5">
                    <a @click="onLogout" class="menu-link px-5 cursor-pointer text-danger">Sign Out</a>
                  </div>
                </div>
              </div>

              <!-- Mobile header menu toggle -->
              <div class="app-navbar-item d-lg-none ms-2 me-n2">
                <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px" id="kt_app_header_menu_toggle">
                  <KTIcon icon-name="element-4" icon-class="fs-2" />
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- ===== END HEADER ===== -->

      <!-- ===== WRAPPER ===== -->
      <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

        <!-- ===== SIDEBAR ===== -->
        <div
          ref="sidebarRef"
          id="kt_app_sidebar"
          class="app-sidebar flex-column"
          data-kt-drawer="true"
          data-kt-drawer-name="app-sidebar"
          data-kt-drawer-activate="{default: true, lg: false}"
          data-kt-drawer-overlay="true"
          data-kt-drawer-width="225px"
          data-kt-drawer-direction="start"
          data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle"
        >
          <!-- Logo sidebar -->
          <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
            <router-link to="/user/dashboard">
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay.png')" class="h-40px app-sidebar-logo-default" style="max-width: 160px; object-fit: contain;" />
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay-small.png')" class="h-20px app-sidebar-logo-minimize" />
            </router-link>
          </div>

          <!-- Menu sidebar -->
          <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
            <div
              id="kt_app_sidebar_menu_wrapper"
              class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
              data-kt-scroll="true"
              data-kt-scroll-activate="true"
              data-kt-scroll-height="auto"
              data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
              data-kt-scroll-wrappers="#kt_app_sidebar_menu"
              data-kt-scroll-offset="5px"
            >
              <div id="#kt_app_sidebar_menu" class="menu menu-column menu-rounded menu-sub-indention px-3" data-kt-menu="true">

                <template v-for="(section, i) in UserMenuConfig" :key="i">
                  <!-- Section heading -->
                  <div v-if="section.heading" class="menu-item pt-5">
                    <div class="menu-content">
                      <span class="menu-heading fw-bold text-uppercase fs-7">{{ section.heading }}</span>
                    </div>
                  </div>

                  <template v-for="(menuItem, j) in section.pages" :key="j">
                    <!-- Item biasa (heading + route langsung) -->
                    <div v-if="menuItem.heading && menuItem.route && !menuItem.sub" class="menu-item">
                      <router-link class="menu-link" active-class="active" :to="menuItem.route">
                        <span class="menu-icon">
                          <KTIcon :icon-name="menuItem.keenthemesIcon || 'element-11'" icon-class="fs-2" />
                        </span>
                        <span class="menu-title">{{ menuItem.heading }}</span>
                      </router-link>
                    </div>

                    <!-- Item dengan submenu (sectionTitle + sub) -->
                    <div
                      v-if="menuItem.sectionTitle"
                      :class="{ show: hasActiveChildren(menuItem.route || '') }"
                      class="menu-item menu-accordion"
                      data-kt-menu-sub="accordion"
                      data-kt-menu-trigger="click"
                    >
                      <span class="menu-link">
                        <span class="menu-icon">
                          <KTIcon :icon-name="menuItem.keenthemesIcon || 'element-11'" icon-class="fs-2" />
                        </span>
                        <span class="menu-title">{{ menuItem.sectionTitle }}</span>
                        <span class="menu-arrow"></span>
                      </span>
                      <div
                        :class="{ show: hasActiveChildren(menuItem.route || '') }"
                        class="menu-sub menu-sub-accordion"
                      >
                        <div v-for="(sub, k) in menuItem.sub" :key="k" class="menu-item">
                          <router-link v-if="sub.route" class="menu-link" active-class="active" :to="sub.route">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">{{ sub.heading }}</span>
                          </router-link>
                        </div>
                      </div>
                    </div>
                  </template>
                </template>

                <!-- Sign Out -->
                <div class="menu-item pt-5">
                  <div class="menu-content">
                    <span class="menu-heading fw-bold text-uppercase fs-7">Account</span>
                  </div>
                </div>
                <div class="menu-item">
                  <a class="menu-link cursor-pointer" @click="onLogout">
                    <span class="menu-icon"><KTIcon icon-name="exit-right" icon-class="fs-2" /></span>
                    <span class="menu-title">Sign Out</span>
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>
        <!-- ===== END SIDEBAR ===== -->

        <!-- ===== MAIN CONTENT ===== -->
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
          <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
              <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                  <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ currentPageTitle }}
                  </h1>
                </div>
              </div>
            </div>
            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
              <div id="kt_app_content_container" class="app-container container-fluid">
                <router-view />
              </div>
            </div>
          </div>
          <!-- Footer -->
          <div id="kt_app_footer" class="app-footer">
            <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
              <div class="text-gray-900 order-2 order-md-1">
                <span class="text-muted fw-semibold me-1">{{ currentYear }}&copy;</span>
                <span class="text-gray-800 fw-bold">KyPay</span>
              </div>
            </div>
          </div>
        </div>
        <!-- ===== END MAIN CONTENT ===== -->

      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, nextTick, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getAssetPath } from "@/core/helpers/assets";
import { useAuthStore } from "@/stores/auth";
import { reinitializeComponents } from "@/core/plugins/keenthemes";
import LayoutService from "@/core/services/LayoutService";
import UserMenuConfig from "@/layouts/default-layout/config/UserMenuConfig";
import KTThemeModeSwitcher from "@/layouts/default-layout/components/theme-mode/ThemeModeSwitcher.vue";
import { ThemeModeComponent } from "@/assets/ts/layout";
import { useThemeStore } from "@/stores/theme";

export default defineComponent({
  name: "user-layout",
  components: { KTThemeModeSwitcher },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const themeStore = useThemeStore();
    const sidebarRef = ref<HTMLElement | null>(null);
    const currentYear = new Date().getFullYear();

    const currentPageTitle = computed(() => route.meta.pageTitle || "Dashboard");

    const avatarUrl = computed(() => {
      if (!authStore.user?.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    const themeMode = computed(() => {
      if (themeStore.mode === "system") return ThemeModeComponent.getSystemMode();
      return themeStore.mode;
    });

    const hasActiveChildren = (match: string) => {
      return match ? route.path.indexOf(match) !== -1 : false;
    };

    onMounted(() => {
      LayoutService.init();
      nextTick(() => { reinitializeComponents(); });
    });

    watch(() => route.path, () => {
      nextTick(() => {
        LayoutService.init();
        reinitializeComponents();
      });
    });

    const onLogout = () => {
      authStore.logout();
      router.push({ name: "user-sign-in" });
    };

    return {
      getAssetPath,
      authStore,
      avatarUrl,
      themeMode,
      UserMenuConfig,
      currentPageTitle,
      currentYear,
      sidebarRef,
      onLogout,
      hasActiveChildren,
      route,
    };
  },
});
</script>

<style>
/*
  Fix header double di UserLayout:
  Metronic kadang inject header kedua via LayoutService.init()
  Ini hide semua #kt_app_header yang bukan bagian dari template kita
  Template kita ada di dalam .app-page sebagai child pertama
*/
.app-page > #kt_app_header ~ #kt_app_header {
  display: none !important;
}
/* Logo sidebar desktop selalu tampil */
#kt_app_sidebar .app-sidebar-logo-default {
  display: block !important;
}
</style>