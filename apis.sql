-- ------------------------------api 1 - de exercicios ------------------------------------
create database apis;
use apis;

create table api1 (
id int key,
nome varchar(100),
musculo varchar(50),
equipamento varchar(50)
);

insert into  api1
values (1, 'Supino Inclinado', "Peitoral Superior", "Máquina"),
	   (2,'Agachamento Livre', "Quadriceps", "Barra livre"),
	   (3, 'Levantamento Terra', "Lombar", "Barra Livre" ),
	   (4, 'Elevação lateral', "Deltóide Medial", "Halteres"),
	   (5, 'Rosca Scott', "Bíceps", "Barra W");
       
	select * from api1;




-- --------------------------------api 2 de frases motivacionais ---------------------------



create table api2 (
id int primary key auto_increment,
frase varchar(255) not null,
autor varchar(100)
);

insert into api2 (frase, autor) values
('Acredite em você mesmo e em tudo o que você é. Saiba que há algo dentro de você que é maior do que qualquer obstáculo.', 'Christian D. Larson'),
('O único lugar onde o sucesso vem antes do trabalho é no dicionário.', 'Vidal Sassoon'),
('Não espere por oportunidades extraordinárias. Agarre ocasiões comuns e as torne grandes.', 'Orison Swett Marden'),
('O futuro pertence àqueles que acreditam na beleza de seus sonhos.', 'Eleanor Roosevelt');

select * from api2;





-- --------------------------- API 3 DE Sabores de Sorvete ------



create table api3 (
id int primary key auto_increment,
sabor varchar(100) not null,
tipo varchar(50),
descricao text
);

insert into api3 (sabor, tipo, descricao) values
('Chocolate Belga', 'Creme', 'Clássico e intenso sabor de chocolate nobre.'),
('Morango', 'Fruta', 'Refrescante sorvete feito com morangos frescos.'),
('Flocos', 'Creme', 'Sorvete de baunilha com pedacinhos de chocolate.'),
('Pistache', 'Creme', 'Sabor sofisticado com pedaços de pistache.'),
('Limão Siciliano', 'Fruta', 'Azedinho e perfeito para dias quentes.');

select * from api3;