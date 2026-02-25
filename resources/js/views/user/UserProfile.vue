<template>
  <div class="row g-6 g-xl-9">

    <!--begin::Sidebar-->
    <div class="col-xl-4">

      <!--begin::Profile Card-->
      <div class="card profile-hero-card mb-6" style="background: linear-gradient(145deg, #0f1b35 0%, #1a2f5a 50%, #0d2244 100%); border: none; overflow: hidden; position: relative;">
        <!-- Decorative elements -->
        <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,0.03);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-60px;left:-30px;width:220px;height:220px;border-radius:50%;background:rgba(99,179,237,0.05);pointer-events:none;"></div>
        <div style="position:absolute;top:50%;right:20px;width:1px;height:120px;background:linear-gradient(to bottom, transparent, rgba(255,255,255,0.08), transparent);transform:translateY(-50%);pointer-events:none;"></div>

        <div class="card-body px-8 py-10">

          <!--begin::Avatar Upload Section-->
          <div class="d-flex flex-center flex-column">

            <!--begin::Avatar-->
            <div class="position-relative mb-5">
              <div class="symbol symbol-110px symbol-circle" style="box-shadow: 0 0 0 4px rgba(255,255,255,0.1), 0 0 0 8px rgba(99,179,237,0.15);">
                <img
                  v-if="avatarPreview || authStore.user.avatar"
                  :src="avatarPreview || avatarUrl"
                  alt="avatar"
                  class="object-fit-cover"
                />
                <div v-else class="symbol-label fs-1 fw-bold" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; font-family: 'DM Sans', sans-serif;">
                  {{ authStore.user.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}
                </div>
              </div>
              <label
                for="avatar-input"
                class="btn btn-icon btn-circle w-20px h-20px cursor-pointer position-absolute bottom-0 end-0"
                style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: 3px solid #0f1b35; box-shadow: 0 4px 12px rgba(59,130,246,0.5);"
                title="Ganti foto profil"
              >
                <i class="ki-duotone ki-pencil fs-7" style="color:#fff;">
                  <span class="path1"></span><span class="path2"></span>
                </i>
              </label>
              <input id="avatar-input" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="d-none" @change="onAvatarChange" />
            </div>
            <!--end::Avatar-->

            <!--begin::Avatar actions-->
            <div v-if="avatarPreview" class="d-flex gap-2 mb-5">
              <button type="button" class="btn btn-sm fw-semibold px-5" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border:none;" :disabled="avatarUploading" @click="onAvatarUpload">
                <span v-if="!avatarUploading"><i class="ki-duotone ki-check fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>Simpan</span>
                <span v-else>Upload... <span class="spinner-border spinner-border-sm ms-2"></span></span>
              </button>
              <button type="button" class="btn btn-sm fw-semibold px-5" style="background:rgba(255,255,255,0.1); color:#fff; border: 1px solid rgba(255,255,255,0.15);" @click="onAvatarCancel">Batal</button>
            </div>
            <!--end::Avatar actions-->

            <div class="text-center mb-2">
              <span class="fw-bolder text-white mb-1 d-block" style="font-size:1.35rem; letter-spacing:-0.3px;">{{ authStore.user.name || '—' }}</span>
              <span class="d-block mb-4" style="color: rgba(255,255,255,0.55); font-size: 0.85rem; font-weight: 500;">{{ authStore.user.job_title || 'User' }}</span>
              <div class="d-flex gap-2 justify-content-center flex-wrap">
                <span class="badge fw-semibold text-capitalize" style="background:rgba(59,130,246,0.2); color:#93c5fd; border: 1px solid rgba(59,130,246,0.3); padding: 5px 12px; border-radius: 20px; font-size: 0.78rem;">
                  <i class="ki-duotone ki-shield-tick fs-8 me-1" style="color:#93c5fd;"><span class="path1"></span><span class="path2"></span></i>
                  {{ authStore.user.role || 'user' }}
                </span>
                <span class="badge fw-semibold" style="background:rgba(16,185,129,0.2); color:#6ee7b7; border: 1px solid rgba(16,185,129,0.3); padding: 5px 12px; border-radius: 20px; font-size: 0.78rem;">
                  <span class="bullet bullet-dot me-1 h-5px w-5px" style="background:#6ee7b7;"></span>Aktif
                </span>
              </div>
            </div>
          </div>
          <!--end::Avatar Upload Section-->

          <!-- Divider -->
          <div class="my-7" style="height:1px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);"></div>

          <!-- Contact Info -->
          <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:36px; height:36px; background:rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.2);">
                <i class="ki-duotone ki-sms fs-5" style="color:#93c5fd;"><span class="path1"></span><span class="path2"></span></i>
              </div>
              <div>
                <div style="color:rgba(255,255,255,0.4); font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Email</div>
                <div style="color:rgba(255,255,255,0.85); font-size:0.82rem; font-weight:600; word-break:break-all;">{{ authStore.user.email || '—' }}</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:36px; height:36px; background:rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.2);">
                <i class="ki-duotone ki-phone fs-5" style="color:#6ee7b7;"><span class="path1"></span><span class="path2"></span></i>
              </div>
              <div>
                <div style="color:rgba(255,255,255,0.4); font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Telepon</div>
                <div style="color:rgba(255,255,255,0.85); font-size:0.82rem; font-weight:600;">{{ authStore.user.phone || '—' }}</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:36px; height:36px; background:rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.2);">
                <i class="ki-duotone ki-building fs-5" style="color:#fcd34d;"><span class="path1"></span><span class="path2"></span></i>
              </div>
              <div>
                <div style="color:rgba(255,255,255,0.4); font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Perusahaan</div>
                <div style="color:rgba(255,255,255,0.85); font-size:0.82rem; font-weight:600;">{{ authStore.user.company || '—' }}</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:36px; height:36px; background:rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.2);">
                <i class="ki-duotone ki-briefcase fs-5" style="color:#c4b5fd;"><span class="path1"></span><span class="path2"></span></i>
              </div>
              <div>
                <div style="color:rgba(255,255,255,0.4); font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Jabatan</div>
                <div style="color:rgba(255,255,255,0.85); font-size:0.82rem; font-weight:600;">{{ authStore.user.job_title || '—' }}</div>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="my-7" style="height:1px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);"></div>

          <!-- Bio -->
          <div v-if="authStore.user.bio">
            <div style="color:rgba(255,255,255,0.4); font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;" class="mb-3">Bio</div>
            <p style="color:rgba(255,255,255,0.7); font-size:0.84rem; line-height:1.7; margin:0;">{{ authStore.user.bio }}</p>
          </div>
          <div v-else class="rounded-2 px-4 py-4" style="background:rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.25);">
            <div class="d-flex align-items-center gap-3">
              <i class="ki-duotone ki-information-5 fs-3" style="color:#fcd34d; flex-shrink:0;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
              <span style="color:rgba(255,255,255,0.55); font-size:0.8rem;">Bio belum diisi. Tambahkan untuk melengkapi profil.</span>
            </div>
          </div>
        </div>
      </div>
      <!--end::Profile Card-->

      <!--begin::Profile Completion-->
      <div class="card" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-body p-7">
          <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
              <div class="fw-bolder text-gray-800 fs-6 mb-1">Kelengkapan Profil</div>
              <div class="text-muted fw-semibold fs-8">{{ filledCount }} dari {{ totalFields }} field terisi</div>
            </div>
            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:52px;height:52px;background: conic-gradient(#3b82f6 0% calc(var(--pct) * 1%), #eef0f5 0%);" :style="{'--pct': profileCompletion}">
              <div class="d-flex align-items-center justify-content-center rounded-circle bg-white" style="width:38px;height:38px;">
                <span class="fw-bolder" style="font-size:0.72rem; color:#1e40af;">{{ profileCompletion }}%</span>
              </div>
            </div>
          </div>
          <div class="progress h-6px mb-6 rounded-pill" style="background:#eef0f5;">
            <div class="progress-bar rounded-pill" :style="{ width: profileCompletion + '%', background: 'linear-gradient(90deg, #3b82f6, #1d4ed8)' }"></div>
          </div>
          <div class="d-flex flex-column gap-3">
            <div v-for="field in fieldStatus" :key="field.key" class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" :style="field.filled ? 'background:#f0fdf4;' : 'background:#fff5f5;'">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle" :style="field.filled ? 'width:8px;height:8px;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.15);' : 'width:8px;height:8px;background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,0.15);'"></div>
                <span class="fw-semibold fs-8" style="color:#374151;">{{ field.label }}</span>
              </div>
              <span v-if="field.filled" style="color:#16a34a; font-size:0.72rem; font-weight:700; letter-spacing:0.03em;">✓ Terisi</span>
              <span v-else style="color:#dc2626; font-size:0.72rem; font-weight:700; letter-spacing:0.03em;">Kosong</span>
            </div>
          </div>
        </div>
      </div>
      <!--end::Profile Completion-->

    </div>
    <!--end::Sidebar-->

    <!--begin::Main Form-->
    <div class="col-xl-8">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">

        <!-- Card Header -->
        <div class="card-header border-0 pt-8 pb-4 px-8">
          <div>
            <h3 class="fw-bolder text-gray-800 mb-1" style="font-size:1.25rem; letter-spacing:-0.3px;">Edit Profil</h3>
            <div class="text-muted fw-semibold fs-7">Perbarui informasi akun kamu</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge badge-light-success fw-semibold fs-8 py-2 px-3">
              <span class="bullet bullet-dot bg-success me-1 h-5px w-5px"></span>Akun Aktif
            </span>
          </div>
        </div>

        <div class="card-body pt-2 px-8 pb-10">
          <VForm @submit="onSubmitProfile" :validation-schema="profileSchema" :initial-values="initialValues">

            <!-- Section: Data Pribadi -->
            <div class="d-flex align-items-center gap-3 mb-6">
              <div class="h-35px d-flex align-items-center px-4 rounded-2 fw-bold fs-8 text-uppercase" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color:#1d4ed8; letter-spacing:0.06em;">
                <i class="ki-duotone ki-profile-circle fs-6 me-2" style="color:#3b82f6;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                Data Pribadi
              </div>
              <div class="flex-grow-1" style="height:1px; background: linear-gradient(to right, #dbeafe, transparent);"></div>
            </div>

            <div class="row g-6 mb-4">
              <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 fs-7 mb-2 required">Nama Lengkap</label>
                <div class="position-relative">
                  <span class="position-absolute top-50 translate-middle-y ms-4 d-flex">
                    <i class="ki-duotone ki-profile-circle fs-4 text-muted"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                  </span>
                  <Field type="text" name="name" class="form-control form-control-solid ps-12 fs-7" placeholder="Masukkan nama lengkap" style="border-radius: 10px; border: 1.5px solid #eef0f5; transition: border-color 0.2s;" />
                </div>
                <div class="fv-plugins-message-container invalid-feedback mt-1"><ErrorMessage name="name" /></div>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 fs-7 mb-2">No. Telepon</label>
                <div class="position-relative">
                  <span class="position-absolute top-50 translate-middle-y ms-4 d-flex">
                    <i class="ki-duotone ki-phone fs-4 text-muted"><span class="path1"></span><span class="path2"></span></i>
                  </span>
                  <Field type="text" name="phone" class="form-control form-control-solid ps-12 fs-7" placeholder="Contoh: +62812xxxxxxxx" style="border-radius: 10px; border: 1.5px solid #eef0f5;" />
                </div>
              </div>
            </div>

            <!-- Section: Pekerjaan -->
            <div class="d-flex align-items-center gap-3 mb-6 mt-4">
              <div class="h-35px d-flex align-items-center px-4 rounded-2 fw-bold fs-8 text-uppercase" style="background: linear-gradient(135deg, #fefce8, #fef08a); color:#854d0e; letter-spacing:0.06em;">
                <i class="ki-duotone ki-briefcase fs-6 me-2" style="color:#ca8a04;"><span class="path1"></span><span class="path2"></span></i>
                Informasi Pekerjaan
              </div>
              <div class="flex-grow-1" style="height:1px; background: linear-gradient(to right, #fef08a, transparent);"></div>
            </div>

            <div class="row g-6 mb-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold text-gray-700 fs-7 mb-2">Jabatan</label>
                <div class="position-relative">
                  <span class="position-absolute top-50 translate-middle-y ms-4 d-flex">
                    <i class="ki-duotone ki-briefcase fs-4 text-muted"><span class="path1"></span><span class="path2"></span></i>
                  </span>
                  <Field type="text" name="job_title" class="form-control form-control-solid ps-12 fs-7" placeholder="Jabatan kamu" style="border-radius: 10px; border: 1.5px solid #eef0f5;" />
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold text-gray-700 fs-7 mb-2">Perusahaan</label>
                <div class="position-relative">
                  <span class="position-absolute top-50 translate-middle-y ms-4 d-flex">
                    <i class="ki-duotone ki-building fs-4 text-muted"><span class="path1"></span><span class="path2"></span></i>
                  </span>
                  <Field type="text" name="company" class="form-control form-control-solid ps-12 fs-7" placeholder="Nama perusahaan" style="border-radius: 10px; border: 1.5px solid #eef0f5;" />
                </div>
              </div>
            </div>

            <!-- Section: Bio -->
            <div class="d-flex align-items-center gap-3 mb-6 mt-4">
              <div class="h-35px d-flex align-items-center px-4 rounded-2 fw-bold fs-8 text-uppercase" style="background: linear-gradient(135deg, #fdf4ff, #f3e8ff); color:#6b21a8; letter-spacing:0.06em;">
                <i class="ki-duotone ki-notepad fs-6 me-2" style="color:#9333ea;"><span class="path1"></span><span class="path2"></span></i>
                Tentang Kamu
              </div>
              <div class="flex-grow-1" style="height:1px; background: linear-gradient(to right, #f3e8ff, transparent);"></div>
            </div>

            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold text-gray-700 fs-7 mb-0">Bio</label>
                <span class="text-muted fs-8 fw-semibold">Opsional • maks. 300 karakter</span>
              </div>
              <Field as="textarea" name="bio" class="form-control form-control-solid fs-7" rows="5" placeholder="Ceritakan sedikit tentang kamu..." style="border-radius: 10px; border: 1.5px solid #eef0f5; resize: vertical; line-height: 1.7;" />
            </div>

            <!-- Footer -->
            <div class="d-flex justify-content-between align-items-center pt-6 mt-4" style="border-top: 1.5px dashed #eef0f5;">
              <div class="d-flex align-items-center gap-2 text-muted fw-semibold fs-8">
                <i class="ki-duotone ki-information-5 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                Perubahan diterapkan setelah disimpan.
              </div>
              <div class="d-flex gap-3">
                <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold fs-7 px-7" style="border-radius: 10px;">
                  Reset
                </button>
                <button type="submit" ref="submitButton" class="btn fw-semibold fs-7 px-8" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; border-radius: 10px; box-shadow: 0 4px 16px rgba(59,130,246,0.35);">
                  <span class="indicator-label">
                    <i class="ki-duotone ki-check fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Simpan Perubahan
                  </span>
                  <span class="indicator-progress">
                    Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                  </span>
                </button>
              </div>
            </div>

          </VForm>
        </div>
      </div>
    </div>
    <!--end::Main Form-->

  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2/dist/sweetalert2.js";
import * as Yup from "yup";

export default defineComponent({
  name: "user-profile",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const authStore = useAuthStore();
    const submitButton = ref<HTMLButtonElement | null>(null);

    // ===== AVATAR =====
    const avatarPreview = ref<string | null>(null);
    const avatarFile = ref<File | null>(null);
    const avatarUploading = ref(false);

    const avatarUrl = computed(() => {
      if (!authStore.user.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    const onAvatarChange = (event: Event) => {
      const input = event.target as HTMLInputElement;
      if (!input.files || !input.files[0]) return;
      const file = input.files[0];
      if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
          text: "Ukuran foto maksimal 2MB!",
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "Ok",
          heightAuto: false,
          customClass: { confirmButton: "btn btn-light-danger" },
        });
        return;
      }
      avatarFile.value = file;
      avatarPreview.value = URL.createObjectURL(file);
    };

    const onAvatarUpload = async () => {
      if (!avatarFile.value) return;
      avatarUploading.value = true;
      await authStore.uploadAvatar(avatarFile.value);
      const error = Object.values(authStore.errors);
      if (error.length === 0) {
        Swal.fire({
          text: "Foto profil berhasil diperbarui!",
          icon: "success",
          buttonsStyling: false,
          confirmButtonText: "Ok!",
          heightAuto: false,
          customClass: { confirmButton: "btn btn-light-primary" },
        });
        avatarPreview.value = null;
        avatarFile.value = null;
      } else {
        Swal.fire({
          text: error[0] as string,
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "Coba lagi!",
          heightAuto: false,
          customClass: { confirmButton: "btn btn-light-danger" },
        });
      }
      avatarUploading.value = false;
    };

    const onAvatarCancel = () => {
      avatarPreview.value = null;
      avatarFile.value = null;
      const input = document.getElementById("avatar-input") as HTMLInputElement;
      if (input) input.value = "";
    };

    // ===== PROFILE FORM =====
    const fieldConfig = [
      { key: "name",      label: "Nama Lengkap" },
      { key: "email",     label: "Email" },
      { key: "phone",     label: "No. Telepon" },
      { key: "job_title", label: "Jabatan" },
      { key: "company",   label: "Perusahaan" },
      { key: "bio",       label: "Bio" },
    ];

    const fieldStatus = computed(() =>
      fieldConfig.map((f) => ({
        key: f.key,
        label: f.label,
        filled: !!authStore.user[f.key as keyof typeof authStore.user],
      }))
    );
    const filledCount = computed(() => fieldStatus.value.filter((f) => f.filled).length);
    const totalFields = fieldConfig.length;
    const profileCompletion = computed(() => Math.round((filledCount.value / totalFields) * 100));

    const profileSchema = Yup.object().shape({
      name: Yup.string().required("Nama wajib diisi").label("Nama"),
      phone: Yup.string().nullable().label("Telepon"),
      job_title: Yup.string().nullable().label("Jabatan"),
      company: Yup.string().nullable().label("Perusahaan"),
      bio: Yup.string().max(300, "Bio maksimal 300 karakter").nullable().label("Bio"),
    });

    const initialValues = computed(() => ({
      name: authStore.user.name || "",
      phone: authStore.user.phone || "",
      job_title: authStore.user.job_title || "",
      company: authStore.user.company || "",
      bio: authStore.user.bio || "",
    }));

    const onSubmitProfile = async (values: any) => {
      if (submitButton.value) {
        submitButton.value.disabled = true;
        submitButton.value.setAttribute("data-kt-indicator", "on");
      }
      await authStore.updateProfile(values);
      const error = Object.values(authStore.errors);
      if (error.length === 0) {
        Swal.fire({
          text: "Profil berhasil diperbarui!",
          icon: "success",
          buttonsStyling: false,
          confirmButtonText: "Ok, Lanjutkan!",
          heightAuto: false,
          customClass: { confirmButton: "btn fw-semibold btn-light-primary" },
        });
      } else {
        Swal.fire({
          text: error[0] as string,
          icon: "error",
          buttonsStyling: false,
          confirmButtonText: "Coba Lagi",
          heightAuto: false,
          customClass: { confirmButton: "btn fw-semibold btn-light-danger" },
        });
      }
      submitButton.value?.removeAttribute("data-kt-indicator");
      submitButton.value!.disabled = false;
    };

    return {
      authStore,
      submitButton,
      profileSchema,
      initialValues,
      onSubmitProfile,
      fieldStatus,
      filledCount,
      totalFields,
      profileCompletion,
      avatarPreview,
      avatarUploading,
      avatarUrl,
      onAvatarChange,
      onAvatarUpload,
      onAvatarCancel,
    };
  },
});
</script>

<style scoped>
.form-control:focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
}

.profile-hero-card {
  transition: box-shadow 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  transition: transform 0.15s ease;
}
</style>