<template>
  <div class="w-lg-460px px-10 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="fw-bolder mb-2 text-gray-900" style="font-size: 1.6rem; letter-spacing: -0.4px;">Masuk</h1>
      <div class="text-muted fw-semibold fs-6">
        Belum punya akun?
        <router-link to="/user/sign-up" class="fw-bold ms-1" style="color: #3b82f6;">Daftar di sini</router-link>
      </div>
    </div>

    <!-- Form -->
    <VForm
      class="form w-100"
      id="kt_user_signin_form"
      @submit="onSubmitLogin"
      :validation-schema="loginSchema"
    >
      <!-- Nomor HP -->
      <div class="fv-row mb-5">
        <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Nomor HP</label>
        <div class="position-relative">
          <!-- Prefix +62 -->
          <div class="position-absolute top-50 translate-middle-y ms-3 d-flex align-items-center gap-2" style="z-index:2; pointer-events:none;">
            <span class="fw-bold fs-7" style="color:#374151;">🇮🇩 +62</span>
            <div style="width:1px; height:18px; background:#e2e8f0;"></div>
          </div>
          <Field
            tabindex="1"
            class="form-control form-control-lg fs-7"
            type="tel"
            name="phone"
            placeholder="8xxxxxxxxxx"
            autocomplete="off"
            inputmode="numeric"
            style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151; padding-left: 90px;"
          />
        </div>
        <div class="fv-plugins-message-container mt-1">
          <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="phone" /></div>
        </div>
      </div>

      <!-- Password -->
      <div class="fv-row mb-7">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-0">Password</label>
          <router-link to="/user/password-reset" class="fw-bold fs-7" style="color: #3b82f6;">
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

      <!-- Submit -->
      <button
        tabindex="3"
        type="submit"
        ref="submitButton"
        class="btn w-100 fw-bold fs-6"
        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; padding: 14px; box-shadow: 0 4px 16px rgba(59,130,246,0.3);"
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
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import Swal from "sweetalert2/dist/sweetalert2.js";
import * as Yup from "yup";

export default defineComponent({
  name: "user-sign-in",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const store = useAuthStore();
    const router = useRouter();
    const submitButton = ref<HTMLButtonElement | null>(null);
    const showPassword = ref(false);

    const loginSchema = Yup.object().shape({
      phone: Yup.string()
        .required("Nomor HP wajib diisi")
        .matches(/^8[0-9]{8,12}$/, "Masukkan nomor tanpa 0 di depan, contoh: 81234567890")
        .label("Nomor HP"),
      password: Yup.string().min(4).required().label("Password"),
    });

    const onSubmitLogin = async (values: any) => {
      store.logout();

      if (submitButton.value) {
        submitButton.value.disabled = true;
        submitButton.value.setAttribute("data-kt-indicator", "on");
      }

      // Kirim dengan prefix +62
      const phone = "+62" + values.phone;
      await store.login({ phone, password: values.password });

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
          router.push({ name: "user-dashboard" });
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