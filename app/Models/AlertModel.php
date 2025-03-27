<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class AlertModel extends Model
{
    use HasFactory;
    protected $table = 'tb_alert';
    protected $primaryKey = 'id';
    protected $fillable = [
            'id'
        ,   'id_cita'
        ,   'state'
        ,   'id_sede'
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
