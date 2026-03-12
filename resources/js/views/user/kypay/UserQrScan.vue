<template>
  <div class="d-flex flex-column gap-6">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-primary">
            <i class="bi bi-qr-code-scan text-primary fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Scan & Bayar QR</h3>
          <div class="text-muted fs-7">Scan QR Code KyPay milik pengguna atau merchant lain</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo KyPay</div>
          <div class="fw-bold text-primary fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Scan / Input Token -->
    <div class="card" v-if="step === 1">
      <div class="card-header border-0 pt-6">
        <h3 class="card-title fw-bold">Masukkan Kode QR</h3>
      </div>
      <div class="card-body pt-2">
        <!-- Scanner area -->
        <div class="text-center mb-6">
          <div class="scanner-box mx-auto mb-4" @click="triggerFileInput">
            <div class="scanner-frame">
              <div class="qr-corner tl"></div>
              <div class="qr-corner tr"></div>
              <div class="qr-corner bl"></div>
              <div class="qr-corner br"></div>
              <div class="qr-scan-line"></div>
              <div class="scanner-hint">
                <i class="bi bi-camera fs-1 text-white opacity-75 d-block mb-2"></i>
                <span class="text-white fs-8 fw-semibold opacity-75">Tap untuk scan via kamera</span>
              </div>
            </div>
          </div>
          <input ref="fileInput" type="file" accept="image/*" capture="environment" class="d-none" @change="onFileCapture" />
          <div class="text-muted fs-8 mb-1">— atau —</div>
        </div>

        <!-- Manual input token -->
        <div class="mb-4">
          <label class="form-label fw-bold">Masukkan Token QR Manual</label>
          <div class="input-group">
            <input
              v-model="manualToken"
              type="text"
              class="form-control form-control-solid"
              :class="tokenError ? 'is-invalid' : ''"
              placeholder="Paste token QR di sini..."
              @input="tokenError = ''"
            />
            <button class="btn btn-primary px-6" @click="fetchDetail" :disabled="loadingDetail || !manualToken.trim()">
              <span v-if="loadingDetail" class="spinner-border spinner-border-sm"></span>
              <span v-else>Cek</span>
            </button>
          </div>
          <div v-if="tokenError" class="text-danger fs-8 mt-1">{{ tokenError }}</div>
        </div>
      </div>
    </div>

    <!-- Step 2: Konfirmasi Pembayaran -->
    <div class="card" v-if="step === 2">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 1; qrDetail = null">
            <i class="bi bi-arrow-left"></i>
          </button>
          <h3 class="card-title fw-bold mb-0">Konfirmasi Pembayaran</h3>
        </div>
      </div>
      <div class="card-body pt-4">

        <!-- Info merchant -->
        <div class="merchant-card mb-5">
          <div class="d-flex align-items-center gap-4">
            <div class="merchant-avatar">
              <i class="bi bi-person-circle fs-2 text-white"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bolder fs-5">{{ qrDetail?.merchant_name }}</div>
              <div class="text-muted fs-8">Pengguna KyPay terverifikasi</div>
            </div>
            <span class="badge badge-light-success">
              <i class="bi bi-shield-check me-1"></i>Aman
            </span>
          </div>
        </div>

        <!-- Detail transaksi -->
        <div class="bg-light rounded-3 p-6 mb-5">
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Nominal</span>
            <span class="fw-bolder text-danger fs-4">{{ formatRupiah(qrDetail?.amount) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-3" v-if="qrDetail?.description">
            <span class="text-muted fs-7">Keterangan</span>
            <span class="fw-bold">{{ qrDetail.description }}</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Saldo Kamu</span>
            <span class="fw-bold">{{ formatRupiah(currentBalance) }}</span>
          </div>
          <div class="separator my-3"></div>
          <div class="d-flex justify-content-between">
            <span class="text-muted fs-7">Saldo Setelah Bayar</span>
            <span class="fw-bold" :class="(currentBalance - (qrDetail?.amount ?? 0)) < 0 ? 'text-danger' : ''">
              {{ formatRupiah(currentBalance - (qrDetail?.amount ?? 0)) }}
            </span>
          </div>
        </div>

        <!-- Countdown -->
        <div class="text-center mb-5">
          <div class="countdown-badge" :class="timeLeft <= 60 ? 'danger' : timeLeft <= 120 ? 'warning' : 'info'">
            <i class="bi bi-clock me-1"></i>
            QR expired dalam <strong>{{ formatTime(timeLeft) }}</strong>
          </div>
        </div>

        <!-- PIN -->
        <div class="mb-5">
          <label class="form-label required fw-bold">Masukkan PIN KyPay</label>
          <input
            v-model="pin"
            type="password"
            class="form-control form-control-solid text-center fw-bold fs-3"
            maxlength="6"
            placeholder="••••••"
          />
        </div>

        <div v-if="errorMsg" class="alert alert-danger py-3 fs-7">
          <i class="bi bi-exclamation-triangle me-2"></i>{{ errorMsg }}
        </div>

        <div class="d-flex gap-3">
          <button class="btn btn-light flex-grow-1" @click="step = 1; qrDetail = null">Batal</button>
          <button
            class="btn btn-primary flex-grow-1"
            @click="submitPay"
            :disabled="loading || !pin || pin.length !== 6 || currentBalance < (qrDetail?.amount ?? 0)"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Bayar Sekarang
          </button>
        </div>
        <div v-if="currentBalance < (qrDetail?.amount ?? 0)" class="text-danger text-center fs-8 mt-2">
          Saldo tidak cukup untuk melakukan pembayaran ini.
        </div>
      </div>
    </div>

    <!-- Step 3: Sukses -->
    <div class="card" v-if="step === 3">
      <div class="card-body text-center py-15">
        <div class="symbol symbol-80px mx-auto mb-5">
          <span class="symbol-label bg-light-success">
            <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
          </span>
        </div>
        <h3 class="fw-bold mb-2">Pembayaran Berhasil!</h3>
        <div class="text-muted fs-6 mb-1">{{ formatRupiah(successData?.amount) }}</div>
        <div class="text-muted fs-7 mb-1">kepada <strong>{{ successData?.merchant_name }}</strong></div>
        <div class="text-muted fs-8 mb-5" v-if="successData?.description">{{ successData.description }}</div>
        <div class="text-muted fs-9 mb-8">Ref: {{ successData?.transaction_number }}</div>
        <div class="d-flex justify-content-center gap-3">
          <button class="btn btn-light-primary" @click="resetForm">Scan QR Lagi</button>
          <router-link to="/user/wallet" class="btn btn-primary text-white">Kembali ke Wallet</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import ApiService from "@/core/services/ApiService";

const step          = ref(1);
const loading       = ref(false);
const loadingDetail = ref(false);
const manualToken   = ref("");
const tokenError    = ref("");
const errorMsg      = ref("");
const pin           = ref("");
const currentBalance = ref(0);
const qrDetail      = ref<any>(null);
const successData   = ref<any>(null);
const timeLeft      = ref(300);
const fileInput     = ref<HTMLInputElement | null>(null);

let countdownInterval: ReturnType<typeof setInterval> | null = null;

const formatRupiah = (val: any) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatTime   = (s: number) => `${String(Math.floor(s / 60)).padStart(2, "0")}:${String(s % 60).padStart(2, "0")}`;

const triggerFileInput = () => fileInput.value?.click();

const onFileCapture = async (e: Event) => {
  // Jika menggunakan library html5-qrcode di masa depan, proses di sini
  // Untuk saat ini redirect ke input manual
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  alert("Fitur scan kamera memerlukan library html5-qrcode.\nGunakan input token manual untuk saat ini.");
};

const fetchDetail = async () => {
  tokenError.value = "";
  const token = manualToken.value.trim();
  if (!token) { tokenError.value = "Token tidak boleh kosong."; return; }

  loadingDetail.value = true;
  try {
    const { data } = await ApiService.get(`qr-payment/detail/${token}`, "");
    qrDetail.value = data.data;

    // Hitung sisa waktu dari expires_at
    const exp = new Date(data.data.expires_at).getTime();
    timeLeft.value = Math.max(0, Math.floor((exp - Date.now()) / 1000));

    startCountdown();
    step.value = 2;
  } catch (e: any) {
    tokenError.value = e.response?.data?.message ?? "QR tidak ditemukan atau sudah expired.";
  } finally {
    loadingDetail.value = false;
  }
};

const startCountdown = () => {
  if (countdownInterval) clearInterval(countdownInterval);
  countdownInterval = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) {
      clearInterval(countdownInterval!);
      errorMsg.value = "QR sudah expired. Minta QR baru dari penerima.";
    }
  }, 1000);
};

