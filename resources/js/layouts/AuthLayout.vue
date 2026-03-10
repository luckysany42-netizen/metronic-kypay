<template>
  <div class="auth-root">
    <!-- Animated background -->
    <div class="bg-layer">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
      <div class="grid-overlay"></div>
    </div>

    <!-- Left: Form -->
    <div class="auth-form-side">
      <div class="form-inner">
        <!-- Logo mobile -->
        <div class="mobile-logo d-lg-none">
          <img :src="getAssetPath('media/logos/logo-kypay.png')" alt="KyPay" class="mobile-logo-img" />
          <span class="brand-name">KyPay</span>
        </div>

        <router-view></router-view>

        <div class="auth-footer">
          <a href="#">Syarat & Ketentuan</a>
          <span class="dot">·</span>
          <a href="#">Privasi</a>
          <span class="dot">·</span>
          <a href="#">Bantuan</a>
        </div>
      </div>
    </div>

    <!-- Right: Branding -->
    <div class="auth-brand-side d-none d-lg-flex">
      <div class="brand-content">

        <!-- Logo -->
        <div class="brand-logo">
          <img :src="getAssetPath('media/logos/logo-kypay.png')" alt="KyPay" class="brand-logo-img" />
          <span class="brand-wordmark">KyPay Gak Bahaya ta</span>
        </div>

        <!-- Stats cards -->
        <div class="stats-showcase">
          <div class="stat-card stat-card-main">
            <div class="stat-label">Total Saldo</div>
            <div class="stat-value">Rp 12.450.000</div>
            <div class="stat-change positive">↑ 8.2% bulan ini</div>
            <div class="stat-bar">
              <div class="stat-bar-fill" style="width: 72%"></div>
            </div>
          </div>

          <div class="stat-row">
            <div class="stat-card stat-card-sm">
              <div class="stat-icon">↑</div>
              <div class="stat-sm-label">Top Up</div>
              <div class="stat-sm-value">Rp 2.5jt</div>
            </div>
            <div class="stat-card stat-card-sm">
              <div class="stat-icon send">→</div>
              <div class="stat-sm-label">Transfer</div>
              <div class="stat-sm-value">Rp 1.2jt</div>
            </div>
          </div>

          <!-- Recent tx -->
          <div class="tx-list">
            <div class="tx-item">
              <div class="tx-icon tx-in">↓</div>
              <div class="tx-info">
                <div class="tx-name">Transfer masuk</div>
                <div class="tx-time">Hari ini, 14:22</div>
              </div>
              <div class="tx-amount positive">+Rp 500.000</div>
            </div>
            <div class="tx-item">
              <div class="tx-icon tx-pay">🛒</div>
              <div class="tx-info">
                <div class="tx-name">Pulsa Telkomsel</div>
                <div class="tx-time">Kemarin, 09.15</div>
              </div>
              <div class="tx-amount negative">-Rp 50.000</div>
            </div>
          </div>
        </div>

        <!-- Tagline -->
        <div class="brand-tagline">
          <h2>Kelola uangmu,<br /><span class="accent">lebih cerdas.</span></h2>
          <p>Transfer, top up, dan bayar tagihan dalam satu platform yang aman dan mudah.</p>
        </div>

        <!-- Trust badges -->
        <div class="trust-badges">
          <div class="badge-item">
            <span class="badge-icon"></span>
            <span>Enkripsi 256-bit</span>
          </div>
          <div class="badge-item">
            <span class="badge-icon"></span>
            <span>Transfer instan</span>
          </div>
          <div class="badge-item">
            <span class="badge-icon"></span>
            <span>Transaksi aman</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { getAssetPath } from "@/core/helpers/assets";
import { defineComponent, onMounted } from "vue";
import LayoutService from "@/core/services/LayoutService";
import { useBodyStore } from "@/stores/body";

