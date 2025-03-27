<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class SedesHorariosModel extends Model
{
    use HasFactory;
    protected $table = 'tb_sede_horario';
    protected $primaryKey = 'id_sede_horario';
    protected $fillable = [
            'id_sede'
        ,   'id_horario'
        ,   'cupo_sede_horario'
        ,   'dia_sede_horario'
        ,   'estado_sede_horario'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
