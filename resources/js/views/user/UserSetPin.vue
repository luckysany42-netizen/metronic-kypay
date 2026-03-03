<template>
  <div class="pin-page">
    <!-- Background decorative -->
    <div class="pin-bg">
      <div class="pin-bg__circle pin-bg__circle--1"></div>
      <div class="pin-bg__circle pin-bg__circle--2"></div>
      <div class="pin-bg__circle pin-bg__circle--3"></div>
    </div>

    <div class="pin-card">
      <!-- Logo -->
      <div class="pin-card__logo">
        <div class="pin-card__logo-icon">
          <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <rect width="28" height="28" rx="8" fill="white" fill-opacity="0.15"/>
            <path d="M14 6L20 10V14C20 17.31 17.43 20.41 14 21C10.57 20.41 8 17.31 8 14V10L14 6Z" fill="white" fill-opacity="0.9"/>
            <path d="M11.5 14L13.5 16L16.5 12" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <span class="pin-card__logo-text">KyPay</span>
      </div>

      <!-- Step indicator -->
      <div class="pin-card__steps">
        <div class="step" :class="{ active: step >= 1, done: step > 1 }">
          <div class="step__dot">
            <svg v-if="step > 1" width="10" height="10" viewBox="0 0 10 10" fill="none">
              <path d="M2 5L4 7L8 3" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span v-else>1</span>
          </div>
          <span class="step__label">Buat PIN</span>
        </div>
        <div class="step__line" :class="{ active: step > 1 }"></div>
        <div class="step" :class="{ active: step >= 2 }">
          <div class="step__dot">
            <span>2</span>
          </div>
          <span class="step__label">Konfirmasi</span>
        </div>
      </div>

      <!-- Step 1: Buat PIN -->
      <Transition name="slide" mode="out-in">
        <div v-if="step === 1" key="step1" class="pin-card__content">
          <div class="pin-card__header">
            <div class="pin-card__icon">🔐</div>
            <h2 class="pin-card__title">Buat PIN KyPay</h2>
            <p class="pin-card__desc">PIN digunakan untuk mengamankan setiap transaksimu. Jangan bagikan ke siapapun.</p>
          </div>

          <div class="pin-dots">
            <div
              v-for="i in 6"
              :key="i"
              class="pin-dot"
              :class="{
                'pin-dot--filled': pin.length >= i,
                'pin-dot--active': pin.length === i - 1,
                'pin-dot--error': pinError
              }"
            ></div>
          </div>

          <p v-if="pinError" class="pin-error">{{ pinError }}</p>

          <!-- Numpad -->
          <div class="numpad">
            <button
              v-for="num in ['1','2','3','4','5','6','7','8','9','','0','⌫']"
              :key="num"
              class="numpad__btn"
              :class="{ 'numpad__btn--empty': num === '', 'numpad__btn--del': num === '⌫' }"
              @click="handleNumpad(num)"
              :disabled="num === ''"
            >
              {{ num }}
            </button>
          </div>
        </div>
      </Transition>

      <!-- Step 2: Konfirmasi PIN -->
      <Transition name="slide" mode="out-in">
        <div v-if="step === 2" key="step2" class="pin-card__content">
          <div class="pin-card__header">
            <div class="pin-card__icon">✅</div>
            <h2 class="pin-card__title">Konfirmasi PIN</h2>
            <p class="pin-card__desc">Masukkan kembali PIN yang baru kamu buat untuk memastikan keduanya sama.</p>
          </div>

          <div class="pin-dots">
            <div
              v-for="i in 6"
              :key="i"
              class="pin-dot"
              :class="{
                'pin-dot--filled': pinConfirm.length >= i,
                'pin-dot--active': pinConfirm.length === i - 1,
                'pin-dot--error': confirmError
              }"
            ></div>
          </div>

          <p v-if="confirmError" class="pin-error">{{ confirmError }}</p>

          <div class="numpad">
            <button
              v-for="num in ['1','2','3','4','5','6','7','8','9','','0','⌫']"
              :key="num"
              class="numpad__btn"
              :class="{ 'numpad__btn--empty': num === '', 'numpad__btn--del': num === '⌫' }"
              @click="handleNumpadConfirm(num)"
              :disabled="num === '' || isSubmitting"
            >
              <span v-if="isSubmitting && num !== '' && num !== '⌫'" class="numpad__spinner"></span>
              <span v-else>{{ num }}</span>
            </button>
          </div>

          <button class="pin-back-btn" @click="backToStep1">
            ← Ulangi dari awal
          </button>
        </div>
      </Transition>

      <!-- Step 3: Sukses -->
      <Transition name="fade" mode="out-in">
        <div v-if="step === 3" key="step3" class="pin-card__content pin-card__content--success">
          <div class="success-animation">
            <div class="success-circle">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <path d="M8 20L16 28L32 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="success-check"/>
              </svg>
            </div>
          </div>
          <h2 class="pin-card__title mt-4">PIN Berhasil Dibuat!</h2>
          <p class="pin-card__desc">Akunmu sudah siap. Kamu bisa mulai menggunakan KyPay sekarang.</p>
          <div class="success-countdown">
            Mengarahkan ke halaman masuk dalam <strong>{{ countdown }}</strong> detik...
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import ApiService from "@/core/services/ApiService";

