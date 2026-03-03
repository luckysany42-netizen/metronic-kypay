<template>
  <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
    <router-link to="/">
      <!-- Dark sidebar atau dark mode -->
      <img
        v-if="layout === 'dark-sidebar' || (themeMode === 'dark' && layout === 'light-sidebar')"
        alt="Logo"
        :src="getAssetPath('media/logos/logo-kypay.png')"
        class="h-40px app-sidebar-logo-default"
      />
      <!-- Light sidebar -->
      <img
        v-if="themeMode === 'light' && layout === 'light-sidebar'"
        alt="Logo"
        :src="getAssetPath('media/logos/logo-kypay.png')"
        class="h-40px app-sidebar-logo-default"
      />
      <!-- Minimize -->
      <img
        alt="Logo"
        :src="getAssetPath('media/logos/logo-kypay-small.png')"
        class="h-20px app-sidebar-logo-minimize"
      />
    </router-link>

    <div
      v-if="sidebarToggleDisplay"
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
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { ToggleComponent } from "@/assets/ts/components";
import { getAssetPath } from "@/core/helpers/assets";
import { layout, sidebarToggleDisplay, themeMode } from "@/layouts/default-layout/config/helper";

interface IProps {
  sidebarRef: HTMLElement | null;
}

const props = defineProps<IProps>();
const toggleRef = ref<HTMLFormElement | null>(null);

onMounted(() => {
  setTimeout(() => {
    const toggleObj = ToggleComponent.getInstance(toggleRef.value!) as ToggleComponent | null;
    if (toggleObj === null) return;
    toggleObj.on("kt.toggle.change", function () {
      props.sidebarRef?.classList.add("animating");
      setTimeout(function () {
        props.sidebarRef?.classList.remove("animating");
      }, 300);
    });
  }, 1);
});
</script>