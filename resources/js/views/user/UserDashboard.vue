<template>
  <div class="row g-6 g-xl-9 mb-6 mb-xl-9">

    <!--begin::Welcome Hero Card-->
    <div class="col-12">
      <div class="card overflow-hidden" style="background: linear-gradient(145deg, #0f1b35 0%, #1a2f5a 55%, #0d2244 100%); border: none; position: relative; min-height: 140px;">
        <div style="position:absolute;top:-60px;right:-60px;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,0.025);pointer-events:none;"></div>
        <div style="position:absolute;top:20px;right:200px;width:100px;height:100px;border-radius:50%;background:rgba(99,179,237,0.06);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-80px;left:-40px;width:260px;height:260px;border-radius:50%;background:rgba(59,130,246,0.07);pointer-events:none;"></div>
        <div style="position:absolute;top:0;right:340px;width:1px;height:100%;background:linear-gradient(to bottom, transparent, rgba(255,255,255,0.06), transparent);pointer-events:none;"></div>
        <div class="card-body py-9 px-10">
          <div class="d-flex align-items-center flex-wrap gap-5">
            <div class="symbol symbol-75px symbol-circle flex-shrink-0" style="box-shadow: 0 0 0 3px rgba(255,255,255,0.08), 0 0 0 7px rgba(59,130,246,0.18);">
              <img v-if="authStore.user.avatar" :src="avatarUrl" alt="avatar" class="object-fit-cover rounded-circle" />
              <div v-else class="symbol-label fs-1 fw-bolder" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff;">
                {{ authStore.user.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                <h2 class="text-white fw-bolder mb-0" style="font-size: 1.5rem; letter-spacing: -0.4px;">
                  Selamat Datang, {{ authStore.user.name || 'Pengguna' }}!
                </h2>
                <span style="background:rgba(34,197,94,0.18); color:#6ee7b7; border: 1px solid rgba(34,197,94,0.3); padding: 3px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing:0.04em;">
                  <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#6ee7b7; margin-right:5px; vertical-align:middle;"></span>AKTIF
                </span>
              </div>
              <p style="color: rgba(255,255,255,0.5); font-size: 0.88rem; margin-bottom: 14px;">{{ authStore.user.email }}</p>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span style="background:rgba(59,130,246,0.18); color:#93c5fd; border: 1px solid rgba(59,130,246,0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 600;" class="text-capitalize">
                  <i class="ki-duotone ki-shield-tick fs-8 me-1" style="color:#93c5fd;"><span class="path1"></span><span class="path2"></span></i>
                  {{ authStore.user.role || 'User' }}
                </span>
                <span style="color:rgba(255,255,255,0.35); font-size:0.78rem;">•</span>
                <span style="color:rgba(255,255,255,0.45); font-size:0.82rem; font-weight:500;">{{ authStore.user.job_title || 'Pengguna Sistem' }}</span>
              </div>
            </div>
            <div class="ms-auto d-none d-md-flex align-items-center gap-3">
              <router-link to="/user/profile" class="btn btn-sm fw-semibold px-6" style="background:rgba(255,255,255,0.1); color:#fff; border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; backdrop-filter: blur(4px);">
                <i class="ki-duotone ki-pencil fs-5 me-2" style="color:#93c5fd;"><span class="path1"></span><span class="path2"></span></i>
                Edit Profil
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Welcome Hero Card-->

    <!--begin::Profile Completion-->
    <div class="col-md-4">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-body p-8">
          <div class="d-flex align-items-center justify-content-between mb-7">
            <div class="d-flex align-items-center gap-4">
              <div class="d-flex align-items-center justify-content-center rounded-2" style="width:46px;height:46px; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                <i class="ki-duotone ki-profile-circle fs-2x" style="color:#3b82f6;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
              </div>
              <div>
                <div class="fw-bolder text-gray-800 fs-6">Profile Status</div>
                <div class="text-muted fw-semibold fs-8">Kelengkapan profil kamu</div>
              </div>
            </div>
            <div class="position-relative d-flex align-items-center justify-content-center" style="width:54px;height:54px;">
              <svg width="54" height="54" style="transform:rotate(-90deg);">
                <circle cx="27" cy="27" r="22" fill="none" stroke="#eef0f5" stroke-width="5"/>
                <circle cx="27" cy="27" r="22" fill="none" stroke="url(#blueGrad)" stroke-width="5"
                  :stroke-dasharray="`${profileCompletion * 1.382} 138.2`" stroke-linecap="round"/>
                <defs><linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient></defs>
              </svg>
              <span class="position-absolute fw-bolder" style="font-size:0.68rem; color:#1e40af;">{{ profileCompletion }}%</span>
            </div>
          </div>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted fw-semibold fs-8">Progress Kelengkapan</span>
              <span class="fw-bold fs-8" style="color:#3b82f6;">{{ filledCount }}/{{ totalFields }}</span>
            </div>
            <div class="progress h-8px rounded-pill" style="background:#eef0f5;">
              <div class="progress-bar rounded-pill" :style="{ width: profileCompletion + '%', background: 'linear-gradient(90deg, #3b82f6, #1d4ed8)' }" role="progressbar"></div>
            </div>
          </div>
          <div class="my-6" style="height:1px; background:#f1f5f9;"></div>
          <div class="row g-3">
            <div v-for="field in fieldStatus" :key="field.key" class="col-6">
              <div class="d-flex align-items-center gap-2 rounded-2 px-3 py-2" :style="field.filled ? 'background:#f0fdf4;' : 'background:#fff5f5;'">
                <div class="rounded-circle flex-shrink-0" :style="field.filled ? 'width:7px;height:7px;background:#22c55e;' : 'width:7px;height:7px;background:#ef4444;'"></div>
                <span class="fw-semibold" style="font-size:0.75rem; color:#374151;">{{ field.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Profile Completion-->

    <!--begin::Status Card-->
    <div class="col-md-4">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-body p-8">
          <div class="d-flex align-items-center gap-4 mb-7">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:46px;height:46px; background: linear-gradient(135deg, #f0fdf4, #dcfce7);">
              <i class="ki-duotone ki-verify fs-2x" style="color:#16a34a;"><span class="path1"></span><span class="path2"></span></i>
            </div>
            <div>
              <div class="fw-bolder text-gray-800 fs-6">Status Akun</div>
              <div class="text-muted fw-semibold fs-8">Keamanan & verifikasi</div>
            </div>
          </div>
          <div class="my-5" style="height:1px; background:#f1f5f9;"></div>
          <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <div class="d-flex align-items-center gap-3">
                <i class="ki-duotone ki-check-circle fs-4" style="color:#16a34a;"><span class="path1"></span><span class="path2"></span></i>
                <span class="text-gray-600 fw-semibold fs-7">Status</span>
              </div>
              <span class="fw-bold fs-8 px-3 py-1 rounded-pill" style="background:#dcfce7; color:#16a34a;">Aktif</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <div class="d-flex align-items-center gap-3">
                <i class="ki-duotone ki-sms fs-4" style="color:#16a34a;"><span class="path1"></span><span class="path2"></span></i>
                <span class="text-gray-600 fw-semibold fs-7">Verifikasi Email</span>
              </div>
              <span class="fw-bold fs-8 px-3 py-1 rounded-pill" style="background:#dcfce7; color:#16a34a;">Terverifikasi</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <div class="d-flex align-items-center gap-3">
                <i class="ki-duotone ki-lock-3 fs-4" style="color:#d97706;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <span class="text-gray-600 fw-semibold fs-7">Keamanan</span>
              </div>
              <span class="fw-bold fs-8 px-3 py-1 rounded-pill" style="background:#fef3c7; color:#d97706;">Standard</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Status Card-->

    <!--begin::Role Card-->
    <div class="col-md-4">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-body p-8">
          <div class="d-flex align-items-center gap-4 mb-7">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:46px;height:46px; background: linear-gradient(135deg, #fffbeb, #fef08a);">
              <i class="ki-duotone ki-shield-tick fs-2x" style="color:#ca8a04;"><span class="path1"></span><span class="path2"></span></i>
            </div>
            <div>
              <div class="fw-bolder text-gray-800 fs-6">Role & Akses</div>
              <div class="text-muted fw-semibold fs-8">Hak akses pengguna</div>
            </div>
          </div>
          <div class="my-5" style="height:1px; background:#f1f5f9;"></div>
          <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <span class="text-gray-500 fw-semibold fs-8">Role</span>
              <span class="fw-bold fs-8 px-3 py-1 rounded-pill text-capitalize" style="background:#fef9c3; color:#854d0e;">{{ authStore.user.role || 'user' }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <span class="text-gray-500 fw-semibold fs-8">Bergabung</span>
              <span class="fw-bolder fs-8 text-gray-700">{{ joinDate }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-2" style="background:#f8fafc;">
              <span class="text-gray-500 fw-semibold fs-8">Akses Level</span>
              <span class="fw-bold fs-8 px-3 py-1 rounded-pill" style="background:#f1f5f9; color:#475569;">Standar</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Role Card-->

    <!--begin::Profile Info-->
    <div class="col-md-8">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-header border-0 pt-7 pb-0 px-8">
          <div>
            <h3 class="fw-bolder text-gray-800 mb-1" style="font-size:1.1rem; letter-spacing:-0.2px;">Informasi Profil</h3>
            <div class="text-muted fw-semibold fs-8">Data akun kamu saat ini</div>
          </div>
          <div class="card-toolbar">
            <router-link to="/user/profile" class="btn btn-sm fw-semibold px-5 fs-8" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color:#1d4ed8; border:none; border-radius:8px;">
              <i class="ki-duotone ki-pencil fs-5 me-1" style="color:#3b82f6;"><span class="path1"></span><span class="path2"></span></i>
              Edit Profil
            </router-link>
          </div>
        </div>
        <div class="card-body px-8 py-5">
          <div class="d-flex flex-column gap-2">
            <div v-for="(item, index) in profileFields" :key="item.key"
              class="d-flex align-items-center justify-content-between py-3 px-4 rounded-2"
              :style="index % 2 === 0 ? 'background:#f8fafc;' : 'background:#fff;'">
              <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" :class="item.bgClass" style="width:32px;height:32px;">
                  <i :class="['ki-duotone', item.icon, 'fs-5', item.iconColor]">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                  </i>
                </div>
                <span class="text-gray-500 fw-semibold fs-8">{{ item.label }}</span>
              </div>
              <span v-if="item.value" class="text-gray-800 fw-bolder fs-7">{{ item.value }}</span>
              <span v-else class="fw-bold fs-9 px-3 py-1 rounded-pill" style="background:#fff5f5; color:#dc2626;">Belum diisi</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Profile Info-->

    <!--begin::Quick Actions-->
    <div class="col-md-4">
      <div class="card h-100" style="border: 1px solid #eef0f5; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <div class="card-header border-0 pt-7 pb-0 px-8">
          <div>
            <h3 class="fw-bolder text-gray-800 mb-1" style="font-size:1.1rem; letter-spacing:-0.2px;">Quick Actions</h3>
            <div class="text-muted fw-semibold fs-8">Aksi cepat untuk kamu</div>
          </div>
        </div>
        <div class="card-body pt-5 pb-7 px-8">
          <div class="d-flex flex-column gap-3 mb-6">

            <router-link to="/user/profile" class="d-flex align-items-center gap-4 rounded-2 px-4 py-4 text-decoration-none" style="border: 1.5px solid #dbeafe; background: #eff6ff; transition: all 0.2s;" @mouseenter="e => (e.currentTarget as HTMLElement).style.background='#dbeafe'" @mouseleave="e => (e.currentTarget as HTMLElement).style.background='#eff6ff'">
              <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:40px;height:40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 4px 12px rgba(59,130,246,0.3);">
                <i class="ki-duotone ki-profile-circle fs-4 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
              </div>
              <div>
                <div class="fw-bold fs-7" style="color:#1d4ed8;">Update Profil</div>
                <div class="text-muted fs-8">Lengkapi data kamu</div>
              </div>
              <i class="ki-duotone ki-arrow-right fs-5 ms-auto" style="color:#93c5fd;"><span class="path1"></span><span class="path2"></span></i>
            </router-link>

            <!-- ✅ Ubah Password — buka modal -->
            <div
              class="d-flex align-items-center gap-4 rounded-2 px-4 py-4 cursor-pointer"
              style="border: 1.5px solid #fde68a; background: #fffbeb; transition: all 0.2s;"
              @mouseenter="e => (e.currentTarget as HTMLElement).style.background='#fef3c7'"
              @mouseleave="e => (e.currentTarget as HTMLElement).style.background='#fffbeb'"
              @click="openModal"
            >
              <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:40px;height:40px; background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                <i class="ki-duotone ki-lock-3 fs-4 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
              </div>
              <div>
                <div class="fw-bold fs-7" style="color:#92400e;">Ubah Password</div>
                <div class="text-muted fs-8">Perbarui keamanan akun</div>
              </div>
              <i class="ki-duotone ki-arrow-right fs-5 ms-auto" style="color:#fcd34d;"><span class="path1"></span><span class="path2"></span></i>
            </div>

            <div class="d-flex align-items-center gap-4 rounded-2 px-4 py-4 cursor-pointer" style="border: 1.5px solid #bae6fd; background: #f0f9ff; transition: all 0.2s;">
              <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:40px;height:40px; background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 4px 12px rgba(14,165,233,0.3);">
                <i class="ki-duotone ki-notification fs-4 text-white"><span class="path1"></span><span class="path2"></span></i>
              </div>
              <div>
                <div class="fw-bold fs-7" style="color:#075985;">Notifikasi</div>
                <div class="text-muted fs-8">Atur preferensi notifikasi</div>
              </div>
              <i class="ki-duotone ki-arrow-right fs-5 ms-auto" style="color:#7dd3fc;"><span class="path1"></span><span class="path2"></span></i>
            </div>
          </div>

          <div style="height:1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent);" class="mb-6"></div>

          <div v-if="profileCompletion < 100" class="rounded-2 p-4 d-flex align-items-start gap-3" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px dashed #fcd34d;">
            <i class="ki-duotone ki-information-5 fs-2x flex-shrink-0" style="color:#f59e0b;"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div>
              <div class="fw-bold fs-7" style="color:#78350f;">Profil Belum Lengkap</div>
              <div class="text-muted fs-8 mt-1">Lengkapi profil untuk pengalaman terbaik.</div>
            </div>
          </div>
          <div v-else class="rounded-2 p-4 d-flex align-items-start gap-3" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px dashed #86efac;">
            <i class="ki-duotone ki-verify fs-2x flex-shrink-0" style="color:#16a34a;"><span class="path1"></span><span class="path2"></span></i>
            <div>
              <div class="fw-bold fs-7" style="color:#14532d;">Profil Lengkap! 🎉</div>
              <div class="text-muted fs-8 mt-1">Semua informasi profil sudah terisi.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end::Quick Actions-->

  </div>

  <!-- ============================================ -->
  <!-- MODAL UBAH PASSWORD                          -->
  <!-- ============================================ -->
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="showModal" class="cp-overlay" @click.self="closeModal">
        <div class="cp-box">

          <!-- Dekorasi -->
          <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;border-radius:50%;background:rgba(59,130,246,0.06);pointer-events:none;"></div>
          <div style="position:absolute;bottom:-60px;left:-40px;width:220px;height:220px;border-radius:50%;background:rgba(245,158,11,0.04);pointer-events:none;"></div>

          <!-- Header modal -->
          <div class="d-flex align-items-center justify-content-between px-8 pt-8 pb-6">
            <div class="d-flex align-items-center gap-4">
              <div style="width:46px;height:46px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;box-shadow:0 4px 14px rgba(245,158,11,0.4);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ki-duotone ki-lock-3 fs-2 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
              </div>
              <div>
                <div class="fw-bolder text-white" style="font-size:1.1rem;letter-spacing:-0.2px;">Ubah Password</div>
                <div style="color:rgba(255,255,255,0.4);font-size:0.78rem;font-weight:500;">Perbarui keamanan akun kamu</div>
              </div>
            </div>
            <button @click="closeModal" style="width:34px;height:34px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
              <i class="ki-duotone ki-cross fs-4 text-white"><span class="path1"></span><span class="path2"></span></i>
            </button>
          </div>

          <!-- Divider -->
          <div style="height:1px;background:linear-gradient(to right,transparent,rgba(255,255,255,0.08),transparent);margin:0 32px 0;"></div>

          <!-- Form -->
          <VForm class="px-8 pt-6 pb-8" @submit="onSubmit" :validation-schema="schema">

            <!-- Password Lama -->
            <div class="mb-5">
              <label style="color:rgba(255,255,255,0.65);font-size:0.8rem;font-weight:600;display:block;margin-bottom:8px;">Password Lama</label>
              <div class="position-relative">
                <Field
                  class="cp-input"
                  :type="show.current ? 'text' : 'password'"
                  name="current_password"
                  placeholder="Masukkan password lama"
                  autocomplete="current-password"
                />
                <span class="cp-eye" @click="show.current = !show.current">
                  <i v-if="!show.current" class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                  <i v-else class="ki-duotone ki-eye-slash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </span>
              </div>
              <div class="mt-1"><span class="cp-error"><ErrorMessage name="current_password" /></span></div>
            </div>

            <!-- Separator -->
            <div style="height:1px;background:rgba(255,255,255,0.06);margin:4px 0 20px;"></div>

            <!-- Password Baru -->
            <div class="mb-5">
              <label style="color:rgba(255,255,255,0.65);font-size:0.8rem;font-weight:600;display:block;margin-bottom:8px;">Password Baru</label>
              <div class="position-relative">
                <Field
                  class="cp-input"
                  :type="show.newPass ? 'text' : 'password'"
                  name="password"
                  placeholder="Minimal 8 karakter"
                  autocomplete="new-password"
                />
                <span class="cp-eye" @click="show.newPass = !show.newPass">
                  <i v-if="!show.newPass" class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                  <i v-else class="ki-duotone ki-eye-slash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </span>
              </div>
              <div class="mt-1"><span class="cp-error"><ErrorMessage name="password" /></span></div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="mb-7">
              <label style="color:rgba(255,255,255,0.65);font-size:0.8rem;font-weight:600;display:block;margin-bottom:8px;">Konfirmasi Password Baru</label>
              <div class="position-relative">
                <Field
                  class="cp-input"
                  :type="show.confirm ? 'text' : 'password'"
                  name="password_confirmation"
                  placeholder="Ulangi password baru"
                  autocomplete="new-password"
                />
                <span class="cp-eye" @click="show.confirm = !show.confirm">
                  <i v-if="!show.confirm" class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                  <i v-else class="ki-duotone ki-eye-slash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </span>
              </div>
              <div class="mt-1"><span class="cp-error"><ErrorMessage name="password_confirmation" /></span></div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-3">
              <button type="button" @click="closeModal" class="btn fw-semibold fs-7 flex-grow-1" style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.65);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:13px;">
                Batal
              </button>
              <button type="submit" ref="submitBtn" class="btn fw-bold fs-7 flex-grow-1" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;border-radius:10px;padding:13px;box-shadow:0 4px 14px rgba(245,158,11,0.35);">
                <span class="indicator-label">
                  <i class="ki-duotone ki-check fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>Simpan
                </span>
                <span class="indicator-progress">
                  Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
              </button>
            </div>
          </VForm>
        </div>
      </div>
    </Transition>
  </Teleport>
  <!-- ============================================ -->
  <!-- END MODAL                                    -->
  <!-- ============================================ -->

</template>

<script lang="ts">
import { defineComponent, computed, ref, reactive } from "vue";
import { ErrorMessage, Field, Form as VForm } from "vee-validate";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2/dist/sweetalert2.js";
import * as Yup from "yup";

export default defineComponent({
  name: "user-dashboard",
  components: { Field, VForm, ErrorMessage },
  setup() {
    const authStore = useAuthStore();

    const avatarUrl = computed(() => {
      if (!authStore.user.avatar) return null;
      return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/storage/${authStore.user.avatar}`;
    });

    // ===== PROFILE =====
    const fieldConfig = [
      { key: "name",      label: "Nama Lengkap", icon: "ki-profile-circle", bgClass: "bg-light-primary", iconColor: "text-primary" },
      { key: "email",     label: "Email",         icon: "ki-sms",            bgClass: "bg-light-info",    iconColor: "text-info" },
      { key: "phone",     label: "No. Telepon",   icon: "ki-phone",          bgClass: "bg-light-success", iconColor: "text-success" },
      { key: "job_title", label: "Jabatan",       icon: "ki-briefcase",      bgClass: "bg-light-warning", iconColor: "text-warning" },
      { key: "company",   label: "Perusahaan",    icon: "ki-building",       bgClass: "bg-light-danger",  iconColor: "text-danger" },
      { key: "bio",       label: "Bio",           icon: "ki-notepad",        bgClass: "bg-light-warning", iconColor: "text-warning" },
    ];

    const profileFields = computed(() =>
      fieldConfig.map(f => ({ ...f, value: authStore.user[f.key as keyof typeof authStore.user] || "" }))
    );
    const fieldStatus = computed(() =>
      fieldConfig.map(f => ({ key: f.key, label: f.label, filled: !!authStore.user[f.key as keyof typeof authStore.user] }))
    );
    const filledCount = computed(() => fieldStatus.value.filter(f => f.filled).length);
    const totalFields = fieldConfig.length;
    const profileCompletion = computed(() => Math.round((filledCount.value / totalFields) * 100));
    const joinDate = computed(() => {
      const d = (authStore.user as any).created_at;
      if (!d) return "—";
      return new Date(d).toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
    });

    // ===== MODAL UBAH PASSWORD =====
    const showModal = ref(false);
    const submitBtn = ref<HTMLButtonElement | null>(null);
    const show = reactive({ current: false, newPass: false, confirm: false });

    const schema = Yup.object().shape({
      current_password: Yup.string().required("Password lama wajib diisi"),
      password: Yup.string().min(8, "Minimal 8 karakter").required("Password baru wajib diisi"),
      password_confirmation: Yup.string()
        .required("Konfirmasi password wajib diisi")
        .oneOf([Yup.ref("password")], "Password tidak cocok"),
    });

    const openModal = () => {
      showModal.value = true;
      show.current = false;
      show.newPass = false;
      show.confirm = false;
    };

    const closeModal = () => {
      showModal.value = false;
      authStore.errors = {};
    };

    const onSubmit = async (values: any, { resetForm }: any) => {
      if (submitBtn.value) {
        submitBtn.value.disabled = true;
        submitBtn.value.setAttribute("data-kt-indicator", "on");
      }

      await authStore.changePassword({
        current_password: values.current_password,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });

      const error = Object.values(authStore.errors);

      if (error.length === 0) {
        closeModal();
        resetForm();
        Swal.fire({
          title: "Berhasil!",
          text: "Password kamu berhasil diubah.",
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
        }).then(() => { authStore.errors = {}; });
      }

      if (submitBtn.value) {
        submitBtn.value.removeAttribute("data-kt-indicator");
        submitBtn.value.disabled = false;
      }
    };

    return {
      authStore, avatarUrl,
      profileFields, fieldStatus, filledCount, totalFields, profileCompletion, joinDate,
      showModal, submitBtn, show, schema,
      openModal, closeModal, onSubmit,
    };
  },
});
</script>

<style scoped>
.card { border-radius: 14px; }

/* Overlay */
.cp-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(6px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Modal box — sama tema dark blue dengan hero card */
.cp-box {
  width: 100%;
  max-width: 460px;
  background: linear-gradient(145deg, #0f1b35 0%, #1a2f5a 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 30px 70px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255,255,255,0.04);
}

/* Input dark */
.cp-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.06);
  border: 1.5px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 500;
  padding: 12px 48px 12px 16px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.cp-input::placeholder { color: rgba(255, 255, 255, 0.22); }
.cp-input:focus {
  border-color: rgba(245, 158, 11, 0.55);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
  background: rgba(255, 255, 255, 0.09);
}

/* Eye toggle */
.cp-eye {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.cp-eye:hover { color: rgba(255, 255, 255, 0.6); }

/* Error text */
.cp-error {
  color: #fbbf24;
  font-size: 0.76rem;
  font-weight: 600;
}

/* Animasi modal */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-active .cp-box,
.modal-fade-leave-active .cp-box { transition: transform 0.2s ease, opacity 0.2s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .cp-box,
.modal-fade-leave-to .cp-box { transform: scale(0.96) translateY(8px); opacity: 0; }
</style>