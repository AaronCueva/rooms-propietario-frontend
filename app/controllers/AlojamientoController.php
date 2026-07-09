<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Alojamiento;
use App\Models\Multimedia;
use App\Models\Catalogo;
use App\Models\Ubicacion;

use App\Models\Servicio;
use App\Models\PoliticaCasa;
use App\Models\AlojamientoServicio;
use App\Models\AlojamientoPolitica;
use App\Models\AlojamientoUniversidad;
use App\Core\AzureStorage;

class AlojamientoController extends Controller
{
    private $alojamientoModel;
    private $multimediaModel;
    private $catalogoModel;
    private $ubicacionModel;
    private $servicioModel;
    private $politicaModel;
    private $alojamientoServicioModel;
    private $alojamientoPoliticaModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->alojamientoModel = new Alojamiento();
        $this->multimediaModel = new Multimedia();
        $this->catalogoModel = new Catalogo();
        $this->ubicacionModel = new Ubicacion();
        $this->servicioModel = new Servicio();
        $this->politicaModel = new PoliticaCasa();
        $this->alojamientoServicioModel = new AlojamientoServicio();
        $this->alojamientoPoliticaModel = new AlojamientoPolitica();
    }

    /**
     * Listado de alojamientos del propietario
     */
    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $alojamientos = $this->alojamientoModel->obtenerPorUsuarioId($usuario_id);

        $this->render('propietario/alojamientos/index', [
            'alojamientos' => $alojamientos,
            'titulo' => 'Mis alojamientos'
        ]);
    }

    /**
     * Formulario para crear un nuevo alojamiento
     */
    public function crear()
    {
        $tipos_alojamiento = $this->catalogoModel->obtenerPorReferencia('TIPO_PUBLICACION_ALOJAMIENTO');
        $generos = $this->catalogoModel->obtenerPorReferencia('GENERO_EXCLUSIVO_ALOJAMIENTO');
        $monedas = $this->catalogoModel->obtenerPorReferencia('TIPO_MONEDA');
        $departamentos = $this->ubicacionModel->obtenerDepartamentos();

        $this->render('propietario/alojamientos/create', [
            'tipos_alojamiento' => $tipos_alojamiento,
            'generos' => $generos,
            'monedas' => $monedas,
            'departamentos' => $departamentos,
            'titulo' => 'Publicar nuevo cuarto'
        ]);
    }

    /**
     * Procesar y guardar el nuevo alojamiento con sus fotos
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $usuario_id = $_SESSION['usuario_id'];

        $datos = [
            'titulo'                  => $_POST['titulo'] ?? '',
            'tipo_codigo'             => $_POST['tipo_codigo'] ?? null,
            'descripcion'             => $_POST['descripcion'] ?? null,
            'numero_habitaciones'     => $_POST['numero_habitaciones'] ?? 1,
            'numero_banos'            => $_POST['numero_banos'] ?? 1,
            'bano_privado'            => isset($_POST['bano_privado']) ? true : false,
            'tamano_m2'               => $_POST['tamano_m2'] ?? null,
            'genero_exclusivo_codigo' => $_POST['genero_exclusivo_codigo'] ?? null,
            'mascotas_permitidas'     => isset($_POST['mascotas_permitidas']) ? true : false,
            'fumadores_permitidos'    => isset($_POST['fumadores_permitidos']) ? true : false,
            'ubicacion_id'            => $_POST['distrito'] ?? null,
            'direccion'               => $_POST['direccion'] ?? null,
            'latitud'                 => !empty($_POST['latitud']) ? $_POST['latitud'] : null,
            'longitud'                => !empty($_POST['longitud']) ? $_POST['longitud'] : null,
            'precio_mensual'          => $_POST['precio_mensual'] ?? 0,
            'moneda_codigo'           => $_POST['moneda_codigo'] ?? 'PEN',
            'garantia'                => $_POST['garantia'] ?? null,
            'duracion_minima_meses'   => $_POST['duracion_minima_meses'] ?? null,
            'fecha_disponible'        => !empty($_POST['fecha_disponible']) ? $_POST['fecha_disponible'] : null,
            'amoblado'                => isset($_POST['amoblado']) ? true : false,
            'estado_codigo'           => 'EPA001',
            'usuario_id'              => $usuario_id
        ];

        // Validaciones básicas
        if (empty($datos['titulo']) || empty($datos['precio_mensual'])) {
            $this->setFlash('error', 'El título y el precio mensual son obligatorios.');
            $this->redirect('/alojamientos/nuevo');
        }

        try {
            // Crear el alojamiento y obtener su ID
            $alojamiento_id = $this->alojamientoModel->crear($datos);

            if (!$alojamiento_id) {
                $this->setFlash('error', 'No se pudo crear el alojamiento.');
                $this->redirect('/alojamientos/nuevo');
            }

            // Procesar fotos — Azure Blob Storage
            if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $total_fotos = count($_FILES['fotos']['name']);
                for ($i = 0; $i < $total_fotos; $i++) {
                    if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['fotos']['tmp_name'][$i];
                        $original_name = $_FILES['fotos']['name'][$i];
                        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                        if (!in_array($extension, $allowed)) {
                            continue;
                        }

                        $new_name = 'alojamientos/' . $alojamiento_id . '_' . ($i + 1) . '_' . time() . '.' . $extension;

                        $mimeType = 'image/jpeg';
                        if (function_exists('mime_content_type')) {
                            $mimeType = mime_content_type($tmp_name);
                        }
                        if (!$mimeType) $mimeType = 'image/jpeg';

                        $azureUrl = AzureStorage::uploadFile($tmp_name, $new_name, $mimeType);

                        if ($azureUrl) {
                            $this->multimediaModel->guardarFotoAlojamiento(
                                $alojamiento_id,
                                $azureUrl,
                                $original_name,
                                $i + 1
                            );
                        }
                    }
                }
            }

            // Sincronizar cercanía a universidades (alojamiento_universidad) según coordenadas
            if (!empty($datos['latitud']) && !empty($datos['longitud'])) {
                (new AlojamientoUniversidad())->sincronizarParaAlojamiento(
                    $alojamiento_id,
                    $datos['latitud'],
                    $datos['longitud']
                );
            }

            $this->setFlash('success', '¡Cuarto publicado exitosamente!');
            $this->redirect('/alojamientos');

        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al crear el alojamiento: ' . $e->getMessage());
            $this->redirect('/alojamientos/nuevo');
        }
    }

    /**
     * Vista de Edición (Datos, Fotos, Servicios, Políticas)
     */
    public function edit()
    {
        $alojamiento_id = $_GET['id'] ?? null;
        if (!$alojamiento_id) {
            $this->redirect('/alojamientos');
        }

        $alojamiento = $this->alojamientoModel->obtenerPorId($alojamiento_id);
        if (!$alojamiento || $alojamiento['usuario_id'] !== $_SESSION['usuario_id']) {
            $this->redirect('/alojamientos');
        }

        $tipos_alojamiento = $this->catalogoModel->obtenerPorReferencia('TIPO_PUBLICACION_ALOJAMIENTO');
        $generos = $this->catalogoModel->obtenerPorReferencia('GENERO_EXCLUSIVO_ALOJAMIENTO');
        $monedas = $this->catalogoModel->obtenerPorReferencia('TIPO_MONEDA');
        $departamentos = $this->ubicacionModel->obtenerDepartamentos();
        
        $fotos = $this->multimediaModel->obtenerFotosPorAlojamiento($alojamiento_id);
        
        // Relacionados
        $servicios_asignados = $this->alojamientoServicioModel->obtenerPorAlojamiento($alojamiento_id);
        $politicas_asignadas = $this->alojamientoPoliticaModel->obtenerPorAlojamiento($alojamiento_id);
        
        // Descuentos y beneficios
        $descuentos = $this->alojamientoModel->obtenerDescuentos($alojamiento_id);
        $beneficios = $this->alojamientoModel->obtenerBeneficios($alojamiento_id);

        // Catálogos
        $servicios_disponibles = $this->servicioModel->obtenerTodos();
        $politicas_disponibles = $this->politicaModel->obtenerTodos();

        // Reseñas del alojamiento (resenia_alojamiento) + universidades cercanas
        $reseniaModel = new \App\Models\ReseniaAlojamiento();
        $resenas = $reseniaModel->getByAlojamientoId($alojamiento_id);

        $alojamientoUniversidadModel = new AlojamientoUniversidad();
        $universidades_cercanas = $alojamientoUniversidadModel->obtenerPorAlojamiento($alojamiento_id);

        $this->render('propietario/alojamientos/edit', [
            'alojamiento' => $alojamiento,
            'tipos_alojamiento' => $tipos_alojamiento,
            'generos' => $generos,
            'monedas' => $monedas,
            'departamentos' => $departamentos,
            'fotos' => $fotos,
            'servicios_asignados' => $servicios_asignados,
            'politicas_asignadas' => $politicas_asignadas,
            'descuentos' => $descuentos,
            'beneficios' => $beneficios,
            'servicios_disponibles' => $servicios_disponibles,
            'politicas_disponibles' => $politicas_disponibles,
            'resenas' => $resenas,
            'universidades_cercanas' => $universidades_cercanas,
            'titulo' => 'Editar alojamiento'
        ]);
    }

    /**
     * El propietario responde a una reseña de su alojamiento (resenia_alojamiento)
     */
    public function responderResena()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $resenia_id = $_POST['resenia_alojamiento_id'] ?? null;
        $respuesta = trim($_POST['respuesta'] ?? '');

        if (!$alojamiento_id || !$resenia_id || $respuesta === '') {
            $this->setFlash('error', 'Datos inválidos para responder la reseña.');
            $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
        }

        $reseniaModel = new \App\Models\ReseniaAlojamiento();
        if ($reseniaModel->responder($resenia_id, $_SESSION['usuario_id'], $respuesta)) {
            $this->setFlash('success', 'Respuesta publicada correctamente.');
        } else {
            $this->setFlash('error', 'No se pudo guardar la respuesta. Verifica que la reseña pertenezca a tu alojamiento.');
        }

        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Procesar actualización básica
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $usuario_id = $_SESSION['usuario_id'];

        $datos = [
            'titulo'                  => $_POST['titulo'] ?? '',
            'tipo_codigo'             => $_POST['tipo_codigo'] ?? null,
            'descripcion'             => $_POST['descripcion'] ?? null,
            'numero_habitaciones'     => $_POST['numero_habitaciones'] ?? 1,
            'numero_banos'            => $_POST['numero_banos'] ?? 1,
            'bano_privado'            => isset($_POST['bano_privado']) ? true : false,
            'tamano_m2'               => $_POST['tamano_m2'] ?? null,
            'genero_exclusivo_codigo' => $_POST['genero_exclusivo_codigo'] ?? null,
            'mascotas_permitidas'     => isset($_POST['mascotas_permitidas']) ? true : false,
            'fumadores_permitidos'    => isset($_POST['fumadores_permitidos']) ? true : false,
            'ubicacion_id'            => $_POST['distrito'] ?? null,
            'direccion'               => $_POST['direccion'] ?? null,
            'latitud'                 => !empty($_POST['latitud']) ? $_POST['latitud'] : null,
            'longitud'                => !empty($_POST['longitud']) ? $_POST['longitud'] : null,
            'precio_mensual'          => $_POST['precio_mensual'] ?? 0,
            'moneda_codigo'           => $_POST['moneda_codigo'] ?? 'PEN',
            'garantia'                => $_POST['garantia'] ?? null,
            'duracion_minima_meses'   => $_POST['duracion_minima_meses'] ?? null,
            'fecha_disponible'        => !empty($_POST['fecha_disponible']) ? $_POST['fecha_disponible'] : null,
            'amoblado'                => isset($_POST['amoblado']) ? true : false,
            'usuario_id'              => $usuario_id
        ];

        $this->alojamientoModel->actualizar($alojamiento_id, $datos);

        // Re-sincronizar cercanía a universidades si las coordenadas cambiaron
        if (!empty($datos['latitud']) && !empty($datos['longitud'])) {
            (new AlojamientoUniversidad())->sincronizarParaAlojamiento(
                $alojamiento_id,
                $datos['latitud'],
                $datos['longitud']
            );
        }

        $this->setFlash('success', 'Datos guardados correctamente.');
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * API: Asignar o remover servicio
     */
    public function gestionarServicios()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $accion = $_POST['accion'] ?? '';
        $alojamiento_id = $_POST['alojamiento_id'];
        $servicio_id = $_POST['servicio_id'];
        
        if ($accion === 'agregar') {
            $precio = $_POST['precio'] ?? 0;
            $this->alojamientoServicioModel->asociar($alojamiento_id, $servicio_id, $precio);
        } else if ($accion === 'remover') {
            $this->alojamientoServicioModel->remover($alojamiento_id, $servicio_id);
        }
        
        $this->setFlash('success', 'Servicios actualizados.');
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * API: Asignar, remover o crear política
     */
    public function gestionarPoliticas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $accion = $_POST['accion'] ?? '';
        $alojamiento_id = $_POST['alojamiento_id'];
        
        if ($accion === 'agregar') {
            $politica_id = $_POST['politica_casa_id'] ?? null;
            if ($politica_id) {
                $this->alojamientoPoliticaModel->asociar($alojamiento_id, $politica_id);
            }
        } else if ($accion === 'remover') {
            $politica_id = $_POST['politica_casa_id'];
            $this->alojamientoPoliticaModel->remover($alojamiento_id, $politica_id);
        } else if ($accion === 'crear') {
            $nombre = $_POST['nueva_politica'] ?? '';
            if (!empty($nombre)) {
                $usuario = $_SESSION['nombres'] ?? 'Propietario';
                $nuevo_id = $this->politicaModel->crear($nombre, $usuario);
                if ($nuevo_id) {
                    $this->alojamientoPoliticaModel->asociar($alojamiento_id, $nuevo_id);
                }
            }
        }
        
        $this->setFlash('success', 'Políticas actualizadas.');
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Eliminar un alojamiento (soft delete)
     */
    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $usuario_id = $_SESSION['usuario_id'];

        if ($alojamiento_id) {
            $this->alojamientoModel->eliminar($alojamiento_id, $usuario_id);
            $this->setFlash('success', 'Alojamiento eliminado correctamente.');
        }

        $this->redirect('/alojamientos');
    }

    /**
     * Subir nuevas fotos a un alojamiento existente
     */
    public function agregarFotos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        if (!$alojamiento_id) {
            $this->redirect('/alojamientos');
        }

        // Obtener el orden máximo actual
        $fotosExistentes = $this->multimediaModel->obtenerFotosPorAlojamiento($alojamiento_id);
        $ordenMax = 0;
        foreach ($fotosExistentes as $f) {
            if ($f['orden'] > $ordenMax) $ordenMax = $f['orden'];
        }

        if (isset($_FILES['nuevas_fotos']) && !empty($_FILES['nuevas_fotos']['name'][0])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $total = count($_FILES['nuevas_fotos']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['nuevas_fotos']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['nuevas_fotos']['tmp_name'][$i];
                    $original_name = $_FILES['nuevas_fotos']['name'][$i];
                    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                    if (!in_array($extension, $allowed)) continue;

                    $new_name = 'alojamientos/' . $alojamiento_id . '_' . ($ordenMax + 1) . '_' . time() . '.' . $extension;

                    $mimeType = 'image/jpeg';
                    if (function_exists('mime_content_type')) {
                        $mimeType = mime_content_type($tmp_name);
                    }
                    if (!$mimeType) $mimeType = 'image/jpeg';

                    $azureUrl = AzureStorage::uploadFile($tmp_name, $new_name, $mimeType);

                    if ($azureUrl) {
                        $ordenMax++;
                        $this->multimediaModel->guardarFotoAlojamiento(
                            $alojamiento_id,
                            $azureUrl,
                            $original_name,
                            $ordenMax
                        );
                    }
                }
            }
        }

        $this->setFlash('success', 'Fotos subidas correctamente.');
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Eliminar (deshabilitar) una foto de un alojamiento
     */
    public function eliminarFoto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $multimedia_id = $_POST['multimedia_id'] ?? null;

        if ($multimedia_id) {
            $this->multimediaModel->eliminar($multimedia_id);
            $this->setFlash('success', 'Foto eliminada.');
        }

        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Establecer una foto como principal del alojamiento
     */
    public function establecerFotoPrincipal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alojamientos');
        }

        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $multimedia_id = $_POST['multimedia_id'] ?? null;

        if (!$alojamiento_id || !$multimedia_id) {
            $this->redirect('/alojamientos');
        }

        // Verificar que el alojamiento pertenece al propietario
        $alojamiento = $this->alojamientoModel->obtenerPorId($alojamiento_id);
        if (!$alojamiento || $alojamiento['usuario_id'] !== $_SESSION['usuario_id']) {
            $this->redirect('/alojamientos');
        }

        if ($this->multimediaModel->establecerPrincipal($multimedia_id, $alojamiento_id)) {
            $this->setFlash('success', 'Foto principal actualizada correctamente.');
        } else {
            $this->setFlash('error', 'No se pudo actualizar la foto principal.');
        }

        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Agregar descuento
     */
    public function agregarDescuento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/alojamientos');
        
        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        if (!$alojamiento_id) $this->redirect('/alojamientos');

        $datos = [
            'alojamiento_id' => $alojamiento_id,
            'nombre' => $_POST['nombre'] ?? '',
            'monto' => $_POST['monto'] ?? 0,
            'motivo' => $_POST['motivo'] ?? null,
            'inicio' => $_POST['inicio'] ?? null,
            'fin' => $_POST['fin'] ?? null,
        ];

        if ($this->alojamientoModel->agregarDescuento($datos)) {
            $this->setFlash('success', 'Descuento agregado.');
        } else {
            $this->setFlash('error', 'Error al agregar descuento.');
        }
        
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Eliminar descuento
     */
    public function eliminarDescuento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/alojamientos');
        
        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $descuento_id = $_POST['descuento_id'] ?? null;
        
        if ($alojamiento_id && $descuento_id) {
            $this->alojamientoModel->eliminarDescuento($descuento_id, $alojamiento_id);
            $this->setFlash('success', 'Descuento eliminado.');
        }
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Agregar beneficio
     */
    public function agregarBeneficio()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/alojamientos');
        
        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        if (!$alojamiento_id) $this->redirect('/alojamientos');

        $datos = [
            'alojamiento_id' => $alojamiento_id,
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null,
        ];

        if ($this->alojamientoModel->agregarBeneficio($datos)) {
            $this->setFlash('success', 'Beneficio agregado.');
        } else {
            $this->setFlash('error', 'Error al agregar beneficio.');
        }
        
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }

    /**
     * Eliminar beneficio
     */
    public function eliminarBeneficio()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/alojamientos');
        
        $alojamiento_id = $_POST['alojamiento_id'] ?? null;
        $beneficio_id = $_POST['beneficio_id'] ?? null;
        
        if ($alojamiento_id && $beneficio_id) {
            $this->alojamientoModel->eliminarBeneficio($beneficio_id, $alojamiento_id);
            $this->setFlash('success', 'Beneficio eliminado.');
        }
        $this->redirect('/alojamientos/editar?id=' . $alojamiento_id);
    }
}
