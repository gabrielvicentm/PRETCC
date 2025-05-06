
CREATE DATABASE tarcisio;
USE tarcisio;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(100),
    bio TEXT,
    foto_perfil VARCHAR(255),
    FOREIGN KEY (username) REFERENCES usuario(username)
);

CREATE TABLE seguidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seguidor_id INT NOT NULL,
    seguido_id INT NOT NULL,
    data_seguindo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(seguidor_id, seguido_id),
    FOREIGN KEY (seguidor_id) REFERENCES usuario(id),
    FOREIGN KEY (seguido_id) REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao TEXT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    data_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
