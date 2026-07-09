<?php
namespace App\Models;

use App\Core\Database;

class CuentaBancaria
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function obtenerPorUsuario($usuario_id)
    {
        $sql = "SELECT c.*, 
                       b.nombre as banco_nombre, 
                       tc.nombre as tipo_cuenta_nombre
                FROM cuenta_bancaria c
                LEFT JOIN catalogo b ON c.banco_codigo = b.codigo
                LEFT JOIN catalogo tc ON c.tipo_cuenta_codigo = tc.codigo
                WHERE c.usuario_id = :usuario_id AND c.habilitado = TRUE
                ORDER BY c.principal DESC, c.creado DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPrincipal($usuario_id)
    {
        $sql = "SELECT c.*, 
                       b.nombre as banco_nombre, 
                       tc.nombre as tipo_cuenta_nombre
                FROM cuenta_bancaria c
                LEFT JOIN catalogo b ON c.banco_codigo = b.codigo
                LEFT JOIN catalogo tc ON c.tipo_cuenta_codigo = tc.codigo
                WHERE c.usuario_id = :usuario_id AND c.principal = TRUE AND c.habilitado = TRUE
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['principal'])) {
            // Desmarcar otras cuentas principales
            $sqlUnset = "UPDATE cuenta_bancaria SET principal = FALSE WHERE usuario_id = :usuario_id";
            $stmtUnset = $this->conn->prepare($sqlUnset);
            $stmtUnset->bindParam(':usuario_id', $data['usuario_id']);
            $stmtUnset->execute();
        }

        if (!empty($data['cuenta_bancaria_id'])) {
            $sql = "UPDATE cuenta_bancaria SET 
                    banco_codigo = :banco_codigo,
                    tipo_cuenta_codigo = :tipo_cuenta_codigo,
                    numero_cuenta = :numero_cuenta,
                    cci = :cci,
                    titular = :titular,
                    principal = :principal,
                    modificado = NOW(),
                    modificado_por = :usuario_id_modificador
                    WHERE cuenta_bancaria_id = :cuenta_bancaria_id AND usuario_id = :usuario_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cuenta_bancaria_id', $data['cuenta_bancaria_id']);
            $stmt->bindParam(':usuario_id_modificador', $data['usuario_id']);
        } else {
            $sql = "INSERT INTO cuenta_bancaria (
                        usuario_id, banco_codigo, tipo_cuenta_codigo, numero_cuenta, cci, titular, principal, habilitado, creado, creado_por
                    ) VALUES (
                        :usuario_id, :banco_codigo, :tipo_cuenta_codigo, :numero_cuenta, :cci, :titular, :principal, TRUE, NOW(), :usuario_id_creador
                    )";
            $stmt = $this->conn->prepare($sql);
        }

        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        if (empty($data['cuenta_bancaria_id'])) {
            $stmt->bindParam(':usuario_id_creador', $data['usuario_id']);
        }
        $stmt->bindParam(':banco_codigo', $data['banco_codigo']);
        $stmt->bindParam(':tipo_cuenta_codigo', $data['tipo_cuenta_codigo']);
        $stmt->bindParam(':numero_cuenta', $data['numero_cuenta']);
        $stmt->bindParam(':cci', $data['cci']);
        $stmt->bindParam(':titular', $data['titular']);
        
        $principal = !empty($data['principal']) ? 'TRUE' : 'FALSE';
        $stmt->bindParam(':principal', $principal, \PDO::PARAM_BOOL);

        return $stmt->execute();
    }
}
