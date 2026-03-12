<template>
  <div class="d-flex flex-column gap-6">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-success">
            <i class="bi bi-qr-code text-success fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Terima Pembayaran QR</h3>
          <div class="text-muted fs-7">Generate QR Code untuk menerima pembayaran dari pengguna KyPay lain</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo KyPay</div>
          <div class="fw-bold text-success fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Form -->
    <div class="card" v-if="step === 1">
      <div class="card-header border-0 pt-6">
        <h3 class="card-title fw-bold">Detail Pembayaran</h3>
      </div>
      <div class="card-body pt-2">
        <div class="mb-6">
          <label class="form-label required fw-bold">Nominal Pembayaran</label>
          <div class="input-group">
            <span class="input-group-text fw-bold">Rp</span>
            <input
              v-model="amountInput"
              type="number"
              class="form-control form-control-solid fs-4 fw-bold"
              :class="amountError ? 'is-invalid' : ''"
              placeholder="0"
              min="1000"
              @input="amountError = ''"
            />
          </div>
          <div v-if="amountError" class="text-danger fs-8 mt-1">{{ amountError }}</div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <button
              v-for="n in quickAmounts" :key="n"
              class="btn btn-sm btn-light-success"
              @click="amountInput = n"
            >{{ formatRupiah(n) }}</button>
          </div>
        </div>
        <div class="mb-6">
          <label class="form-label fw-bold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
          <input v-model="description" type="text" class="form-control form-control-solid" placeholder="Contoh: Pembayaran makan siang" maxlength="255" />
        </div>
        <div class="d-flex justify-content-end">
          <button class="btn btn-success px-10" @click="generateQr" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="bi bi-qr-code me-2"></i>
            Generate QR Code
          </button>
        </div>
      </div>
    </div>

    <!-- Step 2: Tampilkan QR -->
    <div class="card" v-if="step === 2">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="cancelQr">
            <i class="bi bi-arrow-left"></i>
          </button>
          <h3 class="card-title fw-bold mb-0">QR Code Pembayaran</h3>
        </div>
      </div>
      <div class="card-body pt-4">
        <div class="row align-items-start">
          <!-- QR -->
          <div class="col-12 col-md-5 text-center mb-6 mb-md-0">
            <div class="qr-wrapper mx-auto mb-3">
              <div class="qr-header">
                <i class="bi bi-shield-check text-success me-1"></i>
                <span class="text-success fw-bold fs-8">KyPay QR</span>
              </div>
              <canvas ref="qrCanvas" class="qr-canvas"></canvas>
              <div class="qr-footer text-muted fs-9">Scan dengan aplikasi KyPay</div>
            </div>
            <div class="countdown-badge" :class="timeLeft <= 60 ? 'danger' : timeLeft <= 120 ? 'warning' : 'success'">
              <i class="bi bi-clock me-1"></i>
              Expired dalam <strong>{{ formatTime(timeLeft) }}</strong>
            </div>
          </div>

          <!-- Detail -->
          <div class="col-12 col-md-7">
            <div class="bg-light rounded-3 p-6 mb-4">
              <div class="d-flex justify-content-between mb-3">
                <span class="text-muted fs-7">Nominal</span>
                <span class="fw-bolder text-success fs-4">{{ formatRupiah(qrData?.amount) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-3" v-if="qrData?.description">
                <span class="text-muted fs-7">Keterangan</span>
                <span class="fw-bold">{{ qrData.description }}</span>
              </div>
              <div class="d-flex justify-content-between mb-3">
                <span class="text-muted fs-7">Dari</span>
                <span class="fw-bold">{{ qrData?.merchant?.name }}</span>
              </div>
              <div class="separator my-3"></div>
              <div class="d-flex justify-content-between">
                <span class="text-muted fs-7">Status</span>
                <span class="badge"
                  :class="qrStatus === 'paid' ? 'badge-light-success' : qrStatus === 'expired' ? 'badge-light-danger' : 'badge-light-warning'">
                  {{ qrStatus === 'paid' ? 'Dibayar' : qrStatus === 'expired' ? 'Expired' : 'Menunggu Pembayaran...' }}
                </span>
              </div>
            </div>

            <!-- Token untuk dibagikan manual -->
            <div class="mb-4">
              <label class="form-label fw-bold fs-8 text-muted">Token QR (bagikan jika tidak bisa scan)</label>
              <div class="input-group">
                <input :value="qrData?.qr_token" type="text" class="form-control form-control-solid fs-8" readonly />
                <button class="btn btn-light-success" @click="copyToken">
                  <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                </button>
              </div>
            </div>

            <div class="alert alert-info py-3 fs-8 mb-4">
              <i class="bi bi-info-circle me-2"></i>
              Tunjukkan QR ini kepada pembayar. QR otomatis expired setelah 5 menit.
            </div>

            <button class="btn btn-light-danger w-100" @click="cancelQr">
              <i class="bi bi-x-circle me-2"></i>Batalkan QR
            </button>
          </div>
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
        <h3 class="fw-bold mb-2">Pembayaran Diterima!</h3>
        <div class="fw-bolder text-success fs-3 mb-1">{{ formatRupiah(qrData?.amount) }}</div>
        <div class="text-muted fs-7 mb-5" v-if="qrData?.description">{{ qrData.description }}</div>
        <div class="d-flex justify-content-center gap-3 mt-6">
          <button class="btn btn-light-success" @click="resetForm">Generate QR Baru</button>
          <router-link to="/user/wallet" class="btn btn-success text-white">Lihat Wallet</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import ApiService from "@/core/services/ApiService";
import QRCode from "qrcode";

const step           = ref(1);
const loading        = ref(false);
const amountInput    = ref<number | "">("");
const description    = ref("");
const amountError    = ref("");
const currentBalance = ref(0);
const qrData         = ref<any>(null);
const qrStatus       = ref("pending");
const timeLeft       = ref(300);
const qrCanvas       = ref<HTMLCanvasElement | null>(null);
const copied         = ref(false);

const quickAmounts = [5000, 10000, 20000, 50000, 100000];

let pollingInterval:   ReturnType<typeof setInterval> | null = null;
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const formatRupiah = (val: any) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatTime   = (s: number) => `${String(Math.floor(s / 60)).padStart(2, "0")}:${String(s % 60).padStart(2, "0")}`;

const copyToken = async () => {
  if (!qrData.value?.qr_token) return;
  await navigator.clipboard.writeText(qrData.value.qr_token);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
};

const generateQr = async () => {
  amountError.value = "";
  if (!amountInput.value || Number(amountInput.value) < 1000) {
    amountError.value = "Minimal nominal Rp 1.000";
    return;
  }
  loading.value = true;
  try {
    const { data } = await ApiService.post("qr-payment/generate", {
      amount: Number(amountInput.value),
      description: description.value || null,
    });
    qrData.value   = data.data;
    qrStatus.value = "pending";
    timeLeft.value = 300;
    step.value = 2;

    await nextTick();
    if (qrCanvas.value) {
      await QRCode.toCanvas(qrCanvas.value, data.data.qr_token, {
        width: 220, margin: 2,
        color: { dark: "#1a1a2e", light: "#ffffff" },
      });
    }
    startPolling(data.data.qr_token);
    startCountdown();
  } catch (e: any) {
    amountError.value = e.response?.data?.message ?? "Gagal generate QR.";
  } finally {
    loading.value = false;
  }
};

const startPolling = (token: string) => {
  pollingInterval = setInterval(async () => {
    try {
      const { data } = await ApiService.get(`qr-payment/status/${token}`, "");
      qrStatus.value = data.data.status;
      if (data.data.status === "paid") {
        stopAll();
        currentBalance.value += Number(qrData.value?.amount ?? 0);
        step.value = 3;
      } else if (["expired", "cancelled"].includes(data.data.status)) {
        stopAll();
        resetForm();
      }
    } catch {}
  }, 2000);
};

const startCountdown = () => {
  countdownInterval = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) { stopAll(); qrStatus.value = "expired"; }
  }, 1000);
};

