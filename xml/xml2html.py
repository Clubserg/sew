import xml.etree.ElementTree as ET
import os

# Archivos de entrada/salida
XML_CANDIDATES = ["circuitoEsquema.xml", "circuito.xml"]
HTML_OUT = "InfoCircuito.html"

# Namespace del XML
NS = {"ns": "http://www.uniovi.es"}

def find_existing_xml():
    """Busca el archivo XML del circuito"""
    for fname in XML_CANDIDATES:
        if os.path.exists(fname):
            return fname
    raise FileNotFoundError("No se encontró 'circuitoEsquema.xml' ni 'circuito.xml'")

class Html:
    """Clase para generar archivos HTML"""
    
    def __init__(self):
        self.content = []
        self.indent_level = 0
    
    def get_indent(self):
        """Retorna la indentación actual"""
        return "\t" * self.indent_level
    
    def add_line(self, line):
        """Añade una línea con indentación"""
        if line.strip():
            self.content.append(self.get_indent() + line)
        else:
            self.content.append(line)
    
    def increase_indent(self):
        """Aumenta el nivel de indentación"""
        self.indent_level += 1
    
    def decrease_indent(self):
        """Disminuye el nivel de indentación"""
        self.indent_level = max(0, self.indent_level - 1)
    
    def add_doctype(self):
        """Añade el DOCTYPE HTML5"""
        self.add_line('<!DOCTYPE HTML>')
    
    def open_tag(self, tag, attributes=None):
        """Abre una etiqueta con atributos opcionales"""
        if attributes:
            attrs = ' '.join([f'{k}="{v}"' for k, v in attributes.items()])
            self.add_line(f'<{tag} {attrs}>')
        else:
            self.add_line(f'<{tag}>')
        self.increase_indent()
    
    def close_tag(self, tag):
        """Cierra una etiqueta"""
        self.decrease_indent()
        self.add_line(f'</{tag}>')
    
    def add_self_closing_tag(self, tag, attributes):
        """Añade una etiqueta auto-cerrada"""
        attrs = ' '.join([f'{k}="{v}"' for k, v in attributes.items()])
        self.add_line(f'<{tag} {attrs} />')
    
    def add_content_tag(self, tag, content, attributes=None):
        """Añade una etiqueta con contenido en una línea"""
        if attributes:
            attrs = ' '.join([f'{k}="{v}"' for k, v in attributes.items()])
            self.add_line(f'<{tag} {attrs}>{content}</{tag}>')
        else:
            self.add_line(f'<{tag}>{content}</{tag}>')
    
    def write_to_file(self, filename):
        """Escribe el contenido HTML a un archivo"""
        with open(filename, 'w', encoding='utf-8') as f:
            f.write('\n'.join(self.content))

def extract_circuit_info(xml_path):
    """
    Extrae toda la información del circuito del XML usando XPath.
    Retorna un diccionario con toda la información.
    """
    tree = ET.parse(xml_path)
    root = tree.getroot()
    
    info = {}
    
    # Información básica usando XPath
    nombre = root.find("ns:nombre", NS)
    info['nombre'] = nombre.text if nombre is not None else "Circuito desconocido"
    
    longitud = root.find("ns:longitud", NS)
    if longitud is not None:
        info['longitud'] = longitud.text
        info['longitud_unidad'] = longitud.get('unidad', 'metros')
    
    anchura = root.find("ns:anchura", NS)
    if anchura is not None:
        info['anchura'] = anchura.text
        info['anchura_unidad'] = anchura.get('unidad', 'metros')
    
    fecha = root.find("ns:fecha", NS)
    info['fecha'] = fecha.text if fecha is not None else ""
    
    hora = root.find("ns:hora", NS)
    info['hora'] = hora.text if hora is not None else ""
    
    vueltas = root.find("ns:vueltas", NS)
    info['vueltas'] = vueltas.text if vueltas is not None else ""
    
    localidad = root.find("ns:localidad", NS)
    info['localidad'] = localidad.text if localidad is not None else ""
    
    pais = root.find("ns:pais", NS)
    info['pais'] = pais.text if pais is not None else ""
    
    patrocinador = root.find("ns:patrocinador", NS)
    info['patrocinador'] = patrocinador.text if patrocinador is not None else ""
    
    # Referencias usando XPath
    referencias = root.find("ns:referencias", NS)
    info['referencias'] = []
    if referencias is not None:
        for ref in referencias.findall("ns:referencia", NS):
            if ref.text:
                info['referencias'].append(ref.text)
    
    # Galería usando XPath
    galeria = root.find("ns:galeria", NS)
    info['fotografias'] = []
    info['videos'] = []
    if galeria is not None:
        for foto in galeria.findall("ns:fotografia", NS):
            if foto.text:
                info['fotografias'].append(foto.text)
        for video in galeria.findall("ns:video", NS):
            if video.text:
                info['videos'].append(video.text)
    
    # Resultado de carrera usando XPath
    resultado = root.find("ns:resultado_carrera", NS)
    if resultado is not None:
        nombre_piloto = resultado.find("ns:nombre_piloto", NS)
        tiempo = resultado.find("ns:tiempo", NS)
        info['ganador'] = nombre_piloto.text if nombre_piloto is not None else ""
        info['tiempo_ganador'] = tiempo.text if tiempo is not None else ""
    
    # Clasificación mundial usando XPath
    clasificacion = root.find("ns:clasificacion_mundial", NS)
    info['clasificacion'] = []
    if clasificacion is not None:
        for piloto in clasificacion.findall("ns:piloto", NS):
            nombre = piloto.find("ns:nombre_piloto", NS)
            puntos = piloto.find("ns:puntos", NS)
            posicion = piloto.find("ns:posicion", NS)
            
            piloto_info = {
                'nombre': nombre.text if nombre is not None else "",
                'puntos': puntos.text if puntos is not None else "",
                'posicion': posicion.text if posicion is not None else ""
            }
            info['clasificacion'].append(piloto_info)
    
    return info

