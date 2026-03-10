<template>
  <div class="kypay-dashboard">

    <!-- Loading -->
    <div v-if="loading" class="d-flex justify-content-center align-items-center py-20">
      <div class="spinner-border text-primary" role="status"></div>
      <span class="ms-3 text-muted fw-semibold">Memuat data dashboard...</span>
    </div>

    <template v-else>

      <!-- ===== STATS CARDS ===== -->
      <div class="row g-5 mb-7">

        <!-- Total Saldo Sistem -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--blue">
            <div class="stat-card__icon">
              <i class="bi bi-wallet2"></i>
            </div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Saldo Sistem</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_balance_in_system) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot green"></span>
                {{ stats.active_wallets }} wallet aktif
              </div>
            </div>
          </div>
        </div>

        <!-- Total Wallet -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--indigo">
            <div class="stat-card__icon">
              <i class="bi bi-people"></i>
            </div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Wallet</div>
              <div class="stat-card__value">{{ stats.total_wallets?.toLocaleString('id') }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot red"></span>
                {{ stats.suspended_wallets }} disuspend
              </div>
            </div>
          </div>
        </div>

        <!-- Total Top Up -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--green">
            <div class="stat-card__icon">
              <i class="bi bi-arrow-down-circle"></i>
            </div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Top Up</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_topup_amount) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot yellow"></span>
                {{ stats.pending_topup }} pending
              </div>
            </div>
          </div>
        </div>

        <!-- Total Transfer -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--orange">
            <div class="stat-card__icon">
              <i class="bi bi-send"></i>
            </div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Transfer</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_transfer_amount) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot blue"></span>
                {{ stats.total_transfers_today }} transaksi hari ini
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== ROW 2: Pending Top Up + Summary ===== -->
      <div class="row g-5 mb-7">

        <!-- Pending Top Up Requests -->
        <div class="col-xl-8">
          <div class="kcard h-100">
            <div class="kcard__header">
              <div>
                <h5 class="kcard__title">Pengajuan Top Up Pending</h5>
                <div class="kcard__subtitle">Menunggu persetujuan admin</div>
              </div>
              <router-link to="/kypay/topup-approval" class="btn-see-all">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
              </router-link>
            </div>

            <div class="kcard__body">
              <div v-if="stats.recent_topup_requests?.length === 0" class="empty-state">
                <i class="bi bi-check-circle text-success fs-2x mb-3 d-block"></i>
                <div class="fw-semibold text-muted">Tidak ada pengajuan pending</div>
              </div>

              <div v-else class="topup-list">
                <div
                  v-for="req in stats.recent_topup_requests"
                  :key="req.id"
                  class="topup-item"
                >
                  <div class="topup-item__avatar">
                    <img v-if="req.user_avatar" :src="avatarUrl(req.user_avatar)" :alt="req.user_name" />
                    <div v-else class="avatar-initial">{{ req.user_name?.charAt(0) }}</div>
                  </div>
                  <div class="topup-item__info">
                    <div class="topup-item__name">{{ req.user_name }}</div>
                    <div class="topup-item__ref">{{ req.reference_number }}</div>
                  </div>
                  <div class="topup-item__amount">{{ formatRupiah(req.amount) }}</div>
                  <div class="topup-item__time">{{ formatTime(req.created_at) }}</div>
                  <div class="topup-item__actions">
                    <router-link :to="`/topup-requests/${req.id}`" class="btn-action">
                      Review
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="col-xl-4">
          <div class="kcard h-100">
            <div class="kcard__header">
              <div>
                <h5 class="kcard__title">Ringkasan Sistem</h5>
                <div class="kcard__subtitle">Status keseluruhan KyPay</div>
              </div>
            </div>
            <div class="kcard__body">
              <div class="summary-list">

                <div class="summary-item">
                  <div class="summary-item__icon bg-light-success">
                    <i class="bi bi-check-circle text-success"></i>
                  </div>
                  <div class="summary-item__info">
                    <div class="summary-item__label">Top Up Disetujui Hari Ini</div>
                    <div class="summary-item__value">{{ stats.approved_topup_today }} pengajuan</div>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-item__icon bg-light-warning">
                    <i class="bi bi-clock text-warning"></i>
                  </div>
                  <div class="summary-item__info">
                    <div class="summary-item__label">Pending Top Up</div>
                    <div class="summary-item__value text-warning">{{ stats.pending_topup }} pengajuan</div>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-item__icon bg-light-info">
                    <i class="bi bi-arrow-left-right text-info"></i>
                  </div>
                  <div class="summary-item__info">
                    <div class="summary-item__label">Transfer Berhasil Hari Ini</div>
                    <div class="summary-item__value">{{ stats.total_transfers_today }} transaksi</div>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-item__icon bg-light-primary">
                    <i class="bi bi-activity text-primary"></i>
                  </div>
                  <div class="summary-item__info">
                    <div class="summary-item__label">Total Transaksi</div>
                    <div class="summary-item__value">{{ stats.total_transactions?.toLocaleString('id') }}</div>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-item__icon bg-light-danger">
                    <i class="bi bi-slash-circle text-danger"></i>
                  </div>
                  <div class="summary-item__info">
                    <div class="summary-item__label">Wallet Disuspend</div>
                    <div class="summary-item__value text-danger">{{ stats.suspended_wallets }} wallet</div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ===== ROW 3: Quick Actions ===== -->
      <div class="row g-5">
        <div class="col-12">
          <div class="kcard">
            <div class="kcard__header">
              <div>
                <h5 class="kcard__title">Aksi Cepat</h5>
                <div class="kcard__subtitle">Navigasi ke menu utama</div>
              </div>
            </div>
            <div class="kcard__body">
              <div class="quick-actions">
                <router-link to="/kypay/topup-approval" class="quick-action">
                  <div class="quick-action__icon bg-light-warning">
                    <i class="bi bi-arrow-down-circle text-warning fs-2"></i>
                  </div>
                  <div class="quick-action__label">Kelola Top Up</div>
                  <div v-if="stats.pending_topup > 0" class="quick-action__badge">{{ stats.pending_topup }}</div>
                </router-link>

                <router-link to="/kypay/payment-methods" class="quick-action">
                  <div class="quick-action__icon bg-light-success">
                    <i class="bi bi-credit-card text-success fs-2"></i>
                  </div>
                  <div class="quick-action__label">Metode Pembayaran</div>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

