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
          <div class="text-muted fs-7">Pulsa, data, token listrik, BPJS, dan voucher game</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo KyPay</div>
          <div class="fw-bold text-warning fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Pilih Kategori & Produk -->
    <template v-if="step === 1">
      <!-- Pilih Kategori -->
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

      <!-- List Produk -->
      <div class="card" v-if="selectedCategory">
        <div class="card-header border-0 pt-6">
          <h3 class="card-title fw-bold">{{ currentCategoryLabel }}</h3>
        </div>
        <div class="card-body pt-0">

          <!-- Input nomor tujuan -->
          <div class="mb-6">
            <label class="form-label required fw-bold">
              {{ targetLabel }}
            </label>
            <input
              v-model="targetNumber"
              type="text"
              class="form-control form-control-solid"
              :class="targetError ? 'is-invalid' : ''"
              :placeholder="targetPlaceholder"
              @input="targetError = ''"
            />
            <div v-if="targetError" class="invalid-feedback d-block fs-8 mt-1">
              <i class="bi bi-exclamation-circle me-1"></i>{{ targetError }}
            </div>
            <div v-else class="form-text text-muted fs-8">{{ targetHint }}</div>
          </div>

          <!-- Filter provider -->
          <div class="d-flex gap-2 flex-wrap mb-5" v-if="providers.length > 1">
            <button
              v-for="p in ['Semua', ...providers]"
              :key="p"
              class="btn btn-sm"
              :class="selectedProvider === p ? 'btn-warning' : 'btn-light'"
              @click="selectedProvider = p"
            >{{ p }}</button>
          </div>

          <div v-if="loadingProducts" class="text-center py-10">
            <span class="spinner-border text-warning"></span>
          </div>

          <div v-else class="row g-4">
            <div
              v-for="product in filteredProducts"
              :key="product.code"
              class="col-12 col-md-6 col-lg-4"
              @click="selectProduct(product)"
            >
              <div
                class="border rounded-2 p-4 cursor-pointer"
                :class="selectedProduct?.code === product.code ? 'border-warning bg-light-warning' : 'border-gray-200'"
                style="transition: all 0.2s"
              >
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
            <button
              class="btn btn-warning text-white px-8"
              :disabled="!targetNumber.trim()"
              @click="goToConfirm"
            >
              Lanjut <i class="bi bi-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Step 2: Konfirmasi & PIN -->
    <div class="card" v-if="step === 2">
      <div class="card-header border-0 pt-6">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-light-secondary" @click="step = 1">
            <i class="bi bi-arrow-left"></i>
          </button>
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
              <div class="text-muted fs-8">{{ selectedProduct?.description }}</div>
            </div>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">{{ targetLabel }}</span>
            <span class="fw-bold">{{ targetNumber }}</span>
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
          <input
            v-model="pin"
            type="password"
            class="form-control form-control-solid text-center fw-bold fs-3"
            maxlength="6"
            placeholder="••••••"
            autofocus
          />
        </div>

        <div v-if="errorMsg" class="alert alert-danger py-2 fs-7">{{ errorMsg }}</div>

        <div class="d-flex justify-content-end gap-3">
          <button class="btn btn-light" @click="step = 1">Kembali</button>
          <button class="btn btn-warning text-white px-8" @click="submitPayment" :disabled="loading || !pin">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Bayar Sekarang
          </button>
        </div>
      </div>
    </div>

    <!-- Step 3: Sukses -->
    <div class="card" v-if="step === 3">
      <div class="card-body text-center py-15">
        <div class="symbol symbol-80px mx-auto mb-5">
          <span class="symbol-label bg-light-success">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
          </span>
        </div>
        <h3 class="fw-bold mb-2">Pembayaran Berhasil!</h3>
        <div class="text-muted fs-6 mb-1">{{ successData?.product_name }}</div>
        <div class="text-muted fs-7 mb-4">untuk <strong>{{ successData?.target_number }}</strong></div>

        <!-- Tampilkan token/kode hasil (untuk token listrik & voucher game) -->
        <div
          v-if="['token_listrik','voucher_game'].includes(successData?.category)"
          class="bg-light-warning rounded-2 p-5 mx-auto mb-5"
          style="max-width: 320px"
        >
          <div class="text-muted fs-8 mb-1">{{ successData?.category === 'token_listrik' ? 'Token Listrik' : 'Kode Voucher' }}</div>
          <div class="fw-bolder fs-3 text-warning letter-spacing-2">{{ successData?.result_code }}</div>
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

const step = ref(1);
const loading = ref(false);
const loadingProducts = ref(false);
const errorMsg = ref("");
const currentBalance = ref(0);
const targetNumber = ref("");
const pin = ref("");
const selectedCategory = ref("");
const selectedProvider = ref("Semua");
const selectedProduct = ref<any>(null);
const products = ref<any[]>([]);
const successData = ref<any>(null);

