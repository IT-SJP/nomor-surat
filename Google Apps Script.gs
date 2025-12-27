/**
 * Google Apps Script untuk menerima data dari form Request Nomor Surat
 *
 * Setup:
 * 1. Buat Google Sheet kosong dengan nama "Request Nomor Surat"
 * 2. Copy ID sheet ke variable SHEET_ID di bawah
 * 3. Deploy script ini sebagai Web App
 * 4. Copy URL deployment ke index.js di variable GOOGLE_APPS_SCRIPT_URL
 *
 * OTOMATIS:
 * - Script akan auto-create 12 sheet untuk setiap kode perusahaan
 * - Header otomatis dibuat di setiap sheet
 */

// ============================================
// KONFIGURASI - EDIT SESUAI KEBUTUHAN
// ============================================

// ID Google Sheet (dari URL: docs.google.com/spreadsheets/d/SHEET_ID_INI/edit)
const SHEET_ID = "15HvoRQMHRC_JhQadvD3IyE06e1sc7enLkAsO5yBrJs4";

// Daftar Kode Perusahaan (setiap kode akan punya sheet sendiri)
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
// Main Handler Function
// ============================================
function doPost(e) {
  try {
    const data = JSON.parse(e.postData.contents);

    // Validasi data
    if (!data || typeof data !== "object") {
      return ContentService.createTextOutput(
        JSON.stringify({
          status: "error",
          message: "Data tidak valid",
        })
      ).setMimeType(ContentService.MimeType.JSON);
    }

    // Generate nomor surat
    const nomorSurat = generateNomorSurat(
      data.kodePerusahaan,
      data.bulan,
      data.tahun
    );

    // Tambah nomor surat ke data
    data.nomorSurat = nomorSurat;

    // Simpan ke Google Sheet
    saveToSheet(data);

    return ContentService.createTextOutput(
      JSON.stringify({
        status: "success",
        message: "Data berhasil disimpan",
        nomorSurat: nomorSurat,
      })
    ).setMimeType(ContentService.MimeType.JSON);
  } catch (error) {
    return ContentService.createTextOutput(
      JSON.stringify({
        status: "error",
        message: "Error: " + error.toString(),
      })
    ).setMimeType(ContentService.MimeType.JSON);
  }
}

// ============================================
// Save to Google Sheet
// ============================================
function saveToSheet(data) {
  try {
    const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
    const sheetName = data.kodePerusahaan; // Nama sheet = Kode Perusahaan
    let sheet = spreadsheet.getSheetByName(sheetName);

    // Auto-create sheet jika belum ada
    if (!sheet) {
      sheet = spreadsheet.insertSheet(sheetName);

      // Auto-create header row
      const headers = [
        "No",
        "Timestamp",
        "Nomor Surat",
        "Kode Tujuan",
        "Bulan",
        "Tahun",
        "Perihal",
        "Tujuan",
        "Letak Arsip",
        "Requestor",
      ];

      // Set header dengan formatting
      const headerRange = sheet.getRange(1, 1, 1, headers.length);
      headerRange.setValues([headers]);
      headerRange.setBackground("#00B050");
      headerRange.setFontColor("#FFFFFF");
      headerRange.setFontWeight("bold");
      headerRange.setHorizontalAlignment("center");

      // Freeze header row
      sheet.setFrozenRows(1);
    }

    // Get row number (untuk kolom No)
    const lastRow = sheet.getLastRow();
    const rowNumber = lastRow; // karena row 1 = header

    // Format timestamp yang readable
    const timestamp = new Date(data.timestamp).toLocaleString("id-ID", {
      timeZone: "Asia/Jakarta",
    });

    // Append data ke sheet
    sheet.appendRow([
      rowNumber,
      timestamp,
      data.nomorSurat,
      data.kodeTujuan,
      data.bulan,
      data.tahun,
      data.perihal,
      data.tujuan,
      data.letakArsip,
      data.requestor,
    ]);

    // Auto-resize semua kolom
    for (let i = 1; i <= 10; i++) {
      sheet.autoResizeColumn(i);
    }

    // Set alternating row colors untuk readability
    const dataRange = sheet.getRange(2, 1, sheet.getLastRow() - 1, 10);
    sheet.setRowHeights(2, sheet.getLastRow() - 1, 25);
  } catch (error) {
    Logger.log("Error saving to sheet: " + error);
    throw new Error("Gagal menyimpan ke sheet: " + error);
  }
}