export default defineComponent({
  name: "main-dashboard",
  setup() {
    const loading = ref(true);
    const stats = ref<any>({});

    const fetchDashboard = async () => {
      loading.value = true;
      try {
        const { data } = await ApiService.get("admin/payment/dashboard", "");
        stats.value = data.data;
      } catch (e) {
        console.error("Failed to load dashboard", e);
      } finally {
        loading.value = false;
      }
    };

    const formatRupiah = (val: number) => {
      if (!val && val !== 0) return "Rp 0";
      return "Rp " + Number(val).toLocaleString("id-ID");
    };

    const avatarUrl = (avatar: string) => {
      if (!avatar) return null;
      if (avatar.startsWith("http")) return avatar;
      const base = (import.meta.env.VITE_APP_API_URL ?? "").replace("/api", "");
      return `${base}/storage/${avatar}`;
    };

    const formatTime = (date: string) => {
      if (!date) return "";
      return new Date(date).toLocaleString("id-ID", {
        day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit"
      });
    };

    onMounted(fetchDashboard);

    return { loading, stats, formatRupiah, avatarUrl, formatTime };
  },
});
</script>

<style scoped>
.kypay-dashboard { padding: 0; }

/* ===== STAT CARDS ===== */
.stat-card {
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  border: 1px solid transparent;
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }

