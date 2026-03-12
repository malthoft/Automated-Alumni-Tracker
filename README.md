# 🎓 Automated Alumni Tracker (Sistem Pelacakan Alumni Otomatis)

**Identitas Pembuat:**
- **Nama:** Muhammad Althof Taqiyyuddin
- **NIM:** 202310370311023
- **Kelas:** Rekayasa Kebutuhan D
- **Instansi:** Universitas Muhammadiyah Malang

---

Sistem Informasi Pelacakan Alumni Otomatis berbasis web yang dirancang untuk membantu instansi/perguruan tinggi dalam memperbarui data pekerjaan dan rekam jejak alumni secara otomatis dari sumber publik (LinkedIn, Google Scholar, ResearchGate, dll). 

Sistem ini mengubah pendekatan pelacakan alumni dari metode pasif (menunggu pengisian kuesioner/tracer study) menjadi metode proaktif dengan memanfaatkan teknik ekstraksi sinyal identitas digital, *confidence scoring*, dan *cross-validation* untuk meminimalisir kesalahan identifikasi (disambiguasi).

🔗 **Live Demo:** [althof.site/alumnitracker](http://althof.site/alumnitracker)

---

## 🧠 Algoritma Disambiguasi & Logika Scoring

Bagian krusial dari sistem ini adalah fitur **Disambiguasi**, yaitu kemampuan sistem untuk membedakan alumni target dengan orang lain di internet yang memiliki nama yang sama. Sistem menggunakan logika *scoring* (pembobotan nilai) untuk setiap kandidat yang ditemukan dengan aturan sebagai berikut:

1. **Kecocokan Nama (+40 Poin):** Diberikan jika nama kandidat di sumber publik cocok dengan salah satu variasi nama target (misal: "Aris Sudarsono", "Aris S.").
2. **Kecocokan Afiliasi (+30 Poin):** Diberikan jika pada profil kandidat terdapat kata kunci institusi asal (contoh: "UMM" atau "Universitas Muhammadiyah Malang").
3. **Kecocokan Timeline (+10 Poin):** Diberikan jika tahun aktif/bekerja kandidat logis (yaitu lebih besar atau sama dengan tahun lulus dari kampus).
4. **Kecocokan Lokasi (+10 Poin):** Diberikan jika lokasi kandidat sesuai dengan basis kota kampus atau riwayat asal target.
5. **Validasi Silang / Cross-Validation:**
   - **Konsisten (+15 Poin):** Jika kandidat ditemukan di lebih dari satu sumber (misal: LinkedIn dan Google Scholar) dan memiliki data lokasi atau jabatan yang konsisten.
   - **Bertentangan (-10 Poin):** Jika kandidat ditemukan di berbagai sumber namun profilnya bertentangan (misal jabatan dan kota berbeda jauh), skor dikurangi karena tingginya indikasi orang yang berbeda (*False Positive*).

**Penetapan Status Otomatis (Threshold):**
- **Skor ≥ 80:** Sistem otomatis percaya dan menetapkan status `Teridentifikasi dari sumber publik`.
- **Skor 50 - 79:** Sistem ragu dan menetapkan status `Perlu Verifikasi Manual`, menahan data di Dashboard untuk ditinjau oleh Admin (Approve/Reject).
- **Skor < 50:** Kandidat dianggap tidak relevan dan diabaikan (`Belum ditemukan di sumber publik`).

---

## ⚙️ Cara Kerja Logika Sistem (10 Tahapan)

1. **Pembuatan Profil Target**: Memecah nama menjadi beberapa variasi dan menggabungkan kata kunci afiliasi kampus.
2. **Prioritas Sumber Eksternal**: Memfilter pencarian hanya pada sumber publik yang diizinkan dan bereputasi.
3. **Job Scheduler**: Pemrosesan *batch* otomatis untuk alumni dengan status "Belum Dilacak".
4. **Query Generator**: Merakit string kueri pencarian secara presisi dan menyimpannya sebagai log audit.
5. **Ekstraksi Sinyal Identitas**: Menarik atribut penting seperti Jabatan, Afiliasi, Lokasi, dan Tahun Aktif.
6. **Disambiguasi**: Menjalankan algoritma perhitungan *scoring* di atas.
7. **Verifikasi Silang**: Membandingkan konsistensi data lintas platform.
8. **Penetapan Status Dinamis**: Menetapkan status berdasarkan *threshold*.
9. **Penyimpanan Jejak Bukti**: Mendokumentasikan tautan (URL) dan detail kandidat sebagai referensi validasi Admin.
10. **Kontrol Intervensi Admin**: Menyediakan *Dashboard* bagi Admin untuk menyetujui atau menolak hasil pelacakan.

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, CSS3 (Native, Vanilla styling)
- **Backend:** PHP (Native/Procedural)
- **Database:** MySQL
- **Architecture:** Client-Server dengan simulasi fungsi Scheduler & Web Scraping

---

## 🧪 Pengujian Sistem (System Testing)

Pengujian ini dilakukan untuk memastikan bahwa sistem memenuhi aspek kualitas fungsional yang telah ditetapkan dalam rancangan dokumen *Use Case* dan spesifikasi Kebutuhan Sistem.

| ID | Skenario Pengujian (Test Case) | Aspek Kualitas | Hasil yang Diharapkan | Status |
|:---|:---|:---|:---|:---:|
| **TC-01** | Menjalankan *Job* Pelacakan untuk alumni berstatus "Belum Dilacak". | **Otomatisasi & Kinerja** | Sistem menghasilkan variasi nama, membuat kueri, dan memproses semua target tanpa intervensi manual per baris data. | ✅ Pass |
| **TC-02** | Mengevaluasi kandidat dengan nama yang sama persis namun beda kampus/lokasi. | **Akurasi & Disambiguasi** | Sistem memotong poin kandidat tersebut; skor jatuh di bawah 80% sehingga mencegah *False Positive*. | ✅ Pass |
| **TC-03** | Memproses kandidat yang ditemukan di 2 sumber berbeda (misal: LinkedIn & Scholar). | **Validasi Silang (Cross-Validation)** | Sistem menganalisis konsistensi jabatan/lokasi. Jika konsisten, skor +15. Jika bertentangan, skor -10. | ✅ Pass |
| **TC-04** | Menyimpan riwayat pencarian sistem. | **Transparansi & Auditabilitas** | String pencarian (misal: `"Nama" + "UMM" + site:linkedin.com`) tersimpan utuh di tabel `query_log` berserta *timestamp*. | ✅ Pass |
| **TC-05** | Admin memberikan tindakan *Approve* pada kandidat berskor sedang (50-79%). | **Kontrol Pengguna (Usability)** | Status di tabel `alumni` langsung berubah menjadi "Teridentifikasi dari sumber publik". Jejak bukti tetap tersimpan. | ✅ Pass |
| **TC-06** | Admin memberikan tindakan *Reject* pada kandidat berskor sedang. | **Integritas Data** | Sistem menghapus `jejak_bukti` yang salah dan mereset status alumni menjadi "Belum ditemukan di sumber publik". | ✅ Pass |

---
*Proyek ini dikembangkan untuk memenuhi penugasan mata kuliah Rekayasa Kebutuhan.*
