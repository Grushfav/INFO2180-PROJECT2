-- Users table: stores system users (agents, admins)
CREATE TABLE Users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) CHECK (role IN ('Admin','Agent')) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contacts table: stores client information
CREATE TABLE Contacts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(10),
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    company VARCHAR(100),
    type VARCHAR(20) CHECK (type IN ('Client','Lead')) NOT NULL,
    assigned_to INT REFERENCES Users(id) ON DELETE SET NULL,
    created_by INT REFERENCES Users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notes table: stores comments linked to contacts
CREATE TABLE Notes (
    id SERIAL PRIMARY KEY,
    contact_id INT REFERENCES Contacts(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    created_by INT REFERENCES Users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert an admin user (example)
INSERT INTO Users (firstname, lastname, email, password, role)
VALUES ('Admin', 'User', 'admin@project2.com', 
        'password123', 'Admin');