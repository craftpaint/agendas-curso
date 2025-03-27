<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class HorariosModel extends Model
{
    use HasFactory;
    protected $table = 'tb_horario';
    protected $primaryKey = 'id_horario';
    protected $fillable = [
            'rango_horario'
        ,   'inicio_horario'
        ,   'fin_horario'
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
