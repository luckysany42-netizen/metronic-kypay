<template>
  <div class="d-flex flex-column gap-7">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-primary">
            <i class="bi bi-plus-circle-fill text-primary fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Top Up KyPay</h3>
          <div class="text-muted fs-7">Isi saldo KyPay kamu dengan transfer bank</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo saat ini</div>
          <div class="fw-bold text-primary fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Pilih Metode -->
    <div class="card" v-if="step === 1">
      <div class="card-header border-0 pt-6">
        <h3 class="card-title fw-bold">Pilih Metode Pembayaran</h3>
      </div>
      <div class="card-body pt-0">
        <div v-if="loadingMethods" class="text-center py-10">
          <span class="spinner-border text-primary"></span>
        </div>
        <div v-else class="row g-4">
          <div
            v-for="method in paymentMethods"
            :key="method.id"
            class="col-12 col-md-6"
            @click="selectMethod(method)"
          >
            <div
              class="border rounded-2 p-5 cursor-pointer transition"
              :class="selectedMethod?.id === method.id ? 'border-primary bg-light-primary' : 'border-gray-200'"
              style="transition: all 0.2s"
            >
              <div class="d-flex align-items-center gap-3">
                <div class="symbol symbol-40px">
                  <span class="symbol-label" :style="{ backgroundColor: method.color + '20' }">
                    <i class="bi bi-bank fs-4" :style="{ color: method.color || '#009ef7' }"></i>
                  </span>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold text-gray-800">{{ method.name }}</div>
                  <div class="text-muted fs-8">{{ method.account_number }} · {{ method.account_name }}</div>
                </div>
                <i v-if="selectedMethod?.id === method.id" class="bi bi-check-circle-fill text-primary fs-4"></i>
              </div>
              <div class="mt-3 text-muted fs-8">
                Min: {{ formatRupiah(method.min_amount) }} · Maks: {{ formatRupiah(method.max_amount) }}
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 d-flex justify-content-end">
          <button class="btn btn-primary px-8" :disabled="!selectedMethod" @click="step = 2">
            Lanjut <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Step 2: Isi Form & Upload Bukti -->
    <div class="card" v-if="step === 2">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 1">
            <i class="bi bi-arrow-left"></i>
          </button>
          <h3 class="card-title fw-bold mb-0">Detail Top Up</h3>
        </div>
      </div>
      <div class="card-body pt-4">

        <!-- Info Transfer Tujuan -->
        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-6">
          <i class="bi bi-info-circle-fill text-primary fs-2 me-4 mt-1"></i>
          <div>
            <div class="fw-bold text-primary mb-1">Transfer ke rekening berikut:</div>
            <div class="fw-bolder fs-5">{{ selectedMethod?.account_bank }} — {{ selectedMethod?.account_number }}</div>
            <div class="text-muted">a.n. {{ selectedMethod?.account_name }}</div>
            <div class="text-muted fs-8 mt-2" v-if="selectedMethod?.instructions">{{ selectedMethod.instructions }}</div>
          </div>
        </div>

        <div class="row g-5">
          <div class="col-12 col-md-6">
            <label class="form-label required">Jumlah Top Up (Rp)</label>
            <input
              v-model="form.amount"
              type="number"
              class="form-control form-control-solid"
              :min="selectedMethod?.min_amount"
              :max="selectedMethod?.max_amount"
              placeholder="Contoh: 100000"
            />
            <div class="form-text text-muted">
              Min: {{ formatRupiah(selectedMethod?.min_amount) }} · Maks: {{ formatRupiah(selectedMethod?.max_amount) }}
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Catatan (opsional)</label>
            <input v-model="form.user_note" type="text" class="form-control form-control-solid" placeholder="Catatan tambahan..." />
          </div>

          <div class="col-12">
            <label class="form-label required">Upload Bukti Transfer</label>
            <div
              class="border-2 border-dashed border-gray-300 rounded-2 p-8 text-center cursor-pointer"
              :class="{ 'border-primary bg-light-primary': previewImage }"
              @click="$refs.fileInput.click()"
            >
              <div v-if="!previewImage">
                <i class="bi bi-cloud-upload fs-2x text-muted mb-3 d-block"></i>
                <div class="text-muted fs-7">Klik untuk upload bukti transfer</div>
                <div class="text-muted fs-8 mt-1">JPG, PNG maksimal 5MB</div>
              </div>
              <div v-else>
                <img :src="previewImage" alt="Bukti Transfer" class="rounded-2 mh-200px" />
                <div class="text-primary fs-8 mt-2">Klik untuk ganti gambar</div>
              </div>
            </div>
            <input ref="fileInput" type="file" accept="image/*" class="d-none" @change="handleFileChange" />
          </div>
        </div>

        <div v-if="errorMsg" class="alert alert-danger mt-4 py-3 fs-7">{{ errorMsg }}</div>

        <div class="mt-6 d-flex justify-content-end gap-3">
          <button class="btn btn-light" @click="step = 1">Kembali</button>
          <button class="btn btn-primary px-8" @click="submitTopUp" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Kirim Pengajuan
          </button>
        </div>
      </div>
    </div>

    <!-- Step 3: Sukses -->
    <div class="card" v-if="step === 3">
      <div class="card-body text-center py-15">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mt-5 mb-3">Pengajuan Terkirim!</h3>
        <div class="text-muted fs-6 mb-2">Nomor Referensi: <strong>{{ successRef }}</strong></div>
        <div class="text-muted fs-7 mb-8">Menunggu konfirmasi admin. Saldo akan otomatis bertambah setelah disetujui.</div>
        <div class="d-flex justify-content-center gap-3">
          <router-link :to="{ name: 'user-topup-history' }" class="btn btn-light-primary">Lihat Riwayat</router-link>
          <router-link :to="{ name: 'user-wallet' }" class="btn btn-primary">Kembali ke Wallet</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const step = ref(1);
