# MRT Library Management System

## Setup in XAMPP

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin` and import `schema.sql`.
3. Open `http://localhost/mrt-library/ (clean URLs enabled; e.g. `/member-login`)`.
4. Librarian login: **admin** / **admin123**.

## Notes

- Upload e-books only as PDFs. They are stored in `uploads/`, which is created automatically on the first upload.
- Member IDs are generated as `CBMDL1`, `CBMDL2`, etc.
- A member whose end date has passed cannot sign in.
- A user gets no download link. Browser-level screenshot/save protections cannot be made absolute on a web application, but reading is permission and time limited and the PDF endpoint is not accessible without an active approval.
