<template>
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
      <div
        id="kt_app_sidebar_menu"
        class="menu menu-column menu-rounded menu-sub-indention px-3"
        data-kt-menu="true"
      >
        <template v-for="(section, i) in UserMenuConfig" :key="i">
          <!-- Section heading -->
          <div v-if="section.heading" class="menu-item pt-5">
            <div class="menu-content">
              <span class="menu-heading fw-bold text-uppercase fs-7">{{ section.heading }}</span>
            </div>
          </div>

          <template v-for="(menuItem, j) in section.pages" :key="j">
            <!-- Item biasa -->
            <div v-if="menuItem.heading && menuItem.route && !menuItem.sub" class="menu-item">
              <router-link class="menu-link" active-class="active" :to="menuItem.route">
                <span class="menu-icon">
                  <KTIcon :icon-name="menuItem.keenthemesIcon || 'element-11'" icon-class="fs-2" />
                </span>
                <span class="menu-title">{{ menuItem.heading }}</span>
              </router-link>
            </div>

            <!-- Item dengan submenu -->
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
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import UserMenuConfig from "@/layouts/default-layout/config/UserMenuConfig";

export default defineComponent({
  name: "user-sidebar-menu",
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();

    const hasActiveChildren = (match: string) => {
      return match ? route.path.indexOf(match) !== -1 : false;
    };

    const onLogout = () => {
      authStore.logout();
      router.push({ name: "user-sign-in" });
    };

    return { UserMenuConfig, hasActiveChildren, onLogout };
  },
});
</script>