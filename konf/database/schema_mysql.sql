-- Портал «Конференции.РФ» — схема БД для MySQL 8 (кодировка utf8mb4)
CREATE DATABASE IF NOT EXISTS konf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE konf;

CREATE TABLE role (
    id    INT PRIMARY KEY,
    code  VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(150) NOT NULL,
    phone         VARCHAR(20)  NOT NULL,
    email         VARCHAR(100) NOT NULL,
    role_id       INT NOT NULL DEFAULT 1,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role(id)
) ENGINE=InnoDB;

CREATE TABLE room (
    id          INT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    place       VARCHAR(150) NOT NULL,
    capacity    INT NOT NULL,
    description TEXT NOT NULL,
    photo       VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE event (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    starts_at   DATETIME NOT NULL,
    room_id     INT NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (room_id) REFERENCES room(id),
    INDEX ix_event_start (starts_at)
) ENGINE=InnoDB;

CREATE TABLE status (
    id   INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE payment_type (
    id   INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE application (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    event_id        INT NOT NULL,
    payment_type_id INT NOT NULL,
    status_id       INT NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_type_id) REFERENCES payment_type(id),
    FOREIGN KEY (status_id) REFERENCES status(id),
    INDEX ix_application_user (user_id),
    INDEX ix_application_status (status_id)
) ENGINE=InnoDB;

CREATE TABLE review (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL UNIQUE,
    rating         TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    body           TEXT NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES application(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE login_fail (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    at    INT NOT NULL
) ENGINE=InnoDB;

INSERT INTO role VALUES (1, 'user', 'Участник'), (2, 'admin', 'Администратор');
INSERT INTO status VALUES (1, 'Новая'), (2, 'Мероприятие назначено'), (3, 'Завершено');
INSERT INTO payment_type VALUES (1, 'При очном посещении'), (2, 'Перевод по СБП');
INSERT INTO room VALUES
 (1, 'Аудитория', 'Корпус А, 2 этаж', 120, 'Амфитеатр с проектором и микрофонами — для пленарных докладов.', 'auditorium'),
 (2, 'Коворкинг', 'Корпус Б, 1 этаж', 40, 'Открытое пространство со столами — для секций и мастер-классов.', 'coworking'),
 (3, 'Кинозал', 'Корпус А, цокольный этаж', 80, 'Большой экран и мягкие кресла — для презентаций и показов.', 'cinema');
-- администратор Conf2027 / Demo77 создаётся при первом запуске (src/Db.php), чтобы пароль хранился хешем
