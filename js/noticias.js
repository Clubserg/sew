
class Noticias {

    constructor() {
      this.apiKey = "NSItDBhTQkZk9TmAzhOGKHwqjm6frvzeVSDs5hyy";
      this.busqueda = "motoGP";
      
      this.url = "https://api.thenewsapi.com/v1/news/all?api_token=" + this.apiKey + "&search=" + this.busqueda + "&language=en&limit=3"

      this.noticias = [];
    }
  

    buscar() {
      return fetch(this.url)
        .then(response => {
          return response.json();
        })
        .catch(error => {
          console.error("Error al buscar noticias:", error);
          throw error;
        });
    }
  

    procesarInformacion(data) {
      if (data && data.data && Array.isArray(data.data)) {
        this.noticias = data.data.map(article => ({
          titular: article.title || "Sin título",
          entradilla: article.description || "Sin descripción",
          enlace: article.url || "#",
          fuente: article.source || "Fuente desconocida",
          imagen: article.image_url || null,
          fecha: article.published_at || null,
          autor: article.author || "Autor desconocido"
        }));
      } else {
        console.error("Formato de datos inesperado");
        this.noticias =    this.noticias = [];
      }
    }

    mostrarNoticias() {
      const $section = $("<section></section>");
      $section.append("<h2>Noticias sobre MotoGP</h2>");
  
      if (this.noticias.length === 0) {
        $section.append("<p>No se encontraron noticias.</p>");
        return $section;
      }
  
      for (let i = 0; i < this.noticias.length; i++) {
        const noticia = this.noticias[i];
        const $article = $("<article></article>");
  
        // Titular
        const $titular = $("<h3></h3>").text(noticia.titular);
        $article.append($titular);
  
        // Fuente y fecha
        let metaInfo = "Fuente: " + noticia.fuente;
        if (noticia.fecha) {
          const fecha = new Date(noticia.fecha);
          const fechaFormateada = fecha.toLocaleDateString("es-ES", {
            year: "numeric",
            month: "long",
            day: "numeric"
          });
          metaInfo += " | " + fechaFormateada;
        }
        const $meta = $("<p></p>").html("<em>" + metaInfo + "</em>");
        $article.append($meta);
  
        // Entradilla
        const $entradilla = $("<p></p>").text(noticia.entradilla);
        $article.append($entradilla);
  
        // Enlace
        const $enlace = $("<p></p>");
        const $link = $("<a></a>")
          .attr("href", noticia.enlace)
          .attr("target", "_blank")
          .attr("rel", "noopener noreferrer")
          .text("Leer noticia completa");
        $enlace.append($link);
        $article.append($enlace);
  
        $section.append($article);
      }
  
      return $section;
    }
  
  
}