const submitPay = async () => {
  errorMsg.value = "";
  if (!pin.value || pin.value.length !== 6) { errorMsg.value = "PIN harus 6 digit."; return; }
  loading.value = true;
  try {
    const { data } = await ApiService.post("qr-payment/pay", {
      qr_token: qrDetail.value.qr_token,
      pin: pin.value,
    });
    successData.value = data.data;
    currentBalance.value -= Number(qrDetail.value.amount);
    if (countdownInterval) clearInterval(countdownInterval);
    step.value = 3;
  } catch (e: any) {
    errorMsg.value = e.response?.data?.message ?? "Pembayaran gagal.";
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  step.value = 1;
  manualToken.value = "";
  pin.value = "";
  errorMsg.value = "";
  tokenError.value = "";
  qrDetail.value = null;
  successData.value = null;
  timeLeft.value = 300;
  if (countdownInterval) clearInterval(countdownInterval);
};

onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    currentBalance.value = Number(data.data?.balance ?? 0);
  } catch {}
});

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<style scoped>
.scanner-box {
  background: #0f172a;
  border-radius: 16px;
  padding: 2rem;
  cursor: pointer;
  max-width: 280px;
  transition: opacity 0.2s;
}
.scanner-box:hover { opacity: 0.88; }
.scanner-frame {
  position: relative;
  width: 180px;
  height: 180px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qr-corner { position: absolute; width: 26px; height: 26px; border-color: #60a5fa; border-style: solid; }
.qr-corner.tl { top: 0; left: 0; border-width: 3px 0 0 3px; }
.qr-corner.tr { top: 0; right: 0; border-width: 3px 3px 0 0; }
.qr-corner.bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; }
.qr-corner.br { bottom: 0; right: 0; border-width: 0 3px 3px 0; }
.qr-scan-line {
  position: absolute; left: 8px; right: 8px; height: 2px;
  background: linear-gradient(90deg, transparent, #60a5fa, transparent);
  animation: scan 2s ease-in-out infinite;
}
@keyframes scan { 0% { top: 8px; } 50% { top: calc(100% - 8px); } 100% { top: 8px; } }
.scanner-hint { text-align: center; z-index: 1; }
.merchant-card {
  background: linear-gradient(135deg, #1a56db11, #6366f111);
  border: 1px solid #6366f122;
  border-radius: 14px;
  padding: 1.25rem 1.5rem;
}
.merchant-avatar {
  width: 52px; height: 52px;
  background: linear-gradient(135deg, #1a56db, #6366f1);
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.countdown-badge {
  display: inline-block; padding: 6px 16px;
  border-radius: 20px; font-size: 0.82rem; font-weight: 600;
}
.countdown-badge.info    { background: #dbeafe; color: #1e40af; }
.countdown-badge.warning { background: #fef9c3; color: #854d0e; }
.countdown-badge.danger  { background: #fee2e2; color: #991b1b; }
</style>