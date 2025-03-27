<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class EstadoModel extends Model
{
    use HasFactory;
    protected $table = 'tb_estado';
    protected $primaryKey = 'id_estado';
    protected $fillable = [
            'nombre_estado'
        ,   'desc_estado'
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
