<template>
  <div class="card">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <h3 class="fw-bold mb-0">Metode Pembayaran</h3>
      </div>
      <div class="card-toolbar">
        <button class="btn btn-primary" @click="openCreate">
          <i class="bi bi-plus-lg me-2"></i> Tambah Metode
        </button>
      </div>
    </div>
    <div class="card-body pt-0">

      <div v-if="loading" class="text-center py-15">
        <span class="spinner-border text-primary"></span>
      </div>

      <div v-else class="row g-5">
        <div v-for="method in methods" :key="method.id" class="col-12 col-md-6 col-lg-4">
          <div class="card border h-100" :class="!method.is_active ? 'opacity-50' : ''">
            <div class="card-body p-6">
              <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="symbol symbol-45px">
                  <img
                    v-if="method.logo"
                    :src="method.logo"
                    alt=""
                    class="rounded-circle object-fit-cover w-45px h-45px"
                    style="border: 2px solid #f1f1f1;"
                  />
                  <span v-else class="symbol-label" :style="{ backgroundColor: (method.color || '#009ef7') + '20' }">
                    <i class="bi bi-bank fs-3" :style="{ color: method.color || '#009ef7' }"></i>
                  </span>
                </div>
                <span class="badge" :class="method.is_active ? 'badge-light-success' : 'badge-light-danger'">
                  {{ method.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>

              <div class="fw-bolder fs-5 mb-1">{{ method.name }}</div>
              <div class="text-muted fs-8 mb-1">{{ method.account_number }}</div>
              <div class="text-muted fs-8 mb-3">a.n. {{ method.account_name }}</div>

              <div class="d-flex justify-content-between text-muted fs-8 mb-4">
                <span>Min: {{ formatRupiah(method.min_amount) }}</span>
                <span>Maks: {{ formatRupiah(method.max_amount) }}</span>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-light-primary flex-grow-1" @click="openEdit(method)">
                  <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button class="btn btn-sm btn-light-danger" @click="deleteMethod(method)">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="methods.length === 0" class="col-12 text-center py-10">
          <i class="bi bi-credit-card fs-3x text-muted mb-4 d-block"></i>
          <div class="text-muted">Belum ada metode pembayaran</div>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="formModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="formModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Edit' : 'Tambah' }} Metode Pembayaran</h5>
            <button class="btn-close" @click="formModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row g-5">

              <!-- Upload Logo -->
              <div class="col-12">
                <label class="form-label fw-bold">Logo / Ikon Metode Pembayaran</label>
                <div class="d-flex align-items-center gap-5">
                  <div class="symbol symbol-70px flex-shrink-0">
                    <img
                      v-if="logoPreview"
                      :src="logoPreview"
                      alt="Preview"
                      class="rounded-circle object-fit-cover w-70px h-70px"
                      style="border: 2px solid #e4e6ef;"
                    />
                    <span v-else class="symbol-label bg-light-primary" :style="form.color ? { backgroundColor: form.color + '20' } : {}">
                      <i class="bi bi-image fs-2" :style="form.color ? { color: form.color } : { color: '#009ef7' }"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <input
                      ref="logoInput"
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                      class="d-none"
                      @change="onLogoChange"
                    />
                    <button class="btn btn-sm btn-light-primary mb-2" @click="logoInput?.click()">
                      <i class="bi bi-upload me-1"></i>
                      {{ logoPreview ? 'Ganti Logo' : 'Upload Logo' }}
                    </button>
                    <button v-if="logoPreview" class="btn btn-sm btn-light-danger ms-2 mb-2" @click="removeLogo">
                      <i class="bi bi-x-circle me-1"></i>Hapus
                    </button>
                    <div class="text-muted fs-8">Format: PNG, JPG, SVG. Maks: 1MB. Disarankan ukuran 100x100px</div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label required">Nama</label>
                <input v-model="form.name" type="text" class="form-control form-control-solid" placeholder="Contoh: BCA Transfer" />
              </div>
              <div class="col-md-6">
                <label class="form-label required">Kode</label>
                <input v-model="form.code" type="text" class="form-control form-control-solid" placeholder="Contoh: bca" />
              </div>
              <div class="col-md-6">
                <label class="form-label required">Tipe</label>
                <select v-model="form.type" class="form-select form-select-solid">
                  <option value="bank_transfer">Transfer Bank</option>
                  <option value="e_wallet">E-Wallet</option>
                  <option value="other">Lainnya</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Warna (hex)</label>
                <div class="input-group">
                  <input v-model="form.color" type="color" class="form-control form-control-solid" style="max-width: 60px; padding: 4px;" />
                  <input v-model="form.color" type="text" class="form-control form-control-solid" placeholder="#009ef7" />
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nomor Rekening / HP</label>
                <input v-model="form.account_number" type="text" class="form-control form-control-solid" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Pemilik</label>
                <input v-model="form.account_name" type="text" class="form-control form-control-solid" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Bank</label>
                <input v-model="form.account_bank" type="text" class="form-control form-control-solid" />
              </div>
              <div class="col-md-3">
                <label class="form-label">Min Top Up</label>
                <input v-model="form.min_amount" type="number" class="form-control form-control-solid" />
              </div>
              <div class="col-md-3">
                <label class="form-label">Maks Top Up</label>
                <input v-model="form.max_amount" type="number" class="form-control form-control-solid" />
              </div>
              <div class="col-12">
                <label class="form-label">Instruksi Pembayaran</label>
                <textarea v-model="form.instructions" class="form-control form-control-solid" rows="3" placeholder="Langkah-langkah transfer..."></textarea>
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch">
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" />
                  <label class="form-check-label fw-bold">Aktif</label>
                </div>
              </div>
            </div>
            <div v-if="errorMsg" class="alert alert-danger mt-4 py-2 fs-7">{{ errorMsg }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="formModal = false">Batal</button>
            <button class="btn btn-primary" @click="submitForm" :disabled="actionLoading">
              <span v-if="actionLoading" class="spinner-border spinner-border-sm me-2"></span>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const loading       = ref(true);
const actionLoading = ref(false);
const methods       = ref<any[]>([]);
const formModal     = ref(false);
const editingId     = ref<number | null>(null);
const errorMsg      = ref("");

// Logo
const logoInput   = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string>("");
const logoFile    = ref<File | null>(null);
const logoRemoved = ref(false); // ✅ track apakah user hapus logo

const defaultForm = () => ({
  name: "", code: "", type: "bank_transfer", color: "#009ef7",
  account_number: "", account_name: "", account_bank: "",
  instructions: "", min_amount: 10000, max_amount: 50000000,
  is_active: true, sort_order: 0,
});
const form = ref(defaultForm());

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");

// ── Logo handlers ─────────────────────────────────────
const onLogoChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;

  if (file.size > 1024 * 1024) {
    alert("Ukuran file terlalu besar. Maksimal 1MB.");
    return;
  }

  logoFile.value    = file;
  logoPreview.value = URL.createObjectURL(file);
  logoRemoved.value = false; // ada file baru, batalkan hapus
};

const removeLogo = () => {
  logoFile.value    = null;
  logoPreview.value = "";
  logoRemoved.value = true; // ✅ tandai logo dihapus
  if (logoInput.value) logoInput.value.value = "";
};

// ── Fetch ─────────────────────────────────────────────
const fetchMethods = async () => {
  loading.value = true;
  try {
    const { data } = await ApiService.get("admin/payment-methods", "");
    methods.value = data.data;
  } finally {
    loading.value = false;
  }
};

// ── Open modal ────────────────────────────────────────
const openCreate = () => {
  editingId.value   = null;
  form.value        = defaultForm();
  logoFile.value    = null;
  logoPreview.value = "";
  logoRemoved.value = false;
  errorMsg.value    = "";
  formModal.value   = true;
};

const openEdit = (method: any) => {
  editingId.value   = method.id;
  form.value        = { ...method };
  logoFile.value    = null;
  logoPreview.value = method.logo ?? "";
  logoRemoved.value = false;
  errorMsg.value    = "";
  formModal.value   = true;
};

// ── Submit dengan FormData ────────────────────────────
const submitForm = async () => {
  errorMsg.value = "";
  actionLoading.value = true;

  try {
    const formData = new FormData();

    // Append semua field form — SKIP field logo karena dihandle terpisah
  Object.entries(form.value).forEach(([key, value]) => {
  if (key === 'logo') return; // ✅ skip, logo dihandle terpisah di bawah
  if (value !== null && value !== undefined) {
    if (key === 'is_active') {
      formData.append(key, value ? '1' : '0');
    } else {
      formData.append(key, String(value));
    }
  }
});

    if (logoFile.value) {
      // ✅ Ada file baru → upload logo baru
      formData.append("logo", logoFile.value);
    } else if (logoRemoved.value) {
      // ✅ User klik hapus & tidak upload baru → kirim sinyal hapus
      formData.append("remove_logo", "1");
    }
    // Jika tidak keduanya → tidak kirim field logo (logo tidak berubah)

    if (editingId.value) {
      formData.append("_method", "PUT");
      await ApiService.post(`admin/payment-methods/${editingId.value}`, formData);
    } else {
      await ApiService.post("admin/payment-methods", formData);
    }

    formModal.value = false;
    fetchMethods();
  } catch (e: any) {
    errorMsg.value = e.response?.data?.message ?? "Gagal menyimpan.";
  } finally {
    actionLoading.value = false;
  }
};

// ── Delete ────────────────────────────────────────────
const deleteMethod = async (method: any) => {
  if (!confirm(`Hapus "${method.name}"?`)) return;
  try {
    await ApiService.delete(`admin/payment-methods/${method.id}`);
    fetchMethods();
  } catch (e: any) {
    alert(e.response?.data?.message ?? "Gagal menghapus.");
  }
};

onMounted(fetchMethods);
</script>