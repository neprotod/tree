-- Создаем базу данных
CREATE DATABASE IF NOT EXISTS tree CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Используем базу данных
USE tree;
-- *******************************************************
-- Таблица адресов

CREATE TABLE IF NOT EXISTS url
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    sourse VARCHAR(255) NOT NULL COMMENT 'Машинный адрес',
    alias VARCHAR(255) NULL COMMENT 'Синоним машинного адреса',
    language_id MEDIUMINT (255) NULL COMMENT 'ID таблицы language',
    type_id MEDIUMINT (255) NOT NULL COMMENT 'ID таблицы type',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Таблица типов

CREATE TABLE IF NOT EXISTS type
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    type VARCHAR(255) NOT NULL COMMENT 'Типы страниц, такие как module, user, page итп',
    PRIMARY KEY (id)
)ENGINE = INNODB;

INSERT INTO type (type)
    VALUES ('static');
-- Таблица языков

CREATE TABLE IF NOT EXISTS language
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    language VARCHAR(40) NOT NULL COMMENT 'Язык: ru, en',
    discription VARCHAR(255) DEFAULT NULL COMMENT 'Полное имя языка',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- *******************************************************

-- Категории

CREATE TABLE IF NOT EXISTS category
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    catName VARCHAR(255) NOT NULL COMMENT 'Имя категории',
    url_id INT DEFAULT NULL COMMENT 'Адрес категории',
    config TEXT DEFAULT NULL COMMENT 'Конфигурации страници, блог она или нет',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Какие страници в категории

CREATE TABLE IF NOT EXISTS category_url
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    cat_id INT DEFAULT NULL COMMENT 'ID категории',
    url_id INT DEFAULT NULL COMMENT 'Адрес страници которая входит в эту категорию',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Вложенности категории

CREATE TABLE IF NOT EXISTS category_lvl
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    cat_id INT DEFAULT NULL COMMENT 'ID категории',
    parent_id INT NOT NULL COMMENT 'Сыылка на родителя',
    has_child INT NOT NULL COMMENT 'Имеет ли наследников',
    weight INT NOT NULL DEFAULT 1 COMMENT 'Вес',
    lvl INT NOT NULL DEFAULT 1 COMMENT 'Уровень вложености',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- ***************************************************

-- Пользователи

CREATE TABLE IF NOT EXISTS users
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    login VARCHAR(60) NOT NULL COMMENT 'Логин юзера',
    pass VARCHAR(128) NOT NULL COMMENT 'Пароль в md5',
    mail VARCHAR(255) NULL COMMENT 'Почта',
    init VARCHAR(255) NOT NULL COMMENT 'Дополнительная почта для отправки техническхи писем',
    created DATETIME NOT NULL COMMENT 'Дата создания',
    last_login DATETIME NOT NULL COMMENT 'Дата последнего захода',
    siteName VARCHAR(45) NULL COMMENT 'Имя на сайте',
    status INT NOT NULL DEFAULT 1 COMMENT 'Включен ли?',
    language_id INT NOT NULL  COMMENT 'Индтификатор языка',
    confirmed TINYINT NOT NULL  COMMENT 'Авторизован ли?',
    config TEXT NOT NULL  COMMENT 'Дополнительные сведенья, такие как адрес итп если нужны',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Клиенты

CREATE TABLE IF NOT EXISTS customers
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    name VARCHAR(45) NOT NULL COMMENT 'Имя',
    last_name VARCHAR(60) NULL COMMENT 'Фамилия',
    patronymic VARCHAR(60) NULL COMMENT 'Отчество',
    mail VARCHAR(255) NULL COMMENT 'Почта',
    pass VARCHAR(128) NOT NULL COMMENT 'Пароль в md5',
    user_id INT(10) NOT NULL  COMMENT 'Индтификатор языка',
    created DATETIME NOT NULL COMMENT 'Дата создания',
    last_login DATETIME NOT NULL COMMENT 'Дата последнего захода',
    siteName VARCHAR(45) NULL COMMENT 'Имя на сайте',
    status INT NOT NULL DEFAULT 1 COMMENT 'Включен ли?',
    language_id INT NOT NULL  COMMENT 'Индтификатор языка',
    config TEXT NOT NULL  COMMENT 'Дополнительные сведенья, такие как адрес итп если нужны',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- ***********************************

