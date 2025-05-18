<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone',
        'email',
        'table_id',
        'total_price',
        'note',
        
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);  // Relasi ke tabel 'Table'
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class)->withPivot('quantity', 'price');  
    }
}
