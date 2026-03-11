<template>
  <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

      <!-- Header inline — tanpa KTHeader supaya menu tidak dobel -->
      <div id="kt_app_header" class="app-header">
        <div class="app-container container-fluid d-flex align-items-stretch justify-content-between">
          <div class="d-flex align-items-center d-lg-none ms-n3 me-1">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
              <KTIcon icon-name="abstract-14" icon-class="fs-2 fs-md-1" />
            </div>
          </div>
          <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <router-link to="/user/dashboard" class="d-lg-none">
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay-small.png')" class="h-30px" />
            </router-link>
          </div>
          <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            <div class="d-flex align-items-stretch flex-grow-1"></div>
            <KTHeaderNavbar />
          </div>
        </div>
      </div>

      <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

        <div
          v-if="displaySidebar"
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
          <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
            <router-link to="/user/dashboard">
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay.png')" class="h-40px app-sidebar-logo-default" style="max-width:160px;object-fit:contain;" />
              <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay-small.png')" class="h-20px app-sidebar-logo-minimize" />
            </router-link>
            <div
              ref="toggleRef"
              id="kt_app_sidebar_toggle"
              class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
              data-kt-toggle="true"
              data-kt-toggle-state="active"
              data-kt-toggle-target="body"
              data-kt-toggle-name="app-sidebar-minimize"
            >
              <KTIcon icon-name="black-left-line" icon-class="fs-3 rotate-180 ms-1" />
            </div>
          </div>

          <UserSidebarMenu />
        </div>

        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
          <div class="d-flex flex-column flex-column-fluid">
            <KTToolbar />
            <div id="kt_app_content" class="app-content flex-column-fluid">
              <div class="app-container container-fluid">
                <router-view />
              </div>
            </div>
          </div>
          <KTFooter />
        </div>

      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick, onBeforeMount, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { getAssetPath } from "@/core/helpers/assets";
import { reinitializeComponents } from "@/core/plugins/keenthemes";
import LayoutService from "@/core/services/LayoutService";
import { displaySidebar } from "@/layouts/default-layout/config/helper";
import KTHeaderNavbar from "@/layouts/default-layout/components/header/Navbar.vue";
import KTToolbar from "@/layouts/default-layout/components/toolbar/Toolbar.vue";
import KTFooter from "@/layouts/default-layout/components/footer/Footer.vue";
import UserSidebarMenu from "@/layouts/default-layout/components/sidebar/UserSidebarMenu.vue";

export default defineComponent({
  name: "user-layout",
  components: { KTHeaderNavbar, KTToolbar, KTFooter, UserSidebarMenu },
  setup() {
    const route = useRoute();
    const sidebarRef = ref<HTMLElement | null>(null);
    const toggleRef = ref<HTMLElement | null>(null);

    onBeforeMount(() => {
      LayoutService.init();
    });

    onMounted(() => {
      nextTick(() => reinitializeComponents());
    });

    watch(() => route.path, () => {
      nextTick(() => reinitializeComponents());
    });

    return { getAssetPath, displaySidebar, sidebarRef, toggleRef };
  },
});
</script>