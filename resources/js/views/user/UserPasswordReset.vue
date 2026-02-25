<template>
  <div class="w-lg-460px px-10 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="fw-bolder mb-2 text-gray-900" style="font-size: 1.6rem; letter-spacing: -0.4px;">Lupa Password?</h1>
      <div class="text-muted fw-semibold fs-6">
        Masukkan email kamu untuk mereset password.
      </div>
    </div>

    <!-- Form -->
    <VForm
      class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework"
      @submit="onSubmitForgotPassword"
      id="kt_user_password_reset_form"
      :validation-schema="forgotPassword"
    >
      <!-- Email -->
      <div class="fv-row mb-7">
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
          <div class="fv-help-block text-danger fs-8 fw-semibold">
            <ErrorMessage name="email" />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="d-flex gap-3">
        <button
          type="submit"
          ref="submitButton"
          class="btn fw-bold fs-6 flex-grow-1"
          style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; padding: 14px; box-shadow: 0 4px 16px rgba(59,130,246,0.3);"
        >
          <span class="indicator-label">Kirim Reset Password</span>
          <span class="indicator-progress">
            Mohon tunggu...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
          </span>
        </button>

        <router-link
          to="/user/sign-in"
          class="btn fw-bold fs-7"
          style="border: 1.5px solid #e2e8f0; background: #fff; color: #374151; border-radius: 10px; padding: 14px 20px; white-space: nowrap;"
        >
          Batal
        </router-link>
      </div>

      <!-- Back to login -->
      <div class="text-center mt-6">
        <span class="text-muted fw-semibold fs-7">Ingat password kamu? </span>
        <router-link to="/user/sign-in" class="fw-bold fs-7" style="color: #3b82f6;">
          Masuk di sini
        </router-link>
      </div>
    </VForm>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore } from "@/stores/auth";
import * as Yup from "yup";
import Swal from "sweetalert2/dist/sweetalert2.js";

export default defineComponent({
  name: "user-password-reset",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const store = useAuthStore();
    const submitButton = ref<HTMLButtonElement | null>(null);

    const forgotPassword = Yup.object().shape({
      email: Yup.string().email().required().label("Email"),
    });

    const onSubmitForgotPassword = async (values: any) => {
      submitButton.value!.disabled = true;
      submitButton.value?.setAttribute("data-kt-indicator", "on");

      await store.forgotPassword(values);
      const error = Object.values(store.errors);

      if (error.length === 0) {
        Swal.fire({
          title: "Email Terkirim!",
          text: "Link reset password telah dikirim ke email kamu. Cek inbox atau folder spam.",
          icon: "success",
          buttonsStyling: false,
          confirmButtonText: "Ok!",
          heightAuto: false,
          customClass: { confirmButton: "btn fw-semibold btn-light-primary" },
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

    return { onSubmitForgotPassword, forgotPassword, submitButton };
  },
});
</script>

<style scoped>
.form-control:focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
}
</style>