export default defineComponent({
  name: "user-set-pin",
  setup() {
    const router = useRouter();
    const route = useRoute();

    const step = ref(1);
    const pin = ref("");
    const pinConfirm = ref("");
    const pinError = ref("");
    const confirmError = ref("");
    const isSubmitting = ref(false);
    const countdown = ref(3);

    // Ambil api_token dari query param (dikirim saat redirect dari register)
    const apiToken = ref(route.query.token as string || "");

    // Kalau tidak ada token, redirect ke sign-in
    onMounted(() => {
      if (!apiToken.value) {
        router.push({ name: "user-sign-in" });
      }
    });

    // ── Step 1: Input PIN ──────────────────────────────────────────────────
    const handleNumpad = (num: string) => {
      pinError.value = "";
      if (num === "⌫") {
        pin.value = pin.value.slice(0, -1);
        return;
      }
      if (pin.value.length >= 6) return;
      pin.value += num;

      // Otomatis lanjut ke step 2 saat 6 digit terisi
      if (pin.value.length === 6) {
        // Validasi: tidak boleh 6 digit sama (111111, 222222, dll)
        if (/^(.)\1{5}$/.test(pin.value)) {
          pinError.value = "PIN tidak boleh 6 angka yang sama";
          setTimeout(() => { pin.value = ""; }, 500);
          return;
        }
        // Validasi: tidak boleh urutan naik/turun (123456, 654321)
        if (pin.value === "123456" || pin.value === "654321") {
          pinError.value = "PIN terlalu mudah ditebak";
          setTimeout(() => { pin.value = ""; }, 500);
          return;
        }
        setTimeout(() => { step.value = 2; }, 300);
      }
    };

    // ── Step 2: Konfirmasi PIN ─────────────────────────────────────────────
    const handleNumpadConfirm = (num: string) => {
      if (isSubmitting.value) return;
      confirmError.value = "";
      if (num === "⌫") {
        pinConfirm.value = pinConfirm.value.slice(0, -1);
        return;
      }
      if (pinConfirm.value.length >= 6) return;
      pinConfirm.value += num;

      // Otomatis submit saat 6 digit terisi
      if (pinConfirm.value.length === 6) {
        setTimeout(() => submitPin(), 300);
      }
    };

    const backToStep1 = () => {
      pin.value = "";
      pinConfirm.value = "";
      pinError.value = "";
      confirmError.value = "";
      step.value = 1;
    };

    // ── Submit ke backend ──────────────────────────────────────────────────
    const submitPin = async () => {
      if (pin.value !== pinConfirm.value) {
        confirmError.value = "PIN tidak cocok. Coba lagi dari awal.";
        setTimeout(() => {
          pinConfirm.value = "";
          confirmError.value = "";
        }, 1000);
        return;
      }

      isSubmitting.value = true;
      try {
        await ApiService.post("/wallet/set-initial-pin", {
          pin: pin.value,
          pin_confirmation: pinConfirm.value,
          api_token: apiToken.value,
        });

        // Sukses → step 3
        step.value = 3;
        startCountdown();
      } catch (err: any) {
        const msg = err?.response?.data?.errors?.pin || "Gagal menyimpan PIN. Coba lagi.";
        confirmError.value = msg;
        setTimeout(() => {
          pinConfirm.value = "";
          confirmError.value = "";
        }, 1500);
      } finally {
        isSubmitting.value = false;
      }
    };

    // ── Countdown redirect ─────────────────────────────────────────────────
    let countdownInterval: ReturnType<typeof setInterval> | null = null;
    const startCountdown = () => {
      countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
          clearInterval(countdownInterval!);
          router.push({ name: "user-sign-in" });
        }
      }, 1000);
    };

    onUnmounted(() => {
      if (countdownInterval) clearInterval(countdownInterval);
    });

    return {
      step,
      pin,
      pinConfirm,
      pinError,
      confirmError,
      isSubmitting,
      countdown,
      handleNumpad,
      handleNumpadConfirm,
      backToStep1,
    };
  },
});
</script>

<style scoped>
/* ── Page Layout ─────────────────────────────────────── */
.pin-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  position: relative;
  overflow: hidden;
  font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ── Background Decorative ───────────────────────────── */
.pin-bg { position: absolute; inset: 0; pointer-events: none; }
.pin-bg__circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
}
.pin-bg__circle--1 {
  width: 500px; height: 500px;
  background: #3b82f6;
  top: -150px; left: -150px;
}
.pin-bg__circle--2 {
  width: 400px; height: 400px;
  background: #6366f1;
  bottom: -100px; right: -100px;
}
.pin-bg__circle--3 {
  width: 300px; height: 300px;
  background: #0ea5e9;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
}

/* ── Card ─────────────────────────────────────────────── */
.pin-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 400px;
  margin: 24px;
  background: rgba(255,255,255,0.04);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  padding: 36px 32px 40px;
  box-shadow: 0 32px 80px rgba(0,0,0,0.4);
}

