-- Базаи маълумоти системаи «Архиви Корҳои Илмӣ» — ДИС ДДТТ
-- PostgreSQL

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student' CHECK (role IN ('admin','teacher','student')),
    group_name VARCHAR(100),
    faculty VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS faculties (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS work_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS scientific_works (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    author_name VARCHAR(255) NOT NULL,
    supervisor VARCHAR(255),
    faculty_id INTEGER REFERENCES faculties(id) ON DELETE SET NULL,
    work_type_id INTEGER REFERENCES work_types(id) ON DELETE SET NULL,
    year INTEGER NOT NULL,
    group_name VARCHAR(100),
    keywords VARCHAR(500),
    description TEXT,
    file_path VARCHAR(500),
    file_name VARCHAR(255),
    file_size BIGINT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
    views INTEGER DEFAULT 0,
    downloads INTEGER DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT NOW(),
    approved_at TIMESTAMP,
    approved_by INTEGER REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS download_logs (
    id SERIAL PRIMARY KEY,
    work_id INTEGER REFERENCES scientific_works(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    ip_address VARCHAR(50),
    downloaded_at TIMESTAMP DEFAULT NOW()
);

-- Маълумоти намунавӣ
INSERT INTO faculties (name, short_name) VALUES
('Иттилоотӣ ва иқтисодӣ', 'ИИ'),
('Технологияи иттилоотӣ', 'ТИ'),
('Молия ва бонк', 'МБ'),
('Тиҷорат ва менеҷмент', 'ТМ')
ON CONFLICT DO NOTHING;

INSERT INTO work_types (name) VALUES
('Рисолаи хатмӣ'),
('Кори курсӣ'),
('Мақолаи илмӣ'),
('Лоиҳаи амалӣ'),
('Рефератӣ')
ON CONFLICT DO NOTHING;

-- Admin: парол = password
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Администратор Система', 'admin@dis.tj',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON CONFLICT (email) DO NOTHING;
