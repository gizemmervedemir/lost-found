Lost & Found Platform

This is a Lost & Found web application that helps users report, match, and recover lost items efficiently. The platform supports user authentication, item listings, matches between lost and found items, chat messaging, profile management with avatar uploads, and admin functionalities for moderation.

Features
	•	User Management
Registration, login, profile editing, and avatar upload/removal.
	•	Item Listings
Users can add lost or found items with descriptions and images.
	•	Match System
Matches lost items with found items and allows users to chat regarding matches.
	•	Real-time Notifications
Alerts for new matches, messages, and system notifications.
	•	Reporting System
Users can report other users within chat interactions, notifying admins.
	•	Admin Dashboard
Manage users, items, matches, reports, and delete inappropriate content.

Technologies Used
	•	PHP (Backend)
	•	MySQL (Database)
	•	Bootstrap 5 (Frontend)
	•	JavaScript (Interactivity)

Installation & Setup
	1.	Clone the repository to your local machine.
	2.	Set up a local server environment (e.g., XAMPP, MAMP, LAMP).
	3.	Create a MySQL database and import the provided SQL schema or run the create_tables.php script.
	4.	Configure your database credentials in includes/db.php.
	5.	Ensure the following directories exist and have proper write permissions:
	•	uploads/profiles/ (for user avatars)
	•	uploads/items/ (for item images)
	•	uploads/qr_codes/ (for generated QR codes)
	6.	Start the server and access the application via your browser.

Usage
	•	Register and log in as a user.
	•	Add lost or found items with detailed information.
	•	Browse matches and communicate with matched users.
	•	Admin users can manage reports, delete items, and moderate the platform.

Folder Structure
	•	/includes - Shared PHP files for database connection, functions, header/footer templates.
	•	/uploads - Storage for user profile images, item photos, and QR codes.
	•	/admin - Admin-specific pages and controls.
	•	/assets - Static assets like CSS, images, and icons.

Troubleshooting
	•	Make sure uploads/ directories are writable (chmod 777 or equivalent).
	•	Verify database connection settings.
	•	Ensure the MySQL schema is up to date with all required tables.
	•	Clear browser cache if profile images don’t update immediately.

Contribution

Feel free to fork the repo, create branches for your features, and submit pull requests. For major changes, please open an issue first to discuss.

License

MIT License
