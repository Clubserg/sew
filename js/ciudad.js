
class Ciudad {

  constructor(nombre, pais, gentilicio) {
    this.nombre = String(nombre);
    this.pais = String(pais);
    this.gentilicio = String(gentilicio);

    // Atributos que se completan después
    this.poblacion = null;
    this.coordenadas = { lat: null, lon: null }; // {lat: number, lon: number}
  }

  establecerSecundarios(poblacion, lat, lon) {

    const pob = Number(poblacion);
    const la = Number(lat);
    const lo = Number(lon);

    this.poblacion = Number.isFinite(pob) && pob >= 0 ? Math.trunc(pob) : null;

    this.coordenadas = {
      lat: Number.isFinite(la) ? la : null,
      lon: Number.isFinite(lo) ? lo : null,
    };
  }

  getNombre() {
    return this.nombre;
  }

  getPais() {
    return this.pais;
  }


  infoSecundariaHTML() {
    const pobTxt =
      this.poblacion !== null ? this.poblacion.toLocaleString("es-ES") : "—";
    const gentTxt = this.gentilicio || "—";

    return `
        <ul>
        <li>Gentilicio: <strong>${gentTxt}</strong></li>
        <li>Población: <strong>${pobTxt}</strong></li>
        </ul>`.trim();
  }

  escribirCoordenadas() {
    const { lat, lon } = this.coordenadas;

    if (lat === null || lon === null) {
      return `<p><em>Coordenadas no disponibles.</em></p>`;
    }

    const latTxt = lat.toFixed(4);
    const lonTxt = lon.toFixed(4);

    return `<p>Coordenadas del centro: <strong>${latTxt}°, ${lonTxt}°</strong></p>`;
  }

  getMeteorologiaCarrera() {
    const fecha = "2025-03-30";
    // 19 de octubre a las 21 (recordatorio)
    const { lat, lon } = this.coordenadas;
    
    if (lat === null || lon === null) {
      console.error("Coordenadas no disponibles para consultar meteorología");
      $("main > section:nth-of-type(2)").append("<p>Error: Coordenadas no disponibles.</p>");
      return;
    }

    const url = "https://archive-api.open-meteo.com/v1/archive?latitude=" + 
                lat + 
                "&longitude=" + lon + 
                "&start_date=" + fecha + 
                "&end_date=" + fecha + 
                "&hourly=temperature_2m,apparent_temperature,rain,relative_humidity_2m,wind_speed_10m,wind_direction_10m" +
                "&daily=sunrise,sunset&timezone=auto";

    $.ajax({
      url: url,
      method: "GET",
      dataType: "json",
      success: (data) => {
        this.procesarJSONCarrera(data);
      },
      error: (error) => {
        console.error("Error al obtener datos meteorológicos de la carrera:", error);
        $("main > section:nth-of-type(2)").append("<p>Error al cargar los datos meteorológicos de la carrera.</p>");
      }
    });
  }

  procesarJSONCarrera(data) {
    
    const HORA_LOCAL = "21:00";
    const ZONA = "Europe/Madrid";

    const $article = $("<article></article>");
    
    // Datos diarios
    if (data.daily) {
      $article.append("<h4>Fecha: " + data.daily.time[0] + "</h4>");
      $article.append("<p>Amanecer: <strong>" + data.daily.sunrise[0] + "</strong></p>");
      $article.append("<p>Atardecer: <strong>" + data.daily.sunset[0] + "</strong></p>");
    }

    // Datos horarios
    if (data.hourly && Array.isArray(data.hourly.time) && data.hourly.time.length > 0) {
      const hora = data.hourly.time[21].split("T")[1];
      
      $article.append("<h5>Datos meteorológicos a las " + hora + "</h5>");
      
      const $lista = $("<ul></ul>");
      $lista.append("<li>Temperatura: <strong>" + data.hourly.temperature_2m[21] + " °C</strong></li>");
      $lista.append("<li>Sensación térmica: <strong>" + data.hourly.apparent_temperature[21] + " °C</strong></li>");
      $lista.append("<li>Lluvia: <strong>" + data.hourly.rain[21] + " mm</strong></li>");
      $lista.append("<li>Humedad: <strong>" + data.hourly.relative_humidity_2m[21] + " %</strong></li>");
      $lista.append("<li>Velocidad del viento: <strong>" + data.hourly.wind_speed_10m[21] + " km/h</strong></li>");
      $lista.append("<li>Dirección del viento: <strong>" + data.hourly.wind_direction_10m[21] + " °</strong></li>");
      
      $article.append($lista);
    }

    $("main > section:nth-of-type(2)").append($article);
  }