// ============================================
// Generate Nomor Surat
// ============================================
function generateNomorSurat(kodePerusahaan, bulan, tahun) {
  try {
    const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
    let sheet = spreadsheet.getSheetByName(kodePerusahaan);

    // Jika sheet belum ada, nomor pertama
    if (!sheet) {
      return `${kodePerusahaan}/${bulan}/${tahun}/001`;
    }

    // Hitung jumlah data untuk bulan dan tahun ini di sheet kode perusahaan
    const data = sheet.getDataRange().getValues();
    let counter = 0;

    // Loop mulai dari row 2 (row 1 = header)
    for (let i = 1; i < data.length; i++) {
      const rowBulan = data[i][4]; // Column E (Bulan)
      const rowTahun = data[i][5]; // Column F (Tahun)

      if (rowBulan === bulan && rowTahun.toString() === tahun.toString()) {
        counter++;
      }
    }

    // Format nomor surat dengan 3 digit
    const nomorUrut = String(counter + 1).padStart(3, "0");
    const nomorSurat = `${kodePerusahaan}/${bulan}/${tahun}/${nomorUrut}`;

    return nomorSurat;
  } catch (error) {
    Logger.log("Error generating nomor surat: " + error);
    return `${kodePerusahaan}/${bulan}/${tahun}/001`;
  }
}

// ============================================
// Send Confirmation Email
// ============================================
function sendConfirmationEmail(data, nomorSurat) {
  try {
    const email = data.email;
    const subject = `Konfirmasi Request Nomor Surat - ${nomorSurat}`;

    const htmlBody = `
        <html>
            <body style="font-family: Arial, sans-serif; color: #333;">
                <div style="max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                    <div style="background: linear-gradient(135deg, #00B050 0%, #0078D4 100%); color: white; padding: 20px; text-align: center;">
                        <h2 style="margin: 0;">Sistem Request Nomor Surat</h2>
                        <p style="margin: 5px 0 0 0; font-size: 14px;">SJP Holding</p>
                    </div>
                    
                    <div style="padding: 25px;">
                        <p>Yth. <strong>${data.requestor}</strong>,</p>
                        
                        <p style="margin: 20px 0;">Terima kasih telah mengajukan request nomor surat. Berikut adalah detail permohonan Anda:</p>
                        
                        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                            <tr style="background: #f5f5f5;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 40%;">Nomor Surat</td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><strong style="color: #00B050;">${nomorSurat}</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Kode Perusahaan</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${
                                  data.kodePerusahaan
                                }</td>
                            </tr>
                            <tr style="background: #f5f5f5;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Kode Tujuan</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${
                                  data.kodeTujuan
                                }</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Perihal</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${
                                  data.perihal
                                }</td>
                            </tr>
                            <tr style="background: #f5f5f5;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Bulan/Tahun</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${
                                  data.bulan
                                }/${data.tahun}</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Letak Arsip</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${
                                  data.letakArsip || "-"
                                }</td>
                            </tr>
                            <tr style="background: #f5f5f5;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Waktu Request</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">${new Date().toLocaleString(
                                  "id-ID",
                                  { timeZone: "Asia/Jakarta" }
                                )}</td>
                            </tr>
                        </table>
                        
                        <div style="background: #e8f5e9; border-left: 4px solid #00B050; padding: 15px; margin: 20px 0; border-radius: 4px;">
                            <p style="margin: 0; color: #2e7d32;"><strong>✓ Request Berhasil!</strong> Nomor surat Anda telah terdaftar di sistem.</p>
                        </div>
                        
                        <p style="margin-top: 20px; color: #666; font-size: 13px;">
                            <strong>Catatan:</strong> Email ini dikirim otomatis. Untuk informasi lebih lanjut, silahkan hubungi bagian administrasi.
                        </p>
                    </div>
                    
                    <div style="background: #f5f5f5; padding: 15px; text-align: center; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                        <p style="margin: 0;">&copy; 2025 Sistem Request Nomor Surat - SJP Holding</p>
                    </div>
                </div>
            </body>
        </html>
        `;

    GmailApp.sendEmail(email, subject, "Nomor Surat: " + nomorSurat, {
      htmlBody: htmlBody,
    });
  } catch (error) {
    Logger.log("Error sending email: " + error);
    // Jangan throw error agar form tetap tersimpan meskipun email gagal
  }
}