const stopAll = () => {
  if (pollingInterval)   { clearInterval(pollingInterval);   pollingInterval = null; }
  if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
};

const cancelQr = async () => {
  if (qrData.value?.qr_token) {
    try { await ApiService.delete(`qr-payment/${qrData.value.qr_token}/cancel`); } catch {}
  }
  stopAll();
  resetForm();
};

const resetForm = () => {
  step.value = 1; amountInput.value = ""; description.value = "";
  qrData.value = null; qrStatus.value = "pending"; timeLeft.value = 300;
};

onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    currentBalance.value = Number(data.data?.balance ?? 0);
  } catch {}
});

onUnmounted(() => stopAll());
</script>

<style scoped>
.qr-wrapper {
  background: #fff;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.10);
  display: inline-block;
  max-width: 280px;
}
.qr-header { text-align: center; margin-bottom: 12px; }
.qr-canvas  { display: block; margin: 0 auto; border-radius: 8px; }
.qr-footer  { text-align: center; margin-top: 10px; }
.countdown-badge {
  display: inline-block; padding: 6px 16px;
  border-radius: 20px; font-size: 0.82rem; font-weight: 600;
}
.countdown-badge.success { background: #dcfce7; color: #166534; }
.countdown-badge.warning { background: #fef9c3; color: #854d0e; }
.countdown-badge.danger  { background: #fee2e2; color: #991b1b; }
</style>