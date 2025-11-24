
import xml.etree.ElementTree as ET
import os, math
from typing import List, Tuple
import numpy as np
import matplotlib.pyplot as plt

# Archivos de entrada/salida
XML_CANDIDATES = ["circuitoEsquema.xml", "circuito.xml"]
KML_OUT = "circuito.kml"
PNG_OUT = "circuito.png"

# Namespace del XML (por defecto en tu XML)
NS = {"ns": "http://www.uniovi.es"}

def find_existing_xml() -> str:
    for fname in XML_CANDIDATES:
        if os.path.exists(fname):
            return fname
    raise FileNotFoundError("No se encontró 'circuitoEsquema.xml' ni 'circuito.xml' en el directorio actual.")

def read_points_from_xml(xml_path: str) -> List[Tuple[float, float]]:
    tree = ET.parse(xml_path)
    root = tree.getroot()
    points = []

    # punto_origen del circuito
    po = root.find("ns:punto_origen", NS)
    if po is not None:
        lon = float(po.find("ns:longitud_punto", NS).text)
        lat = float(po.find("ns:latitud_punto", NS).text)
        points.append((lon, lat))

    # puntos origen de cada tramo
    tramos = root.find("ns:tramos", NS)
    if tramos is not None:
        for tramo in tramos.findall("ns:tramo", NS):
            p = tramo.find("ns:punto_origen", NS)
            if p is not None:
                lon = float(p.find("ns:longitud_punto", NS).text)
                lat = float(p.find("ns:latitud_punto", NS).text)
                points.append((lon, lat))

    return points

def catmull_rom_spline(P: List[Tuple[float, float]], n_points_per_seg: int = 100, alpha: float = 0.5) -> List[Tuple[float, float]]:
    """
    Genera muchos puntos usando spline Catmull-Rom centrípeta.
    Si hay menos de 4 puntos de control, densifica en línea recta.
    """
    if len(P) < 4:
        densified = []
        for i in range(len(P) - 1):
            p0 = np.array(P[i]); p1 = np.array(P[i+1])
            for t in np.linspace(0, 1, n_points_per_seg, endpoint=False):
                pt = (1-t) * p0 + t * p1
                densified.append(tuple(pt))
        densified.append(P[-1])
        return densified

    def tj(ti, pi, pj):
        return (((pj[0]-pi[0])**2 + (pj[1]-pi[1])**2)**0.5)**alpha + ti

    pts = []
    P_pad = [P[0]] + P + [P[-1]]  # relleno de extremos
    for i in range(1, len(P_pad) - 2):
        p0, p1, p2, p3 = map(np.array, (P_pad[i-1], P_pad[i], P_pad[i+1], P_pad[i+2]))
        t0 = 0.0
        t1 = tj(t0, p0, p1)
        t2 = tj(t1, p1, p2)
        t3 = tj(t2, p2, p3)
        for t in np.linspace(t1, t2, n_points_per_seg, endpoint=False):
            A1 = (t1 - t)/(t1 - t0) * p0 + (t - t0)/(t1 - t0) * p1
            A2 = (t2 - t)/(t2 - t1) * p1 + (t - t1)/(t2 - t1) * p2
            A3 = (t3 - t)/(t3 - t2) * p2 + (t - t2)/(t3 - t2) * p3
            B1 = (t2 - t)/(t2 - t0) * A1 + (t - t0)/(t2 - t0) * A2
            B2 = (t3 - t)/(t3 - t1) * A2 + (t - t1)/(t3 - t1) * A3
            C  = (t2 - t)/(t2 - t1) * B1 + (t - t1)/(t2 - t1) * B2
            pts.append(tuple(C))
    pts.append(tuple(P[-1]))
    return pts

def compute_curvature(points: List[Tuple[float, float]]) -> List[float]:
    """
    Métrica de curvatura aproximada: ángulo entre segmentos consecutivos (en grados).
    """
    curv = [0.0]
    for i in range(1, len(points)-1):
        p_prev = np.array(points[i-1])
        p = np.array(points[i])
        p_next = np.array(points[i+1])
        v1 = p - p_prev; v2 = p_next - p
        if np.linalg.norm(v1) == 0 or np.linalg.norm(v2) == 0:
            curv.append(0.0); continue
        v1 = v1 / np.linalg.norm(v1); v2 = v2 / np.linalg.norm(v2)
        dot = float(np.clip(np.dot(v1, v2), -1.0, 1.0))
        ang = math.degrees(math.acos(dot))
        curv.append(ang)
    curv.append(0.0)
    return curv

def split_straights_curves(points: List[Tuple[float, float]], curvature: List[float], threshold_deg: float = 1.5):
    straights, curves = [], []
    for i, pt in enumerate(points):
        (curves if curvature[i] >= threshold_deg else straights).append(pt)
    return straights, curves

def write_kml(points, straights, curves, kml_path: str):
    def coords_str(pts):
        return " ".join([f"{lon},{lat},0" for lon, lat in pts])

    kml = f"""<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Circuito MotoGP - Planimetría (estimada)</name>

    <Style id="straightStyle">
      <LineStyle><color>ff00ff00</color><width>3</width></LineStyle>
    </Style>
    <Style id="curveStyle">
      <LineStyle><color>ff0000ff</color><width>3</width></LineStyle>
    </Style>

    <Placemark>
      <name>Polilínea completa</name>
      <LineString><tessellate>1</tessellate><coordinates>
        {coords_str(points)}
      </coordinates></LineString>
    </Placemark>

    <Placemark>
      <name>Rectas (estimadas)</name>
      <styleUrl>#straightStyle</styleUrl>
      <LineString><tessellate>1</tessellate><coordinates>
        {coords_str(straights)}
      </coordinates></LineString>
    </Placemark>

    <Placemark>
      <name>Curvas (estimadas)</name>
      <styleUrl>#curveStyle</styleUrl>
      <LineString><tessellate>1</tessellate><coordinates>
        {coords_str(curves)}
      </coordinates></LineString>
    </Placemark>

  </Document>
</kml>
"""
    with open(kml_path, "w", encoding="utf-8") as f:
        f.write(kml)

def plot_png(points, straights, curves, out_path: str):
    pts = np.array(points)
    plt.figure(figsize=(8, 8))
    plt.plot(pts[:,0], pts[:,1], color="#888888", linewidth=1, label="Trazado (total)")
    if straights:
        s = np.array(straights)
        plt.scatter(s[:,0], s[:,1], s=4, c="#00aa00", label="Rectas (estimadas)")
    if curves:
        c = np.array(curves)
        plt.scatter(c[:,0], c[:,1], s=4, c="#0066ff", label="Curvas (estimadas)")
    plt.gca().set_aspect('equal', adjustable='box')
    plt.xlabel('Longitud (°)'); plt.ylabel('Latitud (°)')
    plt.title('Planimetría del Circuito (estimada a partir del XML)')
    plt.legend(); plt.grid(True, alpha=0.3); plt.tight_layout()
    plt.savefig(out_path, dpi=150); plt.close()

if __name__ == "__main__":
    xml_path = find_existing_xml()
    ctrl_points = read_points_from_xml(xml_path)
    if len(ctrl_points) < 2:
        raise RuntimeError("No hay suficientes puntos en el XML para construir el trazado.")
    many_points = catmull_rom_spline(ctrl_points, n_points_per_seg=100)
    curvature = compute_curvature(many_points)
    straights, curves = split_straights_curves(many_points, curvature, threshold_deg=1.5)
    write_kml(many_points, straights, curves, KML_OUT)
    plot_png(many_points, straights, curves, PNG_OUT)
