-- Портал «Конференции.РФ» — схема БД (SQLite).
-- Версия для MySQL: schema_mysql.sql

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS role (
    id    INTEGER PRIMARY KEY,
    code  VARCHAR(20) NOT NULL UNIQUE,         -- user / admin
    title VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(150) NOT NULL,
    phone         VARCHAR(20)  NOT NULL,
    email         VARCHAR(100) NOT NULL,
    role_id       INTEGER NOT NULL DEFAULT 1 REFERENCES role(id),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS room (
    id          INTEGER PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    place       VARCHAR(150) NOT NULL,           -- место проведения
    capacity    INTEGER NOT NULL,
    description TEXT NOT NULL,
    photo       VARCHAR(100) NOT NULL
);

-- мероприятие: название, дата, место, описание
CREATE TABLE IF NOT EXISTS event (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    title       VARCHAR(150) NOT NULL,
    starts_at   DATETIME NOT NULL,
    room_id     INTEGER NOT NULL REFERENCES room(id),
    description TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS status (
    id   INTEGER PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE                 -- Новая / Мероприятие назначено / Завершено
);

CREATE TABLE IF NOT EXISTS payment_type (
    id   INTEGER PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE                 -- При очном посещении / Перевод по СБП
);

-- заявка связывает пользователя и мероприятие (многие-ко-многим через заявку)
CREATE TABLE IF NOT EXISTS application (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id        INTEGER NOT NULL REFERENCES event(id) ON DELETE CASCADE,
    payment_type_id INTEGER NOT NULL REFERENCES payment_type(id),
    status_id       INTEGER NOT NULL DEFAULT 1 REFERENCES status(id),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS review (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    application_id INTEGER NOT NULL UNIQUE REFERENCES application(id) ON DELETE CASCADE,
    rating         INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    body           TEXT NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_fail (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    login  VARCHAR(50) NOT NULL,
    at     INTEGER NOT NULL                          -- unix-время неудачной попытки
);

CREATE INDEX IF NOT EXISTS ix_application_user   ON application(user_id);
CREATE INDEX IF NOT EXISTS ix_application_status ON application(status_id);
CREATE INDEX IF NOT EXISTS ix_event_start        ON event(starts_at);

-- справочники
INSERT OR IGNORE INTO role (id, code, title) VALUES (1, 'user', 'Участник'), (2, 'admin', 'Администратор');
INSERT OR IGNORE INTO status (id, name) VALUES (1, 'Новая'), (2, 'Мероприятие назначено'), (3, 'Завершено');
INSERT OR IGNORE INTO payment_type (id, name) VALUES (1, 'При очном посещении'), (2, 'Перевод по СБП');
INSERT OR IGNORE INTO room (id, name, place, capacity, description, photo) VALUES
 (1, 'Аудитория', 'Корпус А, 2 этаж', 120, 'Амфитеатр с проектором и микрофонами — для пленарных докладов.', 'auditorium'),
 (2, 'Коворкинг', 'Корпус Б, 1 этаж', 40, 'Открытое пространство со столами — для секций и мастер-классов.', 'coworking'),
 (3, 'Кинозал', 'Корпус А, цокольный этаж', 80, 'Большой экран и мягкие кресла — для презентаций и показов.', 'cinema');
