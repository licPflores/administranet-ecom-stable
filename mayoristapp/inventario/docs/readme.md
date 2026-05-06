# Inventario — Análisis del Frontend

> Sub-módulo del sistema **mayoristapp** dentro del proyecto `administraNET-ecom`.
>
> Ruta: `mayoristapp/inventario/`

---

## 1. Descripción general

Aplicación web SPA (Single Page Application) de gestión de inventario para el software de gestión administraNET. Permite a vendedores y supervisores:

- Buscar artículos por nombre, código interno o código de barras (lector físico o cámara)
- Registrar movimientos de stock (ajustes) con selección de depósito y fecha
- Editar datos del artículo (nombre, detalle, nombre e-commerce)
- Gestionar fotos del producto (agregar, eliminar, definir imagen principal)
- Editar y agregar códigos de barras (unidad, display, bulto)

El sistema respeta un esquema de **permisos por usuario** con tres niveles de acción:
- `Todos`: acceso completo
- `Carga inventario`: solo ajustes de stock
- `Editar datos`: edición de datos, fotos y códigos, sin acceso a movimientos

---

## 2. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP (XAMPP, MySQLi) |
| Frontend | HTML5 + Bootstrap 5 + SCSS |
| JS principal | jQuery 3.3.1 |
| CSS framework | Bootstrap 5 (local) |
| Fuentes de íconos | Font Awesome 6 (CDN), Bootstrap Icons 1.9.1 (CDN) |
| Animaciones | Anime.js (`anime-master/anime.min.js`) |
| Alertas | SweetAlert2 |
| Slider | Slick.js |
| Lightbox | jQuery Fancybox |
| Lector de códigos | QuaggaJS (`quagga@0.12.1` desde CDN) |
| Recorte de fotos | **Cropper.js** (local: `js/cropper.js`, `css/cropper.css`) |
| Imágenes externas | API REST propia: `https://img.api.administranet.com.ar/` |
| Autocomplete | custom `autocompletar.js` |

---

## 3. Estructura de archivos

```
inventario/
├── index.php                   Única vista principal (SPA)
├── header.php                  HTML head + navbar + carga de CSS + modal recorte
├── footer.php                  Carga de scripts JS en orden correcto
│
├── includes/
│   ├── defines.inc.php         Constantes: entorno, BD, configuración
│   ├── conex.inc.php           Conexión MySQLi
│   ├── sesion.inc.php          Control de sesión
│   ├── rubros-subrubros.php    Helper rubros
│   └── mas-vendidos.php        Helper más vendidos
│
├── ajax/
│   └── stock-backend.php       Controlador AJAX: todo el backend de la SPA
│
├── js/
│   ├── cropper.js              Librería Cropper.js (local)
│   ├── create-bootstrap-element.js   Factory de elementos BS5 dinámicos
│   └── busqueda-rapida/
│       ├── autocompletar.js    Lógica principal: búsqueda, AJAX, CRUD fotos/inventario
│       ├── assemble-elements.js Constructores de vistas dinámicas (setCardIntro, setFormProducto…)
│       ├── datos_usuario.js    Carga permisos e info del vendedor desde el backend
│       ├── lector-codigo.js    Integración QuaggaJS para cámara / lector de código de barras
│       └── cut-img.js          Lógica de recorte fotográfico (ver sección 5)
│
├── css/
│   ├── bootstrap.min.css
│   ├── cropper.css             Estilos Cropper.js (local)
│   ├── style.css               CSS compilado desde SCSS
│   └── partes/style/
│       └── articulo.css        Estilos de la vista artículo (slick)
│
├── scss/
│   ├── style.scss              Entrada principal SCSS
│   ├── variables/              Paletas de color, mixins, fuentes
│   └── partes/style/
│       ├── _body.scss          Estilos globales
│       ├── _cards.scss         Sistema de tarjetas del inventario
│       ├── _inventario.scss    Loading overlay, contadores animados, secciones internas
│       └── ...                 Otros módulos (header, footer, formulario, etc.)
│
├── img/                        Imágenes estáticas (logo, placeholder, íconos de código de barras)
└── docs/
    └── readme.md               Este archivo
```

---

## 4. Arquitectura de la SPA

La aplicación no usa routing ni framework JS. Todo el contenido dinámico se construye mediante funciones JS que insertan elementos en tres contenedores fijos del DOM:

```
#content-intro   → Tarjeta inicial del artículo encontrado (nombre, precio, foto)
#content-form    → Pantalla de edición activa (fotos / stock / datos / códigos)
#content-video   → Stream de cámara para el lector de barras
```

Las transiciones entre vistas se generan con **Anime.js** (`translateX`) deslizando el contenedor `#content-general`.

### Flujo típico de uso

