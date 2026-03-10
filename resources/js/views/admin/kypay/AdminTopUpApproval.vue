<template>
  <div class="d-flex flex-column gap-7">

    <!-- Stats -->
    <div class="row g-5">
      <div class="col-12 col-md-4">
        <div class="card bg-light-warning border-warning border border-dashed">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-50px">
              <span class="symbol-label bg-warning">
                <i class="bi bi-hourglass-split text-white fs-2"></i>
              </span>
            </div>
            <div>
              <div class="text-muted fs-8">Menunggu Konfirmasi</div>
              <div class="fw-bolder fs-2 text-warning">{{ stats.total_pending }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card bg-light-success border-success border border-dashed">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-50px">
              <span class="symbol-label bg-success">
                <i class="bi bi-check-circle text-white fs-2"></i>
              </span>
            </div>
            <div>
              <div class="text-muted fs-8">Total Disetujui</div>
              <div class="fw-bolder fs-2 text-success">{{ stats.total_approved }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card bg-light-danger border-danger border border-dashed">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-50px">
              <span class="symbol-label bg-danger">
                <i class="bi bi-x-circle text-white fs-2"></i>
              </span>
            </div>
            <div>
              <div class="text-muted fs-8">Total Ditolak</div>
              <div class="fw-bolder fs-2 text-danger">{{ stats.total_rejected }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-header border-0 pt-6">
        <div class="card-title">
          <h3 class="fw-bold mb-0">Pengajuan Top Up</h3>
        </div>
        <div class="card-toolbar gap-3">
          <select v-model="filterStatus" class="form-select form-select-solid form-select-sm w-auto" @change="fetchTopUps">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
          <input v-model="search" @input="fetchTopUps" type="text" class="form-control form-control-solid form-control-sm w-200px" placeholder="Cari nama/referensi..." />
        </div>
      </div>
      <div class="card-body pt-0">

        <div v-if="loading" class="text-center py-15">
          <span class="spinner-border text-primary"></span>
        </div>

        <div v-else-if="topUps.length === 0" class="text-center py-15">
          <i class="bi bi-inbox fs-3x text-muted mb-4 d-block"></i>
          <div class="text-muted">Tidak ada data</div>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
              <tr class="fw-bold text-muted bg-light">
                <th class="ps-4">User</th>
                <th>Referensi</th>
                <th>Metode</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th class="text-end pe-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in topUps" :key="item.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-35px">
                      <!-- ✅ pakai avatarUrl() helper agar path relatif di-prefix storage URL -->
                      <img v-if="item.user.avatar" :src="avatarUrl(item.user.avatar)" alt="" class="rounded-circle" style="width:35px;height:35px;object-fit:cover;" />
                      <span v-else class="symbol-label bg-light-primary text-primary fw-bold">
                        {{ item.user.name?.charAt(0) }}
                      </span>
                    </div>
                    <div>
                      <div class="fw-bold fs-7">{{ item.user.name }}</div>
                      <div class="text-muted fs-8">{{ item.wallet_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="fs-8 text-muted">{{ item.reference_number }}</td>
                <td class="fs-7">{{ item.payment_method }}</td>
                <td class="fw-bold fs-6 text-primary">{{ formatRupiah(item.amount) }}</td>
                <td class="fs-8 text-muted">{{ formatDate(item.created_at) }}</td>
                <td>
                  <span class="badge" :class="`badge-light-${item.status_color}`">
                    {{ item.status_label }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-icon btn-light-primary me-2" @click="openDetail(item)" title="Lihat Detail">
                    <i class="bi bi-eye fs-6"></i>
                  </button>
                  <template v-if="item.status === 'pending'">
                    <button class="btn btn-sm btn-icon btn-light-success me-2" @click="openApprove(item)" title="Setujui">
                      <i class="bi bi-check-lg fs-6"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light-danger" @click="openReject(item)" title="Tolak">
                      <i class="bi bi-x-lg fs-6"></i>
                    </button>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4" v-if="meta.last_page > 1">
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

    <!-- Modal Detail -->
    <div v-if="detailModal && selectedItem" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="detailModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Top Up — {{ selectedItem.reference_number }}</h5>
            <button class="btn-close" @click="detailModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row g-5">
              <div class="col-md-6">
                <div class="fw-bold text-muted fs-8 mb-1">USER</div>
                <div class="fw-bold">{{ selectedItem.user.name }}</div>
                <div class="text-muted fs-8">{{ selectedItem.user.email }}</div>
                <div class="text-muted fs-8">Wallet: {{ selectedItem.wallet_number }}</div>
              </div>
              <div class="col-md-6">
                <div class="fw-bold text-muted fs-8 mb-1">DETAIL PEMBAYARAN</div>
                <div class="fw-bold">{{ selectedItem.payment_method }}</div>
                <div class="text-muted fs-8">{{ selectedItem.payment_account }} · {{ selectedItem.payment_holder }}</div>
              </div>
              <div class="col-md-6">
                <div class="fw-bold text-muted fs-8 mb-1">JUMLAH</div>
                <div class="fw-bolder fs-3 text-primary">{{ formatRupiah(selectedItem.amount) }}</div>
              </div>
              <div class="col-md-6">
                <div class="fw-bold text-muted fs-8 mb-1">STATUS</div>
                <span class="badge fs-7" :class="`badge-light-${selectedItem.status_color}`">{{ selectedItem.status_label }}</span>
              </div>
              <div class="col-12" v-if="selectedItem.proof_image">
                <div class="fw-bold text-muted fs-8 mb-2">BUKTI TRANSFER</div>
                <a :href="avatarUrl(selectedItem.proof_image)" target="_blank">
                  <img :src="avatarUrl(selectedItem.proof_image)" alt="Bukti Transfer" class="rounded-2 mw-100 mh-300px" />
                </a>
              </div>
              <div class="col-12" v-if="selectedItem.user_note">
                <div class="fw-bold text-muted fs-8 mb-1">CATATAN USER</div>
                <div class="bg-light rounded p-3 fs-7">{{ selectedItem.user_note }}</div>
              </div>
              <div class="col-12" v-if="selectedItem.admin_note">
                <div class="fw-bold text-muted fs-8 mb-1">CATATAN ADMIN</div>
                <div class="bg-light rounded p-3 fs-7">{{ selectedItem.admin_note }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="detailModal = false">Tutup</button>
            <template v-if="selectedItem.status === 'pending'">
              <button class="btn btn-success" @click="detailModal = false; openApprove(selectedItem)">Setujui</button>
              <button class="btn btn-danger" @click="detailModal = false; openReject(selectedItem)">Tolak</button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Approve -->
    <div v-if="approveModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="approveModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-success">Setujui Top Up</h5>
            <button class="btn-close" @click="approveModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
              <div class="fw-bold mt-3">{{ selectedItem?.user.name }}</div>
              <div class="fs-2 fw-bolder text-primary mt-1">{{ formatRupiah(selectedItem?.amount) }}</div>
            </div>
            <label class="form-label">Catatan Admin (opsional)</label>
            <textarea v-model="adminNote" class="form-control form-control-solid" rows="3" placeholder="Catatan untuk user..."></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="approveModal = false">Batal</button>
            <button class="btn btn-success" @click="confirmApprove" :disabled="actionLoading">
              <span v-if="actionLoading" class="spinner-border spinner-border-sm me-2"></span>
              Ya, Setujui
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Reject -->
    <div v-if="rejectModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="rejectModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Tolak Pengajuan</h5>
            <button class="btn-close" @click="rejectModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
              <div class="fw-bold mt-3">{{ selectedItem?.user.name }}</div>
              <div class="fs-5 fw-bold text-muted mt-1">{{ formatRupiah(selectedItem?.amount) }}</div>
            </div>
            <label class="form-label required">Alasan Penolakan</label>
            <textarea v-model="adminNote" class="form-control form-control-solid" rows="3" placeholder="Wajib isi alasan penolakan..."></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="rejectModal = false">Batal</button>
            <button class="btn btn-danger" @click="confirmReject" :disabled="actionLoading || !adminNote.trim()">
              <span v-if="actionLoading" class="spinner-border spinner-border-sm me-2"></span>
              Ya, Tolak
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
const actionLoading = ref(false);
const topUps = ref<any[]>([]);
const stats = ref({ total_pending: 0, total_approved: 0, total_rejected: 0 });
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const filterStatus = ref("pending");
const search = ref("");
const currentPage = ref(1);

const selectedItem = ref<any>(null);
const detailModal = ref(false);
const approveModal = ref(false);
const rejectModal = ref(false);
const adminNote = ref("");

const formatRupiah = (val: number) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatDate = (date: string) =>
  new Date(date).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });

/**
 * ✅ Helper: pastikan avatar/gambar selalu punya full URL
 * - Jika sudah http/https → pakai langsung (misal dari upload lama)
 * - Jika path relatif (avatars/xxx.jpg) → prefix dengan storage URL
 */
const avatarUrl = (path: string): string => {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  const base = import.meta.env.VITE_APP_API_URL?.replace("/api", "") ?? "http://127.0.0.1:8000";
  return `${base}/storage/${path}`;
};

const fetchTopUps = async () => {
  loading.value = true;
  try {
    const params: any = { page: currentPage.value };
    if (filterStatus.value) params.status = filterStatus.value;
    if (search.value) params.search = search.value;
    const { data } = await ApiService.query("admin/topup", { params });
    topUps.value = data.data;
    stats.value = data.stats;
    meta.value = data.meta;
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => { currentPage.value = page; fetchTopUps(); };
const openDetail = (item: any) => { selectedItem.value = item; detailModal.value = true; };
const openApprove = (item: any) => { selectedItem.value = item; adminNote.value = ""; approveModal.value = true; };
const openReject = (item: any) => { selectedItem.value = item; adminNote.value = ""; rejectModal.value = true; };

const confirmApprove = async () => {
  actionLoading.value = true;
  try {
    await ApiService.post(`admin/topup/${selectedItem.value.id}/approve`, { admin_note: adminNote.value });
    approveModal.value = false;
    fetchTopUps();
  } catch (e: any) {
    alert(e.response?.data?.message ?? "Gagal menyetujui.");
  } finally {
    actionLoading.value = false;
  }
};

const confirmReject = async () => {
  actionLoading.value = true;
  try {
    await ApiService.post(`admin/topup/${selectedItem.value.id}/reject`, { admin_note: adminNote.value });
    rejectModal.value = false;
    fetchTopUps();
  } catch (e: any) {
    alert(e.response?.data?.message ?? "Gagal menolak.");
  } finally {
    actionLoading.value = false;
  }
};

onMounted(fetchTopUps);
</script>