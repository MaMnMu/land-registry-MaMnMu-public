# Servicio REST
* Se desarrollará un servicio REST que ofrecerá el precio del metro cuadrado por provincia. La aplicación consultará el precio del metro cuadrado del municipio y lo multiplicará por el número de metros cuadrados para calcular el precio estimado. Dicho servicio REST implementa un API REST que recupera información de un municipio de España incluido el precio de la vivienda por metro cuadrado.*
El punto final del API es:
/provincia/{nombre_prov}/municipio/{nombre_mun}
El servicio REST devolverá la información del municipio en formato JSON.
El servidor REST se implementará utilizando el framework de desarrollo SLIM v4.
