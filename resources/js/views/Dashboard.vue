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
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--blue">
            <div class="stat-card__icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Saldo Sistem</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_balance_in_system) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot green"></span>{{ stats.active_wallets }} wallet aktif
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--indigo">
            <div class="stat-card__icon"><i class="bi bi-people"></i></div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Wallet</div>
              <div class="stat-card__value">{{ stats.total_wallets?.toLocaleString('id') }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot red"></span>{{ stats.suspended_wallets }} disuspend
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--green">
            <div class="stat-card__icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Top Up</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_topup_amount) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot yellow"></span>{{ stats.pending_topup }} pending
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card stat-card--orange">
            <div class="stat-card__icon"><i class="bi bi-send"></i></div>
            <div class="stat-card__body">
              <div class="stat-card__label">Total Transfer</div>
              <div class="stat-card__value">{{ formatRupiah(stats.total_transfer_amount) }}</div>
              <div class="stat-card__sub">
                <span class="badge-dot blue"></span>{{ stats.total_transfers_today }} transaksi hari ini
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== ROW 2: Grafik Transaksi ===== -->
      <div class="row g-5 mb-7">

        <!-- Grafik Transaksi -->
        <div class="col-xl-8">
          <div class="kcard h-100">
            <div class="kcard__header">
              <div>
                <h5 class="kcard__title">Grafik Transaksi</h5>
                <div class="kcard__subtitle">Aktivitas transaksi {{ chartPeriod }} hari terakhir</div>
              </div>
              <div class="d-flex gap-2">
                <button
                  v-for="p in chartPeriods" :key="p.value"
                  class="btn btn-sm"
                  :class="chartPeriod === p.value ? 'btn-primary' : 'btn-light'"
                  @click="changeChartPeriod(p.value)"
                  :disabled="chartLoading"
                >{{ p.label }}</button>
              </div>
            </div>
            <div class="kcard__body" style="position: relative;">
              <div v-if="chartLoading" class="chart-overlay">
                <span class="spinner-border text-primary"></span>
              </div>
              <canvas ref="chartCanvas" style="height:280px; max-height:280px;"></canvas>
            </div>
          </div>
        </div>

        <!-- Distribusi Tipe Transaksi -->
        <div class="col-xl-4">
          <div class="kcard h-100">
            <div class="kcard__header">
              <div>
                <h5 class="kcard__title">Tipe Transaksi</h5>
                <div class="kcard__subtitle">Distribusi berdasarkan tipe</div>
              </div>
            </div>
            <div class="kcard__body d-flex flex-column align-items-center">
              <div v-if="chartLoading" class="d-flex align-items-center justify-content-center" style="height:200px;">
                <span class="spinner-border text-primary"></span>
              </div>
              <canvas v-show="!chartLoading" ref="donutCanvas" style="height:200px; max-height:200px; max-width:200px;"></canvas>
              <div class="donut-legend mt-4 w-100" v-if="!chartLoading">
                <div v-for="item in donutLegend" :key="item.label" class="donut-legend-item">
                  <span class="donut-legend-dot" :style="{ background: item.color }"></span>
                  <span class="donut-legend-label">{{ item.label }}</span>
                  <span class="donut-legend-value">{{ item.value }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== ROW 3: Pending Top Up + Summary ===== -->
      <div class="row g-5 mb-7">
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
                <div v-for="req in stats.recent_topup_requests" :key="req.id" class="topup-item">
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
                    <router-link to="/kypay/topup-approval" class="btn-action">Review</router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

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

      <!-- ===== ROW 4: Quick Actions ===== -->
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
import { defineComponent, ref, onMounted, onUnmounted, nextTick } from "vue";
import ApiService from "@/core/services/ApiService";

export default defineComponent({
  name: "main-dashboard",
  setup() {
    const loading      = ref(true);
    const chartLoading = ref(true);
    const stats        = ref<any>({});
    const chartCanvas  = ref<HTMLCanvasElement | null>(null);
    const donutCanvas  = ref<HTMLCanvasElement | null>(null);
    const chartPeriod  = ref(7);
    const chartPeriods = [
      { label: "7H",  value: 7  },
      { label: "14H", value: 14 },
      { label: "30H", value: 30 },
    ];

    let lineChart: any  = null;
    let donutChart: any = null;

    const donutLegend = ref<{ label: string; color: string; value: number }[]>([]);
    const chartStats = ref({ topup: 0, transfer: 0, payment: 0 });

    // ── Load Chart.js dari CDN ──────────────────────────
    const loadChartJs = (): Promise<any> => {
      return new Promise((resolve, reject) => {
        if ((window as any).Chart) { resolve((window as any).Chart); return; }
        const s = document.createElement("script");
        s.src = "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js";
        s.onload  = () => resolve((window as any).Chart);
        s.onerror = () => reject(new Error("Gagal load Chart.js"));
        document.head.appendChild(s);
      });
    };

    // ── Fetch chart data dari API ───────────────────────
    const fetchChartData = async (days: number) => {
      const { data } = await ApiService.get(`admin/payment/chart?days=${days}`, "");
      return data.data;
    };

    // ── Render line chart ───────────────────────────────
    const renderLineChart = async (chartData?: any) => {
      const Chart = await loadChartJs();
      await nextTick();
      if (!chartCanvas.value) return;

      if (lineChart) { lineChart.destroy(); lineChart = null; }

      const cd = chartData ?? await fetchChartData(chartPeriod.value);

      // Update stats
      chartStats.value = {
        topup:    cd.topup ? cd.topup.reduce((a: number, b: number) => a + b, 0) : 0,
        transfer: cd.transfer ? cd.transfer.reduce((a: number, b: number) => a + b, 0) : 0,
        payment:  cd.payment ? cd.payment.reduce((a: number, b: number) => a + b, 0) : 0,
      };

      lineChart = new Chart(chartCanvas.value, {
        type: "line",
        data: {
          labels: cd.labels,
          datasets: [
            {
              label: `${chartStats.value.topup} Top Up`,
              data: cd.topup,
              borderColor: "#22c55e",
              backgroundColor: "rgba(34,197,94,0.1)",
              borderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6,
              tension: 0.4,
              fill: true,
            },
            {
              label: `${chartStats.value.transfer} Transfer`,
              data: cd.transfer,
              borderColor: "#3b82f6",
              backgroundColor: "rgba(59,130,246,0.08)",
              borderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6,
              tension: 0.4,
              fill: true,
            },
            {
              label: `${chartStats.value.payment} Pembayaran`,
              data: cd.payment,
              borderColor: "#f59e0b",
              backgroundColor: "rgba(245,158,11,0.08)",
              borderWidth: 2.5,
              pointRadius: 4,
              pointHoverRadius: 6,
              tension: 0.4,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: {
              position: "top",
              labels: {
                color: "#94a3b8",
                font: { size: 12, weight: "600" },
                usePointStyle: true,
                pointStyleWidth: 8,
                padding: 20,
              },
            },
            tooltip: {
              backgroundColor: "#1e2130",
              borderColor: "rgba(255,255,255,0.1)",
              borderWidth: 1,
              titleColor: "#e2e8f0",
              bodyColor: "#94a3b8",
              padding: 12,
            },
          },
          scales: {
            x: {
              grid:  { color: "rgba(255,255,255,0.05)" },
              ticks: { color: "#64748b", font: { size: 11 } },
            },
            y: {
              grid:  { color: "rgba(255,255,255,0.05)" },
              ticks: { color: "#64748b", font: { size: 11 }, stepSize: 1 },
              beginAtZero: true,
            },
          },
        },
      });
    };

    // ── Render donut chart ──────────────────────────────
    const renderDonutChart = async (donutData?: any) => {
      const Chart = await loadChartJs();
      await nextTick();
      if (!donutCanvas.value) return;

      if (donutChart) { donutChart.destroy(); donutChart = null; }

      const dd = donutData ?? (await fetchChartData(chartPeriod.value)).donut;

      const colors = ["#22c55e", "#3b82f6", "#f59e0b", "#a855f7"];
      const labels = ["Top Up", "Transfer", "Pembayaran", "Lainnya"];
      const values = [dd.top_up, dd.transfer, dd.payment, dd.other];

      donutLegend.value = labels.map((label, i) => ({
        label,
        color:  colors[i],
        value:  values[i],
      }));

      donutChart = new Chart(donutCanvas.value, {
        type: "doughnut",
        data: {
          labels,
          datasets: [{
            data:            values,
            backgroundColor: colors,
            borderColor:     "#1e2130",
            borderWidth:     3,
            hoverOffset:     8,
          }],
        },
        options: {
          responsive:          true,
          maintainAspectRatio: false,
          cutout:              "68%",
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: "#1e2130",
              borderColor:     "rgba(255,255,255,0.1)",
              borderWidth:     1,
              titleColor:      "#e2e8f0",
              bodyColor:       "#94a3b8",
              padding:         10,
            },
          },
        },
      });
    };

    // ── Ganti periode → fetch data baru ────────────────
    const changeChartPeriod = async (period: number) => {
      if (chartPeriod.value === period) return;
      chartPeriod.value  = period;
      chartLoading.value = true;
      try {
        const cd = await fetchChartData(period);
        
        // Update stats
        chartStats.value = {
          topup:    cd.topup ? cd.topup.reduce((a: number, b: number) => a + b, 0) : 0,
          transfer: cd.transfer ? cd.transfer.reduce((a: number, b: number) => a + b, 0) : 0,
          payment:  cd.payment ? cd.payment.reduce((a: number, b: number) => a + b, 0) : 0,
        };

        if (lineChart) {
          // Update labels dengan data baru
          lineChart.data.labels           = cd.labels;
          lineChart.data.datasets[0].label = `${chartStats.value.topup} Top Up`;
          lineChart.data.datasets[0].data = cd.topup;
          lineChart.data.datasets[1].label = `${chartStats.value.transfer} Transfer`;
          lineChart.data.datasets[1].data = cd.transfer;
          lineChart.data.datasets[2].label = `${chartStats.value.payment} Pembayaran`;
          lineChart.data.datasets[2].data = cd.payment;
          lineChart.update("active");
        }
      } finally {
        chartLoading.value = false;
      }
    };

    // ── Fetch dashboard + chart sekaligus ───────────────
    const fetchDashboard = async () => {
      loading.value = true;
      try {
        const [dashRes, chartRes] = await Promise.all([
          ApiService.get("admin/payment/dashboard", ""),
          ApiService.get(`admin/payment/chart?days=${chartPeriod.value}`, ""),
        ]);

        stats.value = dashRes.data.data;
        const cd    = chartRes.data.data;

        loading.value      = false;
        chartLoading.value = true;
        await nextTick();
        await nextTick();

        try {
          await Promise.all([renderLineChart(cd), renderDonutChart(cd.donut)]);
        } finally {
          chartLoading.value = false;
        }
      } catch (e) {
        console.error("Failed to load dashboard", e);
        loading.value      = false;
        chartLoading.value = false;
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
      return `${base}/uploads/avatars/${avatar}`;
    };

    const formatTime = (date: string) => {
      if (!date) return "";
      return new Date(date).toLocaleString("id-ID", {
        day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit",
      });
    };

    onMounted(fetchDashboard);

    onUnmounted(() => {
      if (lineChart)  { lineChart.destroy();  lineChart  = null; }
      if (donutChart) { donutChart.destroy(); donutChart = null; }
    });

    return {
      loading, chartLoading, stats,
      chartCanvas, donutCanvas,
      chartPeriod, chartPeriods, donutLegend, chartStats,
      changeChartPeriod,
      formatRupiah, avatarUrl, formatTime,
    };
  },
});
</script>

<style scoped>
.kypay-dashboard { padding: 0; }

/* ===== STAT CARDS ===== */
.stat-card { border-radius: 16px; padding: 1.5rem; display: flex; align-items: flex-start; gap: 1rem; border: 1px solid transparent; transition: transform 0.2s, box-shadow 0.2s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.stat-card--blue   { background: linear-gradient(135deg, #1a56db15, #1a56db08); border-color: #1a56db25; }
.stat-card--indigo { background: linear-gradient(135deg, #6366f115, #6366f108); border-color: #6366f125; }
.stat-card--green  { background: linear-gradient(135deg, #22c55e15, #22c55e08); border-color: #22c55e25; }
.stat-card--orange { background: linear-gradient(135deg, #f9731615, #f9731608); border-color: #f9731625; }
.stat-card__icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
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
.kcard__header { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.07); flex-wrap: wrap; gap: 0.5rem; }
.kcard__title { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0; }
.kcard__subtitle { font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 2px; }
.kcard__body { padding: 1.25rem 1.5rem; }
.btn-see-all { font-size: 0.8rem; font-weight: 600; color: #1a56db; text-decoration: none; display: flex; align-items: center; white-space: nowrap; }
.btn-see-all:hover { color: #1239a6; }

/* ✅ Overlay loading saat ganti periode */
.chart-overlay {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  background: rgba(30,33,48,0.7);
  border-radius: 0 0 16px 16px;
  z-index: 10;
}

/* ===== DONUT LEGEND ===== */
.donut-legend { display: flex; flex-direction: column; gap: 8px; }
.donut-legend-item { display: flex; align-items: center; gap: 8px; }
.donut-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.donut-legend-label { font-size: 0.78rem; color: #94a3b8; flex: 1; }
.donut-legend-value { font-size: 0.78rem; font-weight: 700; color: #e2e8f0; }

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