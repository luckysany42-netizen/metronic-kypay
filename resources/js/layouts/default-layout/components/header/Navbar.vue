<template>
  <!--begin::Navbar-->
  <div class="app-navbar flex-shrink-0">

    <!--begin::Theme mode-->
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
    <!--end::Theme mode-->

    <!--begin::User menu-->
    <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
      <div
        class="cursor-pointer symbol symbol-35px"
        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
        data-kt-menu-attach="parent"
        data-kt-menu-placement="bottom-end"
      >
        <img
          v-if="userAvatar"
          :src="userAvatar"
          class="rounded-3 object-fit-cover"
          alt="user"
        />
        <div v-else class="symbol-label fw-bold bg-primary text-white fs-6 rounded-3">
          {{ userInitial }}
        </div>
      </div>
      <KTUserMenu />
    </div>
    <!--end::User menu-->

    <!--begin::Header menu toggle (mobile)-->
    <div class="app-navbar-item d-lg-none ms-2 me-n2" v-tooltip title="Show header menu">
      <div
        class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px"
        id="kt_app_header_menu_toggle"
      >
        <KTIcon icon-name="element-4" icon-class="fs-2" />
      </div>
    </div>
    <!--end::Header menu toggle-->

  </div>
  <!--end::Navbar-->
</template>

<script lang="ts">
import { computed, defineComponent } from "vue";
import KTUserMenu from "@/layouts/default-layout/components/menus/UserAccountMenu.vue";
import KTThemeModeSwitcher from "@/layouts/default-layout/components/theme-mode/ThemeModeSwitcher.vue";
import { ThemeModeComponent } from "@/assets/ts/layout";
import { useThemeStore } from "@/stores/theme";
import { useAuthStore } from "@/stores/auth";
import { getAssetPath } from "@/core/helpers/assets";

export default defineComponent({
  name: "header-navbar",
  components: { KTUserMenu, KTThemeModeSwitcher },
  setup() {
    const themeStore = useThemeStore();
    const authStore = useAuthStore();

    const themeMode = computed(() => {
      if (themeStore.mode === "system") return ThemeModeComponent.getSystemMode();
      return themeStore.mode;
    });

    const userAvatar = computed(() => {
      if (!authStore.user?.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    const userInitial = computed(() => {
      return authStore.user?.name?.charAt(0).toUpperCase() ?? "A";
    });

    return { themeMode, userAvatar, userInitial, getAssetPath };
  },
});
</script>