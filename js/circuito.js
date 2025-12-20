class Circuito {

    constructor() {
        this.comprobarApiFile();
    }
    
    comprobarApiFile() {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            console.log("API File soportada correctamente");
        } else {
            const mensaje = document.createElement("p");
            mensaje.textContent = "Error: Este navegador no soporta el API File de HTML5. Por favor, utilice un navegador moderno.";
            document.body.appendChild(mensaje);
        }
    }

    leerArchivoHTML(evento) {
        const archivo = evento.target.files[0];
        
        const lector = new FileReader();
        
        lector.onload = (eventoLectura) => {
            const contenidoHTML = eventoLectura.target.result;
            this.procesarContenidoHTML(contenidoHTML);
        };

        
        lector.readAsText(archivo);
    }

    procesarContenidoHTML(contenidoHTML) {
        const parser = new DOMParser();
        const documentoParseado = parser.parseFromString(contenidoHTML, "text/html");
        
        const mainParseado = documentoParseado.querySelector("main");

        
        const contenedorCircuito = document.querySelector("main");
        

        
        let contenedorInfoCircuito = contenedorCircuito.querySelector("section[data-tipo='contenido-html']");
        
        if (!contenedorInfoCircuito) {
            contenedorInfoCircuito = document.createElement("section");
            contenedorInfoCircuito.setAttribute("data-tipo", "contenido-html");
            
            const selectorHTML = contenedorCircuito.querySelector("section[data-tipo='selector-html']");
            if (selectorHTML && selectorHTML.nextSibling) {
                contenedorCircuito.insertBefore(contenedorInfoCircuito, selectorHTML.nextSibling);
            } else {
                contenedorCircuito.appendChild(contenedorInfoCircuito);
            }
        }
        
        contenedorInfoCircuito.innerHTML = "";
        
        const h3Info = document.createElement("h3");
        h3Info.textContent = "Información del Circuito";
        contenedorInfoCircuito.appendChild(h3Info);
        
        const secciones = mainParseado.querySelectorAll("section");
        secciones.forEach((seccion) => {
            const seccionClonada = seccion.cloneNode(true);
            contenedorInfoCircuito.appendChild(seccionClonada);
        });
        
    }
    

    añadirSelectorArchivo() {

        const contenedorSelector = document.createElement("section");
        contenedorSelector.setAttribute("data-tipo", "selector-html");
        
        const h3 = document.createElement("h3");
        h3.textContent = "Cargar Información del Circuito";
        contenedorSelector.appendChild(h3);
        
        const etiqueta = document.createElement("label");
        etiqueta.textContent = "Seleccione el archivo InfoCircuito.html: ";
        etiqueta.setAttribute("for", "selectorArchivo");
        
        const inputArchivo = document.createElement("input");
        inputArchivo.type = "file";
        inputArchivo.id = "selectorArchivo";
        inputArchivo.accept = ".html,text/html";
        
        inputArchivo.addEventListener("change", this.leerArchivoHTML.bind(this));
        
        contenedorSelector.appendChild(etiqueta);
        contenedorSelector.appendChild(inputArchivo);
        
        const main = document.querySelector("main");
        if (main) {
            main.appendChild(contenedorSelector);
        } else {
            document.body.appendChild(contenedorSelector);
        }
    }
}

class CargadorSVG {
    
    constructor() {
        this.comprobarApiFile();
    }
    