```
1. Selección de fecha y depósito (AJAX → listarDepositos)
2. Búsqueda de artículo:
   a. Autocomplete en input → AJAX autocompletarArticulo
   b. Lector QR/código de barras → QuaggaJS → getNewSearchScan
3. Resultado encontrado → setCardIntro (pantalla de opciones)
4. El usuario elige una sección:
   ├── Datos del producto  → setCardEditNombre
   ├── Movimiento de stock → setCardProducto
   ├── Códigos de barras   → setCardEditCodigo
   └── Imágenes            → setFormProducto  ← recorte de fotos
5. Guardado → AJAX POST/GET → stock-backend.php → respuesta SweetAlert2
6. Reload de datos → reloadSearch → vuelve a la vista activa
```

---

## 5. Sección de recorte fotográfico

### 5.1 Visión general

El módulo de gestión de imágenes permite al usuario:
1. **Subir una foto nueva** desde el dispositivo, pasar por el recortador y guardarla
2. **Ver la lista de fotos existentes** del artículo en un carrusel arrastrable
3. **Borrar una imagen** del servidor externo y de la base de datos
4. **Marcar una imagen como principal**, que es la que se usa en el e-commerce

### 5.2 Librería utilizada: Cropper.js

Se usa la versión local de [Cropper.js](https://fengyuanchen.github.io/cropperjs/), cargada antes que jQuery para evitar conflictos:

```html
<!-- footer.php — orden de carga -->
<script src="js/cropper.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
```

Los estilos se cargan en el `<head>`:

```html
<link rel="stylesheet" href="css/cropper.css">
```

### 5.3 Modal de recorte

El modal está definido estáticamente en `index.php`:

```html
<div class="modal fade modalCrop" id="modal-img-cut"
    data-bs-backdrop="static"
    data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cortar la imagen desde la linea punteada</h5>
        <button type="button" class="btn close-button" data-bs-dismiss="modal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="img-container">
          <div class="cropImageContainer">
            <img src="" id="cut_image" />   <!-- target del Cropper -->
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="crop" class="btn btn-primary">Guardar imagen</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
```

Atributos `data-bs-backdrop="static"` y `data-bs-keyboard="false"` impiden el cierre accidental mientras el usuario recorta.

El input `<input type="file" id="image1">` está oculto en `index.php`. La pantalla de fotos (`setFormProducto`) crea dinámicamente un nuevo `<input type="file" onChange="changeFile()">` para disparar la carga.

### 5.4 Flujo completo de recorte (`cut-img.js`)

```
Usuario selecciona archivo
        ↓
changeFile()
  └→ FileReader.readAsDataURL()
        ↓
  image.src = dataURL      ← inyecta la imagen en #cut_image
  $modal.modal('show')     ← abre el modal Bootstrap 5
        ↓
shown.bs.modal
  └→ new Cropper(image, {
       aspectRatio: 1,      ← recorte cuadrado forzado (1:1)
       viewMode: 2           ← la imagen no puede salir del contenedor
     })
        ↓
Usuario ajusta el recorte
        ↓
Clic en "Guardar imagen"
  └→ cropper.getCroppedCanvas({ width: 800, height: 800 })
        ↓
  canvas.toBlob(blob, 'image/jpeg', 0.8)
        ↓                         ↑ calidad JPEG 80%
  new File([blob], 'img.jpg', { type:'image/jpeg' })
        ↓
  saveFoto(file)  ← en autocompletar.js
        ↓
hidden.bs.modal
  └→ cropper.destroy()    ← libera la instancia al cerrar
```

**Dimensiones de salida:** 800 × 800 px, JPEG calidad 0.8 (80%).
**Relación de aspecto:** 1:1 (cuadrado) — el comentado `image_w/image_h` sugiere que estuvo planificado para ser configurable pero se dejó fijo en 1.

### 5.5 Guardado en el servidor (`autocompletar.js → saveFoto`)

```javascript
function saveFoto(nuevaFoto) {
    var form_data = new FormData();
    form_data.append('guardarImagen', 1);
    form_data.append('idArticulo', search.articulo.IDArt);
    form_data.append('imagenNueva', nuevaFoto);    // File object

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "POST",
        data: form_data,
        dataType: 'text',           // respuesta cruda, se parsea con JSON.parse()
        contentType: false,
        processData: false,
        ...
    });
}
```

### 5.6 Backend de imágenes (`stock-backend.php → altaImagenAdministranet`)

El backend **no almacena imágenes localmente**. Las envía al servidor de imágenes externo de administraNET mediante **cURL**:

**POST para guardar:**
```
POST https://img.api.administranet.com.ar/imagen/?codigo=SanMartin
Content-Type: multipart/form-data
```
Respuesta: URL pública de la imagen + thumbnails (`_m`, `_l`).

**DELETE para borrar:**
```
DELETE https://img.api.administranet.com.ar/imagen/{nombre_archivo}?codigo=SanMartin
```

Tras el alta en la API, el backend guarda la referencia en la tabla `articulo_foto` de la base de datos local (campo `url_externo` y `nombre_archivo`).

### 5.7 Gestión de la lista de fotos

La función `createFormImgList` (en `assemble-elements.js`) genera dinámicamente la lista de imágenes del artículo. Cada ítem tiene:

- `<img>` con la URL del servidor externo
- Botón **"Borrar"** → `deleteFoto(id)` → DELETE en la API + DELETE en BD
- Botón **"Imagen principal"** → `saveFotoPrincipal(id)` → UPDATE `articulo_foto.foto_principal` en BD

El ítem marcado como principal lleva la clase CSS `item principal`.

---

## 6. Módulos JS adicionales

### `create-bootstrap-element.js`
Factory de funciones para crear elementos Bootstrap 5 de forma programática:
`createCard`, `createCardTitle`, `createButton`, `createInput`, `createSelect`, `createInputFile`, `createDiv`, `createRow`, `createDivDropdown`, etc.

Toda la interfaz dinámica (formularios, tarjetas de resultados, contadores) se construye con estas funciones, sin templates HTML ni frameworks reactivos.

### `lector-codigo.js` + QuaggaJS
Activa la cámara del dispositivo para leer códigos de barras en tiempo real. Soporta múltiples formatos: EAN-13, EAN-8, Code 128, Code 39, UPC, UPC-E, 2of5, Code 93.

Incluye un botón de **flash** para dispositivos móviles. La búsqueda tras la lectura dispara `getNewSearchScan()`.

### `datos_usuario.js`
Carga los datos del usuario autenticado y sus permisos de inventario desde `stock-backend.php` (`traerDatosUsuario`). Los almacena en la variable global `datosUsuario` que controla qué secciones se muestran en `setCardIntro()`.

---

## 7. Sistema de estilos (SCSS)

La hoja de estilos está organizada en módulos SCSS compilados a `css/style.css`:

| Archivo SCSS | Contenido |
|---|---|
| `variables/_variables.scss` | Paleta de colores `$color-web` y `$color-app`, tipografías |
| `variables/_mixins.scss` | Mixins de fuente, transiciones |
| `partes/style/_body.scss` | Reset global, botones, backgrounds con imagen |
| `partes/style/_cards.scss` | Sistema de tarjetas: `.card`, `.card-title`, `.card-img`, `.card-list` |
| `partes/style/_inventario.scss` | Loading overlay, contadores animados (`.texto-unidad`, `.texto-display`, `.texto-bulto`) |
| `partes/style/_formulario.scss` | Formularios de edición |
| `partes/inventario/_menues.scss` | Menús desplegables del inventario |
| `partes/inventario/_scanner.scss` | Contenedor del video Quagga, overlay del lector |

El sistema de colores usa dos paletas:
- `$color-web`: colores corporativos de administraNET (fondo, texto, primario)
- `$color-app`: colores de los contadores y estados del inventario (unidad/display/bulto)

---

## 8. API backend (`stock-backend.php`) — resumen de endpoints

| Parámetro | Método | Descripción |
|---|---|---|
| `listarDepositos=1` | GET | Lista de depósitos disponibles |
| `autocompletarArticulo=1` | GET | Array de artículos (id, nombre, foto) para autocomplete |
| `buscarArticulo=1` | GET | Búsqueda completa: por id, texto o código de barras |
| `listarTipoMovimiento=1` | GET | Lista de tipos de ajuste de stock |
| `traerDatosUsuario=1` | GET | Permisos y datos del vendedor (requiere sesión) |
| `altaMovimiento=1` | POST | Registrar movimiento de inventario |
| `guardarImagen=1` | POST | Subir nueva foto (multipart) |
| `borrarImagen=1` | GET | Eliminar foto por id |
| `fotoPrincipal=1` | GET | Marcar foto como principal |
| `guardarDatosProducto=1` | POST | Editar nombre/detalle del artículo |
| `guardarCodBarra=1` | POST | Guardar/actualizar códigos de barras |

---

## 9. Notas y observaciones

- El proyecto está en **modo `test`** (`PROYECTO='test'` en `defines.inc.php`), apuntando a la base de datos `administranet74` en `190.15.214.142`. Para pasar a producción hay que cambiarlo a `'produccion'`.
- Las consultas SQL en `stock-backend.php` construyen cláusulas `WHERE` concatenando variables directamente. **Es recomendable migrar a prepared statements** con MySQLi o PDO para el caso de texto libre y código de barras.
- El `$modal` en `cut-img.js` usa la sintaxis jQuery de Bootstrap 4 (`.modal('show')`), pero el HTML usa atributos de Bootstrap 5. Funciona porque `bootstrap.bundle.js` incluye el plugin Modal compatible con jQuery, pero conviene verificar que no haya conflictos de versiones si se actualiza Bootstrap.
- La función `changeFile()` usa `event` como variable global implícita. Es más robusto pasarla como argumento: `onChange="changeFile(event)"` y recibir `function changeFile(event)`.
- La lista de fotos no implementa drag & drop para reordenar (el código comentado lo tenía planificado con `Sortable.js`, que ya está incluido pero comentado en el footer).