// ============================================
// Utility: Setup All Company Sheets
// ============================================
function setupAllCompanySheets() {
  try {
    const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
    let createdCount = 0;
    
    COMPANY_CODES.forEach(code => {
      let sheet = spreadsheet.getSheetByName(code);
      
      // Jika sheet belum ada, buat baru
      if (!sheet) {
        sheet = spreadsheet.insertSheet(code);
        
        // Auto-create header row
        const headers = [
          'No',
          'Timestamp',
          'Nomor Surat',
          'Kode Tujuan',
          'Bulan',
          'Tahun',
          'Perihal',
          'Tujuan',
          'Letak Arsip',
          'Requestor',
        ];
        
        // Set header dengan formatting
        const headerRange = sheet.getRange(1, 1, 1, headers.length);
        headerRange.setValues([headers]);
        headerRange.setBackground('#00B050');
        headerRange.setFontColor('#FFFFFF');
        headerRange.setFontWeight('bold');
        headerRange.setHorizontalAlignment('center');
        
        // Freeze header row
        sheet.setFrozenRows(1);
        
        // Set column widths
        sheet.setColumnWidth(1, 50);   // No
        sheet.setColumnWidth(2, 150);  // Timestamp
        sheet.setColumnWidth(3, 180);  // Nomor Surat
        sheet.setColumnWidth(4, 120);  // Kode Tujuan
        sheet.setColumnWidth(5, 80);   // Bulan
        sheet.setColumnWidth(6, 80);   // Tahun
        sheet.setColumnWidth(7, 250);  // Perihal
        sheet.setColumnWidth(8, 200);  // Tujuan
        sheet.setColumnWidth(9, 150);  // Letak Arsip
        sheet.setColumnWidth(10, 150); // Requestor
        
        createdCount++;
        Logger.log(`Sheet created: ${code}`);
      } else {
        Logger.log(`Sheet already exists: ${code}`);
      }
    });
    
    // Hapus Sheet1 default jika ada
    const defaultSheet = spreadsheet.getSheetByName('Sheet1');
    if (defaultSheet && spreadsheet.getSheets().length > 1) {
      spreadsheet.deleteSheet(defaultSheet);
      Logger.log('Default Sheet1 deleted');
    }
    
    Logger.log(`Setup completed! Created ${createdCount} new sheets.`);
    return `Setup completed! Created ${createdCount} new sheets out of ${COMPANY_CODES.length} companies.`;
    
  } catch (error) {
    Logger.log('Error in setupAllCompanySheets: ' + error);
    throw error;
  }
}

// ============================================
// Test Function (gunakan untuk debug)
// ============================================
function testSubmission() {
  const testData = {
    timestamp: new Date().toISOString(),
    kodePerusahaan: "SJP",
    kodeTujuan: "Jakarta",
    bulan: "I",
    tahun: "2025",
    perihal: "Test Request Nomor Surat",
    tujuan: "Testing aplikasi request nomor surat",
    letakArsip: "Ruang Arsip B1",
    requestor: "Test User",
    email: "test@example.com",
    telepon: "081234567890"
  };

  const nomorSurat = generateNomorSurat(
    testData.kodePerusahaan,
    testData.bulan,
    testData.tahun
  );

  testData.nomorSurat = nomorSurat;

  saveToSheet(testData);
  sendConfirmationEmail(testData, nomorSurat);

  Logger.log("Test submission completed. Generated nomor: " + nomorSurat);
}
