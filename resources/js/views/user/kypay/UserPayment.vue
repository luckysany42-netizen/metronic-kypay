<template>
  <div class="d-flex flex-column gap-7">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-warning">
            <i class="bi bi-grid-fill text-warning fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Bayar & Beli</h3>
          <div class="text-muted fs-7">Pulsa, data, token listrik, BPJS & voucher game</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo KyPay</div>
          <div class="fw-bold text-warning fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- ===== STEP 1: Pilih Kategori & Produk ===== -->
    <template v-if="step === 1">
      <div class="row g-4">
        <div class="col-6 col-md-4 col-lg-2halfx" v-for="cat in categories" :key="cat.key">
          <div
            class="card card-hover text-center p-5 cursor-pointer"
            :class="selectedCategory === cat.key ? 'border border-warning bg-light-warning' : ''"
            @click="selectCategory(cat.key)"
          >
            <div class="symbol symbol-45px mx-auto mb-3">
              <span class="symbol-label" :style="{ backgroundColor: cat.color + '22' }">
                <i class="bi fs-2" :class="cat.icon" :style="{ color: cat.color }"></i>
              </span>
            </div>
            <div class="fw-bold fs-7 text-gray-800">{{ cat.label }}</div>
          </div>
        </div>
      </div>

      <div class="card" v-if="selectedCategory">
        <div class="card-header border-0 pt-6">
          <h3 class="card-title fw-bold">{{ currentCategoryLabel }}</h3>
        </div>
        <div class="card-body pt-0">
          <div class="mb-6">
            <label class="form-label required fw-bold">{{ targetLabel }}</label>
            <input v-model="targetNumber" type="text" class="form-control form-control-solid"
              :class="targetError ? 'is-invalid' : ''" :placeholder="targetPlaceholder" @input="targetError = ''" />
            <div v-if="targetError" class="invalid-feedback d-block fs-8 mt-1">
              <i class="bi bi-exclamation-circle me-1"></i>{{ targetError }}
            </div>
            <div v-else class="form-text text-muted fs-8">{{ targetHint }}</div>
          </div>

          <div class="d-flex gap-2 flex-wrap mb-5" v-if="providers.length > 1">
            <button v-for="p in ['Semua', ...providers]" :key="p" class="btn btn-sm"
              :class="selectedProvider === p ? 'btn-warning' : 'btn-light'" @click="selectedProvider = p">
              {{ p }}
            </button>
          </div>

          <div v-if="loadingProducts" class="text-center py-10">
            <span class="spinner-border text-warning"></span>
          </div>

          <div v-else class="row g-4">
            <div v-for="product in filteredProducts" :key="product.code" class="col-12 col-md-6 col-lg-4" @click="selectProduct(product)">
              <div class="border rounded-2 p-4 cursor-pointer"
                :class="selectedProduct?.code === product.code ? 'border-warning bg-light-warning' : 'border-gray-200'"
                style="transition: all 0.2s">
                <div class="d-flex align-items-center gap-3">
                  <div class="symbol symbol-35px">
                    <span class="symbol-label" :style="{ backgroundColor: product.color + '22' }">
                      <i class="bi" :class="product.icon" :style="{ color: product.color }"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold text-gray-800 fs-7">{{ product.name }}</div>
                    <div class="text-muted fs-8">{{ product.provider }}</div>
                  </div>
                  <div class="text-end">
                    <div class="fw-bolder text-warning">{{ formatRupiah(product.price) }}</div>
                    <i v-if="selectedProduct?.code === product.code" class="bi bi-check-circle-fill text-warning fs-6"></i>
                  </div>
                </div>
                <div class="text-muted fs-9 mt-2">{{ product.description }}</div>
              </div>
            </div>
          </div>

          <div class="mt-6 d-flex justify-content-end" v-if="selectedProduct">
            <button class="btn btn-warning text-white px-8" :disabled="!targetNumber.trim()" @click="goToPaymentMethod">
              Lanjut <i class="bi bi-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== STEP 2: Pilih Metode Pembayaran ===== -->
    <div class="card" v-if="step === 2">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 1"><i class="bi bi-arrow-left"></i></button>
          <h3 class="card-title fw-bold mb-0">Pilih Metode Pembayaran</h3>
        </div>
      </div>
      <div class="card-body pt-2">

        <!-- Ringkasan produk -->
        <div class="bg-light rounded-2 p-4 mb-6 d-flex align-items-center gap-4">
          <div class="symbol symbol-40px">
            <span class="symbol-label" :style="{ backgroundColor: selectedProduct?.color + '22' }">
              <i class="bi" :class="selectedProduct?.icon" :style="{ color: selectedProduct?.color }"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <div class="fw-bold fs-7">{{ selectedProduct?.name }}</div>
            <div class="text-muted fs-8">{{ targetNumber }}</div>
          </div>
          <div class="fw-bolder text-warning fs-5">{{ formatRupiah(selectedProduct?.price) }}</div>
        </div>

        <!-- Opsi metode -->
        <div class="d-flex flex-column gap-3">
          <!-- Saldo KyPay -->
          <div class="payment-method-card" :class="paymentMethod === 'saldo' ? 'selected' : ''" @click="paymentMethod = 'saldo'">
            <div class="method-icon bg-light-warning">
              <i class="bi bi-wallet2 text-warning fs-3"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold">Saldo KyPay</div>
              <div class="text-muted fs-8">Saldo kamu: {{ formatRupiah(currentBalance) }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span v-if="currentBalance < (selectedProduct?.price ?? 0)" class="badge badge-light-danger fs-9">Tidak cukup</span>
              <div class="method-radio" :class="paymentMethod === 'saldo' ? 'active' : ''"></div>
            </div>
          </div>

          <!-- QR Code -->
          <div class="payment-method-card" :class="paymentMethod === 'qr' ? 'selected-qr' : ''" @click="paymentMethod = 'qr'">
            <div class="method-icon bg-light-primary">
              <i class="bi bi-qr-code text-primary fs-3"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold d-flex align-items-center gap-2">
                Bayar via QR Code
                <span class="badge badge-light-primary fs-9">QRIS</span>
              </div>
              <div class="text-muted fs-8">Scan QR dari kasir / mesin EDC merchant</div>
            </div>
            <div class="method-radio" :class="paymentMethod === 'qr' ? 'active-qr' : ''"></div>
          </div>
        </div>

        <div class="mt-6 d-flex justify-content-end">
          <button class="btn btn-warning text-white px-8" :disabled="!paymentMethod" @click="goToConfirm">
            Lanjut <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ===== STEP 3a: Konfirmasi Saldo ===== -->
    <div class="card" v-if="step === 3 && paymentMethod === 'saldo'">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 2"><i class="bi bi-arrow-left"></i></button>
          <h3 class="card-title fw-bold mb-0">Konfirmasi Pembayaran</h3>
        </div>
      </div>
      <div class="card-body pt-4">
        <div class="bg-light rounded-2 p-6 mb-6">
          <div class="d-flex align-items-center gap-4 mb-5 pb-5 border-bottom">
            <div class="symbol symbol-50px">
              <span class="symbol-label" :style="{ backgroundColor: selectedProduct?.color + '22' }">
                <i class="bi fs-2" :class="selectedProduct?.icon" :style="{ color: selectedProduct?.color }"></i>
              </span>
            </div>
            <div>
              <div class="fw-bolder fs-5">{{ selectedProduct?.name }}</div>
              <div class="text-muted fs-7">{{ selectedProduct?.provider }}</div>
            </div>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">{{ targetLabel }}</span>
            <span class="fw-bold">{{ targetNumber }}</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Metode Bayar</span>
            <span class="fw-bold"><i class="bi bi-wallet2 me-1 text-warning"></i>Saldo KyPay</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Harga</span>
            <span class="fw-bold text-warning fs-5">{{ formatRupiah(selectedProduct?.price) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Biaya Admin</span>
            <span class="fw-bold text-success">Gratis</span>
          </div>
          <div class="separator my-3"></div>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Total Bayar</span>
            <span class="fw-bolder text-danger fs-5">{{ formatRupiah(selectedProduct?.price) }}</span>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-muted fs-7">Saldo Setelah Bayar</span>
            <span class="fw-bold">{{ formatRupiah(currentBalance - (selectedProduct?.price ?? 0)) }}</span>
          </div>
        </div>

        <div class="mb-5">
          <label class="form-label required fw-bold">Masukkan PIN KyPay</label>
          <input v-model="pin" type="password" class="form-control form-control-solid text-center fw-bold fs-3" maxlength="6" placeholder="••••••" autofocus />
        </div>

        <div v-if="errorMsg" class="alert alert-danger py-2 fs-7">{{ errorMsg }}</div>
        <div class="d-flex justify-content-end gap-3">
          <button class="btn btn-light" @click="step = 2">Kembali</button>
          <button class="btn btn-warning text-white px-8" @click="submitPayment" :disabled="loading || !pin">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Bayar Sekarang
          </button>
        </div>
      </div>
    </div>

    <!-- ===== STEP 3b: Bayar via QR ===== -->
    <div class="card" v-if="step === 3 && paymentMethod === 'qr'">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 2"><i class="bi bi-arrow-left"></i></button>
          <h3 class="card-title fw-bold mb-0">Bayar via QR Code</h3>
        </div>
      </div>
      <div class="card-body pt-4">

        <!-- Ringkasan produk -->
        <div class="bg-light rounded-2 p-4 mb-6 d-flex align-items-center gap-4">
          <div class="symbol symbol-40px">
            <span class="symbol-label" :style="{ backgroundColor: selectedProduct?.color + '22' }">
              <i class="bi" :class="selectedProduct?.icon" :style="{ color: selectedProduct?.color }"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <div class="fw-bold fs-7">{{ selectedProduct?.name }}</div>
            <div class="text-muted fs-8">{{ targetNumber }}</div>
          </div>
          <div class="fw-bolder text-danger fs-5">{{ formatRupiah(selectedProduct?.price) }}</div>
        </div>

        <!-- Belum scan -->
        <template v-if="!qrScanned">
          <div class="text-center mb-5">
            <div class="text-muted fs-7 mb-4">Arahkan kamera ke QR Code kasir / mesin EDC</div>
            <div class="qr-scanner-box mb-5" @click="simulateQrScan">
              <div class="qr-frame">
                <div class="qr-corner tl"></div>
                <div class="qr-corner tr"></div>
                <div class="qr-corner bl"></div>
                <div class="qr-corner br"></div>
                <div class="qr-scan-line"></div>
                <div class="qr-hint">
                  <i class="bi bi-qr-code-scan fs-1 text-white opacity-75 d-block mb-2"></i>
                  <span class="text-white fs-8 fw-semibold opacity-75">Tap untuk simulasi scan QR</span>
                </div>
              </div>
            </div>
            <div class="separator separator-dashed my-4"></div>
            <div class="text-muted fs-8 mb-3">Atau masukkan kode QR merchant secara manual</div>
            <div class="d-flex gap-2 justify-content-center" style="max-width: 400px; margin: 0 auto;">
              <input v-model="merchantCode" type="text" class="form-control form-control-solid" placeholder="Kode merchant / QRIS" />
              <button class="btn btn-light-primary px-5" @click="manualQrScan">Cek</button>
            </div>
          </div>
        </template>

        <!-- Setelah scan -->
        <template v-if="qrScanned">
          <div class="qr-merchant-result mb-6">
            <div class="d-flex align-items-center gap-4 mb-4">
              <div class="qr-merchant-icon">
                <i class="bi bi-shop fs-3"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-bolder fs-5">{{ scannedMerchant.name }}</div>
                <div class="text-muted fs-8">{{ scannedMerchant.sub }}</div>
              </div>
              <span class="badge badge-light-success fs-8">
                <i class="bi bi-check-circle me-1"></i>Terverifikasi
              </span>
            </div>
            <div class="separator my-4"></div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Produk</span>
              <span class="fw-bold">{{ selectedProduct?.name }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Untuk</span>
              <span class="fw-bold">{{ targetNumber }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Metode</span>
              <span class="fw-bold"><i class="bi bi-qr-code me-1 text-primary"></i>QR Code / QRIS</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-muted fs-7">Total Bayar</span>
              <span class="fw-bolder text-danger fs-5">{{ formatRupiah(selectedProduct?.price) }}</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted fs-7">Saldo Setelah</span>
              <span class="fw-bold">{{ formatRupiah(currentBalance - (selectedProduct?.price ?? 0)) }}</span>
            </div>
          </div>

          <div class="mb-5">
            <label class="form-label required fw-bold">Masukkan PIN KyPay</label>
            <input v-model="pin" type="password" class="form-control form-control-solid text-center fw-bold fs-3" maxlength="6" placeholder="••••••" />
          </div>

          <div v-if="errorMsg" class="alert alert-danger py-2 fs-7">{{ errorMsg }}</div>
          <div class="d-flex gap-3">
            <button class="btn btn-light flex-grow-1" @click="qrScanned = false; merchantCode = ''">Scan Ulang</button>
            <button class="btn btn-primary flex-grow-1" @click="submitQrPayment" :disabled="loading || !pin">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              Bayar via QR
            </button>
          </div>
        </template>

      </div>
    </div>

    <!-- ===== STEP 4: Sukses ===== -->
    <div class="card" v-if="step === 4">
      <div class="card-body text-center py-15">
        <div class="symbol symbol-80px mx-auto mb-5">
          <span class="symbol-label bg-light-success">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
          </span>
        </div>
        <h3 class="fw-bold mb-2">Pembayaran Berhasil!</h3>
        <div class="text-muted fs-6 mb-1">{{ successData?.product_name }}</div>
        <div class="text-muted fs-7 mb-3">untuk <strong>{{ successData?.target_number }}</strong></div>
        <div class="mb-4">
          <span class="badge fs-8" :class="paymentMethod === 'qr' ? 'badge-light-primary' : 'badge-light-warning'">
            <i class="bi me-1" :class="paymentMethod === 'qr' ? 'bi-qr-code' : 'bi-wallet2'"></i>
            {{ paymentMethod === 'qr' ? 'Dibayar via QR Code / QRIS' : 'Dibayar via Saldo KyPay' }}
          </span>
        </div>
        <div v-if="['token_listrik','voucher_game'].includes(successData?.category)"
          class="bg-light-warning rounded-2 p-5 mx-auto mb-5" style="max-width: 320px">
          <div class="text-muted fs-8 mb-1">{{ successData?.category === 'token_listrik' ? 'Token Listrik' : 'Kode Voucher' }}</div>
          <div class="fw-bolder fs-3 text-warning">{{ successData?.result_code }}</div>
          <div class="text-muted fs-9 mt-1">Simpan kode ini dengan baik</div>
        </div>
        <div class="text-muted fs-8 mb-8">Ref: {{ successData?.transaction_number }}</div>
        <div class="d-flex justify-content-center gap-3">
          <button class="btn btn-light-warning" @click="resetForm">Bayar Lagi</button>
          <router-link :to="{ name: 'user-wallet' }" class="btn btn-warning text-white">Kembali ke Wallet</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const step              = ref(1);
const loading           = ref(false);
const loadingProducts   = ref(false);
const errorMsg          = ref("");
const currentBalance    = ref(0);
const targetNumber      = ref("");
const pin               = ref("");
const selectedCategory  = ref("");
const selectedProvider  = ref("Semua");
const selectedProduct   = ref<any>(null);
const products          = ref<any[]>([]);
const successData       = ref<any>(null);
const targetError       = ref("");
const paymentMethod     = ref<'saldo' | 'qr' | ''>("");
const qrScanned         = ref(false);
const merchantCode      = ref("");
const scannedMerchant   = ref({ name: "", sub: "" });

const categories = [
  { key: "pulsa",         label: "Pulsa",          icon: "bi-phone-fill",       color: "#e74c3c" },
  { key: "paket_data",    label: "Paket Data",     icon: "bi-wifi",             color: "#3498db" },
  { key: "token_listrik", label: "Token Listrik",  icon: "bi-lightning-fill",   color: "#f1c40f" },
  { key: "bpjs",          label: "BPJS",           icon: "bi-heart-pulse-fill", color: "#27ae60" },
  { key: "voucher_game",  label: "Voucher Game",   icon: "bi-controller",       color: "#9b59b6" },
];

const targetLabels: Record<string, string> = {
  pulsa: "Nomor HP Tujuan", paket_data: "Nomor HP Tujuan",
  token_listrik: "Nomor Meter / ID PLN", bpjs: "Nomor Peserta BPJS", voucher_game: "ID Game",
};
const targetPlaceholders: Record<string, string> = {
  pulsa: "08123456789", paket_data: "08123456789",
  token_listrik: "12345678901", bpjs: "0001234567890", voucher_game: "ID Game kamu",
};
const targetHints: Record<string, string> = {
  pulsa: "Format: 08xxxxxxxxxx", paket_data: "Format: 08xxxxxxxxxx",
  token_listrik: "11–13 digit angka", bpjs: "13 digit angka", voucher_game: "3–30 karakter",
};

const targetLabel        = computed(() => targetLabels[selectedCategory.value] ?? "Nomor Tujuan");
const targetPlaceholder  = computed(() => targetPlaceholders[selectedCategory.value] ?? "");
const targetHint         = computed(() => targetHints[selectedCategory.value] ?? "");
const currentCategoryLabel = computed(() => categories.find(c => c.key === selectedCategory.value)?.label ?? "");
const providers = computed(() => [...new Set(products.value.map((p: any) => p.provider))] as string[]);
const filteredProducts = computed(() =>
  selectedProvider.value === "Semua" ? products.value : products.value.filter((p: any) => p.provider === selectedProvider.value)
);

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");

const selectCategory = async (key: string) => {
  selectedCategory.value = key; selectedProduct.value = null;
  selectedProvider.value = "Semua"; targetNumber.value = "";
  loadingProducts.value = true;
  try {
    const { data } = await ApiService.query("payment/products", { params: { category: key } });
    products.value = data.data ?? [];
  } catch { products.value = []; }
  finally { loadingProducts.value = false; }
};

const selectProduct = (product: any) => { selectedProduct.value = product; };

const validateTarget = (category: string, value: string): string => {
  const v = value.trim();
  if (["pulsa","paket_data"].includes(category) && !/^08\d{8,11}$/.test(v)) return "Nomor HP harus diawali 08, 10–13 digit";
  if (category === "token_listrik" && !/^\d{11,13}$/.test(v)) return "Nomor meter harus 11–13 digit angka";
  if (category === "bpjs" && !/^\d{13}$/.test(v)) return "Nomor peserta BPJS harus 13 digit";
  if (category === "voucher_game" && (v.length < 3 || v.length > 30)) return "ID Game harus 3–30 karakter";
  return "";
};

const goToPaymentMethod = () => {
  const err = validateTarget(selectedCategory.value, targetNumber.value);
  if (err) { targetError.value = err; return; }
  paymentMethod.value = ""; pin.value = ""; errorMsg.value = ""; step.value = 2;
};

const goToConfirm = () => {
  if (!paymentMethod.value) return;
  qrScanned.value = false; merchantCode.value = ""; pin.value = ""; errorMsg.value = ""; step.value = 3;
};

const dummyMerchants = [
  { name: "Alfamart KyPay Partner", sub: "ID Merchant: AF-2024-01928" },
  { name: "Indomaret Digital", sub: "ID Merchant: IM-2024-00481" },
  { name: "Tokopedia QRIS", sub: "ID Merchant: TKP-2024-88192" },
  { name: "GoPay Merchant", sub: "ID Merchant: GP-2024-55031" },
  { name: "Warung Sembako Bu Sari", sub: "ID Merchant: WRG-2024-00123" },
];

const simulateQrScan = () => {
  const m = dummyMerchants[Math.floor(Math.random() * dummyMerchants.length)];
  scannedMerchant.value = m;
  qrScanned.value = true;
};

const manualQrScan = () => {
  if (!merchantCode.value.trim()) return;
  scannedMerchant.value = { name: "Merchant " + merchantCode.value.slice(0, 12), sub: "ID: " + merchantCode.value };
  qrScanned.value = true;
};

const submitPayment = async () => {
  errorMsg.value = "";
  if (!pin.value || pin.value.length !== 6) { errorMsg.value = "PIN harus 6 digit."; return; }
  loading.value = true;
  try {
    const { data } = await ApiService.post("payment", {
      product_code: selectedProduct.value.code, target_number: targetNumber.value, pin: pin.value, note: null,
    });
    successData.value = data.data;
    currentBalance.value -= selectedProduct.value.price;
    step.value = 4;
  } catch (e: any) {
    errorMsg.value = e.response?.data?.errors?.pin ?? e.response?.data?.message ?? "Pembayaran gagal.";
  } finally { loading.value = false; }
};

const submitQrPayment = async () => {
  errorMsg.value = "";
  if (!pin.value || pin.value.length !== 6) { errorMsg.value = "PIN harus 6 digit."; return; }
  loading.value = true;
  try {
    const { data } = await ApiService.post("payment", {
      product_code:  selectedProduct.value.code,
      target_number: targetNumber.value,
      pin:           pin.value,
      note:          "QR - " + scannedMerchant.value.name,
    });
    successData.value = data.data;
    currentBalance.value -= selectedProduct.value.price;
    step.value = 4;
  } catch (e: any) {
    errorMsg.value = e.response?.data?.errors?.pin ?? e.response?.data?.message ?? "Pembayaran gagal.";
  } finally { loading.value = false; }
};

const resetForm = () => {
  step.value = 1; selectedProduct.value = null; selectedCategory.value = ""; targetNumber.value = "";
  pin.value = ""; errorMsg.value = ""; successData.value = null; paymentMethod.value = "";
  qrScanned.value = false; merchantCode.value = "";
};

onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    const w = data.wallet ?? data.data ?? data;
    currentBalance.value = Number(w?.balance ?? 0);
  } catch {}
});
</script>

<style scoped>
.payment-method-card {
  display: flex; align-items: center; gap: 1rem;
  padding: 1.25rem 1.5rem;
  border: 2px solid var(--bs-border-color, #e9ecef);
  border-radius: 14px; cursor: pointer; transition: all 0.2s;
}
.payment-method-card:hover { border-color: #f59e0b44; background: #fef9c322; }
.payment-method-card.selected { border-color: #f59e0b; background: #fef9c333; }
.payment-method-card.selected-qr { border-color: #3b82f6; background: #eff6ff55; }

.method-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.method-radio { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; flex-shrink: 0; transition: all 0.2s; }
.method-radio.active { border-color: #f59e0b; background: #f59e0b; box-shadow: inset 0 0 0 4px #fff; }
.method-radio.active-qr { border-color: #3b82f6; background: #3b82f6; box-shadow: inset 0 0 0 4px #fff; }

.qr-scanner-box { background: #0f172a; border-radius: 16px; padding: 2rem; cursor: pointer; transition: opacity 0.2s; max-width: 300px; margin: 0 auto; }
.qr-scanner-box:hover { opacity: 0.9; }
.qr-frame { position: relative; width: 180px; height: 180px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
.qr-corner { position: absolute; width: 26px; height: 26px; border-color: #60a5fa; border-style: solid; }
.qr-corner.tl { top: 0; left: 0; border-width: 3px 0 0 3px; }
.qr-corner.tr { top: 0; right: 0; border-width: 3px 3px 0 0; }
.qr-corner.bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; }
.qr-corner.br { bottom: 0; right: 0; border-width: 0 3px 3px 0; }
.qr-scan-line { position: absolute; left: 8px; right: 8px; height: 2px; background: linear-gradient(90deg, transparent, #60a5fa, transparent); animation: scan 2s ease-in-out infinite; }
@keyframes scan { 0% { top: 8px; } 50% { top: calc(100% - 8px); } 100% { top: 8px; } }
.qr-hint { text-align: center; z-index: 1; }

.qr-merchant-result { background: var(--bs-light, #f8f9fa); border-radius: 14px; padding: 1.5rem; }
.qr-merchant-icon { width: 52px; height: 52px; background: linear-gradient(135deg, #1a56db, #6366f1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; }
</style>