<template>
  <div class="w-lg-460px px-10 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="fw-bolder mb-2 text-gray-900" style="font-size: 1.6rem; letter-spacing: -0.4px;">Masuk</h1>
      <div class="text-muted fw-semibold fs-6">
        Belum punya akun?
        <router-link to="/sign-up" class="fw-bold ms-1" style="color: #3b82f6;">Daftar di sini</router-link>
      </div>
    </div>

    <!-- Social Login -->
    <div class="d-flex gap-3 mb-7">
      <a href="#" class="d-flex align-items-center justify-content-center gap-2 fw-semibold fs-7 text-gray-700 text-decoration-none flex-grow-1"
        style="border: 1.5px solid #e2e8f0; padding: 11px 16px; border-radius: 10px; background: #fff; transition: all 0.18s;"
        @mouseenter="e => { (e.currentTarget as HTMLElement).style.background='#f8fafc'; (e.currentTarget as HTMLElement).style.borderColor='#cbd5e1'; }"
        @mouseleave="e => { (e.currentTarget as HTMLElement).style.background='#fff'; (e.currentTarget as HTMLElement).style.borderColor='#e2e8f0'; }">
        <img alt="Google" :src="getAssetPath('media/svg/brand-logos/google-icon.svg')" class="h-15px" />
        Gunakan Google
      </a>
      <a href="#" class="d-flex align-items-center justify-content-center gap-2 fw-semibold fs-7 text-gray-700 text-decoration-none flex-grow-1"
        style="border: 1.5px solid #e2e8f0; padding: 11px 16px; border-radius: 10px; background: #fff; transition: all 0.18s;"
        @mouseenter="e => { (e.currentTarget as HTMLElement).style.background='#f8fafc'; (e.currentTarget as HTMLElement).style.borderColor='#cbd5e1'; }"
        @mouseleave="e => { (e.currentTarget as HTMLElement).style.background='#fff'; (e.currentTarget as HTMLElement).style.borderColor='#e2e8f0'; }">
        <img alt="Apple" :src="getAssetPath('media/svg/brand-logos/apple-black.svg')" class="h-15px" />
        Gunakan Apple
      </a>
    </div>

    <!-- OR Divider -->
    <div class="d-flex align-items-center gap-3 mb-7">
      <div class="flex-grow-1" style="height:1px; background: #e2e8f0;"></div>
      <span class="text-muted fw-semibold fs-8 text-uppercase" style="letter-spacing:0.1em;">atau</span>
      <div class="flex-grow-1" style="height:1px; background: #e2e8f0;"></div>
    </div>

    <!-- Form -->
    <VForm
      class="form w-100"
      id="kt_login_signin_form"
      @submit="onSubmitLogin"
      :validation-schema="loginSchema"
    >
      <!-- Email -->
      <div class="fv-row mb-5">
        <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Email</label>
        <Field
          tabindex="1"
          class="form-control form-control-lg fs-7"
          type="text"
          name="email"
          placeholder="email@email.com"
          autocomplete="off"
          style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151;"
        />
        <div class="fv-plugins-message-container mt-1">
          <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="email" /></div>
        </div>
      </div>

      <!-- Password -->
      <div class="fv-row mb-5">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-0">Password</label>
          <router-link to="/password-reset" class="fw-bold fs-7" style="color: #3b82f6;">
            Lupa Password?
          </router-link>
        </div>
        <div class="position-relative">
          <Field
            tabindex="2"
            class="form-control form-control-lg fs-7"
            :type="showPassword ? 'text' : 'password'"
            name="password"
            placeholder="Masukkan Password"
            autocomplete="off"
            style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151;"
          />
          <span
            class="position-absolute top-50 translate-middle-y me-4 end-0 cursor-pointer d-flex"
            @click="showPassword = !showPassword"
          >
            <i v-if="!showPassword" class="ki-duotone ki-eye fs-4 text-muted"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <i v-else class="ki-duotone ki-eye-slash fs-4 text-muted"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
          </span>
        </div>
        <div class="fv-plugins-message-container mt-1">
          <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="password" /></div>
        </div>
      </div>

      <!-- Remember Me -->
      <div class="d-flex align-items-center mb-7">
        <input type="checkbox" id="remember_me" class="form-check-input me-3"
          style="width:18px; height:18px; border: 1.5px solid #cbd5e1; border-radius:4px; cursor:pointer;" />
        <label for="remember_me" class="fw-semibold fs-7 text-gray-700 cursor-pointer">Ingat saya</label>
      </div>

      <!-- Submit -->
      <button
        tabindex="3"
        type="submit"
        ref="submitButton"
        id="kt_sign_in_submit"
        class="btn w-100 fw-bold fs-6"
        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; padding: 14px; box-shadow: 0 4px 16px rgba(59,130,246,0.3); letter-spacing: 0.01em;"
      >
        <span class="indicator-label">Masuk</span>
        <span class="indicator-progress">
          Mohon tunggu...
          <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
        </span>
      </button>
    </VForm>
  </div>
</template>

<script lang="ts">
import { getAssetPath } from "@/core/helpers/assets";
import { defineComponent, ref } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore, type User } from "@/stores/auth";
import { useRouter } from "vue-router";
import Swal from "sweetalert2/dist/sweetalert2.js";
import * as Yup from "yup";

export default defineComponent({
  name: "sign-in",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const store = useAuthStore();
    const router = useRouter();
    const submitButton = ref<HTMLButtonElement | null>(null);
    const showPassword = ref(false);

    const loginSchema = Yup.object().shape({
      email: Yup.string().email("Format email tidak valid").required("Email wajib diisi").label("Email"),
      password: Yup.string().min(4).required("Password wajib diisi").label("Password"),
    });

    const onSubmitLogin = async (values: any) => {
      store.logout();

      if (submitButton.value) {
        submitButton.value.disabled = true;
        submitButton.value.setAttribute("data-kt-indicator", "on");
      }

      // ✅ Pakai adminLogin — kirim ke /api/admin/login (email based)
      await store.adminLogin(values);
      const error = Object.values(store.errors);

      if (error.length === 0) {
        Swal.fire({
          text: "Login berhasil!",
          icon: "success",
          buttonsStyling: false,
          confirmButtonText: "Ok!",
          heightAuto: false,
          customClass: { confirmButton: "btn fw-semibold btn-light-primary" },
        }).then(() => {
          router.push({ name: "dashboard" });
        });
      } else {
        Swal.fire({
          text: error[0] as string,
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "Coba lagi!",
          heightAuto: false,
          customClass: { confirmButton: "btn fw-semibold btn-light-danger" },
        }).then(() => {
          store.errors = {};
        });
      }

      submitButton.value?.removeAttribute("data-kt-indicator");
      submitButton.value!.disabled = false;
    };

    return { onSubmitLogin, loginSchema, submitButton, showPassword, getAssetPath };
  },
});
</script>

<style scoped>
.form-control:focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
}
</style>