-- Тема

CREATE TABLE IF NOT EXISTS theme
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    theme VARCHAR(128) NOT NULL COMMENT 'Имя темы',
    paths VARCHAR(255) NOT NULL COMMENT 'Путь темы',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- ***********************************

-- Поля

CREATE TABLE IF NOT EXISTS field
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    fieldName_id INT(10) NOT NULL COMMENT 'ID на имя поля',
    value TEXT NOT NULL COMMENT 'Содержимое поле, от кода то текста',
    url_id INT NOT NULL COMMENT 'С какой страницой связано',
    param TEXT NULL COMMENT 'Параметры, например для поля img',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Имена полей

CREATE TABLE IF NOT EXISTS field_name
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    FieldName VARCHAR(128) NOT NULL COMMENT 'Такие как body, img итп',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- ***********************************

-- Системные таблицы

CREATE TABLE IF NOT EXISTS system
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    fieldSetting VARCHAR(60) NOT NULL COMMENT 'Имя значения поля',
    value TEXT NOT NULL COMMENT 'Параметры',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- ***********************************

-- Модули

CREATE TABLE IF NOT EXISTS system
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    name VARCHAR(255) NOT NULL COMMENT 'Имя модуля',
    keyword VARCHAR(120) NULL COMMENT 'Техническое имя модуля',
    config TEXT NULL COMMENT 'Массив конфигураций',
    description TEXT NULL COMMENT 'Описание моуля',
    createData DATETIME NOT NULL COMMENT 'Дата создания',
    module_type_id INT NOT NULL COMMENT 'Id модуля, системный или гаджет, у системного может быть своя страмница',
    url_id INT NULL COMMENT 'Если у данного модуля есть своя страница',
    status INT(2) NOT NULL DEFAULT 1 COMMENT 'Включен ли',
    weight INT NULL COMMENT 'Вес',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Типы модулей

CREATE TABLE IF NOT EXISTS module_type
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    name VARCHAR(65) NOT NULL COMMENT 'Как gatget или system',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Блоки

CREATE TABLE IF NOT EXISTS block
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    region VARCHAR(120) NOT NULL COMMENT 'Регион для модули или виджета',
    custom TINYINT NOT NULL DEFAULT 0 COMMENT 'Создал ли вручную',
    status TINYINT NOT NULL COMMENT 'Работает ли данный блок',
    thema_id INT NOT NULL COMMENT 'Индитификатрр темы',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Соединение, блок - модуль - виджет

CREATE TABLE IF NOT EXISTS block_module_widget
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    block_id INT NOT NULL COMMENT 'Регион для модули или виджета',
    id_either INT NOT NULL  COMMENT 'Индитификатор модуля или виджета',
    weight INT NOT NULL DEFAULT 1 COMMENT 'Вес',
    title VARCHAR(255) NOT NULL COMMENT 'Заголовок места',
    discription TEXT NULL COMMENT 'Описание темы',
    page TEXT NOT NULL COMMENT 'Если есть ячейка TRUE, значит на которых он может быть, если ячейка FALSE, значит на которых не может',
    block_type_id INT NOT NULL COMMENT 'Тип блока, модуль или виджет',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Виджеты

CREATE TABLE IF NOT EXISTS widget
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    name VARCHAR(128) NOT NULL COMMENT 'Имя виджета',
    category_id INT NOT NULL  COMMENT 'Id категория виджетов',
    value TEXT NULL DEFAULT 1 COMMENT 'Содержимое виджета',
    config TEXT NULL COMMENT 'Если есть конфигурации',
    PRIMARY KEY (id)
)ENGINE = INNODB;

-- Категории виджета

CREATE TABLE IF NOT EXISTS widget_category
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    category VARCHAR(128) NOT NULL COMMENT 'Категория виджета',
)ENGINE = INNODB;

-- Типы блока

CREATE TABLE IF NOT EXISTS block_type
(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Индитификатор записи',
    type VARCHAR(128) NOT NULL COMMENT 'Тип, модули или виджет',
)ENGINE = INNODB;