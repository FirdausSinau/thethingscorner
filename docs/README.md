# Dokumentasi Teknis - thethingscorner

**Update terakhir:** 9 November 2026

Folder ini hanya berisi dokumentasi yang berkaitan dengan kode dan produk. Dokumen kerja tim, setup laptop, data request, dan file client ada di [`../project-management/`](../project-management/README.md).

---

## 1. Dokumen teknis

| Dokumen | Versi | Isi |
|---|---|---|
| [`PRD Fase 1 - The Things Corner.md`](PRD%20Fase%201%20-%20The%20Things%20Corner.md) | 0.2 | Kebutuhan produk, role, fitur, aturan bisnis, acceptance criteria |
| [`erd.md`](erd.md) | 0.4 | ERD, relasi, constraint, index, enum status |
| [`technical-design-fase-1.md`](technical-design-fase-1.md) | 0.1 | Arsitektur, state machine, route, authorization, integrasi, deployment |
| [`backend-overview.md`](backend-overview.md) | 0.1 | Alur backend, folder, service, transaksi, keamanan, jalur belajar backend |
| [`catalog-contract.md`](catalog-contract.md) | 2.1 | Kontrak schema produk, warna, varian, route, storage, dan status Slice 2 |
| [`decisions.md`](decisions.md) | 0.4 | Decision log dan asumsi sementara |
| [`design-system.md`](design-system.md) | 0.1 | Token warna, tipografi, komponen, aksesibilitas |
| [`wireframes.md`](wireframes.md) | 0.1 | Wireframe pembeli dan admin |
| [`implementation-plan-fase-1.md`](implementation-plan-fase-1.md) | 0.1 | Rencana per slice, test, commit, deployment |
| [`data/README.md`](data/README.md) | 0.1 | Penjelasan sample dataset dan urutan import |
| [`data/*.csv`](data/) | 0.1 | Data dummy dan template CSV |

---

## 2. Urutan membaca

1. PRD Fase 1 - apa yang dibangun.
2. ERD - data dan relasi.
3. Technical design - bagaimana membangun.
4. Backend overview - cara kerja backend dan jalur belajar.
5. Decision log - asumsi dan keputusan yang belum dikunci.
6. Design system + wireframes - arah UI.
7. Implementation plan - urutan pengerjaan per slice.
8. Sample data - contoh untuk pengujian.

Dokumen kerja tim:

1. [`../project-management/setup-local.md`](../project-management/setup-local.md) - menyiapkan laptop.
2. [`../project-management/team-workflow.md`](../project-management/team-workflow.md) - branch dan PR.
3. [`../project-management/work-plan.md`](../project-management/work-plan.md) - domain, slice, dan urutan kerja.
4. [`../project-management/database-rules.md`](../project-management/database-rules.md) - aturan Neon dan migrasi.
5. [`../project-management/data-request.md`](../project-management/data-request.md) - data yang perlu diminta ke client.

---

## 3. Status

- Fase 1 sudah disepakati secara lingkup.
- Database: PostgreSQL 16+ untuk lokal, Neon untuk database bersama.
- Provider Fase 1: Midtrans/Xendit, Biteship, `wa.me`, local storage + Cloudflare.
- Provider Fase 2: Shopee API, WA API, Google Maps, analytics, Cloudinary, Sentry, PDF, CAPTCHA.
- Sample data masih dummy; data asli client menyusul.
- Implementasi: setup Laravel + Git selesai (`35545cc`). Slice 1 auth/role selesai (`990ea92`, `91280d5`). Slice 2 katalog, detail produk, dan admin produk/warna/varian selesai; 38 test lulus.