    comprobarApiFile() {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            console.log("API File para SVG soportada correctamente");
        } else {
            const mensaje = document.createElement("p");
            mensaje.textContent = "Error: Este navegador no soporta el API File de HTML5. Por favor, utilice un navegador moderno.";
            mensaje.style.color = "red";
            mensaje.style.fontWeight = "bold";
            document.body.appendChild(mensaje);
        }
    }
    
    leerArchivoSVG(evento) {
        const archivo = evento.target.files[0];

        
        const lector = new FileReader();
        
        lector.onload = (eventoLectura) => {
            const contenidoSVG = eventoLectura.target.result;
            this.insertarSVG(contenidoSVG);
        };

        
        lector.readAsText(archivo);
    }
    
    insertarSVG(contenidoSVG) {
        const parser = new DOMParser();
        const documentoSVG = parser.parseFromString(contenidoSVG, "image/svg+xml");
        
        const svgElemento = documentoSVG.querySelector("svg");
        
        const main = document.querySelector("main");
        let contenedorSVG = main ? main.querySelector("section[data-tipo='contenido-svg']") : null;
        
        if (!contenedorSVG) {
            contenedorSVG = document.createElement("section");
            contenedorSVG.setAttribute("data-tipo", "contenido-svg");
            
            const h3SVG = document.createElement("h3");
            h3SVG.textContent = "Perfil de Altimetría del Circuito";
            contenedorSVG.appendChild(h3SVG);
            
            const selectorSVG = main.querySelector("section[data-tipo='selector-svg']");
            if (selectorSVG && selectorSVG.nextSibling) {
                main.insertBefore(contenedorSVG, selectorSVG.nextSibling);
            } else {
                main.appendChild(contenedorSVG);
            }
        }
        
        const svgExistente = contenedorSVG.querySelector("svg");
        if (svgExistente) {
            svgExistente.remove();
        }
        
        contenedorSVG.appendChild(svgElemento);
    }
    
    añadirSelectorArchivoSVG() {
        const contenedorSelector = document.createElement("section");
        contenedorSelector.setAttribute("data-tipo", "selector-svg");
        
        const h3 = document.createElement("h3");
        h3.textContent = "Cargar Perfil de Altimetría";
        contenedorSelector.appendChild(h3);
        
        const etiqueta = document.createElement("label");
        etiqueta.textContent = "Seleccione el archivo altimetria.svg: ";
        etiqueta.setAttribute("for", "selectorArchivoSVG");
        
        const inputArchivo = document.createElement("input");
        inputArchivo.type = "file";
        inputArchivo.id = "selectorArchivoSVG";
        inputArchivo.accept = ".svg,image/svg+xml";
        
        inputArchivo.addEventListener("change", this.leerArchivoSVG.bind(this));
        
        contenedorSelector.appendChild(etiqueta);
        contenedorSelector.appendChild(inputArchivo);
        
        const main = document.querySelector("main");
        if (main) {
            main.appendChild(contenedorSelector);
        } else {
            document.body.appendChild(contenedorSelector);
        }
    }
}

class CargadorKML {
    
    constructor() {
        this.comprobarApiFile();
        this.mapa = null;
        this.coordenadas = [];
        this.puntoOrigen = null;
    }
    