/* ── Logo ─────────────────────────────────────────────── */
.pin-card__logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
}
.pin-card__logo-icon {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pin-card__logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.3px;
}

/* ── Steps ────────────────────────────────────────────── */
.pin-card__steps {
  display: flex;
  align-items: center;
  gap: 0;
  margin-bottom: 32px;
}
.step {
  display: flex;
  align-items: center;
  gap: 8px;
}
.step__dot {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: rgba(255,255,255,0.08);
  border: 1.5px solid rgba(255,255,255,0.15);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255,255,255,0.4);
  transition: all 0.3s ease;
}
.step.active .step__dot {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border-color: transparent;
  color: #fff;
}
.step.done .step__dot {
  background: #10b981;
  border-color: transparent;
}
.step__label {
  font-size: 0.8rem;
  font-weight: 500;
  color: rgba(255,255,255,0.35);
  transition: color 0.3s;
}
.step.active .step__label { color: rgba(255,255,255,0.8); }
.step__line {
  flex: 1;
  height: 1.5px;
  background: rgba(255,255,255,0.1);
  margin: 0 12px;
  transition: background 0.3s;
}
.step__line.active { background: #10b981; }

/* ── Content ──────────────────────────────────────────── */
.pin-card__content { animation: fadeIn 0.3s ease; }
.pin-card__header { text-align: center; margin-bottom: 28px; }
.pin-card__icon { font-size: 2.2rem; margin-bottom: 12px; }
.pin-card__title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0 0 8px;
  letter-spacing: -0.3px;
}
.pin-card__desc {
  font-size: 0.85rem;
  color: rgba(255,255,255,0.45);
  margin: 0;
  line-height: 1.6;
}

/* ── PIN Dots ─────────────────────────────────────────── */
.pin-dots {
  display: flex;
  justify-content: center;
  gap: 14px;
  margin-bottom: 8px;
}
.pin-dot {
  width: 16px; height: 16px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.2);
  background: transparent;
  transition: all 0.2s ease;
}
.pin-dot--filled {
  background: #3b82f6;
  border-color: #3b82f6;
  transform: scale(1.1);
  box-shadow: 0 0 10px rgba(59,130,246,0.5);
}
.pin-dot--active {
  border-color: rgba(59,130,246,0.6);
  animation: pulse 1s ease infinite;
}
.pin-dot--error {
  border-color: #ef4444 !important;
  background: #ef4444 !important;
  animation: shake 0.4s ease;
}

.pin-error {
  text-align: center;
  color: #f87171;
  font-size: 0.8rem;
  font-weight: 500;
  margin: 8px 0 0;
  min-height: 20px;
}

/* ── Numpad ───────────────────────────────────────────── */
.numpad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-top: 28px;
}
.numpad__btn {
  height: 64px;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.05);
  color: #f1f5f9;
  font-size: 1.3rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}
.numpad__btn:not(.numpad__btn--empty):hover {
  background: rgba(59,130,246,0.15);
  border-color: rgba(59,130,246,0.3);
  transform: scale(0.97);
}
.numpad__btn:not(.numpad__btn--empty):active {
  background: rgba(59,130,246,0.25);
  transform: scale(0.93);
}
.numpad__btn--empty {
  background: transparent;
  border-color: transparent;
  cursor: default;
}
.numpad__btn--del {
  color: rgba(255,255,255,0.5);
  font-size: 1.1rem;
}

/* ── Back Button ──────────────────────────────────────── */
.pin-back-btn {
  display: block;
  width: 100%;
  margin-top: 16px;
  padding: 10px;
  background: transparent;
  border: none;
  color: rgba(255,255,255,0.35);
  font-size: 0.82rem;
  cursor: pointer;
  transition: color 0.2s;
  text-align: center;
}
.pin-back-btn:hover { color: rgba(255,255,255,0.7); }

/* ── Success ──────────────────────────────────────────── */
.pin-card__content--success { text-align: center; padding: 16px 0; }
.success-animation { display: flex; justify-content: center; margin-bottom: 8px; }
.success-circle {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 40px rgba(16,185,129,0.4);
  animation: successPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.success-check {
  stroke-dasharray: 50;
  stroke-dashoffset: 50;
  animation: drawCheck 0.4s ease 0.3s forwards;
}
.success-countdown {
  margin-top: 20px;
  font-size: 0.82rem;
  color: rgba(255,255,255,0.35);
}
.success-countdown strong { color: #3b82f6; }

/* ── Animations ───────────────────────────────────────── */
@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(59,130,246,0.4); }
  50% { box-shadow: 0 0 0 5px rgba(59,130,246,0); }
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20% { transform: translateX(-6px); }
  40% { transform: translateX(6px); }
  60% { transform: translateX(-4px); }
  80% { transform: translateX(4px); }
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
@keyframes successPop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@keyframes drawCheck { to { stroke-dashoffset: 0; } }

/* ── Slide Transition ─────────────────────────────────── */
.slide-enter-active, .slide-leave-active { transition: all 0.25s ease; }
.slide-enter-from { opacity: 0; transform: translateX(30px); }
.slide-leave-to { opacity: 0; transform: translateX(-30px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>