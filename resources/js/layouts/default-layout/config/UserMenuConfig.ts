import type { MenuItem } from "@/layouts/default-layout/config/types";

const UserMenuConfig: Array<MenuItem> = [
  {
    pages: [
      {
        heading: "Dashboard",
        route: "/user/dashboard",
        keenthemesIcon: "element-11",
        bootstrapIcon: "bi-app-indicator",
      },
    ],
  },
  {
    heading: "KyPay",
    pages: [
      {
        heading: "Wallet Saya",
        route: "/user/wallet",
        keenthemesIcon: "wallet",
        bootstrapIcon: "bi-wallet2",
      },
      {
        sectionTitle: "Transaksi",
        route: "/user/topup",
        keenthemesIcon: "dollar",
        bootstrapIcon: "bi-arrow-left-right",
        sub: [
          { heading: "Top Up",          route: "/user/topup" },
          { heading: "Riwayat Top Up",  route: "/user/topup/history" },
          { heading: "Transfer",        route: "/user/transfer" },
          { heading: "Semua Transaksi", route: "/user/transactions" },
        ],
      },
      {
        heading: "Bayar & Beli",
        route: "/user/payment",
        keenthemesIcon: "tag",
        bootstrapIcon: "bi-grid-fill",
      },
      {
        sectionTitle: "QR Payment",
        route: "/user/qr-receive",
        keenthemesIcon: "scan-barcode",
        bootstrapIcon: "bi-qr-code",
        sub: [
          { heading: "Terima Pembayaran", route: "/user/qr-receive" },
          { heading: "Scan & Bayar",      route: "/user/qr-scan" },
        ],
      },
    ],
  },
  {
    heading: "Account",
    pages: [
      {
        heading: "My Profile",
        route: "/user/profile",
        keenthemesIcon: "profile-circle",
        bootstrapIcon: "bi-person",
      },
    ],
  },
];

export default UserMenuConfig;