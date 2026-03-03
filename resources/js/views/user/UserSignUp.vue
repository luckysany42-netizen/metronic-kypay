<template>
  <div class="w-lg-460px px-10 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="fw-bolder mb-2 text-gray-900" style="font-size: 1.6rem; letter-spacing: -0.4px;">Buat Akun</h1>
      <div class="text-muted fw-semibold fs-6">
        Sudah punya akun?
        <router-link to="/user/sign-in" class="fw-bold ms-1" style="color: #3b82f6;">Masuk di sini</router-link>
      </div>
    </div>

    <!-- Social Login (TOP) -->
    <div class="d-flex gap-3 mb-7">
      <a href="#" class="d-flex align-items-center justify-content-center gap-2 fw-semibold fs-7 text-gray-700 text-decoration-none flex-grow-1"
        style="border: 1.5px solid #e2e8f0; padding: 11px 16px; border-radius: 10px; background: #fff; transition: all 0.18s;"
        @mouseenter="e => { e.currentTarget.style.background='#f8fafc'; e.currentTarget.style.borderColor='#cbd5e1'; }"
        @mouseleave="e => { e.currentTarget.style.background='#fff'; e.currentTarget.style.borderColor='#e2e8f0'; }">
        <img alt="Google" :src="getAssetPath('media/svg/brand-logos/google-icon.svg')" class="h-15px" />
        Gunakan Google
      </a>
      <a href="#" class="d-flex align-items-center justify-content-center gap-2 fw-semibold fs-7 text-gray-700 text-decoration-none flex-grow-1"
        style="border: 1.5px solid #e2e8f0; padding: 11px 16px; border-radius: 10px; background: #fff; transition: all 0.18s;"
        @mouseenter="e => { e.currentTarget.style.background='#f8fafc'; e.currentTarget.style.borderColor='#cbd5e1'; }"
        @mouseleave="e => { e.currentTarget.style.background='#fff'; e.currentTarget.style.borderColor='#e2e8f0'; }">
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
      class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework"
      novalidate
      id="kt_user_signup_form"
      @submit="onSubmitRegister"
      :validation-schema="registration"
    >
      <!-- Nama Depan & Belakang -->
      <div class="row fv-row mb-5">
        <div class="col-6">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Nama Depan</label>
          <Field
            class="form-control form-control-lg fs-7"
            type="text"
            name="first_name"
            placeholder="Nama depan"
            autocomplete="off"
            style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151;"
          />
          <div class="fv-plugins-message-container mt-1">
            <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="first_name" /></div>
          </div>
        </div>
        <div class="col-6">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Nama Belakang</label>
          <Field
            class="form-control form-control-lg fs-7"
            type="text"
            name="last_name"
            placeholder="Nama belakang"
            autocomplete="off"
            style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151;"
          />
          <div class="fv-plugins-message-container mt-1">
            <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="last_name" /></div>
          </div>
        </div>
      </div>

      <!-- Email -->
      <div class="fv-row mb-5">
        <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Email</label>
        <Field
          class="form-control form-control-lg fs-7"
          type="email"
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
      <div class="fv-row mb-3">
        <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Password</label>
        <div class="position-relative">
          <Field
            class="form-control form-control-lg fs-7"
            :type="showPassword ? 'text' : 'password'"
            name="password"
            placeholder="Minimal 8 karakter"
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

      <!-- Password Strength Meter -->
      <div class="mb-5" data-kt-password-meter="true">
        <div class="d-flex align-items-center gap-2 mb-2" data-kt-password-meter-control="highlight">
          <div class="flex-grow-1 rounded h-5px" style="background:#e2e8f0;"></div>
          <div class="flex-grow-1 rounded h-5px" style="background:#e2e8f0;"></div>
          <div class="flex-grow-1 rounded h-5px" style="background:#e2e8f0;"></div>
          <div class="flex-grow-1 rounded h-5px" style="background:#e2e8f0;"></div>
        </div>
        <div class="text-muted fw-semibold fs-8">Gunakan 8+ karakter dengan kombinasi huruf, angka & simbol.</div>
      </div>

      <!-- Konfirmasi Password -->
      <div class="fv-row mb-5">
        <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Konfirmasi Password</label>
        <div class="position-relative">
          <Field
            class="form-control form-control-lg fs-7"
            :type="showPasswordConfirm ? 'text' : 'password'"
            name="password_confirmation"
            placeholder="Ulangi password kamu"
            autocomplete="off"
            style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #374151;"
          />
          <span
            class="position-absolute top-50 translate-middle-y me-4 end-0 cursor-pointer d-flex"
            @click="showPasswordConfirm = !showPasswordConfirm"
          >
            <i v-if="!showPasswordConfirm" class="ki-duotone ki-eye fs-4 text-muted"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <i v-else class="ki-duotone ki-eye-slash fs-4 text-muted"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
          </span>
        </div>
        <div class="fv-plugins-message-container mt-1">
          <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="password_confirmation" /></div>
        </div>
      </div>

      <!-- Terms & Conditions -->
      <div class="fv-row mb-2">
        <label class="form-check form-check-custom form-check-solid d-flex align-items-start gap-3">
          <Field
            class="form-check-input mt-1 flex-shrink-0"
            type="checkbox"
            name="toc"
            value="1"
            style="width:18px; height:18px; border: 1.5px solid #cbd5e1; border-radius:4px; cursor:pointer;"
          />
          <span class="fw-semibold fs-8 text-gray-600 cursor-pointer lh-base">
            Saya menyetujui
            <a href="#" style="color: #3b82f6;" class="fw-bold">Syarat & Ketentuan</a>
            serta
            <a href="#" style="color: #3b82f6;" class="fw-bold">Kebijakan Privasi</a>
            yang berlaku.
          </span>
        </label>
      </div>
      <div class="fv-plugins-message-container mb-6">
        <div class="fv-help-block text-danger fs-8 fw-semibold"><ErrorMessage name="toc" /></div>
      </div>

      <!-- Submit Button -->
      <button
        id="kt_sign_up_submit"
        ref="submitButton"
        type="submit"
        class="btn w-100 fw-bold fs-6"
        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; padding: 14px; box-shadow: 0 4px 16px rgba(59,130,246,0.3); letter-spacing: 0.01em;"
      >
        <span class="indicator-label">Buat Akun</span>
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
import { defineComponent, nextTick, onMounted, ref } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import * as Yup from "yup";
import { useAuthStore, type User } from "@/stores/auth";
import { useRouter } from "vue-router";
import { PasswordMeterComponent } from "@/assets/ts/components";
import Swal from "sweetalert2/dist/sweetalert2.js";