.stat-card--blue   { background: linear-gradient(135deg, #1a56db15, #1a56db08); border-color: #1a56db25; }
.stat-card--indigo { background: linear-gradient(135deg, #6366f115, #6366f108); border-color: #6366f125; }
.stat-card--green  { background: linear-gradient(135deg, #22c55e15, #22c55e08); border-color: #22c55e25; }
.stat-card--orange { background: linear-gradient(135deg, #f9731615, #f9731608); border-color: #f9731625; }

.stat-card__icon {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}
.stat-card--blue   .stat-card__icon { background: #1a56db20; color: #1a56db; }
.stat-card--indigo .stat-card__icon { background: #6366f120; color: #6366f1; }
.stat-card--green  .stat-card__icon { background: #22c55e20; color: #22c55e; }
.stat-card--orange .stat-card__icon { background: #f9731620; color: #f97316; }

.stat-card__label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem; }
.stat-card__value { font-size: 1.3rem; font-weight: 800; color: #e2e8f0; margin-bottom: 0.4rem; letter-spacing: -0.5px; }
.stat-card__sub { font-size: 0.75rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 0.4rem; }

.badge-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.badge-dot.green  { background: #22c55e; }
.badge-dot.red    { background: #ef4444; }
.badge-dot.yellow { background: #f59e0b; }
.badge-dot.blue   { background: #3b82f6; }

/* ===== CARD ===== */
.kcard { background: #1e2130; border-radius: 16px; border: 1px solid rgba(255,255,255,0.07); overflow: hidden; }
.kcard__header { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.07); }
.kcard__title { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0; }
.kcard__subtitle { font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 2px; }
.kcard__body { padding: 1.25rem 1.5rem; }

.btn-see-all { font-size: 0.8rem; font-weight: 600; color: #1a56db; text-decoration: none; display: flex; align-items: center; white-space: nowrap; }
.btn-see-all:hover { color: #1239a6; }

/* ===== TOPUP LIST ===== */
.topup-list { display: flex; flex-direction: column; gap: 0.75rem; }
.topup-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 1rem; background: rgba(255,255,255,0.04); border-radius: 12px; transition: background 0.2s; }
.topup-item:hover { background: rgba(255,255,255,0.07); }

.topup-item__avatar { width: 38px; height: 38px; border-radius: 10px; overflow: hidden; flex-shrink: 0; }
.topup-item__avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-initial { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #1a56db, #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }

.topup-item__info { flex: 1; min-width: 0; }
.topup-item__name { font-size: 0.85rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.topup-item__ref { font-size: 0.72rem; color: #64748b; font-weight: 500; }

.topup-item__amount { font-size: 0.9rem; font-weight: 700; color: #22c55e; white-space: nowrap; }
.topup-item__time { font-size: 0.72rem; color: #475569; white-space: nowrap; }

.btn-action { font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.875rem; background: #1a56db; color: #fff; border-radius: 8px; text-decoration: none; white-space: nowrap; }
.btn-action:hover { background: #1239a6; color: #fff; }

/* ===== EMPTY STATE ===== */
.empty-state { text-align: center; padding: 2rem; color: #64748b; }

/* ===== SUMMARY LIST ===== */
.summary-list { display: flex; flex-direction: column; gap: 1rem; }
.summary-item { display: flex; align-items: center; gap: 0.875rem; }
.summary-item__icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.summary-item__label { font-size: 0.75rem; color: #64748b; font-weight: 500; margin-bottom: 1px; }
.summary-item__value { font-size: 0.9rem; font-weight: 700; color: #e2e8f0; }

/* ===== QUICK ACTIONS ===== */
.quick-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
.quick-action { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1.25rem 1.5rem; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s, background 0.2s; position: relative; flex: 1; min-width: 120px; }
.quick-action:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); background: rgba(255,255,255,0.08); }
.quick-action__icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.quick-action__label { font-size: 0.8rem; font-weight: 600; color: #94a3b8; text-align: center; }
.quick-action__badge { position: absolute; top: 10px; right: 10px; background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 99px; }
</style>