def generate_html(info, output_path):
    """
    Genera el archivo HTML con la información del circuito
    """
    html = Html()
    
    # DOCTYPE
    html.add_doctype()
    html.add_line('')
    
    # HTML
    html.open_tag('html', {'lang': 'es'})
    
    # HEAD
    html.open_tag('head')
    html.add_self_closing_tag('meta', {'name': 'author', 'content': 'Sergio Fernandez-Miranda Longo'})
    html.add_self_closing_tag('meta', {'name': 'description', 'content': f'Información del circuito {info["nombre"]}'})
    html.add_self_closing_tag('meta', {'name': 'keywords', 'content': 'MotoGP, circuito, carrera'})
    html.add_self_closing_tag('meta', {'name': 'viewport', 'content': 'width=device-width, initial-scale=1.0'})
    html.add_line('')
    html.add_self_closing_tag('meta', {'charset': 'UTF-8'})
    html.add_content_tag('title', f'MotoGP - {info["nombre"]}')
    html.add_self_closing_tag('link', {'rel': 'icon', 'type': 'image/x-icon', 'href': '../multimedia/favicon.ico'})
    html.add_self_closing_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': '../estilo/estilo.css'})
    html.add_self_closing_tag('link', {'rel': 'stylesheet', 'type': 'text/css', 'href': '../estilo/layout.css'})
    html.close_tag('head')
    html.add_line('')
    
    # BODY
    html.open_tag('body')
    
    # HEADER
    html.open_tag('header')
    html.open_tag('h1')
    html.add_content_tag('a', 'MotoGP Desktop', {'href': '../index.html'})
    html.close_tag('h1')
    html.add_line('')
    
    # NAV dentro del header
    html.open_tag('nav')
    html.add_content_tag('a', 'Inicio', {'href': '../index.html', 'title': 'Inicio de la pagina de MotoGP'})
    html.add_content_tag('a', 'Piloto', {'href': '../piloto.html', 'title': 'Información del piloto'})
    html.add_content_tag('a', 'Circuito', {'href': '../circuito.html', 'title': 'Información de los circuitos'})
    html.add_content_tag('a', 'Meteorologia', {'href': '../meteorologia.html', 'title': 'Información del tiempo'})
    html.add_content_tag('a', 'Clasificaciones', {'href': '../clasificaciones.html', 'title': 'Información de la clasificacion'})
    html.add_content_tag('a', 'Juegos', {'href': '../juegos.html', 'title': 'Información de los juegos'})
    html.add_content_tag('a', 'Ayuda', {'href': '../ayuda.html', 'title': 'Ayuda'})
    html.close_tag('nav')
    html.close_tag('header')
    html.add_line('')
    
    # NAV para migas de pan
    html.open_tag('nav')
    html.open_tag('p')
    html.content[-1] = html.get_indent() + '<p>Estás en: '
    html.increase_indent()
    html.add_line('<a href="../index.html">Inicio</a> &gt;&gt;')
    html.add_line('<a href="../circuito.html">Circuito</a> &gt;&gt;')
    html.add_line(f'<span><strong>{info["nombre"]}</strong></span>')
    html.decrease_indent()
    html.add_line('</p>')
    html.close_tag('nav')
    html.add_line('')
    
    # Título de la página
    html.add_content_tag('h2', f'Información del Circuito: {info["nombre"]}')
    html.add_line('')
    
    # MAIN
    html.open_tag('main')
    
    # SECTION: Datos del Circuito
    html.open_tag('section')
    html.add_content_tag('h2', 'Datos del Circuito')
    html.open_tag('dl')
    
    html.add_content_tag('dt', 'Nombre')
    html.add_content_tag('dd', info['nombre'])
    
    if 'longitud' in info:
        html.add_content_tag('dt', 'Longitud')
        html.add_content_tag('dd', f"{info['longitud']} {info['longitud_unidad']}")
    
    if 'anchura' in info:
        html.add_content_tag('dt', 'Anchura')
        html.add_content_tag('dd', f"{info['anchura']} {info['anchura_unidad']}")
    
    if info.get('localidad'):
        html.add_content_tag('dt', 'Localidad')
        html.add_content_tag('dd', info['localidad'])
    
    if info.get('pais'):
        html.add_content_tag('dt', 'País')
        html.add_content_tag('dd', info['pais'])
    
    if info.get('patrocinador'):
        html.add_content_tag('dt', 'Patrocinador')
        html.add_content_tag('dd', info['patrocinador'])
    
    html.close_tag('dl')
    html.close_tag('section')
    html.add_line('')
    
    # SECTION: Información de la Carrera
    html.open_tag('section')
    html.add_content_tag('h2', 'Información de la Carrera')
    html.open_tag('dl')
    
    if info.get('fecha'):
        html.add_content_tag('dt', 'Fecha')
        html.add_content_tag('dd', info['fecha'])
    
    if info.get('hora'):
        html.add_content_tag('dt', 'Hora')
        html.add_content_tag('dd', info['hora'])
    
    if info.get('vueltas'):
        html.add_content_tag('dt', 'Número de Vueltas')
        html.add_content_tag('dd', info['vueltas'])
    
    html.close_tag('dl')
    html.close_tag('section')
    html.add_line('')
    
    # SECTION: Resultado de la Carrera
    if info.get('ganador'):
        html.open_tag('section')
        html.add_content_tag('h2', 'Resultado de la Carrera')
        html.open_tag('dl')
        
        html.add_content_tag('dt', 'Ganador')
        html.add_content_tag('dd', info['ganador'])
        
        if info.get('tiempo_ganador'):
            html.add_content_tag('dt', 'Tiempo')
            html.add_content_tag('dd', info['tiempo_ganador'])
        
        html.close_tag('dl')
        html.close_tag('section')
        html.add_line('')
    
    # SECTION: Clasificación Mundial
    if info.get('clasificacion'):
        html.open_tag('section')
        html.add_content_tag('h2', 'Clasificación Mundial')
        html.open_tag('ol')
        
        for piloto in info['clasificacion']:
            texto = f"{piloto['nombre']} - {piloto['puntos']} puntos"
            html.add_content_tag('li', texto)
        
        html.close_tag('ol')
        html.close_tag('section')
        html.add_line('')
    
    # SECTION: Referencias
    if info.get('referencias'):
        html.open_tag('section')
        html.add_content_tag('h2', 'Referencias')
        html.open_tag('ul')
        
        for ref in info['referencias']:
            html.open_tag('li')
            html.add_content_tag('a', ref, {'href': ref})
            html.close_tag('li')
        
        html.close_tag('ul')
        html.close_tag('section')
        html.add_line('')
    
    # SECTION: Galería
    if info.get('fotografias') or info.get('videos'):
        html.open_tag('section')
        html.add_content_tag('h2', 'Galería Multimedia')
        
        # Fotografías
        if info.get('fotografias'):
            html.open_tag('article')
            html.add_content_tag('h3', 'Fotografías')
            for i, foto in enumerate(info['fotografias'], 1):
                # Ajustar ruta relativa añadiendo ../
                foto_path = f"../{foto}" if not foto.startswith('../') else foto
                html.add_self_closing_tag('img', {'src': foto_path, 'alt': f'Fotografía {i} del circuito {info["nombre"]}'})
            html.close_tag('article')
        
        # Videos
        if info.get('videos'):
            html.open_tag('article')
            html.add_content_tag('h3', 'Videos')
            for video in info['videos']:
                # Ajustar ruta relativa añadiendo ../
                video_path = f"../{video}" if not video.startswith('../') else video
                html.open_tag('video', {'controls': 'controls'})
                html.add_self_closing_tag('source', {'src': video_path, 'type': 'video/mp4'})
                html.close_tag('video')
            html.close_tag('article')
        
        html.close_tag('section')
    
    html.close_tag('main')
    html.close_tag('body')
    html.close_tag('html')
    
    # Escribir archivo
    html.write_to_file(output_path)

if __name__ == "__main__":
    try:
        # Buscar y leer archivo XML
        xml_path = find_existing_xml()
        print(f"Leyendo archivo: {xml_path}")
        
        # Extraer información usando XPath
        circuit_info = extract_circuit_info(xml_path)
        print(f"Información extraída del circuito: {circuit_info['nombre']}")
        
        # Generar archivo HTML
        generate_html(circuit_info, HTML_OUT)
        print(f"✓ HTML generado: {HTML_OUT}")
        print(f"\nEl archivo HTML cumple con:")

        
    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()