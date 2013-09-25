CLTVO - WP 2 FBK
================

PLUGIN ADMIN PAGE:

Para activar:
1. Configura los parámetros de la App de Fbk en la página de plugin

El plugin:
1. Logea al usuario
2. Autentica
3. Guarda en la base el AppId, Secret, PagId, Token y expiración


NEW POST:
- Si no tiene token no te deja postear
- Publica imágenes en Fbk de todos las imágenes adjuntas
- Guarda el id del post fbk en el meta del post de WP

FRONT END:
- Muestra los likes guardados en el meta
- Al dar like te pide hacer login vía JS
- Si logeado, postea un like en Fbk, y actualiza el meta con el total real de likes