    comprobarApiFile() {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            console.log("API File para KML soportada correctamente");
        } else {
            const mensaje = document.createElement("p");
            mensaje.textContent = "Error: Este navegador no soporta el API File de HTML5. Por favor, utilice un navegador moderno.";
            document.body.appendChild(mensaje);
        }
    }
    
    leerArchivoKML(evento) {
        const archivo = evento.target.files[0];
        
        const lector = new FileReader();
        
        lector.onload = (eventoLectura) => {
            const contenidoKML = eventoLectura.target.result;
            this.procesarKML(contenidoKML);
        };
        
        lector.readAsText(archivo);
    }
    
    procesarKML(contenidoKML) {
        const parser = new DOMParser();
        const documentoKML = parser.parseFromString(contenidoKML, "text/xml");
        
        const coordinatesElement = documentoKML.querySelector("coordinates");

        
        const coordenadasTexto = coordinatesElement.textContent.trim();
        const lineasCoordenadas = coordenadasTexto.split(/\s+/);
        
        this.coordenadas = [];
        
        lineasCoordenadas.forEach((linea) => {
            const partes = linea.split(",");
            if (partes.length >= 2) {
                const longitud = parseFloat(partes[0]);
                const latitud = parseFloat(partes[1]);
                if (!isNaN(longitud) && !isNaN(latitud)) {
                    this.coordenadas.push([longitud, latitud]);
                }
            }
        });
        
        if (this.coordenadas.length > 0) {
            this.puntoOrigen = this.coordenadas[0];
            this.insertarCapaKML();
        }
    }
    
    insertarCapaKML() {

        
        const main = document.querySelector("main");
        let contenedorMapa = main.querySelector("section[data-tipo='contenido-mapa']");
        
        if (!contenedorMapa) {
            contenedorMapa = document.createElement("section");
            contenedorMapa.setAttribute("data-tipo", "contenido-mapa");
            
            const h3Mapa = document.createElement("h3");
            h3Mapa.textContent = "Mapa del Circuito";
            contenedorMapa.appendChild(h3Mapa);
            
            const divMapa = document.createElement("div");
            contenedorMapa.appendChild(divMapa);
            
            const selectorKML = main.querySelector("section[data-tipo='selector-kml']");
            if (selectorKML && selectorKML.nextSibling) {
                main.insertBefore(contenedorMapa, selectorKML.nextSibling);
            } else {
                main.appendChild(contenedorMapa);
            }
        }
        
        const divMapa = contenedorMapa.querySelector("div");
        
        divMapa.innerHTML = "";
        
        
        mapboxgl.accessToken = 'pk.eyJ1IjoiY2x1YnNlcmciLCJhIjoiY21qZDByanljMDFhdTNjcjBlMmtkaDlybCJ9.TQFGAdBG_2kUtMk0kjqw3Q';
        
        this.mapa = new mapboxgl.Map({
            container: divMapa,
            style: 'mapbox://styles/mapbox/streets-v12',
            center: this.puntoOrigen,
            zoom: 14
        });
        
        this.mapa.on('load', () => {
            new mapboxgl.Marker({color: '#FF0000'})
                .setLngLat(this.puntoOrigen)
                .setPopup(new mapboxgl.Popup().setHTML('<h4>Punto de Origen</h4><p>Inicio del Circuito MotoGP</p>'))
                .addTo(this.mapa);
            
            this.mapa.addSource('ruta-circuito', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: this.coordenadas
                    }
                }
            });
            
            this.mapa.addLayer({
                id: 'ruta-circuito',
                type: 'line',
                source: 'ruta-circuito',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': '#0000FF',
                    'line-width': 4
                }
            });
            
        });
    }
    
    añadirSelectorArchivoKML() {
        const contenedorSelector = document.createElement("section");
        contenedorSelector.setAttribute("data-tipo", "selector-kml");
        
        const h3 = document.createElement("h3");
        h3.textContent = "Cargar Trazado del Circuito";
        contenedorSelector.appendChild(h3);
        
        const etiqueta = document.createElement("label");
        etiqueta.textContent = "Seleccione el archivo circuito.kml: ";
        etiqueta.setAttribute("for", "selectorArchivoKML");
        
        const inputArchivo = document.createElement("input");
        inputArchivo.type = "file";
        inputArchivo.id = "selectorArchivoKML";
        inputArchivo.accept = ".kml";
        
        inputArchivo.addEventListener("change", this.leerArchivoKML.bind(this));
        
        contenedorSelector.appendChild(etiqueta);
        contenedorSelector.appendChild(inputArchivo);
        
        const main = document.querySelector("main");
        if (main) {
            main.appendChild(contenedorSelector);
        } else {
            document.body.appendChild(contenedorSelector);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const circuito = new Circuito();
    circuito.añadirSelectorArchivo();
    
    const cargadorSVG = new CargadorSVG();
    cargadorSVG.añadirSelectorArchivoSVG();
    
    const cargadorKML = new CargadorKML();
    cargadorKML.añadirSelectorArchivoKML();
});