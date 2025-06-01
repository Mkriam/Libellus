# Libellus

**Libellus** es una aplicación web que te permite guardar tus libros favoritos, comentarlos, hacer seguimiento de tus lecturas y crear grupos de lectura con otras personas.

## Características principales

- Guarda tus libros favoritos
- Comenta y opina sobre libros
- Lleva un registro de tus lecturas
- Crea grupos de lectura con amigos u otras comunidades

## Requisitos previos

### 1. Docker y Docker Compose

Asegúrate de tener Docker y Docker Compose instalados. Si no los tienes, puedes instalarlos desde su [página oficial](https://www.docker.com/).

### 2. Conexión a Internet

Necesaria al menos para la configuración inicial. La conexión también es útil recomendable para aprovechar todas las funcionalidades de la web.

### 3. Verifica puertos libres

La aplicación utiliza el puerto `3306` (MySQL por defecto). Asegúrate de que no esté ocupado por otro proceso. Si está en uso, puedes cambiarlo en el archivo `docker-compose.yml`, por ejemplo:

```yaml
ports:
  - "3307:3306"
```

### 4. Editor de texto o IDE

Es útil para visualizar o modificar los scripts y archivos de configuración.

## Instalación de la aplicación

1. En la carpeta principal del proyecto encontrarás el archivo `libellus.exe`. Haz doble clic para iniciar el instalador.
2. Sigue los pasos del instalador:
   - Puedes elegir si deseas un acceso directo en el escritorio.
   - La instalación se realizará por defecto en la carpeta `Documentos`, en idioma Español.
3. Espera a que se complete la instalación. La aplicación web se abrirá automáticamente al finalizar.

## Uso con Docker

1. Clona el repositorio y navega al directorio del proyecto.
2. Ejecuta el siguiente comando para levantar los servicios:

```bash
docker-compose up -d
```

3. Abre tu navegador y accede a `http://localhost`.

---

Creado por Miriam Rodríguez Antequera.