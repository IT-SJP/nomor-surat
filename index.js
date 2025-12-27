// ============================================
// Konfigurasi
// ============================================
const GOOGLE_APPS_SCRIPT_URL =
  "https://script.google.com/macros/s/AKfycbx85kx4_veeIPeJeFvF8apF-zKOzntCw-tX0vZotwdOR-F3FoT7M6B20zjK9z8D4lgd/exec";

// ============================================
// Initialize
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  initializeTahunDropdown();
  setupFormSubmit();
  setDefaultBulanTahun();
  setupNavigation();
  setupMobileMenuToggle();
  generateCompanyStats();
});

// ============================================
// Navigation Setup
// ============================================
function setupNavigation() {
  const navItems = document.querySelectorAll(".nav-item");

  navItems.forEach((item) => {
    item.addEventListener("click", function () {
      const pageId = this.getAttribute("data-page");

      // Update active nav item
      navItems.forEach((nav) => nav.classList.remove("active"));
      this.classList.add("active");

      // Update active page
      showPage(pageId);

      // Close mobile sidebar after clicking
      closeMobileSidebar();
    });
  });

  // Set Dashboard as default active page
  document
    .querySelector('[data-page="dashboard-page"]')
    .classList.add("active");
}

// ============================================
// Mobile Menu Toggle Setup
// ============================================
function setupMobileMenuToggle() {
  const toggleBtn = document.getElementById("mobileMenuToggle");
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebarBackdrop");

  if (toggleBtn && sidebar && backdrop) {
    // Toggle button click
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
      backdrop.classList.toggle("active");
    });

    // Backdrop click - close sidebar
    backdrop.addEventListener("click", function () {
      sidebar.classList.remove("active");
      backdrop.classList.remove("active");
    });
  }

  // Close sidebar when clicking outside (fallback)
  document.addEventListener("click", function (event) {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("mobileMenuToggle");
    const backdrop = document.getElementById("sidebarBackdrop");

    if (
      sidebar &&
      toggleBtn &&
      backdrop &&
      !sidebar.contains(event.target) &&
      !toggleBtn.contains(event.target) &&
      sidebar.classList.contains("active")
    ) {
      sidebar.classList.remove("active");
      backdrop.classList.remove("active");
    }
  });
}

function closeMobileSidebar() {
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebarBackdrop");
  if (sidebar && window.innerWidth <= 768) {
    sidebar.classList.remove("active");
    if (backdrop) {
      backdrop.classList.remove("active");
    }
  }
}

function showPage(pageId) {
  const pages = document.querySelectorAll(".page-section");

  pages.forEach((page) => page.classList.remove("active"));

  const targetPage = document.getElementById(pageId);
  if (targetPage) {
    targetPage.classList.add("active");
  }
}

// ============================================
// Generate Company Statistics
// ============================================
function generateCompanyStats() {
  const companies = [
    "SJP",
    "SJPRA",
    "SJK",
    "SJE",
    "SJR",
    "SAS",
    "PAS",
    "SPEK",
    "SKORD",
    "BTJ",
    "PTU",
    "RBJ",
  ];
  const gridContainer = document.getElementById("companyStatsGrid");

  if (!gridContainer) return;

  gridContainer.innerHTML = "";

  companies.forEach((code) => {
    const count = getCompanyRequestCount(code);
    const card = document.createElement("div");
    card.className = "stat-card company-card";
    card.innerHTML = `
            <div class="company-code">${code}</div>
            <div class="company-count">${count}</div>
            <div class="company-label">Surat</div>
        `;
    gridContainer.appendChild(card);
  });
}

function getCompanyRequestCount(code) {
  const requests = JSON.parse(localStorage.getItem("requests") || "[]");
  return requests.filter((req) => req.kodePerusahaan === code).length;
}

// ============================================
// Populate Tahun Dropdown
// ============================================
function initializeTahunDropdown() {
  const tahunSelect = document.getElementById("tahun");
  const currentYear = new Date().getFullYear();

  // Tambah tahun dari 2020 sampai 5 tahun ke depan
  for (let i = currentYear + 5; i >= 2020; i--) {
    const option = document.createElement("option");
    option.value = i;
    option.textContent = i;
    tahunSelect.appendChild(option);
  }
}

// ============================================
// Set Default Bulan dan Tahun
// ============================================
function setDefaultBulanTahun() {
  const today = new Date();
  const currentMonth = today.getMonth() + 1;
  const currentYear = today.getFullYear();

  // Bulan dalam Roman
  const romanMonths = [
    "I",
    "II",
    "III",
    "IV",
    "V",
    "VI",
    "VII",
    "VIII",
    "IX",
    "X",
    "XI",
    "XII",
  ];

  const bulanSelect = document.getElementById("bulan");
  const tahunSelect = document.getElementById("tahun");

  if (bulanSelect && tahunSelect) {
    bulanSelect.value = romanMonths[currentMonth - 1];
    tahunSelect.value = currentYear;
  }
}

// ============================================
// Setup Form Submit
// ============================================
function setupFormSubmit() {
  const form = document.getElementById("formRequest");
  if (form) {
    form.addEventListener("submit", handleFormSubmit);
  }
}

