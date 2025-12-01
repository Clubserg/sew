
class Carrusel {
    constructor(busqueda) {
        this.busqueda = busqueda;
        this.actual = 0;
        this.maximo = 4;
        this.fotos = [];
        this.intervalo = null;
        this.imgElemento = null; // Referencia directa a la imagen
    }

    getFotografias() {
        const flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";

        return $.getJSON(flickrAPI, {
            tags: this.busqueda,
            tagmode: "any",
            format: "json"
        }).then(data => {
            // Convertir imágenes de _m a _z (640px)
            data.items.forEach(item => {
                item.media.m = item.media.m.replace("_m.jpg", "_z.jpg");
            });
            return data;
        });
    }

    procesarJSONFotografias(json) {
        if (!json || !json.items) {
            console.error("JSON inválido");
            return;
        }

        this.fotos = [];

        json.items.slice(0, 5).forEach(item => {
            this.fotos.push({
                titulo: item.title || "Sin título",
                url: item.media.m
            });
        });
    }

    mostrarFotografias() {
        if (this.fotos.length === 0) {
            return "<p>No hay fotos para mostrar</p>";
        }

        const primeraFoto = this.fotos[0];

        // Crear elementos sin id ni class
        const article = document.createElement("article");
        const titulo = document.createElement("h2");
        titulo.textContent = `Imágenes del circuito de ${this.busqueda}`;

        const img = document.createElement("img");
        img.src = primeraFoto.url;
        img.alt = primeraFoto.titulo;

        // Guardamos referencia para cambiar la foto después
        this.imgElemento = img;

        article.appendChild(titulo);
        article.appendChild(img);

        // Iniciar el carrusel
        this.intervalo = setInterval(this.cambiarFotografia.bind(this), 3000);

        return article; // Devuelve el nodo, no HTML
    }

    cambiarFotografia() {
        if (this.fotos.length === 0 || !this.imgElemento) return;

        this.actual = (this.actual + 1) % this.fotos.length;
        const fotoActual = this.fotos[this.actual];

        this.imgElemento.src = fotoActual.url;
        this.imgElemento.alt = fotoActual.titulo;
    }
}