export default defineComponent({
  name: "user-sign-up",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const store = useAuthStore();
    const router = useRouter();
    const submitButton = ref<HTMLButtonElement | null>(null);
    const showPassword = ref(false);
    const showPasswordConfirm = ref(false);

    const registration = Yup.object().shape({
      first_name: Yup.string().required("Nama depan wajib diisi").label("Nama Depan"),
      last_name: Yup.string().required("Nama belakang wajib diisi").label("Nama Belakang"),
      email: Yup.string().min(4).required("Email wajib diisi").email("Format email tidak valid").label("Email"),
      password: Yup.string().min(8, "Password minimal 8 karakter").required("Password wajib diisi").label("Password"),
      password_confirmation: Yup.string()
        .required("Konfirmasi password wajib diisi")
        .oneOf([Yup.ref("password")], "Konfirmasi password tidak cocok")
        .label("Konfirmasi Password"),
      toc: Yup.string().required("Kamu harus menyetujui syarat & ketentuan").label("Syarat & Ketentuan"),
    });

    onMounted(() => {
      nextTick(() => {
        PasswordMeterComponent.bootstrap();
      });
    });

    const onSubmitRegister = async (values: any) => {
      values = values as User;

      store.logout();

      submitButton.value!.disabled = true;
      submitButton.value?.setAttribute("data-kt-indicator", "on");

      await store.register(values);
      const error = Object.values(store.errors);

      if (error.length === 0) {
        // ✅ Setelah register sukses, redirect ke halaman buat PIN
        // Kirim api_token sebagai query param agar halaman set-pin bisa panggil API
        const token = store.user?.api_token || "";
        router.push({ name: "user-set-pin", query: { token } });
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

    return {
      registration,
      onSubmitRegister,
      submitButton,
      showPassword,
      showPasswordConfirm,
      getAssetPath,
    };
  },
});
</script>

<style scoped>
.form-control:focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
}
</style>