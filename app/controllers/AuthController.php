<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Catalogo;
use App\Models\Ubicacion;

class AuthController extends Controller
{
    private $usuarioModel;
    private $rolModel;
    private $catalogoModel;
    private $ubicacionModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        $this->catalogoModel = new Catalogo();
        $this->ubicacionModel = new Ubicacion();
    }

    public function showLogin()
    {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [], 'auth');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($correo) || empty($password)) {
                $this->setFlash('error', 'Todos los campos son obligatorios.');
                $this->redirect('/login');
            }

            $usuario = $this->usuarioModel->findByEmail($correo);

            if ($usuario) {
                if ($usuario['habilitado'] === false) {
                    $this->setFlash('error', 'Su cuenta ha sido deshabilitada.');
                    $this->redirect('/login');
                }

                if (password_verify($password, $usuario['password'])) {
                    $rol_propietario_id = $this->rolModel->obtenerIdPorCodigo('PROPIETARIO');
                    
                    if ($usuario['rol_id'] != $rol_propietario_id) {
                         $this->setFlash('error', 'No tiene permisos para acceder al portal de propietarios.');
                         $this->redirect('/login');
                    }

                    $_SESSION['usuario_id'] = $usuario['usuario_id'];
                    $_SESSION['nombres'] = $usuario['nombres'];
                    $_SESSION['apellidos'] = $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno'];
                    $_SESSION['correo'] = $usuario['correo'];
                    $_SESSION['rol_id'] = $usuario['rol_id'];
                    $_SESSION['url_foto'] = $usuario['url_foto'] ?? null;

                    $this->redirect('/dashboard');
                } else {
                    $this->setFlash('error', 'Contraseña incorrecta.');
                    $this->redirect('/login');
                }
            } else {
                $this->setFlash('error', 'No existe una cuenta con este correo.');
                $this->redirect('/login');
            }
        }
    }

    public function showRegister()
    {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }

        $tipos_documento = $this->catalogoModel->obtenerPorReferencia('CAT_TIPODOC');
        $departamentos = $this->ubicacionModel->obtenerDepartamentos();

        $this->render('auth/register', [
            'tipos_documento' => $tipos_documento,
            'departamentos' => $departamentos
        ], 'auth');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rol_id = $this->rolModel->obtenerIdPorCodigo('PROPIETARIO');
            
            if (!$rol_id) {
                 $this->setFlash('error', 'Error del sistema: Rol de Propietario no configurado.');
                 $this->redirect('/register');
            }

            $datos = [
                'nombres' => filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING),
                'apellido_paterno' => filter_input(INPUT_POST, 'apellido_paterno', FILTER_SANITIZE_STRING),
                'apellido_materno' => filter_input(INPUT_POST, 'apellido_materno', FILTER_SANITIZE_STRING),
                'tipo_documento_codigo' => filter_input(INPUT_POST, 'tipo_documento_codigo', FILTER_SANITIZE_STRING),
                'numero_documento' => filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING),
                'correo' => filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL),
                'celular' => filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING),
                'telefono' => filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING),
                'ubicacion_id' => filter_input(INPUT_POST, 'distrito', FILTER_SANITIZE_STRING),
                'universidad_id' => null, // El propietario no tiene universidad vinculada en el registro
                'password' => $_POST['password'] ?? '',
                'rol_id' => $rol_id
            ];

            $existe = $this->usuarioModel->findByEmail($datos['correo']);
            if ($existe) {
                $this->setFlash('error', 'Ya existe un usuario registrado con este correo.');
                $this->redirect('/register');
            }

            if ($this->usuarioModel->create($datos)) {
                $this->setFlash('success', 'Cuenta de propietario creada exitosamente. Inicie sesión.');
                $this->redirect('/login');
            } else {
                $this->setFlash('error', 'Ocurrió un error al crear la cuenta. Inténtelo de nuevo.');
                $this->redirect('/register');
            }
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }
}
