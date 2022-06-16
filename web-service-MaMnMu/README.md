# web-services-init

El objetivo del proyecto es crear un programa que permita localizar fácilmente un
inmueble y obtener la información catastral. Adicionalmente hará un cálculo del precio
estimado de dicho inmueble. Como funcionalidad adicional se mostrará un mapa de la 
localización del inmueble. Para acceder a utilizarlo es necesario que, el usuario, sea
validado por Google.

Se puede ver un ejemplo de esta funcionalidad en la web http://webapps.fpmadridonline.es.
Esta web debe tomarse como un ejemplo y no como una guia a seguir. El alumno puede hacerlo de 
una manera diferente siempre que se cumpla el objetivo final de obtener la información
catastral de un inmueble.
  En el aula virtual del módulo se puede ver un documento más detallado sobre los
requerimientos.

Utilizando la herramienta composer será necesario cargar las librerías eftec/bladeone y
google/apiclient. La primera ya es conocida y, la segunda, se utilizará en la validación
de los usuarios por google.

El programa PHP obtendrá la información catastral del servicio web del catastro. La 
información se puede obtener en:
https://www.catastro.meh.es/ayuda/lang/castellano/servicios_web.htm  