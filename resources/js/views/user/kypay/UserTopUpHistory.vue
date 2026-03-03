<template>
  <div class="card">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <h3 class="fw-bold mb-0">Riwayat Top Up</h3>
      </div>
      <div class="card-toolbar">
        <router-link :to="{ name: 'user-topup' }" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-2"></i> Top Up Baru
        </router-link>
      </div>
    </div>
    <div class="card-body pt-0">

      <div v-if="loading" class="text-center py-15">
        <span class="spinner-border text-primary"></span>
      </div>

      <div v-else-if="topUps.length === 0" class="text-center py-15">
        <i class="bi bi-inbox fs-3x text-muted mb-4 d-block"></i>
        <div class="text-muted fs-6 mb-4">Belum ada riwayat top up</div>
        <router-link :to="{ name: 'user-topup' }" class="btn btn-primary">Top Up Sekarang</router-link>
      </div>

      <div v-else>
        <div v-for="item in topUps" :key="item.id" class="d-flex align-items-center py-5 border-bottom border-gray-100">
          <div class="symbol symbol-45px me-4">
            <span class="symbol-label" :class="`bg-light-${item.status_color}`">
              <i class="bi fs-4"
                :class="{
                  'bi-hourglass-split': item.status === 'pending',
                  'bi-check-circle-fill': item.status === 'approved',
                  'bi-x-circle-fill': item.status === 'rejected',
                }"
                :style="{ color: item.status === 'approved' ? '#50cd89' : item.status === 'rejected' ? '#f1416c' : '#ffc700' }"
              ></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <div class="fw-bold text-gray-800 fs-6">Top Up via {{ item.payment_method }}</div>
            <div class="text-muted fs-8">{{ item.reference_number }}</div>
            <div class="text-muted fs-8">{{ formatDate(item.created_at) }}</div>
            <div v-if="item.admin_note && item.status === 'rejected'" class="text-danger fs-8 mt-1">
              <i class="bi bi-info-circle me-1"></i>{{ item.admin_note }}
            </div>
          </div>
          <div class="text-end">
            <div class="fw-bolder fs-5 text-primary">+{{ formatRupiah(item.amount) }}</div>
            <span class="badge mt-1" :class="`badge-light-${item.status_color}`">{{ item.status_label }}</span>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-6" v-if="meta.last_page > 1">
          <div class="text-muted fs-7">{{ meta.total }} pengajuan</div>
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
const topUps = ref<any[]>([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const currentPage = ref(1);

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatDate = (date: string) =>
  new Date(date).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });

const fetchTopUps = async () => {
  loading.value = true;
  try {
    const { data } = await ApiService.query("topup", { params: { page: currentPage.value } });
    topUps.value = data.data;
    meta.value = data.meta;
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => { currentPage.value = page; fetchTopUps(); };
onMounted(fetchTopUps);
</script>