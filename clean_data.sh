#!/bin/bash
pass="adivina"
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table archivos_graficos;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table matriz_c;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "truncate table parametro;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "update parametros_mem set status = 0;" bolas_sag
mysql -h 127.0.0.1 -u root -p$pass -e "update graficados set ai=1, an=0, ri=1, rn=0, fci=1, fcn=0, fai=1, fan=0;" bolas_sag
rm -r graficos/*

