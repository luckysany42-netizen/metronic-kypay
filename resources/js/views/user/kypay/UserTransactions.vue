<template>
  <div class="card">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <h3 class="fw-bold mb-0">Riwayat Transaksi</h3>
      </div>
      <div class="card-toolbar gap-3">
        <select v-model="filterType" class="form-select form-select-solid form-select-sm w-auto" @change="fetchTransactions">
          <option value="">Semua Tipe</option>
          <option value="top_up">Top Up</option>
          <option value="transfer_in">Transfer Masuk</option>
          <option value="transfer_out">Transfer Keluar</option>
          <option value="payment">Pembayaran</option>
        </select>
      </div>
    </div>
    <div class="card-body pt-0">

      <div v-if="loading" class="text-center py-15">
        <span class="spinner-border text-primary"></span>
        <div class="text-muted mt-3 fs-7">Memuat transaksi...</div>
      </div>

      <div v-else-if="transactions.length === 0" class="text-center py-15">
        <i class="bi bi-inbox fs-3x text-muted mb-4 d-block"></i>
        <div class="text-muted fs-6">Belum ada transaksi</div>
      </div>

      <div v-else>
        <div
          v-for="trx in transactions"
          :key="trx.id"
          class="d-flex align-items-center py-5 border-bottom border-gray-100"
        >
          <div class="symbol symbol-45px me-4">
            <span class="symbol-label" :class="isCredit(trx) ? 'bg-light-success' : 'bg-light-danger'">
              <i class="bi fs-3"
                :class="{
                  'bi-plus-circle-fill text-success': trx.type === 'top_up',
                  'bi-arrow-down-circle-fill text-success': trx.type === 'transfer_in',
                  'bi-arrow-up-circle-fill text-danger': trx.type === 'transfer_out',
                  'bi-cart-fill text-warning': trx.type === 'payment',
                }"
              ></i>
            </span>
          </div>

          <div class="flex-grow-1">
            <div class="fw-bold text-gray-800 fs-6">{{ trx.type_label }}</div>
            <div class="text-muted fs-8">{{ trx.description }}</div>
            <div class="text-muted fs-8 mt-1">
              <i class="bi bi-calendar3 me-1"></i>{{ formatDate(trx.created_at) }}
              · Ref: {{ trx.transaction_number }}
            </div>
          </div>

          <div class="text-end">
            <div class="fw-bolder fs-5" :class="isCredit(trx) ? 'text-success' : 'text-danger'">
              {{ isCredit(trx) ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
            </div>
            <div class="text-muted fs-8">Saldo: {{ formatRupiah(trx.balance_after) }}</div>
            <span class="badge badge-light-success fs-9 mt-1">{{ trx.status }}</span>
          </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-6" v-if="meta.last_page > 1">
          <div class="text-muted fs-7">Total {{ meta.total }} transaksi</div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light" :disabled="meta.current_page === 1" @click="changePage(meta.current_page - 1)">
              <i class="bi bi-chevron-left"></i>
            </button>
            <span class="btn btn-sm btn-primary">{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button class="btn btn-sm btn-light" :disabled="meta.current_page === meta.last_page" @click="changePage(meta.current_page + 1)">
              <i class="bi bi-chevron-right"></i>
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
const transactions = ref<any[]>([]);
const filterType = ref("");
const isCredit = (trx: any): boolean =>
  trx.is_credit ?? (trx.type === "top_up" || trx.type === "transfer_in");
const currentPage = ref(1);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatDate = (date: string) =>
  new Date(date).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });

const fetchTransactions = async () => {
  loading.value = true;
  try {
    const params: any = { page: currentPage.value };
    if (filterType.value) params.type = filterType.value;
    const { data } = await ApiService.query("wallet/transactions", { params });
    transactions.value = data.data;
    meta.value = {
      current_page: data.current_page ?? 1,
      last_page: data.last_page ?? 1,
      total: data.total ?? 0,
    };
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  currentPage.value = page;
  fetchTransactions();
};

onMounted(fetchTransactions);
</script>