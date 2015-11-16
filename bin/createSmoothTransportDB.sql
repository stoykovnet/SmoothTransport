CREATE TABLE ccst16_sms_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE ccst16_logistician (
    id BIGINT UNSIGNED PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    password VARCHAR(20) NOT NULL,
    telephone VARCHAR(15) NOT NULL,
    FOREIGN KEY (id) REFERENCES ccst16_sms_user(id)	ON DELETE CASCADE
);

CREATE TABLE ccst16_truck_driver (
    id BIGINT UNSIGNED PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    telephone VARCHAR(15) NOT NULL,
    FOREIGN KEY (id) REFERENCES ccst16_sms_user(id)	ON DELETE CASCADE
);

CREATE TABLE ccst16_sms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creation_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    text VARCHAR(160) NOT NULL,
    is_sent TINYINT(1) DEFAULT 0,
    is_received TINYINT(1) DEFAULT 0,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (sender_id) REFERENCES ccst16_sms_user(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES ccst16_sms_user(id) ON DELETE CASCADE
);

CREATE TABLE ccst16_logistician_and_truck_driver (
    logistician_id BIGINT UNSIGNED,
    truck_driver_id BIGINT UNSIGNED,
    PRIMARY KEY(logistician_id, truck_driver_id),
    FOREIGN KEY (logistician_id) REFERENCES ccst16_logistician(id) ON DELETE CASCADE,
    FOREIGN KEY (truck_driver_id) REFERENCES ccst16_truck_driver(id) ON DELETE CASCADE
    
);

CREATE TABLE ccst16_truck (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_capacity TINYINT NOT NULL,
    brand VARCHAR(20) NOT NULL,
    model VARCHAR(20) NOT NULL,
    engine VARCHAR(20) NOT NULL,
    tires_serial VARCHAR(20) NOT NULL,
    age TINYINT NOT NULL
);

CREATE TABLE ccst16_truck_driver_and_truck (    
    truck_driver_id BIGINT UNSIGNED,
    truck_id BIGINT UNSIGNED,
    PRIMARY KEY(truck_driver_id, truck_id),
    FOREIGN KEY (truck_driver_id) REFERENCES ccst16_truck_driver(id) ON DELETE CASCADE,
    FOREIGN KEY (truck_id) REFERENCES ccst16_truck(id) ON DELETE CASCADE
);

CREATE TABLE ccst16_car (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(20) NOT NULL,
    model VARCHAR(20) NOT NULL,
    series_name VARCHAR(20) NOT NULL,
    manufacture_dt DATETIME NOT NULL,
    is_new TINYINT(1) DEFAULT 1
);

CREATE TABLE ccst16_point_of_interest (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(20) NOT NULL,
    longitude VARCHAR(20) NOT NULL,
    latitude VARCHAR(20) NOT NULL
);

CREATE TABLE ccst16_delivery_order (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creation_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    take_cars_dt DATETIME NOT NULL,
    deliver_cars_dt DATETIME NOT NULL,
    status VARCHAR(10),
    truck_id BIGINT UNSIGNED NOT NULL,
    car_id BIGINT UNSIGNED NOT NULL,
    manufacturer_id BIGINT UNSIGNED NOT NULL,
    shop_id BIGINT UNSIGNED NOT NULL,
    quantity TINYINT NOT NULL,
    price DECIMAL(15, 2) NOT NULL
);