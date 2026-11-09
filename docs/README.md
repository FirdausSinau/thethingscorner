# Dokumentasi Proyek — The Things Corner

**Update terakhir:** 9 November 2026

| Dokumen | Versi | Isi |
|---|---|---|
| `docs/PRD Fase 1 - The Things Corner.md` | 0.2 | Kebutuhan produk, role, fitur, aturan bisnis, acceptance criteria |
| `docs/erd.md` | 0.1 | ERD, relasi, constraint, index, enum status |
| `docs/technical-design-fase-1.md` | 0.1 | Arsitektur, state machine, route, authorization, integrasi, deployment |
| `docs/backend-overview.md` | 0.1 | Alur backend, folder, service, transaksi, keamanan, jalur belajar backend |
| `docs/decisions.md` | 0.2 | Decision log dan asumsi sementara |
| `docs/design-system.md` | 0.1 | Token warna, tipografi, komponen, aksesibilitas |
| `docs/wireframes.md` | 0.1 | Wireframe pembeli dan admin |
| `docs/implementation-plan-fase-1.md` | 0.1 | Rencana per slice, test, commit, deployment |
| `docs/team-workflow.md` | 0.2 | Pembagian tugas per domain, branch, PR, kepemilikan folder, jalur backend |
| `docs/setup-local.md` | 1.1 | Setup laptop tim, PostgreSQL, akun demo, update harian, troubleshooting 505 |
| `docs/data-request.md` | 0.1 | Daftar data yang perlu diminta ke client |
| `docs/data/README.md` | 0.1 | Penjelasan sample dataset dan urutan import |
| `docs/data/*.csv` | 0.1 | Data dummy dan template CSV |
| `Dokumen Kebutuhan Awal dan Opsi Solusi - The Things Corner (RFP) v0.3.docx` | 0.3 | Dokumen client: kebutuhan, opsi provider, biaya, fase |
| `Panduan Persiapan Client dan Developer - The Things Corner.docx` | 0.2 | Panduan persiapan client/developer dan keputusan fase |
| `Format Deskripsi Topik dan Kebutuhan Awal Proyek - The Things Corner.docx` | — | Form tugas yang sudah diisi |

## Urutan membaca

1. RFP v0.3 — konteks client dan pilihan solusi.
2. PRD Fase 1 — apa yang dibangun.
3. ERD — data dan relasi.
4. Technical design — bagaimana membangun.
5. Decision log — asumsi dan keputusan yang belum dikunci.
6. Design system + wireframes — arah UI.
7. Implementation plan — urutan pengerjaan.
8. Backend overview — cara kerja backend dan jalur belajar untuk anggota tim.
9. Sample data — contoh untuk pengujian.
10. Panduan persiapan — apa yang perlu disiapkan client/developer.

## Status

- Fase 1 sudah disepakati secara lingkup.
- Database: PostgreSQL 16+.
- Provider Fase 1: Midtrans/Xendit, Biteship, `wa.me`, local storage + Cloudflare.
- Provider Fase 2: Shopee API, WA API, Google Maps, analytics, Cloudinary, Sentry, PDF, CAPTCHA.
- Sample data masih dummy; data asli client menyusul.
- Implementasi: setup Laravel + Git selesai (`35545cc`). Slice 1 auth/role selesai (`990ea92`, `91280d5`). Slice 2 katalog belum.
