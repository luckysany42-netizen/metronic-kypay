<template>
  <div id="kt_app_header" class="app-header">
    <div
      class="app-container d-flex align-items-stretch justify-content-between"
      :class="{
        'container-fluid': headerWidthFluid,
        'container-xxl': !headerWidthFluid,
      }"
    >
      <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" v-tooltip title="Show sidebar menu">
        <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
          <KTIcon icon-name="abstract-14" icon-class="fs-2 fs-md-1" />
        </div>
      </div>

      <!-- Mobile logo -->
      <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
        <router-link to="/" class="d-lg-none">
          <img alt="Logo" :src="getAssetPath('media/logos/logo-kypay-small.png')" class="h-30px" />
        </router-link>
      </div>

      <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
        <KTHeaderMenu />
        <KTHeaderNavbar />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { getAssetPath } from "@/core/helpers/assets";
import { defineComponent } from "vue";
import KTHeaderMenu from "@/layouts/default-layout/components/header/menu/Menu.vue";
import KTHeaderNavbar from "@/layouts/default-layout/components/header/Navbar.vue";
import { headerWidthFluid } from "@/layouts/default-layout/config/helper";

export default defineComponent({
  name: "layout-header",
  components: { KTHeaderMenu, KTHeaderNavbar },
  setup() {
    return { headerWidthFluid, getAssetPath };
  },
});
</script>