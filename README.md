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

#### 📋 Contoh Request Authentication

**POST `/login`**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**POST `/register`**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**POST `/logout`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**GET `/user`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

---

### 📊 Dashboard

| Method | Endpoint       | Fungsi                             |
|--------|----------------|------------------------------------|
| GET    | `/dashboard`   | Menampilkan ringkasan sistem GTD   |

#### 📋 Contoh Request Dashboard

**GET `/dashboard`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

---

### 🧩 Contexts

| Method | Endpoint             | Fungsi                    |
|--------|----------------------|---------------------------|
| GET    | `/contexts`          | List semua konteks        |
| POST   | `/contexts`          | Membuat konteks baru      |
| GET    | `/contexts/{id}`     | Detail konteks spesifik   |
| PUT    | `/contexts/{id}`     | Update konteks            |
| DELETE | `/contexts/{id}`     | Hapus konteks             |

#### 📋 Contoh Request Contexts

**GET `/contexts`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**POST `/contexts`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "name": "Office",
  "icon": "🏢",
  "color": "#3B82F6"
}
```

**GET `/contexts/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /contexts/1
```

**PUT `/contexts/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "name": "Home Office",
  "icon": "🏠",
  "color": "#10B981"
}
URL: /contexts/1
```

**DELETE `/contexts/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /contexts/1
```

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

#### 📋 Contoh Request Projects

**GET `/projects`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
Query Parameters (optional): ?status=active
```

**POST `/projects`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "title": "Website Redesign Project",
  "description": "Complete redesign of company website",
  "due_date": "2025-12-31",
  "status": "active"
}
```

**GET `/projects/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /projects/1
```

**PUT `/projects/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "title": "Website Redesign Project - Updated",
  "description": "Complete redesign with new features",
  "due_date": "2025-11-30",
  "status": "active"
}
URL: /projects/1
```

**DELETE `/projects/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /projects/1
```

**GET `/projects/{id}/next-actions`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /projects/1/next-actions
```

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

#### 📋 Contoh Request GTD Items

**GET `/items`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
Query Parameters (optional): ?type=inbox&status=active&context_id=1
```

**POST `/items`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "title": "Review project requirements",
  "description": "Go through the detailed requirements document",
  "type": "inbox",
  "due_date": "2025-02-15",
  "reminder_date": "2025-02-14",
  "energy_level": 2,
  "time_estimate": 60,
  "notes": "Important for project timeline",
  "project_id": 1,
  "context_id": 1,
  "waiting_for_person": null,
  "waiting_since": null
}
```

**GET `/items/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /items/1
```

**PUT `/items/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "title": "Review project requirements - Updated",
  "description": "Go through the detailed requirements document and create summary",
  "type": "next_action",
  "due_date": "2025-02-16",
  "reminder_date": "2025-02-15",
  "energy_level": 3,
  "time_estimate": 90,
  "notes": "Updated with additional scope",
  "project_id": 1,
  "context_id": 1
}
URL: /items/1
```

**DELETE `/items/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /items/1
```

**POST `/items/{id}/complete`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /items/1/complete
```

**POST `/items/{id}/clarify`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "type": "next_action",
  "context_id": 2,
  "energy_level": 3,
  "time_estimate": 45
}
URL: /items/1/clarify
```

**GET `/items/by-context/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /items/by-context/1
```

---

### 📥 Kategori Khusus GTD

| Endpoint           | Fungsi                          |
|--------------------|---------------------------------|
| GET `/inbox`       | Ambil semua item inbox          |
| GET `/next-actions`| Ambil semua next actions        |
| GET `/waiting-for` | Ambil semua waiting items       |
| GET `/someday-maybe`| Ambil semua someday/maybe      |
| GET `/reference`   | Ambil semua reference items     |

#### 📋 Contoh Request Kategori Khusus GTD

**GET `/inbox`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**GET `/next-actions`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**GET `/waiting-for`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**GET `/someday-maybe`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**GET `/reference`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

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

#### 📋 Contoh Request Weekly Reviews

**GET `/weekly-reviews`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

**POST `/weekly-reviews`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "review_date": "2025-01-19",
  "notes": "Weekly review for this week",
  "review_data": {
    "completed_projects": [
      {"id": 1, "title": "Website Setup", "completion_date": "2025-01-15"}
    ],
    "completed_items": [
      {"id": 5, "title": "Design homepage", "completion_date": "2025-01-16"}
    ],
    "pending_items": [
      {"id": 6, "title": "Review content", "status": "in_progress"}
    ],
    "new_projects": [
      {"title": "Mobile App Development", "description": "New mobile app project"}
    ],
    "areas_of_focus": [
      {"area": "Marketing", "status": "needs_attention"},
      {"area": "Development", "status": "on_track"}
    ],
    "accomplishments": [
      "Completed website design phase",
      "Client feedback integrated successfully"
    ],
    "challenges": [
      "Resource allocation needs improvement",
      "Timeline adjustment required"
    ],
    "next_week_priorities": [
      "Start mobile app wireframes",
      "Schedule client meeting",
      "Prepare development roadmap"
    ]
  }
}
```

**GET `/weekly-reviews/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /weekly-reviews/1
```

**PUT `/weekly-reviews/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body:
{
  "review_date": "2025-01-19",
  "notes": "Updated weekly review notes",
  "review_data": {
    "completed_projects": [
      {"id": 1, "title": "Website Setup", "completion_date": "2025-01-15"},
      {"id": 2, "title": "Logo Design", "completion_date": "2025-01-17"}
    ],
    "completed_items": [
      {"id": 5, "title": "Design homepage", "completion_date": "2025-01-16"},
      {"id": 7, "title": "Create contact form", "completion_date": "2025-01-18"}
    ],
    "accomplishments": [
      "Completed website design phase",
      "Logo design approved by client",
      "Contact form functionality implemented"
    ]
  }
}
URL: /weekly-reviews/1
```

**DELETE `/weekly-reviews/{id}`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
URL: /weekly-reviews/1
```

**GET `/weekly-reviews/current`**
```json
Header: Authorization: Bearer <your-token>
Body: (tidak ada body)
```

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
