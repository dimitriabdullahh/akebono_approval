# 🚀 Quick Setup Guide - Employee Roles

## Setup dalam 3 Langkah

### 1️⃣ Run Migration
```bash
php artisan migrate
```

### 2️⃣ Setup Employees
```bash
php artisan employees:setup-roles
```

### 3️⃣ Verify
```bash
php artisan tinker
```
```php
Employee::count() // Should return 5
```

---

## 👥 Default Accounts

| NPK    | Division   | Role     | Password    |
|--------|------------|----------|-------------|
| PUR001 | Purchasing | Approver | password123 |
| PPC001 | PCC        | Approver | password123 |
| QA001  | QA         | Checker  | password123 |
| QA002  | QA         | Approver | password123 |
| QA003  | QA         | Receiver | password123 |

⚠️ **Ganti password setelah first login!**

---

## 🔄 Approval Workflow

### Inspection Standard (IS)
```
Supplier → Receiver(QA003) → Checker(QA001) → Approvers(QA002,PPC001,PUR001)
```

### Packaging Standard (PS) & PSW
```
Supplier → Checker(QA001) → Approvers(QA002,PPC001,PUR001)
```

---

## 📝 Role Permissions

| Role     | Division   | Check Docs | Approve Docs | Receive IS |
|----------|------------|------------|--------------|------------|
| Checker  | QA         | ✅         | ❌           | ❌         |
| Approver | QA         | ❌         | ✅           | ❌         |
| Receiver | QA         | ❌         | ❌           | ✅         |
| Approver | PPC        | ❌         | ✅           | ❌         |
| Approver | Purchasing | ❌         | ✅           | ❌         |

---

## 🐛 Troubleshooting

### Migration Error
```bash
php artisan migrate:fresh
php artisan employees:setup-roles
```

### Re-run Seeder
```bash
php artisan db:seed --class=EmployeeRoleSeeder
```

### Manual SQL
```bash
mysql -u root -p your_database < database/sql/insert_employee_roles.sql
```

---

## 📚 Documentation

Lihat dokumentasi lengkap di file:
- Employee Role Setup & Approval Workflow Documentation
- Implementation Summary - Employee Role Setup

---

**Ready to use!** 🎉
