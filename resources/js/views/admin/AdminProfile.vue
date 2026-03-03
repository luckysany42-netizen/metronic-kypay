<template>
  <div class="d-flex flex-column gap-7">

    <!-- Header Card -->
    <div class="card">
      <div class="card-body pt-9 pb-0">
        <div class="d-flex flex-wrap flex-sm-nowrap gap-7">
          <!-- Avatar -->
          <div class="me-7">
            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
              <img v-if="avatarPreview" :src="avatarPreview" alt="avatar" class="rounded-3 object-fit-cover" />
              <div v-else class="symbol-label fw-bold bg-primary text-white fs-1 rounded-3">
                {{ authStore.user.name?.charAt(0).toUpperCase() }}
              </div>
              <label class="position-absolute translate-middle bottom-0 start-100 mb-6 cursor-pointer">
                <span class="badge badge-circle badge-primary w-30px h-30px">
                  <KTIcon icon-name="pencil" icon-class="fs-5 text-white" />
                </span>
                <input type="file" class="d-none" accept="image/*" @change="onAvatarChange" />
              </label>
            </div>
          </div>
          <!-- Info -->
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between flex-wrap mb-4">
              <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="text-gray-900 fs-2 fw-bold">{{ authStore.user.name }}</span>
                  <span class="badge badge-light-success">Admin</span>
                </div>
                <div class="d-flex gap-4 text-muted fs-6">
                  <span v-if="authStore.user.job_title">
                    <KTIcon icon-name="briefcase" icon-class="fs-4 me-1" />{{ authStore.user.job_title }}
                  </span>
                  <span>
                    <KTIcon icon-name="sms" icon-class="fs-4 me-1" />{{ authStore.user.email }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Tabs -->
        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mt-4">
          <li class="nav-item">
            <a class="nav-link cursor-pointer pb-4" :class="{ active: tab === 'profile' }" @click="tab = 'profile'">
              Edit Profil
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link cursor-pointer pb-4" :class="{ active: tab === 'password' }" @click="tab = 'password'">
              Ubah Password
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Tab: Edit Profil -->
    <div v-if="tab === 'profile'" class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Profil</h3>
      </div>
      <div class="card-body">
        <div v-if="profileSuccess" class="alert alert-success d-flex align-items-center mb-6">
          <KTIcon icon-name="shield-tick" icon-class="fs-2 text-success me-3" />
          Profil berhasil diperbarui!
        </div>
        <div v-if="profileError" class="alert alert-danger mb-6">{{ profileError }}</div>

        <div class="row g-6">
          <div class="col-md-6">
            <label class="form-label required">Nama Lengkap</label>
            <input v-model="profileForm.name" type="text" class="form-control form-control-solid" placeholder="Nama lengkap" />
          </div>
          <div class="col-md-6">
            <label class="form-label required">Email</label>
            <input v-model="profileForm.email" type="email" class="form-control form-control-solid" placeholder="Email" />
          </div>
          <div class="col-md-6">
            <label class="form-label">No. Telepon</label>
            <input v-model="profileForm.phone" type="text" class="form-control form-control-solid" placeholder="No. Telepon" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Jabatan</label>
            <input v-model="profileForm.job_title" type="text" class="form-control form-control-solid" placeholder="Jabatan" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Perusahaan</label>
            <input v-model="profileForm.company" type="text" class="form-control form-control-solid" placeholder="Perusahaan" />
          </div>
          <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea v-model="profileForm.bio" class="form-control form-control-solid" rows="3" placeholder="Tulis bio singkat..."></textarea>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-3">
        <button class="btn btn-light" @click="resetProfileForm">Reset</button>
        <button class="btn btn-primary" @click="submitProfile" :disabled="profileLoading">
          <span v-if="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
          Simpan Perubahan
        </button>
      </div>
    </div>

    <!-- Tab: Ubah Password -->
    <div v-if="tab === 'password'" class="card">
      <div class="card-header">
        <h3 class="card-title">Ubah Password</h3>
      </div>
      <div class="card-body">
        <div v-if="passwordSuccess" class="alert alert-success d-flex align-items-center mb-6">
          <KTIcon icon-name="shield-tick" icon-class="fs-2 text-success me-3" />
          Password berhasil diubah!
        </div>
        <div v-if="passwordError" class="alert alert-danger mb-6">{{ passwordError }}</div>

        <div class="row g-6 mw-500px">
          <div class="col-12">
            <label class="form-label required">Password Saat Ini</label>
            <input v-model="passwordForm.current_password" type="password" class="form-control form-control-solid" placeholder="••••••••" />
          </div>
          <div class="col-12">
            <label class="form-label required">Password Baru</label>
            <input v-model="passwordForm.password" type="password" class="form-control form-control-solid" placeholder="••••••••" />
          </div>
          <div class="col-12">
            <label class="form-label required">Konfirmasi Password Baru</label>
            <input v-model="passwordForm.password_confirmation" type="password" class="form-control form-control-solid" placeholder="••••••••" />
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-3">
        <button class="btn btn-light" @click="resetPasswordForm">Reset</button>
        <button class="btn btn-primary" @click="submitPassword" :disabled="passwordLoading">
          <span v-if="passwordLoading" class="spinner-border spinner-border-sm me-2"></span>
          Ubah Password
        </button>
      </div>
    </div>

  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import ApiService from "@/core/services/ApiService";

export default defineComponent({
  name: "admin-profile",
  setup() {
    const authStore = useAuthStore();
    const tab = ref("profile");

    // Avatar
    const avatarPreview = computed(() => {
      if (!authStore.user?.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    const onAvatarChange = async (e: Event) => {
      const file = (e.target as HTMLInputElement).files?.[0];
      if (!file) return;
      try {
        await authStore.uploadAvatar(file);
      } catch {}
    };

    // Profile Form
    const profileForm = reactive({
      name: "",
      email: "",
      phone: "",
      job_title: "",
      company: "",
      bio: "",
    });

    const profileLoading = ref(false);
    const profileSuccess = ref(false);
    const profileError = ref("");

    const resetProfileForm = () => {
      profileForm.name = authStore.user.name ?? "";
      profileForm.email = authStore.user.email ?? "";
      profileForm.phone = authStore.user.phone ?? "";
      profileForm.job_title = authStore.user.job_title ?? "";
      profileForm.company = authStore.user.company ?? "";
      profileForm.bio = authStore.user.bio ?? "";
      profileSuccess.value = false;
      profileError.value = "";
    };

    const submitProfile = async () => {
      profileLoading.value = true;
      profileSuccess.value = false;
      profileError.value = "";
      try {
        await authStore.updateProfile({ ...profileForm });
        profileSuccess.value = true;
      } catch (e: any) {
        profileError.value = e?.response?.data?.message ?? "Gagal menyimpan profil.";
      } finally {
        profileLoading.value = false;
      }
    };

    // Password Form
    const passwordForm = reactive({
      current_password: "",
      password: "",
      password_confirmation: "",
    });

    const passwordLoading = ref(false);
    const passwordSuccess = ref(false);
    const passwordError = ref("");

    const resetPasswordForm = () => {
      passwordForm.current_password = "";
      passwordForm.password = "";
      passwordForm.password_confirmation = "";
      passwordSuccess.value = false;
      passwordError.value = "";
    };

    const submitPassword = async () => {
      if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordError.value = "Konfirmasi password tidak cocok.";
        return;
      }
      passwordLoading.value = true;
      passwordSuccess.value = false;
      passwordError.value = "";
      try {
        await authStore.changePassword({ ...passwordForm });
        passwordSuccess.value = true;
        resetPasswordForm();
      } catch (e: any) {
        passwordError.value = e?.response?.data?.message ?? "Gagal mengubah password.";
      } finally {
        passwordLoading.value = false;
      }
    };

    onMounted(() => {
      resetProfileForm();
    });

    return {
      authStore,
      tab,
      avatarPreview,
      onAvatarChange,
      profileForm,
      profileLoading,
      profileSuccess,
      profileError,
      resetProfileForm,
      submitProfile,
      passwordForm,
      passwordLoading,
      passwordSuccess,
      passwordError,
      resetPasswordForm,
      submitPassword,
    };
  },
});
</script>