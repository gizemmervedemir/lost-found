# 🧭 Lost & Found Platform

A modern web application to help users report, match, and recover lost items efficiently.

---

## 🔑 Features

- **👤 User Management**  
  Registration, login, profile editing, and avatar upload/removal.

- **📦 Item Listings**  
  Users can add lost or found items with descriptions and images.

- **🔗 Match System**  
  Matches lost items with found items and enables private chat between users.

- **🔔 Real-time Notifications**  
  Alerts for new matches, chat messages, and system updates.

- **🚩 Reporting System**  
  Users can report others within chats, alerting administrators.

- **🛠️ Admin Dashboard**  
  Admins can manage users, items, matches, reports, and delete inappropriate content.

---

## 🧰 Technologies Used

- **PHP** – Backend
- **MySQL** – Database
- **Bootstrap 5** – Frontend UI
- **JavaScript** – Interactivity
- **Chart.js / ApexCharts** – (for analytics in admin panel)

---

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/lost-found.git
````

2. **Set up a local server (XAMPP / MAMP / LAMP).**

3. **Create a MySQL database** and run either:

   * `createdb.php` and `createtables.php`, or
   * Import the provided `.sql` file manually.

4. **Update DB credentials** inside:
   `includes/db.php`

5. **Ensure the following folders exist and are writable:**

   ```
   /uploads/profiles/      ← for user avatars  
   /uploads/items/         ← for item images  
   /uploads/qr_codes/      ← for QR codes
   ```

6. **Launch the server** and visit:
   `http://localhost/lost-found`

---

## 🚀 Usage

* Register and log in as a user.
* Add lost/found items with descriptions and images.
* View possible matches and start a conversation.
* Admin users can view reports, delete items, and moderate content.

---

## 🗂️ Project Structure

```
📁 includes/        → Shared PHP files (db, auth, templates, functions)
📁 uploads/         → User avatars, item images, QR codes
📁 admin/           → Admin dashboard and moderation tools
📁 assets/          → CSS, JavaScript, icons, images
```

---

## ❗ Troubleshooting

* Make sure `/uploads/` folders are **writable** (`chmod 777` on Unix).
* Double-check your database connection in `db.php`.
* Run migration scripts if tables are missing.
* Clear browser cache if profile images aren't updating.

---

## 🤝 Contribution

Pull requests are welcome!
For major features, please open an issue to propose changes before submitting a PR.

---

## 📄 License

This project is licensed under the **MIT License**.

````