const categories = [
  { key: "pulsa",         label: "Pulsa",          icon: "bi-phone-fill",         color: "#e74c3c" },
  { key: "paket_data",    label: "Paket Data",      icon: "bi-wifi",               color: "#3498db" },
  { key: "token_listrik", label: "Token Listrik",   icon: "bi-lightning-fill",     color: "#f1c40f" },
  { key: "bpjs",          label: "BPJS",            icon: "bi-heart-pulse-fill",   color: "#27ae60" },
  { key: "voucher_game",  label: "Voucher Game",    icon: "bi-controller",         color: "#9b59b6" },
];

const targetLabels: Record<string, string> = {
  pulsa:         "Nomor HP Tujuan",
  paket_data:    "Nomor HP Tujuan",
  token_listrik: "Nomor Meter / ID Pelanggan PLN",
  bpjs:          "Nomor Peserta BPJS",
  voucher_game:  "ID Game / Username",
};

const targetPlaceholders: Record<string, string> = {
  pulsa:         "Contoh: 08123456789",
  paket_data:    "Contoh: 08123456789",
  token_listrik: "Contoh: 12345678901",
  bpjs:          "Contoh: 0001234567890",
  voucher_game:  "Contoh: ID Game kamu",
};

const targetHints: Record<string, string> = {
  pulsa:         "Format: 08xxxxxxxxxx (10–13 digit)",
  paket_data:    "Format: 08xxxxxxxxxx (10–13 digit)",
  token_listrik: "Format: 11–13 digit angka",
  bpjs:          "Format: 13 digit angka",
  voucher_game:  "Format: 3–30 karakter",
};

const targetLabel = computed(() => targetLabels[selectedCategory.value] ?? "Nomor Tujuan");
const targetPlaceholder = computed(() => targetPlaceholders[selectedCategory.value] ?? "Masukkan nomor tujuan");
const targetHint = computed(() => targetHints[selectedCategory.value] ?? "");
const currentCategoryLabel = computed(() => categories.find(c => c.key === selectedCategory.value)?.label ?? "");

const targetError = ref("");

// Validasi format per kategori
const validateTarget = (category: string, value: string): string => {
  const v = value.trim();
  switch (category) {
    case "pulsa":
    case "paket_data":
      if (!/^08\d{8,11}$/.test(v))
        return "Nomor HP harus diawali 08 dan terdiri dari 10–13 angka";
      break;
    case "token_listrik":
      if (!/^\d{11,13}$/.test(v))
        return "Nomor meter PLN harus 11–13 digit angka";
      break;
    case "bpjs":
      if (!/^\d{13}$/.test(v))
        return "Nomor peserta BPJS harus 13 digit angka";
      break;
    case "voucher_game":
      if (v.length < 3 || v.length > 30)
        return "ID Game harus 3–30 karakter";
      break;
  }
  return "";
};

const providers = computed(() => {
  const all = products.value.map((p: any) => p.provider);
  return [...new Set(all)] as string[];
});

const filteredProducts = computed(() => {
  if (selectedProvider.value === "Semua") return products.value;
  return products.value.filter((p: any) => p.provider === selectedProvider.value);
});

const formatRupiah = (val: number) =>
  "Rp " + Number(val || 0).toLocaleString("id-ID");

const selectCategory = async (key: string) => {
  selectedCategory.value = key;
  selectedProduct.value = null;
  selectedProvider.value = "Semua";
  targetNumber.value = "";
  loadingProducts.value = true;
  try {
    const { data } = await ApiService.query("payment/products", { params: { category: key } });
    products.value = data.data ?? [];
  } catch (e) {
    products.value = [];
  } finally {
    loadingProducts.value = false;
  }
};

const selectProduct = (product: any) => {
  selectedProduct.value = product;
};

const goToConfirm = () => {
  targetError.value = "";
  const err = validateTarget(selectedCategory.value, targetNumber.value);
  if (err) { targetError.value = err; return; }
  if (!targetNumber.value.trim()) return;
  errorMsg.value = "";
  pin.value = "";
  step.value = 2;
};

const submitPayment = async () => {
  errorMsg.value = "";
  if (!pin.value || pin.value.length !== 6) {
    errorMsg.value = "PIN harus 6 digit."; return;
  }
  loading.value = true;
  try {
    const { data } = await ApiService.post("payment", {
      product_code:  selectedProduct.value.code,
      target_number: targetNumber.value,
      pin:           pin.value,
      note:          null,
    });
    successData.value = data.data;
    currentBalance.value -= selectedProduct.value.price;
    step.value = 3;
  } catch (e: any) {
    errorMsg.value =
      e.response?.data?.errors?.pin ??
      e.response?.data?.message ??
      "Pembayaran gagal.";
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  step.value = 1;
  selectedProduct.value = null;
  selectedCategory.value = "";
  targetNumber.value = "";
  pin.value = "";
  errorMsg.value = "";
  successData.value = null;
};

onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    const walletData = data.wallet ?? data.data ?? data;
    currentBalance.value = Number(walletData?.balance ?? 0);
  } catch (e) {}
});
</script>