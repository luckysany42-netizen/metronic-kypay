<template>
  <div class="d-flex flex-column gap-7">

    <!-- Header -->
    <div class="card">
      <div class="card-body d-flex align-items-center gap-4 p-7">
        <div class="symbol symbol-50px">
          <span class="symbol-label bg-light-info">
            <i class="bi bi-send-fill text-info fs-2"></i>
          </span>
        </div>
        <div>
          <h3 class="fw-bold mb-1">Transfer KyPay</h3>
          <div class="text-muted fs-7">Kirim saldo ke pengguna KyPay lainnya</div>
        </div>
        <div class="ms-auto text-end">
          <div class="text-muted fs-8">Saldo tersedia</div>
          <div class="fw-bold text-info fs-5">{{ formatRupiah(currentBalance) }}</div>
        </div>
      </div>
    </div>

    <!-- Form Transfer -->
    <div class="card" v-if="step === 1">
      <div class="card-body p-8">

        <!-- Cari Penerima -->
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

        <!-- Info Penerima -->
        <div v-if="receiver" class="notice d-flex bg-light-success rounded border-success border border-dashed p-5 mb-6">
          <div class="symbol symbol-45px me-4">
            <img
              v-if="getAvatarUrl(receiver.avatar)"
              :src="getAvatarUrl(receiver.avatar)"
              alt=""
              class="rounded-circle object-fit-cover w-45px h-45px"
            />
            <span v-else class="symbol-label bg-success text-white fw-bold fs-5">
              {{ receiver.user_name?.charAt(0) }}
            </span>
          </div>
          <div>
            <div class="fw-bold text-success">Penerima Ditemukan</div>
            <div class="fw-bolder fs-5 text-gray-800">{{ receiver.user_name }}</div>
            <div class="text-muted fs-8">{{ receiver.wallet_number }}</div>
            <div class="text-muted fs-8">{{ receiver.wallet_name }}</div>
          </div>
          <i class="bi bi-check-circle-fill text-success fs-2 ms-auto mt-1"></i>
        </div>

        <div v-if="searchError" class="alert alert-danger py-2 fs-7 mb-6">{{ searchError }}</div>

        <!-- Detail Transfer -->
        <div v-if="receiver" class="row g-5">
          <div class="col-12 col-md-6">
            <label class="form-label required fw-bold">Jumlah Transfer (Rp)</label>
            <input
              v-model="form.amount"
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
            <input
              v-model="form.note"
              type="text"
              class="form-control form-control-solid"
              placeholder="Contoh: bayar makan siang"
            />
          </div>

          <div class="col-12">
            <div class="alert alert-warning py-3 fs-7">
              <i class="bi bi-shield-exclamation me-2"></i>
              Transfer bersifat <strong>instan dan tidak dapat dibatalkan</strong>. Pastikan nomor wallet tujuan sudah benar.
            </div>
          </div>
        </div>

        <div v-if="errorMsg" class="alert alert-danger mt-4 py-2 fs-7">{{ errorMsg }}</div>

        <div class="mt-6 d-flex justify-content-end" v-if="receiver">
          <button class="btn btn-info text-white px-8" @click="step = 2" :disabled="!form.amount || Number(form.amount) < 1000">
            Lanjut <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Konfirmasi -->
    <div class="card" v-if="step === 2">
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

        <!-- Summary -->
        <div class="bg-light rounded-2 p-6 mb-6">
          <!-- Avatar penerima di konfirmasi -->
          <div class="d-flex align-items-center gap-3 mb-5 pb-5 border-bottom">
            <div class="symbol symbol-50px">
              <img
                v-if="getAvatarUrl(receiver?.avatar)"
                :src="getAvatarUrl(receiver?.avatar)"
                alt=""
                class="rounded-circle object-fit-cover w-50px h-50px"
              />
              <span v-else class="symbol-label bg-info text-white fw-bold fs-4">
                {{ receiver?.user_name?.charAt(0) }}
              </span>
            </div>
            <div>
              <div class="fw-bold fs-5">{{ receiver?.user_name }}</div>
              <div class="text-muted fs-8">{{ receiver?.wallet_number }}</div>
            </div>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Jumlah</span>
            <span class="fw-bold text-info fs-5">{{ formatRupiah(Number(form.amount)) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted fs-7">Biaya Transfer</span>
            <span class="fw-bold text-success">Gratis</span>
          </div>
          <div class="separator my-3"></div>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Total Dipotong</span>
            <span class="fw-bolder text-danger fs-5">{{ formatRupiah(Number(form.amount)) }}</span>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-muted fs-7">Saldo Setelah Transfer</span>
            <span class="fw-bold">{{ formatRupiah(currentBalance - Number(form.amount)) }}</span>
          </div>
          <div v-if="form.note" class="mt-3 pt-3 border-top">
            <span class="text-muted fs-8">Catatan: </span>
            <span class="fs-7">{{ form.note }}</span>
          </div>
        </div>

        <!-- Input PIN -->
        <div class="mb-5">
          <label class="form-label required fw-bold">Masukkan PIN KyPay</label>
          <input
            v-model="form.pin"
            type="password"
            class="form-control form-control-solid text-center fw-bold fs-3"
            maxlength="6"
            placeholder="••••••"
          />
        </div>

        <div v-if="errorMsg" class="alert alert-danger py-2 fs-7">{{ errorMsg }}</div>

        <div class="d-flex justify-content-end gap-3">
          <button class="btn btn-light" @click="step = 1">
            <i class="bi bi-arrow-left me-1"></i>Kembali
          </button>
          <button class="btn btn-info text-white px-8" @click="submitTransfer" :disabled="loading || !form.pin">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Konfirmasi Transfer
          </button>
        </div>
      </div>
    </div>

    <!-- Sukses -->
    <div class="card" v-if="step === 3">
      <div class="card-body text-center py-15">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mt-5 mb-2">Transfer Berhasil!</h3>
        <div class="text-muted fs-6 mb-1">Dikirim ke <strong>{{ receiver?.user_name }}</strong></div>
        <div class="fw-bolder text-info fs-3 mb-2">{{ formatRupiah(Number(form.amount)) }}</div>
        <div class="text-muted fs-8 mb-8">Ref: {{ successRef }}</div>
        <div class="d-flex justify-content-center gap-3">
          <router-link :to="{ name: 'user-transactions' }" class="btn btn-light-info">Lihat Riwayat</router-link>
          <router-link :to="{ name: 'user-wallet' }" class="btn btn-info text-white">Kembali ke Wallet</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const step = ref(1);
const loading = ref(false);
const searchLoading = ref(false);
const searchError = ref("");
const errorMsg = ref("");
const successRef = ref("");
const currentBalance = ref(0);
const receiverNumber = ref("");
const receiver = ref<any>(null);

const form = ref({ amount: "", note: "", pin: "" });

const formatRupiah = (val: number) =>
  "Rp " + Number(val || 0).toLocaleString("id-ID");

// Konversi path avatar relatif ke URL lengkap
const getAvatarUrl = (avatar: string | null | undefined): string | null => {
  if (!avatar) return null;
  if (avatar.startsWith("http")) return avatar;
  const base = (import.meta.env.VITE_APP_API_URL ?? "").replace("/api", "");
  return `${base}/storage/${avatar}`;
};

const searchWallet = async () => {
  searchError.value = "";
  receiver.value = null;
  if (!receiverNumber.value.trim()) return;
  searchLoading.value = true;
  try {
    const { data } = await ApiService.get("wallet/find", receiverNumber.value.trim());
    receiver.value = data.wallet ?? data.data ?? data;
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
  errorMsg.value = "";
  if (!form.value.pin || form.value.pin.length !== 6) {
    errorMsg.value = "PIN harus 6 digit."; return;
  }
  loading.value = true;
  try {
    const { data } = await ApiService.post("transfer", {
      receiver_wallet_number: receiver.value.wallet_number,
      amount: form.value.amount,
      pin: form.value.pin,
      note: form.value.note,
    });
    successRef.value = data.data?.reference_number ?? data.reference_number ?? "-";
    // ✅ Update saldo setelah transfer berhasil
    currentBalance.value = currentBalance.value - Number(form.value.amount);
    step.value = 3;
  } catch (e: any) {
    errorMsg.value =
      e.response?.data?.errors?.pin ??
      e.response?.data?.message ??
      "Transfer gagal.";
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    const { data } = await ApiService.get("wallet", "");
    const walletData = data.wallet ?? data.data ?? data;
    currentBalance.value = Number(walletData?.balance ?? 0);
  } catch (e) {}
});
</script>