const loadingMethods = ref(true);
const loading = ref(false);
const errorMsg = ref("");
const successRef = ref("");

const paymentMethods = ref<any[]>([]);
const selectedMethod = ref<any>(null);
const currentBalance = ref(0);
const previewImage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = ref({
  amount: "",
  user_note: "",
  proof_image: null as File | null,
  payment_method: "",
  payment_account: "",
  payment_holder: "",
});

const formatRupiah = (val: number) =>
  "Rp " + Number(val || 0).toLocaleString("id-ID");

const selectMethod = (method: any) => {
  selectedMethod.value = method;
  form.value.payment_method = method.name;
  form.value.payment_account = method.account_number;
  form.value.payment_holder = method.account_name;
};

const handleFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  form.value.proof_image = file;
  previewImage.value = URL.createObjectURL(file);
};

const submitTopUp = async () => {
  errorMsg.value = "";
  if (!form.value.amount || Number(form.value.amount) < (selectedMethod.value?.min_amount ?? 0)) {
    errorMsg.value = `Jumlah minimum top up adalah ${formatRupiah(selectedMethod.value?.min_amount)}.`; return;
  }
  if (!form.value.proof_image) {
    errorMsg.value = "Bukti transfer wajib diupload."; return;
  }

  loading.value = true;
  try {
    const formData = new FormData();
    formData.append("amount", form.value.amount);
    formData.append("payment_method", form.value.payment_method);
    formData.append("payment_account", form.value.payment_account);
    formData.append("payment_holder", form.value.payment_holder);
    formData.append("proof_image", form.value.proof_image);
    if (form.value.user_note) formData.append("user_note", form.value.user_note);

    const { data } = await ApiService.post("topup", formData);
    successRef.value = data.data?.reference_number ?? data.reference_number ?? "-";
    step.value = 3;
  } catch (e: any) {
    errorMsg.value = e.response?.data?.message ?? "Gagal mengirim pengajuan.";
  } finally {
    loading.value = false;
  }
};

const fetchData = async () => {
  try {
    const [methodsRes, walletRes] = await Promise.all([
      ApiService.get("topup", "payment-methods"),
      ApiService.get("wallet", "").catch(() => ({ data: {} })),
    ]);
    paymentMethods.value = methodsRes.data.data ?? methodsRes.data ?? [];
    // Fix: handle struktur response wallet yang benar
    const walletData = walletRes.data.wallet ?? walletRes.data.data ?? walletRes.data ?? {};
    currentBalance.value = Number(walletData.balance ?? 0);
  } catch (e) {
    console.error("fetchData error:", e);
  } finally {
    loadingMethods.value = false;
  }
};

onMounted(fetchData);
</script>