  getMeteorologiaEntrenos(fechaInicio, fechaFin) {
    const { lat, lon } = this.coordenadas;
    
    if (lat === null || lon === null) {
      console.error("Coordenadas no disponibles para consultar meteorología");
      $("main > section:nth-of-type(3)").append("<p>Error: Coordenadas no disponibles.</p>");
      return;
    }

    const url = "https://archive-api.open-meteo.com/v1/archive?latitude=" + 
                lat + 
                "&longitude=" + lon + 
                "&start_date=" + fechaInicio + 
                "&end_date=" + fechaFin + 
                "&hourly=temperature_2m,rain,wind_speed_10m,relative_humidity_2m&timezone=auto";

    $.ajax({
      url: url,
      method: "GET",
      dataType: "json",
      success: (data) => {
        this.procesarJSONEntrenos(data);
      },
      error: (error) => {
        console.error("Error al obtener datos meteorológicos de entrenamientos:", error);
        $("main > section:nth-of-type(3)").append("<p>Error al cargar los datos meteorológicos de entrenamientos.</p>");
      }
    });
  }

  procesarJSONEntrenos(data) {
    const $article = $("<article></article>");

    if (data.hourly && data.hourly.time.length > 0) {
      // Agrupar datos por día
      const datosPorDia = {};
      
      for (let i = 0; i < data.hourly.time.length; i++) {
        const fecha = data.hourly.time[i].split("T")[0];
        
        if (!datosPorDia[fecha]) {
          datosPorDia[fecha] = {
            temperatura: [],
            lluvia: [],
            viento: [],
            humedad: []
          };
        }
        
        datosPorDia[fecha].temperatura.push(data.hourly.temperature_2m[i]);
        datosPorDia[fecha].lluvia.push(data.hourly.rain[i]);
        datosPorDia[fecha].viento.push(data.hourly.wind_speed_10m[i]);
        datosPorDia[fecha].humedad.push(data.hourly.relative_humidity_2m[i]);
      }

      for (const fecha in datosPorDia) {
        const dia = datosPorDia[fecha];
        
        const mediaTemp = (dia.temperatura.reduce((a, b) => a + b, 0) / dia.temperatura.length).toFixed(2);
        const mediaLluvia = (dia.lluvia.reduce((a, b) => a + b, 0) / dia.lluvia.length).toFixed(2);
        const mediaViento = (dia.viento.reduce((a, b) => a + b, 0) / dia.viento.length).toFixed(2);
        const mediaHumedad = (dia.humedad.reduce((a, b) => a + b, 0) / dia.humedad.length).toFixed(2);

        $article.append("<h4>Día: " + fecha + "</h4>");
        
        const $lista = $("<ul></ul>");
        $lista.append("<li>Temperatura media: <strong>" + mediaTemp + " °C</strong></li>");
        $lista.append("<li>Lluvia media: <strong>" + mediaLluvia + " mm</strong></li>");
        $lista.append("<li>Velocidad del viento media: <strong>" + mediaViento + " km/h</strong></li>");
        $lista.append("<li>Humedad media: <strong>" + mediaHumedad + " %</strong></li>");
        
        $article.append($lista);
      }
    }

    // TAREA 8: Añadir al documento
    $("main > section:nth-of-type(3)").append($article);
  }

}