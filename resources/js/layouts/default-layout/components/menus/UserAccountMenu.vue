<template>
  <!--begin::Menu-->
  <div
    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold py-4 fs-6 w-275px"
    data-kt-menu="true"
  >
    <!--begin::Menu item-->
    <div class="menu-item px-3">
      <div class="menu-content d-flex align-items-center px-3">
        <!--begin::Avatar-->
        <div class="symbol symbol-50px me-5">
          <img v-if="avatarUrl" :src="avatarUrl" alt="avatar" class="rounded-circle object-fit-cover" />
          <div v-else class="symbol-label fw-bold bg-primary text-white fs-4">
            {{ userInitial }}
          </div>
        </div>
        <!--end::Avatar-->
        <!--begin::Username-->
        <div class="d-flex flex-column">
          <div class="fw-bold d-flex align-items-center fs-5">
            {{ authStore.user.name }}
            <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">Admin</span>
          </div>
          <span class="fw-semibold text-muted text-hover-primary fs-7">{{ authStore.user.email }}</span>
        </div>
        <!--end::Username-->
      </div>
    </div>
    <!--end::Menu item-->

    <div class="separator my-2"></div>

    <!--begin::Menu item-->
    <div class="menu-item px-5">
      <router-link to="/admin/profile" class="menu-link px-5">
        Account Settings
      </router-link>
    </div>
    <!--end::Menu item-->

    <div class="separator my-2"></div>

    <!--begin::Menu item-->
    <div class="menu-item px-5">
      <a @click="signOut" class="menu-link px-5 cursor-pointer text-danger">
        Sign Out
      </a>
    </div>
    <!--end::Menu item-->
  </div>
  <!--end::Menu-->
</template>

<script lang="ts">
import { computed, defineComponent } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

export default defineComponent({
  name: "kt-user-menu",
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();

    const avatarUrl = computed(() => {
      if (!authStore.user?.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    const userInitial = computed(() => {
      return authStore.user?.name?.charAt(0).toUpperCase() ?? "A";
    });

    const signOut = () => {
      authStore.logout();
      router.push({ name: "sign-in" });
    };

    return { authStore, avatarUrl, userInitial, signOut };
  },
});
</script>