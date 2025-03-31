<?php

namespace App\Imports;

use App\Models\Clientes;
use App\Models\Plan;
use App\Models\Disciplina;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $planId = Plan::where('nombre', $row['plan'] ?? '')->value('id');
        $disciplinaId = Disciplina::where('nombre', $row['disciplina'] ?? '')->value('id');

        return new Clientes([
            'nombre' => $row['nombres'] ?? null,
            'apellido_paterno' => $row['apellido_paterno'] ?? null,
            'apellido_materno' => $row['apellido_materno'] ?? null,
            'fecha_de_nacimiento' => Carbon::parse($row['fecha_de_nacimiento']),
            'ci' => $row['c_i'] ?? null,
            'telefono' => $row['celular'] ?? null,
            'correo' => $row['correo_electronico'] ?? null,
            'sexo' => strtolower($row['sexo'] ?? 'otro'),
            'antecedentes_medicos' => $row['antecedentes_medicos'] ?? null,
            'contacto_emergencia_nombre' => $row['contacto_de_emergencia_nombre'] ?? null,
            'contacto_emergencia_parentesco' => $row['contacto_de_emergencia_parentesco'] ?? null,
            'contacto_emergencia_celular' => $row['contacto_de_emergencia_celular'] ?? null,

            'plan_id' => $planId,
            'disciplina_id' => $disciplinaId,

            'fecha_inicio' => Carbon::parse($row['fecha_inicial']),
            'fecha_final' => Carbon::parse($row['fecha_final']),

            'precio_plan' => $row['precio_del_plan'] ?? 0,
            'a_cuenta' => $row['a_cuenta'] ?? 0,
            'saldo' => $row['saldo'] ?? 0,
            'total' => $row['total'] ?? 0,
            'casillero_monto' => $row['casillero_monto'] ?? 0,

            'metodo_pago' => strtolower($row['metodo_de_pago'] ?? 'efectivo'),
            'comprobante' => strtolower($row['comprobante'] ?? 'simple'),

            'estado' => 'activo',
            'foto' => null, // La imagen no puede ser importada por Excel, deberás subirla manualmente
            'biometrico_id' => $row['biometrico_id'] ?? null,
            'registrado_por' => auth()->id(), // o null si se importa automáticamente

            'bloqueado_por_deuda' => false,
        ]);
    }
}
