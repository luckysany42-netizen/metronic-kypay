<template>
  <div class="w-lg-460px px-10 py-12">

    <!-- Loading State -->
    <div v-if="isValidating" class="text-center py-10">
      <div class="spinner-border text-primary mb-4" style="width:2.5rem; height:2.5rem;"></div>
      <p class="text-muted fw-semibold fs-7">Memvalidasi link reset password...</p>
    </div>

    <!-- Invalid Token State -->
    <div v-else-if="isInvalidToken" class="text-center py-6">
      <div class="mb-6" style="font-size:3rem;">⚠️</div>
      <h1 class="fw-bolder mb-3 text-gray-900" style="font-size:1.5rem;">Link Tidak Valid</h1>
      <p class="text-muted fw-semibold fs-6 mb-8">
        Link reset password tidak valid atau sudah kadaluarsa.<br/>
        Silakan minta link baru.
      </p>
      <router-link
        to="/password-reset"
        class="btn fw-bold fs-6 w-100"
        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color:#fff; border:none; border-radius:10px; padding:14px;"
      >
        Minta Link Baru
      </router-link>
    </div>

    <!-- Success State -->
    <div v-else-if="isSuccess" class="text-center py-6">
      <div class="mb-6" style="font-size:3rem;">✅</div>
      <h1 class="fw-bolder mb-3 text-gray-900" style="font-size:1.5rem;">Password Berhasil Direset!</h1>
      <p class="text-muted fw-semibold fs-6 mb-8">
        Password kamu berhasil diubah.<br/>
        Silakan masuk dengan password baru.
      </p>
      <button
        @click="goToLogin"
        class="btn fw-bold fs-6 w-100"
        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color:#fff; border:none; border-radius:10px; padding:14px;"
      >
        Masuk Sekarang
      </button>
    </div>

    <!-- Form State -->
    <div v-else>
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="fw-bolder mb-2 text-gray-900" style="font-size: 1.6rem; letter-spacing: -0.4px;">
          Buat Password Baru
        </h1>
        <div class="text-muted fw-semibold fs-6">
          Password baru kamu harus berbeda dari yang sebelumnya.
        </div>
      </div>

      <!-- Form -->
      <VForm
        class="form w-100"
        @submit="onSubmitReset"
        :validation-schema="resetSchema"
      >
        <!-- Password Baru -->
        <div class="fv-row mb-5">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Password Baru</label>
          <div class="position-relative">
            <Field
              class="form-control form-control-lg fs-7"
              :type="showPassword ? 'text' : 'password'"
              name="password"
              placeholder="Minimal 8 karakter"
              autocomplete="new-password"
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
            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
          </div>
          <div class="text-muted fw-semibold fs-8">Gunakan 8+ karakter dengan kombinasi huruf, angka & simbol.</div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="fv-row mb-7">
          <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Konfirmasi Password Baru</label>
          <div class="position-relative">
            <Field
              class="form-control form-control-lg fs-7"
              :type="showPasswordConfirm ? 'text' : 'password'"
              name="password_confirmation"
              placeholder="Ulangi password baru kamu"
              autocomplete="new-password"
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

        <!-- Submit -->
        <button
          type="submit"
          ref="submitButton"
          class="btn w-100 fw-bold fs-6"
          style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; padding: 14px; box-shadow: 0 4px 16px rgba(59,130,246,0.3);"
        >
          <span class="indicator-label">Simpan Password Baru</span>
          <span class="indicator-progress">
            Mohon tunggu...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
          </span>
        </button>

        <!-- Back to login -->
        <div class="text-center mt-6">
          <router-link :to="loginRoute" class="fw-bold fs-7" style="color: #3b82f6;">
            ← Kembali ke halaman masuk
          </router-link>
        </div>
      </VForm>
    </div>

  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, nextTick } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore } from "@/stores/auth";
import { useRouter, useRoute } from "vue-router";
import { PasswordMeterComponent } from "@/assets/ts/components";
import Swal from "sweetalert2/dist/sweetalert2.js";
import * as Yup from "yup";

export default defineComponent({
  name: "new-password",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const store = useAuthStore();
    const router = useRouter();
    const route = useRoute();

    const submitButton = ref<HTMLButtonElement | null>(null);
    const showPassword = ref(false);
    const showPasswordConfirm = ref(false);
    const isValidating = ref(true);
    const isInvalidToken = ref(false);
    const isSuccess = ref(false);

    // Ambil token & email dari URL query: /reset-password?token=xxx&email=yyy
    const token = computed(() => route.query.token as string || "");
    const email = computed(() => route.query.email as string || "");

    // Tentukan route login berdasarkan context (bisa dikembangkan)
    const loginRoute = computed(() => "/sign-in");

    const resetSchema = Yup.object().shape({
      password: Yup.string()
        .min(8, "Password minimal 8 karakter")
        .required("Password wajib diisi")
        .label("Password"),
      password_confirmation: Yup.string()
        .required("Konfirmasi password wajib diisi")
        .oneOf([Yup.ref("password")], "Konfirmasi password tidak cocok")
        .label("Konfirmasi Password"),
    });

    onMounted(() => {
      // Validasi token & email dari URL
      if (!token.value || !email.value) {
        isInvalidToken.value = true;
        isValidating.value = false;
        return;
      }

      // Token & email ada, tampilkan form
      isValidating.value = false;

      nextTick(() => {
        PasswordMeterComponent.bootstrap();
      });
    });

    const onSubmitReset = async (values: any) => {
      if (submitButton.value) {
        submitButton.value.disabled = true;
        submitButton.value.setAttribute("data-kt-indicator", "on");
      }

      await store.resetPassword({
        token: token.value,
        email: email.value,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });

      const error = Object.values(store.errors);

      if (error.length === 0) {
        isSuccess.value = true;
      } else {
        // Jika error token/expired
        const errMsg = error[0] as string;
        if (errMsg.includes("tidak valid") || errMsg.includes("kadaluarsa")) {
          isInvalidToken.value = true;
        } else {
          Swal.fire({
            text: errMsg,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Coba lagi!",
            heightAuto: false,
            customClass: { confirmButton: "btn fw-semibold btn-light-danger" },
          }).then(() => {
            store.errors = {};
          });
        }
      }

      if (submitButton.value) {
        submitButton.value.removeAttribute("data-kt-indicator");
        submitButton.value.disabled = false;
      }
    };

    const goToLogin = () => {
      router.push({ name: "sign-in" });
    };

    return {
      submitButton,
      showPassword,
      showPasswordConfirm,
      isValidating,
      isInvalidToken,
      isSuccess,
      loginRoute,
      resetSchema,
      onSubmitReset,
      goToLogin,
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