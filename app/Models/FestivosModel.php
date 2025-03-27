<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class FestivosModel extends Model
{
    use HasFactory;
    protected $table = 'tb_festivos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fecha'
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
