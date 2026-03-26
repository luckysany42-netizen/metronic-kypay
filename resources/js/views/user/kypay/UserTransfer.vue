<template>
  <div class="d-flex flex-column gap-7">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-info">
            <i class="bi bi-arrow-left-right text-info fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Transfer KyPay</h3>
          <div class="text-muted fs-7">Kirim atau terima saldo dari pengguna KyPay lainnya</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo tersedia</div>
          <div class="fw-bold text-info fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Tab Switcher -->
    <div class="card">
      <div class="card-body p-3">
        <div class="d-flex gap-2">
          <button
            class="btn flex-fill py-4 fw-bold fs-7 d-flex align-items-center justify-content-center gap-2"
            :class="activeTab === 'transfer' ? 'btn-info text-white' : 'btn-light text-muted'"
            @click="switchTab('transfer')"
          >
            <i class="bi bi-send-fill fs-6"></i>
            Kirim via Nomor Wallet
          </button>
          <button
            class="btn flex-fill py-4 fw-bold fs-7 d-flex align-items-center justify-content-center gap-2"
            :class="activeTab === 'qr' ? 'btn-success text-white' : 'btn-light text-muted'"
            @click="switchTab('qr')"
          >
            <i class="bi bi-qr-code fs-6"></i>
            Terima Pembayaran QR
          </button>
        </div>
      </div>
    </div>

    <!-- ===================== TAB: TRANSFER ===================== -->
    <template v-if="activeTab === 'transfer'">

      <!-- Step 1: Form Transfer -->
      <div class="card" v-if="transferStep === 1">
        <div class="card-body p-8">
          <div class="mb-6">
            <label class="form-label required fw-bold">Nomor Wallet Tujuan</label>
            <div class="input-group">
              <input
                v-model="receiverNumber"
                type="text"
                class="form-control form-control-solid"
                placeholder="Contoh: KP-2026-XXXXX"
                @keyup.enter="searchWallet"
              />
              <button class="btn btn-primary" @click="searchWallet" :disabled="searchLoading">
                <span v-if="searchLoading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-search"></i>
              </button>
            </div>
            <div class="form-text text-muted">Masukkan nomor wallet KyPay penerima</div>
          </div>

          <div v-if="receiver" class="notice d-flex bg-light-success rounded border-success border border-dashed p-5 mb-6">
            <div class="symbol symbol-45px me-4">
              <img
                v-if="getAvatarUrl(receiver.owner_avatar)"
                :src="getAvatarUrl(receiver.owner_avatar)"
                alt=""
                class="rounded-circle object-fit-cover w-45px h-45px"
              />
              <span v-else class="symbol-label bg-success text-white fw-bold fs-5">
                {{ receiver.owner_name?.charAt(0)?.toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="fw-bold text-success">Penerima Ditemukan</div>
              <div class="fw-bolder fs-5 text-gray-800">{{ receiver.owner_name }}</div>
              <div class="text-muted fs-8">{{ receiver.wallet_number }}</div>
              <div class="text-muted fs-8">{{ receiver.wallet_name }}</div>
            </div>
            <i class="bi bi-check-circle-fill text-success fs-2 ms-auto mt-1"></i>
          </div>

          <div v-if="searchError" class="alert alert-danger py-2 fs-7 mb-6">{{ searchError }}</div>

          <div v-if="receiver" class="row g-5">
            <div class="col-12 col-md-6">
              <label class="form-label required fw-bold">Jumlah Transfer (Rp)</label>
              <input
                v-model="transferForm.amount"
                type="number"
                class="form-control form-control-solid"
                placeholder="Minimal Rp 1.000"
                min="1000"
              />
              <div class="form-text">
                Saldo tersedia: <strong>{{ formatRupiah(currentBalance) }}</strong>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Catatan (opsional)</label>
              <input v-model="transferForm.note" type="text" class="form-control form-control-solid" placeholder="Contoh: bayar makan siang" />
            </div>
            <div class="col-12">
              <div class="alert alert-warning py-3 fs-7">
                <i class="bi bi-shield-exclamation me-2"></i>
                Transfer bersifat <strong>instan dan tidak dapat dibatalkan</strong>. Pastikan nomor wallet tujuan sudah benar.
              </div>
            </div>
          </div>

          <div v-if="transferError" class="alert alert-danger mt-4 py-2 fs-7">{{ transferError }}</div>

          <div class="mt-6 d-flex justify-content-end" v-if="receiver">
            <button class="btn btn-info text-white px-8" @click="transferStep = 2" :disabled="!transferForm.amount || Number(transferForm.amount) < 1000">
              Lanjut <i class="bi bi-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Konfirmasi Transfer -->
      <div class="card" v-if="transferStep === 2">
        <div class="card-header border-0 pt-6">
          <div class="d-flex align-items-center gap-3">
            <div class="symbol symbol-40px">
              <span class="symbol-label bg-light-info">
                <i class="bi bi-send-fill text-info fs-4"></i>
              </span>
            </div>
            <h3 class="card-title fw-bold mb-0">Konfirmasi Transfer</h3>
          </div>
        </div>
        <div class="card-body pt-4">
          <div class="bg-light rounded-2 p-6 mb-6">
            <div class="d-flex align-items-center gap-3 mb-5 pb-5 border-bottom">
              <div class="symbol symbol-50px">
                <img
                  v-if="getAvatarUrl(receiver?.owner_avatar)"
                  :src="getAvatarUrl(receiver?.owner_avatar)"
                  alt=""
                  class="rounded-circle object-fit-cover w-50px h-50px"
                />
                <span v-else class="symbol-label bg-info text-white fw-bold fs-4">
                  {{ receiver?.owner_name?.charAt(0)?.toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="fw-bold fs-5">{{ receiver?.owner_name }}</div>
                <div class="text-muted fs-8">{{ receiver?.wallet_number }}</div>
              </div>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Jumlah</span>
              <span class="fw-bold text-info fs-5">{{ formatRupiah(Number(transferForm.amount)) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Biaya Transfer</span>
              <span class="fw-bold text-success">Gratis</span>
            </div>
            <div class="separator my-3"></div>
            <div class="d-flex justify-content-between">
              <span class="fw-bold">Total Dipotong</span>
              <span class="fw-bolder text-danger fs-5">{{ formatRupiah(Number(transferForm.amount)) }}</span>
            </div>
            <div class="d-flex justify-content-between mt-2">
              <span class="text-muted fs-7">Saldo Setelah Transfer</span>
              <span class="fw-bold">{{ formatRupiah(currentBalance - Number(transferForm.amount)) }}</span>
            </div>
            <div v-if="transferForm.note" class="mt-3 pt-3 border-top">
              <span class="text-muted fs-8">Catatan: </span>
              <span class="fs-7">{{ transferForm.note }}</span>
            </div>
          </div>

          <div class="mb-5">
            <label class="form-label required fw-bold">Masukkan PIN KyPay</label>
            <input
              v-model="transferForm.pin"
              type="password"
              class="form-control form-control-solid text-center fw-bold fs-3"
              maxlength="6"
              placeholder="••••••"
            />
          </div>

          <div v-if="transferError" class="alert alert-danger py-2 fs-7">{{ transferError }}</div>

          <div class="d-flex justify-content-end gap-3">
            <button class="btn btn-light" @click="transferStep = 1">
              <i class="bi bi-arrow-left me-1"></i>Kembali
            </button>
            <button class="btn btn-info text-white px-8" @click="submitTransfer" :disabled="transferLoading || !transferForm.pin">
              <span v-if="transferLoading" class="spinner-border spinner-border-sm me-2"></span>
              Konfirmasi Transfer
            </button>
          </div>
        </div>
      </div>

      <!-- Step 3: Sukses Transfer -->
      <div class="card" v-if="transferStep === 3">
        <div class="card-body text-center py-15">
          <div class="symbol symbol-80px mx-auto mb-5">
            <span class="symbol-label bg-light-success">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            </span>
          </div>
          <h3 class="fw-bold mt-2 mb-2">Transfer Berhasil!</h3>
          <div class="text-muted fs-6 mb-1">Dikirim ke <strong>{{ receiver?.owner_name }}</strong></div>
          <div class="fw-bolder text-info fs-3 mb-2">{{ formatRupiah(Number(transferForm.amount)) }}</div>
          <div class="text-muted fs-8 mb-8">Ref: {{ successRef }}</div>
          <div class="d-flex justify-content-center gap-3 flex-wrap">
            <button class="btn btn-light-success" @click="showStruk = true">
              <i class="bi bi-receipt me-2"></i>Lihat Struk
            </button>
            <router-link :to="{ name: 'user-transactions' }" class="btn btn-light-info">Lihat Riwayat</router-link>
            <router-link :to="{ name: 'user-wallet' }" class="btn btn-info text-white">Kembali ke Wallet</router-link>
          </div>
        </div>
      </div>

    </template>

    <!-- ===================== TAB: TERIMA PEMBAYARAN QR ===================== -->
    <template v-if="activeTab === 'qr'">

      <!-- Step 1: Form QR -->
      <div class="card" v-if="qrStep === 1">
        <div class="card-header border-0 pt-6">
          <h3 class="card-title fw-bold">Detail Pembayaran</h3>
        </div>
        <div class="card-body pt-2">
          <div class="mb-6">
            <label class="form-label required fw-bold">Nominal Pembayaran</label>
            <div class="input-group">
              <span class="input-group-text fw-bold">Rp</span>
              <input
                v-model="amountInput" type="number"
                class="form-control form-control-solid fs-4 fw-bold"
                :class="amountError ? 'is-invalid' : ''"
                placeholder="0" min="1000" @input="amountError = ''"
              />
            </div>
            <div v-if="amountError" class="text-danger fs-8 mt-1">{{ amountError }}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
              <button v-for="n in quickAmounts" :key="n" class="btn btn-sm btn-light-success" @click="amountInput = n">
                {{ formatRupiah(n) }}
              </button>
            </div>
          </div>
          <div class="mb-6">
            <label class="form-label fw-bold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
            <input v-model="qrDescription" type="text" class="form-control form-control-solid" placeholder="Contoh: Pembayaran makan siang" maxlength="255" />
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-success px-10" @click="generateQr" :disabled="qrLoading">
              <span v-if="qrLoading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-qr-code me-2"></i>
              Generate QR Code
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Tampilkan QR -->
      <div class="card" v-if="qrStep === 2">
        <div class="card-header border-0 pt-6">
          <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light-secondary" @click="cancelQr"><i class="bi bi-arrow-left"></i></button>
            <h3 class="card-title fw-bold mb-0">QR Code Pembayaran</h3>
          </div>
        </div>
        <div class="card-body pt-4">
          <div class="row align-items-start">
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
                  <span class="badge" :class="qrStatus === 'paid' ? 'badge-light-success' : qrStatus === 'expired' ? 'badge-light-danger' : 'badge-light-warning'">
                    {{ qrStatus === 'paid' ? 'Dibayar' : qrStatus === 'expired' ? 'Expired' : 'Menunggu Pembayaran...' }}
                  </span>
                </div>
              </div>
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

      <!-- Step 3: Sukses QR -->
      <div class="card" v-if="qrStep === 3">
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
            <button class="btn btn-light-success" @click="resetQrForm">Generate QR Baru</button>
            <router-link to="/user/wallet" class="btn btn-success text-white">Lihat Wallet</router-link>
          </div>
        </div>
      </div>

    </template>

  </div>

  <!-- ===== MODAL STRUK TRANSFER ===== -->
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="showStruk" class="struk-overlay" @click.self="showStruk = false">
        <div class="struk-container">

          <div class="d-flex justify-content-between align-items-center mb-4 px-1">
            <h5 class="fw-bold mb-0">Struk Transfer</h5>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-light-primary" @click="downloadPdf" :disabled="pdfLoading">
                <span v-if="pdfLoading" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-download me-1"></i>
                Download PDF
              </button>
              <button class="btn btn-sm btn-light" @click="showStruk = false">
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>

          <div ref="strukRef" class="struk-paper">
            <div class="struk-header">
              <div class="struk-logo">
                <i class="bi bi-wallet2" style="font-size:2rem; color:#f59e0b;"></i>
              </div>
              <div class="struk-brand">KyPay</div>
              <div class="struk-subtitle">Struk Transfer Digital</div>
              <div class="struk-date">{{ strukDate }}</div>
            </div>

            <div class="struk-divider"></div>

            <div class="struk-status">
              <i class="bi bi-check-circle-fill" style="color:#16a34a; font-size:1.5rem;"></i>
              <span>TRANSFER BERHASIL</span>
            </div>

            <div class="struk-divider"></div>

            <div class="struk-row">
              <span class="struk-label">Penerima</span>
              <span class="struk-value">{{ receiver?.owner_name }}</span>
            </div>
            <div class="struk-row">
              <span class="struk-label">No. Wallet</span>
              <span class="struk-value">{{ receiver?.wallet_number }}</span>
            </div>
            <div v-if="transferForm.note" class="struk-row">
              <span class="struk-label">Catatan</span>
              <span class="struk-value">{{ transferForm.note }}</span>
            </div>
            <div class="struk-row">
              <span class="struk-label">Biaya Admin</span>
              <span class="struk-value" style="color:#16a34a;">Gratis</span>
            </div>

            <div class="struk-divider"></div>

            <div class="struk-row struk-total">
              <span>Total Transfer</span>
              <span>{{ formatRupiah(Number(transferForm.amount)) }}</span>
            </div>

            <div class="struk-divider"></div>

            <div class="struk-ref">
              <div class="struk-label" style="text-align:center;">No. Referensi</div>
              <div class="struk-ref-number">{{ successRef || '-' }}</div>
            </div>

            <div class="struk-divider"></div>
            <div class="struk-footer">
              <div>Terima kasih telah menggunakan KyPay</div>
              <div style="margin-top:4px; font-size:0.7rem; color:#9ca3af;">Struk ini merupakan bukti transaksi yang sah</div>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import ApiService from "@/core/services/ApiService";
import QRCode from "qrcode";

defineOptions({ name: 'UserTransfer' });

// ── Shared ───────────────────────────────────────────────
const currentBalance = ref(0);
const activeTab      = ref<"transfer" | "qr">("transfer");

const formatRupiah = (val: any) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatTime   = (s: number) => `${String(Math.floor(s / 60)).padStart(2, "0")}:${String(s % 60).padStart(2, "0")}`;

const getAvatarUrl = (avatar: string | null | undefined): string | null => {
  if (!avatar) return null;
  if (avatar.startsWith("http")) return avatar;
  const base = (import.meta.env.VITE_APP_API_URL ?? "").replace("/api", "");
  return `${base}/storage/${avatar}`;
};

const switchTab = (tab: "transfer" | "qr") => { activeTab.value = tab; };

// ── Struk state ──────────────────────────────────────────
const showStruk  = ref(false);
const pdfLoading = ref(false);
const strukRef   = ref<HTMLElement | null>(null);

const strukDate = computed(() =>
  new Date().toLocaleString("id-ID", {
    day: "2-digit", month: "long", year: "numeric",
    hour: "2-digit", minute: "2-digit", second: "2-digit",
  })
);

const downloadPdf = async () => {
  if (!strukRef.value) return;
  pdfLoading.value = true;
  try {
    if (!(window as any).html2canvas) {
      await new Promise<void>((resolve, reject) => {
        const s = document.createElement("script");
        s.src = "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
        s.onload = () => resolve(); s.onerror = () => reject();
        document.head.appendChild(s);
      });
    }
    if (!(window as any).jspdf) {
      await new Promise<void>((resolve, reject) => {
        const s = document.createElement("script");
        s.src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js";
        s.onload = () => resolve(); s.onerror = () => reject();
        document.head.appendChild(s);
      });
    }
    const html2canvas = (window as any).html2canvas;
    const { jsPDF }   = (window as any).jspdf;
    const canvas  = await html2canvas(strukRef.value, { scale: 2, useCORS: true, backgroundColor: "#ffffff" });
    const imgData = canvas.toDataURL("image/png");
    const pdf     = new jsPDF({ orientation: "portrait", unit: "mm", format: "a5" });
    const pdfW    = pdf.internal.pageSize.getWidth();
    const pdfH    = (canvas.height * pdfW) / canvas.width;
    pdf.addImage(imgData, "PNG", 0, 0, pdfW, pdfH);
    pdf.save(`struk-transfer-${successRef.value || Date.now()}.pdf`);
  } catch {
    alert("Gagal generate PDF. Coba lagi.");
  } finally {
    pdfLoading.value = false;
  }
};

// ── Transfer State ────────────────────────────────────────
const transferStep    = ref(1);
const transferLoading = ref(false);
const searchLoading   = ref(false);
const searchError     = ref("");
const transferError   = ref("");
const successRef      = ref("");
const receiverNumber  = ref("");
const receiver        = ref<any>(null);
const transferForm    = ref({ amount: "", note: "", pin: "" });

const searchWallet = async () => {
  searchError.value = "";
  receiver.value = null;
  if (!receiverNumber.value.trim()) return;
  searchLoading.value = true;
  try {
    const { data } = await ApiService.get("wallet/find", receiverNumber.value.trim());
    receiver.value = data.data ?? data;
  } catch (e: any) {
    searchError.value =
      e.response?.data?.errors?.wallet ??
      e.response?.data?.message ??
      "Wallet tidak ditemukan.";
  } finally {
    searchLoading.value = false;
  }
};

const submitTransfer = async () => {
  transferError.value = "";
  if (!transferForm.value.pin || transferForm.value.pin.length !== 6) {
    transferError.value = "PIN harus 6 digit."; return;
  }
  transferLoading.value = true;
  try {
    const { data } = await ApiService.post("transfer", {
      receiver_wallet_number: receiver.value.wallet_number,
      amount: transferForm.value.amount,
      pin: transferForm.value.pin,
      note: transferForm.value.note,
    });
    successRef.value = data.data?.reference_number ?? data.reference_number ?? "-";
    currentBalance.value = currentBalance.value - Number(transferForm.value.amount);
    transferStep.value = 3;
  } catch (e: any) {
    transferError.value =
      e.response?.data?.errors?.pin ??
      e.response?.data?.message ??
      "Transfer gagal.";
  } finally {
    transferLoading.value = false;
  }
};

// ── QR State ──────────────────────────────────────────────
const qrStep        = ref(1);
const qrLoading     = ref(false);
const amountInput   = ref<number | "">("");
const qrDescription = ref("");
const amountError   = ref("");
const qrData        = ref<any>(null);
const qrStatus      = ref("pending");
const timeLeft      = ref(300);
const qrCanvas      = ref<HTMLCanvasElement | null>(null);
const copied        = ref(false);
const quickAmounts  = [5000, 10000, 20000, 50000, 100000];

let pollingInterval:   ReturnType<typeof setInterval> | null = null;
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const copyToken = async () => {
  if (!qrData.value?.qr_token) return;
  await navigator.clipboard.writeText(qrData.value.qr_token);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
};

const generateQr = async () => {
  amountError.value = "";
  if (!amountInput.value || Number(amountInput.value) < 1000) {
    amountError.value = "Minimal nominal Rp 1.000"; return;
  }
  qrLoading.value = true;
  try {
    const { data } = await ApiService.post("qr-payment/generate", {
      amount: Number(amountInput.value),
      description: qrDescription.value || null,
    });
    qrData.value = data.data; qrStatus.value = "pending";
    timeLeft.value = 300; qrStep.value = 2;
    await nextTick();
    if (qrCanvas.value) {
      await QRCode.toCanvas(qrCanvas.value, data.data.qr_token, {
        width: 220, margin: 2, color: { dark: "#1a1a2e", light: "#ffffff" },
      });
    }
    startPolling(data.data.qr_token);
    startCountdown();
  } catch (e: any) {
    amountError.value = e.response?.data?.message ?? "Gagal generate QR.";
  } finally {
    qrLoading.value = false;
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
        qrStep.value = 3;
      } else if (["expired", "cancelled"].includes(data.data.status)) {
        stopAll(); resetQrForm();
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
  stopAll(); resetQrForm();
};

const resetQrForm = () => {
  qrStep.value = 1; amountInput.value = ""; qrDescription.value = "";
  qrData.value = null; qrStatus.value = "pending"; timeLeft.value = 300;
};

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    const walletData = data.wallet ?? data.data ?? data;
    currentBalance.value = Number(walletData?.balance ?? 0);
  } catch {}
});

onUnmounted(() => stopAll());
</script>

<style scoped>
.qr-wrapper {
  background: #fff; border-radius: 20px; padding: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.10); display: inline-block; max-width: 280px;
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

/* Modal struk */
.struk-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px); z-index: 9999;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.struk-container {
  width: 100%; max-width: 400px; background: #f8fafc;
  border-radius: 16px; padding: 24px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.4);
  max-height: 90vh; overflow-y: auto;
}
.struk-paper {
  background: #fff; border-radius: 8px; padding: 24px 20px;
  font-family: 'Courier New', monospace;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.struk-header { text-align: center; margin-bottom: 8px; }
.struk-logo { margin-bottom: 4px; }
.struk-brand { font-size: 1.5rem; font-weight: 900; color: #1e293b; letter-spacing: -1px; }
.struk-subtitle { font-size: 0.72rem; color: #64748b; margin-top: 2px; }
.struk-date { font-size: 0.7rem; color: #94a3b8; margin-top: 6px; }
.struk-divider { border: none; border-top: 2px dashed #e2e8f0; margin: 14px 0; }
.struk-status {
  display: flex; align-items: center; justify-content: center;
  gap: 8px; font-weight: 800; font-size: 0.85rem;
  color: #16a34a; letter-spacing: 0.05em; margin: 4px 0;
}
.struk-row {
  display: flex; justify-content: space-between; align-items: flex-start;
  gap: 12px; margin-bottom: 8px; font-size: 0.78rem;
}
.struk-label { color: #64748b; flex-shrink: 0; }
.struk-value { color: #1e293b; font-weight: 600; text-align: right; word-break: break-all; }
.struk-total { font-weight: 800; font-size: 1rem; color: #1e293b; padding: 4px 0; }
.struk-ref { text-align: center; margin: 4px 0; }
.struk-ref-number { font-size: 0.75rem; font-weight: 700; color: #475569; margin-top: 4px; letter-spacing: 0.05em; }
.struk-footer { text-align: center; color: #64748b; font-size: 0.72rem; padding-top: 4px; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>