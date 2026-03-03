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
                  <span class="symbol-label" :style="{ backgroundColor: (method.color || '#009ef7') + '20' }">
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
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Edit' : 'Tambah' }} Metode Pembayaran</h5>
            <button class="btn-close" @click="formModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row g-5">
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

const loading = ref(true);
const actionLoading = ref(false);
const methods = ref<any[]>([]);
const formModal = ref(false);
const editingId = ref<number | null>(null);
const errorMsg = ref("");

const defaultForm = () => ({
  name: "", code: "", type: "bank_transfer", color: "#009ef7",
  account_number: "", account_name: "", account_bank: "",
  instructions: "", min_amount: 10000, max_amount: 50000000,
  is_active: true, sort_order: 0,
});
const form = ref(defaultForm());

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");

const fetchMethods = async () => {
  loading.value = true;
  try {
    const { data } = await ApiService.get("admin/payment-methods", "");
    methods.value = data.data;
  } finally {
    loading.value = false;
  }
};

const openCreate = () => {
  editingId.value = null;
  form.value = defaultForm();
  errorMsg.value = "";
  formModal.value = true;
};

const openEdit = (method: any) => {
  editingId.value = method.id;
  form.value = { ...method };
  errorMsg.value = "";
  formModal.value = true;
};

const submitForm = async () => {
  errorMsg.value = "";
  actionLoading.value = true;
  try {
    if (editingId.value) {
      await ApiService.put(`admin/payment-methods/${editingId.value}`, form.value);
    } else {
      await ApiService.post("admin/payment-methods", form.value);
    }
    formModal.value = false;
    fetchMethods();
  } catch (e: any) {
    errorMsg.value = e.response?.data?.message ?? "Gagal menyimpan.";
  } finally {
    actionLoading.value = false;
  }
};

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