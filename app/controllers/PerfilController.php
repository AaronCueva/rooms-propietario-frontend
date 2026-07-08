<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class PerfilController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->usuarioModel = new Usuario();
    }

    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $usuario = $this->usuarioModel->findById($usuario_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombres' => $_POST['nombres'] ?? '',
                'apellido_paterno' => $_POST['apellido_paterno'] ?? '',
                'apellido_materno' => $_POST['apellido_materno'] ?? '',
                'correo' => $_POST['correo'] ?? '',
                'celular' => $_POST['celular'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'tipo_documento_codigo' => $_POST['tipo_documento_codigo'] ?? '',
                'numero_documento' => $_POST['numero_documento'] ?? ''
            ];

            // Handle file upload
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/perfiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
                $fileName = $_FILES['foto_perfil']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = $usuario_id . '_' . time() . '.' . $fileExtension;
                    $destPath = $uploadDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $datos['url_foto'] = '/public/uploads/perfiles/' . $newFileName;
                        $_SESSION['url_foto'] = $datos['url_foto']; // Update session
                    }
                }
            }

            if ($this->usuarioModel->actualizarPerfil($usuario_id, $datos)) {
                // Actualizar nombres en sesión
                $_SESSION['nombres'] = $datos['nombres'];
                $_SESSION['apellidos'] = $datos['apellido_paterno'];
                $this->setFlash('success', 'Perfil actualizado exitosamente.');
                $this->redirect('/perfil');
            } else {
                $this->setFlash('error', 'Ocurrió un error al actualizar el perfil.');
            }
        }

        // Obtener catálogos para tipo de documento
        $db = \App\Core\Database::getInstance()->getConnection();
        $tipos_documento = $db->query("SELECT codigo, nombre FROM catalogo WHERE referencia_codigo = 'TIPO_DOCUMENTO' AND habilitado = TRUE ORDER BY orden")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('propietario/perfil/index', [
            'usuario' => $usuario,
            'tipos_documento' => $tipos_documento,
            'titulo' => 'Mi Perfil'
        ]);
    }

    public function password()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario_id = $_SESSION['usuario_id'];
            $password_actual = $_POST['password_actual'] ?? '';
            $nueva_password = $_POST['nueva_password'] ?? '';
            $confirmar_password = $_POST['confirmar_password'] ?? '';

            if (empty($password_actual) || empty($nueva_password) || empty($confirmar_password)) {
                $this->setFlash('error', 'Todos los campos son obligatorios.');
                $this->redirect('/perfil/password');
            }

            if ($nueva_password !== $confirmar_password) {
                $this->setFlash('error', 'Las nuevas contraseñas no coinciden.');
                $this->redirect('/perfil/password');
            }

            $hash_actual = $this->usuarioModel->obtenerPasswordHash($usuario_id);

            if (password_verify($password_actual, $hash_actual)) {
                $nuevo_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                if ($this->usuarioModel->actualizarPassword($usuario_id, $nuevo_hash)) {
                    $this->setFlash('success', 'Contraseña actualizada exitosamente.');
                    $this->redirect('/perfil');
                } else {
                    $this->setFlash('error', 'Ocurrió un error al actualizar la contraseña.');
                }
            } else {
                $this->setFlash('error', 'La contraseña actual es incorrecta.');
            }
            $this->redirect('/perfil/password');
        }

        $this->render('propietario/perfil/password', [
            'titulo' => 'Cambiar Contraseña'
        ]);
    }
}
