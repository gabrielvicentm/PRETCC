--------------------------------api 1 - de exercicios ------------------------------------
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




----------------------------------api 2 de frases motivacionais ---------------------------