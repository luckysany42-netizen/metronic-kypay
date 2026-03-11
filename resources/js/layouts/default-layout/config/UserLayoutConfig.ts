import type LayoutConfigTypes from "@/layouts/default-layout/config/types";

const UserLayoutConfig: LayoutConfigTypes = {
  general: {
    mode: "system",
    primaryColor: "#50CD89",
    pageWidth: "default",
    layout: "dark-sidebar",
    iconsType: "duotone",
  },
  header: {
    display: true,
    default: {
      container: "fluid",
      fixed: {
        desktop: true,
        mobile: false,
      },
      menu: {
        display: false, // ✅ Matikan header menu supaya tidak dobel
        iconType: "keenthemes",
      },
    },
  },
  sidebar: {
    display: true,
    default: {
      minimize: {
        desktop: {
          enabled: false,  // ✅ Matikan minimize bawaan Metronic, kita handle sendiri
          default: false,
          hoverable: false,
        },
      },
      menu: {
        iconType: "keenthemes",
      },
    },
  },
  toolbar: {
    display: true,
    container: "fluid",
    fixed: {
      desktop: false,
      mobile: false,
    },
  },
  pageTitle: {
    display: true,
    breadcrumb: true,
    direction: "column",
  },
  content: {
    container: "fluid",
  },
  footer: {
    display: true,
    container: "fluid",
    fixed: {
      desktop: false,
      mobile: false,
    },
  },
  illustrations: {
    set: "sketchy-1",
  },
  scrolltop: {
    display: true,
  },
};

export default UserLayoutConfig;