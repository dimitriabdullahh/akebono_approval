-- ========================================
-- Employee Role Setup SQL Script
-- ========================================
-- Insert 5 employees untuk approval workflow:
-- - Purchasing: 1 Approver
-- - PPC: 1 Approver  
-- - QA: 3 roles (Checker, Approver, Receiver)
-- ========================================

-- Step 1: Update enum to add 'Receiver' role (if not already updated via migration)
-- ALTER TABLE employees MODIFY COLUMN role ENUM('Checker', 'Approver', 'Receiver') NOT NULL;

-- Step 2: Insert/Update employees
-- Note: Password hash for 'password123'

-- 1. Purchasing Approver
INSERT INTO employees (name, npk, division, role, email, password, is_active, created_at, updated_at)
VALUES (
    'Purchasing Approver',
    'PUR001',
    'Purchasing',
    'Approver',
    'purchasing.approver@akebono.co.id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password123
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = 'Purchasing Approver',
    division = 'Purchasing',
    role = 'Approver',
    email = 'purchasing.approver@akebono.co.id',
    is_active = 1,
    updated_at = NOW();

-- 2. PPC Approver
INSERT INTO employees (name, npk, division, role, email, password, is_active, created_at, updated_at)
VALUES (
    'PPC Approver',
    'PPC001',
    'PCC',
    'Approver',
    'ppc.approver@akebono.co.id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password123
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = 'PPC Approver',
    division = 'PCC',
    role = 'Approver',
    email = 'ppc.approver@akebono.co.id',
    is_active = 1,
    updated_at = NOW();

-- 3. QA Checker
INSERT INTO employees (name, npk, division, role, email, password, is_active, created_at, updated_at)
VALUES (
    'QA Checker',
    'QA001',
    'QA',
    'Checker',
    'qa.checker@akebono.co.id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password123
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = 'QA Checker',
    division = 'QA',
    role = 'Checker',
    email = 'qa.checker@akebono.co.id',
    is_active = 1,
    updated_at = NOW();

-- 4. QA Approver
INSERT INTO employees (name, npk, division, role, email, password, is_active, created_at, updated_at)
VALUES (
    'QA Approver',
    'QA002',
    'QA',
    'Approver',
    'qa.approver@akebono.co.id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password123
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = 'QA Approver',
    division = 'QA',
    role = 'Approver',
    email = 'qa.approver@akebono.co.id',
    is_active = 1,
    updated_at = NOW();

-- 5. QA Receiver
INSERT INTO employees (name, npk, division, role, email, password, is_active, created_at, updated_at)
VALUES (
    'QA Receiver',
    'QA003',
    'QA',
    'Receiver',
    'qa.receiver@akebono.co.id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password123
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = 'QA Receiver',
    division = 'QA',
    role = 'Receiver',
    email = 'qa.receiver@akebono.co.id',
    is_active = 1,
    updated_at = NOW();

-- Step 3: Verify inserted data
SELECT 
    id,
    npk,
    name,
    division,
    role,
    email,
    is_active,
    created_at
FROM employees
WHERE npk IN ('PUR001', 'PPC001', 'QA001', 'QA002', 'QA003')
ORDER BY 
    FIELD(division, 'Purchasing', 'PCC', 'QA'),
    FIELD(role, 'Checker', 'Approver', 'Receiver');

-- Expected Result:
-- +----+--------+---------------------+------------+----------+----------------------------------+-----------+---------------------+
-- | id | npk    | name                | division   | role     | email                            | is_active | created_at          |
-- +----+--------+---------------------+------------+----------+----------------------------------+-----------+---------------------+
-- |  1 | PUR001 | Purchasing Approver | Purchasing | Approver | purchasing.approver@akebono.co.id|         1 | 2026-10-13 08:00:00 |
-- |  2 | PPC001 | PPC Approver        | PCC        | Approver | ppc.approver@akebono.co.id       |         1 | 2026-10-13 08:00:00 |
-- |  3 | QA001  | QA Checker          | QA         | Checker  | qa.checker@akebono.co.id         |         1 | 2026-10-13 08:00:00 |
-- |  4 | QA002  | QA Approver         | QA         | Approver | qa.approver@akebono.co.id        |         1 | 2026-10-13 08:00:00 |
-- |  5 | QA003  | QA Receiver         | QA         | Receiver | qa.receiver@akebono.co.id        |         1 | 2026-10-13 08:00:00 |
-- +----+--------+---------------------+------------+----------+----------------------------------+-----------+---------------------+

-- ========================================
-- NOTES:
-- ========================================
-- Default Password: password123
-- Password Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- 
-- Login credentials:
-- NPK: PUR001, PPC001, QA001, QA002, QA003
-- Password: password123
-- 
-- Please change passwords after first login!
-- ========================================