// ============================================
// Handle Form Submit
// ============================================
async function handleFormSubmit(e) {
  e.preventDefault();

  // Pastikan request-page active (kalau user submit via shortcut/enter)
  const requestPage = document.getElementById("request-page");
  if (requestPage && !requestPage.classList.contains("active")) {
    showPage("request-page");
    // Tunggu sebentar agar page sudah rendered
    await new Promise((resolve) => setTimeout(resolve, 100));
  }

  // Validasi form
  if (!validateForm()) {
    return;
  }

  // Disable button
  const submitBtn = document.getElementById("submitBtn");
  const btnText = document.getElementById("btnText");
  const spinner = document.getElementById("spinner");

  submitBtn.disabled = true;
  btnText.textContent = "Sedang Mengirim...";
  spinner.classList.remove("hidden");

  try {
    // Kumpulkan data form
    const formData = collectFormData();

    // Kirim ke Google Apps Script
    await sendToGoogleSheets(formData);

    // Simpan ke localStorage untuk dashboard
    saveRequestToLocalStorage(formData);

    // Tampilkan success message
    showAlert("✓ Pengajuan surat berhasil dikirim!", "success");

    // Update dashboard stats
    generateCompanyStats();

    // Reset form
    setTimeout(() => {
      document.getElementById("formRequest").reset();
      setDefaultBulanTahun();
    }, 1500);
  } catch (error) {
    console.error("Error:", error);
    showAlert(
      "✗ Terjadi kesalahan saat mengirim form. Silahkan coba lagi.",
      "error"
    );
  } finally {
    // Enable button
    submitBtn.disabled = false;
    btnText.textContent = "Ajukan Pengajuan";
    spinner.classList.add("hidden");
  }
}

// ============================================
// Validate Form
// ============================================
function validateForm() {
  // Get form element
  const form = document.getElementById("formRequest");
  if (!form) {
    console.error("Form not found!");
    return false;
  }

  // Safe element retrieval dengan null checking
  const kodePerusahaanEl = form.querySelector("#kodePerusahaan");
  const kodeTujuanEl = form.querySelector("#kodeTujuan");
  const bulanEl = form.querySelector("#bulan");
  const tahunEl = form.querySelector("#tahun");
  const perihalEl = form.querySelector("#perihal");
  const tujuanEl = form.querySelector("#tujuan");
  const requestorEl = form.querySelector("#requestor");

  // Cek jika ada element yang null
  if (
    !kodePerusahaanEl ||
    !kodeTujuanEl ||
    !bulanEl ||
    !tahunEl ||
    !perihalEl ||
    !tujuanEl ||
    !requestorEl
  ) {
    console.error("Form elements not found!");
    showAlert("⚠ Terjadi kesalahan sistem. Silahkan refresh halaman.", "error");
    return false;
  }

  const kodePerusahaan = kodePerusahaanEl.value;
  const kodeTujuan = kodeTujuanEl.value;
  const bulan = bulanEl.value;
  const tahun = tahunEl.value;
  const perihal = perihalEl.value;
  const tujuan = tujuanEl.value;
  const requestor = requestorEl.value;

  // Cek required fields
  if (
    !kodePerusahaan ||
    !kodeTujuan ||
    !bulan ||
    !tahun ||
    !perihal ||
    !tujuan ||
    !requestor
  ) {
    showAlert("⚠ Mohon lengkapi semua field yang bertanda merah (*)", "info");
    return false;
  }

  return true;
}

// ============================================
// Collect Form Data
// ============================================
function collectFormData() {
  const form = document.getElementById("formRequest");
  if (!form) {
    console.error("Form not found in collectFormData!");
    return null;
  }

  return {
    timestamp: new Date().toISOString(),
    kodePerusahaan: form.querySelector("#kodePerusahaan")?.value || "",
    kodeTujuan: form.querySelector("#kodeTujuan")?.value || "",
    bulan: form.querySelector("#bulan")?.value || "",
    tahun: form.querySelector("#tahun")?.value || "",
    perihal: form.querySelector("#perihal")?.value || "",
    tujuan: form.querySelector("#tujuan")?.value || "",
    letakArsip: form.querySelector("#letakArsip")?.value || "",
    requestor: form.querySelector("#requestor")?.value || "",
  };
}

// ============================================
// Save Request to LocalStorage
// ============================================
function saveRequestToLocalStorage(formData) {
  const requests = JSON.parse(localStorage.getItem("requests") || "[]");
  requests.push(formData);
  localStorage.setItem("requests", JSON.stringify(requests));
}

// ============================================
// Send to Google Sheets
// ============================================
async function sendToGoogleSheets(data) {
  try {
    const response = await fetch(GOOGLE_APPS_SCRIPT_URL, {
      method: "POST",
      mode: "no-cors",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    // Untuk no-cors, response tidak bisa dibaca, tapi kita asumsikan berhasil
    return true;
  } catch (error) {
    throw new Error("Gagal menghubungi server: " + error.message);
  }
}

// ============================================
// Show Alert
// ============================================
function showAlert(message, type = "info") {
  const alertBox = document.getElementById("alertBox");
  if (alertBox) {
    alertBox.textContent = message;
    alertBox.className = `alert ${type}`;
    alertBox.classList.remove("hidden");

    // Auto hide after 5 seconds
    setTimeout(() => {
      alertBox.classList.add("hidden");
    }, 5000);
  }
}
