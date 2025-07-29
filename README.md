## **🧰 How to Run This Project (Frontend And Backend)**

## **Backend:**
🛠️ Setup Instructions

✅ Prerequisites
- PHP >= 8.1
- Composer
- MySQL

🚀 Installation Steps
1. After unzipping the zipped file
   ```bash
    c:\Users\> cd backend
    ```

2. Install dependencies
   ```bash
    c:\Users\> composer install
    ```

⚙️ Environment Setup
1. Copy the .env file and configure
   ```bash
    cp .env.example .env
    ```

2. Generate app key and JWT secret
   ```bash
    php artisan key:generate
    php artisan jwt:secret
    ```

3. Update .env with:
   ```bash
    DB_DATABASE="database_name"
    DB_USERNAME="root"
    DB_PASSWORD=""
    JWT_REFRESH_TTL=20160
   ```

🧱 Migrate the Database
   ```bash
    php artisan migrate:fresh --seed
   ```

🧾 Run Development Server
   ```bash
    php artisan serve
   ```
    Access: http://localhost:8000/api/docs


## **Frontend:**

### **📦 Step 1: Unzip the Project**
1. Locate the ZIP file sent to you.
2. Right-click the file and choose **“Extract All”** or **“Unzip”**.
3. Open the extracted folder.

---

### **⚙️ Step 2: Install Node.js (if not already installed)**
1. Visit [https://nodejs.org](https://nodejs.org)
2. Download the **LTS version** (Recommended for Most Users).
3. Install it on your system.
4. Open **Terminal** (Mac/Linux) or **Command Prompt** (Windows) and run:

node -v
If it returns something like `v18.x.x`, you're good to go.

---

### **📂 Step 3: Open the Project Folder in Terminal**
- Open **Terminal** or **Command Prompt**.
- Navigate to the unzipped folder:

cd path/to/unzipped-folder

---

### **📦 Step 4: Install Project Dependencies**
- Inside the terminal, run:

```bash
npm install
```

- This installs all necessary packages for the app to work. Wait until it's done.

---


### **🚀 Step 5: Start the Development Server**
```bash
npm run dev
```


After a few seconds, you'll see:

Local: http://localhost:3000

---

### **🌐 Step 6: Open the App in Your Browser**

Go to: [http://localhost:3000](http://localhost:3000)

✅ You should now see the Salary Admin Panel running!