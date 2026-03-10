<template>
  <div class="d-flex flex-column gap-7">

    <!-- Loading state -->
    <div v-if="loadingWallet" class="card" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 200px;">
      <div class="card-body d-flex align-items-center justify-content-center">
        <span class="spinner-border text-light"></span>
      </div>
    </div>

    <!-- Header Wallet Card -->
    <div v-else-if="wallet" class="card bgi-no-repeat bgi-size-cover" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 200px;">
      <div class="card-body d-flex flex-column justify-content-between p-9">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="d-flex align-items-center gap-3 mb-2">
              <span class="badge badge-light-success fs-7 fw-bold">● AKTIF</span>
              <span class="text-white-50 fs-7">{{ wallet.wallet_number }}</span>
            </div>
            <div class="text-white-50 fs-7 mb-1">Saldo KyPay</div>
            <div class="text-white fw-bolder" style="font-size: 2.5rem;">
              {{ formatRupiah(wallet.balance) }}
            </div>
          </div>
          <div class="text-end">
            <div class="d-flex align-items-center gap-2 justify-content-end mb-1">
              <button
                class="btn btn-sm btn-icon"
                style="background: rgba(255,255,255,0.15); border: none; color: white;"
                @click="refreshAll"
                :disabled="refreshing"
                title="Refresh data"
              >
                <i class="bi bi-arrow-clockwise fs-5" :class="{ spin: refreshing }"></i>
              </button>
              <i class="bi bi-wallet2 text-white fs-1" style="opacity: 0.8;"></i>
            </div>
            <div class="text-white fw-bold fs-3">KyPay</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 mt-4 flex-wrap">
          <div class="text-white-50 fs-8">
            <i class="bi bi-person-fill text-white-50 me-1"></i>
            {{ authStore.user?.name }}
          </div>
          <div class="text-white-50 fs-8 ms-4" v-if="wallet.pin_set">
            <i class="bi bi-shield-check text-success me-1"></i> PIN Aktif
          </div>
          <div class="text-white-50 fs-8 ms-4" v-else>
            <i class="bi bi-shield-exclamation text-warning me-1"></i> PIN Belum Diset
          </div>
          <div class="text-white-50 fs-9 ms-auto" v-if="lastRefreshed">
            <i class="bi bi-check2 me-1"></i>Update: {{ lastRefreshed }}
          </div>
        </div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else class="card" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 200px;">
      <div class="card-body d-flex align-items-center justify-content-center">
        <div class="text-center text-white">
          <i class="bi bi-exclamation-triangle fs-2 mb-2 d-block"></i>
          <div>Gagal memuat data wallet</div>
          <button class="btn btn-sm btn-light mt-3" @click="fetchWallet">Coba Lagi</button>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-5">
      <div class="col-6 col-md-3">
        <router-link :to="{ name: 'user-topup' }" class="card card-hover text-center p-6 text-decoration-none">
          <div class="symbol symbol-50px mx-auto mb-3">
            <span class="symbol-label bg-light-primary">
              <i class="bi bi-plus-circle-fill text-primary fs-2"></i>
            </span>
          </div>
          <div class="fw-bold text-gray-800 fs-7">Top Up</div>
        </router-link>
      </div>
      <div class="col-6 col-md-3">
        <router-link :to="{ name: 'user-transfer' }" class="card card-hover text-center p-6 text-decoration-none">
          <div class="symbol symbol-50px mx-auto mb-3">
            <span class="symbol-label bg-light-info">
              <i class="bi bi-send-fill text-info fs-2"></i>
            </span>
          </div>
          <div class="fw-bold text-gray-800 fs-7">Transfer</div>
        </router-link>
      </div>
      <div class="col-6 col-md-3">
        <router-link :to="{ name: 'user-transactions' }" class="card card-hover text-center p-6 text-decoration-none">
          <div class="symbol symbol-50px mx-auto mb-3">
            <span class="symbol-label bg-light-success">
              <i class="bi bi-clock-history text-success fs-2"></i>
            </span>
          </div>
          <div class="fw-bold text-gray-800 fs-7">Riwayat</div>
        </router-link>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-hover text-center p-6 cursor-pointer" @click="showPinModal = true">
          <div class="symbol symbol-50px mx-auto mb-3">
            <span class="symbol-label bg-light-warning">
              <i class="bi bi-shield-lock-fill text-warning fs-2"></i>
            </span>
          </div>
          <div class="fw-bold text-gray-800 fs-7">{{ wallet?.pin_set ? 'Ganti PIN' : 'Buat PIN' }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
      <div class="card-header border-0 pt-6">
        <div class="card-title">
          <h3 class="fw-bold mb-0">Transaksi Terakhir</h3>
        </div>
        <div class="card-toolbar d-flex align-items-center gap-3">
          <button class="btn btn-sm btn-icon btn-light" @click="fetchTransactions" :disabled="loadingTransactions" title="Refresh">
            <i class="bi bi-arrow-clockwise" :class="{ spin: loadingTransactions }"></i>
          </button>
          <router-link :to="{ name: 'user-transactions' }" class="btn btn-sm btn-light-primary">
            Lihat Semua
          </router-link>
        </div>
      </div>
      <div class="card-body pt-0">
        <div v-if="loadingTransactions" class="text-center py-10">
          <span class="spinner-border spinner-border-sm text-primary"></span>
          <div class="text-muted mt-2 fs-7">Memuat transaksi...</div>
        </div>

        <div v-else-if="transactions.length === 0" class="text-center py-10">
          <i class="bi bi-inbox fs-2x text-muted mb-3 d-block"></i>
          <div class="text-muted fs-6">Belum ada transaksi</div>
        </div>

        <div v-else>
          <div
            v-for="trx in transactions.slice(0, 5)"
            :key="trx.id"
            class="d-flex align-items-center py-4 border-bottom border-gray-100"
          >
            <div class="symbol symbol-40px me-4">
              <span class="symbol-label" :class="isCredit(trx) ? 'bg-light-success' : 'bg-light-danger'">
                <i class="bi fs-4" :class="trxIcon(trx.type)" :style="{ color: isCredit(trx) ? '#50cd89' : '#f1416c' }"></i>
              </span>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold text-gray-800 fs-7">{{ trx.type_label }}</div>
              <div class="text-muted fs-8">{{ trx.description }}</div>
            </div>
            <div class="text-end">
              <div class="fw-bold fs-7" :class="isCredit(trx) ? 'text-success' : 'text-danger'">
                {{ isCredit(trx) ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
              </div>
              <div class="text-muted fs-8">{{ formatDate(trx.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Set PIN -->
    <div v-if="showPinModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" @click.self="showPinModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ wallet?.pin_set ? 'Ganti PIN' : 'Buat PIN KyPay' }}</h5>
            <button type="button" class="btn-close" @click="showPinModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="wallet?.pin_set" class="mb-4">
              <label class="form-label required">PIN Saat Ini</label>
              <input v-model="pinForm.current_pin" type="password" class="form-control form-control-solid text-center fs-3 fw-bold" maxlength="6" placeholder="••••••" />
            </div>
            <div class="mb-4">
              <label class="form-label required">PIN Baru (6 digit)</label>
              <input v-model="pinForm.pin" type="password" class="form-control form-control-solid text-center fs-3 fw-bold" maxlength="6" placeholder="••••••" />
            </div>
            <div class="mb-4">
              <label class="form-label required">Konfirmasi PIN Baru</label>
              <input v-model="pinForm.pin_confirmation" type="password" class="form-control form-control-solid text-center fs-3 fw-bold" maxlength="6" placeholder="••••••" />
            </div>
            <div v-if="pinError" class="alert alert-danger py-2 fs-7">{{ pinError }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="showPinModal = false">Batal</button>
            <button class="btn btn-primary" @click="submitPin" :disabled="pinLoading">
              <span v-if="pinLoading" class="spinner-border spinner-border-sm me-2"></span>
              Simpan PIN
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onActivated, onUnmounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import ApiService from "@/core/services/ApiService";

const authStore = useAuthStore();

const wallet              = ref<any>(null);
const transactions        = ref<any[]>([]);
const loadingWallet       = ref(true);
const loadingTransactions = ref(true);
const refreshing          = ref(false);
const lastRefreshed       = ref("");

const showPinModal = ref(false);
const pinLoading   = ref(false);
const pinError     = ref("");
const pinForm      = ref({ current_pin: "", pin: "", pin_confirmation: "" });

const isCredit = (trx: any) =>
  trx.is_credit ?? (trx.type === "top_up" || trx.type === "transfer_in");

const formatRupiah = (val: number) =>
  "Rp " + Number(val || 0).toLocaleString("id-ID");

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });

const trxIcon = (type: string) => {
  if (type === "top_up")       return "bi-plus-circle-fill";
  if (type === "transfer_in")  return "bi-arrow-down-circle-fill";
  if (type === "transfer_out") return "bi-arrow-up-circle-fill";
  if (type === "payment")      return "bi-cart-fill";
  return "bi-circle-fill";
};

const fetchWallet = async () => {
  loadingWallet.value = true;
  try {
    const { data } = await ApiService.get("wallet", "");
    wallet.value = data.data ?? data.wallet ?? data ?? null;
  } catch {
    wallet.value = null;
  } finally {
    loadingWallet.value = false;
  }
};

const fetchTransactions = async () => {
  loadingTransactions.value = true;
  try {
    const { data } = await ApiService.get("wallet", "transactions");
    transactions.value = data.data ?? [];
  } catch {
    transactions.value = [];
  } finally {
    loadingTransactions.value = false;
  }
};

const refreshAll = async () => {
  refreshing.value = true;
  await Promise.all([fetchWallet(), fetchTransactions()]);
  refreshing.value = false;
  const now = new Date();
  lastRefreshed.value = now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
};

const submitPin = async () => {
  pinError.value = "";
  if (pinForm.value.pin.length !== 6) { pinError.value = "PIN harus 6 digit."; return; }
  if (pinForm.value.pin !== pinForm.value.pin_confirmation) { pinError.value = "Konfirmasi PIN tidak cocok."; return; }
  pinLoading.value = true;
  try {
    await ApiService.post("wallet/set-pin", pinForm.value);
    showPinModal.value = false;
    if (wallet.value) wallet.value.pin_set = true;
    pinForm.value = { current_pin: "", pin: "", pin_confirmation: "" };
  } catch (e: any) {
    pinError.value = e.response?.data?.message ?? "Gagal menyimpan PIN.";
  } finally {
    pinLoading.value = false;
  }
};

// Refresh saat window fokus (kembali dari tab/halaman lain di browser)
const onWindowFocus = () => refreshAll();

onMounted(() => {
  refreshAll();
  window.addEventListener("focus", onWindowFocus);
});

// Refresh saat navigasi balik via Vue Router (keep-alive)
onActivated(() => refreshAll());

onUnmounted(() => {
  window.removeEventListener("focus", onWindowFocus);
});
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.spin { animation: spin 0.8s linear infinite; display: inline-block; }
</style>