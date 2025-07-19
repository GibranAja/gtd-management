# GTD Management System API Documentation

Dokumentasi API untuk sistem manajemen **GTD (Getting Things Done)** yang memungkinkan pengguna mengorganisir tugas, proyek, dan konteks sesuai metodologi produktivitas GTD.

---

## 🧭 Overview

API ini menyediakan layanan backend untuk aplikasi manajemen GTD, mencakup:

- Pengelolaan tugas dalam kategori: `inbox`, `next actions`, `waiting for`, `someday/maybe`
- Manajemen proyek dan konteks
- Fitur **Weekly Review** untuk menjaga sistem tetap up-to-date

### 🌐 Base URL

```
https://gtd-api.kuadrattech.my.id/api/
```

---

## 🔐 Authentication

API menggunakan **Laravel Sanctum**. Setelah login, semua request yang dilindungi harus menyertakan **Bearer Token** di header:

```
Authorization: Bearer <token>
```

### 🔁 Alur Autentikasi

```
Registrasi/Login → Mendapatkan Token → Gunakan Token untuk Akses Endpoints
```

---

## 🔄 Flow Sistem

1. **Authentication Flow**  
   Pengguna melakukan registrasi/login untuk mendapatkan token akses.

2. **Data Management Flow**  
   Buat konteks → Buat proyek (opsional) → Buat item/tugas → Kelola status item

3. **GTD Workflow**  
   Inbox → Clarify → Organize ke kategori sesuai (next actions, waiting for, someday/maybe) → Weekly Review

---

## 📌 Endpoints

### 🧑‍💼 Authentication

| Method | Endpoint     | Fungsi                              |
|--------|--------------|-------------------------------------|
| POST   | `/login`     | Login & ambil token akses           |
| POST   | `/register`  | Registrasi pengguna baru            |
| POST   | `/logout`    | Logout & hapus token aktif          |
| GET    | `/user`      | Ambil data pengguna yang sedang login |

---

### 📊 Dashboard

| Method | Endpoint       | Fungsi                             |
|--------|----------------|------------------------------------|
| GET    | `/dashboard`   | Menampilkan ringkasan sistem GTD   |

---

### 🧩 Contexts

| Method | Endpoint             | Fungsi                    |
|--------|----------------------|---------------------------|
| GET    | `/contexts`          | List semua konteks        |
| POST   | `/contexts`          | Membuat konteks baru      |
| GET    | `/contexts/{id}`     | Detail konteks spesifik   |
| PUT    | `/contexts/{id}`     | Update konteks            |
| DELETE | `/contexts/{id}`     | Hapus konteks             |

---

### 📁 Projects

| Method | Endpoint                     | Fungsi                         |
|--------|------------------------------|--------------------------------|
| GET    | `/projects`                  | Ambil semua proyek             |
| POST   | `/projects`                  | Buat proyek baru               |
| GET    | `/projects/{id}`             | Detail proyek                  |
| PUT    | `/projects/{id}`             | Update proyek                  |
| DELETE | `/projects/{id}`             | Hapus proyek                   |
| GET    | `/projects/{id}/next-actions`| Ambil next actions proyek      |

---

### 📝 GTD Items

| Method | Endpoint                    | Fungsi                            |
|--------|-----------------------------|-----------------------------------|
| GET    | `/items`                    | Ambil semua item dengan filter    |
| POST   | `/items`                    | Buat item baru                    |
| GET    | `/items/{id}`              | Detail item spesifik              |
| PUT    | `/items/{id}`              | Update item                       |
| DELETE | `/items/{id}`              | Hapus item                        |
| POST   | `/items/{id}/complete`     | Tandai item sebagai selesai       |
| POST   | `/items/{id}/clarify`      | Ubah tipe item (clarify process)  |
| GET    | `/items/by-context/{id}`   | Ambil item berdasarkan konteks    |

#### 🔍 Filter Items

- `type`: inbox, next_action, waiting_for, someday_maybe, reference  
- `status`: active, completed, cancelled  
- `context_id`: ID konteks  

---

### 📥 Kategori Khusus GTD

| Endpoint           | Fungsi                          |
|--------------------|---------------------------------|
| GET `/inbox`       | Ambil semua item inbox          |
| GET `/next-actions`| Ambil semua next actions        |
| GET `/waiting-for` | Ambil semua waiting items       |
| GET `/someday-maybe`| Ambil semua someday/maybe      |
| GET `/reference`   | Ambil semua reference items     |

---

### 🔁 Weekly Reviews

| Method | Endpoint                        | Fungsi                        |
|--------|----------------------------------|-------------------------------|
| GET    | `/weekly-reviews`               | History review (paginasi)     |
| POST   | `/weekly-reviews`               | Buat weekly review baru       |
| GET    | `/weekly-reviews/{id}`          | Detail review spesifik        |
| PUT    | `/weekly-reviews/{id}`          | Update review                 |
| DELETE | `/weekly-reviews/{id}`          | Hapus review                  |
| GET    | `/weekly-reviews/current`       | Review minggu ini (atau template) |

---

## 🧱 Relasi Antar Endpoint

- Autentikasi **harus dilakukan** sebelum akses endpoint lainnya.
- Buat **konteks terlebih dahulu** untuk kategorisasi item yang baik.
- Proyek **opsional**, tapi membantu grouping item.
- Item bisa dipindahkan antar kategori via endpoint `/clarify`.
- Weekly review **dianjurkan** dilakukan rutin setiap minggu.

---

## 🕒 Date Format

Format tanggal mengikuti standar **ISO 8601** dalam timezone Asia/Jakarta (WIB).

Contoh response tanggal:

```json
{
  "formatted": "19 Juli 2025",
  "iso": "2025-07-19T12:34:56+07:00",
  "timestamp": 1752912896,
  "relative": "2 hours ago"
}
```

---

## ⛔ Error Responses

| Status | Makna                        | Contoh Response                                |
|--------|------------------------------|------------------------------------------------|
| 401    | Unauthorized                 | `{ "message": "Unauthenticated." }`           |
| 403    | Forbidden                    | `{ "success": false, "message": "Forbidden" }`|
| 404    | Not Found                    | `{ "success": false, "message": "Not found" }`|
| 422    | Validation Failed            | `{ "errors": { "field": ["message"] } }`      |
| 500    | Internal Server Error        | `{ "success": false, "message": "Error" }`    |
| 429    | Too Many Requests (Rate Limit)|                                              |

---

## 🚦 Rate Limiting

Menggunakan rate limiting standar Laravel. Jika melebihi batas, response akan:

```
Status: 429 Too Many Requests
```

---

## 📈 Change Log

**Version 1.0.0** - Initial Release

- Autentikasi dengan Laravel Sanctum
- CRUD untuk: Contexts, Projects, Items, Weekly Reviews
- Endpoint khusus untuk dashboard
- Dokumentasi error response & struktur API