export default defineComponent({
  name: "auth-layout",
  setup() {
    const store = useBodyStore();
    onMounted(() => {
      LayoutService.emptyElementClassesAndAttributes(document.body);
      store.addBodyClassname("app-blank");
      store.addBodyClassname("bg-body");
    });
    return { getAssetPath };
  },
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

* { box-sizing: border-box; }

.auth-root {
  min-height: 100vh;
  display: flex;
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: #070b14;
  position: relative;
  overflow: hidden;
}

/* ===== ANIMATED BACKGROUND ===== */
.bg-layer {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  animation: float 8s ease-in-out infinite;
}

.orb-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, #1a56db, #0d3b9e);
  top: -100px; right: 10%;
  animation-delay: 0s;
}

.orb-2 {
  width: 350px; height: 350px;
  background: radial-gradient(circle, #0ea5e9, #0369a1);
  bottom: 10%; right: 25%;
  animation-delay: -3s;
}

.orb-3 {
  width: 250px; height: 250px;
  background: radial-gradient(circle, #6366f1, #4338ca);
  top: 40%; left: 5%;
  animation-delay: -5s;
}

@keyframes float {
  0%, 100% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-30px) scale(1.05); }
}

.grid-overlay {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
  background-size: 50px 50px;
}

/* ===== FORM SIDE ===== */
.auth-form-side {
  flex: 0 0 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  z-index: 1;
}

@media (min-width: 992px) {
  .auth-form-side {
    flex: 0 0 42%;
    border-right: 1px solid rgba(255,255,255,0.06);
    background: rgba(7, 11, 20, 0.8);
    backdrop-filter: blur(20px);
  }
}

.form-inner {
  width: 100%;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.mobile-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.mobile-logo-img { height: 36px; }

.brand-name {
  font-size: 1.4rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.5px;
}

.auth-footer {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  justify-content: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.auth-footer a {
  color: rgba(255,255,255,0.4);
  text-decoration: none;
  font-size: 0.78rem;
  font-weight: 500;
  transition: color 0.2s;
}

.auth-footer a:hover { color: rgba(255,255,255,0.8); }

.dot { color: rgba(255,255,255,0.2); font-size: 0.7rem; }

/* ===== BRAND SIDE ===== */
.auth-brand-side {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 3rem 3rem 2.5rem;
  position: relative;
  z-index: 1;
}

.brand-content {
  width: 100%;
  max-width: 480px;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Logo */
.brand-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.brand-logo-img { height: 44px; }

.brand-wordmark {
  font-size: 1.8rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -1px;
}

/* Stats showcase */
.stats-showcase {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.stat-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  backdrop-filter: blur(10px);
  transition: transform 0.3s, border-color 0.3s;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255,255,255,0.15);
}

.stat-card-main {}

.stat-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: rgba(255,255,255,0.4);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.4rem;
}

.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.5px;
  margin-bottom: 0.3rem;
}

.stat-change {
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.stat-change.positive { color: #34d399; }

.stat-bar {
  height: 4px;
  background: rgba(255,255,255,0.08);
  border-radius: 99px;
  overflow: hidden;
}

.stat-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #1a56db, #0ea5e9);
  border-radius: 99px;
  animation: grow 1.5s ease-out forwards;
}

@keyframes grow {
  from { width: 0 !important; }
}

.stat-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.stat-card-sm {
  padding: 1rem 1.25rem;
}

.stat-icon {
  font-size: 1.1rem;
  color: #34d399;
  font-weight: 700;
  margin-bottom: 0.4rem;
}

.stat-icon.send { color: #60a5fa; }

.stat-sm-label {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.4);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 0.2rem;
}

.stat-sm-value {
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

/* Transaction list */
.tx-list {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 16px;
  overflow: hidden;
}

.tx-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1.25rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: background 0.2s;
}

.tx-item:last-child { border-bottom: none; }
.tx-item:hover { background: rgba(255,255,255,0.03); }

.tx-icon {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  flex-shrink: 0;
}

.tx-in { background: rgba(52,211,153,0.15); color: #34d399; }
.tx-pay { background: rgba(251,191,36,0.15); font-size: 0.75rem; }

.tx-info { flex: 1; }

.tx-name {
  font-size: 0.82rem;
  font-weight: 600;
  color: rgba(255,255,255,0.85);
}

.tx-time {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.35);
  margin-top: 1px;
}

.tx-amount {
  font-size: 0.85rem;
  font-weight: 700;
}

.tx-amount.positive { color: #34d399; }
.tx-amount.negative { color: #f87171; }

/* Tagline */
.brand-tagline h2 {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
  letter-spacing: -0.5px;
  margin: 0 0 0.75rem;
}

.accent {
  background: linear-gradient(135deg, #60a5fa, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-tagline p {
  font-size: 0.9rem;
  color: rgba(255,255,255,0.45);
  line-height: 1.65;
  margin: 0;
}

/* Trust badges */
.trust-badges {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.badge-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255,255,255,0.45);
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 99px;
  padding: 0.35rem 0.75rem;
}

.badge-icon { font-size: 0.8rem; }
</style>