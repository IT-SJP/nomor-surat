/**
 * Google Apps Script - Sistem Request Nomor Surat (Updated)
 * Fitur: Auto-sheet, Email Confirmation, Anti-Duplicate Number (LockService)
 */

// ============================================
// KONFIGURASI
// ============================================

const SHEET_ID = "1llpSzD7Hu3g71p7i394DglnuWcEQ6LXsam3ChYoe088";

const COMPANY_CODES = [
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

// ============================================
// Main Handler (GET & POST)
// ============================================

function doGet(e) {
  try {
    const action = e.parameter.action;
    if (action === "getStats") {
      return ContentService.createTextOutput(
        JSON.stringify({
          status: "success",
          data: getCompanyStats(),
        })
      ).setMimeType(ContentService.MimeType.JSON);
    }
    return ContentService.createTextOutput(
      JSON.stringify({ status: "error", message: "Invalid action" })
    ).setMimeType(ContentService.MimeType.JSON);
  } catch (error) {
    return ContentService.createTextOutput(
      JSON.stringify({ status: "error", message: error.toString() })
    ).setMimeType(ContentService.MimeType.JSON);
  }
}

function doPost(e) {
  // LockService mencegah dua request memproses nomor di waktu yang sama
  const lock = LockService.getScriptLock();
  try {
    lock.waitLock(10000); // Tunggu maksimal 10 detik

    const data = JSON.parse(e.postData.contents);
    if (!data || !data.kodePerusahaan) {
      throw new Error("Data tidak lengkap");
    }

    // 1. Generate Nomor Surat
    const nomorSurat = generateNomorSurat(
      data.kodePerusahaan,
      data.bulan,
      data.tahun
    );
    data.nomorSurat = nomorSurat;

    // 2. Simpan ke Google Sheet
    saveToSheet(data);

    // 3. Kirim Email Konfirmasi
    if (data.email) {
      sendConfirmationEmail(data, nomorSurat);
    }

    return ContentService.createTextOutput(
      JSON.stringify({
        status: "success",
        nomorSurat: nomorSurat,
      })
    ).setMimeType(ContentService.MimeType.JSON);
  } catch (error) {
    return ContentService.createTextOutput(
      JSON.stringify({
        status: "error",
        message: error.toString(),
      })
    ).setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}

// ============================================
// Core Functions
// ============================================

function generateNomorSurat(kodePerusahaan, bulan, tahun) {
  const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
  let sheet = spreadsheet.getSheetByName(kodePerusahaan);

  if (!sheet) return `${kodePerusahaan}/${bulan}/${tahun}/001`;

  const data = sheet.getDataRange().getValues();
  let counter = 0;

  // Indeks Kolom (Array mulai dari 0):
  // F = Bulan (Index 5), G = Tahun (Index 6)
  for (let i = 1; i < data.length; i++) {
    const rowBulan = data[i][5];
    const rowTahun = data[i][6];

    if (rowBulan === bulan && String(rowTahun) === String(tahun)) {
      counter++;
    }
  }

  const nomorUrut = String(counter + 1).padStart(3, "0");
  return `${kodePerusahaan}/${bulan}/${tahun}/${nomorUrut}`;
}

function saveToSheet(data) {
  const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
  let sheet = spreadsheet.getSheetByName(data.kodePerusahaan);

  if (!sheet) {
    sheet = spreadsheet.insertSheet(data.kodePerusahaan);
    const headers = [
      "No",
      "Timestamp",
      "Nomor Surat",
      "Kode Perusahaan",
      "Kode Tujuan",
      "Bulan",
      "Tahun",
      "Perihal",
      "Tujuan",
      "Letak Arsip",
      "Requestor",
    ];
    const headerRange = sheet.getRange(1, 1, 1, headers.length);
    headerRange
      .setValues([headers])
      .setBackground("#00B050")
      .setFontColor("#FFFFFF")
      .setFontWeight("bold")
      .setHorizontalAlignment("center");
    sheet.setFrozenRows(1);
  }

  const timestamp = new Date().toLocaleString("id-ID", {
    timeZone: "Asia/Jakarta",
  });
  const lastRow = sheet.getLastRow();

  sheet.appendRow([
    lastRow, // Kolom No
    timestamp,
    data.nomorSurat,
    data.kodePerusahaan,
    data.kodeTujuan,
    data.bulan,
    data.tahun,
    data.perihal,
    data.tujuan,
    data.letakArsip,
    data.requestor,
  ]);

  // Styling: Auto resize dan Alternating colors
  sheet.autoResizeColumns(1, 11);
}

function sendConfirmationEmail(data, nomorSurat) {
  try {
    const subject = `Konfirmasi Request Nomor Surat - ${nomorSurat}`;
    const htmlBody = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; border: 1px solid #ddd; border-radius: 8px;">
        <div style="background: #00B050; color: white; padding: 20px; text-align: center;">
          <h2>Request Berhasil!</h2>
        </div>
        <div style="padding: 20px;">
          <p>Yth. <b>${data.requestor}</b>,</p>
          <p>Nomor surat Anda telah diterbitkan:</p>
          <div style="background: #f4f4f4; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; color: #00B050; border: 1px dashed #00B050;">
            ${nomorSurat}
          </div>
          <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr><td style="padding: 8px; border-bottom: 1px solid #eee;"><b>Perihal</b></td><td>${
              data.perihal
            }</td></tr>
            <tr><td style="padding: 8px; border-bottom: 1px solid #eee;"><b>Tujuan</b></td><td>${
              data.tujuan
            }</td></tr>
            <tr><td style="padding: 8px; border-bottom: 1px solid #eee;"><b>Arsip</b></td><td>${
              data.letakArsip || "-"
            }</td></tr>
          </table>
        </div>
        <div style="background: #eee; padding: 10px; text-align: center; font-size: 11px;">
          &copy; 2025 SJP Holding - Sistem Penomoran Otomatis
        </div>
      </div>`;

    GmailApp.sendEmail(data.email, subject, "", { htmlBody: htmlBody });
  } catch (e) {
    Logger.log("Email gagal dikirim: " + e.message);
  }
}

// ============================================
// Utilities
// ============================================

function getCompanyStats() {
  const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
  const stats = {};
  COMPANY_CODES.forEach((code) => {
    const sheet = spreadsheet.getSheetByName(code);
    stats[code] = sheet ? Math.max(0, sheet.getLastRow() - 1) : 0;
  });
  return stats;
}

function setupAllCompanySheets() {
  const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
  COMPANY_CODES.forEach((code) => {
    if (!spreadsheet.getSheetByName(code)) {
      const sheet = spreadsheet.insertSheet(code);
      const headers = [
        "No",
        "Timestamp",
        "Nomor Surat",
        "Kode Perusahaan",
        "Kode Tujuan",
        "Bulan",
        "Tahun",
        "Perihal",
        "Tujuan",
        "Letak Arsip",
        "Requestor",
      ];
      sheet
        .getRange(1, 1, 1, headers.length)
        .setValues([headers])
        .setBackground("#00B050")
        .setFontColor("#FFFFFF")
        .setFontWeight("bold");
      sheet.setFrozenRows(1);
    }
  });
  return "Setup selesai!";
}
