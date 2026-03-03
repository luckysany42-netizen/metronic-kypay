import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
  {
    pages: [
      {
        heading: "Dashboard",
        route: "/dashboard",
        keenthemesIcon: "element-11",
        bootstrapIcon: "bi-app-indicator",
      },
    ],
  },
  {
    heading: "KyPay",
    route: "/kypay",
    pages: [
      {
        sectionTitle: "KyPay Payment",
        route: "/kypay",
        keenthemesIcon: "wallet",
        bootstrapIcon: "bi-wallet2",
        sub: [
          {
            heading: "Approval Top Up",
            route: "/kypay/topup-approval",
          },
          {
            heading: "Metode Pembayaran",
            route: "/kypay/payment-methods",
          },
        ],
      },
    ],
  },
];

export default MainMenuConfig;