# Dokumentasi Kerja Tim dan Client

Folder ini berisi dokumen yang tidak berkaitan langsung dengan arsitektur kode:

- pembagian kerja dan urutan slice;
- alur branch dan PR;
- setup laptop;
- daftar data yang diminta ke client;
- dokumen client dalam format `.docx`.

Dokumentasi teknis ada di [`../docs/`](../docs/README.md).

---

## 1. Dokumen kerja tim

| Dokumen | Versi | Isi |
|---|---|---|
| [`work-plan.md`](work-plan.md) | 1.0 | Domain owner, pembagian per slice, urutan batch, checklist integrasi |
| [`database-rules.md`](database-rules.md) | 1.1 | Aturan Neon, migrasi, branch database, backup, credential, insiden schema |
| [`team-workflow.md`](team-workflow.md) | 0.3 | Branch, PR, kepemilikan folder, aturan konflik, definition of done |
| [`setup-local.md`](setup-local.md) | 1.3 | Setup laptop, PostgreSQL/Neon, akun demo, update harian, troubleshooting 505 |
| [`data-request.md`](data-request.md) | 0.1 | Checklist data yang perlu diminta ke client |

Urutan membaca untuk anggota baru:

1. `setup-local.md` untuk menyiapkan laptop.
2. `team-workflow.md` untuk aturan branch dan PR.
3. `work-plan.md` untuk tahu bagian dan urutan pekerjaan.
4. `database-rules.md` sebelum menyentuh database bersama.
5. `../docs/backend-overview.md` untuk memahami backend.
6. `../docs/PRD Fase 1 - The Things Corner.md` untuk kebutuhan produk.

---

## 2. Dokumen client

File `.docx` disimpan di `client-docs/`:

| File | Isi |
|---|---|
| `Dokumen Kebutuhan Awal dan Opsi Solusi - The Things Corner (RFP) v0.3.docx` | Kebutuhan client, opsi provider, biaya, fase |
| `Panduan Persiapan Client dan Developer - The Things Corner.docx` | Persiapan client/developer dan keputusan fase |
| `Format Deskripsi Topik dan Kebutuhan Awal Proyek - The Things Corner.docx` | Form tugas yang sudah diisi |

Catatan:

- File `.docx` dan `.pdf` tidak masuk Git karena berisi dokumen client.
- Simpan salinan di penyimpanan tim bila anggota lain membutuhkan.
- Jangan menaruh data pelanggan asli di repository.

---

## 3. Aturan singkat

- Dokumen teknis baru masuk ke `docs/`.
- Dokumen kerja tim, setup, data request, dan client masuk ke `project-management/`.
- Setiap perubahan keputusan produk atau schema harus memperbarui dokumen terkait di `docs/`.
- Setiap perubahan pembagian kerja harus memperbarui `work-plan.md` dan `team-workflow.md`.
